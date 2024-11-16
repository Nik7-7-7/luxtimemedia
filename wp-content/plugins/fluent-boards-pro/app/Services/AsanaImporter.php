<?php

namespace FluentBoardsPro\App\Services;

use FluentBoards\App\Models\Board;
use FluentBoards\App\Services\Constant;
use FluentBoards\Framework\Support\Str;

class AsanaImporter
{
    private static $alreadyCreatedStages = [];
    private static $stageMapper = [];
    private static $boardMapper = null;

    private static $stagePosition = 1;
    public static function process($data)
    {
        // Create board
        $board = self::createBoard($data);

        foreach ($data['data'] as $asanaTask)
        {
            $taskStage = self::getStageIdOfAsanaTask($asanaTask);
            if(!in_array($taskStage['gid'], self::$alreadyCreatedStages))
            {
                $stageName = $taskStage['name'];
                $newStage = self::createStage($board, $stageName);
                self::$stageMapper[trim($taskStage['gid'])] = $newStage->id;
                self::$alreadyCreatedStages[] = trim($taskStage['gid']);
            }

            $taskData = self::prepareTaskData($asanaTask, null, $taskStage);
            $task = $board->tasks()->create($taskData);

            if(!empty($asanaTask['subtasks']))
            {
                self::loadSubtasks($task, $asanaTask['subtasks'], $board);
            }
        }

        Helper::defaultTaskCountAdd($board, count($data['data']));

        return $board;
        
    }

    private static function loadSubtasks($task, $subtasks, $board)
    {
        foreach ($subtasks as $subtask)
        {
            $taskData = self::prepareTaskData($subtask, $task, null);
            $subtask = $board->tasks()->create($taskData);
            if($subtask){
                $parentTaskSettings = $task->settings;
                $parentTaskSettings['subtask_count'] = $parentTaskSettings['subtask_count'] + 1;
                $task->settings = $parentTaskSettings;
                $task->save();
            }
        }
    }

    private static function getStageIdOfAsanaTask($asanaTask)
    {
        foreach ($asanaTask['memberships'] as $index => $membership){
            if($membership['project']['gid'] == self::$boardMapper || $index == count($asanaTask['memberships']) - 1){
                return $membership['section'];
            }
        }
    }

    private static function createBoard($asanaTasks)
    {
        $boardName = null;
        $boardId = null;

        foreach ($asanaTasks['data'] as $index => $task)
        {
            //if a task has one board data that will be the board we imported
            //or if there is no task with one board then taking first board of last task
            //asana json data pattern is like that
            if(count($task['projects']) == 1 || $index == count($asanaTasks['data']) - 1){
                $boardName = $task['projects'][0]['name'];
                $boardId = $task['projects'][0]['gid'];
                break;
            }
        }

        $boardData = [
            'title' => $boardName,
            'slug' => Str::slug($boardName),
            'user_id' => get_current_user_id()
        ];

        $board = Board::create($boardData);

        if($board){
            //set user's default preferences
            Helper::setCurrentUserPreferencesOnBoardCreate($board);

            //set default Labels of Board
            Helper::createDefaultLabels($board->id);

            do_action('fluent_boards/board_created', $board);
        }

        self::$boardMapper = $boardId;

        return $board;
    }

    private static function createStage($board, $taskStageName)
    {
        $newStage = $board->stages()->create([
            'title' => $taskStageName,
            'slug' => str::slug($taskStageName),
            'type' => 'stage',
            'position' => self::$stagePosition++
        ]);
        return $newStage;
    }

    private static function prepareTaskData($task, $parentTask = null, $taskStage = null)
    {
        $taskData = [
            'title' => $task['name'],
            'parent_id' => $parentTask ? $parentTask->id : null,
            'source' => 'asana',
            'status' => $task['completed'] ? 'closed' : 'open',
            'last_completed_at' => $task['completed_at'] ? gmdate('Y-m-d H:i:s', strtotime($task['completed_at'])) : null,
            'due_at' => $task['due_on'] ? gmdate('Y-m-d H:i:s', strtotime($task['due_on'])) : null,
            'started_at' => $task['start_on'] ? gmdate('Y-m-d H:i:s', strtotime($task['start_on'])) : null,
            'archived_at' => null,
            'stage_id' => $taskStage ? self::$stageMapper[trim($taskStage['gid'])] : null
        ];

        return $taskData;
    }

}