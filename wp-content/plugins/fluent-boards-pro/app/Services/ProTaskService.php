<?php

namespace FluentBoardsPro\app\Services;

use FluentBoards\App\Models\Board;
use FluentBoards\App\Models\Meta;
use FluentBoards\App\Models\NotificationUser;
use FluentBoards\App\Models\Task;
use FluentBoards\App\Models\TaskMeta;
use FluentBoards\App\Models\User;
use FluentBoards\App\Services\Constant;
use FluentBoards\App\Services\Helper;
use FluentBoards\App\Services\PermissionManager;
use FluentBoards\App\Services\TaskService;
use FluentBoardsPro\App\Models\TaskAttachment;
use FluentBoardsPro\App\Modules\TimeTracking\Model\TimeTrack;

class ProTaskService
{
    public function getDefaultBoardImages()
    {
        $url = Constant::BOARD_DEFAULT_IMAGE_URL;

        /**
         * Image URL names after the static URL
         * 'https://fluentboards.com/shared-files/5036/?image_1.jpg'
         * https://fluentboards.com/shared-files/ is the static part
         * $remoteImages will be ['5036/?image_1.jpg']
         */

        $remoteImages = [
            '5027/?image_1.jpg',
            '5029/?image_2.jpg',
            '5036/?image_3.jpg',
        ];
        $existingImages = Meta::where('object_type', Constant::BOARD_DEFAULT_IMAGE)->get();

        $data = [];

        foreach ($remoteImages as $index => $remoteImage) {
            $downloaded = false;

            foreach ($existingImages as $key => $image) {
                if ($image->key == $remoteImage) {
                    $downloaded = true;
                    $data[] = [
                        'id' => $image->object_id,
                        'downloadable' => false,
                        'value' => $image->value,
                    ];
                    // Remove the image from the collection
                    unset($existingImages[$key]);
                    break;
                }
            }

            if (!$downloaded) {
                $data[] = [
                    'id' => $remoteImage,
                    'downloadable' => true,
                    'value' => $url . $remoteImage,
                ];
            }
        }

        return $data;
    }

    public function downloadDefaultBoardImages($imageData)
    {
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $attachment_id = media_sideload_image(Constant::BOARD_DEFAULT_IMAGE_URL . $imageData['id'], 0, $imageData['id'], 'id');
        $media = wp_prepare_attachment_for_js($attachment_id);
        $downloadedImage = (new Meta());
        $downloadedImage['object_id'] = $attachment_id;
        $downloadedImage['object_type'] = Constant::BOARD_DEFAULT_IMAGE;
        $downloadedImage['key'] = $imageData['id'];
        $downloadedImage['value'] = $media['url'];
        $downloadedImage->save();
        return [
            'id' => $downloadedImage->object_id,
            'downloadable' => false,
            'value' => $downloadedImage->value,
        ];
    }
  
    public function getTemplateTasks()
    {
        $userId = get_current_user_id();
        $currentUser = User::find($userId);

        $relatedBoardsQuery = Board::query();

        if (!PermissionManager::isAdmin($userId)) {
            $relatedBoardsQuery->whereIn('id', $currentUser->whichBoards->pluck('id'));
        }

        $templateTaskIds = TaskMeta::where('key', 'is_template')->where('value', 'yes')->pluck('task_id');
        return Task::whereIn('id', $templateTaskIds)
            ->where('archived_at', null)
            ->whereIn('board_id', $relatedBoardsQuery->pluck('id'))
            ->with('assignees', 'labels')
            ->get();

    }

    public function createFromTemplate($taskId, $data)
    {
        $templateTask = Task::find($taskId);
        $taskService = new TaskService();

        $task = new Task();

        $task->fill($templateTask->toArray());

        $task['title'] = $data['title'];
        $task['board_id'] = $data['board_id'];
        $task['stage_id'] = $data['stage_id'];
        $task['created_by'] = get_current_user_id();
        $task['comments_count'] = 0;
        $task->moveToNewPosition(1);
        $task->save();


        if (isset($data['assignee']) && $data['assignee'] == 'true') {
            $templateTask->load('assignees');
            foreach ($templateTask->assignees as $assignee) {
                $taskService->updateAssignee($assignee->ID, $task);
            }
        }
        if (isset($data['label']) && $data['label'] == 'true') {
            $templateTask->load('labels');
            foreach ($templateTask->labels as $label) {
                $task->labels()->syncWithoutDetaching([$label->id => ['object_type' => Constant::OBJECT_TYPE_TASK_LABEL]]);
            }
        }
        if (isset($data['attachment']) && $data['attachment'] == 'true') {
            $templateTask->load('attachments');
            foreach ($templateTask->attachments as $attachment) {
                $newAttachment = new TaskAttachment();
                $newAttachment->object_id = $task->id;
                $newAttachment->object_type = $attachment->object_type;
                $newAttachment->attachment_type = $attachment->attachment_type;
                $newAttachment->title = $attachment->title;
                $newAttachment->file_path = $attachment->file_path;
                $newAttachment->full_url = $attachment->full_url;
                $newAttachment->file_size = $attachment->file_size;
                $newAttachment->settings = $attachment->settings;
                $newAttachment->driver = $attachment->driver;
                $newAttachment->save();
            }
        }
        if (isset($data['subtask']) && $data['subtask'] == 'true') {
            $subtasks = Task::where('parent_id', $taskId)->get();
            foreach ($subtasks as $subtask) {
                $newSubtask = new Task();
                $newSubtask->fill($subtask->toArray());
                $newSubtask['parent_id'] = $task->id;
                $newSubtask['board_id'] = $data['board_id'];
                $newSubtask->save();
                $subtask->load('assignees');
                foreach ($subtask->assignees as $assignee) {
                    $taskService->updateAssignee($assignee->ID, $newSubtask);
                }
            }
        }
        $task->load(['board', 'assignees', 'labels', 'watchers', 'attachments']);

        return $task;
    }

    public function convertTaskToSubtask($taskId, $parent_id)
    {
        $task = Task::findOrFail($taskId);
        $parentTask = Task::findOrFail($parent_id);
        if($task)
        {
            $task->parent_id = $parent_id;
            $task->stage_id = null;
            $task->save();

            //Removing all task related notifications, because task can not be opened from notification
            $notificationIds = $task->notifications->pluck('id');
            $task->notifications()->delete();
            NotificationUser::whereIn('notification_id', $notificationIds)->delete();
        }

        return $parentTask;
    }

    public function addAssigneeToSubtask($taskId, $assigneeId){
        $task = Task::findOrFail($taskId);

        //task assignees watchers removed
        $task->watchers()->detach();
        $task->assignees()->detach();

        $taskService = new TaskService();
        $taskService->updateTaskProperty('assignees', $assigneeId, $task);
    }

    public function getTaskTimeTrack($boardId, $taskId)
    {
        return TimeTrack::where('board_id', $boardId)
            ->where('task_id', $taskId)
            ->orderBy('updated_at', 'DESC')
            ->with(['user'])
            ->get();
    }

}