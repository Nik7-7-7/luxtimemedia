<?php

namespace FluentBoardsPro\App\Hooks\Handlers;

use FluentBoards\App\Services\PermissionManager;
use FluentBoards\Framework\Support\Str;
use FluentBoardsPro\App\Modules\TimeTracking\Model\TimeTrack;
use FluentBoardsPro\App\Services\ProHelper;

class DataExporter
{
    private $request;

    public function exportTimeSheet()
    {
        $this->verifyRequest();
        $boardId = $this->request->get('board_id');

        $dateRange = ProHelper::getValidatedDateRange($this->request->get('date_range', []));

        $tracks = TimeTrack::when($this->request->get('board_id'), function ($q) use ($boardId) {
            $q->where('board_id', $boardId);
        })
            ->orderBy('updated_at', 'DESC')
            ->whereBetween('completed_at', $dateRange)
            ->with(['user', 'board', 'task' => function ($q) {
                $q->select('id', 'title', 'slug');
            }])
            ->whereHas('task')
            ->get();

        $writer = $this->getCsvWriter();
        $writer->insertOne([
            'Board',
            'Task',
            'Member',
            'Log Date',
            'Billable Hours',
            'Notes'
        ]);

        $rows = [];
        foreach ($tracks as $track) {
            $rows[] = [
                $this->sanitizeForCSV($track->board->title),
                $this->sanitizeForCSV($track->task->title),
                $this->sanitizeForCSV($track->user->display_name),
                $this->formatTime($track->completed_at, 'Y-m-d'),
                $this->miniutesToHours($track->billable_minutes),
                $this->sanitizeForCSV($track->message)
            ];
        }

        $writer->insertAll($rows);
        $writer->output('time-sheet-' . date('Y-m-d_H-i') . '.csv');
        die();
    }

    private function verifyRequest()
    {
        $this->request = FluentBoards('request');
        $boardId = $this->request->get('board_id');
        if (PermissionManager::isBoardManager($boardId)) {
            return true;
        }

        die('You do not have permission');
    }

    private function getCsvWriter()
    {
        if (!class_exists('\League\Csv\Writer')) {
            include FLUENT_BOARDS_PLUGIN_PATH . 'app/Services/Libs/csv/autoload.php';
        }

        return \League\Csv\Writer::createFromFileObject(new \SplTempFileObject());
    }

    private function sanitizeForCSV($content)
    {
        $formulas = ['=', '-', '+', '@', "\t", "\r"];

        if (Str::startsWith($content, $formulas)) {
            $content = "'" . $content;
        }

        return $content;
    }

    /*
     * Convert minutes to hours 30 mins as .5 hours
     */
    private function miniutesToHours($minutes)
    {
        return round($minutes / 60, 2);
    }

    private function formatTime($time, $format = 'Y-m-d H:i:s'): string
    {
        return date($format, strtotime($time));
    }
}
