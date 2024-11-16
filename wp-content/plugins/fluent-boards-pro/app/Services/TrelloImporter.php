<?php

namespace FluentBoardsPro\App\Services;

use FluentBoards\App\Models\Activity;
use FluentBoards\App\Models\Board;
use FluentBoards\App\Models\Relation;
use FluentBoards\App\Models\Task;
use FluentBoards\Framework\Support\Arr;
use FluentBoards\Framework\Support\Str;

class TrelloImporter
{
    public static function process($data)
    {
        // Increase max execution time to 5 minutes if less than 5 minutes
        if (ini_get('max_execution_time') < 300) {
            ini_set('max_execution_time', '300');
        }

        $boardName = $data['name'];

        $stages = Arr::get($data, 'lists', []);
        $labels = Arr::get($data, 'labels', []);
        $cards = Arr::get($data, 'cards', []);
        $subtaskGroups = Arr::get($data, 'checklists', []);
        $prefs = Arr::get($data, 'prefs', []);

        $parseDown = new Parsedown(); // Markdown to HTML

        // Create board
        $boardData = [
            'title' => $boardName,
            'slug' => Str::slug($boardName),
            'description' => strip_tags($parseDown->text($data['desc'])),
            'archived_at' => $data['closed'] ? gmdate('Y-m-d H:i:s',  strtotime($data['dateClosed'])) : null,
            'user_id' => get_current_user_id()
        ];
        if ($prefs['backgroundImage']){
            // if background image is set then set random color ( automatically handled by fluent boards)
        } else {
            $boardData['background']['image'] = null;
            $boardData['background']['is_image'] = false;
            $boardData['background']['color'] = $prefs['backgroundColor'];
        }
        $board = Board::create($boardData);

        //Create stages
        $listStageMapper = self::stageCreateAgainstList($board, $stages);

        // Create labels
        $labelMapper = self::labelCreate($board, $labels);
        // Create cards
        foreach ($cards as $card) {
            $taskData = [
                'title' => $card['name'],
                'source' => 'trello',
                'description' => $parseDown->text($card['desc']),
                'position' => $card['pos'],
                'status' => $card['dueComplete'] ? 'closed' : 'open',
                // here time is like 2024-04-30T14:11:00.000Z
                // we need to convert to user timezone

                'due_at' => $card['due'] ? gmdate('Y-m-d H:i:s', strtotime($card['due'])) : null,
                'started_at' => $card['start'],
                //                "dueReminder": 120, will be used in remind at - 120 minutes
                'archived_at' => $card['closed'] ? gmdate('Y-m-d H:i:s') : null,
                'stage_id' => $listStageMapper[trim($card['idList'])],
                'settings' => []
            ];

            if($card['cover']['color']){
                $taskData['settings']['cover'] = [
                    'backgroundColor' => Constant::TRELLO_COLOR_MAP[$card['cover']['color']],
                ];
            }

            $task = $board->tasks()->create($taskData);
            $relatedCheckLists = Arr::get($card, 'idChecklists', []);
            $task->updateMeta('is_template', $card['isTemplate'] ? 'yes' : 'no');
            // map labels
            self::associateLabelsWithTask($card['idLabels'], $task, $labelMapper);
            // Create subtasks
            self::addSubtasks($subtaskGroups, $card, $task, $relatedCheckLists);

        }

        self::updateBoardTaskCount($board->id);

        Activity::create([
            'created_by' => get_current_user_id(),
            'action' => 'created',
            'object_id' => $board->id,
            'object_type' => \FluentBoards\App\Services\Constant::ACTIVITY_BOARD,
            'old_value' => null,
            'new_value' => $boardName,
            'column' => 'board',
            'description' => 'imported from Trello <a href="' . $data['url'] . '" target="_blank">'.$data['url'].'</a>',
        ]);
        return $board;
    }


    private static function addSubtasks($subtaskGroups, $card, $task, $relatedCheckLists)
    {
        $subTaskCounter  = 0;
        foreach ($subtaskGroups as $subtaskGroup) {
            if ($subtaskGroup['idCard'] == $card['id']) {
                foreach ($subtaskGroup['checkItems'] as $subtask) {
                    if(in_array($subtask['idChecklist'], $relatedCheckLists)) {
                        Task::create([
                            'source' => 'trello',
                            'board_id' => $task->board_id,
                            'title' => $subtask['name'],
                            'status' => $subtask['state'] == 'complete' ? 'closed' : 'open',
                            'due_at' => $subtask['due'] ? gmdate('Y-m-d H:i:s', strtotime($subtask['due'])) : null,
                            'position' => $subtask['pos'],
                            'parent_id' => $task->id,
                        ]);
                        $subTaskCounter++;
                    }
                }
            }
        }
        $taskSettings = $task->settings ?? [];
        $taskSettings['subtask_count'] = $subTaskCounter;
        $task->settings = $taskSettings;
        $task->save();

    }

    private static function updateBoardTaskCount($boardId)
    {
        $board = Board::find($boardId);
        $settings = $board->settings ?? [];
        $settings['tasks_count'] = $board->tasks()->where('parent_id', null)->count();
        $board->settings = $settings;
        $board->save();
    }

    private static function associateLabelsWithTask($taskAssociatedLabels, $task, $labelMapper)
    {
        foreach ($taskAssociatedLabels as $label) {
            Relation::create([
                'object_id'   =>  $task->id,
                'object_type' => \FluentBoards\App\Services\Constant::OBJECT_TYPE_TASK_LABEL,
                'foreign_id'   =>  $labelMapper[$label]
            ]);
        }
    }

    private static function stageCreateAgainstList($board, $stages)
    {
        $listStageMapper = [];
        foreach ($stages as $stage) {
            $newStage = $board->stages()->create([
                'title' => $stage['name'],
                'slug' => str::slug($stage['name']),
                'type' => 'stage',
                'archived_at' => $stage['closed'] ? gmdate('Y-m-d H:i:s') : null,
                'position' => $stage['pos'],
            ]);
            $listStageMapper[trim($stage['id'])] = $newStage->id;
        }
        return $listStageMapper;
    }

    private static function labelCreate($board, $labels)
    {
        $labelMapper = [];
        foreach ($labels as $label) {
            $newLabel = $board->labels()->create([
                'title' => $label['name'],
                'slug' => $label['color'],
                'color' => Constant::TEXT_COLOR_MAP[$label['color']],
                'bg_color' => Constant::TRELLO_COLOR_MAP[$label['color']],
            ]);
            $labelMapper[trim($label['id'])] = $newLabel->id;
        }
        return $labelMapper;
    }

}