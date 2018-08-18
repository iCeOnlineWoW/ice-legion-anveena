<?php

namespace App\Models;

/**
 * Model for managing projects
 */
class ProjectModel extends BaseModel
{
    public $implicitTable = 'projects';

    /**
     * Retrieves project record by id
     * @param string $id
     * @return \Nette\Database\Table\ActiveRow
     */
    public function getProjectById($id)
    {
        return $this->getTable()->where('id', $id)->fetch();
    }

    /**
     * Retrieves project record by name
     * @param string $name
     * @return \Nette\Database\Table\ActiveRow
     */
    public function getProjectByName($name)
    {
        return $this->getTable()->where('name', $name)->fetch();
    }

    /**
     * Retrieves all projects in system
     * @return \Nette\Database\Table\Selection
     */
    public function getAllProjects()
    {
        return $this->getTable();
    }
    
    /**
     * Retrieves projects fetched into associative map
     * @return array
     */
    public function getProjectMap()
    {
        $sel = $this->getAllProjects();
        $arr = array();
        foreach ($sel as $proj)
            $arr[$proj->id] = $proj->name;
        
        return $arr;
    }

    /**
     * Adds new project to database
     * @param string $name
     * @param string $description
     * @param string $repo_type
     * @param string $repo_url
     * @param string $repo_branch
     * @return \Nette\Database\Table\ActiveRow
     */
    public function addProject($name, $description, $repo_type, $repo_url, $repo_branch, $local_deploy_dir)
    {
        return $this->getTable()->insert(array(
            'name' => $name,
            'description' => $description,
            'repository_type' => $repo_type,
            'repository_url' => $repo_url,
            'repository_branch' => $repo_branch,
            'local_deploy_dir' => $local_deploy_dir,
            'last_build_number' => 0,
            'last_build_status' => \App\Models\BuildStatus::NONE,
        ));
    }

    /**
     * Edits existing project in database
     * @param int $id
     * @param string $name
     * @param string $description
     * @param string $repo_type
     * @param string $repo_url
     * @param string $repo_branch
     * @return \Nette\Database\Table\ActiveRow
     */
    public function editProject($id, $name, $description, $repo_type, $repo_url, $repo_branch, $local_deploy_dir)
    {
        return $this->getTable()->where('id', $id)->update(array(
            'name' => $name,
            'description' => $description,
            'repository_type' => $repo_type,
            'repository_url' => $repo_url,
            'repository_branch' => $repo_branch,
            'local_deploy_dir' => $local_deploy_dir
        ));
    }

    /**
     * Delete project with specified id
     * @param int $id
     */
    public function deleteProject($id)
    {
        $this->getTable()->where('id', $id)->delete();
    }

    /**
     * Updates project last build status
     * @param int $id
     * @param int $build_number
     * @param string $build_status
     */
    public function setProjectBuildInfo($id, $build_number, $build_status)
    {
        $this->getTable()->where('id', $id)->update(array(
            'last_build_number' => $build_number,
            'last_build_status' => $build_status
        ));
    }
}
