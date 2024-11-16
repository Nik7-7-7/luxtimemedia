<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Form_Processor
{
    /**
     * @var Quform_Repository
     */
    protected $repository;

    /**
     * @var Quform_Session
     */
    protected $session;

    /**
     * @var Quform_Uploader
     */
    protected $uploader;

    /**
     * @var Quform_Options
     */
    protected $options;

    /**
     * @param  Quform_Repository $repository
     * @param  Quform_Session    $session
     * @param  Quform_Uploader   $uploader
     * @param  Quform_Options    $options
     */
    public function __construct(
        Quform_Repository $repository,
        Quform_Session $session,
        Quform_Uploader $uploader,
        Quform_Options $options
    ) {
        $this->repository = $repository;
        $this->session = $session;
        $this->uploader = $uploader;
        $this->options = $options;
    }

    /**
     * Process the given form
     *
     * @param   Quform_Form  $form
     * @return  array
     */
    public function process(Quform_Form $form)
    {
        // Strip slashes from the submitted data (WP adds them automatically)
        $_POST = wp_unslash($_POST);

        // CSRF check
        if ($this->options->get('csrfProtection') && ( ! isset($_POST['quform_csrf_token']) || $this->session->getToken() != $_POST['quform_csrf_token'])) {
            return apply_filters(
                'quform_csrf_failure_response',
                array(
                    'type' => 'error',
                    'error' => array(
                        'enabled' => true,
                        'title' => __('An error occurred', 'quform'),
                        'content' => __('Refresh the page and try again.', 'quform')
                    )
                ),
                $form
            );
        }

        // Pre-process hooks
        $result = apply_filters('quform_pre_process', array(), $form);
        $result = apply_filters('quform_pre_process_' . $form->getId(), $result, $form);

        if (is_array($result) && ! empty($result)) {
            return $result;
        }

        $result = $this->checkEntryLimits( $form);

        if (is_array($result) && ! empty($result)) {
            return $result;
        }

        $result = $this->checkFormSchedule( $form);

        if (is_array($result) && ! empty($result)) {
            return $result;
        }

        $this->uploader->mergeSessionFiles($form);

        // Set the form element values
        $form->setValues($_POST, true);

        $result = apply_filters('quform_post_set_form_values', array(), $form);
        $result = apply_filters('quform_post_set_form_values_' . $form->getId(), $result, $form);

        if (is_array($result) && ! empty($result)) {
            return $result;
        }

        // Calculate which elements are hidden by conditional logic and which groups are empty
        $form->calculateElementVisibility();

        $form->setCurrentPageById((int) $_POST['quform_current_page_id']);

        // Pre-validate action hooks
        $result = apply_filters('quform_pre_validate', array(), $form);
        $result = apply_filters('quform_pre_validate_' . $form->getId(), $result, $form);

        if (is_array($result) && ! empty($result)) {
            return $result;
        }

        // Moving between pages
        if ($form->hasPages()) {
            if (isset($_POST['quform_submit']) && $_POST['quform_submit'] === 'back') {
                // We want to go to a previous page, so don't validate just find the closest page that isn't hidden by CL
                return array('type' => 'page', 'page' => $form->getNextPageId(true));
            } else {
                // If the current page is valid and there is a next page not hidden by CL, return the next page ID
                if (($nextPageId = $form->getNextPageId()) !== null) {
                    if ($form->getCurrentPage()->isValid()) {
                        return array('type' => 'page', 'page' => $nextPageId);
                    } else {
                        // Return current page errors
                        return array('type' => 'error', 'error' => $form->getGlobalError(), 'errors' => $form->getCurrentPage()->getErrors(), 'page' => $form->getCurrentPage()->getId());
                    }
                }
            }
        }

        // This is the last page so validate the entire form
        list($valid, $firstErrorPage) = $form->isValid();

        if ($valid) {
            // Post-validate action hooks
            $result = apply_filters('quform_post_validate', array(), $form);
            $result = apply_filters('quform_post_validate_' . $form->getId(), $result, $form);

            if (is_array($result) && ! empty($result)) {
                return $result;
            }

            // Save the entry
            $entryId = $this->saveEntry($form);
            $form->setEntryId($entryId);

            $result = apply_filters('quform_post_set_entry_id', array(), $form);
            $result = apply_filters('quform_post_set_entry_id_' . $form->getId(), $result, $form);

            if (is_array($result) && ! empty($result)) {
                return $result;
            }

            // Process any uploads
            $this->uploader->process($form);

            // Save the entry data
            $this->saveEntryData($entryId, $form);

            $result = apply_filters('quform_post_save_entry_data', array(), $form);
            $result = apply_filters('quform_post_save_entry_data_' . $form->getId(), $result, $form);

            if (is_array($result) && ! empty($result)) {
                return $result;
            }

            // Send notification emails
            $this->sendNotifications($form);

            // Set the confirmation to use for this submission
            $form->setConfirmation();

            // Save to custom database table
            $this->saveToCustomDatabase($form);

            // Clear session data for this form
            if ($this->session->has($form->getSessionKey())) {
                $this->session->forget($form->getSessionKey());
            }

            // Post-process action hooks
            $result = apply_filters('quform_post_process', array(), $form);
            $result = apply_filters('quform_post_process_' . $form->getId(), $result, $form);

            if (is_array($result) && ! empty($result)) {
                return $result;
            }

            $result = array(
                'type' => 'success',
                'confirmation' => $form->getConfirmation()->getData()
            );
        } else {
            $result = array(
                'type' => 'error',
                'error' => $form->getGlobalError(),
                'errors' => $form->getErrors(),
                'page' => $firstErrorPage->getId()
            );
        }

        return $result;
    }

    /**
     * Check if the current submission exceeds any entry limit
     *
     * @param   Quform_Form  $form  The current form instance
     * @return  true|array          Return true to proceed with form processing, or a response array to show an error
     */
    protected function checkEntryLimits(Quform_Form $form) {
        if ( ! $this->options->get('saveEntries') || ! $form->config('saveEntry')) {
            return true;
        }

        if (apply_filters('quform_bypass_entry_limits', current_user_can('quform_edit_forms'), $form)) {
            return true;
        }

        if ($form->config('oneEntryPerUser')) {
            $entryExists = false;

            if ($form->config('oneEntryPer') == 'logged-in-user') {
                if (is_user_logged_in()) {
                    $entryExists = $this->repository->entryExistsByFormIdAndCreatedBy(
                        $form->getId(),
                        get_current_user_id()
                    );
                }
            } elseif ($form->config('oneEntryPer') == 'ip-address') {
                $entryExists = $this->repository->entryExistsByFormIdAndIpAddress(
                    $form->getId(),
                    Quform::getClientIp()
                );
            }

            $entryExists = apply_filters('quform_one_entry_per_user_entry_exists', $entryExists, $form);

            if ($entryExists) {
                return apply_filters('quform_one_entry_per_user_error', array(
                    'type' => 'error',
                    'error' => array(
                        'enabled' => true,
                        'title' => '',
                        'content' => $form->getTranslation(
                            'onlyOneSubmissionAllowed',
                            __('Only one submission is allowed.', 'quform')
                        )
                    )
                ), $form);
            }
        }

        if ($form->config('limitEntries') && is_numeric($form->config('entryLimit')) && $form->config('entryLimit') > 0) {
            $entryCount = $this->repository->getEntryCount($form->getId());

            $entryCount = apply_filters('quform_entry_count', $entryCount, $form);
            $entryCount = apply_filters('quform_entry_count_' . $form->getId(), $entryCount, $form);

            if ($entryCount >= $form->config('entryLimit')) {
                return apply_filters('quform_entry_limit_reached_error', array(
                    'type' => 'error',
                    'error' => array(
                        'enabled' => true,
                        'title' => '',
                        'content' => $form->getTranslation(
                            'thisFormIsCurrentlyClosed',
                            __('This form is currently closed for submissions.', 'quform')
                        )
                    )
                ), $form);
            }
        }

        return true;
    }

    /**
     * Check if the current submission is within the schedule
     *
     * @param   Quform_Form  $form  The current form instance
     * @return  true|array          Return true to proceed with form processing, or a response array to show an error
     */
    public function checkFormSchedule(Quform_Form $form)
    {
        if (
            ! $form->config('enableSchedule') ||
            (
                ! Quform::isNonEmptyString('scheduleStart') &&
                ! Quform::isNonEmptyString('scheduleEnd')
            )
        ) {
            return true;
        }

        if (apply_filters('quform_bypass_form_schedule', current_user_can('quform_edit_forms'), $form)) {
            return true;
        }

        try {
            $now = new DateTime('now', new DateTimeZone('UTC'));

            if (Quform::isNonEmptyString($form->config('scheduleStart'))) {
                $start = DateTime::createFromFormat('Y-m-d H:i:s', $form->config('scheduleStart'));

                if ($start instanceof DateTime && $now < $start) {
                    return apply_filters('quform_schedule_start_error', array(
                        'type' => 'error',
                        'error' => array(
                            'enabled' => true,
                            'title' => '',
                            'content' => $form->getTranslation(
                                'formIsNotYetOpenForSubmissions',
                                __('This form is not yet open for submissions.', 'quform')
                            )
                        )
                    ), $form);
                }
            }

            if (Quform::isNonEmptyString($form->config('scheduleEnd'))) {
                $end = DateTime::createFromFormat('Y-m-d H:i:s', $form->config('scheduleEnd'));

                if ($end instanceof DateTime && $now > $end) {
                    return apply_filters('quform_schedule_end_error', array(
                        'type' => 'error',
                        'error' => array(
                            'enabled' => true,
                            'title' => '',
                            'content' => $form->getTranslation(
                                'formIsNoLongerOpenForSubmissions',
                                __('This form is no longer open for submissions.', 'quform')
                            )
                        )
                    ), $form);
                }
            }
        } catch (Exception $e) {
            // Ignore exception, continue processing
        }

        return true;
    }

    /**
     * @param   Quform_Form  $form
     * @return  $this
     */
    protected function sendNotifications(Quform_Form $form)
    {
        foreach ($form->getNotifications() as $notification) {
            if ( ! $notification->config('enabled')) {
                continue;
            }

            if ($notification->config('logicEnabled') && count($notification->config('logicRules'))) {
                if ($form->checkLogicAction($notification->config('logicAction'), $notification->config('logicMatch'), $notification->config('logicRules'))) {
                    $notification->send();
                }
            } else {
                $notification->send();
            }
        }

        return $this;
    }

    /**
     * Create a new entry and return the new entry ID
     *
     * @param   Quform_Form  $form
     * @return  int|null
     */
    protected function saveEntry(Quform_Form $form)
    {
        if ( ! $this->options->get('saveEntries') || ! $form->config('saveEntry')) {
            return null;
        }

        $currentTime = Quform::date('Y-m-d H:i:s', null, new DateTimeZone('UTC'));

        $entry = array(
            'form_id'           => $form->getId(),
            'ip'                => $this->options->get('saveIpAddresses') ? Quform::substr(Quform::getClientIp(), 0, 45) : '',
            'form_url'          => isset($_POST['form_url']) ? Quform::substr($_POST['form_url'], 0, 512) : '',
            'referring_url'     => isset($_POST['referring_url']) ? Quform::substr($_POST['referring_url'], 0, 512) : '',
            'post_id'           => is_numeric($postId = Quform::get($_POST, 'post_id')) && $postId > 0 ? (int) $postId : null,
            'created_by'        => is_user_logged_in() ? (int) Quform::getUserProperty('ID') : null,
            'created_at'        => $currentTime,
            'updated_at'        => $currentTime,
        );

        $entry = $this->repository->saveEntry($entry);

        return $entry['id'];
    }

    /**
     * Save the entry data
     *
     * @param  int          $entryId
     * @param  Quform_Form  $form
     */
    protected function saveEntryData($entryId, Quform_Form $form)
    {
        if ( ! ($entryId > 0) || ! $this->options->get('saveEntries') || ! $form->config('saveEntry')) {
            return;
        }

        $data = array();

        foreach ($form->getRecursiveIterator() as $element) {
            if ($element->config('saveToDatabase') && ! $element->isConditionallyHidden()) {
                if ( ! $element->isEmpty()) {
                    $data[$element->getId()] = $element->getValueForStorage();
                }
            }
        }

        if (count($data)) {
            $this->repository->saveEntryData($entryId, $data);
        }
    }

    /**
     * Save to the custom database table if configured
     *
     * @param Quform_Form $form
     */
    protected function saveToCustomDatabase(Quform_Form $form)
    {
        if ( ! $form->config('databaseEnabled') ||
             ! count($columns = $form->config('databaseColumns')) ||
             ! Quform::isNonEmptyString($table = $form->config('databaseTable'))
        ) {
            return;
        }

        $data = array();
        foreach ($columns as $column) {
            if ( ! Quform::isNonEmptyString($column['name'])) {
                continue;
            }

            $data[$column['name']] = $form->replaceVariables($column['value']);
        }

        if ($form->config('databaseWordpress')) {
            global $wpdb;
            $wpdb->insert($table, $data);
        } else {
            $customWpdb = new wpdb(
                $form->config('databaseUsername'),
                $form->config('databasePassword'),
                $form->config('databaseDatabase'),
                $form->config('databaseHost')
            );

            $customWpdb->insert($table, $data);
        }
    }
}
