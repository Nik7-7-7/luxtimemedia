<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Admin_Page_Tools_Migrate extends Quform_Admin_Page_Tools
{
    /**
     * @var Quform_Migrator
     */
    protected $migrator;

    /**
     * @param  Quform_ViewFactory  $viewFactory
     * @param  Quform_Repository   $repository
     * @param  Quform_Migrator     $migrator
     */
    public function __construct(Quform_ViewFactory $viewFactory, Quform_Repository $repository, Quform_Migrator $migrator)
    {
        parent::__construct($viewFactory, $repository);

        $this->migrator = $migrator;
    }

    /**
     * Enqueue the page scripts
     */
    protected function enqueueScripts()
    {
        parent::enqueueScripts();

        wp_enqueue_script('quform-tools-migrate', Quform::adminUrl('js/tools.migrate.min.js'), array('jquery'), QUFORM_VERSION, true);
        wp_localize_script('quform-tools-migrate', 'quformToolsMigrateL10n', $this->getScriptL10n());
    }

    /**
     * JavaScript l10n
     *
     * @return array
     */
    protected function getScriptL10n()
    {
        return array(
            'migrateNonce' => wp_create_nonce('quform_migrate_form'),
            'pleaseWait' => __('Please wait', 'quform'),
            /* translators: %1$d: current form number, %2$d: total number of forms */
            'processingFormXOfY' => __('Processing form %1$d of %2$d', 'quform'),
            'migrateImportFormNonce' => wp_create_nonce('quform_migrate_import_form'),
            'migrateSettingsNonce' => wp_create_nonce('quform_migrate_settings'),
            'errorImportingForm' => __('An error occurred importing the form', 'quform'),
            'stopping' => __('Stopping...', 'quform'),
            'processing' => __('Processing...', 'quform'),
            'stopMigration' => __('Stop migration', 'quform'),
            /* translators: %s: the error message */
            'errorMigratingKeys' => __('Error migrating keys: %s', 'quform'),
            /* translators: %1$d: the form ID, %2$s: the error message */
            'errorMigratingForm' => __('Error migrating form ID %1$d: %2$s', 'quform'),
            'migrationCompleted' => __('Migration completed.', 'quform'),
            'migrationStopped' => __('Migration stopped.', 'quform'),
            'migrationError' => __('Migration error.', 'quform'),
            /* translators: %s: the error message */
            'errorStartingMigration' => __('An error occurred starting the migration: %s', 'quform'),
        );
    }

    public function init()
    {
        $this->template = QUFORM_TEMPLATE_PATH . '/admin/tools/migrate.php';
    }

    /**
     * Set the page title
     *
     * @return string
     */
    protected function getAdminTitle()
    {
        return __('Migrate', 'quform');
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
            '<div class="qfb-nav-item qfb-nav-page-info"><i class="qfb-nav-page-icon qfb-icon-suitcase"></i><span class="qfb-nav-page-title">%s</span></div>',
            esc_html__('Migrate', 'quform')
        );

        return parent::getNavHtml($currentForm, $extra);
    }

    /**
     * Process this page
     */
    public function process()
    {
        if ( ! current_user_can('quform_full_access')) {
            wp_die(__( 'You do not have sufficient permissions to access this page.', 'quform'), 403);
        }

        $oldForms = $this->migrator->get1xForms();

        $this->view->with(array(
            'oldForms' => $oldForms
        ));
    }
}
