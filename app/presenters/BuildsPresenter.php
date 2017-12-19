<?php

namespace App\Presenters;

class BuildsPresenter extends SecuredPresenter
{
    /** @var \App\Models\BuildModel @inject */
    public $builds;

    /** @var int */
    protected $buildId;

    public function actionView($id)
    {
        $this->buildId = $id;

        if (!$this->builds->getBuildById($this->buildId))
        {
            // TODO: error message
            $this->redirect('Projects:');
            return;
        }

        $this->refreshLog();

        \Helpers::$translator = $this->translator;
        $this->template->deployStatusFunc = array('\Helpers', 'getBuildIcon');
        $this->template->deployStatusText = array('\Helpers', 'getBuildTitle');
    }

    public function handleRefreshLog()
    {
        $this->refreshLog();
        $this->redrawControl('buildLog');
        $this->payload->build_ended = ($this->builds->getBuildById($this->buildId)->status != \App\Models\BuildStatus::RUNNING);
    }

    protected function refreshLog()
    {
        $this->template->build = $this->builds->getBuildById($this->buildId);
    }
}
