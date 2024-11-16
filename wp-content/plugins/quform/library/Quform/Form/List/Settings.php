<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Form_List_Settings
{
    /**
     * Hook entry point for saving forms list settings
     */
    public function save()
    {
        $this->validateSaveRequest();
        $this->handleSaveRequest();
    }

    /**
     * Validate the request for saving forms list settings
     */
    protected function validateSaveRequest()
    {
        if ( ! Quform::isPostRequest() || ! isset($_POST['per_page']) || ! is_string($_POST['per_page'])) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Bad request', 'quform')
            ));
        }

        if ( ! check_ajax_referer('quform_save_forms_table_settings', false, false)) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Nonce check failed', 'quform')
            ));
        }
    }

    /**
     * Handle the request for saving forms list settings
     */
    protected function handleSaveRequest()
    {
        if ( $_POST['per_page'] === '') {
            wp_send_json(array(
                'type' => 'error',
                'errors' => array('qfb_forms_per_page' => __('This field is required', 'quform'))
            ));
        }

        if ( ! is_numeric($_POST['per_page'])) {
            wp_send_json(array(
                'type' => 'error',
                'errors' => array('qfb_forms_per_page' => __('Value must be numeric', 'quform'))
            ));
        }

        $perPage = (int) $_POST['per_page'];

        if ($perPage < 1) {
            wp_send_json(array(
                'type' => 'error',
                'errors' => array('qfb_forms_per_page' => __('Value must be greater than 1', 'quform'))
            ));
        }

        if ($perPage > 1000000) {
            wp_send_json(array(
                'type' => 'error',
                'errors' => array('qfb_forms_per_page' => __('Value must be less than 1000000', 'quform'))
            ));
        }

        update_user_meta(get_current_user_id(), 'quform_forms_per_page', $perPage);

        wp_send_json(array(
            'type' => 'success'
        ));
    }
}
