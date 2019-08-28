<?php

/**
 * SFTP upload task
 */
class UploadSftpTask extends DeployTask
{
    /** @var \Nette\Database\Table\ActiveRow */
    protected $project;

    protected $sftpHost;
    protected $sftpPort;
    protected $sftpDirectory;

    protected $sftpCredentials;

    public function Setup($container, $args)
    {
        if (!parent::Setup($container, $args))
            return false;

        $this->container = $container;

        if (!isset($args['projects_id']) || !is_numeric($args['projects_id']))
            return false;

        $this->project = $this->projects->getProjectById($args['projects_id']);

        if (!$this->project)
            return false;

        $this->sftpHost = $this->parameters['ftp_host'];
        $this->sftpPort = $this->parameters['ftp_port'];
        // fallback to default SSH port
        if (strlen($this->sftpPort) == 0)
            $this->sftpPort = 22;

        $this->sftpDirectory = $this->parameters['ftp_directory'];

        $this->sftpCredentials = $this->credentials->getCredentialByIdentifier($args['step']->ref_credentials_identifier);

        if (!$this->sftpCredentials)
            return false;

        return true;
    }

    public function Run()
    {
        $sftp = new SftpUploader($this->sftpHost.":".$this->sftpPort);

        try
        {
            $this->log("Logging in to SFTP host ".$this->sftpHost." with given credentials...");

            if ($this->sftpCredentials->type == \App\Models\CredentialType::LOGIN)
                $sftpSession = $sftp->login_password($this->sftpCredentials->username, $this->sftpCredentials->auth_ref);
            // TODO: pubkey auth

            if (!$sftpSession)
            {
                $this->log("Could not establish SFTP connection to ".$this->sftpHost." with given credentials");
                return false;
            }

            $this->log("Wiping remote directory...");

            // wipe the remote directory, but just the contents, as with SFTP/SCP, this may be more difficult than on FTP (rights, etc.)
            $sftp->remove_recursive($this->sftpDirectory, true);

            $this->log("Uploading project structure...");

            $sftp->send_recursive_directory(getcwd(), $this->sftpDirectory);

            $sftp->disconnect();
        }
        catch (Exception $e)
        {
            $this->log("SFTP connector has thrown an exception: ".$e->getMessage());
            return false;
        }

        return true;
    }
}
