<?php

namespace FluentBoardsPro\App\Http\Controllers;

use FluentBoards\App\Models\Board;
use FluentBoards\App\Models\Meta;
use FluentBoards\App\Models\Relation;
use FluentBoards\App\Models\User;
use FluentBoards\App\Services\BoardService;
use FluentBoards\App\Services\Constant;
use FluentBoards\App\Services\PermissionManager;
use FluentBoards\App\Services\UserService;
use FluentBoards\Framework\Http\Request\Request;

class BoardUserController extends Controller
{
    public function users()
    {
        $userService = new UserService();

        return [
            'users'  => $userService->allFluentBoardsUsers(),
            'boards' => Board::select('id', 'title')->orderBy('title', 'ASC')->get()
        ];
    }

    /*
     * Add as FluentBoard Admin
     */
    public function addAsAdmin(Request $request)
    {
        $userIds = $request->get('user_ids', []);

        if (!is_array($userIds)) {
            return $this->sendError([
                'message' => 'Invalid user ids. Please select valid users.'
            ]);
        }

        $userIds = array_filter(array_map('intval', $userIds));

        foreach ($userIds as $userId) {
            $user = get_user_by('ID', $userId);
            if (!$user) {
                continue;
            }

            $existUser = Meta::where('object_id', $userId)->where('object_type', Constant::FLUENT_BOARD_ADMIN)->first();
            if (!$existUser) {
                Meta::create([
                    'object_id'   => $userId,
                    'object_type' => Constant::FLUENT_BOARD_ADMIN
                ]);
            }
        }

        return [
            'message' => __('Selected users has been added as admin', 'fluent-boards-pro')
        ];
    }

    public function removeAdmins(Request $request)
    {
        $userIds = $request->get('user_ids', []);

        if (!is_array($userIds)) {
            return $this->sendError([
                'message' => 'Invalid user ids. Please select valid users.'
            ]);
        }

        $userIds = array_filter(array_map('intval', $userIds));

        foreach ($userIds as $userId) {
            $user = get_user_by('ID', $userId);
            if (!$user) {
                continue;
            }

            if ($user->has_cap('manage_options')) {
                $this->sendError([
                    'message' => 'You can not remove super admin from admin list.'
                ]);
            }

            Meta::where('object_id', $userId)->where('object_type', Constant::FLUENT_BOARD_ADMIN)->delete();
        }

        return [
            'message' => __('Selected user has been deleted from admin access. Please check individual board accesses.')
        ];
    }

    public function addUserToBoards(Request $request, $userId)
    {
        $boardIds = $request->get('board_ids', []);

        if (!is_array($boardIds)) {
            return $this->sendError([
                'message' => 'Invalid board ids. Please select valid boards.'
            ]);
        }

        $boardIds = array_filter(array_map('intval', $boardIds));

        $boardService = new BoardService();

        foreach ($boardIds as $boardId) {
            $boardService->addMembersInBoard($boardId, $userId);
        }

        return [
            'message' => __('Selected user has been added to selected boards')
        ];
    }

    public function addUsersToBoards(Request $request)
    {
        $this->validate($request->all(), [
            'board_ids' => 'required|array',
            'user_ids'  => 'required|array'
        ]);

        $boardIds = $request->get('board_ids', []);
        $userIds = $request->get('user_ids', []);

        $boardIds = array_filter(array_map('intval', $boardIds));
        $userIds = array_filter(array_map('intval', $userIds));


        $boardService = new BoardService();

        foreach ($userIds as $userId) {
            foreach ($boardIds as $boardId) {
                if(!$boardService->isAlreadyMember($boardId, $userId)){
                    $boardService->addMembersInBoard($boardId, $userId);
                }
            }
        }

        return [
            'message' => __('Selected users has been added to selected boards', 'fluent-boards-pro')
        ];
    }

    public function syncBoardRoles(Request $request, $userId)
    {
        if (PermissionManager::isAdmin($userId)) {
            return $this->sendError([
                'message' => 'You can not sync access for super admin.'
            ]);
        }

        $roles = $request->get('roles', []);
        $roles = array_filter($roles);

        $previousRoles = Relation::where('foreign_id', $userId)
            ->where('object_type', Constant::OBJECT_TYPE_BOARD_USER)
            ->get();

        $syncIds = [];
        foreach ($previousRoles as $role) {
            $syncIds[] = $role->object_id;
            if (empty($roles[$role->object_id])) {
                $role->delete();
            } else {
                // update the role
                $preSettings = $role->settings;
                $preSettings['is_admin'] = $roles[$role->object_id] == 'admin' ? 1 : 0;
                $role->settings = $preSettings;
                $role->save();

                //board activity hooks
                $hookName = $roles[$role->object_id] == 'admin' ? 'board_admin_added' : 'board_admin_removed';
                do_action('fluent_boards/'.$hookName, $role->object_id, $userId);
            }
        }

        $newBoards = array_diff(array_keys($roles), $syncIds);

        if ($newBoards) {
            $boardService = new BoardService();
            foreach ($newBoards as $boardId) {
                $boardService->addMembersInBoard($boardId, $userId);
            }
        }

        return [
            'message' => __('User roles has been synced successfully.', 'fluent-boards-pro')
        ];
    }

    public function removeUserFromAllBoards(Request $request, $userId)
    {
        if (user_can($userId, 'manage_options')) {
            return $this->sendError([
                'message' => 'You can not sync access for super admin.'
            ]);
        }

        // first remove from all board admin
        Meta::where('object_id', $userId)->where('object_type', Constant::FLUENT_BOARD_ADMIN)->delete();

        // remove from all individual boards
        Relation::whereIn('object_type', [
            'board_user',
            'task_assignee',
            'task_user_watch'
        ])
            ->where('foreign_id', $userId)
            ->delete();

        return [
            'message' => __('User has been removed from all boards.', 'fluent-boards-pro')
        ];
    }

    public function makeManager($boardId, $userId)
    {
        try {
            $boardService = new BoardService();
            return $this->sendSuccess([
                'message' => __('Role updated successfully', 'fluent-boards-pro'),
                'member'  => $boardService->makeAdminOfBoard($boardId, $userId)
            ], 200);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 404);
        }
    }

    public function removeManager($board_id, $user_id)
    {
        try {
            $boardService = new BoardService();
            return $this->sendSuccess([
                'message' => __('Role updated successfully', 'fluent-boards-pro'),
                'member'  => $boardService->removeAdminFromBoard($board_id, $user_id)
            ], 200);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 404);
        }
    }
}
