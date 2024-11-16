<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Admin_Page_Tools_ImportForm extends Quform_Admin_Page_Tools
{
    /**
     * Enqueue the page scripts
     */
    protected function enqueueScripts()
    {
        parent::enqueueScripts();

        wp_enqueue_script('quform-tools-import-form', Quform::adminUrl('js/tools.import-form.min.js'), array('jquery'), QUFORM_VERSION, true);
        wp_localize_script('quform-tools-import-form', 'quformToolsImportFormL10n', $this->getScriptL10n());
    }

    /**
     * JavaScript l10n
     *
     * @return array
     */
    protected function getScriptL10n()
    {
        return array(
            'importFormNonce' => wp_create_nonce('quform_import_form'),
            'errorImportingForm' => __('An error occurred importing the form', 'quform')
        );
    }

    public function init()
    {
        $this->template = QUFORM_TEMPLATE_PATH . '/admin/tools/import-form.php';
    }

    /**
     * Set the page title
     *
     * @return string
     */
    protected function getAdminTitle()
    {
        return __('Import Form', 'quform');
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
            '<div class="qfb-nav-item qfb-nav-page-info"><i class="qfb-nav-page-icon qfb-mdi qfb-mdi-playlist_add"></i><span class="qfb-nav-page-title">%s</span></div>',
            esc_html__('Import form', 'quform')
        );

        return parent::getNavHtml($currentForm, $extra);
    }

    /**
     * Process this page
     */
    public function process()
    {
        if ( ! current_user_can('quform_import_forms')) {
            wp_die(__( 'You do not have sufficient permissions to access this page.', 'quform'), 403);
        }
    }
}
