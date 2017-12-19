<?php

namespace App\Presenters;

class SecuredPresenter extends BasePresenter
{
    /** @var \App\Models\WorkerModel @inject */
    public $workers;
    /** @var \App\Models\BuildModel @inject */
    public $builds;
    
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
        $build = $this->builds->addBuildRecord($projectid);
        $this->workers->selectAndRunBuild($build->id);
    }
}
