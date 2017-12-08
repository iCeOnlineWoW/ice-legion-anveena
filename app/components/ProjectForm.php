<?php

namespace App\Components;

use Nette,
    Nette\Application\UI\Form;

/**
 * Project create and edit form component
 */
class ProjectForm extends Nette\Application\UI\Control
{
    /** @var \App\Models\ProjectModel */
    private $projects;

    /** @var \Nette\Localization\ITranslator */
    private $translator;

    /** @var \Nette\Database\Table\ActiveRow */
    private $editProject;

    public function __construct(\App\Models\ProjectModel $projects, Nette\Localization\ITranslator $translator, $editId = 0)
    {
        parent::__construct();

        $this->projects = $projects;
        $this->translator = $translator;

        $this->editProject = $editId ? $this->projects->getProjectById($editId) : null;
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/ProjectForm.latte');
        $this->template->render();
    }

    public function createComponentProjectForm()
    {
        $form = new Form();

        $form->addHidden('edit_id', $this->editProject ? $this->editProject->id : 0);

        $form->addText('name', $this->translator->translate('main.project.form.name'))
             ->setRequired($this->translator->translate('main.project.form.name_must_be'))
             ->setDefaultValue($this->editProject ? $this->editProject->name : null);

        $form->addTextArea('description', $this->translator->translate('main.project.form.description'))
             ->setDefaultValue($this->editProject ? $this->editProject->description : null);

        $form->addSelect('repository_type', $this->translator->translate('main.project.form.repository_type'),
            \App\Models\RepositoryType::getValueArray())
             ->setDefaultValue($this->editProject ? $this->editProject->repository_type : null);

        $form->addText('repository_url', $this->translator->translate('main.project.form.repository_url'))
             ->setRequired($this->translator->translate('main.project.form.repository_url_must_be'))
             ->setDefaultValue($this->editProject ? $this->editProject->repository_url : null);
        $form->addText('repository_branch', $this->translator->translate('main.project.form.repository_branch'))
             ->setDefaultValue($this->editProject ? $this->editProject->repository_branch : 'master');

        $form->addSubmit('submit', $this->translator->translate('main.project.form.submit'));

        $form->onSuccess[] = [$this, 'projectFormSuccess'];

        return $form;
    }

    public function projectFormSuccess(Form $form)
    {
        $vals = $form->values;

        $existing = $this->projects->getProjectByName($vals->name);
        if ($existing && $existing->id != $vals->edit_id)
        {
            $form['name']->addError($this->translator->translate('main.project.form.name_already_exists'));
            $this->redrawControl('projectForm');
            return;
        }

        if ($vals->edit_id)
        {
            $this->projects->editProject($vals->edit_id,
                    $vals->name, $vals->description,
                    $vals->repository_type,
                    $vals->repository_url,
                    $vals->repository_branch);
        }
        else
        {
            $this->projects->addProject($vals->name, $vals->description,
                    $vals->repository_type,
                    $vals->repository_url,
                    $vals->repository_branch);
        }

        $this->presenter->redirect('Projects:');
    }
}
