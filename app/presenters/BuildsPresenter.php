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

                $this->template->deployStatusFunc = function($proj) {
            if (!$proj || $proj->last_build_status == \App\Models\BuildStatus::NONE)
                return 'none';

            switch ($proj->last_build_status)
            {
                case \App\Models\BuildStatus::RUNNING:
                    return 'running';
                case \App\Models\BuildStatus::SUCCESS:
                    return 'success';
                case \App\Models\BuildStatus::WARNING:
                    return 'warning';
                case \App\Models\BuildStatus::FAIL:
                    return 'fail';
            }

            return 'none';
        };
        $this->template->deployStatusText = function($proj) {
            if (!$proj || $proj->last_build_status == \App\Models\BuildStatus::NONE)
                return $this->translator->translate('main.project.list.notbuilt');

            switch ($proj->last_build_status)
            {
                case \App\Models\BuildStatus::RUNNING:
                    return $this->translator->translate('main.project.list.build_progress');
                case \App\Models\BuildStatus::SUCCESS:
                    return $this->translator->translate('main.project.list.build_success');
                case \App\Models\BuildStatus::WARNING:
                    return $this->translator->translate('main.project.list.build_warning');
                case \App\Models\BuildStatus::FAIL:
                    return $this->translator->translate('main.project.list.build_fail');
            }

            return $this->translator->translate('main.project.list.notbuilt');
        };
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
