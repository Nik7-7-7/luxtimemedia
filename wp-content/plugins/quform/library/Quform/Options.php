<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Options
{
    /**
     * The key within the wp_options table
     *
     * @var string
     */
    protected $key;

    /**
     * The options
     *
     * @var array
     */
    protected $options = array();

    public function __construct($key)
    {
        $this->key = $key;

        $options = get_option($this->key);

        if ( ! is_array($options)) {
            $options = $this->getDefaults();
        }

        $this->options = $options;
    }

    /**
     * Get the default options
     *
     * @return array
     */
    protected function getDefaults()
    {
        return array(
            'defaultEmailAddress' => get_bloginfo('admin_email'),
            'defaultEmailName' => '',
            'defaultFromEmailAddress' => 'wordpress@' . preg_replace('/^www./', '', Quform::get($_SERVER, 'SERVER_NAME')),
            'defaultFromEmailName' => get_bloginfo('name'),
            'licenseKey' => '',
            'locale' => 'en-US',
            'dateFormatJs' => '',
            'timeFormatJs' => '',
            'dateTimeFormatJs' => '',
            'dateFormat' => '',
            'timeFormat' => '',
            'dateTimeFormat' => '',
            'rtl' => '',
            'recaptchaSiteKey' => '',
            'recaptchaSecretKey' => '',
            'hcaptchaSiteKey' => '',
            'hcaptchaSecretKey' => '',
            'turnstileSiteKey' => '',
            'turnstileSecretKey' => '',
            'customCss' => '',
            'customCssTablet' => '',
            'customCssPhone' => '',
            'customJs' => '',
            'loadScripts' => 'always',
            'loadScriptsCustom' => array(),
            'disabledStyles' => array(
                'fontAwesome' => false,
                'select2' => false,
                'qtip' => false,
                'fancybox' => false,
                'fancybox2' => false,
                'fancybox3' => false,
                'magnificPopup' => false
            ),
            'disabledScripts' => array(
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
            ),
            'combineCss' => true,
            'combineJs' => true,
            'popupEnabled' => false,
            'popupScript' => 'fancybox-2',
            'rawFix' => false,
            'scrollOffset' => '50',
            'scrollSpeed' => '800',
            'allowAllFileTypes' => false,
            'showEditLink' => true,
            'csrfProtection' => true,
            'supportPageCaching' => true,
            'toolbarMenu' => true,
            'dashboardWidget' => true,
            'insertFormButton' => true,
            'preventFouc' => false,
            'secureApiRequests' => true,
            'saveEntries' => true,
            'saveIpAddresses' => true,
            'alwaysShowFullDates' => false,
            'referralEnabled' => false,
            'referralText' => __('Powered by Quform', 'quform'),
            'referralLink' => '',
            'activeThemes' => array(),
            'activeLocales' => array(),
            'activeDatepickers' => array(),
            'activeTimepickers' => array(),
            'activeEnhancedUploaders' => array(),
            'activeEnhancedSelects' => array(),
            'activeCustomCss' => array(),
            'inactiveCustomCss' => array(),
            'cacheBuster' => time()
        );
    }

    /**
     * Save the options
     */
    protected function update()
    {
        update_option($this->key, $this->options);
    }

    /**
     * Get the value opf the option with the given key
     *
     * If it does not exist the given default will be returned
     * If the given default is null it will get the default value for the option
     *
     * @param   string      $key      The option key
     * @param   mixed|null  $default  The default to return if the key does not exist
     * @return  mixed
     */
    public function get($key, $default = null)
    {
        $value = Quform::get($this->options, $key, $default);

        if ($value === null) {
            $value = Quform::get($this->getDefaults(), $key, $default);
        }

        return apply_filters("quform_get_option_$key", $value, $default);
    }

    /**
     * Set the value of the option with the given key and save the options
     *
     * @param  string|array  $key
     * @param  null|mixed    $value
     */
    public function set($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->options[$k] = $v;
            }
        } else {
            $this->options[$key] = $value;
        }

        $this->update();
    }

    /**
     * Get the locale configured in the plugin settings
     *
     * @return string
     */
    public function getLocale() {
        return apply_filters('quform_locale', $this->get('locale'));
    }

    /**
     * Formats a date to local time and translates
     *
     * @param   string        $datetime           A date/time string able to be parsed by DateTime, in the UTC timezone
     * @param   boolean       $hideDateIfSameDay  Hides the date and only shows the time if the date is today
     * @param   string        $customDateFormat   Specify a custom date format
     * @return  string|false                      The formatted date or false if there was an error
     */
    public function formatDate($datetime, $hideDateIfSameDay = false, $customDateFormat = '')
    {
        if ( ! Quform::isNonEmptyString($datetime)) {
            return '';
        }

        try {
            $date = new DateTime($datetime, new DateTimeZone('UTC'));

            if (Quform::isNonEmptyString($customDateFormat)) {
                return Quform::date($customDateFormat, $date);
            }

            if ($this->get('alwaysShowFullDates')) {
                $hideDateIfSameDay = false;
            }

            $today = new DateTime('now', new DateTimeZone('UTC'));

            if ($hideDateIfSameDay && $date->format('Y-m-d') == $today->format('Y-m-d')) {
                $key = 'timeFormat';
            } else {
                $key = 'dateTimeFormat';
            }

            $format = $this->get($key);

            if ( ! Quform::isNonEmptyString($format)) {
                $locale = Quform::getLocale($this->getLocale());
                $format = $locale[$key];
            }

            return Quform::date($format, $date);
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * Called when the plugin is uninstalled, delete all options
     */
    public function uninstall()
    {
        // Delete options
        delete_option($this->key);
    }
}
