<?php

namespace App\Models;

/**
 * Model for managing workers
 */
class WorkerModel extends BaseModel
{
    public $implicitTable = 'workers';

    /**
     * Retrieves worker record by id
     * @param int $identifier
     * @return \Nette\Database\Table\ActiveRow
     */
    public function getWorkerById($id)
    {
        return $this->getTable()->where('id', $id)->fetch();
    }

    /**
     * Retrieves all workers in system
     * @return \Nette\Database\Table\Selection
     */
    public function getAllWorkers()
    {
        return $this->getTable();
    }

    /**
     * Creates a new worker in database
     * @param int $id
     */
    public function createWorker($id)
    {
        $existing = $this->getWorkerById($id);
        if ($existing)
        {
            $this->updateWorkerStatus($id, \App\Models\WorkerStatus::IDLE, null);
        }
        else
        {
            $this->getTable()->insert(array(
                'id' => $id,
                'status' => \App\Models\WorkerStatus::IDLE
            ));
        }
    }

    /**
     * Updates worker status in database
     * @param int $id
     * @param string $status
     * @param int $projects_id
     */
    public function updateWorkerStatus($id, $status, $projects_id)
    {
        $this->getTable()->where('id', $id)->update(array(
            'id' => $id,
            'status' => $status,
            'job_start' => date('Y-m-d H:i:s'),
            'projects_id' => $projects_id
        ));
    }

    /**
     * Deletes worker with given id
     * @param int $id
     */
    public function deleteWorker($id)
    {
        $this->getTable()->where('id', $id)->delete();
    }
    
    /**
     * Sends command to specified worker to start build
     * @param int $id
     * @param int $projects_id
     * @return boolean
     */
    protected function workerBuildCommand($id, $projects_id)
    {
        $worker = $this->getWorkerById($id);
        if (!$worker)
            return false;
        
        $sock = socket_create(AF_INET, SOCK_DGRAM, 0);
        if ($sock === false)
            return false;
        
        $tosend = ''.$projects_id;
        
        $status = socket_sendto($sock, $tosend, strlen($tosend), 0, "127.0.0.1", WORKER_BASE_PORT+$id);
        if ($status == 0)
            return false;
        
        return true;
    }
    
    /**
     * Selects available worker and sends build command
     * @param int $projects_id
     * @return boolean
     */
    public function selectAndRunBuild($projects_id)
    {
        $workers = $this->getAllWorkers();
        foreach ($workers as $wrk)
        {
            if ($wrk->status == \App\Models\WorkerStatus::IDLE)
            {
                if ($this->workerBuildCommand($wrk->id, $projects_id))
                    return true;
            }
        }
        
        return false;
    }
}
