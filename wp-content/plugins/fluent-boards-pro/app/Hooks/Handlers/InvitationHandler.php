<?php

namespace FluentBoardsPro\App\Hooks\Handlers;


use FluentBoards\App\Models\Board;
use FluentBoards\App\Models\Meta;
use FluentBoards\App\Services\Constant;
use FluentBoards\App\Services\Helper;

class InvitationHandler
{
    public function processInvitation()
    {
        $password = esc_sql($_REQUEST['password']);
        $firstname = esc_sql($_REQUEST['firstname']);
        $lastname = esc_sql($_REQUEST['lastname']);
        $email = esc_sql($_REQUEST['email']);
        $boardId = esc_sql($_REQUEST['board_id']);

        $user_data = array(
            'user_login'    =>  $email,    // User login (username)
            'user_pass'     =>  $password,    // User password
            'user_email'    =>  $email,       // User email address
            'first_name'    =>  $firstname,       // User firstname
            'last_name'     =>  $lastname,       // User lastname
            'role'          => 'subscriber', // User role (optional)
        );

        // Insert the user into the database
        $userId = wp_insert_user($user_data);

        // Check if the user was successfully created
        if (is_wp_error($userId)) {
            // There was an error creating the user
            echo 'Error: ' . $userId->get_error_message();
        } else {
            $this->makeBoardMember($boardId, $userId);
            $login_data = array();
            $login_data['user_login'] = $email;
            $login_data['user_password'] = $password;
            $login_data['remember'] = true;
            $user_verify = wp_signon($login_data, true);

            if (is_wp_error($user_verify)) {
                $errors[] = 'Invalid email or password. Please try again!';
            } else {
                wp_set_auth_cookie($user_verify->ID);
                $this->deleteHashCode($boardId, $email);
                $page_url = fluent_boards_page_url();
                $boardUrl = $page_url . 'boards/' . $boardId;
                wp_redirect($boardUrl);
                exit;
            }
        }
        status_header(200);
        die("Server received from your browser.");
        //request handlers should die() when they complete their task

    }
    private function makeBoardMember($boardId, $userId)
    {
        $board = Board::find($boardId);
        $board->users()->attach(
            $userId,
            [
                'object_type' => Constant::OBJECT_TYPE_BOARD_USER,
                'settings' => maybe_serialize(Constant::BOARD_USER_SETTINGS),
                'preferences' => maybe_serialize(Constant::BOARD_NOTIFICATION_TYPES)
            ]
        );
    }

    private function deleteHashCode($boardId, $email)
    {
        $activeHashCodes = $this->getActiveHashCodes($boardId);

        foreach ($activeHashCodes as $savedHash) {
            $value = maybe_unserialize($savedHash->value);
            if($value['email'] == $email){
                Meta::where('id', $savedHash->id)->delete();
            }
        }
    }

    private function getActiveHashCodes($boardId)
    {
        return Meta::query()->where('object_id', $boardId)
            ->where('object_type', Constant::OBJECT_TYPE_BOARD)
            ->where('key', Constant::BOARD_INVITATION)
            ->get();
    }
}
