<?php

namespace App\Components;

use Nette;

/**
 * Project list component
 */
class ProjectList extends Nette\Application\UI\Control
{
    /** @var \App\Models\ProjectModel */
    private $projects;

    /** @var \Nette\Localization\ITranslator */
    private $translator;

    public function __construct(\App\Models\ProjectModel $projects, Nette\Localization\ITranslator $translator)
    {
        parent::__construct();

        $this->projects = $projects;
        $this->translator = $translator;
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/ProjectList.latte');
        $this->template->projects = $this->projects->getAllProjects();
        $this->template->deployStatusFunc = function($proj) {
            if (!$proj || $proj->last_build_status == \App\Models\BuildStatus::NONE)
                return 'none';

            switch ($proj->last_build_status)
            {
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
                case \App\Models\BuildStatus::SUCCESS:
                    return $this->translator->translate('main.project.list.build_success');
                case \App\Models\BuildStatus::WARNING:
                    return $this->translator->translate('main.project.list.build_warning');
                case \App\Models\BuildStatus::FAIL:
                    return $this->translator->translate('main.project.list.build_fail');
            }

            return $this->translator->translate('main.project.list.notbuilt');
        };
        $this->template->render();
    }
}
