<?php

/**
 * FTP upload task
 */
class UploadFtpTask extends DeployTask
{
    /** @var \Nette\Database\Table\ActiveRow */
    protected $project;

    protected $ftpHost;
    protected $ftpDirectory;

    protected $ftpCredentials;

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

        $this->ftpHost = $this->parameters['ftp_host'];
        $this->ftpDirectory = $this->parameters['ftp_directory'];

        $this->ftpCredentials = $this->credentials->getCredentialByIdentifier($args['step']->ref_credentials_identifier);

        if (!$this->ftpCredentials || $this->ftpCredentials->type != \App\Models\CredentialType::LOGIN)
            return false;

        return true;
    }

    public function Run()
    {
        $ftp = new FtpUploader("$this->ftpHost");

        $this->log("Logging in to FTP host ".$this->ftpHost." with given credentials...");

        $ftpSession = $ftp->login($this->ftpCredentials->username, $this->ftpCredentials->auth_ref);

        if (!$ftpSession)
        {
            $this->log("Could not establish FTP connection to ".$this->ftpHost." with given credentials");
            return false;
        }

        $this->log("Wiping remote directory...");

        // wipe the remote directory
        $ftp->remove_recursive($this->ftpDirectory);

        $this->log("Uploading project structure...");

        // then create it again
        $ftp->make_directory($this->ftpDirectory);

        $ftp->send_recursive_directory(getcwd(), $this->ftpDirectory);

        $ftp->disconnect();

        return true;
    }
}
