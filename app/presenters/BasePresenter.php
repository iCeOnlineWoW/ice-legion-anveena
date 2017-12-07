<?php

namespace App\Presenters;

use Nette;

abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    /** @var string */
    public $locale;

    /** @var \Kdyby\Translation\Translator @inject */
    public $translator;

    public function startup()
    {
        parent::startup();

        // retrieve locale from session, if available; fallback to english
        $this->locale = 'en';
        if (isset($this->getSession()->getSection('lang')->locale))
            $this->locale = $this->getSession()->getSection('lang')->locale;
        $this->translator->setLocale($this->locale);

        $this->template->loggedIn = $this->getUser()->isLoggedIn();
        $this->template->user = $this->getUser();
        $this->template->userIdentity = $this->getUser()->getIdentity();
    }

    /**
     * Signal handler for change language
     * @param string $lang
     */
    public function handleChangeLang($lang)
    {
        $this->locale = $lang;
        $this->getSession()->getSection('lang')->locale = $lang;
        $this->redirect('this');
    }

    /**
     * Signal handler for logout
     */
    public function handleLogout()
    {
        if ($this->getUser()->isLoggedIn())
            $this->getUser()->logout();
        $this->redirect('Sign:in');
    }
}

