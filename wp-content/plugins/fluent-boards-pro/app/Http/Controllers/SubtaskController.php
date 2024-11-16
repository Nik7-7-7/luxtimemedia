<?php

namespace FluentBoardsPro\App\Http\Controllers;

use FluentBoards\App\Models\Task;
use FluentBoards\App\Services\Helper;
use FluentBoards\App\Services\TaskService;
use FluentBoardsPro\App\Services\ProTaskService;
use FluentBoardsPro\App\Services\SubtaskService;
use FluentBoards\Framework\Http\Request\Request;

class SubtaskController extends Controller
{
    private SubtaskService $subtaskService;

    public function __construct(SubtaskService $subtaskService)
    {
        parent::__construct();
        $this->subtaskService = $subtaskService;
    }

    public function getSubtasks($board_id, $task_id)
    {
        try {
            $subtasks = $this->subtaskService->getSubtasks($task_id);

            return $this->sendSuccess([
                'data' => $subtasks,
            ], 200);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 400);
        }
    }

    public function createSubtask(Request $request, $board_id, $task_id)
    {
        $subtaskData = $this->subtaskSanitizeAndValidate($request->all(), [
            'title' => 'required|string',
        ]);

        try {
            $subtask = $this->subtaskService->createSubtask($task_id, $subtaskData);

            $subtask['assignees'] = $subtask->assignees;

            return $this->sendSuccess([
                'subtask' => $subtask,
                'message' => __('Subtask has been added', 'fluent-boards-pro')
            ], 200);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 400);
        }
    }

    public function deleteSubtasks($board_id, $task_id)
    {
        try {
            $task = Task::findOrFail($task_id);
            $deletedTask = clone $task;

            $options = null;
            //if we need to do something before a task is deleted
            do_action('fluent_boards/before_task_deleted', $task, $options);

            $this->subtaskService->deleteSubtask($task);

            do_action('fluent_boards/subtask_deleted_activity', $deletedTask->parent_id, $deletedTask->title);

            // therefore the task is subtask and we need to update other subtasks position
            return $this->sendSuccess([
                'deletedSubtask'  => $deletedTask,
                'changedSubtasks' => $this->subtaskService->getLastMinuteUpdatedSubtasks($deletedTask->parent_id),
                'message'         => __('Task has been deleted', 'fluent-boards-pro'),
            ], 200);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 404);
        }
    }

    /*
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $board_id
     * @param  int  $subtask_id
     * Convert a subtask to a task and move it to the board where parent task was created
     * @return \Illuminate\Http\Response
     */
    public function moveToBoard(Request $request, $board_id, $task_id)
    {
	    $subtaskId = $task_id;
        $subtaskData = $this->subtaskSanitizeAndValidate($request->all(), [
            'stage_id' => 'required',
        ]);
        $taskService = new TaskService();
        try {
            $subtask = Task::findOrFail($subtaskId);
            $subtask = $this->subtaskService->moveToBoard($subtask, $subtaskData);
            $changedSubtasks = $this->subtaskService->getLastMinuteUpdatedSubtasks($subtask->parent_id);

            return $this->sendSuccess([
                'moveSubtask' => $subtask,
                'changedSubtasks' => $changedSubtasks,
            ], 201);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 400);
        }
    }



    private function subtaskSanitizeAndValidate($data, array $rules = [])
    {
        $data = Helper::sanitizeSubtask($data);

        return $this->validate($data, $rules);
    }

    public function updateSubtaskPosition (Request $request, $board_id, $subtask_id)
    {
        $subtaskData = $this->subtaskSanitizeAndValidate($request->all(), [
            'newPosition' => 'required|integer',
        ]);
        try {
            $subtask = Task::find($subtask_id);
            $subtask->moveToNewPosition($subtaskData['newPosition']);
            $changedSubtasks = $this->subtaskService->getLastMinuteUpdatedSubtasks($subtask->parent_id);
            return $this->sendSuccess([
                'changedSubtasks' => $changedSubtasks
            ], 201);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 400);
        }
    }

    public function ConvertTaskToSubtask(Request $request, $board_id, $task_id)
    {
        $parentId = $request->getSafe('parent_id');
        $assigneeId = $request->getSafe('assigneeId');
        $taskService = new ProTaskService();
        try {
            $parentTask = $taskService->convertTaskToSubtask($task_id, $parentId);

            if (!empty($assigneeId))
            {
                $taskService->addAssigneeToSubtask($task_id, $assigneeId);
            }

            return $this->sendSuccess([
                'message'         => __('Task has been converted to subtask', 'fluent-boards-pro'),
                'parentTask'      => $parentTask
            ], 200);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 400);
        }
    }

}
