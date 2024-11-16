<?php

namespace FluentBoardsPro\App\Http\Controllers;

use FluentBoards\App\Http\Controllers\Controller;
use FluentBoards\App\Models\Board;
use FluentBoards\App\Models\Meta;
use FluentBoards\App\Services\PermissionManager;
use FluentBoardsPro\App\Services\Constant;

class ExportController extends Controller
{
    public function exportBoard($boardId)
    {
        try {
            if (!PermissionManager::isAdmin())
            {
                throw new \Exception('You do not have permission to export board', 400);
            }
            // Fetch boards with related tasks and comments
            $board = Board::findOrFail($boardId);
            $board->load(['stages', 'labels', 'tasks', 'users', 'customFields', 'tasks.assignees', 'tasks.watchers', 'tasks.comments', 'tasks.subtasks', 'tasks.subtasks.assignees', 'tasks.labels', 'tasks.customFields']);

            // Combine data into a single array
            $data = [
                'key' => Constant::FLUENT_BOARDS_IMPORT,
                'site_url' => site_url('/'),
                'board' => $board,
            ];

            // Convert to JSON
            $jsonData = json_encode($data, JSON_PRETTY_PRINT);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('JSON encoding error: ' . json_last_error_msg());
            }

            // Define the filename
            $fileName = $board->title . '.json';

            // Set headers to force download
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="' . $fileName . '"');

            // Output the JSON data
            return $this->sendSuccess([
                'fileName' => $fileName,
                'data' => $jsonData
            ]);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }
}