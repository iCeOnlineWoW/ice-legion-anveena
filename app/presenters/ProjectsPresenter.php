<?php

namespace App\Presenters;

class ProjectsPresenter extends SecuredPresenter
{
    /** @var \App\Models\ProjectModel @inject */
    public $projects;

    /** @var int */
    public $editId = 0;

    public function actionEdit($id)
    {
        if (!$this->projects->getProjectById($id))
        {
            $this->flashMessage($this->translator->translate('main.project.form.no_such_project'), 'error');
            $this->redirect('Projects:');
            return;
        }

        $this->editId = $id;
    }

    public function handleDelete($project_id)
    {
        $this->projects->deleteProject($project_id);
        $this->redrawControl('projectList');
    }

    public function createComponentProjectForm($name)
    {
        return new \App\Components\ProjectForm($this->projects, $this->translator, $this->editId);
    }

    public function createComponentProjectList($name)
    {
        return new \App\Components\ProjectList($this->projects, $this->translator);
    }
}
