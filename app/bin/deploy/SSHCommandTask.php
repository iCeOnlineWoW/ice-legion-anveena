<?php

/**
 * Phinx remote execution task
 */
class SSHCommandTask extends DeployTask
{
    /** @var \Nette\Database\Table\ActiveRow */
    protected $project;
    
    protected $sshHost;
    protected $sshPort;
    protected $command;
    protected $sshCredentials;
    protected $ignoreError = false;

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
        
        $this->sshHost = $this->parameters['ftp_host'];
        $this->sshPort = $this->parameters['ftp_port'];
        // fallback to default SSH port
        if (strlen($this->sshPort) == 0)
            $this->sshPort = 22;

        $this->sshCredentials = $this->credentials->getCredentialByIdentifier($args['step']->ref_credentials_identifier);

        if (!$this->sshCredentials)
            return false;

        $this->command = $this->parameters['command'];

        if (!$this->command || strlen($this->command) == 0)
            return false;

        $this->ignoreError = $this->parameters['ignore_error'];

        return true;
    }

    public function Run()
    {
        try
        {
            $sess = ssh2_connect($this->sshHost, $this->sshPort);
            if (!$sess)
            {
                $this->log("Unable to connect to host " . $this->sshHost);
                return false;
            }

            $res = ssh2_auth_password($sess, $this->sshCredentials->username, $this->sshCredentials->password);
            if (!$res)
            {
                $this->log("Invalid login credentials (not authenticated)");
                return false;
            }

            $stream = ssh2_exec($sess, $this->command);

            $error_stream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
            stream_set_blocking($stream, true);
            stream_set_blocking($error_stream, true);
            //$stdout = stream_get_contents($stream);
            $stderr = stream_get_contents($error_stream);
            fclose($stream);
            fclose($error_stream);

            if (strlen($stderr) !== 0)
            {
                $this->log("SSH remote command wrote to error output: ".$stderr);
                return ($this->ignoreError ? true : false);
            }
        }
        catch (Exception $ex)
        {
            $this->log("SSH remote command task has thrown an exception: ".$ex->getMessage());
            return false;
        }

        return true;
    }
}
