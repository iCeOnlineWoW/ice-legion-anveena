<?php

namespace App\Models;

/**
 * Model for managing build steps
 */
class BuildStepModel extends BaseModel
{
    public $implicitTable = 'project_build_steps';
    
    /**
     * Retrieves project build step by project id and step number
     * @param int $projects_id
     * @param int $step
     * @return \Nette\Database\Table\ActiveRow
     */
    public function getBuildStep($projects_id, $step)
    {
        return $this->getTable()->where('projects_id', $projects_id)->where('step', $step)->fetch();
    }

    /**
     * Retrieves build steps for one project
     * @param int $projects_id
     * @return \Nette\Database\Table\Selection
     */
    public function getStepsForProject($projects_id)
    {
        return $this->getTable()->where('projects_id', $projects_id)->order('step ASC');
    }
    
    /**
     * Adds build step to database
     * @param int $projects_id
     * @param string $type
     * @param string $cred_id
     * @param string $proj_id
     * @param int $usr_id
     * @param array $additional_params
     * @return int
     */
    public function addBuildStep($projects_id, $type, $cred_id, $proj_id, $usr_id, $additional_params = array())
    {
        $maxid = $this->getStepsForProject($projects_id)->max('step');
        if (!$maxid)
            $maxid = 0;
        
        $this->getTable()->insert(array(
            'projects_id' => $projects_id,
            'step' => $maxid + 1,
            'type' => $type,
            'ref_credentials_identifier' => $cred_id,
            'ref_projects_id' => $proj_id,
            'ref_users_id' => $usr_id,
            'additional_params' => json_encode($additional_params)
        ));
        
        return $maxid + 1;
    }
    
    /**
     * Edits build step in database
     * @param int $projects_id
     * @param int $step
     * @param string $type
     * @param string $cred_id
     * @param string $proj_id
     * @param int $usr_id
     * @param string $cfg_id
     * @param array $additional_params
     */
    public function editBuildStep($projects_id, $step, $type, $cred_id, $proj_id, $usr_id, $cfg_id, $additional_params = array())
    {
        $this->getTable()->where('projects_id', $projects_id)->where('step', $step)->update(array(
            'type' => $type,
            'ref_credentials_identifier' => $cred_id,
            'ref_projects_id' => $proj_id,
            'ref_users_id' => $usr_id,
            'ref_configurations_identifier' => $cfg_id,
            'additional_params' => json_encode($additional_params)
        ));
    }
    
    /**
     * Removes build step from database and moves next steps by one
     * @param int $projects_id
     * @param int $step
     */
    public function deleteBuildStep($projects_id, $step)
    {
        $this->getTable()->where('projects_id', $projects_id)->where('step', $step)->delete();
        
        $this->database->query('UPDATE '.$this->implicitTable.' SET step = step - 1 WHERE step > '.$step);
    }
    
    /**
     * Moves step by one place up
     * @param int $projects_id
     * @param int $step
     */
    public function moveStepUp($projects_id, $step)
    {
        if ($step == 1)
            return;
        
        $prev = $this->getTable()->where('projects_id', $projects_id)->where('step', $step - 1);
        $cur = $this->getTable()->where('projects_id', $projects_id)->where('step', $step);
        
        // due to lazy loading, delay real move by one query
        $this->database->query('UPDATE '.$this->implicitTable.' SET step = '.($step+10000).' WHERE projects_id = '.$projects_id.' AND step = '.$step);
        $this->database->query('UPDATE '.$this->implicitTable.' SET step = '.$step.' WHERE projects_id = '.$projects_id.' AND step = '.($step - 1));
        $this->database->query('UPDATE '.$this->implicitTable.' SET step = '.($step-1).' WHERE projects_id = '.$projects_id.' AND step = '.($step + 10000));
    }
    
    /**
     * Moves step by one place down
     * @param int $projects_id
     * @param int $step
     */
    public function moveStepDown($projects_id, $step)
    {
        $maxid = $this->getStepsForProject($projects_id)->max('step');
        if (!$maxid || $maxid == $step)
            return;
        
        $next = $this->getTable()->where('projects_id', $projects_id)->where('step', $step + 1);
        $cur = $this->getTable()->where('projects_id', $projects_id)->where('step', $step);
        
        // due to lazy loading, delay real move by one query
        $this->database->query('UPDATE '.$this->implicitTable.' SET step = '.($step+10000).' WHERE projects_id = '.$projects_id.' AND step = '.$step);
        $this->database->query('UPDATE '.$this->implicitTable.' SET step = '.$step.' WHERE projects_id = '.$projects_id.' AND step = '.($step + 1));
        $this->database->query('UPDATE '.$this->implicitTable.' SET step = '.($step+1).' WHERE projects_id = '.$projects_id.' AND step = '.($step + 10000));
    }
}
