<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Entry_UserSearcher
{
    /**
     * Handle the request to search users via Ajax
     */
    public function search()
    {
        $this->validateSearchRequest();

        $search = sanitize_text_field(wp_unslash($_GET['search']));
        $results = array();

        foreach (Quform::searchUsers($search) as $user) {
            $results[] = array('id' => $user->ID, 'text' => $user->user_login);
        }

        wp_send_json(array(
            'type' => 'success',
            'results' => $results
        ));
    }

    protected function validateSearchRequest()
    {
        if ( ! Quform::isGetRequest() || ! isset($_GET['search']) || ! is_string($_GET['search'])) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Bad request', 'quform')
            ));
        }

        if ( ! current_user_can('quform_edit_entries')) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Insufficient permissions', 'quform')
            ));
        }

        if ( ! check_ajax_referer('quform_entries_search_users', false, false)) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Nonce check failed', 'quform')
            ));
        }
    }
}
