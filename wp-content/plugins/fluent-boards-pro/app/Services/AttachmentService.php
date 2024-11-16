<?php

namespace FluentBoardsPro\App\Services;

use FluentBoardsPro\App\Models\TaskAttachment;
use FluentBoards\App\Models\Task;
use FluentBoards\App\Models\CommentImage;
use FluentBoards\App\Models\TaskMeta;
use FluentCommunity\Framework\Support\Arr;

class AttachmentService
{
    public function handleAttachment($taskId, $attachmentData, $file = null, $type = null)
    {
        // Fetch the task
        $task = Task::find($taskId);
        if (!$task) {
            throw new \Exception(__("Task doesn't exist", 'fluent-boards-pro'));
        }

        // Determine attachment type and handle accordingly
        if ($attachmentData['type'] === 'url') {
            $attachment = $this->createUrlAttachment($task, $attachmentData, $type);
        } else {
            $attachment = $this->createFileAttachment($task, $attachmentData, $file, $type);
        }

        // Update task's attachment count
        $this->updateTaskAttachmentCount($task);

        return $attachment;
    }

    /**
     * Creates and handles a URL attachment.
     */
    protected function createUrlAttachment($task, $urlData, $type = null)
    {
        $urlMeta = RemoteUrlParser::parse($urlData['url']);
        $urlData['settings'] = ['meta' => $urlMeta];
        $attachment = $this->initializeAttachment($task, $urlData, $type, $urlMeta);
        $attachment->save();

        return $attachment;
    }

    /**
     * Creates and handles a file attachment.
     */
    protected function createFileAttachment($task, $fileData, $file, $type = null)
    {
        // Process media data for file attachment and merge with file data
        $mediaData = $this->processMediaData($fileData, $file);
        $updatedData = array_merge($fileData, $mediaData);

        // Create and initialize TaskAttachment object
        $attachment = $this->initializeAttachment($task, $updatedData, $type);
        $attachment->fill($mediaData); // Fill with processed media data
        $attachment->save(); // Save the attachment

        return $attachment;
    }

    /**
     * Initializes a TaskAttachment object.
     */
    protected function initializeAttachment($task, $attachData, $type, $urlMeta = [])
    {
        $attachment = new TaskAttachment();
        $attachment->object_id = $task->id;
        $attachment->object_type = $type ?? \FluentBoards\App\Services\Constant::TASK_ATTACHMENT;
        $attachment->attachment_type = $attachData['type'];
        $attachment->title =  $this->setTitle($attachData['type'], $attachData['name'] ?? '', $urlMeta);
        $attachment->file_path = ($attachData['type'] !== 'url') ? $attachData['file'] : null;
        $attachment->full_url = esc_url($attachData['url']);
        $attachment->file_size = $attachData['size'] ?? null;
        $attachment->settings = $attachData['settings'] ?? '';
        $attachment->driver = $attachData['driver'] ?? 'local';

        return $attachment;
    }

    /**
     * Processes media data for non-URL attachments.
     */
    protected function processMediaData($attachData, $file)
    {
        $attachData['driver'] = $attachData['driver'] ?? 'local';
        return apply_filters('fluent_boards/upload_media_data', [
            'type' => $attachData['type'],
            'driver' => $attachData['driver'],
            'file_path' => $attachData['full_path'],
            'full_url' => $attachData['url'],
            'settings' => '',
        ], $file);
    }

    /**
     * Updates the task's attachment count.
     */
    protected function updateTaskAttachmentCount($task)
    {
        $taskSettings = $task->settings;
        $taskSettings['attachment_count'] = (int)($taskSettings['attachment_count'] ?? 0) + 1;
        $task->settings = $taskSettings;
        $task->save();
    }

    private function setTitle($type, $title, $UrlMeta)
    {
        if($type != 'url') {
            return sanitize_file_name($title);
        }
        return $title ?? $UrlMeta['title'] ?? '';
    }

    public function deleteTaskAttachment($taskId, $attachmentId)
    {
        $task = Task::find($taskId);
        $attachment = TaskAttachment::find($attachmentId);
        $deletedAttachment = clone $attachment;
        $attachment->delete();

        do_action('fluent_boards/task_attachment_deleted', $deletedAttachment);

        $taskSettings = $task->settings;
        $taskSettings['attachment_count'] = max((int)($taskSettings['attachment_count'] ?? 0) - 1, 0);
        $task->settings = $taskSettings;
        $task->save();
        // Return the updated list of task attachments
        return $task->attachments;
    }

    public function updateTaskAttachment($attachmentId, $attachmentTitle)
    {
        $attachment = TaskAttachment::find($attachmentId);

        $attachment->title = $attachmentTitle;
        $attachment->save();

        // Return the updated list of task attachments
        return $attachment;
    }

    public function getAttachTaskFilesLink($task_id)
    {
        return TaskMeta::where('task_id', $task_id)->where('key', 'task-attachment')->orWhere('key', 'task-uploaded-file')->get();
    }

}