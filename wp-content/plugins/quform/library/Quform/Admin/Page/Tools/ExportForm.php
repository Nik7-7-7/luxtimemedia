<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Admin_Page_Tools_ExportForm extends Quform_Admin_Page_Tools
{
    /**
     * @var Quform_Repository
     */
    protected $repository;


    public function init()
    {
        $this->template = QUFORM_TEMPLATE_PATH  . '/admin/tools/export-form.php';
    }

    /**
     * Enqueue the page scripts
     */
    protected function enqueueScripts()
    {
        parent::enqueueScripts();

        wp_enqueue_script('quform-tools-export-form', Quform::adminUrl('js/tools.export-form.min.js'), array('jquery'), QUFORM_VERSION, true);
        wp_localize_script('quform-tools-export-form', 'quformToolsExportFormL10n', $this->getScriptL10n());
    }

    /**
     * JavaScript l10n
     *
     * @return array
     */
    protected function getScriptL10n()
    {
        return array(
            'exportFormNonce' => wp_create_nonce('quform_export_form'),
            'errorExportingForm' => __('An error occurred exporting the form', 'quform'),
            'noFormSelected' => __('No form selected', 'quform')
        );
    }

    /**
     * Set the page title
     *
     * @return string
     */
    protected function getAdminTitle()
    {
        return __('Export Form', 'quform');
    }

    /**
     * Get the HTML for the admin navigation menu
     *
     * @param   array|null  $currentForm  The data for the current form (if any)
     * @param   array       $extra        Extra HTML to add to the nav, the array key is the hook position
     * @return  string
     */
    public function getNavHtml(array $currentForm = null, array $extra = array())
    {
        $extra[40] = sprintf(
            '<div class="qfb-nav-item qfb-nav-page-info"><i class="qfb-nav-page-icon qfb-icon-file-code-o"></i><span class="qfb-nav-page-title">%s</span></div>',
            esc_html__('Export form', 'quform')
        );

        return parent::getNavHtml($currentForm, $extra);
    }

    /**
     * Process this page and send data to the view
     */
    public function process()
    {
        if ( ! current_user_can('quform_export_forms')) {
            wp_die(__( 'You do not have sufficient permissions to access this page.', 'quform'), 403);
        }

        $orderBy = get_user_meta(get_current_user_id(), 'quform_forms_order_by', true);
        $order = get_user_meta(get_current_user_id(), 'quform_forms_order', true);

        $this->view->with(array(
            'forms' => $this->repository->formsToSelectArray(null, $orderBy, $order)
        ));
    }
}
