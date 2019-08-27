<?php

/**
 * Composer install/update task
 */
class ComposerTask extends DeployTask
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
        // just run composer, there's nothing special to this task
        $output = "";
        $ret = $this->execCmd("composer update", $output);
        
        if ($ret !== 0)
            $this->log("Composer returned $ret. Output: ".implode("\n", $output));

        return ($ret === 0);
    }
}
