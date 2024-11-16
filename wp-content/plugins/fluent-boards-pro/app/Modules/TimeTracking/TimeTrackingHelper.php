<?php

namespace FluentBoardsPro\App\Modules\TimeTracking;

use FluentBoards\App\Models\TaskMeta;
use FluentBoardsPro\App\Services\ProHelper;

class TimeTrackingHelper
{

    public static function isTimeTrackingEnabled()
    {
        static $enabled = null;

        if ($enabled !== null) {
            return $enabled;
        }

        $settings = ProHelper::getModuleSettings();

        $enabled = $settings['timeTracking']['enabled'] === 'yes';

        return $enabled;
    }

    public static function getTaskEstimation($taskId)
    {
        $exist = TaskMeta::where('task_id', $taskId)->where('key', '_estimated_minutes')->first();

        if ($exist) {
            return (int)$exist->value;
        }

        return 0;
    }

}
