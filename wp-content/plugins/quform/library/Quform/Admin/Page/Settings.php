<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Admin_Page_Settings extends Quform_Admin_Page
{
    /**
     * @var Quform_Options
     */
    protected $options;

    /**
     * @var Quform_License
     */
    protected $license;

    /**
     * @var Quform_Permissions
     */
    protected $permissions;

    /**
     * @var Quform_ScriptLoader
     */
    protected $scriptLoader;

    /**
     * @param  Quform_ViewFactory   $viewFactory
     * @param  Quform_Options       $options
     * @param  Quform_License       $license
     * @param  Quform_Repository    $repository
     * @param  Quform_Permissions   $permissions
     * @param  Quform_ScriptLoader  $scriptLoader
     */
    public function __construct(Quform_ViewFactory $viewFactory, Quform_Repository $repository,
                                Quform_Options $options, Quform_License $license,
                                Quform_Permissions $permissions, Quform_ScriptLoader $scriptLoader)
    {
        parent::__construct($viewFactory, $repository);

        $this->options = $options;
        $this->license = $license;
        $this->permissions = $permissions;
        $this->viewFactory = $viewFactory;
        $this->scriptLoader = $scriptLoader;
    }

    public function init()
    {
        $this->template = QUFORM_TEMPLATE_PATH .  '/admin/settings.php';
    }

    /**
     * Enqueue the page styles
     */
    protected function enqueueStyles()
    {
        wp_enqueue_style('qtip2', Quform::url('css/jquery.qtip.min.css'), array(), '3.0.4');
        wp_enqueue_style('quform-select2', Quform::url('css/select2.min.css'), array(), '4.0.13');

        parent::enqueueStyles();
    }

    /**
     * Enqueue the page scripts
     */
    protected function enqueueScripts()
    {
        parent::enqueueScripts();

        wp_enqueue_script('qtip2', Quform::url('js/jquery.qtip.min.js'), array('jquery'), '3.0.4', true);
        wp_enqueue_script('quform-select2', Quform::url('js/select2.min.js'), array('jquery'), '4.0.13', true);
        wp_enqueue_script('themecatcher-tabs', Quform::adminUrl('js/tc.tabs.min.js'), array('jquery'), '1.0.0', true);
        wp_enqueue_script('quform-settings', Quform::adminUrl('js/settings.min.js'), array('jquery'), QUFORM_VERSION, true);

        wp_localize_script('quform-settings', 'quformSettingsL10n', array(
            'verifyNonce' => wp_create_nonce('quform_verify_purchase_code'),
            /* translators: %1$s: open link tag, %2$s: close link tag */
            'errorVerifying' => sprintf(__('An error occurred verifying the license key, please try again. If this problem persists, see %1$sthis page%2$s.', 'quform'), '<a href="https://support.themecatcher.net/quform-wordpress-v2/troubleshooting/common-problems/an-error-occurred-verifying-the-license-key" target="_blank">', '</a>'),
            'waitVerifying' => __('Please wait, verification in progress', 'quform'),
            'valid' => __('Valid', 'quform'),
            'bundled' => __('Valid (bundled)', 'quform'),
            'unlicensed' => __('Unlicensed', 'quform'),
            'updateCheckNonce' => wp_create_nonce('quform_manual_update_check'),
            'errorCheckingForUpdate' => __('An error occurred checking for an update.', 'quform'),
            'waitUpdating' => __('Please wait, checking for an update', 'quform'),
            'saveSettingsNonce' => wp_create_nonce('quform_save_settings'),
            'settingsSaved' => __('Settings saved', 'quform'),
            'errorSavingSettings' => __('An error occurred saving the settings.', 'quform'),
            'rebuildScriptCacheNonce' => wp_create_nonce('quform_rebuild_script_cache'),
            'errorRebuildingScriptCache' => __('An error occurred rebuilding the script cache.', 'quform'),
            'scriptCacheRebuilt' => __('Script cache rebuilt', 'quform'),
            'searchPostsNonce' => wp_create_nonce('quform_settings_search_posts')
        ));
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
        <div class="qfb-nav-item qfb-nav-page-info"><i class="qfb-nav-page-icon qfb-mdi qfb-mdi-settings"></i><span class="qfb-nav-page-title"><?php esc_html_e('Settings', 'quform'); ?></span></div>
        <?php

        return parent::getNavHtml($currentForm, array(40 => ob_get_clean()));
    }

    /**
     * Process this page and send data to the view
     */
    public function process()
    {
        if ( ! current_user_can('quform_settings')) {
            wp_die(__( 'You do not have sufficient permissions to access this page.', 'quform'), 403);
        }

        global $wp_roles;

        $this->view->with(array(
            'options' => $this->options,
            'license' => $this->license,
            'requirements' => $this->getServerCompatibility(),
            'roles' => $wp_roles->roles,
            'caps' => $this->permissions->getAllCapabilitiesWithDescriptions(),
            'disableableStyles' => $this->getDisableableStyles(),
            'disableableScripts' => $this->getDisableableScripts()
        ));
    }

    /**
     * Get the server compatibility information
     *
     * @return array
     */
    protected function getServerCompatibility()
    {
        $server = array(
            'phpVersion' => phpversion(),
            'mysqlVersion' => $this->repository->getDbVersion(),
            'wordpressVersion' => get_bloginfo('version'),
            'gdEnabled' => function_exists('imagecreate'),
            'ftEnabled' => function_exists('imagettftext'),
            'zipEnabled' => extension_loaded('zip'),
            'tmpDir' => Quform::getTempDir('quform'),
            'cacheDir' => $this->scriptLoader->getCachePath(),
            'uploadsDir' => Quform::getUploadsDir()
        );

        $compatibility = array(
            'php' => array(
                'name' => __('PHP Version', 'quform'),
                'info' => $server['phpVersion']
            ),
            'mysql' => array(
                'name' => __('MySQL Version', 'quform'),
                'info' => $server['mysqlVersion']
            ),
            'wordpress' => array(
                'name' => __('WordPress Version', 'quform'),
                'info' => $server['wordpressVersion']
            ),
            'gd' => array(
                'name' => __('GD Image Library', 'quform'),
                'info' => $server['gdEnabled'] ? __('Available', 'quform') : __('Unavailable', 'quform')
            ),
            'ft' => array(
                'name' => __('FreeType Library', 'quform'),
                'info' => $server['ftEnabled'] ? __('Available', 'quform') : __('Unavailable', 'quform')
            ),
            'zip' => array(
                'name' => __('Zip Extension', 'quform'),
                'info' => $server['zipEnabled'] ? __('Available', 'quform') : __('Unavailable', 'quform')
            ),
            'tmp' => array(
                'name' => __('Temporary Directory', 'quform'),
                'info' => $server['tmpDir'],
            ),
            'cache' => array(
                'name' => __('Cache Directory', 'quform'),
                'info' => $server['cacheDir'],
            ),
            'uploads' => array(
                'name' => __('Uploads Directory', 'quform'),
                'info' => $server['uploadsDir'],
            )
        );

        if (version_compare($server['phpVersion'], '5.2', '<')) {
            $compatibility['php']['error'] = __('The plugin requires PHP version 5.2 or later.', 'quform');
        }

        if (version_compare($server['mysqlVersion'], '5.0.0', '<')) {
            $compatibility['mysql']['error'] = __('The plugin requires MySQL version 5 or later.', 'quform');
        }

        if (version_compare($server['wordpressVersion'], '4.5', '<')) {
            $compatibility['wordpress']['error'] = __('The plugin requires WordPress version 4.5 or later.', 'quform');
        }

        if ( ! $server['gdEnabled']) {
            $compatibility['gd']['error'] = __('The plugin requires the GD image library for the CAPTCHA element.', 'quform');
        }

        if ( ! $server['ftEnabled']) {
            $compatibility['ft']['error'] = __('The plugin requires the FreeType library for the CAPTCHA element.', 'quform');
        }

        if ( ! $server['zipEnabled']) {
            $compatibility['zip']['error'] = __('The plugin requires the Zip PHP extension to export entries.', 'quform');
        }

        if ( ! is_dir($server['tmpDir'])) {
            $compatibility['tmp']['error'] = __('The temporary directory does not exist, please create the directory and make it writable.', 'quform');
        } else if ( ! wp_is_writable($server['tmpDir'])) {
            $compatibility['tmp']['error'] = __('The temporary directory is not writable.', 'quform');
        }

        if ( ! is_dir($server['cacheDir'])) {
            $compatibility['cache']['error'] = __('The plugin cache directory does not exist, please create the directory and make it writable.', 'quform');
        } else if ( ! wp_is_writable($server['cacheDir'])) {
            $compatibility['cache']['error'] = __('The plugin cache directory is not writable.', 'quform');
        }

        if ( ! is_dir($server['uploadsDir'])) {
            $compatibility['uploads']['error'] = __('The WordPress uploads directory does not exist, please create the directory and make it writable.', 'quform');
        } else if ( ! wp_is_writable($server['uploadsDir'])) {
            $compatibility['uploads']['error'] = __('The WordPress uploads directory is not writable.', 'quform');
        }

        return $compatibility;
    }

    /**
     * Get the array of stylesheets that can be disabled
     *
     * @return array
     */
    protected function getDisableableStyles()
    {
        return array(
            'font_awesome' => array(
                'disabled' => $this->options->get('disabledStyles.fontAwesome'),
                'name' => 'Font Awesome',
                'version' => 'v4.7.0',
                'tooltip' => __('Used for displaying icons in various parts of the form.', 'quform')
            ),
            'select2' => array(
                'disabled' => $this->options->get('disabledStyles.select2'),
                'name' => 'Select2',
                'version' => 'v4.0.13',
                'tooltip' => __('Used as a select menu replacement.', 'quform')
            ),
            'qtip' => array(
                'disabled' => $this->options->get('disabledStyles.qtip'),
                'name' => 'qTip',
                'version' => 'v3.0.3',
                'tooltip' => __('Used for displaying tooltips in the form.', 'quform')
            ),
            'fancybox' => array(
                'disabled' => $this->options->get('disabledStyles.fancybox'),
                'name' => 'Fancybox',
                'version' => 'v1.3.9',
                'tooltip' => __('Used for displaying the form in a popup.', 'quform'),
                'hidden' => ! $this->options->get('popupEnabled') || $this->options->get('popupScript') != 'fancybox-1'
            ),
            'fancybox2' => array(
                'disabled' => $this->options->get('disabledStyles.fancybox2'),
                'name' => 'Fancybox',
                'version' => 'v2.1.9',
                'tooltip' => __('Used for displaying the form in a popup.', 'quform'),
                'hidden' => ! $this->options->get('popupEnabled') || $this->options->get('popupScript') != 'fancybox-2'
            ),
            'fancybox3' => array(
                'disabled' => $this->options->get('disabledStyles.fancybox3'),
                'name' => 'Fancybox',
                'version' => 'v3.5.7',
                'tooltip' => __('Used for displaying the form in a popup.', 'quform'),
                'hidden' => ! $this->options->get('popupEnabled') || $this->options->get('popupScript') != 'fancybox-3'
            ),
            'magnific_popup' => array(
                'disabled' => $this->options->get('disabledStyles.magnificPopup'),
                'name' => 'Magnific Popup',
                'version' => 'v1.1.0',
                'tooltip' => __('Used for displaying the form in a popup.', 'quform'),
                'hidden' => ! $this->options->get('popupEnabled') || $this->options->get('popupScript') != 'magnific-popup'
            )
        );
    }

    /**
     * Get the array of scripts that can be disabled
     *
     * @return array
     */
    protected function getDisableableScripts()
    {
        return array(
            'file_upload' => array(
                'disabled' => $this->options->get('disabledScripts.fileUpload'),
                'name' => 'jQuery File Upload',
                'version' => 'v9.21.0',
                'tooltip' => __('Used for adding upload progress to File Upload elements.', 'quform')
            ),
            'scroll_to' => array(
                'disabled' => $this->options->get('disabledScripts.scrollTo'),
                'name' => 'jQuery.scrollTo',
                'version' => 'v2.1.2',
                'tooltip' => __('Used for animated scrolling to success and error messages.', 'quform')
            ),
            'select2' => array(
                'disabled' => $this->options->get('disabledScripts.select2'),
                'name' => 'Select2',
                'version' => 'v4.0.13',
                'tooltip' => __('Used as a select menu replacement.', 'quform')
            ),
            'qtip' => array(
                'disabled' => $this->options->get('disabledScripts.qtip'),
                'name' => 'qTip',
                'version' => 'v3.0.3',
                'tooltip' => __('Used for displaying tooltips in the form.', 'quform')
            ),
            'fancybox' => array(
                'disabled' => $this->options->get('disabledScripts.fancybox'),
                'name' => 'Fancybox',
                'version' => 'v1.3.9',
                'tooltip' => __('Used for displaying the form in a popup.', 'quform'),
                'hidden' => ! $this->options->get('popupEnabled') || $this->options->get('popupScript') != 'fancybox-1'
            ),
            'fancybox2' => array(
                'disabled' => $this->options->get('disabledScripts.fancybox2'),
                'name' => 'Fancybox',
                'version' => 'v2.1.9',
                'tooltip' => __('Used for displaying the form in a popup.', 'quform'),
                'hidden' => ! $this->options->get('popupEnabled') || $this->options->get('popupScript') != 'fancybox-2'
            ),
            'fancybox3' => array(
                'disabled' => $this->options->get('disabledScripts.fancybox3'),
                'name' => 'Fancybox',
                'version' => 'v3.5.7',
                'tooltip' => __('Used for displaying the form in a popup.', 'quform'),
                'hidden' => ! $this->options->get('popupEnabled') || $this->options->get('popupScript') != 'fancybox-3'
            ),
            'magnific_popup' => array(
                'disabled' => $this->options->get('disabledScripts.magnificPopup'),
                'name' => 'Magnific Popup',
                'version' => 'v1.1.0',
                'tooltip' => __('Used for displaying the form in a popup.', 'quform'),
                'hidden' => ! $this->options->get('popupEnabled') || $this->options->get('popupScript') != 'magnific-popup'
            ),
            'infield_labels' => array(
                'disabled' => $this->options->get('disabledScripts.infieldLabels'),
                'name' => 'Infield Labels',
                'version' => 'v0.1.5',
                'tooltip' => __('Used for displaying labels inside fields.', 'quform')
            ),
            'datepicker' => array(
                'disabled' => $this->options->get('disabledScripts.datepicker'),
                'name' => 'Datepicker',
                'version' => 'v2020.2.617',
                'tooltip' => __('Used for displaying a datepicker calendar on the Date element.', 'quform')
            ),
            'timepicker' => array(
                'disabled' => $this->options->get('disabledScripts.timepicker'),
                'name' => 'Timepicker',
                'version' => 'v2020.2.617',
                'tooltip' => __('Used for displaying a timepicker list on the Time element.', 'quform')
            )
        );
    }
}
