<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Entry_Controller
{
    /**
     * @var Quform_Form_Factory
     */
    protected $formFactory;

    /**
     * @var Quform_Repository
     */
    protected $repository;

    /**
     * @var Quform_Entry_Processor
     */
    protected $entryProcessor;

    /**
     * @param  Quform_Form_Factory     $formFactory
     * @param  Quform_Repository       $repository
     * @param  Quform_Entry_Processor  $entryProcessor
     */
    public function __construct(Quform_Form_Factory $formFactory, Quform_Repository $repository,
                                Quform_Entry_Processor $entryProcessor)
    {
        $this->formFactory = $formFactory;
        $this->repository = $repository;
        $this->entryProcessor = $entryProcessor;
    }

    /**
     * Hook entry point for submitting the edit entry form via Ajax
     */
    public function process()
    {
        if ( ! Quform::isPostRequest() || Quform::get($_POST, 'quform_save_entry') !== '1') {
            return;
        }

        $this->validateProcessRequest();
        $this->handleProcessRequest();
    }

    /**
     * Validate the request for submitting the edit entry form via Ajax
     */
    protected function validateProcessRequest()
    {
        if ( ! isset($_POST['quform_form_id'], $_POST['quform_form_uid'], $_POST['quform_entry_id']) ||
            ! is_numeric($_POST['quform_form_id']) ||
            ! Quform_Form::isValidUniqueId($_POST['quform_form_uid']) ||
            ! is_numeric($_POST['quform_entry_id'])
        ) {
            $this->sendEncodedResponse(array(
                'type' => 'error',
                'message' => __('Bad request', 'quform')
            ));
        }

        if ( ! current_user_can('quform_edit_entries')) {
            $this->sendEncodedResponse(array(
                'type' => 'error',
                'message' => __('Insufficient permissions', 'quform')
            ));
        }

        if ( ! check_ajax_referer('quform_edit_entry_' . $_POST['quform_entry_id'], false, false)) {
            $this->sendEncodedResponse(array(
                'type' => 'error',
                'message' => __('Nonce check failed', 'quform')
            ));
        }
    }

    /**
     * Handle the request for submitting the edit entry form via Ajax
     */
    protected function handleProcessRequest()
    {
        $config = $this->repository->getConfig((int) Quform::get($_POST, 'quform_form_id'));

        if ($config === null) {
            $this->sendEncodedResponse(array(
                'type' => 'error',
                'message' => __('Form not found', 'quform')
            ));
        }

        $config['uniqueId'] = Quform::get($_POST, 'quform_form_uid');
        $config['entryId'] = isset($_POST['quform_entry_id']) && is_numeric($_POST['quform_entry_id']) ? (int) $_POST['quform_entry_id'] : null;
        $config['environment'] = 'editEntry';

        $form = $this->formFactory->create($config);

        $result = $this->entryProcessor->process($form);

        $this->sendEncodedResponse($result);
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
