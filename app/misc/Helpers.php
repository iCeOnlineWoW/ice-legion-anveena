<?php

class Helpers
{
    public static $translator = null;

    public static function getBuildIcon($status)
    {
        if (!$status || $status == \App\Models\BuildStatus::NONE)
            return 'none';

        switch ($status)
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
    }
    
    public static function getBuildTitle($status)
    {
        if (!$status || $status == \App\Models\BuildStatus::NONE)
            return self::$translator->translate('main.project.list.notbuilt');

        switch ($status)
        {
            case \App\Models\BuildStatus::RUNNING:
                return self::$translator->translate('main.project.list.build_progress');
            case \App\Models\BuildStatus::SUCCESS:
                return self::$translator->translate('main.project.list.build_success');
            case \App\Models\BuildStatus::WARNING:
                return self::$translator->translate('main.project.list.build_warning');
            case \App\Models\BuildStatus::FAIL:
                return self::$translator->translate('main.project.list.build_fail');
        }

        return self::$translator->translate('main.project.list.notbuilt');
    }

    public static function getWorkerStatusText($status)
    {
        if (!$status)
            return self::$translator->translate('main.workers.status.none');

        switch ($status)
        {
            case \App\Models\WorkerStatus::IDLE:
                return self::$translator->translate('main.workers.status.idle');
            case \App\Models\WorkerStatus::WORKING:
                return self::$translator->translate('main.workers.status.working');
        }

        return self::$translator->translate('main.workers.status.none');
    }
}
