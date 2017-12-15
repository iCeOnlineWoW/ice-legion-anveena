<?php

namespace App\Components;

use Nette,
    Nette\Application\UI\Form;

/**
 * Build step edit form component
 */
class BuildStepForm extends Nette\Application\UI\Control
{
    /** @var \App\Models\ProjectModel */
    private $projects;
    /** @var \App\Models\BuildStepModel */
    private $buildSteps;
    /** @var \App\Models\CredentialModel */
    private $credentials;
    /** @var \App\Models\UserModel */
    private $users;

    /** @var \Nette\Localization\ITranslator */
    private $translator;

    /** @var \Nette\Database\Table\ActiveRow */
    private $editStep;

    public function __construct(\App\Models\ProjectModel $projects, \App\Models\BuildStepModel $buildSteps,
            \App\Models\CredentialModel $credentials, \App\Models\UserModel $users,
            Nette\Localization\ITranslator $translator, $projectsId, $step)
    {
        parent::__construct();

        $this->projects = $projects;
        $this->buildSteps = $buildSteps;
        $this->credentials = $credentials;
        $this->users = $users;
        $this->translator = $translator;

        $this->editStep = $this->buildSteps->getBuildStep($projectsId, $step);
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/BuildStepForm.latte');
        $this->template->render();
    }

    public function createComponentBuildStepForm()
    {
        $form = new Form();

        $form->addHidden('projects_id', $this->editStep ? $this->editStep->projects_id : null);
        $form->addHidden('step', $this->editStep ? $this->editStep->step : null);
        
        $form->addSelect('type', $this->translator->translate('main.buildsteps.form.type'), \App\Models\BuildStepType::getValueArray())
             ->setDefaultValue($this->editStep ? $this->editStep->type : null);
        $form->addSelect('ref_credentials_identifier', $this->translator->translate('main.buildsteps.form.credentials'), $this->credentials->getCredentialMap())
             ->setDefaultValue($this->editStep ? $this->editStep->ref_credentials_identifier : null)
             ->setPrompt($this->translator->translate('main.buildsteps.form.credentials_prompt'));
        $form->addSelect('ref_projects_id', $this->translator->translate('main.buildsteps.form.project_ref'), $this->projects->getProjectMap())
             ->setDefaultValue($this->editStep ? $this->editStep->ref_projects_id : null)
             ->setPrompt($this->translator->translate('main.buildsteps.form.projects_prompt'));
        $form->addSelect('ref_users_id', $this->translator->translate('main.buildsteps.form.user_ref'), $this->users->getUserMap())
             ->setDefaultValue($this->editStep ? $this->editStep->ref_users_id : null)
             ->setPrompt($this->translator->translate('main.buildsteps.form.users_prompt'));

        $form->addSubmit('submit', $this->translator->translate('main.buildsteps.form.submit'));

        $form->onSuccess[] = [$this, 'buildStepFormSuccess'];

        return $form;
    }

    public function buildStepFormSuccess(Form $form)
    {
        $vals = $form->values;

        // this assumes, that validation was made by Nette forms core
        $this->buildSteps->editBuildStep($vals->projects_id, $vals->step, $vals->type, $vals->ref_credentials_identifier,
                $vals->ref_projects_id, $vals->ref_users_id);
        
        $this->presenter->redrawBuildSteps();
    }
}
