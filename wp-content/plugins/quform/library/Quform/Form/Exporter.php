<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Form_Exporter
{
    /**
     * @var Quform_Repository
     */
    protected $repository;

    /**
     * @param Quform_Repository $repository
     */
    public function __construct(Quform_Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Handle the Ajax request to get the form export data
     *
     * Sends a JSON response, ending execution
     */
    public function export()
    {
        $this->validateExportRequest();

        $config = $this->repository->getConfig((int) $_POST['id']);

        if ( ! is_array($config)) {
            wp_send_json(array(
                'type' => 'error',
                'errors' => array(
                    'qfb-export-form' => __('Form not found', 'quform')
                )
            ));
        }

        wp_send_json(array(
            'type' => 'success',
            'data' => base64_encode(serialize($config))
        ));
    }

    /**
     * Validate the Ajax request to get the form export data
     *
     * Sends a JSON response if the request is invalid, ending execution
     */
    protected function validateExportRequest()
    {
        if ( ! Quform::isPostRequest() || ! isset($_POST['id']) || ! is_numeric($_POST['id'])) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Bad request', 'quform')
            ));
        }

        if ( ! current_user_can('quform_export_forms')) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Insufficient permissions', 'quform')
            ));
        }

        if ( ! check_ajax_referer('quform_export_form', false, false)) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Nonce check failed', 'quform')
            ));
        }
    }
}
