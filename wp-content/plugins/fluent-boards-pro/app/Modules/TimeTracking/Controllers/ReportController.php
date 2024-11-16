<?php

namespace FluentBoardsPro\App\Modules\TimeTracking\Controllers;

use FluentBoards\App\Models\Task;
use FluentBoards\App\Http\Controllers\Controller;
use FluentBoards\Framework\Http\Request\Request;
use FluentBoardsPro\App\Modules\TimeTracking\Model\TimeTrack;
use FluentBoardsPro\App\Modules\TimeTracking\TimeTrackingHelper;
use FluentBoardsPro\App\Services\ProHelper;

class ReportController extends Controller
{
    public function getTracksByTasks(Request $request)
    {
        $boardId = $request->get('board_id');

        $dateRange = $this->getValidatedDateRange($request->get('date_range', []));

        $tracks = TimeTrack::when($boardId, function ($q) use ($boardId) {
            $q->where('board_id', $boardId);
        })
            ->orderBy('updated_at', 'DESC')
            ->whereBetween('completed_at', $dateRange)
            ->with(['user', 'board', 'task' => function ($q) {
                $q->select('id', 'title', 'slug');
            }])
            ->whereHas('task')
            ->get();

        $timeSheets = [];
        $totalMinutes = 0;
        $formattedTasks = [];

        foreach ($tracks as $track) {
            if (!isset($formattedTasks[$track->task_id])) {
                $formattedTasks[$track->task_id] = [
                    'task'  => $track->task,
                    'board' => $track->board
                ];
            }

            $date = date('Y-m-d', strtotime($track->completed_at));

            if (!isset($timeSheets[$date])) {
                $timeSheets[$date] = [];
            }

            if (!isset($timeSheets[$date][$track->task_id])) {
                $timeSheets[$date][$track->task_id] = [];
            }

            $timeSheets[$date][$track->task_id][] = [
                'id'               => $track->id,
                'created_at'       => (string)$track->created_at,
                'user'             => $track->user,
                'completed_at'     => $track->completed_at,
                'billable_minutes' => $track->billable_minutes,
                'message'          => $track->message
            ];

            $totalMinutes += $track->billable_minutes;
        }

        $dateLabels = [];

        // create date labels from date range
        $date = $dateRange[0];
        while ($date <= $dateRange[1]) {
            $dateLabels[] = date('Y-m-d', strtotime($date));
            $date = date('Y-m-d', strtotime('+1 day', strtotime($date)));
        }

        return [
            'tasks'        => array_values($formattedTasks),
            'date_labels'  => $dateLabels,
            'totalMinutes' => $totalMinutes,
            'time_sheets'  => $timeSheets,
            'date_range'   => $dateRange
        ];
    }

    public function getTracksByUsers(Request $request)
    {
        $boardId = $request->get('board_id');
        $dateRange = $this->getValidatedDateRange($request->get('date_range', []));

        $tracks = TimeTrack::when($boardId, function ($q) use ($boardId) {
            $q->where('board_id', $boardId);
        })
            ->orderBy('updated_at', 'DESC')
            ->whereBetween('completed_at', $dateRange)
            ->with(['user', 'board', 'task' => function ($q) {
                $q->select('id', 'title', 'slug');
            }])
            ->whereHas('task')
            ->get();

        $timeSheets = [];
        $totalMinutes = 0;
        $formattedUsers = [];

        foreach ($tracks as $track) {
            if (!$track->user) {
                continue;
            }

            if (!isset($formattedUsers[$track->user_id])) {
                $formattedUsers[$track->user_id] = $track->user;
            }

            $date = date('Y-m-d', strtotime($track->completed_at));

            if (!isset($timeSheets[$date])) {
                $timeSheets[$date] = [];
            }

            if (!isset($timeSheets[$date][$track->user_id])) {
                $timeSheets[$date][$track->user_id] = [];
            }

            $timeSheets[$date][$track->user_id][] = [
                'id'               => $track->id,
                'created_at'       => (string)$track->created_at,
                'task'             => $track->task,
                'board'            => $track->board,
                'completed_at'     => $track->completed_at,
                'billable_minutes' => $track->billable_minutes,
                'message'          => $track->message
            ];

            $totalMinutes += $track->billable_minutes;
        }

        $dateLabels = [];

        // create date labels from date range
        $date = $dateRange[0];
        while ($date <= $dateRange[1]) {
            $dateLabels[] = date('Y-m-d', strtotime($date));
            $date = date('Y-m-d', strtotime('+1 day', strtotime($date)));
        }

        return [
            'users'        => array_values($formattedUsers),
            'date_labels'  => $dateLabels,
            'totalMinutes' => $totalMinutes,
            'time_sheets'  => $timeSheets,
            'date_range'   => $dateRange
        ];
    }

    private function getValidatedDateRange($dateRange)
    {
        return ProHelper::getValidatedDateRange($dateRange);
    }

}
