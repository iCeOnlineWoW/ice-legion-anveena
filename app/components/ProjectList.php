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
        \Helpers::$translator = $this->translator;
        $this->template->deployStatusFunc = array('\Helpers', 'getBuildIcon');
        $this->template->deployStatusText = array('\Helpers', 'getBuildTitle');
        $this->template->render();
    }
}
