<?php

namespace App\Presenters;

class SignPresenter extends BasePresenter
{
    public function createComponentLoginForm($name)
    {
        return new \App\Components\LoginForm($this->getUser(), $this->translator);
    }
}
