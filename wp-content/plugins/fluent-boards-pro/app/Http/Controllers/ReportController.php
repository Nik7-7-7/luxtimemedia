<?php

namespace FluentBoardsPro\App\Http\Controllers;

use FluentBoards\App\Services\BoardService;
use FluentBoards\App\Services\PermissionManager;
use FluentBoards\Framework\Http\Request\Request;
use FluentBoardsPro\App\Modules\TimeTracking\Model\TimeTrack;

class ReportController extends Controller
{
    public function getTimeSheetReport(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $authUser = wp_get_current_user();
        $boardIds = PermissionManager::getBoardIdsForUser($authUser->ID);
        if (!empty($request->get('board_id'))) {
            $boardIds = PermissionManager::getBoardIdsForUser($authUser->ID, $request->get('board_id'));
        }
        $timings = TimeTrack::where('status', 'commited')->whereIn('board_id', $boardIds);
        if ($startDate && $endDate) {
            $timings = $timings->whereBetween('completed_at', [$startDate, $endDate]);
        }
        $timings = $timings->get();
        $timings = $timings->load('task', 'user', 'board');

        $sortTasks = [];
        foreach ($timings as $timing) {
            $times = $timings->where('task_id', $timing->task_id);

            if (!isset($sortTasks[$timing['task_id']])) {
                $sortTasks[$timing['task_id']] = [
                    'id'    => $timing['task_id'],
                    'title' => $timing->task->title,
                    'board' => $timing->board,
                    'total' => 0,
                    'times' => []
                ];
            }

            $sortTasks[$timing['task_id']]['total'] += $timing['billable_minutes'];

            $formattedTimes = [];
            foreach ($times as $time) {
                $user = $time->user;

                $formattedTimes[] = [
                    'id' => $time['id'],
                    'billable_minutes' => $time['billable_minutes'],
                    'working_minutes'  => $time['working_minutes'],
                    'completed_at'     => $time['completed_at'],
                    'message'          => $time['message'],
                    'user' => [
                        'ID'     => $user->ID,
                        'name'   => $user->display_name,
                        'avatar' => fluent_boards_user_avatar($user->user_email),
                        'email'  => $user->user_email
                    ]
                ];

            }
            $sortTasks[$timing['task_id']]['times'] = $formattedTimes;
        }

        $sortTasks = array_values($sortTasks);

        return $this->sendSuccess([
            'message' => 'Time sheet report',
            'timings' => $sortTasks
        ], 200);
    }

    public function getBoardReports(Request $request)
    {
        try {
            $boardService = new BoardService();
            if (!empty($request->get('board_id')))
            {
                $boardReport = $boardService->getBoardReports($request->get('board_id'));
            } else {
                $boardReport = $boardService->getAllBoardReports();
            }
            return $this->sendSuccess([
                'report' => $boardReport,
            ], 200);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 400);
        }
    }

    public function getStageWiseBoardReports($board_id)
    {
        try {
            $boardService = new BoardService();
            $stages = $boardService->getStageWiseBoardReports($board_id);
            return $this->sendSuccess([
                'stages' => $stages,
            ], 200);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 400);
        }
    }


}