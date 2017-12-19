<?php

namespace App\Models;

/**
 * Model for managing build records
 */
class BuildModel extends BaseModel
{
    public $implicitTable = 'builds';

    /**
     * Retrieves build record by id
     * @param int $id
     * @return \Nette\Database\Table\ActiveRow
     */
    public function getBuildById($id)
    {
        return $this->getTable()->where('id', $id)->fetch();
    }

    /**
     * Retrieves build record by project ID and build number
     * @param int $projects_id
     * @param int $build_number
     * @return \Nette\Database\Table\ActiveRow
     */
    public function getBuild($projects_id, $build_number)
    {
        return $this->getTable()->where('projects_id', $projects_id)->where('build_number', $build_number)->fetch();
    }

    /**
     * Adds new build record, prepares it to be run
     * @param int $projects_id
     * @return \Nette\Database\Table\ActiveRow
     */
    public function addBuildRecord($projects_id)
    {
        $maxno = $this->getTable()->where('projects_id', $projects_id)->max('build_number');
        if (!$maxno)
            $maxno = 0;

        return $this->getTable()->insert(array(
            'projects_id' => $projects_id,
            'build_number' => $maxno + 1,
            'start_at' => date('Y-m-d H:i:s'),
            'end_at' => null,
            'status' => \App\Models\BuildStatus::NONE
        ));
    }

    /**
     * Updates build status
     * @param int $id
     * @param string $status
     */
    public function updateBuildStatus($id, $status)
    {
        $this->getTable()->where('id', $id)->update(array(
            'status' => $status
        ));
    }
}
