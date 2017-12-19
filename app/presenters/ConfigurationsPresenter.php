<?php

namespace App\Presenters;

use Nette\Application\UI\Form;

class ConfigurationsPresenter extends SecuredPresenter
{
    /** @var \App\Models\ConfigurationModel @inject */
    public $configurations;

    /** @var \Nette\Database\ActiveRow */
    private $editConfiguration = null;

    public function actionDefault()
    {
        $this->template->configurations = $this->configurations->getAllConfigurations();
    }

    public function handleDelete($configuration_id)
    {
        $this->configurations->deleteConfiguration($configuration_id);
        $this->redrawControl('configurationList');
    }

    public function handleConfigurationFormEditAction($configuration_id)
    {
        $this->editConfiguration = strlen($configuration_id) > 0 ? $this->configurations->getConfigurationByIdentifier($configuration_id) : null;
        $this->redrawControl('editConfigurationForm');
    }

    public function createComponentConfigurationForm()
    {
        $form = new Form();

        $form->addHidden('edit_id', $this->editConfiguration ? $this->editConfiguration->identifier : null);

        $form->addText('identifier', $this->translator->translate('main.configuration.form.identifier'))
             ->setRequired($this->translator->translate('main.configuration.form.identifier_must_be'))
             ->setDefaultValue($this->editConfiguration ? $this->editConfiguration->identifier : null);

        $form->addTextArea('configuration', $this->translator->translate('main.configuration.form.configuration'))
             ->setDefaultValue($this->editConfiguration ? $this->editConfiguration->configuration : null);

        $form->addSubmit('submit', $this->translator->translate('main.configuration.form.submit'));

        $form->onSuccess[] = [$this, 'configurationFormSuccess'];

        return $form;
    }

    public function configurationFormSuccess(Form $form)
    {
        $vals = $form->values;

        $existing = $this->configurations->getConfigurationByIdentifier($vals->identifier);
        if ($existing && $existing->identifier != $vals->edit_id)
        {
            $form['identifier']->addError($this->translator->translate('main.configuration.form.identifier_already_exists'));
            $this->payload->success_flag = 0;
            return;
        }

        if ($vals->edit_id)
            $this->configurations->editConfiguration($vals->edit_id, $vals->identifier, $vals->configuration);
        else
            $this->configurations->addConfiguration($vals->identifier, $vals->configuration);

        $this->payload->success_flag = 1;
        $this->redrawControl('configurationList');
    }

    public function handleConfigurationDeleteRequest($identifier)
    {
        $this->template->confIdentToDelete = $identifier;
        $this->redrawControl('configurationDeleteModalBody');
    }
}
