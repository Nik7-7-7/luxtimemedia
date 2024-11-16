<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Form_Controller
{
    /**
     * @var Quform_Repository
     */
    protected $repository;

    /**
     * @var Quform_Form_Factory
     */
    protected $formFactory;

    /**
     * @var Quform_Form_Processor
     */
    protected $processor;

    /**
     * @var Quform_Session
     */
    protected $session;

    /**
     * @var Quform_Uploader
     */
    protected $uploader;

    /*
     * Form counter to differentiate multiple instances of the same form
     *
     * @var int
     */
    protected $count = 0;

    /**
     * Store used unique IDs to avoid conflicts
     *
     * @var array
     */
    protected $uniqueIds = array();

    /**
     * @param  Quform_Form_Factory   $formFactory
     * @param  Quform_Repository     $repository
     * @param  Quform_Form_Processor $processor
     * @param  Quform_Session        $session
     * @param  Quform_Uploader       $uploader
     */
    public function __construct(Quform_Form_Factory $formFactory, Quform_Repository $repository,
                                Quform_Form_Processor $processor, Quform_Session $session, Quform_Uploader $uploader)
    {
        $this->formFactory = $formFactory;
        $this->repository = $repository;
        $this->processor = $processor;
        $this->session = $session;
        $this->uploader = $uploader;
    }

    /**
     * Display the form, also handle non-Ajax form processing
     *
     * @param   array        $options
     * @return  string|void
     */
    public function form(array $options = array())
    {
        $options = wp_parse_args($options, array(
            'id' => '',
            'values' => '',
            'content' => '',
            'popup' => false,
            'options' => '',
            'width' => '',
            'show_title' => true,
            'show_description' => true
        ));

        $config = $this->repository->getConfig((int) $options['id']);

        if ( ! is_array($config)) {
            return;
        }

        $this->count++;
        $config['count'] = $this->count;

        $processingThisForm = Quform::isPostRequest() && Quform::get($_POST, 'quform_form_id') == $config['id'] && Quform::get($_POST, 'quform_count') == $this->count;

        if ($processingThisForm && Quform_Form::isValidUniqueId(Quform::get($_POST, 'quform_form_uid'))) {
            $uniqueId = Quform::get($_POST, 'quform_form_uid');
        } else {
            $uniqueId = $this->generateUniqueId();
        }

        $config['uniqueId'] = $uniqueId;
        $this->uniqueIds[] = $uniqueId;

        if (is_string($options['values'])) {
            $options['values'] = join('&', explode('&amp;', $options['values']));
        }

        $config['dynamicValues'] = $options['values'];

        $form = $this->formFactory->create($config);

        if ( ! ($form instanceof Quform_Form) || $form->config('trashed')) {
            return;
        }

        if ( ! $form->isActive()) {
            return do_shortcode($form->config('inactiveMessage'));
        }

        $output = '';

        if ($processingThisForm) {
            $result = $this->processor->process($form);

            if ($result['type'] == 'page') {
                $this->uploader->saveUploadedFilesIntoSession($form);
                $form->setCurrentPageById($result['page']);
            } else if ($result['type'] == 'success') {
                $confirmation = $result['confirmation'];

                switch ($confirmation['type']) {
                    case 'message':
                        // Show the success message and reset the form values
                        $form->setSubmitted(true);
                        $form->reset();
                        break;
                    case 'message-redirect-page':
                    case 'message-redirect-url':
                        // Show the success message
                        $form->setSubmitted(true);
                        $output .= sprintf('<meta http-equiv="refresh" content="%d;URL=\'%s\'">', esc_attr($confirmation['redirectDelay']), esc_url($confirmation['redirectUrl']));
                        break;
                    case 'redirect-page':
                    case 'redirect-url':
                        $output .= sprintf('<meta http-equiv="refresh" content="0;URL=\'%s\'">', esc_url($confirmation['redirectUrl']));
                        break;
                    case 'reload':
                        $output .= '<meta http-equiv="refresh" content="0">';
                        break;
                }
            } else if ($result['type'] == 'error') {
                // If the first error is on a previous page, go there
                if (isset($result['page']) && $form->getCurrentPage()->getId() != $result['page']) {
                    $form->setCurrentPageById($result['page']);
                }

                // Set up the non-JS error global form error message
                if (isset($result['error']) && is_array($result['error'])) {
                    if (isset($result['error']['enabled']) && is_bool($result['error']['enabled'])) {
                        $form->setConfig('errorEnabled', $result['error']['enabled']);
                    }

                    if (isset($result['error']['title']) && is_string($result['error']['title'])) {
                        $form->setConfig('errorTitle', $result['error']['title']);
                    }

                    if (isset($result['error']['content']) && is_string($result['error']['content'])) {
                        $form->setConfig('errorContent', $result['error']['content']);
                    }
                }

                // Show the global form error message if enabled
                if ($form->config('errorEnabled')) {
                    $form->setShowGlobalError(true);
                }
            }
        }

        $outputOverride = '';

        if (
            !apply_filters('quform_bypass_entry_limits', current_user_can('quform_edit_forms'), $form) &&
            $form->config('limitEntries') &&
            is_numeric($form->config('entryLimit')) &&
            $form->config('entryLimit') > 0
        ) {
            $entryCount = $this->repository->getEntryCount($form->getId());

            $entryCount = apply_filters('quform_entry_count', $entryCount, $form);
            $entryCount = apply_filters('quform_entry_count_' . $form->getId(), $entryCount, $form);

            if ($entryCount >= $form->config('entryLimit')) {
                $outputOverride = do_shortcode(shortcode_unautop(wpautop($form->config('entryLimitReachedMessage'))));
            }
        }

        if (
            !apply_filters('quform_bypass_form_schedule', current_user_can('quform_edit_forms'), $form) &&
            $form->config('enableSchedule')
        ) {
            try {
                $now = new DateTime('now', new DateTimeZone('UTC'));

                if (Quform::isNonEmptyString($form->config('scheduleStart'))) {
                    $start = DateTime::createFromFormat('Y-m-d H:i:s', $form->config('scheduleStart'));

                    if ($start instanceof DateTime && $now < $start) {
                        $outputOverride = do_shortcode(shortcode_unautop(wpautop($form->config('scheduleStartMessage'))));
                    }
                }

                if (Quform::isNonEmptyString($form->config('scheduleEnd'))) {
                    $end = DateTime::createFromFormat('Y-m-d H:i:s', $form->config('scheduleEnd'));

                    if ($end instanceof DateTime && $now > $end) {
                        $outputOverride = do_shortcode(shortcode_unautop(wpautop($form->config('scheduleEndMessage'))));
                    }
                }
            } catch (Exception $e) {
                // Ignore exception, show the form as normal
            }
        }

        $output .= $options['popup'] ? $form->renderPopup($options, $outputOverride) : $form->render($options, $outputOverride);

        return $output;
    }

    /**
     * Get the entry limit for the given form ID
     *
     * @param   int     $formId  The ID of the form
     * @return  string           The maximum entry limit number, an empty string or infinity sign
     */
    public function getEntryLimit($formId) {
        $config = $this->repository->getConfig($formId);

        if ( ! is_array($config)) {
            return '';
        }

        $form = $this->formFactory->create($config);

        if ( ! ($form instanceof Quform_Form) || $form->config('trashed')) {
            return '';
        }

        if ($form->config('limitEntries') && is_numeric($form->config('entryLimit')) && $form->config('entryLimit') > 0) {
            return $form->config('entryLimit');
        }

        return '∞';
    }

    /**
     * Get the entry count for the given form ID
     *
     * @param   int  $formId  The ID of the form
     * @return  int           The number of entries
     */
    public function getEntryCount($formId) {
        $config = $this->repository->getConfig($formId);

        if ( ! is_array($config)) {
            return '';
        }

        $form = $this->formFactory->create($config);

        if ( ! ($form instanceof Quform_Form) || $form->config('trashed')) {
            return '';
        }

        $entryCount = $this->repository->getEntryCount($formId);

        $entryCount = apply_filters('quform_entry_count', $entryCount, $form);
        $entryCount = apply_filters('quform_entry_count_' . $form->getId(), $entryCount, $form);

        return $entryCount;
    }

    /**
     * Get the number of entries remaining for the given form ID
     *
     * @param   int         $formId  The ID of the form
     * @return  int|string           The number of entries remaining, an empty string or infinity sign
     */
    public function getEntriesRemaining($formId) {
        $config = $this->repository->getConfig($formId);

        if ( ! is_array($config)) {
            return '';
        }

        $form = $this->formFactory->create($config);

        if ( ! ($form instanceof Quform_Form) || $form->config('trashed')) {
            return '';
        }

        if ($form->config('limitEntries') && is_numeric($form->config('entryLimit')) && $form->config('entryLimit') > 0) {
            $entryCount = $this->repository->getEntryCount($formId);

            $entryCount = apply_filters('quform_entry_count', $entryCount, $form);
            $entryCount = apply_filters('quform_entry_count_' . $form->getId(), $entryCount, $form);

            $entryLimit = (int) $form->config('entryLimit');

            return $entryCount < $entryLimit ? $entryLimit - $entryCount : 0;
        }

        return '∞';
    }

    /**
     * Hook entry point for submitting the form via Ajax
     */
    public function process()
    {
        if ( ! Quform::isPostRequest() || ! isset($_POST['quform_ajax'])) {
            return;
        }

        $this->validateProcessRequest();
        $this->handleProcessRequest();
    }

    /**
     * Validate the request for submitting the form via Ajax
     */
    protected function validateProcessRequest()
    {
        if ( ! isset($_POST['quform_form_id'], $_POST['quform_form_uid']) ||
             ! is_numeric($_POST['quform_form_id']) ||
             ! Quform_Form::isValidUniqueId($_POST['quform_form_uid'])
        ) {
            $this->sendEncodedResponse(array(
                'type' => 'error',
                'error' => array(
                    'enabled' => true,
                    'title' => __('An error occurred', 'quform'),
                    'content' => __('Bad request', 'quform')
                )
            ));
        }
    }

    /**
     * Handle the request for submitting the form via Ajax
     */
    protected function handleProcessRequest()
    {
        $config = $this->repository->getConfig((int) $_POST['quform_form_id']);

        if ( ! is_array($config)) {
            $this->sendEncodedResponse(array(
                'type' => 'error',
                'error' => array(
                    'enabled' => true,
                    'title' => __('An error occurred', 'quform'),
                    'content' => __('Form not found', 'quform')
                )
            ));
        }

        $config['uniqueId'] = $_POST['quform_form_uid'];

        $form = $this->formFactory->create($config);

        if ( ! ($form instanceof Quform_Form) || $form->config('trashed')) {
            $this->sendEncodedResponse(array(
                'type' => 'error',
                'error' => array(
                    'enabled' => true,
                    'title' => __('An error occurred', 'quform'),
                    'content' => __('Form does not exist', 'quform')
                )
            ));
        }

        if ( ! $form->isActive()) {
            $this->sendEncodedResponse(array(
                'type' => 'error',
                'error' => array(
                    'enabled' => true,
                    'title' => __('An error occurred', 'quform'),
                    'content' => __('Form is not active', 'quform')
                )
            ));
        }

        $this->sendEncodedResponse($this->processor->process($form));
    }

    /**
     * Generate a unique ID that is not currently being used
     *
     * @return string
     */
    protected function generateUniqueId()
    {
        $uniqueId = Quform_Form::generateUniqueId();

        while (in_array($uniqueId, $this->uniqueIds) || $this->session->has(sprintf('quform-%s', $uniqueId))) {
            $uniqueId = Quform_Form::generateUniqueId();
        }

        return $uniqueId;
    }

    /**
     * Hook entry point for the support page caching request
     */
    public function supportPageCaching()
    {
        $this->validateSupportPageCachingRequest();
        $this->handleSupportPageCachingRequest();
    }

    /**
     * Validate the request to support page caching
     */
    protected function validateSupportPageCachingRequest()
    {
        if ( ! isset($_GET['forms']) || ! is_array($_GET['forms'])) {
            wp_send_json(array('type' => 'error'));
        }
    }

    /**
     * Handle the request to support page caching
     */
    protected function handleSupportPageCachingRequest()
    {
        $forms = array();

        foreach ($_GET['forms'] as $oldUniqueId) {
            $forms[$oldUniqueId] = $this->generateUniqueId();
        }

        wp_send_json(array(
            'type' => 'success',
            'token' => $this->session->getToken(),
            'forms' => $forms
        ));
    }

    /**
     * JSON encodes the given data and wraps it in a &lt;textarea&gt; tag
     *
     * Escaping is necessary to counteract the fact that wrapping the JSON response in a textarea decodes HTML entities
     *
     * @see http://malsup.com/jquery/form/#file-upload
     *
     * @param array $response
     */
    protected function sendEncodedResponse($response)
    {
        if ( ! headers_sent()) {
            header('Content-Type: text/html; charset=' . get_option('blog_charset'));
        }

        echo '<textarea>' . Quform::escape(wp_json_encode($response)) . '</textarea>';

        // Call the die handler instead of exit to facilitate unit tests
        call_user_func(apply_filters('wp_die_ajax_handler', '_ajax_wp_die_handler'), '');
    }
}
