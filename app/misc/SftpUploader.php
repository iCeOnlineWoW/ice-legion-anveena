<?php

class SftpUploader
{
    private $connectionID = -1;
    private $sftpSession = false;
    private $blackList = array('.', '..', 'Thumbs.db', '.git');

    public function __construct($sftpHost = "")
    {
        $this->connect($sftpHost);
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    public function connect($sftpHost)
    {
        $this->disconnect();

        if ($sftpHost != "")
        {
            $parts = explode(':', $sftpHost);
            $port = 22;
            if (count($parts) > 0 && is_numeric($parts[count($parts) - 1]))
            {
                $port = intval($parts[count($parts) - 1]);
                $sftpHost = substr($sftpHost, 0, strlen($sftpHost) - strlen($parts[count($parts) - 1]) - 1);
            }

            echo "conn to ".$sftpHost." to port ".$port."\r\n";
            $this->connectionID = ssh2_connect($sftpHost, $port);
        }

        return $this->connectionID;
    }

    public function login_password($sftpUser, $sftpPass)
    {
        if (!$this->connectionID)
            throw new Exception("Connection not established.", -1);

        if (!ssh2_auth_password($this->connectionID, $sftpUser, $sftpPass))
            throw new Exception("Authentication failed.", -2);

        $this->sftpSession = ssh2_sftp($this->connectionID);

        return $this->sftpSession;
    }

    public function disconnect()
    {
        if ($this->connectionID !== -1)
        {
            // This call causes segfault in PECL/SSH2 v1.x
            // The connection will not be closed imediatelly on v1.x, but rather
            // with any further attempt to connect elsewhere
            //ssh2_disconnect($this->connectionID);

            // this should work on PECL/SSH2 v0.x, but is not needed on v1.x
            unset($this->connectionID);

            $this->connectionID = -1;
        }
    }

    public function send_recursive_directory($localPath, $remotePath)
    {
        return $this->recurse_directory($localPath, $localPath, $remotePath);
    }

    private function recurse_directory($rootPath, $localPath, $remotePath)
    {
        $errorList = array();
        if (!is_dir($localPath))
            throw new Exception("Invalid directory: $localPath");

        chdir($localPath);
        $directory = opendir(".");

        while ($file = readdir($directory))
        {
            if (in_array($file, $this->blackList))
                continue;

            if (is_dir($file))
            {
                $errorList["$remotePath/$file"] = $this->make_directory("$remotePath/$file");
                $errorList[] = $this->recurse_directory($rootPath, "$localPath/$file", "$remotePath/$file");
                chdir($localPath);
            }
            else
                $errorList["$remotePath/$file"] = $this->put_file("$localPath/$file", "$remotePath/$file");
        }
        return $errorList;
    }

    public function remove_recursive($remotePath, $childrenOnly = true)
    {
        if (ssh2_sftp_unlink($this->sftpSession, $remotePath) === false)
        {
            $sftp_fd = intval($this->sftpSession);

            if ($remotePath[0] === '/')
                $pathSpec = substr($remotePath, 1);
            else
                $pathSpec = $remotePath;

            $handle = opendir("ssh2.sftp://$sftp_fd/$pathSpec");
            if ($handle)
            {
                while (($entry = readdir($handle)) !== false)
                {
                    if (!in_array(basename($entry), $this->blackList))
                        $this->remove_recursive($remotePath.'/'.$entry, false);
                }
            }

            if (!$childrenOnly)
                ssh2_sftp_rmdir($this->sftpSession, $remotePath);
        }
    }

    public function make_directory($remotePath)
    {
        $error = "";
        try
        {
            ssh2_sftp_mkdir($this->sftpSession, $remotePath);
        }
        catch (Exception $e)
        {
            if ($e->getCode() == 2)
                $error = $e->getMessage();
        }

        return $error;
    }

    public function put_file($localPath, $remotePath)
    {
        if (ssh2_scp_send($this->connectionID, $localPath, $remotePath) === false)
            return false;

        return true;
    }
}