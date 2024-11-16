<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Form_Importer
{
    /**
     * @var Quform_Repository
     */
    protected $repository;

    /**
     * @var Quform_ScriptLoader
     */
    protected $scriptLoader;

    /**
     * @var Quform_Builder
     */
    protected $builder;

    /**
     * @param  Quform_Repository    $repository
     * @param  Quform_Builder       $builder
     * @param  Quform_ScriptLoader  $scriptLoader
     */
    public function __construct(Quform_Repository $repository, Quform_Builder $builder, Quform_ScriptLoader $scriptLoader)
    {
        $this->repository = $repository;
        $this->builder = $builder;
        $this->scriptLoader = $scriptLoader;
    }

    /**
     * Handle the Ajax request to import a form
     *
     * Sends a JSON response, ending execution
     */
    public function import()
    {
        $this->validateImportRequest();

        @set_time_limit(3600);

        $config = base64_decode(trim(stripslashes($_POST['config'])));
        $config = maybe_unserialize($config);

        if ( ! is_array($config)) {
            wp_send_json(array(
                'type' => 'error',
                'errors' => array(
                    'qfb-import-form-data' => __('The import data is invalid', 'quform')
                )
            ));
        }

        if ( ! isset($config['notifications'])) {
            wp_send_json(array(
                'type' => 'error',
                'errors' => array(
                    'qfb-import-form-data' => __('The import data does not appear to be a valid form. If the form is from Quform 1.x, please import the form at Forms &rarr; Tools &rarr; Migrate &rarr; Import a single Quform 1.x form.', 'quform')
                )
            ));
        }

        $config = $this->builder->sanitizeForm($config);
        $config = $this->repository->add($config);

        if ( ! is_array($config)) {
            wp_send_json(array(
                'type' => 'error',
                'message' => wp_kses(sprintf(
                    /* translators: %1$s: open link tag, %2$s: close link tag */
                    __('Failed to insert into database, check the %1$serror log%2$s for more information', 'quform'),
                    '<a href="https://support.themecatcher.net/quform-wordpress-v2/guides/advanced/enabling-debug-logging">',
                    '</a>'
                ), array('a' => array('href' => array())))
            ));
        }

        $this->scriptLoader->rebuildScriptCache();

        wp_send_json(array(
            'type' => 'success',
            'message' => wp_kses(sprintf(
                /* translators: %1$s: open link tag, %2$s: close link tag */
                __('Form imported successfully, %1$sedit the form%2$s', 'quform'),
                '<a href="' . esc_url(admin_url('admin.php?page=quform.forms&sp=edit&id=' . $config['id'])) . '">',
                '</a>'
            ), array('a' => array('href' => array())))
        ));
    }

    /**
     * Validate the Ajax request to import a form
     *
     * Sends a JSON response if the request is invalid, ending execution
     */
    protected function validateImportRequest()
    {
        if ( ! isset($_POST['config']) || ! Quform::isNonEmptyString($_POST['config'])) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Bad request', 'quform')
            ));
        }

        if ( ! current_user_can('quform_import_forms')) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Insufficient permissions', 'quform')
            ));
        }

        if ( ! check_ajax_referer('quform_import_form', false, false)) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Nonce check failed', 'quform')
            ));
        }
    }
}
