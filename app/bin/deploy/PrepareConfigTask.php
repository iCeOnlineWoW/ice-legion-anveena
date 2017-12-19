<?php

/**
 * Task for preparing config from configuration
 */
class PrepareConfigTask extends DeployTask
{
    /** @var \App\Models\ConfigurationModel */
    protected $configurations;

    public function Setup($container, $args)
    {
        if (!parent::Setup($container, $args))
            return false;

        $this->container = $container;

        $this->configurations = $container->getByType('App\Models\ConfigurationModel');

        if (!isset($this->parameters['step']->ref_configurations_identifier) || !$this->parameters['step']->ref_configurations_identifier)
        {
            $this->log("Configuration identifier must be set");
            return false;
        }

        if (!isset($this->parameters['source_file']) || !isset($this->parameters['target_file']))
        {
            $this->log("Source and target config files must be set");
            return false;
        }

        if (!file_exists($this->parameters['source_file']))
        {
            $this->log("Source config file ".$this->parameters['source_file']." does not exist");
            return false;
        }

        return true;
    }

    public function Run()
    {
        $contents = file_get_contents($this->parameters['source_file']);

        $conf = $this->configurations->getParsed($this->parameters['step']->ref_configurations_identifier);
        if (!$conf)
        {
            $this->log("Configuration ".$this->parameters['step']->ref_configurations_identifier." not found");
            return false;
        }
        
        if (empty($conf))
        {
            $this->log("Configuration ".$this->parameters['step']->ref_configurations_identifier." does not contain any settings");
            return false;
        }

        foreach ($conf as $key => $val)
            $contents = str_replace('${{'.$key.'}}', $val, $contents);

        file_put_contents($this->parameters['target_file'], $contents);

        return true;
    }
}
