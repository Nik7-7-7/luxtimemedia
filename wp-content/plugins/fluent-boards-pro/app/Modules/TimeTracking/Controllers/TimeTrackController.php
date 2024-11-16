<?php

namespace FluentBoardsPro\App\Modules\TimeTracking\Controllers;

use FluentBoards\App\Models\Task;
use FluentBoards\Framework\Http\Request\Request;
use FluentBoardsPro\App\Modules\TimeTracking\Model\TimeTrack;
use FluentBoardsPro\App\Modules\TimeTracking\TimeTrackingHelper;
use FluentBoardsPro\App\Services\ProTaskService;

class TimeTrackController extends \FluentBoards\App\Http\Controllers\Controller
{
    public function getTracks(Request $request, $boardId, $taskId)
    {

        $tracks = (new ProTaskService())->getTaskTimeTrack($boardId, $taskId);

        return [
            'tracks'            => $tracks,
            'estimated_minutes' => TimeTrackingHelper::getTaskEstimation($taskId)
        ];
    }

    public function updateTimeEstimation(Request $request, $boardId, $taskId)
    {
        $task = Task::findOrFail($taskId);
        $task->updateMeta('_estimated_minutes', (int)$request->get('estimated_minutes'));

        return [
            'message' => __('Estimated time has been updated', 'fluent-boards-pro')
        ];
    }

    public function deleteTrack(Request $request, $boardId, $taskId, $trackId)
    {
        $activeTrack = TimeTrack::where('board_id', $boardId)
            ->where('task_id', $taskId)
            ->find($trackId);

        if (!$activeTrack) {
            return $this->sendError([
                'success' => false,
                'message' => __('Time track not found', 'fluent-boards-pro'),
            ], 422);
        }

        try {
            $activeTrack->delete();
            return $this->sendSuccess([
                'success' => true,
                'message' => __('Selected time track has been deleted', 'fluent-boards-pro'),
            ]);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 400);
        }
    }

    public function manualCommitTrack(Request $request, $boardId, $taskId)
    {
        $task = Task::findOrFail($taskId);

        $this->validate($request->all(), [
            'billable_minutes' => 'required|numeric|min:1',
            'message'          => 'string'
        ]);

        $totalMinutes = (int)$request->get('billable_minutes');
        $message = $request->getSafe('message', 'wp_kses_post');

        if ($totalMinutes <= 0) {
            return $this->sendError([
                'message' => __('Please provide valid hours and minutes', 'fluent-boards-pro')
            ]);
        }

        $data = [
            'status'           => 'commited',
            'completed_at'     => current_time('mysql'),
            'billable_minutes' => $totalMinutes,
            'working_minutes'  => $totalMinutes,
            'message'          => $message,
            'user_id'          => get_current_user_id(),
            'board_id'         => $boardId,
            'is_manual'        => 1,
            'task_id'          => $taskId
        ];

        $track = TimeTrack::create($data);

        $track->load('user');

        return [
            'track'   => $track,
            'message' => __('You have successfully submitted your working time', 'fluent-boards-pro')
        ];
    }

    public function updateCommitTrack(Request $request, $boardId, $taskId, $trackId)
    {
        $this->validate($request->all(), [
            'billable_minutes' => 'required|numeric|min:1',
            'message'          => 'string'
        ]);

        $totalMinutes = (int)$request->get('billable_minutes');
        $message = $request->getSafe('message', 'wp_kses_post');
        try {
            $track = TimeTrack::where('board_id', $boardId)
                ->where('task_id', $taskId)
                ->findOrFail($trackId);

            $track->message = $message;
            $track->billable_minutes = $totalMinutes;
            $track->working_minutes  = $totalMinutes;
            $track->save();

            return $this->sendSuccess([
                'success' => true,
                'message' => __('Selected Time-track has been updated', 'fluent-boards-pro'),
            ]);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 400);
        }
    }
}
