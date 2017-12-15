<?php

/**
 * Clone repository task
 */
class CloneTask extends DeployTask
{
    /** @var \Nette\Database\Table\ActiveRow */
    protected $project;
    
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
        
        return true;
    }
    
    public function Run()
    {
        $repo = null;

        // repository does not exist - clone it
        if (!file_exists('.git'))
        {
            $this->log('Cloning repository of project '.$this->project->name.'...');
            
            chdir('..');
            $repo = \Cz\Git\GitRepository::cloneRepository($this->project->repository_url, 'p'.$this->project->id);
            chdir('p'.$this->project->id);
        }
        else // repository exists - update it
        {
            $this->log('Updating repository of project '.$this->project->name.'...');
            
            $repo = new \Cz\Git\GitRepository(getcwd());
            
            // TODO:
        }
    }
}
