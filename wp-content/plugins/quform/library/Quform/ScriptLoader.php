<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_ScriptLoader
{
    /**
     * @var Quform_Options
     */
    protected $options;

    /**
     * @var Quform_Themes
     */
    protected $themes;

    /**
     * @var Quform_Form_Factory
     */
    protected $factory;

    /**
     * @var Quform_Repository
     */
    protected $repository;

    /**
     * @var Quform_Session
     */
    protected $session;

    /**
     * @param  Quform_Options       $options
     * @param  Quform_Themes        $themes
     * @param  Quform_Repository    $repository
     * @param  Quform_Form_Factory  $factory
     */
    public function __construct(Quform_Options $options, Quform_Themes $themes, Quform_Repository $repository, Quform_Form_Factory $factory, Quform_Session $session) {
        $this->options = $options;
        $this->themes = $themes;
        $this->factory = $factory;
        $this->repository = $repository;
        $this->session = $session;
    }

    /**
     * Enqueue the plugin scripts/styles
     */
    public function enqueue()
    {
        if ($this->shouldLoadScripts()) {
            $this->enqueueStyles();
            $this->enqueueScripts();
        }
    }

    /**
     * Should the load the scripts/styles be loaded?
     *
     * @return bool
     */
    protected function shouldLoadScripts()
    {
        static $loadScripts = null;

        if ($loadScripts === null) {
            $loadScripts = true;

            if ($this->options->get('loadScripts') == 'autodetect') {
                if ( ! $this->detectFormInContent() && ! $this->detectFormInWidget()) {
                    $loadScripts = false;
                }
            } else if ($this->options->get('loadScripts') == 'custom') {
                $post = Quform::getCurrentPost();
                $postIds = $this->options->get('loadScriptsCustom');
                $loadScripts = false;

                if ($post instanceof WP_Post && count($postIds) && in_array($post->ID, $postIds)) {
                    $loadScripts = true;
                }
            }

            $loadScripts = apply_filters('quform_enqueue_scripts', $loadScripts);
        }

        return $loadScripts;
    }

    /**
     * Enqueue the plugin styles
     */
    protected function enqueueStyles()
    {
        // Load non-core theme CSS files
        foreach (array_unique($this->options->get('activeThemes')) as $key) {
            if ( ! $this->themes->isCoreTheme($key)) {
                $data = $this->themes->getTheme($key);

                if (is_array($data)) {
                    wp_enqueue_style($key, $data['cssUrl'], array(), isset($data['version']) ? $data['version'] : QUFORM_VERSION);
                }
            }
        }

        if ($this->options->get('combineCss')) {
            wp_enqueue_style('quform', $this->getCacheUrl($this->getCombinedCssFilename()), array(), $this->options->get('cacheBuster'));
        } else {
            foreach ($this->getStyles() as $key => $style) {
                wp_enqueue_style($key, $style['url'], array(), $style['version']);
            }
        }

        do_action('quform_styles_enqueued');
    }

    /**
     * Get the styles to be enqueued
     *
     * @param   bool   $isPreview  True to get the styles for the form preview
     * @return  array
     */
    public function getStyles($isPreview = false)
    {
        $styles = array();

        if ( ! $this->options->get('disabledStyles.fontAwesome')) {
            $styles['font-awesome'] = array(
                'url' => Quform::url('css/font-awesome.min.css'),
                'path' => QUFORM_PATH . '/css/font-awesome.min.css',
                'version' => '4.7.0'
            );
        }

        if ( ! $this->options->get('disabledStyles.select2') && (count($this->options->get('activeEnhancedSelects')) || $isPreview)) {
            $styles['quform-select2'] = array(
                'url' => Quform::url('css/select2.min.css'),
                'path' => QUFORM_PATH . '/css/select2.min.css',
                'version' => '4.0.13'
            );
        }

        if ( ! $this->options->get('disabledStyles.qtip')) {
            $styles['qtip'] = array(
                'url' => Quform::url('css/jquery.qtip.min.css'),
                'path' => QUFORM_PATH . '/css/jquery.qtip.min.css',
                'version' => '3.0.4'
            );
        }

        if ($this->options->get('popupEnabled')) {
            if ($this->options->get('popupScript') == 'fancybox-1' && ! $this->options->get('disabledStyles.fancybox')) {
                $styles['fancybox'] = array(
                    'url' => Quform::url('css/jquery.fancybox1.min.css'),
                    'path' => QUFORM_PATH . '/css/jquery.fancybox1.min.css',
                    'version' => '1.3.9'
                );
            } elseif ($this->options->get('popupScript') == 'fancybox-2' && ! $this->options->get('disabledStyles.fancybox2')) {
                $styles['fancybox2'] = array(
                    'url' => Quform::url('css/jquery.fancybox.min.css'),
                    'path' => QUFORM_PATH . '/css/jquery.fancybox.min.css',
                    'version' => '2.1.9'
                );
            } elseif ($this->options->get('popupScript') == 'fancybox-3' && ! $this->options->get('disabledStyles.fancybox3')) {
                $styles['fancybox3'] = array(
                    'url' => Quform::url('css/jquery.fancybox3.min.css'),
                    'path' => QUFORM_PATH . '/css/jquery.fancybox3.min.css',
                    'version' => '3.5.7'
                );
            } elseif ($this->options->get('popupScript') == 'magnific-popup' && ! $this->options->get('disabledStyles.magnificPopup')) {
                $styles['magnific-popup'] = array(
                    'url' => Quform::url('css/magnific-popup.min.css'),
                    'path' => QUFORM_PATH . '/css/magnific-popup.min.css',
                    'version' => '1.1.0'
                );
            }
        }

        $styles['quform'] = array(
            'url' => Quform::url('css/styles.min.css'),
            'path' => QUFORM_PATH . '/css/styles.min.css',
            'version' => QUFORM_VERSION
        );

        // Load core theme CSS files
        foreach (array_unique($this->options->get('activeThemes')) as $key) {
            if ($this->themes->isCoreTheme($key)) {
                $data = $this->themes->getTheme($key);

                if (is_array($data)) {
                    $styles['quform-theme-' . $key] = array(
                        'url' => $data['cssUrl'],
                        'path' => $data['cssPath'],
                        'version' => isset($data['version']) ? $data['version'] : QUFORM_VERSION
                    );
                }
            }
        }

        if (apply_filters('quform_enfold_compatibility', defined('AV_FRAMEWORK_VERSION'))) {
            $styles['quform-enfold'] = array(
                'url' => Quform::url('css/enfold.min.css'),
                'path' => QUFORM_PATH . '/css/enfold.min.css',
                'version' => QUFORM_VERSION
            );
        }

        if (is_file($this->getCachePath($this->getCustomCssFilename()))) {
            $styles['quform-custom'] = array(
                'url' => $this->getCacheUrl($this->getCustomCssFilename()),
                'path' => $this->getCachePath($this->getCustomCssFilename()),
                'version' => $this->options->get('cacheBuster')
            );
        }

        return $styles;
    }

    /**
     * Enqueue the plugin styles
     */
    protected function enqueueScripts()
    {
        wp_deregister_script('jquery-form');
        wp_register_script('jquery-form', Quform::url('js/jquery.form.min.js'), array('jquery'), '4.3.0', true);

        // Load non-core theme JS files
        foreach (array_unique($this->options->get('activeThemes')) as $key) {
            if ( ! $this->themes->isCoreTheme($key)) {
                $data = $this->themes->getTheme($key);

                if (is_array($data) && isset($data['jsUrl'])) {
                    wp_enqueue_script($key, $data['jsUrl'], array('jquery'), isset($data['version']) ? $data['version'] : QUFORM_VERSION, true);
                }
            }
        }

        if ($this->options->get('combineJs')) {
            wp_enqueue_script('quform', $this->getCacheUrl($this->getCombinedJsFilename()), array('jquery'), $this->options->get('cacheBuster'), true);
        } else {
            foreach ($this->getScripts() as $key => $script) {
                wp_enqueue_script($key, $script['url'], array('jquery'), $script['version'], true);
            }
        }

        wp_localize_script('quform', 'quformL10n', array(
            'l10n_print_after' => 'quformL10n = ' . wp_json_encode($this->jsL10n())
        ));

        do_action('quform_scripts_enqueued');
    }

    /**
     * Get the scripts to be enqueued
     *
     * @param   bool   $isPreview  True to get the scripts for the form preview
     * @return  array
     */
    public function getScripts($isPreview = false)
    {
        global $wp_version;
        $scripts = array();

        $scripts['jquery-form'] = array(
            'url' => Quform::url('js/jquery.form.min.js'),
            'path' => QUFORM_PATH . '/js/jquery.form.min.js',
            'version' => '4.3.0'
        );

        if ( ! $this->options->get('disabledScripts.fileUpload') && (count($this->options->get('activeEnhancedUploaders')) || $isPreview)) {
            if (version_compare($wp_version, '5.6', '>=')) {
                $scripts['jquery-ui-core'] = array(
                    'url' => site_url('wp-includes/js/jquery/ui/core.min.js'),
                    'path' => ABSPATH . WPINC . '/js/jquery/ui/core.min.js',
                    'version' => '1.12.1'
                );
            } else {
                $scripts['jquery-ui-widget'] = array(
                    'url' => site_url('wp-includes/js/jquery/ui/widget.min.js'),
                    'path' => ABSPATH . WPINC . '/js/jquery/ui/widget.min.js',
                    'version' => '1.11.4'
                );
            }

            $scripts['jquery-fileupload'] = array(
                'url' => Quform::url('js/jquery.fileupload.min.js'),
                'path' => QUFORM_PATH . '/js/jquery.fileupload.min.js',
                'version' => '10.31.0'
            );
        }

        if ( ! $this->options->get('disabledScripts.scrollTo')) {
            $scripts['jquery-scroll-to'] = array(
                'url' => Quform::url('js/jquery.scrollTo.min.js'),
                'path' => QUFORM_PATH . '/js/jquery.scrollTo.min.js',
                'version' => '2.1.2'
            );
        }

        if ( ! $this->options->get('disabledScripts.select2') && (count($this->options->get('activeEnhancedSelects')) || $isPreview)) {
            $scripts['quform-select2'] = array(
                'url' => Quform::url('js/select2.min.js'),
                'path' => QUFORM_PATH . '/js/select2.min.js',
                'version' => '4.0.13'
            );
        }

        if ( ! $this->options->get('disabledScripts.qtip')) {
            $scripts['qtip'] = array(
                'url' => Quform::url('js/jquery.qtip.min.js'),
                'path' => QUFORM_PATH . '/js/jquery.qtip.min.js',
                'version' => '3.0.4'
            );
        }

        if ($this->options->get('popupEnabled')) {
            if ($this->options->get('popupScript') == 'fancybox-1' && ! $this->options->get('disabledScripts.fancybox')) {
                $scripts['fancybox'] = array(
                    'url' => Quform::url('js/jquery.fancybox1.min.js'),
                    'path' => QUFORM_PATH . '/js/jquery.fancybox1.min.js',
                    'version' => '1.3.9'
                );
            } elseif ($this->options->get('popupScript') == 'fancybox-2' && ! $this->options->get('disabledScripts.fancybox2')) {
                $scripts['fancybox2'] = array(
                    'url' => Quform::url('js/jquery.fancybox.pack.js'),
                    'path' => QUFORM_PATH . '/js/jquery.fancybox.pack.js',
                    'version' => '2.1.9'
                );
            } elseif ($this->options->get('popupScript') == 'fancybox-3' && ! $this->options->get('disabledScripts.fancybox3')) {
                $scripts['fancybox3'] = array(
                    'url' => Quform::url('js/jquery.fancybox3.min.js'),
                    'path' => QUFORM_PATH . '/js/jquery.fancybox3.min.js',
                    'version' => '3.5.7'
                );
            } elseif ($this->options->get('popupScript') == 'magnific-popup' && ! $this->options->get('disabledScripts.magnificPopup')) {
                $scripts['magnific-popup'] = array(
                    'url' => Quform::url('js/jquery.magnific-popup.min.js'),
                    'path' => QUFORM_PATH . '/js/jquery.magnific-popup.min.js',
                    'version' => '1.1.0'
                );
            }
        }

        if ( ! $this->options->get('disabledScripts.infieldLabels')) {
            $scripts['infield-label'] = array(
                'url' => Quform::url('js/jquery.infieldlabel.min.js'),
                'path' => QUFORM_PATH . '/js/jquery.infieldlabel.min.js',
                'version' => '0.1.5'
            );
        }

        $loadDatepicker = ! $this->options->get('disabledScripts.datepicker') && (count($this->options->get('activeDatepickers')) || $isPreview);
        $loadTimepicker = ! $this->options->get('disabledScripts.timepicker') && (count($this->options->get('activeTimepickers')) || $isPreview);

        if ($loadDatepicker || $loadTimepicker) {
            $scripts['kendo-core'] = array(
                'url' => Quform::url('js/kendo.core.min.js'),
                'path' => QUFORM_PATH . '/js/kendo.core.min.js',
                'version' => '2020.2.617'
            );

            $scripts['kendo-calendar'] = array(
                'url' => Quform::url('js/kendo.calendar.min.js'),
                'path' => QUFORM_PATH . '/js/kendo.calendar.min.js',
                'version' => '2020.2.617'
            );

            $scripts['kendo-popup'] = array(
                'url' => Quform::url('js/kendo.popup.min.js'),
                'path' => QUFORM_PATH . '/js/kendo.popup.min.js',
                'version' => '2020.2.617'
            );

            if ($loadDatepicker) {
                $scripts['kendo-datepicker'] = array(
                    'url' => Quform::url('js/kendo.datepicker.min.js'),
                    'path' => QUFORM_PATH . '/js/kendo.datepicker.min.js',
                    'version' => '2020.2.617'
                );
            }

            if ($loadTimepicker) {
                $scripts['kendo-timepicker'] = array(
                    'url' => Quform::url('js/kendo.timepicker.min.js'),
                    'path' => QUFORM_PATH . '/js/kendo.timepicker.min.js',
                    'version' => '2020.2.617'
                );
            }

            $loadDefaultLocale = false;
            $activeLocales = array();
            foreach ($this->options->get('activeLocales') as $locales) {
                $activeLocales = array_merge($activeLocales, $locales);
            }

            $activeLocales = array_unique(apply_filters('quform_active_locales', $activeLocales));

            foreach ($activeLocales as $locale) {
                if ($locale == '') {
                    $loadDefaultLocale = true;
                    continue;
                } elseif ($locale == 'en-US') {
                    continue;
                }

                $scripts['kendo-culture-' . $locale] = array(
                    'url' => Quform::url('js/cultures/kendo.culture.' . $locale . '.min.js'),
                    'path' => QUFORM_PATH . '/js/cultures/kendo.culture.'  . $locale . '.min.js',
                    'version' => '2020.2.617'
                );
            }

            if ($loadDefaultLocale && $this->options->get('locale') && $this->options->get('locale') != 'en-US') {
                $scripts['kendo-culture-' . $this->options->get('locale')] = array(
                    'url' => Quform::url('js/cultures/kendo.culture.'  . $this->options->get('locale') . '.min.js'),
                    'path' => QUFORM_PATH . '/js/cultures/kendo.culture.'  . $this->options->get('locale') . '.min.js',
                    'version' => '2020.2.617'
                );
            }
        }

        // Load core theme JS files
        foreach (array_unique($this->options->get('activeThemes')) as $key) {
            if ($this->themes->isCoreTheme($key)) {
                $data = $this->themes->getTheme($key);

                if (is_array($data) && isset($data['jsUrl'])) {
                    $scripts['quform-theme-' . $key] = array(
                        'url' => $data['jsUrl'],
                        'path' => $data['jsPath'],
                        'version' => isset($data['version']) ? $data['version'] : QUFORM_VERSION
                    );
                }
            }
        }

        $scripts['quform'] = array(
            'url' => Quform::url('js/quform.min.js'),
            'path' => QUFORM_PATH . '/js/quform.min.js',
            'version' => QUFORM_VERSION
        );

        if ( ! $isPreview && is_file($this->getCachePath($this->getCustomJsFilename()))) {
            $scripts['quform-custom'] = array(
                'url' => $this->getCacheUrl($this->getCustomJsFilename()),
                'path' => $this->getCachePath($this->getCustomJsFilename()),
                'version' => $this->options->get('cacheBuster')
            );
        }

        return $scripts;
    }

    /**
     * JavaScript localisation
     *
     * @return array
     */
    public function jsL10n()
    {
        return apply_filters('quform_script_loader_js_l10n', array(
            'pluginUrl' => Quform::url(),
            'ajaxUrl' => admin_url('admin-ajax.php', null),
            'ajaxError' => __('Ajax error', 'quform'),
            'errorMessageTitle' => __('There was a problem', 'quform'),
            'removeFile' => __('Remove', 'quform'),
            'supportPageCaching' => $this->options->get('supportPageCaching') && ! Quform::isPostRequest()
        ));
    }

    /**
     * Print head scripts
     */
    public function printHeadScripts()
    {
        if ($this->shouldLoadScripts()) {
            echo '<script>!function(e,c){e[c]=e[c]+(e[c]&&" ")+"quform-js"}(document.documentElement,"className");</script>';
        }
    }

    /**
     * Check if the page content has one of the shortcodes
     *
     * @return bool
     */
    protected function detectFormInContent()
    {
        $post = Quform::getCurrentPost();

        if ($post instanceof WP_Post) {
            if (has_shortcode($post->post_content, 'quform') || has_shortcode($post->post_content, 'quform_popup')) {
                return true;
            }

            if (function_exists('has_block') && has_block('quform/form', $post->post_content)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if there is one of the widgets on the current page
     *
     * @return bool
     */
    protected function detectFormInWidget()
    {
        return is_active_widget(false, false, 'quform-widget') || is_active_widget(false, false, 'quform-popup-widget');
    }

    /**
     * Update the feature cache and generate the CSS/JS files
     *
     * @param array $config
     */
    public function handleSaveForm(array $config)
    {
        $form = $this->factory->create($config);

        $this->updateActiveFeatureCache(array($config));
        $this->generateFormCssFile(array($form));
        $this->generateFiles();
    }

    /**
     * Update the feature cache and move the custom CSS flag to active
     *
     * @param array $ids
     */
    public function handleActivateForms(array $ids)
    {
        $this->updateActiveFeatureCache($this->repository->getFormsById($ids));
        $activeCustomCss = $this->options->get('activeCustomCss');
        $inactiveCustomCss = $this->options->get('inactiveCustomCss');

        foreach ($ids as $id) {
            if (isset($inactiveCustomCss[$id])) {
                unset($inactiveCustomCss[$id]);
                $activeCustomCss[$id] = true;
            }
        }

        $this->options->set(compact('activeCustomCss', 'inactiveCustomCss'));

        $this->generateFiles();
    }

    /**
     * Update the feature cache and move the custom CSS flag to inactive
     *
     * @param array $ids
     */
    public function handleDeactivateForms(array $ids)
    {
        $this->updateActiveFeatureCache($this->repository->getFormsById($ids));
        $activeCustomCss = $this->options->get('activeCustomCss');
        $inactiveCustomCss = $this->options->get('inactiveCustomCss');

        foreach ($ids as $id) {
            if (isset($activeCustomCss[$id])) {
                unset($activeCustomCss[$id]);
                $inactiveCustomCss[$id] = true;
            }
        }

        $this->options->set(compact('activeCustomCss', 'inactiveCustomCss'));

        $this->generateFiles();
    }

    /**
     * Update the feature cache and generate the custom CSS file
     *
     * @param array $ids
     */
    public function handleDuplicateForms(array $ids)
    {
        $configs = $this->repository->getFormsById($ids);

        $this->updateActiveFeatureCache($configs);

        $forms = array();
        foreach ($configs as $config) {
            $forms[] = $this->factory->create($config);
        }

        $this->generateFormCssFile($forms);
        $this->generateFiles();
    }

    /**
     * Remove anything related to the form from the feature cache, and delete the form custom CSS file
     *
     * @param array $ids
     */
    public function handleTrashForms(array $ids)
    {
        $this->updateActiveFeatureCache($this->repository->getFormsById($ids));
        $activeCustomCss = $this->options->get('activeCustomCss');
        $inactiveCustomCss = $this->options->get('inactiveCustomCss');

        foreach ($ids as $id) {
            unset($activeCustomCss[$id]);
            unset($inactiveCustomCss[$id]);

            $customCssFile = $this->getCachePath($this->getFormCssFilename($id));
            if (is_file($customCssFile)) {
                @unlink($customCssFile);
            }
        }

        $this->options->set(compact('activeCustomCss', 'inactiveCustomCss'));

        $this->generateFiles();
    }

    /**
     * Handle untrashing forms
     *
     * @param array $ids
     */
    public function handleUntrashForms(array $ids)
    {
        // The process is the same for duplicate
        $this->handleDuplicateForms($ids);
    }

    /**
     * Generate the custom CSS/JS files when the settings are saved
     */
    public function handleSaveSettings()
    {
        $this->generateFiles();
    }

    /**
     * Generate the custom/combined CSS and JS files
     */
    public function generateFiles()
    {
        if ( ! $this->isCacheDirWritable()) {
            Quform::debug('Could not generate custom CSS/JS files: cache directory not writable');
            return;
        }

        $this->generateCustomCssFile();

        if ($this->options->get('combineCss')) {
            $this->generateCombinedCssFile();
        } else {
            $combinedCssPath = $this->getCachePath($this->getCombinedCssFilename());
            if (is_file($combinedCssPath)) {
                @unlink($combinedCssPath);
            }
        }

        $this->generateCustomJsFile();

        if ($this->options->get('combineJs')) {
            $this->generateCombinedJsFile();
        } else {
            $combinedJsPath = $this->getCachePath($this->getCombinedJsFilename());
            if (is_file($combinedJsPath)) {
                @unlink($combinedJsPath);
            }
        }

        $this->options->set('cacheBuster', time());
    }

    /**
     * Generate the custom CSS file for the given forms
     *
     * @param Quform_Form[] $forms The array of forms
     */
    protected function generateFormCssFile($forms)
    {
        if ( ! $this->isCacheDirWritable()) {
            Quform::debug('Could not generate form custom CSS file: cache directory not writable');
            return;
        }

        $activeCustomCss = $this->options->get('activeCustomCss');
        $inactiveCustomCss = $this->options->get('inactiveCustomCss');

        foreach ($forms as $form) {
            $id = $form->getId();
            $css = $form->getCss();
            $path = $this->getCachePath($this->getFormCssFilename($id));

            if ($css) {
                $css = $this->minifyCss($css);

                $fp = fopen($path, 'w');
                fwrite($fp, $css);
                fclose($fp);

                $this->setPermissions($path);

                if ($form->isActive()) {
                    $activeCustomCss[$id] = true;
                    unset($inactiveCustomCss[$id]);
                } else {
                    unset($activeCustomCss[$id]);
                    $inactiveCustomCss[$id] = true;
                }
            } else {
                if (is_file($path)) {
                    @unlink($path);
                }

                unset($activeCustomCss[$id]);
                unset($inactiveCustomCss[$id]);
            }
        }

        $this->options->set(compact('activeCustomCss', 'inactiveCustomCss'));
    }

    /**
     * Generate the custom CSS file which is all form custom CSS files combined into one
     */
    protected function generateCustomCssFile()
    {
        if ( ! $this->isCacheDirWritable()) {
            Quform::debug('Could not generate custom CSS file: cache directory not writable');
            return;
        }

        // Combine all of the active form individual CSS files into a single file
        $combined = '';
        foreach (array_keys($this->options->get('activeCustomCss')) as $formId) {
            $cssFilePath = $this->getCachePath($this->getFormCssFilename($formId));
            if (is_file($cssFilePath)) {
                $contents = file_get_contents($cssFilePath);

                if ($contents) {
                    $combined .= $contents;
                }
            }
        }

        // Add in custom CSS from the Settings page
        if (Quform::isNonEmptyString($this->options->get('customCss'))) {
            $combined .= $this->minifyCss($this->options->get('customCss'));
        }

        if (Quform::isNonEmptyString($this->options->get('customCssTablet'))) {
            $combined .= sprintf('@media screen and (min-width: 569px) and (max-width: 1024px) { %s }', $this->minifyCss($this->options->get('customCssTablet')));
        }

        if (Quform::isNonEmptyString($this->options->get('customCssPhone'))) {
            $combined .= sprintf('@media screen and (max-width: 568px) { %s }', $this->minifyCss($this->options->get('customCssPhone')));
        }

        $combinedPath = $this->getCachePath($this->getCustomCssFilename());

        if ($combined != '') {
            $fp = fopen($combinedPath, 'w');
            fwrite($fp, $combined);
            fclose($fp);

            $this->setPermissions($combinedPath);
        } else {
            if (is_file($combinedPath)) {
                @unlink($combinedPath);
            }
        }
    }

    /**
     * Generate the custom JS file with the code from the Settings page
     */
    protected function generateCustomJsFile()
    {
        $contents = $this->options->get('customJs');
        $path = $this->getCachePath($this->getCustomJsFilename());

        if ($contents != '') {
            $fp = fopen($path, 'w');
            fwrite($fp, $contents);
            fclose($fp);

            $this->setPermissions($path);
        } else {
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }

    /**
     * Generate the CSS file containing all plugin CSS files combined
     */
    protected function generateCombinedCssFile()
    {
        $path = $this->getCachePath($this->getCombinedCssFilename());
        $styles = $this->getStyles();

        $fp = fopen($path, 'w');

        foreach ($styles as $style) {
            if ( ! is_file($style['path']) || ! ($contents = file_get_contents($style['path']))) {
                continue;
            }

            if ($this->getCacheUrl() != Quform::url('cache')) {
                $contents = str_replace(
                    array('../fonts/', '../images/'),
                    array(Quform::url('/fonts/'), Quform::url('/images/')),
                    $contents
                );
            }

            fwrite($fp, $contents);
        }

        fclose($fp);

        $this->setPermissions($path);
    }

    /**
     * Generate the JS file containing all plugin JS files combined
     */
    protected function generateCombinedJsFile()
    {
        $path = $this->getCachePath($this->getCombinedJsFilename());
        $scripts = $this->getScripts();

        $fp = fopen($path, 'w');

        foreach ($scripts as $script) {
            if ( ! is_file($script['path']) || ! ($contents = file_get_contents($script['path']))) {
                continue;
            }

            fwrite($fp, $contents . PHP_EOL);
        }

        fclose($fp);

        $this->setPermissions($path);
    }

    /**
     * Get the path to the cache directory
     *
     * @param   string  $extra  Extra path to append to the path
     * @return  string          Path without trailing slash
     */
    public function getCachePath($extra = '')
    {
        $cachePath = apply_filters('quform_cache_path', QUFORM_PATH . '/cache', $this);

        return Quform::pathExtra($cachePath, $extra);
    }

    /**
     * Get the URL to the cache directory
     *
     * @param   string  $extra  Extra path to append to the path
     * @return  string          Path without trailing slash
     */
    protected function getCacheUrl($extra = '')
    {
        $cacheUrl = apply_filters('quform_cache_url', Quform::url('cache'), $this);

        return Quform::pathExtra($cacheUrl, $extra);
    }

    /**
     * Is the cache directory writable?
     *
     * @return bool
     */
    protected function isCacheDirWritable()
    {
        $cachePath = $this->getCachePath();
        $isWritable = is_dir($cachePath) && wp_is_writable($cachePath);

        return apply_filters('quform_is_cache_dir_writable', $isWritable, $this);
    }

    /**
     * Get the filename of the individual custom CSS file for the given form ID
     *
     * @param   int     $formId
     * @return  string
     */
    protected function getFormCssFilename($formId)
    {
        if (is_multisite()) {
            return sprintf('form.%d.%d.css', get_current_blog_id(), $formId);
        }

        return sprintf('form.%d.css', $formId);
    }

    /**
     * Get the filename of the combined custom CSS file
     *
     * @return string
     */
    protected function getCustomCssFilename()
    {
        if (is_multisite()) {
            return sprintf('custom.%d.css', get_current_blog_id());
        }

        return 'custom.css';
    }

    /**
     * Get the filename of the combined CSS file
     *
     * @return string
     */
    protected function getCombinedCssFilename()
    {
        if (is_multisite()) {
            return sprintf('quform.%d.css', get_current_blog_id());
        }

        return 'quform.css';
    }

    /**
     * Get the filename of the custom JS file
     *
     * @return string
     */
    protected function getCustomJsFilename()
    {
        if (is_multisite()) {
            return sprintf('custom.%d.js', get_current_blog_id());
        }

        return 'custom.js';
    }

    /**
     * Get the filename of the combined JS file
     *
     * @return string
     */
    protected function getCombinedJsFilename()
    {
        if (is_multisite()) {
            return sprintf('quform.%d.js', get_current_blog_id());
        }

        return 'quform.js';
    }

    /**
     * Update the active feature cache for the given form configs
     *
     * @param array $configs
     */
    protected function updateActiveFeatureCache(array $configs)
    {
        $activeThemes = $this->options->get('activeThemes');
        $activeLocales = $this->options->get('activeLocales');
        $activeDatepickers = $this->options->get('activeDatepickers');
        $activeTimepickers = $this->options->get('activeTimepickers');
        $activeEnhancedUploaders = $this->options->get('activeEnhancedUploaders');
        $activeEnhancedSelects = $this->options->get('activeEnhancedSelects');

        foreach ($configs as $config) {
            $id = $config['id'];
            $active = $config['active'] && ! $config['trashed'];

            if ($active && Quform::isNonEmptyString($config['theme'])) {
                $activeThemes[$id] = $config['theme'];
            } else {
                unset($activeThemes[$id]);
            }

            if ($active && $config['hasDatepicker']) {
                $activeDatepickers[$id] = true;
            } else {
                unset($activeDatepickers[$id]);
            }

            if ($active && $config['hasTimepicker']) {
                $activeTimepickers[$id] = true;
            } else {
                unset($activeTimepickers[$id]);
            }

            if ($active && $config['hasEnhancedUploader']) {
                $activeEnhancedUploaders[$id] = true;
            } else {
                unset($activeEnhancedUploaders[$id]);
            }

            if ($active && $config['hasEnhancedSelect']) {
                $activeEnhancedSelects[$id] = true;
            } else {
                unset($activeEnhancedSelects[$id]);
            }

            if ($active && count($config['locales'])) {
                $activeLocales[$id] = $config['locales'];
            } else {
                unset($activeLocales[$id]);
            }
        }

        $this->options->set(compact(
            'activeThemes',
            'activeLocales',
            'activeDatepickers',
            'activeTimepickers',
            'activeEnhancedUploaders',
            'activeEnhancedSelects'
        ));
    }

    /**
     * Rebuild the feature cache and custom CSS files
     */
    public function rebuildScriptCache()
    {
        $configs = $this->repository->allForms();

        // Reset the feature cache
        $this->options->set(array(
            'activeThemes' => array(),
            'activeLocales' => array(),
            'activeDatepickers' => array(),
            'activeTimepickers' => array(),
            'activeEnhancedUploaders' => array()
        ));

        $this->updateActiveFeatureCache($configs);

        $forms = array();
        foreach ($configs as $config) {
            $forms[] = $this->factory->create($config);
        }

        $this->generateFormCssFile($forms);
        $this->generateFiles();
    }

    /**
     * On plugin activation generate the combined script files
     */
    public function activate()
    {
        $this->rebuildScriptCache();
    }

    /**
     * Minify the given CSS
     *
     * @param   string  $css
     * @return  string
     */
    protected function minifyCss($css)
    {
        static $instance;

        if ($instance === null) {
            if ( ! class_exists('CSSminNoConflict')) {
                require_once QUFORM_LIBRARY_PATH . '/cssmin.php';
            }

            $instance = new CSSminNoConflict();
        }

        return $instance->run($css);
    }

    /**
     * Set the permissions of the generated file
     *
     * @param string $file
     */
    protected function setPermissions($file) {
        $stat = stat(dirname($file));
        $perms = $stat['mode'] & 0000666;
        chmod($file, $perms);
    }
}
