<?php

namespace FluentBoardsPro\App\Services;

use FluentBoards\App\Models\Task;
use FluentBoards\Framework\Support\Arr;

class SubtaskService
{
    public function getSubtasks($task_id)
    {
        $subtasks = Task::where('parent_id', $task_id)->with(['assignees'])->get();
        foreach ($subtasks as $task) {
            $task->isOverdue = $task->isOverdue();
            $task->isUpcoming = $task->upcoming();
        }
        return $subtasks;
    }

    public function createSubtask($task_id, $subtaskData)
    {
        $parentTask = Task::findOrFail($task_id);

        $lastPositionOfSubtask = $this->getLastSubtaskPosition($task_id);

        $data = [];
        $data['position'] = $lastPositionOfSubtask + 1;
        $data['parent_id'] = $parentTask->id;
        $data['title'] = $subtaskData['title'];
        $data['board_id'] = $parentTask->board_id;
        $data['status'] = 'open';
        $data['priority'] = 'low';
        $data['due_at'] = null;
        $subtask = Task::create($data);
        do_action('fluent_boards/subtask_added', $parentTask, $subtask);

        return $subtask;
    }

    public function deleteSubtask($task)
    {
        $parentTaskId = $task->parent_id;
        do_action('fluent_boards/task_deleted', $task);
        $task->watchers()->detach();
        $task->delete();

        Task::adjustSubtaskCount($parentTaskId);
    }

    /**
     * Retrieves subtasks that have been updated within the last minute for a given parent task ID.
     * @param int $parent_id The ID of the parent task for which subtasks are to be retrieved.
     * @return \FluentBoards\Framework\Database\Orm\Builder[]|\FluentBoards\Framework\Database\Orm\Collection
     * Returns a collection of subtasks that meet the specified criteria.
     */
    public function getLastMinuteUpdatedSubtasks($parent_id)
    {
        try {
            return Task::query()
                ->where('parent_id', (int)$parent_id)
                ->where('updated_at', '>=', gmdate('Y-m-d H:i:s', current_time('timestamp') - 60))
                ->with(['assignees'])
                ->get();
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 400);
        }
    }
    public function moveToBoard($subtask, $subtaskData)
    {
        $parentTaskId = $subtask->parent_id;
        $subtask->parent_id = null;
        $subtask->stage_id = $subtaskData['stage_id'];
        $subtask->save();
        $subtask->moveToNewPosition(1);
        Task::adjustSubtaskCount($parentTaskId);
        return $subtask;
    }


    /**
     * Summary of getLastSubtaskPosition it will return last position of subtask in a task. if there is no subtask than it will return 0
     * @param mixed $task_id
     * @return mixed
     */

    public function getLastSubtaskPosition($task_id)
    {
        $subtask = Task::where('parent_id', $task_id)->orderBy('position', 'desc')->first();

        return isset($subtask->position) ? $subtask->position : 0;
    }

}