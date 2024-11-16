<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Settings
{
    /**
     * @var Quform_Options
     */
    protected $options;

    /**
     * @var Quform_Permissions
     */
    protected $permissions;

    /**
     * @var Quform_ScriptLoader
     */
    protected $scriptLoader;

    /**
     * @param Quform_Options      $options
     * @param Quform_Permissions  $permissions
     * @param Quform_ScriptLoader $scriptLoader
     */
    public function __construct(Quform_Options $options, Quform_Permissions $permissions, Quform_ScriptLoader $scriptLoader)
    {
        $this->options = $options;
        $this->permissions = $permissions;
        $this->scriptLoader = $scriptLoader;
    }

    /**
     * Handle saving the settings page via Ajax
     */
    public function save()
    {
        $this->validateSaveRequest();

        $options = json_decode(stripslashes($_POST['options']), true);

        if ( ! is_array($options)) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Bad request', 'quform')
            ));
        }

        $options = $this->sanitizeOptions($options);

        if (array_key_exists('permissions', $options)) {
            if (is_array($options['permissions'])) {
                $this->permissions->update($options['permissions']);
            }

            unset($options['permissions']);
        }

        $this->options->set($options);

        $this->scriptLoader->generateFiles();

        do_action('quform_settings_saved', $this->options);

        wp_send_json(array(
            'type' => 'success'
        ));
    }

    /**
     * Validate the request to save the settings
     */
    protected function validateSaveRequest()
    {
        if ( ! Quform::isPostRequest() || ! isset($_POST['options']) || ! is_string($_POST['options'])) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Bad request', 'quform')
            ));
        }

        if ( ! current_user_can('quform_settings')) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Insufficient permissions', 'quform')
            ));
        }

        if ( ! check_ajax_referer('quform_save_settings', false, false)) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Nonce check failed', 'quform')
            ));
        }
    }

    /**
     * Sanitize the given options and return them
     *
     * @param   array  $options
     * @return  array  $options
     */
    protected function sanitizeOptions(array $options)
    {
        if (array_key_exists('defaultEmailAddress', $options)) {
            $options['defaultEmailAddress'] = is_string($options['defaultEmailAddress']) ? sanitize_email($options['defaultEmailAddress']) : get_bloginfo('admin_email');
        }

        if (array_key_exists('defaultEmailName', $options)) {
            $options['defaultEmailName'] = is_string($options['defaultEmailName']) ? sanitize_text_field($options['defaultEmailName']) : '';
        }

        if (array_key_exists('defaultFromEmailAddress', $options)) {
            $options['defaultFromEmailAddress'] = is_string($options['defaultFromEmailAddress']) ? sanitize_email($options['defaultFromEmailAddress']) : 'wordpress@' . preg_replace('/^www./', '', Quform::get($_SERVER, 'SERVER_NAME'));
        }

        if (array_key_exists('defaultFromEmailName', $options)) {
            $options['defaultFromEmailName'] = is_string($options['defaultFromEmailName']) ? sanitize_text_field($options['defaultFromEmailName']) : '';
        }

        if (array_key_exists('locale', $options)) {
            $options['locale'] = is_string($options['locale']) ? sanitize_text_field($options['locale']) : 'en-US';
        }

        if (array_key_exists('dateFormatJs', $options)) {
            $options['dateFormatJs'] = is_string($options['dateFormatJs']) ? sanitize_text_field($options['dateFormatJs']) : '';
        }

        if (array_key_exists('timeFormatJs', $options)) {
            $options['timeFormatJs'] = is_string($options['timeFormatJs']) ? sanitize_text_field($options['timeFormatJs']) : '';
        }

        if (array_key_exists('dateFormat', $options)) {
            $options['dateFormat'] = is_string($options['dateFormat']) ? sanitize_text_field($options['dateFormat']) : '';
        }

        if (array_key_exists('timeFormat', $options)) {
            $options['timeFormat'] = is_string($options['timeFormat']) ? sanitize_text_field($options['timeFormat']) : '';
        }

        if (array_key_exists('rtl', $options)) {
            $options['rtl'] = is_string($options['rtl']) ? sanitize_text_field($options['rtl']) : '';
        }

        if (array_key_exists('recaptchaSiteKey', $options)) {
            $options['recaptchaSiteKey'] = is_string($options['recaptchaSiteKey']) ? sanitize_text_field($options['recaptchaSiteKey']) : '';
        }

        if (array_key_exists('recaptchaSecretKey', $options)) {
            $options['recaptchaSecretKey'] = is_string($options['recaptchaSecretKey']) ? sanitize_text_field($options['recaptchaSecretKey']) : '';
        }

        if (array_key_exists('hcaptchaSiteKey', $options)) {
            $options['hcaptchaSiteKey'] = is_string($options['hcaptchaSiteKey']) ? sanitize_text_field($options['hcaptchaSiteKey']) : '';
        }

        if (array_key_exists('hcaptchaSecretKey', $options)) {
            $options['hcaptchaSecretKey'] = is_string($options['hcaptchaSecretKey']) ? sanitize_text_field($options['hcaptchaSecretKey']) : '';
        }

        if (array_key_exists('turnstileSiteKey', $options)) {
            $options['turnstileSiteKey'] = is_string($options['turnstileSiteKey']) ? sanitize_text_field($options['turnstileSiteKey']) : '';
        }

        if (array_key_exists('turnstileSecretKey', $options)) {
            $options['turnstileSecretKey'] = is_string($options['turnstileSecretKey']) ? sanitize_text_field($options['turnstileSecretKey']) : '';
        }

        if (array_key_exists('customCss', $options)) {
            $options['customCss'] = is_string($options['customCss']) ? wp_strip_all_tags($options['customCss']) : '';
        }

        if (array_key_exists('customCssTablet', $options)) {
            $options['customCssTablet'] = is_string($options['customCssTablet']) ? wp_strip_all_tags($options['customCssTablet']) : '';
        }

        if (array_key_exists('customCssPhone', $options)) {
            $options['customCssPhone'] = is_string($options['customCssPhone']) ? wp_strip_all_tags($options['customCssPhone']) : '';
        }

        if (array_key_exists('customJs', $options)) {
            $options['customJs'] = is_string($options['customJs']) ? $options['customJs'] : '';
        }

        if (array_key_exists('loadScripts', $options)) {
            $options['loadScripts'] = is_string($options['loadScripts']) ? sanitize_text_field($options['loadScripts']) : 'always';
        }

        if (array_key_exists('loadScriptsCustom', $options)) {
            $options['loadScriptsCustom'] = is_array($options['loadScriptsCustom']) ? $options['loadScriptsCustom'] : array();
        }

        if (array_key_exists('disabledStyles', $options)) {
            $options['disabledStyles'] = is_array($options['disabledStyles']) ? $options['disabledStyles'] : array(
                'fontAwesome' => false,
                'select2' => false,
                'qtip' => false,
                'fancybox' => false,
                'fancybox2' => false,
                'fancybox3' => false,
                'magnificPopup' => false
            );
        }

        if (array_key_exists('disabledScripts', $options)) {
            $options['disabledScripts'] = is_array($options['disabledScripts']) ? $options['disabledScripts'] : array(
                'fileUpload' => false,
                'scrollTo' => false,
                'select2' => false,
                'qtip' => false,
                'fancybox' => false,
                'fancybox2' => false,
                'fancybox3' => false,
                'magnificPopup' => false,
                'infieldLabels' => false,
                'datepicker' => false,
                'timepicker' => false
            );
        }

        if (array_key_exists('combineCss', $options)) {
            $options['combineCss'] = is_bool($options['combineCss']) ? $options['combineCss'] : true;
        }

        if (array_key_exists('combineJs', $options)) {
            $options['combineJs'] = is_bool($options['combineJs']) ? $options['combineJs'] : true;
        }

        if (array_key_exists('popupEnabled', $options)) {
            $options['popupEnabled'] = is_bool($options['popupEnabled']) ? $options['popupEnabled'] : false;
        }

        if (array_key_exists('popupScript', $options)) {
            $options['popupScript'] = is_string($options['popupScript']) ? sanitize_text_field($options['popupScript']) : 'fancybox-2';
        }

        if (array_key_exists('rawFix', $options)) {
            $options['rawFix'] = is_bool($options['rawFix']) ? $options['rawFix'] : false;
        }

        if (array_key_exists('scrollOffset', $options)) {
            $options['scrollOffset'] = is_string($options['scrollOffset']) && is_numeric($options['scrollOffset']) ? (string) (float) $options['scrollOffset'] : '50';
        }

        if (array_key_exists('scrollSpeed', $options)) {
            $options['scrollSpeed'] = is_string($options['scrollSpeed']) && is_numeric($options['scrollSpeed']) ? (string) (float) $options['scrollSpeed'] : '800';
        }

        if (array_key_exists('allowAllFileTypes', $options)) {
            $options['allowAllFileTypes'] = is_bool($options['allowAllFileTypes']) ? $options['allowAllFileTypes'] : false;
        }

        if (array_key_exists('showEditLink', $options)) {
            $options['showEditLink'] = is_bool($options['showEditLink']) ? $options['showEditLink'] : true;
        }

        if (array_key_exists('csrfProtection', $options)) {
            $options['csrfProtection'] = is_bool($options['csrfProtection']) ? $options['csrfProtection'] : true;
        }

        if (array_key_exists('supportPageCaching', $options)) {
            $options['supportPageCaching'] = is_bool($options['supportPageCaching']) ? $options['supportPageCaching'] : true;
        }

        if (array_key_exists('toolbarMenu', $options)) {
            $options['toolbarMenu'] = is_bool($options['toolbarMenu']) ? $options['toolbarMenu'] : true;
        }

        if (array_key_exists('dashboardWidget', $options)) {
            $options['dashboardWidget'] = is_bool($options['dashboardWidget']) ? $options['dashboardWidget'] : true;
        }

        if (array_key_exists('insertFormButton', $options)) {
            $options['insertFormButton'] = is_bool($options['insertFormButton']) ? $options['insertFormButton'] : true;
        }

        if (array_key_exists('preventFouc', $options)) {
            $options['preventFouc'] = is_bool($options['preventFouc']) ? $options['preventFouc'] : false;
        }

        if (array_key_exists('secureApiRequests', $options)) {
            $options['secureApiRequests'] = is_bool($options['secureApiRequests']) ? $options['secureApiRequests'] : true;
        }

        if (array_key_exists('saveEntries', $options)) {
            $options['saveEntries'] = is_bool($options['saveEntries']) ? $options['saveEntries'] : true;
        }

        if (array_key_exists('saveIpAddresses', $options)) {
            $options['saveIpAddresses'] = is_bool($options['saveIpAddresses']) ? $options['saveIpAddresses'] : true;
        }

        if (array_key_exists('referralEnabled', $options)) {
            $options['referralEnabled'] = is_bool($options['referralEnabled']) ? $options['referralEnabled'] : false;
        }

        if (array_key_exists('referralText', $options)) {
            $options['referralText'] = is_string($options['referralText']) ? wp_kses_post($options['referralText']) : __('Powered by Quform', 'quform');
        }

        if (array_key_exists('referralLink', $options)) {
            $options['referralLink'] = is_string($options['referralLink']) ? sanitize_text_field($options['referralLink']) : '';
        }

        return $options;
    }

    /**
     * Handle the Ajax request to rebuild the feature cache and custom CSS
     */
    public function rebuildScriptCache()
    {
        if ( ! current_user_can('quform_settings')) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Insufficient permissions', 'quform')
            ));
        }

        if ( ! check_ajax_referer('quform_rebuild_script_cache', false, false)) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Nonce check failed', 'quform')
            ));
        }

        $this->scriptLoader->rebuildScriptCache();

        wp_send_json(array(
            'type'    => 'success'
        ));
    }

    /**
     * Handle the request to enable the popup script
     */
    public function enablePopup()
    {
        $this->options->set('popupEnabled', true);
        $this->scriptLoader->rebuildScriptCache();
        exit;
    }

    /**
     * Handle the request to search posts via Ajax
     */
    public function searchPosts()
    {
        $this->validateSearchPostsRequest();

        $search = sanitize_text_field(wp_unslash($_GET['search']));
        $results = array();

        foreach (Quform::searchPosts($search) as $post) {
            $results[] = array('id' => $post->ID, 'text' => $post->post_title);
        }

        wp_send_json(array(
            'type' => 'success',
            'results' => $results
        ));
    }

    /**
     * Validate the request to search posts via Ajax
     */
    protected function validateSearchPostsRequest()
    {
        if ( ! Quform::isGetRequest() || ! isset($_GET['search']) || ! is_string($_GET['search'])) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Bad request', 'quform')
            ));
        }

        if ( ! current_user_can('quform_settings')) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Insufficient permissions', 'quform')
            ));
        }

        if ( ! check_ajax_referer('quform_settings_search_posts', false, false)) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Nonce check failed', 'quform')
            ));
        }
    }
}
