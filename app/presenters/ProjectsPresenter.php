<?php

namespace App\Presenters;

class ProjectsPresenter extends SecuredPresenter
{
    /** @var \App\Models\ProjectModel @inject */
    public $projects;
    /** @var \App\Models\BuildStepModel @inject */
    public $buildSteps;
    /** @var \App\Models\CredentialModel @inject */
    public $credentials;
    /** @var \App\Models\UserModel @inject */
    public $users;

    /** @var int */
    public $editId = 0;
    /** @var int */
    public $editStep = 0;

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

    public function actionEditBuild($id)
    {
        if (!$this->projects->getProjectById($id))
        {
            $this->flashMessage($this->translator->translate('main.project.form.no_such_project'), 'error');
            $this->redirect('Projects:');
            return;
        }

        $this->editId = $id;
        $this->template->buildSteps = $this->buildSteps->getStepsForProject($id);
        $this->template->editStep = $this->editStep;
        $this->template->maxStep = $this->buildSteps->getStepsForProject($this->editId)->max('step');

        $this->template->getStepTypeName = function($type) {
            $key = 'main.buildsteps.step.'.$type;
            return $this->translator->translate($key);
        };
    }
    
    public function redrawBuildSteps()
    {
        $this->template->buildSteps = $this->buildSteps->getStepsForProject($this->editId);
        $this->template->maxStep = $this->buildSteps->getStepsForProject($this->editId)->max('step');
        $this->template->editStep = $this->editStep;
        $this->redrawControl('buildSteps');
    }
    
    public function handleAddBuildStep()
    {
        $this->editStep = $this->buildSteps->addBuildStep($this->editId, \App\Models\BuildStepType::DUMMY, null, null, null);
        $this->redrawBuildSteps();
    }
    
    public function handleEditStep($step)
    {
        $this->editStep = $step;
        $this->redrawBuildSteps();
    }

    public function handleDelete($project_id)
    {
        $this->projects->deleteProject($project_id);
        $this->redrawControl('projectList');
    }
    
    public function handleBuildStepDeleteRequest($step)
    {
        $this->template->stepToDelete = $step;
        $this->redrawControl('buildStepDeleteModalBody');
    }
    
    public function handleDeleteBuildStep($step)
    {
        $this->buildSteps->deleteBuildStep($this->editId, $step);
        $this->redrawBuildSteps();
    }
    
    public function handleStepMoveUp($step)
    {
        $this->buildSteps->moveStepUp($this->editId, $step);
        $this->redrawBuildSteps();
    }
    
    public function handleStepMoveDown($step)
    {
        $this->buildSteps->moveStepDown($this->editId, $step);
        $this->redrawBuildSteps();
    }

    public function createComponentProjectForm($name)
    {
        return new \App\Components\ProjectForm($this->projects, $this->translator, $this->editId);
    }

    public function createComponentProjectList($name)
    {
        return new \App\Components\ProjectList($this->projects, $this->translator);
    }
    
    public function createComponentBuildStepForm($name)
    {
        return new \App\Components\BuildStepForm($this->projects, $this->buildSteps, $this->credentials, $this->users, $this->translator, $this->editId, $this->editStep);
    }
}
