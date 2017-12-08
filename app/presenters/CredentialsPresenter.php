<?php

namespace App\Presenters;

use Nette\Application\UI\Form;

class CredentialsPresenter extends SecuredPresenter
{
    /** @var \App\Models\CredentialModel @inject */
    public $credentials;

    /** @var \Nette\Database\ActiveRow */
    private $editCredential = null;

    public function actionDefault()
    {
        $this->template->credentials = $this->credentials->getAllCredentials();
    }

    public function handleDelete($credential_id)
    {
        $this->credentials->deleteCredential($credential_id);
        $this->redrawControl('credentialList');
    }

    public function handleCredentialFormEditAction($credential_id)
    {
        $this->editCredential = strlen($credential_id) > 0 ? $this->credentials->getCredentialByIdentifier($credential_id) : null;
        $this->redrawControl('editCredentialForm');
    }

    public function createComponentCredentialForm()
    {
        $form = new Form();

        $form->addHidden('edit_id', $this->editCredential ? $this->editCredential->identifier : null);

        $form->addText('identifier', $this->translator->translate('main.credential.form.identifier'))
             ->setRequired($this->translator->translate('main.credential.form.identifier_must_be'))
             ->setDefaultValue($this->editCredential ? $this->editCredential->identifier : null);

        $form->addSelect('type', $this->translator->translate('main.credential.form.type'),
            \App\Models\CredentialType::getValueArray())
             ->setDefaultValue($this->editCredential ? $this->editCredential->type : null);

        $form->addText('username', $this->translator->translate('main.credential.form.username'))
             ->setDefaultValue($this->editCredential ? $this->editCredential->username : null);
        $form->addText('auth_ref', $this->translator->translate('main.credential.form.auth_ref'))
             ->setRequired($this->translator->translate('main.credential.form.auth_ref_must_be'))
             ->setDefaultValue($this->editCredential ? $this->editCredential->auth_ref : null);

        $form->addSubmit('submit', $this->translator->translate('main.credential.form.submit'));

        $form->onSuccess[] = [$this, 'credentialFormSuccess'];

        return $form;
    }

    public function credentialFormSuccess(Form $form)
    {
        $vals = $form->values;

        $existing = $this->credentials->getCredentialByIdentifier($vals->identifier);
        if ($existing && $existing->identifier != $vals->edit_id)
        {
            $form['identifier']->addError($this->translator->translate('main.credential.form.identifier_already_exists'));
            $this->payload->success_flag = 0;
            return;
        }

        if ($vals->edit_id)
            $this->credentials->editCredential($vals->edit_id, $vals->identifier, $vals->type, $vals->username, $vals->auth_ref);
        else
            $this->credentials->addCredential($vals->identifier, $vals->type, $vals->username, $vals->auth_ref);

        $this->payload->success_flag = 1;
        $this->redrawControl('credentialList');
    }

    public function handleCredentialDeleteRequest($identifier)
    {
        $this->template->credIdentToDelete = $identifier;
        $this->redrawControl('credentialDeleteModalBody');
    }
}
