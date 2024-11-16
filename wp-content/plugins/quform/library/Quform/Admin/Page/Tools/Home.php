<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Admin_Page_Tools_Home extends Quform_Admin_Page_Tools
{
    public function init()
    {
        $this->template = QUFORM_TEMPLATE_PATH . '/admin/tools/home.php';
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
            '<div class="qfb-nav-item qfb-nav-page-info"><i class="qfb-nav-page-icon qfb-mdi qfb-mdi-build"></i><span class="qfb-nav-page-title">%s</span></div>',
            esc_html__('Tools', 'quform')
        );

        return parent::getNavHtml($currentForm, $extra);
    }

    /**
     * Process this page and send data to the view
     */
    public function process()
    {
        $tools = array(
            'export.entries' => array(
                'title' => __('Export Entries', 'quform'),
                'cap' => 'quform_export_entries',
                'url' => admin_url('admin.php?page=quform.tools&sp=export.entries'),
                'icon' => '<i class="qfb-icon qfb-icon-file-excel-o"></i>'
            ),
            'export.form' => array(
                'title' => __('Export Form', 'quform'),
                'cap' => 'quform_export_forms',
                'url' => admin_url('admin.php?page=quform.tools&sp=export.form'),
                'icon' => '<i class="qfb-icon qfb-icon-file-code-o"></i>'
            ),
            'import.form' => array(
                'title' => __('Import Form', 'quform'),
                'cap' => 'quform_import_forms',
                'url' => admin_url('admin.php?page=quform.tools&sp=import.form'),
                'icon' => '<i class="qfb-mdi qfb-mdi-playlist_add"></i>'
            ),
            'migrate' => array(
                'title' => __('Migrate', 'quform'),
                'cap' => 'quform_full_access',
                'url' => admin_url('admin.php?page=quform.tools&sp=migrate'),
                'icon' => '<i class="qfb-icon qfb-icon-suitcase"></i>'
            ),
            'uninstall' => array(
                'title' => __('Uninstall', 'quform'),
                'cap' => 'activate_plugins',
                'url' => admin_url('admin.php?page=quform.tools&sp=uninstall'),
                'icon' => '<i class="qfb-icon qfb-icon-trash-o"></i>'
            )
        );

        foreach ($tools as $key => $tool) {
            if ( ! current_user_can($tool['cap'])) {
                unset($tools[$key]);
            }
        }

        $this->view->with(compact('tools'));
    }
}
