<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Dispatcher
{
    /**
     * @var Quform_Container
     */
    protected $container;

    /**
     * @param Quform_Container $container
     */
    public function __construct(Quform_Container $container)
    {
        $this->container = $container;
    }

    /**
     * Link the services to WordPress hooks
     *
     * @return $this
     */
    public function bootstrap()
    {
        // Register core themes (must be before upgrade check)
        add_action('init', array($this->container['themes'], 'registerCoreThemes'), 1);

        // Check if any upgrades need to be processed
        add_action('init', array($this->container['upgrader'], 'upgradeCheck'), 3);

        // Session
        add_action('init', array($this->container['session'], 'start'), 5);
        add_action('shutdown', array($this->container['session'], 'save'));
        add_action('quform_session_gc', array($this->container['session'], 'gc'));

        // Translations
        add_action('init', array($this->container['translations'], 'load'));

        // File uploads
        add_action('init', array($this->container['uploader'], 'upload'));
        add_action('quform_upload_cleanup', array($this->container['uploader'], 'cleanup'));

        // Scripts
        add_action('wp_head', array($this->container['scriptLoader'], 'printHeadScripts'), 0);
        add_action('wp_enqueue_scripts', array($this->container['scriptLoader'], 'enqueue'));

        // Email
        add_action('quform_pre_send_notification', array('Quform_Notification', 'addAltBody'), 15, 3);

        // Form processing
        add_action('wp_loaded', array($this->container['formController'], 'process'));
        add_action('wp_ajax_quform_support_page_caching',  array($this->container['formController'], 'supportPageCaching'));
        add_action('wp_ajax_nopriv_quform_support_page_caching',  array($this->container['formController'], 'supportPageCaching'));

        // Shortcodes
        add_shortcode('quform', array($this->container['shortcode'], 'form'));
        add_shortcode('quform_popup', array($this->container['shortcode'], 'popup'));
        add_shortcode('quform_entry_limit', array($this->container['shortcode'], 'entryLimit'));
        add_shortcode('quform_entry_count', array($this->container['shortcode'], 'entryCount'));
        add_shortcode('quform_entries_remaining', array($this->container['shortcode'], 'entriesRemaining'));

        // Widgets
        add_action('widgets_init', array('Quform_Widget_Form', 'register'));
        add_action('widgets_init', array('Quform_Widget_Popup', 'register'));

        // Captcha
        add_action('wp_ajax_quform_regenerate_captcha', array($this->container['captcha'], 'regenerate'));
        add_action('wp_ajax_nopriv_quform_regenerate_captcha', array($this->container['captcha'], 'regenerate'));

        // Toolbar menu
        add_action('admin_bar_menu', array($this->container['toolbar'], 'addNodes'), 90);
        add_action('admin_head', array($this->container['toolbar'], 'printStyles'));
        add_action('wp_head', array($this->container['toolbar'], 'printStyles'));

        // Permissions for users with full plugin access
        add_filter('user_has_cap', array($this->container['permissions'], 'fullAccessCheck'), 10, 3);

        // Editor block
        add_action('init', array($this->container['block'], 'register'));

        if (is_admin() || defined('QUFORM_TESTING')) {
            // Deactivation hooks
            register_deactivation_hook(QUFORM_BASENAME, array($this->container['uploader'], 'deactivate'));
            register_deactivation_hook(QUFORM_BASENAME, array($this->container['session'], 'deactivate'));

            // Menus and pages
            add_action('admin_menu', array($this->container['adminPageController'], 'createMenus'));
            add_action('current_screen', array($this->container['adminPageController'], 'process'));
            add_filter('admin_title', array($this->container['adminPageController'], 'setAdminTitle'));
            add_filter('admin_body_class', array($this->container['adminPageController'], 'addBodyClass'));
            add_action('admin_enqueue_scripts', array($this->container['adminPageController'], 'enqueueAssets'));

            // Settings
            add_action('wp_ajax_quform_verify_purchase_code', array($this->container['license'], 'verifyPurchaseCode'));
            add_action('wp_ajax_quform_save_settings', array($this->container['settings'], 'save'));
            add_action('wp_ajax_quform_rebuild_script_cache', array($this->container['settings'], 'rebuildScriptCache'));
            add_action('wp_ajax_quform_set_popup_enabled', array($this->container['settings'], 'enablePopup'));
            add_filter('wp_ajax_quform_settings_search_posts', array($this->container['settings'], 'searchPosts'));

            // Preview
            add_action('admin_init', array($this->container['formController'], 'process'));
            add_action('wp_ajax_quform_preview_form', array($this->container['builder'], 'preview'));
            add_action('wp_ajax_nopriv_quform_preview_form', array($this->container['builder'], 'preview'));

            // Forms
            add_action('wp_ajax_quform_save_forms_table_settings', array($this->container['formsListSettings'], 'save'));
            add_action('wp_ajax_quform_add_form', array($this->container['builder'], 'add'));
            add_action('wp_ajax_quform_save_form', array($this->container['builder'], 'save'));
            add_filter('wp_ajax_quform_builder_search_posts', array($this->container['builder'], 'searchPosts'));
            add_filter('wp_ajax_quform_builder_get_post_title', array($this->container['builder'], 'getPostTitle'));

            // Insert form button & page
            add_action('admin_enqueue_scripts', array($this->container['insertForm'], 'registerScripts'));
            add_action('media_buttons', array($this->container['insertForm'], 'button'), 20);
            add_action('wp_ajax_quform_insert_form', array($this->container['insertForm'], 'display'));

            // Dashboard widget
            add_action('wp_dashboard_setup', array($this->container['dashboardWidget'], 'setup'));

            // Update
            add_action('pre_set_site_transient_update_plugins', array($this->container['updater'], 'setUpdateTransient'));
            add_action('wp_ajax_quform_manual_update_check', array($this->container['updater'], 'checkForUpdate'));
            add_filter('plugins_api', array($this->container['updater'], 'pluginInformation'), 10, 3);

            // Entries
            add_action('wp_ajax_quform_save_entries_table_settings', array($this->container['entriesListSettings'], 'save'));
            add_action('wp_ajax_quform_set_entry_labels', array($this->container['entriesListSettings'], 'setEntryLabels'));
            add_action('wp_loaded', array($this->container['entryController'], 'process'));
            add_action('wp_ajax_quform_resend_notifications', array($this->container['notificationResender'], 'resend'));
            add_filter('wp_ajax_quform_entries_search_users', array($this->container['entriesUserSearcher'], 'search'));

            // Tools
            add_action('wp_ajax_quform_export_form', array($this->container['formExporter'], 'export'));
            add_action('wp_ajax_quform_import_form', array($this->container['formImporter'], 'import'));
            add_action('wp_ajax_quform_get_export_field_list', array($this->container['entryExporter'], 'getExportFieldList'));
            add_action('wp_ajax_quform_save_export_field_list_order', array($this->container['entryExporter'], 'saveExportFieldListOrder'));
            add_action('wp_ajax_quform_migrate_get_all_form_ids', array($this->container['migrator'], 'getAllFormIds'));
            add_action('wp_ajax_quform_migrate_form', array($this->container['migrator'], 'migrateForm'));
            add_action('wp_ajax_quform_migrate_settings', array($this->container['migrator'], 'migrateSettings'));
            add_action('wp_ajax_quform_migrate_import_form', array($this->container['migrator'], 'migrateImportForm'));

            // Misc
            add_action('wp_refresh_nonces', array($this->container['nonceRefresher'], 'refreshNonces'), 10, 3);
            add_filter('wpmu_drop_tables', array($this->container['repository'], 'dropTablesOnSiteDeletion'));
            add_filter('wpmu_drop_tables', array($this->container['session'], 'dropTableOnSiteDeletion'));
        }

        do_action('quform_bootstrap', $this->container);

        return $this;
    }

    /**
     * Get a service from the container
     *
     * @param   string  $service  The service name
     * @return  mixed             The service instance
     */
    public function getService($service)
    {
        return isset($this->container[$service]) ? $this->container[$service] : null;
    }
}
