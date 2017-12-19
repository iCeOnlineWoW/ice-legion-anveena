<?php

namespace App\Models;

/**
 * Model for managing credentials
 */
class ConfigurationModel extends BaseModel
{
    public $implicitTable = 'configurations';

    /**
     * Retrieves configuration record by id
     * @param string $identifier
     * @return \Nette\Database\Table\ActiveRow
     */
    public function getConfigurationByIdentifier($identifier)
    {
        return $this->getTable()->where('identifier', $identifier)->fetch();
    }

    /**
     * Retrieves all configurations in system
     * @return \Nette\Database\Table\Selection
     */
    public function getAllConfigurations()
    {
        return $this->getTable();
    }
    
    /**
     * Retrieves configurations fetched into associative map
     * @return array
     */
    public function getConfigurationsMap()
    {
        $sel = $this->getAllConfigurations();
        $arr = array();
        foreach ($sel as $cf)
            $arr[$cf->identifier] = $cf->identifier;
        
        return $arr;
    }

    /**
     * Adds new configuration to database
     * @param string $identifier
     * @param string $configuration
     * @return \Nette\Database\Table\ActiveRow
     */
    public function addConfiguration($identifier, $configuration)
    {
        return $this->getTable()->insert(array(
            'identifier' => $identifier,
            'configuration' => $configuration
        ));
    }

    /**
     * Edits credential in database
     * @param string $identifier
     * @param string $new_identifier
     * @param string $configuration
     */
    public function editConfiguration($identifier, $new_identifier, $configuration)
    {
        $this->getTable()->where('identifier', $identifier)->update(array(
            'identifier' => $new_identifier,
            'configuration' => $configuration
        ));
    }

    /**
     * Delete configuration with specified identifier
     * @param string $identifier
     */
    public function deleteConfiguration($identifier)
    {
        $this->getTable()->where('identifier', $identifier)->delete();
    }

    /**
     * Retrieves parsed configuration; returns null when not found, empty array on error
     * @param string $identifier
     * @return null | array
     */
    public function getParsed($identifier)
    {
        $conf = $this->getConfigurationByIdentifier($identifier);
        if (!$conf)
            return null;

        $cf = array();

        $expl = explode("\n", $conf->configuration);
        foreach ($expl as $a)
        {
            $pos = strpos($a, '=');
            if ($pos <= 0)
                return array();

            $key = substr($a, 0, $pos);
            $val = substr($a, $pos+1);

            $cf[$key] = $val;
        }

        return $cf;
    }
}
