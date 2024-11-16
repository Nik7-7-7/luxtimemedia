<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Entry_List_Settings
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
     * Hook entry point for saving entries list settings
     */
    public function save()
    {
        $this->validateSaveRequest();
        $this->handleSaveRequest();
    }

    /**
     * Validate the request for saving entries list settings
     */
    protected function validateSaveRequest()
    {
        if ( ! Quform::isPostRequest() ||
            ! isset($_POST['per_page'], $_POST['columns'], $_POST['id']) ||
            ! is_string($_POST['per_page']) ||
            ! is_array($_POST['columns']) ||
            ! is_numeric($_POST['id'])
        ) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Bad request', 'quform')
            ));
        }

        if ( ! check_ajax_referer('quform_save_entries_table_settings', false, false)) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Nonce check failed', 'quform')
            ));
        }
    }

    /**
     * Handle the request for saving entries list settings
     */
    protected function handleSaveRequest()
    {
        if ( $_POST['per_page'] === '') {
            wp_send_json(array(
                'type' => 'error',
                'errors' => array('qfb_entries_per_page' => __('This field is required', 'quform'))
            ));
        }

        if ( ! is_numeric($_POST['per_page'])) {
            wp_send_json(array(
                'type' => 'error',
                'errors' => array('qfb_entries_per_page' => __('Value must be numeric', 'quform'))
            ));
        }

        $perPage = (int) $_POST['per_page'];

        if ($perPage < 1) {
            wp_send_json(array(
                'type' => 'error',
                'errors' => array('qfb_entries_per_page' => __('Value must be greater than 1', 'quform'))
            ));
        }

        if ($perPage > 1000000) {
            wp_send_json(array(
                'type' => 'error',
                'errors' => array('qfb_entries_per_page' => __('Value must be less than 1000000', 'quform'))
            ));
        }

        update_user_meta(get_current_user_id(), 'quform_entries_per_page', $perPage);

        $config = $this->repository->getConfig((int) $_POST['id']);

        if ( ! is_array($config)) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Could not find the form config', 'quform')
            ));
        }

        $config['entriesTableColumns'] = array_map('sanitize_key', wp_unslash($_POST['columns']));

        $this->repository->save($config);

        $labels = isset($_POST['labels']) && is_array($_POST['labels']) ? wp_unslash($_POST['labels']) : array();

        $this->repository->setFormEntryLabels($config['id'], $labels);

        wp_send_json(array(
            'type' => 'success'
        ));
    }

    /**
     * Hook entry point for setting entry labels on an entry
     */
    public function setEntryLabels()
    {
        $this->validateSetEntryLabelsRequest();
        $this->handleSetEntryLabelsRequest();
    }

    /**
     * Validate the request for setting entry labels on an entry
     */
    protected function validateSetEntryLabelsRequest()
    {
        if ( ! Quform::isPostRequest() ||
            ! isset($_POST['_ajax_nonce'], $_POST['entry_label_id'], $_POST['entry_id'], $_POST['adding']) ||
            ! is_string($_POST['_ajax_nonce']) ||
            ! is_numeric($_POST['entry_label_id']) ||
            ! is_numeric($_POST['entry_id']) ||
            ! in_array($_POST['adding'], array('true', 'false'), true)
        ) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Bad request', 'quform')
            ));
        }

        if ( ! check_ajax_referer('quform_set_entry_labels', false, false)) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Nonce check failed', 'quform')
            ));
        }
    }

    /**
     * Handle the request for setting entry labels on an entry
     */
    protected function handleSetEntryLabelsRequest()
    {
        if ($_POST['adding'] == 'false') {
            $this->repository->deleteEntryEntryLabel((int) $_POST['entry_id'], (int) $_POST['entry_label_id']);
        } else {
            $this->repository->addEntryEntryLabel((int) $_POST['entry_id'], (int) $_POST['entry_label_id']);
        }

        wp_send_json(array(
            'type' => 'success'
        ));
    }
}
