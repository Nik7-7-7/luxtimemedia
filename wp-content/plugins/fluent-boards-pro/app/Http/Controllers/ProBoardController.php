<?php

namespace FluentBoardsPro\App\Http\Controllers;

use FluentBoards\App\Models\Board;
use FluentBoards\App\Models\Meta;
use FluentBoards\App\Models\Stage;
use FluentBoards\App\Models\User;
use FluentBoards\App\Services\BoardService;
use FluentBoards\App\Services\BoardTermService;
use FluentBoards\App\Services\Constant;
use FluentBoards\App\Services\StageService;
use FluentBoards\App\Services\Helper;
use FluentBoards\App\Services\PermissionManager;
use FluentBoards\App\Services\TaskService;
use FluentBoards\Framework\Http\Request\Request;
use FluentBoards\Framework\Support\Arr;
use FluentBoardsPro\App\Services\CustomFieldService;
use FluentBoardsPro\App\Services\ProTaskService;

class ProBoardController extends Controller
{
    public function createBoard(Request $request)
    {
        $boardData = $this->boardSanitizeAndValidate($request->get('board'), [
            'title'       => 'required|string',
            'description' => 'nullable',
            'type'        => 'required|string',
            'currency'    => 'required|string',
            'slug'        => 'required|string',
        ]);

        $boardService = new BoardService();
        $boardTermService = new BoardTermService();

        try {
            $board = $boardService->createBoard($boardData);
            $boardTermService->createDefaultLabel($board->id);
            $type = ucfirst($boardData['type']);

            if (isset($boardData['is_roadmap']) && $boardData['is_roadmap'] == 'yes') {
                $boardTermService->createRoadmapStages($board, $boardData['stages']);
            } else {
                $boardTermService->createDefaultStages($board);
            }

            do_action('fluent_boards/board_created', $board);

            // translators: This placeholder is for the type of board (e.g., "project" or "task").
            $message = sprintf(__('%s board has been created', 'fluent-boards-pro'), esc_html($type));

            return $this->sendSuccess([
                'message' => $message,
                'board'   => $board,
            ]);

        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 400);
        }
    }


    private function boardSanitizeAndValidate($data, array $rules = [])
    {
        $data = Helper::sanitizeBoard($data);

        return $this->validate($data, $rules);
    }

    public function sendInvitationToBoard(Request $request, $board_id)
    {
        $data = $this->validate($request->all(), [
            'email'  => 'required|email'
        ]);

        $boardService = new BoardService();

        try {
            $user = $boardService->sendInvitationToBoard($board_id, $data['email']);

            if ($user) {
                return $this->sendError([
                    'message' => __('Already a wordpress member', 'fluent-boards-pro'),
                ], 304);
            }

            return $this->sendSuccess([
                'message' => __('Invitation sent successfully!', 'fluent-boards-pro'),
            ], 200);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 404);
        }
    }

    public function getInvitations($board_id)
    {
        try {
            $boardService = new BoardService();

            $invitations = $boardService->getInvitations($board_id);

            return $this->sendSuccess([
                'invitations' => $invitations
            ], 200);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 404);
        }
    }

    public function deleteInvitation($board_id, $invitation_id)
    {
        try {
            $boardService = new BoardService();

            $boardService->deleteInvitation($invitation_id);

            return $this->sendSuccess([
                'message' => __('Invitation deleted successfully!', 'fluent-boards-pro'),
            ], 200);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 404);
        }
    }

    public function updateStageTemplate($board_id, $stage_id)
    {
        try{
            $stageService = new StageService();

            $stage = $stageService->updateStageTemplate($stage_id);

            return $this->sendSuccess([
                'stage' => $stage,
                'message'      => __('Stage updated successfully', 'fluent-boards-pro'),
            ], 200);

        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 400);
        }
    }

    public function getTemplateStages(Request $request)
    {
        try {
            $stageService = new StageService();

            $userId   = get_current_user_id();
            $templateStages = [];

            if (PermissionManager::isAdmin($userId)) {
                $relatedBoardsQuery = Board::query()->where('type', 'to-do')->pluck('id');
                $relatedStages = Stage::whereIn('board_id', $relatedBoardsQuery)->with('board')->get();
                foreach ($relatedStages as $stage)
                {
                    $isThisStageTemplate = Arr::get($stage->settings, 'is_template', false);
                    if($isThisStageTemplate) {
                        $templateStages[] = $stage;
                    }
                }
            } else {
                $currentUser = User::find($userId);
                $relatedBoardsQuery = $currentUser->whichBoards->pluck('id');
                $relatedStages = Stage::whereIn('board_id', $relatedBoardsQuery)->with('board')->get();
                foreach ($relatedStages as $stage)
                {
                    $isThisStageTemplate = Arr::get($stage->settings, 'is_template', false);
                    if($isThisStageTemplate){
                        $templateStages[] = $stage;
                    }
                }
            }

            return $this->sendSuccess([
                'stages' => $templateStages,
            ], 200);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 404);
        }
    }
    public function getDefaultBoardImages($board_id = null)
    {
        try {
            return $this->sendSuccess((new ProTaskService())->getDefaultBoardImages());
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 400);
        }
    }

    public function downloadDefaultBoardImages(Request $request, $board_id)
    {
        try {
            $imageData = $request->getSafe('imageData');
            return $this->sendSuccess((new ProTaskService())->downloadDefaultBoardImages($imageData));
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 400);
        }
    }

    public function getTemplateTasks()
    {
        try {
            return $this->sendSuccess((new ProTaskService())->getTemplateTasks());
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 400);
        }
    }

    public function createFromTemplate(Request $request, $board_id, $task_id)
    {
        $taskData = $this->taskSanitizeAndValidate($request->all(), [
            'title'          => 'required|string',
            'board_id'       => 'required|numeric',
            'stage_id'       => 'required|numeric',
            'assignee'       => 'required',
            'subtask'        => 'required',
            'label'          => 'required',
            'attachment'     => 'required',
        ]);
        try {
            $task = (new ProTaskService())->createFromTemplate($task_id, $taskData);

            return $this->sendSuccess([
                'task'         => $task,
                'message'      => __('Task has been successfully created', 'fluent-boards-pro'),
                'updatedTasks' => (new TaskService())->getLastOneMinuteUpdatedTasks($task->board_id)
            ], 201);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 400);
        }
    }
    private function taskSanitizeAndValidate($data, array $rules = [])
    {
        $data = Helper::sanitizeTask($data);

        return $this->validate($data, $rules);
    }
}
