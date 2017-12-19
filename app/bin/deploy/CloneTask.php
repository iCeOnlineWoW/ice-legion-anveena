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
            $out = null;
            $ret = $this->execCmd("git clone -b ".$this->project->repository_branch." ".$this->project->repository_url." p".$this->project->id, $out);
            if ($ret !== 0)
                return false;

            chdir('p'.$this->project->id);
        }
        else // repository exists - update it
        {
            $this->log('Updating repository of project '.$this->project->name.'...');
            
            $out = null;
            $ret = $this->execCmd("git config remote.origin.url ".$this->project->repository_url, $out);
            if ($ret !== 0)
                return false;

            $ret = $this->execCmd("git fetch origin ".$this->project->repository_branch, $out);
            if ($ret !== 0)
                return false;

            $retArray = array();
            $ret = $this->execCmd('git rev-parse '.escapeshellarg('refs/remotes/origin/'.$this->project->repository_branch.'^{commit}'), $retArray);
            if ($ret !== 0)
                return false;

            $commit = $retArray[0];

            $ret = $this->execCmd("git checkout -f ".$commit, $out);
            if ($ret !== 0)
                return false;
        }

        return true;
    }
}
