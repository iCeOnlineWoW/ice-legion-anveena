<?php

class FtpUploader
{
    private $connectionID;
    private $ftpSession = false;
    private $blackList = array('.', '..', 'Thumbs.db', '.git');

    public function __construct($ftpHost = "")
    {
        if ($ftpHost != "")
            $this->connectionID = ftp_connect($ftpHost);
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    public function connect($ftpHost)
    {
        $this->disconnect();
        $this->connectionID = ftp_connect($ftpHost);

        return $this->connectionID;
    }

    public function login($ftpUser, $ftpPass)
    {
        if (!$this->connectionID)
            throw new Exception("Connection not established.", -1);

        $this->ftpSession = ftp_login($this->connectionID, $ftpUser, $ftpPass);

        // enter passive mode
        // TODO: make this optional
        ftp_pasv($this->ftpSession, true);

        return $this->ftpSession;
    }

    public function disconnect()
    {
        if (isset($this->connectionID))
        {
            ftp_close($this->connectionID);
            unset($this->connectionID);
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

    public function remove_recursive($remotePath)
    {
        if (ftp_delete($this->connectionID, $remotePath) === false)
        {
            if ($children = ftp_nlist($this->connectionID, $remotePath))
            {
                foreach ($children as $p)
                {
                    if (!in_array(basename($p), $this->blackList))
                        $this->remove_recursive($p);
                }
            }

            ftp_rmdir($this->connectionID, $remotePath);
        }
    }

    public function make_directory($remotePath)
    {
        $error = "";
        try
        {
            ftp_mkdir($this->connectionID, $remotePath);
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
        $error = "";
        try
        {
            ftp_put($this->connectionID, $remotePath, $localPath, FTP_BINARY);
        }
        catch (Exception $e)
        {
            if ($e->getCode() == 2)
                $error = $e->getMessage();
        }
        return $error;
    }
}