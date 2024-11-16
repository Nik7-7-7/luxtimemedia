<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Admin_Page_Dashboard extends Quform_Admin_Page
{
    /**
     * @var Quform_Options
     */
    protected $options;

    /**
     * @param  Quform_ViewFactory  $viewFactory
     * @param  Quform_Repository   $repository
     * @param  Quform_Options      $options
     */
    public function __construct(Quform_ViewFactory $viewFactory, Quform_Repository $repository, Quform_Options $options)
    {
        parent::__construct($viewFactory, $repository);

        $this->options = $options;
    }

    public function init()
    {
        $this->template = QUFORM_TEMPLATE_PATH . '/admin/dashboard.php';
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
        ob_start();
        ?>
        <div class="qfb-nav-item qfb-nav-page-info"><i class="qfb-nav-page-icon qfb-mdi qfb-mdi-dashboard"></i><span class="qfb-nav-page-title"><?php esc_html_e('Dashboard', 'quform'); ?></span></div>
        <?php

        return parent::getNavHtml($currentForm, array(40 => ob_get_clean()));
    }

    /**
     * Process this page and send data to the view
     */
    public function process()
    {
        if ( ! current_user_can('quform_view_dashboard')) {
            wp_die(__( 'You do not have sufficient permissions to access this page.', 'quform'), 403);
        }

        $recentEntries = $this->repository->getRecentEntries(10);

        $unreadCount = 0;

        foreach ($recentEntries as $recentEntry) {
            if ($recentEntry['unread'] == '1') {
                $unreadCount++;
            }
        }

        $this->view->with(array(
            'options' => $this->options,
            'forms' => $this->repository->getForms(array('limit' => 9)),
            'unreadCount' => $unreadCount,
            'recentEntries' => $recentEntries,
            'tools' => $this->getTools(),
        ));
    }

    /**
     * Get the array of tools that the user has permission to use
     *
     * @return array
     */
    protected function getTools()
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
            )
        );

        foreach ($tools as $key => $tool) {
            if ( ! current_user_can($tool['cap'])) {
                unset($tools[$key]);
            }
        }

        return $tools;
    }
}
