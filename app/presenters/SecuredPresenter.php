<?php

namespace App\Presenters;

class SecuredPresenter extends BasePresenter
{
    /** @var \App\Models\WorkerModel @inject */
    public $workers;
    
    public function startup()
    {
        parent::startup();

        if (!$this->getUser()->isLoggedIn())
        {
            $this->redirect('Sign:in');
        }
    }
    
    public function handleBuild($projectid)
    {
        $this->workers->selectAndRunBuild($projectid);
    }
}
