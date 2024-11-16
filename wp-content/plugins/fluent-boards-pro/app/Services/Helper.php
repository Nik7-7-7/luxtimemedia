<?php

namespace FluentBoardsPro\App\Services;

use FluentBoards\App\Models\Activity;
use FluentBoards\App\Models\Label;
use FluentBoards\App\Services\Constant;

class Helper
{
    public static function setCurrentUserPreferencesOnBoardCreate($board)
    {
        $board->users()->attach(
            get_current_user_id(),
            [
                'object_type' => Constant::OBJECT_TYPE_BOARD_USER,
                'settings' => maybe_serialize([
                    Constant::IS_BOARD_ADMIN => true
                ]),
                'preferences' => maybe_serialize(Constant::BOARD_NOTIFICATION_TYPES)
            ]
        );
    }

    public static function createDefaultLabels($boardId)
    {
        $bg_colors = ['#4bce97', '#f5cd47', '#fea362', '#f87168', '#9f8fef'];
        $text_colors = ['#1B2533', '#1B2533', '#1B2533', '#1B2533', '#1B2533'];

        $data = [];

        foreach ($text_colors as $index => $text_color)
        {
            $data[] = [
                'board_id' => $boardId,
                'type' => 'label',
                'bg_color' => $bg_colors[$index],
                'color' => $text_colors[$index],
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ];
        }

        Label::insert($data);
    }

    public static function defaultTaskCountAdd($board, $totalTasks)
    {
        $settings = $board->settings ?? [];

        if (isset($settings['tasks_count'])) {
            $settings['tasks_count'] += $totalTasks;
        } else {
            $settings['tasks_count'] = $totalTasks;
        }
        $board->settings = $settings;
        $board->save();
    }

    public static function createTrackingActivity($track, $type)
    {
        $data = [
            'object_type' => Constant::ACTIVITY_TASK,
            'object_id' => $track->task_id,
            'action' => $type == 'start' ? 'started' : 'committed',
            'column' => 'time_tracking',
            'old_value' => null,
            'new_value' => $track->billable_minutes,
            'description' => $type == 'start' ? 'time tracking started' : 'logged time ' . $track->billable_minutes . ' minutes',
            'settings' => null,
            'created_by' => $track->user_id,
        ];

        \FluentBoards\App\Services\Helper::createActivity($data);
    }
}

