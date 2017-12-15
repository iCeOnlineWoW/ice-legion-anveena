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
    /** @var int */
    protected $worker_id;
    
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
            $this->workers = $container->getByType('App\Models\WorkerModel');
            $this->projects = $container->getByType('App\Models\ProjectModel');
            $this->worker_id = $args['worker_id'];
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
        // TODO: redirect to log file
        echo "Worker ".$this->worker_id.": ".$what."\n";
    }
}
