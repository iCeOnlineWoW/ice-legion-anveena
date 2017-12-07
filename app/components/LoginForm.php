<?php

namespace App\Components;

use Nette,
    Nette\Application\UI\Form;

/**
 * Login form component
 */
class LoginForm extends Nette\Application\UI\Control
{
    /** @var \Nette\Security\User */
    private $userRef;

    /** @var \Nette\Localization\ITranslator */
    private $translator;

    public function __construct(Nette\Security\User $user, Nette\Localization\ITranslator $translator)
    {
        parent::__construct();

        $this->userRef = $user;
        $this->translator = $translator;
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/LoginForm.latte');
        $this->template->render();
    }

    public function createComponentLoginForm()
    {
        $form = new Form();

        $form->addText('username', $this->translator->translate('main.sign.in.username'))
             ->setRequired($this->translator->translate('main.sign.in.username_must_be'));

        $form->addPassword('password', $this->translator->translate('main.sign.in.password'))
             ->setRequired($this->translator->translate('main.sign.in.password_must_be'));

        $form->addSubmit('submit', $this->translator->translate('main.sign.in.submit'));

        $form->onSuccess[] = [$this, 'loginFormSuccess'];

        return $form;
    }

    public function loginFormSuccess(Form $form)
    {
        try
        {
            $this->userRef->login($form->values->username, $form->values->password);

            $this->presenter->redirect('Homepage:');
        }
        catch (\Nette\Security\AuthenticationException $ex)
        {
            $form->addError($this->translator->translate('main.sign.in.invalid_credentials'));
            $this->redrawControl('loginForm');
        }
    }
}
