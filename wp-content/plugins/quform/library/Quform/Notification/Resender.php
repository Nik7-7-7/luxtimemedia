<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Notification_Resender
{
    /**
     * @var Quform_Repository
     */
    protected $repository;

    /**
     * @var Quform_Form_Factory
     */
    protected $factory;

    /**
     * @param  Quform_Repository    $repository
     * @param  Quform_Form_Factory  $factory
     */
    public function __construct(Quform_Repository $repository, Quform_Form_Factory $factory)
    {
        $this->repository = $repository;
        $this->factory = $factory;
    }

    /**
     * Validate the Ajax request to resend notifications
     */
    protected function validateResendRequest()
    {
        if (
            ! Quform::isPostRequest() ||
            ! isset($_POST['data']) ||
            ! is_string($_POST['data'])
        ) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Bad request', 'quform')
            ));
        }

        if ( ! current_user_can('quform_resend_notifications')) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Insufficient permissions', 'quform')
            ));
        }

        if ( ! check_ajax_referer('quform_resend_notifications', false, false)) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Nonce check failed', 'quform')
            ));
        }
    }

    /**
     * Handle the Ajax request to resend notifications
     */
    public function resend()
    {
        $this->validateResendRequest();

        $data = json_decode(wp_unslash($_POST['data']), true);

        if ( ! is_array($data)) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Bad request', 'quform')
            ));
        }

        $entryId = isset($data['eid']) && is_numeric($data['eid']) ? (int) $data['eid'] : null;

        $entry = $this->repository->findEntry($entryId);

        if ( ! is_array($entry)) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Entry not found', 'quform')
            ));
        }

        $identifiers = isset($data['identifiers']) && is_array($data['identifiers']) ? array_map('sanitize_key', $data['identifiers']) : array();

        if ( ! count($identifiers)) {
            wp_send_json(array(
                'type' => 'error',
                'errors' => array(
                    'qfb-resend-notifications-identifiers' => __('This field is required', 'quform')
                )
            ));
        }

        $formId = $this->repository->getFormIdFromEntryId($entryId);

        $config = $this->repository->getConfig($formId);

        if ( ! is_array($config)) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Form not found', 'quform')
            ));
        }

        $config['entryId'] = $entryId;
        $config['environment'] = 'viewEntry';

        do_action('quform_resender_before_create_form', $config, $entry);
        do_action("quform_resender_before_create_form_$formId", $config, $entry);

        $form = $this->factory->create($config);

        $form->setValues(array(), true);

        foreach ($entry as $key => $value) {
            if (preg_match('/element_(\d+)/', $key, $matches)) {
                $form->setValueFromStorage($matches[1], $value);
                unset($entry[$key]);
            }
        }

        do_action('quform_pre_resend_notifications', $form, $entry);

        foreach ($form->getNotifications() as $notification) {
            if (in_array($notification->getIdentifier(), $identifiers)) {
                do_action('quform_pre_resend_notification', $notification, $form, $entry);
                do_action('quform_pre_resend_notification_' . $notification->getIdentifier(), $notification, $form, $entry);

                $notification->send();

                do_action('quform_post_resend_notification', $notification, $form, $entry);
                do_action('quform_post_resend_notification_' . $notification->getIdentifier(), $notification, $form, $entry);
            }
        }

        do_action('quform_post_resend_notifications', $form, $entry);

        wp_send_json(array(
            'type' => 'success',
            'message' => __('The notifications have been sent successfully.', 'quform')
        ));
    }
}
