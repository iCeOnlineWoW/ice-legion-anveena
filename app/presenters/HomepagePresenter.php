<?php

namespace App\Presenters;

class HomepagePresenter extends SecuredPresenter
{
    /** @var \App\Models\BuildModel @inject */
    public $builds;
    /** @var \App\Models\WorkerModel @inject */
    public $workers;

    public function actionDefault()
    {
        $this->refreshLists();
    }

    public function handleRefreshLists()
    {
        $this->refreshLists();
        $this->redrawControl('buildHistory');
        $this->redrawControl('workerList');
        $this->redrawControl('lastUpdateTime');
    }

    protected function refreshLists()
    {
        $this->template->buildHistory = $this->builds->getNewestBuilds(5);

        \Helpers::$translator = $this->translator;
        $this->template->deployStatusFunc = array('\Helpers', 'getBuildIcon');
        $this->template->deployStatusText = array('\Helpers', 'getBuildTitle');

        $this->template->workers = $this->workers->getAllWorkers();

        $this->template->workerStatusText = array('\Helpers', 'getWorkerStatusText');

        $this->template->lastUpdate = date('j. n. Y, H:i:s');
    }
}
