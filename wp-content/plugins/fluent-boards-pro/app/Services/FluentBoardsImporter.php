<?php

namespace FluentBoardsPro\App\Services;

use FluentBoards\App\Models\Activity;
use FluentBoards\App\Models\Board;
use FluentBoards\App\Models\Comment;
use FluentBoards\App\Models\Relation;
use FluentBoards\App\Models\Task;
use FluentBoards\App\Models\User;
use FluentBoards\Framework\Support\Arr;

/**
 * Class FluentBoardsImporter
 *
 * Handles the importing of FluentBoards data, including boards, stages, labels, tasks, subtasks, and comments.
 *
 * @package FluentBoardsPro\App\Services
 */
class FluentBoardsImporter
{
    private static $siteUrl;
    /**
     * Process the given data to import a board along with its related data (stages, labels, tasks, etc.).
     *
     * @param array $data The data to import.
     * @return Board The imported board.
     */
    public static function process($data)
    {
        // Increase max execution time to 5 minutes if less than 5 minutes
        if (ini_get('max_execution_time') < 300) {
            ini_set('max_execution_time', '300');
        }

        $boardData = self::sanitizeBoardData($data['board']);

        $stages = Arr::get($data['board'], 'stages', []);
        $labels = Arr::get($data['board'], 'labels', []);
        $tasks = Arr::get($data['board'], 'tasks', []);
        $users = Arr::get($data['board'], 'users', []);
        $customFields = Arr::get($data['board'], 'custom_fields', []);
        self::$siteUrl = $data['site_url'];

        // Create Board
        $board = Board::create($boardData);
        // Create stages
        $listStageMapper = self::stageCreate($board, $stages);

        //Create Members if import in same site
        if (self::$siteUrl == site_url('/'))
        {
            self::createMembers($board, $users);
        }

        // Create labels
        $labelMapper = self::labelCreate($board, $labels);

        $customFieldMapper = self::customFieldCreate($board, $customFields);

        self::createTasks($board, $listStageMapper, $labelMapper, $customFieldMapper, $tasks);

        self::createActivity($board);
        self::updateBoardTaskCount($board->id);

        return $board;
    }

    /**
     * Create stages for a given board.
     *
     * @param Board $board The board to create stages for.
     * @param array $stages The stages to create.
     * @return array A mapping of old stage IDs to new stage IDs.
     */
    private static function stageCreate($board, $stages)
    {
        $listStageMapper = [];
        foreach ($stages as $stage) {
            $stageData = self::sanitizeStageAndLabelData($stage);
            $newStage = $board->stages()->create($stageData);
            $listStageMapper[(int)$stage['id']] = $newStage->id;
        }
        return $listStageMapper;
    }

    private static function createMembers($board, $users)
    {
        if (!$board) {
            return false;
        }

        foreach ($users as $user) {
            $boardMember = User::where('user_email', $user['user_email'])->first();

            if(!$boardMember) {
                continue;
            }

            $board->users()->attach(
                $user['ID'],
                [
                    'object_type' => \FluentBoards\App\Services\Constant::OBJECT_TYPE_BOARD_USER,
                    'settings'    => $user['pivot']['settings'],
                    'preferences' => $user['pivot']['preferences']
                ]
            );
        }
    }

    /**
     * Create labels for a given board.
     *
     * @param Board $board The board to create labels for.
     * @param array $labels The labels to create.
     * @return array A mapping of old label IDs to new label IDs.
     */
    private static function labelCreate($board, $labels)
    {
        $labelMapper = [];
        foreach ($labels as $label) {
            $labelData = self::sanitizeStageAndLabelData($label);
            $newLabel = $board->labels()->create($labelData);
            $labelMapper[(string)$label['id']] = $newLabel->id;
        }
        return $labelMapper;
    }

    private static function customFieldCreate($board, $customFields)
    {
        $customFieldMapper = [];
        foreach ($customFields as $customField)
        {
            $customFieldData = self::sanitizeStageAndLabelData($customField);
            $newCustomField = $board->customFields()->create($customFieldData);
            $customFieldMapper[(string)$customField['id']] = $newCustomField->id;
        }

        return $customFieldMapper;
    }

    /**
     * Create tasks for a given board.
     *
     * @param Board $board The board to create tasks for.
     * @param array $listStageMapper A mapping of old stage IDs to new stage IDs.
     * @param array $labelMapper A mapping of old label IDs to new label IDs.
     * @param array $tasks The tasks to create.
     */
    private static function createTasks($board, $listStageMapper, $labelMapper, $customFieldMapper, $tasks)
    {
        foreach ($tasks as $task) {
            try {
                $task['stage_id'] = $listStageMapper[(int) $task['stage_id']] ??
                                    reset($listStageMapper); // if task is missing the stage id then it included in first stage
                $task['board_id'] = $board->id;
                $taskData         = self::sanitizeTaskData($task);
                $newTask = new Task();
                $newTask->title = $taskData['title'];
                $newTask->slug = $taskData['slug'];
                $newTask->parent_id = $taskData['parent_id'];
                $newTask->type = $taskData['type'];
                $newTask->status = $taskData['status'];
                $newTask->stage_id = $taskData['stage_id'];
                $newTask->source = $taskData['source'];
                $newTask->source_id = $taskData['source_id'];
                $newTask->priority = $taskData['priority'];
                $newTask->description = $taskData['description'];
                $newTask->lead_value = $taskData['lead_value'];
                $newTask->position = $taskData['position'];
                $newTask->issue_number = $taskData['issue_number'];
                $newTask->reminder_type = $taskData['reminder_type'];
                $newTask->remind_at = $taskData['remind_at'];
                $newTask->started_at = $taskData['started_at'];
                $newTask->due_at = $taskData['due_at'];
                $newTask->last_completed_at = $taskData['last_completed_at'];
                $newTask->archived_at = $taskData['archived_at'];
                $newTask->settings = $taskData['settings'];
                $newTask->board_id = $taskData['board_id'];
                $newTask->save();

                $templateMeta     = $task['meta']['is_template'] ?? 'no';
                $newTask->updateMeta('is_template', $templateMeta);
                // map labels
                self::associateLabelsWithTask($task['labels'], $newTask,
                    $labelMapper);

                //map customFields
                self::associateCustomFieldsWithTask($task['custom_fields'],
                    $newTask, $customFieldMapper);

                if (self::$siteUrl == site_url('/')) {
                    // attach assignees
                    self::attachAssigneeToTask($task['assignees'], $newTask);
                    //attach wathers
                    self::attachWatchersToTask($task['watchers'], $newTask);
                }

                // Create subtasks
                self::addSubtasks($task['subtasks'], $newTask, $board);
                // Create comments
                self::createComments($task['comments'], $newTask);
            } catch (\Exception $e) {
                error_log('Error while importing task: old '. $task['id'] . ' new: '.  $newTask->id. $e->getMessage());
                continue;
            }
        }
    }

    /**
     * Add subtasks to a given task.
     *
     * @param array $subtasks The subtasks to add.
     * @param Task $task The task to add subtasks to.
     * @param Board $board The board to associate the subtasks with.
     */
    private static function addSubtasks($subtasks, $task, $board)
    {
        $subTaskCounter = 0;
        foreach ($subtasks as $subtask) {
            $subtask['stage_id'] = $task->stage_id;
            $subtask['parent_id'] = $task->id;
            $subtask['board_id'] = $board->id;
            $subTaskData = self::sanitizeTaskData($subtask);
            $newSubtasks = $board->tasks()->create($subTaskData);
            self::attachAssigneeToTask($subtask['assignees'], $newSubtasks);
            $subTaskCounter++;
        }
        $taskSettings = $task->settings ?? [];
        $taskSettings['subtask_count'] = $subTaskCounter;
        $task->settings = $taskSettings;
        $task->save();
    }

    /**
     * Create comments and replies for a given task.
     *
     * @param array $commentsAndReply The comments and replies to create.
     * @param Task $task The task to associate the comments with.
     */
    private static function createComments($commentsAndReply, $task)
    {
        $comments = [];
        $replies = [];
        $commentMapper = [];
        $commentCounter = 0;

        foreach ($commentsAndReply as $data) {
            $commentCreator = User::where('user_email', $data['author_email'])->first();
            if (!$commentCreator) {
                continue;
            }
            $data['created_by'] = $commentCreator->ID;
            $data['task_id'] = $task->id;
            $data['board_id'] = $task->board_id;
            if ($data['type'] == 'reply') {
                $replies[] = $data;
            } else {
                $comments[] = $data;
            }
        }

        foreach ($comments as $comment) {
            $commentData = self::sanitizeCommentAndReply($comment);
            $newComment = Comment::create($commentData);
            $commentMapper[(int)$comment['id']] = $newComment->id;
            $commentCounter++;
        }
        foreach ($replies as $reply) {
            $replyCreator = User::where('user_email', $reply['author_email'])->first();
            if (!$replyCreator) {
                continue;
            }
            $reply['created_by'] = $replyCreator->ID;

            $replyData = self::sanitizeCommentAndReply($reply);
            if(!$commentMapper[(int)$reply['parent_id']]) {
                $reply['type'] = 'comment';
                Comment::create($replyData);
                $commentCounter++;
            } else {
                $replyData['parent_id'] = $commentMapper[(int)$reply['parent_id']];
                Comment::create($replyData);
            }
        }

        $task['comments_count'] = $commentCounter;
        $task->save();
    }

    /**
     * Create an activity log for a given board.
     *
     * @param Board $board The board to create an activity log for.
     */
    private static function createActivity($board)
    {
        Activity::create([
            'created_by' => get_current_user_id(),
            'action' => 'created',
            'object_id' => $board->id,
            'object_type' => \FluentBoards\App\Services\Constant::ACTIVITY_BOARD,
            'old_value' => null,
            'new_value' => $board->title,
            'column' => 'board',
            'description' => 'imported from FluentBoards',
        ]);
    }

    /**
     * Sanitize Board data
     *
     * @param array $data The board data to sanitize.
     * @return array The sanitized board data.
     */
    private static function sanitizeBoardData($data)
    {
        $boardSettings = [];
        $boardBackground = [];
        foreach ($data['settings'] as $key => $value) {
            $boardSettings[$key] = sanitize_text_field($value);
        }
        foreach ($data['background'] as $key => $value) {
            switch ($key) {
                case 'image_url':
                    $boardBackground['image_url'] = sanitize_url($value);
                    break;
                case 'id':
                    $boardBackground['id'] = sanitize_text_field($value);
                    break;
                case 'is_image':
                    $boardBackground['is_image'] = rest_sanitize_boolean($value);
                    break;
                case 'color':
                    $boardBackground['color'] = sanitize_text_field($value);
                    break;
                default:
                    break;
            }
        }

        return [
            'title' => sanitize_text_field($data['title']),
            'description' => wp_kses_post($data['description']),
            'type' => sanitize_text_field($data['type']),
            'parent_id' => intval($data['parent_id']),
            'currency' => sanitize_text_field($data['currency']),
            'background' => $boardBackground,
            'settings' => $boardSettings,
        ];
    }

    /**
     * Sanitize Stage and Label data
     *
     * @param array $data The stage or label data to sanitize.
     * @return array The sanitized data.
     */
    private static function sanitizeStageAndLabelData($data)
    {
        $settings = [];
        if (isset($data['settings'])) {
            foreach ($data['settings'] as $key => $value) {
                if ($key == 'is_template') {
                    $settings[$key] = rest_sanitize_boolean($value);
                } else {
                    $settings[$key] = $value ? sanitize_text_field($value) : null;
                }
            }
        }

        return [
            'title' => sanitize_text_field($data['title']),
            'slug' => sanitize_text_field($data['slug']),
            'type' => sanitize_text_field($data['type']),
            'position' => $data['position'] ? intval($data['position']) : null,
            'color' => $data['color'] ? sanitize_text_field($data['color']) : null,
            'bg_color' => $data['bg_color'] ? sanitize_text_field($data['bg_color']) : null,
            'archived_at' => $data['archived_at'] ? sanitize_text_field($data['archived_at']) : null,
            'settings' => $settings,
        ];
    }

    /**
     * Sanitize Task and Subtask data
     *
     * @param array $data The task or subtask data to sanitize.
     * @return array The sanitized data.
     */
    /**
     * Sanitize Task and Subtask data
     *
     * @param array $data The task or subtask data to sanitize.
     * @return array The sanitized data.
     */
    private static function sanitizeTaskData($data)
    {
        $settings = [];
        if (isset($data['settings'])) {
            foreach ($data['settings'] as $key => $value) {
                if ($key == 'cover') {
                    $cover = [];
                    foreach ($value as $coverKey => $coverValue) {
                        $cover[$coverKey] = $coverValue ? sanitize_text_field($coverValue) : null;
                    }
                    $settings[$key] = $cover;
                } else {
                    $settings[$key] = $value ? sanitize_text_field($value) : null;
                }
            }
        }


        return [
            'board_id' => intval($data['board_id']),
            'title' => sanitize_text_field($data['title']),
            'slug' => sanitize_text_field($data['slug']),
            'parent_id' => $data['parent_id'] ? intval($data['parent_id']) : null,
            'type' => $data['type'] ? sanitize_text_field($data['type']) : 'task',
            'status' => $data['status'] ? sanitize_text_field($data['status']) : 'open',
            'stage_id' => intval($data['stage_id']),
            'source' => sanitize_text_field($data['source']),
            'source_id' => $data['source_id'] ? intval($data['source_id']) : null,
            'priority' => $data['priority'] ? sanitize_text_field($data['priority']) : 'low',
            'description' => wp_kses_post($data['description']),
            'lead_value' => $data['lead_value'] ? doubleval($data['lead_value']) : null,
            'position' => intval($data['position']),
            'issue_number' => $data['issue_number'] ? intval($data['issue_number']) : null,
            'reminder_type' => $data['reminder_type'] ? sanitize_text_field($data['reminder_type']) : null,
            'remind_at' => $data['remind_at'] ? sanitize_text_field($data['remind_at']) : null,
            'started_at' => $data['started_at'] ? sanitize_text_field($data['started_at']) : null,
            'due_at' => $data['due_at'] ? sanitize_text_field($data['due_at']) : null,
            'last_completed_at' => $data['last_completed_at'] ? sanitize_text_field($data['last_completed_at']) : null,
            'archived_at' => $data['archived_at'] ? sanitize_text_field($data['archived_at']) : null,
            'settings' => $settings,
        ];
    }


    /**
     * Sanitize Comment and Reply data
     *
     * This method takes an array of comment or reply data and sanitizes each field to ensure it is safe for storage and display.
     *
     * @param array $data The comment or reply data to sanitize.
     * @return array The sanitized data.
     */
    private static function sanitizeCommentAndReply($data)
    {
        return [
            'task_id' => intval($data['task_id']),
            'board_id' => intval($data['board_id']),
            'description' => wp_kses_post($data['description']),
            'type' => sanitize_text_field($data['type']),
            'privacy' => sanitize_text_field($data['privacy']),
            'status' => sanitize_text_field($data['status']),
            'author_name' => sanitize_text_field($data['author_name']),
            'author_email' => sanitize_email($data['author_email']),
            'author_ip' => sanitize_text_field($data['author_ip']),
            'created_by' => intval($data['created_by']),
            'settings' => $data['settings'],
        ];
    }


    /**
     * Update the task count for a given board.
     *
     * @param int $boardId The ID of the board to update.
     */
    private static function updateBoardTaskCount($boardId)
    {
        $board = Board::find($boardId);
        $settings = $board->settings ?? [];
        $settings['tasks_count'] = $board->tasks()->where('parent_id', null)->count();
        $board->settings = $settings;
        $board->save();
    }

    /**
     * Associate labels with a given task.
     *
     * @param array $taskAssociatedLabels The labels to associate.
     * @param Task $task The task to associate labels with.
     * @param array $labelMapper A mapping of label IDs to new label IDs.
     */
    private static function associateLabelsWithTask($taskAssociatedLabels, $task, $labelMapper)
    {
        foreach ($taskAssociatedLabels as $label) {
            Relation::create([
                'object_id' => $task->id,
                'object_type' => \FluentBoards\App\Services\Constant::OBJECT_TYPE_TASK_LABEL,
                'foreign_id' => $labelMapper[(string)$label['id']]
            ]);
        }
    }

    private static function associateCustomFieldsWithTask($taskCustomFields, $task, $customFieldMapper)
    {
        foreach ($taskCustomFields as $customField) {
            Relation::create([
                'object_id' => $task->id,
                'object_type' => Constant::TASK_CUSTOM_FIELD,
                'foreign_id' => $customFieldMapper[(string)$customField['id']],
                'settings' => maybe_unserialize($customField['pivot']['settings'])
            ]);
        }
    }

    private static function attachAssigneeToTask($assignees, $task)
    {
        foreach ($assignees as $assignee)
        {
            $boardMember = User::where('user_email', $assignee['user_email'])->first();

            if(!$boardMember) {
                continue;
            }

            $task->assignees()->attach(
                [
                    $assignee['ID'] => [ 'object_type' => \FluentBoards\App\Services\Constant::OBJECT_TYPE_TASK_ASSIGNEE ]
                ]
            );
        }
    }

    private static function attachWatchersToTask($watchers, $task)
    {
        foreach ($watchers as $watcher)
        {
            $boardMember = User::where('user_email', $watcher['user_email'])->first();

            if(!$boardMember) {
                continue;
            }

            $task->watchers()->attach(
                [
                    $watcher['ID'] => [ 'object_type' => \FluentBoards\App\Services\Constant::OBJECT_TYPE_USER_TASK_WATCH ]
                ]
            );
        }
    }

}
