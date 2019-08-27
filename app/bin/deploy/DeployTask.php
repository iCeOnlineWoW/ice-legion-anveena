<?php

/**
 * Base class for all deploy tasks (build steps)
 */
abstract class DeployTask
{
    protected $container;
    /** @var \App\Models\WorkerModel */
    protected $workers;
    /** @var \App\Models\ProjectModel */
    protected $projects;
    /** @var \App\Models\BuildModel */
    protected $builds;
    /** @var \App\Models\CredentialModel */
    protected $credentials;
    /** @var int */
    protected $worker_id;
    /** @var array */
    protected $parameters;
    /** @var int */
    protected $build_id;
    
    /**
     * Sets task up with given arguments
     * @param mixed $container
     * @param array $args
     * @return boolean
     */
    public function Setup($container, $args)    
    {
        try
        {
            $this->container = $container;
            $this->parameters = $args;
            $this->workers = $container->getByType('App\Models\WorkerModel');
            $this->projects = $container->getByType('App\Models\ProjectModel');
            $this->builds = $container->getByType('App\Models\BuildModel');
            $this->credentials = $container->getByType('App\Models\CredentialModel');
            $this->worker_id = $args['worker_id'];
            $this->build_id = $args['build_id'];
        }
        catch (Exception $e)
        {
            return false;
        }
        
        return true;
    }

    /**
     * Runs task with arguments; the "Setup" call must be made prior "Run"
     */
    abstract public function Run();
    
    /**
     * Logs message to build log
     * @param string $what
     */
    public function log($what)
    {
        $line = "Worker ".$this->worker_id.": ".$what;
        $this->builds->appendLog($this->build_id, $line);
        echo $line."\n";
    }

    protected function execCmd($cmd, &$output)
    {
        $this->log(">> ".$cmd);
        $retval = 0;
        exec($cmd . " 2>&1", $output, $retval);

        return $retval;
    }
}
