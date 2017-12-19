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
    /** @var \App\Models\ConfigurationModel */
    private $configurations;

    /** @var \Nette\Localization\ITranslator */
    private $translator;

    /** @var \Nette\Database\Table\ActiveRow */
    private $editStep;

    /** @var array */
    private static $additionalFields = array(
        'source_file',
        'target_file',
        'ftp_host',
        'ftp_directory',
        'consider_successful'
    );

    /** @var array */
    private static $additionalFieldTypes = array(
        'consider_successful' => 'checkbox'
    );

    /** @var array */
    private static $fieldsForType = array(
        \App\Models\BuildStepType::DUMMY => array(),
        \App\Models\BuildStepType::CLONE_REPOSITORY => array('ref_credentials_identifier'),
        \App\Models\BuildStepType::COMPOSER => array(),
        \App\Models\BuildStepType::UPLOAD_FTP => array('ref_credentials_identifier', 'ftp_host', 'ftp_directory'),
        \App\Models\BuildStepType::PREPARE_CONFIG => array('ref_configurations_identifier', 'source_file', 'target_file'),
        \App\Models\BuildStepType::NOTIFY_BUILD_STATUS => array('ref_users_id', 'consider_successful')
    );

    public function __construct(\App\Models\ProjectModel $projects, \App\Models\BuildStepModel $buildSteps,
            \App\Models\CredentialModel $credentials, \App\Models\UserModel $users, \App\Models\ConfigurationModel $configurations,
            Nette\Localization\ITranslator $translator, $projectsId, $step)
    {
        parent::__construct();

        $this->projects = $projects;
        $this->buildSteps = $buildSteps;
        $this->credentials = $credentials;
        $this->users = $users;
        $this->configurations = $configurations;
        $this->translator = $translator;

        $this->editStep = $this->buildSteps->getBuildStep($projectsId, $step);
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/BuildStepForm.latte');
        $this->template->additionalFields = self::$additionalFields;
        $this->template->fieldsForType = self::$fieldsForType;
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
        $form->addSelect('ref_configurations_identifier', $this->translator->translate('main.buildsteps.form.configuration_ref'), $this->configurations->getConfigurationsMap())
             ->setDefaultValue($this->editStep ? $this->editStep->ref_configurations_identifier : null)
             ->setPrompt($this->translator->translate('main.buildsteps.form.configurations_prompt'));

        $additional = $this->editStep ? json_decode($this->editStep->additional_params) : array();

        foreach (self::$additionalFields as $field)
        {
            $type = 'text';
            if (isset(self::$additionalFieldTypes[$field]))
                $type = self::$additionalFieldTypes[$field];
            
            $fieldDescription = $this->translator->translate('main.buildsteps.form.additional_'.$field);
            $fieldValue = isset($additional->{$field}) ? $additional->{$field} : null;
                
            switch ($type)
            {
                case 'text':
                    $form->addText($field, $fieldDescription)->setDefaultValue($fieldValue);
                    break;
                case 'checkbox':
                    $form->addCheckbox($field, $fieldDescription)->setDefaultValue($fieldValue);
                    break;
            }
        }

        $form->addSubmit('submit', $this->translator->translate('main.buildsteps.form.submit'));

        $form->onSuccess[] = [$this, 'buildStepFormSuccess'];

        return $form;
    }

    public function buildStepFormSuccess(Form $form)
    {
        $vals = $form->values;

        $additional = array();
        foreach (self::$additionalFields as $field)
        {
            if (isset($vals->{$field}) && $vals->{$field} && strlen($vals->{$field}) > 0)
                $additional[$field] = $vals->{$field};
        }

        // this assumes, that validation was made by Nette forms core
        $this->buildSteps->editBuildStep($vals->projects_id, $vals->step, $vals->type, $vals->ref_credentials_identifier,
                $vals->ref_projects_id, $vals->ref_users_id, $vals->ref_configurations_identifier, $additional);
        
        $this->presenter->redrawBuildSteps();
    }
}
