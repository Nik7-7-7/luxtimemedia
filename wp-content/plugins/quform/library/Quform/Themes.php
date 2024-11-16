<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Themes
{
    /**
     * @var array
     */
    protected $themes = array();

    /**
     * @var array
     */
    protected $coreThemes;

    public function __construct()
    {
        $this->coreThemes = array(
            'minimal' => array(
                'name' => 'Minimal',
                'version' => '1.0.0',
                'cssPath' => QUFORM_PATH . '/css/theme.minimal.min.css',
                'cssUrl' => Quform::url('css/theme.minimal.min.css'),
                'previewColor' => '#FFFFFF'
            ),
            'light' => array(
                'name' => 'Quform Light',
                'version' => '1.0.0',
                'cssPath' => QUFORM_PATH . '/css/theme.light.min.css',
                'cssUrl' => Quform::url('css/theme.light.min.css'),
                'previewColor' => '#FFFFFF'
            ),
            'dark' => array(
                'name' => 'Quform Dark',
                'version' => '1.0.0',
                'cssPath' => QUFORM_PATH . '/css/theme.dark.min.css',
                'cssUrl' => Quform::url('css/theme.dark.min.css'),
                'previewColor' => '#0d0d0c'
            ),
            'hollow' => array(
                'name' => 'Hollow',
                'version' => '1.0.0',
                'cssPath' => QUFORM_PATH . '/css/theme.hollow.min.css',
                'cssUrl' => Quform::url('css/theme.hollow.min.css'),
                'previewColor' => '#FFFFFF'
            ),
            'underlined' => array(
                'name' => 'Underline',
                'version' => '1.0.0',
                'cssPath' => QUFORM_PATH . '/css/theme.underlined.min.css',
                'cssUrl' => Quform::url('css/theme.underlined.min.css'),
                'previewColor' => '#FFFFFF'
            ),
            'simple' => array(
                'name' => 'Simple',
                'version' => '1.0.0',
                'cssPath' => QUFORM_PATH . '/css/theme.simple.min.css',
                'cssUrl' => Quform::url('css/theme.simple.min.css'),
                'previewColor' => '#FFFFFF'
            ),
            'react' => array(
                'name' => 'React',
                'version' => '1.0.0',
                'cssPath' => QUFORM_PATH . '/css/theme.react.min.css',
                'cssUrl' => Quform::url('css/theme.react.min.css'),
                'previewColor' => '#FFFFFF'
            ),
            'bootstrap' => array(
                'name' => 'Bootstrap',
                'version' => '1.0.0',
                'cssPath' => QUFORM_PATH . '/css/theme.bootstrap.min.css',
                'cssUrl' => Quform::url('css/theme.bootstrap.min.css'),
                'previewColor' => '#FFFFFF'
            ),
            'shine-gradient' => array(
                'name' => 'Shine Gradient',
                'version' => '1.0.0',
                'cssPath' => QUFORM_PATH . '/css/theme.shine-gradient.min.css',
                'cssUrl' => Quform::url('css/theme.shine-gradient.min.css'),
                'previewColor' => '#FFFFFF'
            ),
            'blend-gradient' => array(
                'name' => 'Blend Gradient',
                'version' => '1.0.0',
                'cssPath' => QUFORM_PATH . '/css/theme.blend-gradient.min.css',
                'cssUrl' => Quform::url('css/theme.blend-gradient.min.css'),
                'previewColor' => '#FFFFFF'
            ),
            'storm' => array(
                'name' => 'Storm',
                'version' => '1.0.0',
                'cssPath' => QUFORM_PATH . '/css/theme.storm.min.css',
                'cssUrl' => Quform::url('css/theme.storm.min.css'),
                'previewColor' => '#0d0d0c'
            )
        );
    }

    /**
     * @param  string  $key   Unique theme key
     * @param  array   $data  Theme data
     */
    public function register($key, array $data)
    {
        $this->themes[$key] = $data;
    }

    /**
     * @return array
     */
    public function getThemes()
    {
        return $this->themes;
    }

    /**
     * Get the theme with the given key
     *
     * @param   string      $key
     * @return  array|null
     */
    public function getTheme($key)
    {
        return isset($this->themes[$key]) ? $this->themes[$key] : null;
    }

    /**
     * Register the themes that are included with the plugin
     */
    public function registerCoreThemes()
    {
        foreach ($this->coreThemes as $key => $data) {
            $this->register($key, $data);
        }
    }

    /**
     * @param   string  $key
     * @return  bool
     */
    public function isCoreTheme($key)
    {
        return array_key_exists($key, $this->coreThemes);
    }

    /**
     * Get the CSS for the theme when using a custom primary color
     *
     * @param   string  $theme         The theme key
     * @param   int     $formId        The form ID
     * @param   string  $primaryColor  The primary CSS color
     * @return  string
     */
    public static function getPrimaryColorCustomCss($theme, $formId, $primaryColor)
    {
        $css = '';

        if ( ! Quform::isNonEmptyString($primaryColor)) {
            return $css;
        }

        switch ($theme) {
            case 'light':
                $css .= '.quform-%1$s.quform-theme-light .quform-page-progress-bar {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-light .quform-page-progress-tab.quform-current-tab {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-light .select2-container--quform.select2-container--open .select2-selection,
                .quform-%1$s-timepicker.quform-theme-light.quform-timepicker.k-list-container.k-popup,
                .quform-%1$s.quform-theme-light .quform-field-text:active,
                .quform-%1$s.quform-theme-light .quform-field-captcha:active,
                .quform-%1$s.quform-theme-light .quform-field-password:active,
                .quform-%1$s.quform-theme-light .quform-field-textarea:active,
                .quform-%1$s.quform-theme-light .quform-field-email:active,
                .quform-%1$s.quform-theme-light .quform-field-date:active,
                .quform-%1$s.quform-theme-light .quform-field-time:active,
                .quform-%1$s.quform-theme-light .quform-field-select:active,
                .quform-%1$s.quform-theme-light .quform-field-multiselect:active,
                .quform-%1$s.quform-theme-light .quform-field-text:focus,
                .quform-%1$s.quform-theme-light .quform-field-captcha:focus,
                .quform-%1$s.quform-theme-light .quform-field-password:focus,
                .quform-%1$s.quform-theme-light .quform-field-textarea:focus,
                .quform-%1$s.quform-theme-light .quform-field-email:focus,
                .quform-%1$s.quform-theme-light .quform-field-date:focus,
                .quform-%1$s.quform-theme-light .quform-field-time:focus,
                .quform-%1$s.quform-theme-light .quform-field-select:focus,
                .quform-%1$s.quform-theme-light .quform-field-multiselect:focus {
                    border-color: %2$s;
                    box-shadow: 0 0 16px -8px %2$s;
                }
                .quform-%1$s.quform-theme-light .quform-upload-progress-bar {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-light .quform-upload-file {
                    background-color: %2$s;
                }
                .quform-%1$s-select2.quform-theme-light.select2-container--quform .select2-dropdown {
                    border: 2px solid %2$s;
                }
                .quform-%1$s-select2.quform-theme-light.select2-container--quform .select2-dropdown--above {
                    border: 2px solid %2$s;
                    border-bottom: transparent;
                }
                .quform-%1$s-select2.quform-theme-light.select2-container--quform .select2-dropdown--below {
                    border: 2px solid %2$s;
                    border-top: transparent;
                }
                .quform-%1$s-datepicker.quform-theme-light.quform-datepicker .k-calendar .k-header,
                .quform-%1$s-datepicker.quform-theme-light.quform-datepicker .k-calendar .k-footer,
                .quform-%1$s-datepicker.quform-theme-light.quform-datepicker .k-calendar .k-header .k-state-hover {
                    background-color: %2$s;
                }
                .quform-%1$s-timepicker.quform-theme-light.quform-timepicker.k-popup ul.k-list li.k-item.k-state-selected {
                    color: %2$s;
                }
                .quform-%1$s-datepicker.quform-theme-light.quform-datepicker .k-calendar .k-content .k-link:hover,
                .quform-%1$s-datepicker.quform-theme-light.quform-datepicker .k-calendar td.k-state-focused .k-link,
                .quform-%1$s-datepicker.quform-theme-light.quform-datepicker .k-calendar td.k-state-selected.k-state-focused .k-link {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-light .quform-button-style-theme .quform-upload-button,
                .quform-%1$s.quform-theme-light .quform-options-style-button .quform-option-label,
                .quform-%1$s.quform-theme-light .quform-element-submit.quform-button-style-theme > .quform-button-submit-default > button,
                .quform-%1$s.quform-theme-light .quform-element-submit.quform-button-style-theme > .quform-button-next-default > button,
                .quform-%1$s.quform-theme-light .quform-element-submit.quform-button-style-theme > .quform-button-back-default > button {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-light .quform-loading-type-spinner-1 .quform-loading-spinner,
                .quform-%1$s.quform-theme-light .quform-loading-type-spinner-1 .quform-loading-spinner:after {
                    border-top-color: %2$s;
                }
                .quform-%1$s.quform-theme-light .quform-loading-type-spinner-2 .quform-loading-spinner {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-light .quform-loading-type-spinner-3 .quform-loading-spinner,
                .quform-%1$s.quform-theme-light .quform-loading-type-spinner-3 .quform-loading-spinner:after {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-light .quform-loading-type-spinner-4 .quform-loading-spinner:after {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-light .quform-loading-type-spinner-5 .quform-loading-spinner {
                    border-left-color: %2$s;
                }
                .quform-%1$s.quform-theme-light .quform-loading-type-spinner-6 .quform-loading-spinner-inner {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-light .quform-loading-type-spinner-7 .quform-loading-spinner-inner,
                .quform-%1$s.quform-theme-light .quform-loading-type-spinner-7 .quform-loading-spinner-inner:before,
                .quform-%1$s.quform-theme-light .quform-loading-type-spinner-7 .quform-loading-spinner-inner:after {
                    background-color: %2$s;
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-light .quform-loading-type-custom .quform-loading-inner {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-light .quform-referral-link a:hover {
                    color: %2$s;
                }';
                break;
            case 'dark':
                $css .= '.quform-%1$s.quform-theme-dark .quform-page-progress-bar {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-dark .quform-page-progress-tab.quform-current-tab {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-dark .select2-container--quform.select2-container--open .select2-selection,
                .quform-%1$s-timepicker.quform-theme-dark.quform-timepicker.k-list-container.k-popup,
                .quform-%1$s.quform-theme-dark .quform-field-text:active,
                .quform-%1$s.quform-theme-dark .quform-field-captcha:active,
                .quform-%1$s.quform-theme-dark .quform-field-password:active,
                .quform-%1$s.quform-theme-dark .quform-field-textarea:active,
                .quform-%1$s.quform-theme-dark .quform-field-email:active,
                .quform-%1$s.quform-theme-dark .quform-field-date:active,
                .quform-%1$s.quform-theme-dark .quform-field-time:active,
                .quform-%1$s.quform-theme-dark .quform-field-select:active,
                .quform-%1$s.quform-theme-dark .quform-field-multiselect:active,
                .quform-%1$s.quform-theme-dark .quform-field-text:focus,
                .quform-%1$s.quform-theme-dark .quform-field-captcha:focus,
                .quform-%1$s.quform-theme-dark .quform-field-password:focus,
                .quform-%1$s.quform-theme-dark .quform-field-textarea:focus,
                .quform-%1$s.quform-theme-dark .quform-field-email:focus,
                .quform-%1$s.quform-theme-dark .quform-field-date:focus,
                .quform-%1$s.quform-theme-dark .quform-field-time:focus,
                .quform-%1$s.quform-theme-dark .quform-field-select:focus,
                .quform-%1$s.quform-theme-dark .quform-field-multiselect:focus {
                    border-color: %2$s;
                    box-shadow: 0 0 16px -8px %2$s;
                }
                .quform-%1$s.quform-theme-dark .quform-upload-progress-bar {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-dark .quform-upload-file {
                    background-color: %2$s;
                }
                .quform-%1$s-select2.quform-theme-dark.select2-container--quform .select2-dropdown {
                    border: 2px solid %2$s;
                }
                .quform-%1$s-select2.quform-theme-dark.select2-container--quform .select2-dropdown--above {
                    border: 2px solid %2$s;
                    border-bottom: transparent;
                }
                .quform-%1$s-select2.quform-theme-dark.select2-container--quform .select2-dropdown--below {
                    border: 2px solid %2$s;
                    border-top: transparent;
                }
                .quform-%1$s-datepicker.quform-theme-dark.quform-datepicker .k-calendar .k-header,
                .quform-%1$s-datepicker.quform-theme-dark.quform-datepicker .k-calendar .k-footer,
                .quform-%1$s-datepicker.quform-theme-dark.quform-datepicker .k-calendar .k-header .k-state-hover {
                    background-color: %2$s;
                }
                .quform-%1$s-timepicker.quform-theme-dark.quform-timepicker.k-popup ul.k-list li.k-item.k-state-selected {
                    color: %2$s;
                }
                .quform-%1$s-datepicker.quform-theme-dark.quform-datepicker .k-calendar .k-content .k-link:hover,
                .quform-%1$s-datepicker.quform-theme-dark.quform-datepicker .k-calendar td.k-state-focused .k-link,
                .quform-%1$s-datepicker.quform-theme-dark.quform-datepicker .k-calendar td.k-state-selected.k-state-focused .k-link {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-dark .quform-button-style-theme .quform-upload-button,
                .quform-%1$s.quform-theme-dark .quform-options-style-button .quform-option-label,
                .quform-%1$s.quform-theme-dark .quform-element-submit.quform-button-style-theme > .quform-button-submit-default > button,
                .quform-%1$s.quform-theme-dark .quform-element-submit.quform-button-style-theme > .quform-button-next-default > button,
                .quform-%1$s.quform-theme-dark .quform-element-submit.quform-button-style-theme > .quform-button-back-default > button {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-dark .quform-loading-type-spinner-1 .quform-loading-spinner,
                .quform-%1$s.quform-theme-dark .quform-loading-type-spinner-1 .quform-loading-spinner:after {
                    border-top-color: %2$s;
                }
                .quform-%1$s.quform-theme-dark .quform-loading-type-spinner-2 .quform-loading-spinner {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-dark .quform-loading-type-spinner-3 .quform-loading-spinner,
                .quform-%1$s.quform-theme-dark .quform-loading-type-spinner-3 .quform-loading-spinner:after {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-dark .quform-loading-type-spinner-4 .quform-loading-spinner:after {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-dark .quform-loading-type-spinner-5 .quform-loading-spinner {
                    border-left-color: %2$s;
                }
                .quform-%1$s.quform-theme-dark .quform-loading-type-spinner-6 .quform-loading-spinner-inner {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-dark .quform-loading-type-spinner-7 .quform-loading-spinner-inner,
                .quform-%1$s.quform-theme-dark .quform-loading-type-spinner-7 .quform-loading-spinner-inner:before,
                .quform-%1$s.quform-theme-dark .quform-loading-type-spinner-7 .quform-loading-spinner-inner:after {
                    background-color: %2$s;
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-dark .quform-loading-type-custom .quform-loading-inner {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-dark .quform-referral-link a:hover {
                    color: %2$s;
                }';
                break;
            case 'hollow':
                $css .= '.quform-%1$s.quform-theme-hollow .quform-page-progress-bar {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-hollow .quform-page-progress-tab.quform-current-tab {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-hollow .select2-container--quform .select2-selection,
                .quform-%1$s-timepicker.quform-theme-hollow.quform-timepicker.k-list-container.k-popup,
                .quform-%1$s.quform-theme-hollow .quform-field-text,
                .quform-%1$s.quform-theme-hollow .quform-field-captcha,
                .quform-%1$s.quform-theme-hollow .quform-field-password,
                .quform-%1$s.quform-theme-hollow .quform-field-textarea,
                .quform-%1$s.quform-theme-hollow .quform-field-email,
                .quform-%1$s.quform-theme-hollow .quform-field-date,
                .quform-%1$s.quform-theme-hollow .quform-field-time,
                .quform-%1$s.quform-theme-hollow .quform-field-select,
                .quform-%1$s.quform-theme-hollow .quform-field-multiselect {
                    border: 3px solid %2$s;
                }
                .quform-%1$s.quform-theme-hollow .quform-field-icon {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-hollow .select2-container--quform .select2-selection:hover,
                .quform-%1$s.quform-theme-hollow .quform-field-text:hover,
                .quform-%1$s.quform-theme-hollow .quform-field-captcha:hover,
                .quform-%1$s.quform-theme-hollow .quform-field-password:hover,
                .quform-%1$s.quform-theme-hollow .quform-field-textarea:hover,
                .quform-%1$s.quform-theme-hollow .quform-field-email:hover,
                .quform-%1$s.quform-theme-hollow .quform-field-date:hover,
                .quform-%1$s.quform-theme-hollow .quform-field-time:hover,
                .quform-%1$s.quform-theme-hollow .quform-field-select:hover,
                .quform-%1$s.quform-theme-hollow .quform-field-multiselect:hover {
                    border-color: %2$s;
                }
                .quform-%1$s.quform-theme-hollow .select2-container--quform.select2-container--open .select2-selection,
                .quform-%1$s-timepicker.quform-theme-hollow.quform-timepicker.k-list-container.k-popup,
                .quform-%1$s.quform-theme-hollow .quform-field-text:active,
                .quform-%1$s.quform-theme-hollow .quform-field-captcha:active,
                .quform-%1$s.quform-theme-hollow .quform-field-password:active,
                .quform-%1$s.quform-theme-hollow .quform-field-textarea:active,
                .quform-%1$s.quform-theme-hollow .quform-field-email:active,
                .quform-%1$s.quform-theme-hollow .quform-field-date:active,
                .quform-%1$s.quform-theme-hollow .quform-field-time:active,
                .quform-%1$s.quform-theme-hollow .quform-field-select:active,
                .quform-%1$s.quform-theme-hollow .quform-field-multiselect:active,
                .quform-%1$s.quform-theme-hollow .quform-field-text:focus,
                .quform-%1$s.quform-theme-hollow .quform-field-captcha:focus,
                .quform-%1$s.quform-theme-hollow .quform-field-password:focus,
                .quform-%1$s.quform-theme-hollow .quform-field-textarea:focus,
                .quform-%1$s.quform-theme-hollow .quform-field-email:focus,
                .quform-%1$s.quform-theme-hollow .quform-field-date:focus,
                .quform-%1$s.quform-theme-hollow .quform-field-time:focus,
                .quform-%1$s.quform-theme-hollow .quform-field-select:focus,
                .quform-%1$s.quform-theme-hollow .quform-field-multiselect:focus {
                    border: 3px solid %2$s;
                }
                .quform-%1$s.quform-theme-hollow .quform-upload-progress-bar {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-hollow .quform-upload-file {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-hollow .quform-upload-dropzone {
                    border-color: %2$s;
                }
                .quform-%1$s.quform-theme-hollow .select2-container--quform .select2-selection--multiple .select2-selection__choice {
                    background-color: %2$s;
                    border: 1px solid %2$s;
                }
                .quform-%1$s-select2.quform-theme-hollow.select2-container--quform .select2-dropdown {
                    border: 3px solid %2$s;
                }
                .quform-%1$s-select2.quform-theme-hollow.select2-container--quform .select2-dropdown--above {
                    border: 3px solid %2$s;
                    border-bottom: transparent;
                }
                .quform-%1$s-select2.quform-theme-hollow.select2-container--quform .select2-dropdown--below {
                    border: 3px solid %2$s;
                    border-top: transparent;
                }
                .quform-%1$s-select2.quform-theme-hollow.select2-container--quform .select2-results__option--highlighted[aria-selected] {
                    background-color: %2$s;
                    border-color: %2$s;
                }
                .quform-%1$s-datepicker.quform-theme-hollow.quform-datepicker .k-calendar .k-header,
                .quform-%1$s-datepicker.quform-theme-hollow.quform-datepicker .k-calendar .k-footer,
                .quform-%1$s-datepicker.quform-theme-hollow.quform-datepicker .k-calendar .k-header .k-state-hover {
                    background-color: %2$s;
                }
                .quform-%1$s-timepicker.quform-theme-hollow.quform-timepicker.k-popup ul.k-list li.k-item {
                    color: #000;
                }
                .quform-%1$s-timepicker.quform-theme-hollow.quform-timepicker.k-popup ul.k-list li.k-item.k-state-hover {
                    background-color: %2$s;
                    border-color: %2$s;
                }
                .quform-%1$s-datepicker.quform-theme-hollow.quform-datepicker .k-calendar td.k-state-focused,
                .quform-%1$s-datepicker.quform-theme-hollow.quform-datepicker .k-calendar td.k-state-selected.k-state-focused {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-hollow .quform-group-style-plain > .quform-spacer > .quform-group-title-description .quform-group-title {
                    border-bottom: 2px solid %2$s;
                }
                .quform-%1$s.quform-theme-hollow .quform-group-style-bordered  > .quform-spacer > .quform-group-title-description {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-hollow .quform-group-style-bordered > .quform-spacer > .quform-child-elements,
                .quform-%1$s.quform-theme-hollow .quform-group-style-bordered > .quform-child-elements {
                    border: 2px solid %2$s;
                }
                .quform-%1$s.quform-theme-hollow .quform-button-style-theme .quform-upload-button,
                .quform-%1$s.quform-theme-hollow .quform-element-submit.quform-button-style-theme > .quform-button-submit-default > button,
                .quform-%1$s.quform-theme-hollow .quform-element-submit.quform-button-style-theme > .quform-button-next-default > button,
                .quform-%1$s.quform-theme-hollow .quform-element-submit.quform-button-style-theme > .quform-button-back-default > button {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-hollow .quform-options-style-button .quform-option-label {
                    border: 2px solid %2$s;
                }
                .quform-%1$s.quform-theme-hollow .quform-options-style-button .quform-option-label:active,
                .quform-%1$s.quform-theme-hollow .quform-options-style-button .quform-option-label:focus,
                .quform-%1$s.quform-theme-hollow .quform-options-style-button .quform-field-checkbox:checked + label,
                .quform-%1$s.quform-theme-hollow .quform-options-style-button .quform-field-radio:checked + label {
                    background-color: %2$s;
                    border: 2px solid %2$s;
                }
                .quform-%1$s.quform-theme-hollow .quform-button-style-theme .quform-upload-button:hover,
                .quform-%1$s.quform-theme-hollow .quform-element-submit.quform-button-style-theme > .quform-button-submit-default > button:hover,
                .quform-%1$s.quform-theme-hollow .quform-element-submit.quform-button-style-theme > .quform-button-next-default > button:hover,
                .quform-%1$s.quform-theme-hollow .quform-element-submit.quform-button-style-theme > .quform-button-back-default > button:hover {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-hollow .quform-loading-type-spinner-1 .quform-loading-spinner,
                .quform-%1$s.quform-theme-hollow .quform-loading-type-spinner-1 .quform-loading-spinner:after {
                    border-top-color: %2$s;
                }
                .quform-%1$s.quform-theme-hollow .quform-loading-type-spinner-2 .quform-loading-spinner {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-hollow .quform-loading-type-spinner-3 .quform-loading-spinner,
                .quform-%1$s.quform-theme-hollow .quform-loading-type-spinner-3 .quform-loading-spinner:after {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-hollow .quform-loading-type-spinner-4 .quform-loading-spinner:after {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-hollow .quform-loading-type-spinner-5 .quform-loading-spinner {
                    border-left-color: %2$s;
                }
                .quform-%1$s.quform-theme-hollow .quform-loading-type-spinner-6 .quform-loading-spinner-inner {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-hollow .quform-loading-type-spinner-7 .quform-loading-spinner-inner,
                .quform-%1$s.quform-theme-hollow .quform-loading-type-spinner-7 .quform-loading-spinner-inner:before,
                .quform-%1$s.quform-theme-hollow .quform-loading-type-spinner-7 .quform-loading-spinner-inner:after {
                    background-color: %2$s;
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-hollow .quform-loading-type-custom .quform-loading-inner {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-hollow .quform-referral-link a:hover {
                    color: %2$s;
                }';
                break;
            case 'underlined':
                $css .= '.quform-%1$s.quform-theme-underlined .quform-page-progress {
                    border-bottom: 1px solid %2$s;
                }
                .quform-%1$s.quform-theme-underlined .quform-page-progress-bar {
                    border-bottom: 4px solid %2$s;
                }
                .quform-%1$s.quform-theme-underlined .quform-page-progress-tab.quform-current-tab {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-underlined .select2-container--quform .select2-selection,
                .quform-%1$s.quform-theme-underlined .quform-field-text,
                .quform-%1$s.quform-theme-underlined .quform-field-captcha,
                .quform-%1$s.quform-theme-underlined .quform-field-password,
                .quform-%1$s.quform-theme-underlined .quform-field-textarea,
                .quform-%1$s.quform-theme-underlined .quform-field-email,
                .quform-%1$s.quform-theme-underlined .quform-field-date,
                .quform-%1$s.quform-theme-underlined .quform-field-time,
                .quform-%1$s.quform-theme-underlined .quform-field-select,
                .quform-%1$s.quform-theme-underlined .quform-field-multiselect {
                    border-bottom: 2px solid %2$s;
                    color: %2$s;
                }
                .quform-%1$s-timepicker.quform-theme-underlined.quform-timepicker.k-list-container.k-popup {
                    border-color: %2$s;
                }
                .quform-%1$s.quform-theme-underlined .quform-upload-progress-bar {
                    background-color: %2$s;
                    border-bottom: 2px solid %2$s;
                }
                .quform-%1$s.quform-theme-underlined .quform-upload-file {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-underlined .quform-upload-dropzone {
                    border-color: %2$s;
                }
                .quform-%1$s.quform-theme-underlined .select2-container--quform .select2-selection--multiple .select2-selection__choice {
                    background: %2$s;
                    border: 1px solid %2$s;
                }
                .quform-%1$s-select2.quform-theme-underlined.select2-container--quform .select2-dropdown {
                    border: 2px solid %2$s;
                }
                .quform-%1$s-select2.quform-theme-underlined.select2-container--quform .select2-dropdown--above {
                    border: 2px solid %2$s;
                }
                .quform-%1$s-select2.quform-theme-underlined.select2-container--quform .select2-dropdown--below {
                    border: 2px solid %2$s;
                }
                .quform-%1$s-datepicker.quform-theme-underlined.quform-datepicker .k-calendar .k-header,
                .quform-%1$s-datepicker.quform-theme-underlined.quform-datepicker .k-calendar .k-footer,
                .quform-%1$s-datepicker.quform-theme-underlined.quform-datepicker .k-calendar .k-header .k-state-hover {
                    background-color: %2$s;
                }
                .quform-%1$s-timepicker.quform-theme-underlined.quform-timepicker.k-popup ul.k-list li.k-item.k-state-selected {
                    color: %2$s;
                }
                .quform-%1$s-datepicker.quform-theme-underlined.quform-datepicker .k-calendar td.k-state-focused .k-link,
                .quform-%1$s-datepicker.quform-theme-underlined.quform-datepicker .k-calendar td.k-state-selected.k-state-focused .k-link {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-underlined .quform-group-style-bordered > .quform-spacer > .quform-child-elements,
                .quform-%1$s.quform-theme-underlined .quform-group-style-bordered > .quform-child-elements {
                    border: 4px solid %2$s;
                }
                .quform-%1$s.quform-theme-underlined .quform-button-style-theme .quform-upload-button,
                .quform-%1$s.quform-theme-underlined .quform-options-style-button .quform-option-label,
                .quform-%1$s.quform-theme-underlined .quform-element-submit.quform-button-style-theme > .quform-button-submit-default > button,
                .quform-%1$s.quform-theme-underlined .quform-element-submit.quform-button-style-theme > .quform-button-next-default > button,
                .quform-%1$s.quform-theme-underlined .quform-element-submit.quform-button-style-theme > .quform-button-back-default > button {
                    border: 2px solid %2$s;
                }
                .quform-%1$s.quform-theme-underlined .quform-loading-type-spinner-1 .quform-loading-spinner,
                .quform-%1$s.quform-theme-underlined .quform-loading-type-spinner-1 .quform-loading-spinner:after {
                    border-top-color: %2$s;
                }
                .quform-%1$s.quform-theme-underlined .quform-loading-type-spinner-2 .quform-loading-spinner {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-underlined .quform-loading-type-spinner-3 .quform-loading-spinner,
                .quform-%1$s.quform-theme-underlined .quform-loading-type-spinner-3 .quform-loading-spinner:after {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-underlined .quform-loading-type-spinner-4 .quform-loading-spinner:after {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-underlined .quform-loading-type-spinner-5 .quform-loading-spinner {
                    border-left-color: %2$s;
                }
                .quform-%1$s.quform-theme-underlined .quform-loading-type-spinner-6 .quform-loading-spinner-inner {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-underlined .quform-loading-type-spinner-7 .quform-loading-spinner-inner,
                .quform-%1$s.quform-theme-underlined .quform-loading-type-spinner-7 .quform-loading-spinner-inner:before,
                .quform-%1$s.quform-theme-underlined .quform-loading-type-spinner-7 .quform-loading-spinner-inner:after {
                    background-color: %2$s;
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-underlined .quform-loading-type-custom .quform-loading-inner {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-underlined .quform-referral-link a:hover {
                    color: %2$s;
                }';
                break;
            case 'simple':
                 $css .= '.quform-%1$s.quform-theme-simple .quform-page-progress-bar {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-simple .quform-page-progress-tab.quform-current-tab {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-simple .select2-container--quform.select2-container--open .select2-selection,
                .quform-%1$s-timepicker.quform-theme-simple.quform-timepicker.k-list-container.k-popup,
                .quform-%1$s.quform-theme-simple .quform-field-text:active,
                .quform-%1$s.quform-theme-simple .quform-field-captcha:active,
                .quform-%1$s.quform-theme-simple .quform-field-password:active,
                .quform-%1$s.quform-theme-simple .quform-field-textarea:active,
                .quform-%1$s.quform-theme-simple .quform-field-email:active,
                .quform-%1$s.quform-theme-simple .quform-field-date:active,
                .quform-%1$s.quform-theme-simple .quform-field-time:active,
                .quform-%1$s.quform-theme-simple .quform-field-select:active,
                .quform-%1$s.quform-theme-simple .quform-field-multiselect:active,
                .quform-%1$s.quform-theme-simple .quform-field-text:focus,
                .quform-%1$s.quform-theme-simple .quform-field-captcha:focus,
                .quform-%1$s.quform-theme-simple .quform-field-password:focus,
                .quform-%1$s.quform-theme-simple .quform-field-textarea:focus,
                .quform-%1$s.quform-theme-simple .quform-field-email:focus,
                .quform-%1$s.quform-theme-simple .quform-field-date:focus,
                .quform-%1$s.quform-theme-simple .quform-field-time:focus,
                .quform-%1$s.quform-theme-simple .quform-field-select:focus,
                .quform-%1$s.quform-theme-simple .quform-field-multiselect:focus {
                    border-color: %2$s;
                    box-shadow: 0 0 14px -8px %2$s, 0 1px 6px 0 rgba(0, 0, 0, 0.07) inset;
                }
                .quform-%1$s.quform-theme-simple .quform-upload-progress-bar {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-simple .quform-upload-file {
                    background-color: %2$s;
                }
                .quform-%1$s-select2.quform-theme-simple.select2-container--quform .select2-dropdown--above {
                    border: 1px solid %2$s;
                    border-bottom: transparent;
                    box-shadow: 0 -3px 14px -8px %2$s, 0 1px 6px 0 rgba(0, 0, 0, 0.07) inset;
                }
                .quform-%1$s-select2.quform-theme-simple.select2-container--quform .select2-dropdown--below {
                    border: 1px solid %2$s;
                    border-top: transparent;
                    box-shadow: 0 3px 14px -8px %2$s, 0 1px 6px 0 rgba(0, 0, 0, 0.07) inset;
                }
                .quform-%1$s-datepicker.quform-theme-simple.quform-datepicker .k-calendar .k-header,
                .quform-%1$s-datepicker.quform-theme-simple.quform-datepicker .k-calendar .k-footer,
                .quform-%1$s-datepicker.quform-theme-simple.quform-datepicker .k-calendar .k-header .k-state-hover {
                    background-color: %2$s;
                }
                .quform-%1$s-datepicker.quform-theme-simple.quform-datepicker .k-calendar td.k-state-focused .k-link,
                .quform-%1$s-datepicker.quform-theme-simple.quform-datepicker .k-calendar td.k-state-selected.k-state-focused .k-link {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-simple .quform-button-style-theme .quform-upload-button,
                .quform-%1$s.quform-theme-simple .quform-options-style-button .quform-option-label,
                .quform-%1$s.quform-theme-simple .quform-element-submit.quform-button-style-theme > .quform-button-submit-default > button,
                .quform-%1$s.quform-theme-simple .quform-element-submit.quform-button-style-theme > .quform-button-next-default > button,
                .quform-%1$s.quform-theme-simple .quform-element-submit.quform-button-style-theme > .quform-button-back-default > button {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-simple .quform-loading-type-spinner-1 .quform-loading-spinner,
                .quform-%1$s.quform-theme-simple .quform-loading-type-spinner-1 .quform-loading-spinner:after {
                    border-top-color: %2$s;
                }
                .quform-%1$s.quform-theme-simple .quform-loading-type-spinner-2 .quform-loading-spinner {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-simple .quform-loading-type-spinner-3 .quform-loading-spinner,
                .quform-%1$s.quform-theme-simple .quform-loading-type-spinner-3 .quform-loading-spinner:after {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-simple .quform-loading-type-spinner-4 .quform-loading-spinner:after {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-simple .quform-loading-type-spinner-5 .quform-loading-spinner {
                    border-left-color: %2$s;
                }
                .quform-%1$s.quform-theme-simple .quform-loading-type-spinner-6 .quform-loading-spinner-inner {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-simple .quform-loading-type-spinner-7 .quform-loading-spinner-inner,
                .quform-%1$s.quform-theme-simple .quform-loading-type-spinner-7 .quform-loading-spinner-inner:before,
                .quform-%1$s.quform-theme-simple .quform-loading-type-spinner-7 .quform-loading-spinner-inner:after {
                    background-color: %2$s;
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-simple .quform-loading-type-custom .quform-loading-inner {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-simple .quform-referral-link a:hover {
                    color: %2$s;
                }';
                break;
            case 'react':
                $css .= '.quform-%1$s.quform-theme-react .quform-page-progress-bar {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-react .quform-page-progress-tab.quform-current-tab {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-react .select2-container--quform.select2-container--open .select2-selection,
                .quform-%1$s-timepicker.quform-theme-react.quform-timepicker.k-list-container.k-popup,
                .quform-%1$s.quform-theme-react .quform-field-text:active,
                .quform-%1$s.quform-theme-react .quform-field-captcha:active,
                .quform-%1$s.quform-theme-react .quform-field-password:active,
                .quform-%1$s.quform-theme-react .quform-field-textarea:active,
                .quform-%1$s.quform-theme-react .quform-field-email:active,
                .quform-%1$s.quform-theme-react .quform-field-date:active,
                .quform-%1$s.quform-theme-react .quform-field-time:active,
                .quform-%1$s.quform-theme-react .quform-field-select:active,
                .quform-%1$s.quform-theme-react .quform-field-multiselect:active,
                .quform-%1$s.quform-theme-react .quform-field-text:focus,
                .quform-%1$s.quform-theme-react .quform-field-captcha:focus,
                .quform-%1$s.quform-theme-react .quform-field-password:focus,
                .quform-%1$s.quform-theme-react .quform-field-textarea:focus,
                .quform-%1$s.quform-theme-react .quform-field-email:focus,
                .quform-%1$s.quform-theme-react .quform-field-date:focus,
                .quform-%1$s.quform-theme-react .quform-field-time:focus,
                .quform-%1$s.quform-theme-react .quform-field-select:focus,
                .quform-%1$s.quform-theme-react .quform-field-multiselect:focus {
                    border-color: %2$s;
                }
                .quform-%1$s.quform-theme-react .quform-upload-progress-bar {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-react .quform-upload-file {
                    background-color: %2$s;
                }
                .quform-%1$s-select2.quform-theme-react.select2-container--quform .select2-dropdown {
                    border: 2px solid %2$s;
                }
                .quform-%1$s-select2.quform-theme-react.select2-container--quform .select2-dropdown--above {
                    border: 2px solid %2$s;
                }
                .quform-%1$s-select2.quform-theme-react.select2-container--quform .select2-dropdown--below {
                    border: 2px solid %2$s;
                }
                .quform-%1$s-datepicker.quform-theme-react.quform-datepicker .k-calendar .k-header,
                .quform-%1$s-datepicker.quform-theme-react.quform-datepicker .k-calendar .k-footer,
                .quform-%1$s-datepicker.quform-theme-react.quform-datepicker .k-calendar .k-header .k-state-hover {
                    background-color: %2$s;
                }
                .quform-%1$s-timepicker.quform-theme-react.quform-timepicker.k-popup ul.k-list li.k-item.k-state-selected {
                    color: %2$s;
                }
                .quform-%1$s-datepicker.quform-theme-react.quform-datepicker .k-calendar td.k-state-focused .k-link,
                .quform-%1$s-datepicker.quform-theme-react.quform-datepicker .k-calendar td.k-state-selected.k-state-focused .k-link {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-react .quform-button-style-theme .quform-upload-button,
                .quform-%1$s.quform-theme-react .quform-options-style-button .quform-option-label,
                .quform-%1$s.quform-theme-react .quform-element-submit.quform-button-style-theme > .quform-button-submit-default > button,
                .quform-%1$s.quform-theme-react .quform-element-submit.quform-button-style-theme > .quform-button-next-default > button,
                .quform-%1$s.quform-theme-react .quform-element-submit.quform-button-style-theme > .quform-button-back-default > button {
                    background-color: %2$s;
                    box-shadow: 0 -3px 0 0 rgba(0, 0, 0, 0.4) inset, 0 2px 3px 0 rgba(0, 0, 0, 0.1);
                }
                .quform-%1$s.quform-theme-react .quform-loading-type-spinner-1 .quform-loading-spinner,
                .quform-%1$s.quform-theme-react .quform-loading-type-spinner-1 .quform-loading-spinner:after {
                    border-top-color: %2$s;
                }
                .quform-%1$s.quform-theme-react .quform-loading-type-spinner-2 .quform-loading-spinner {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-react .quform-loading-type-spinner-3 .quform-loading-spinner,
                .quform-%1$s.quform-theme-react .quform-loading-type-spinner-3 .quform-loading-spinner:after {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-react .quform-loading-type-spinner-4 .quform-loading-spinner:after {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-react .quform-loading-type-spinner-5 .quform-loading-spinner {
                    border-left-color: %2$s;
                }
                .quform-%1$s.quform-theme-react .quform-loading-type-spinner-6 .quform-loading-spinner-inner {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-react .quform-loading-type-spinner-7 .quform-loading-spinner-inner,
                .quform-%1$s.quform-theme-react .quform-loading-type-spinner-7 .quform-loading-spinner-inner:before,
                .quform-%1$s.quform-theme-react .quform-loading-type-spinner-7 .quform-loading-spinner-inner:after {
                    background-color: %2$s;
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-react .quform-loading-type-custom .quform-loading-inner {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-react .quform-referral-link a:hover {
                    color: %2$s;
                }';
                break;
            case 'bootstrap':
                 $css .= '.quform-%1$s.quform-theme-bootstrap .quform-page-progress-bar {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-bootstrap .quform-page-progress-tab.quform-current-tab {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-bootstrap .select2-container--quform.select2-container--open .select2-selection,
                .quform-%1$s-timepicker.quform-theme-bootstrap.quform-timepicker.k-list-container.k-popup,
                .quform-%1$s.quform-theme-bootstrap .quform-field-text:active,
                .quform-%1$s.quform-theme-bootstrap .quform-field-captcha:active,
                .quform-%1$s.quform-theme-bootstrap .quform-field-password:active,
                .quform-%1$s.quform-theme-bootstrap .quform-field-textarea:active,
                .quform-%1$s.quform-theme-bootstrap .quform-field-email:active,
                .quform-%1$s.quform-theme-bootstrap .quform-field-date:active,
                .quform-%1$s.quform-theme-bootstrap .quform-field-time:active,
                .quform-%1$s.quform-theme-bootstrap .quform-field-select:active,
                .quform-%1$s.quform-theme-bootstrap .quform-field-multiselect:active,
                .quform-%1$s.quform-theme-bootstrap .quform-field-text:focus,
                .quform-%1$s.quform-theme-bootstrap .quform-field-captcha:focus,
                .quform-%1$s.quform-theme-bootstrap .quform-field-password:focus,
                .quform-%1$s.quform-theme-bootstrap .quform-field-textarea:focus,
                .quform-%1$s.quform-theme-bootstrap .quform-field-email:focus,
                .quform-%1$s.quform-theme-bootstrap .quform-field-date:focus,
                .quform-%1$s.quform-theme-bootstrap .quform-field-time:focus,
                .quform-%1$s.quform-theme-bootstrap .quform-field-select:focus,
                .quform-%1$s.quform-theme-bootstrap .quform-field-multiselect:focus {
                    border-color: %2$s;
                    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.075) inset, 0 0 16px -8px %2$s;
                }
                .quform-%1$s.quform-theme-bootstrap .quform-button-style-theme .quform-upload-button,
                .quform-%1$s.quform-theme-bootstrap .quform-options-style-button .quform-option-label,
                .quform-%1$s.quform-theme-bootstrap .quform-element-submit.quform-button-style-theme > .quform-button-submit-default > button,
                .quform-%1$s.quform-theme-bootstrap .quform-element-submit.quform-button-style-theme > .quform-button-next-default > button,
                .quform-%1$s.quform-theme-bootstrap .quform-element-submit.quform-button-style-theme > .quform-button-back-default > button {
                    background-color: %2$s;
                }

                .quform-%1$s.quform-theme-bootstrap .quform-options-style-button .quform-field-checkbox:checked + label,
                .quform-%1$s.quform-theme-bootstrap .quform-options-style-button .quform-field-radio:checked + label {
                     background-image: none;
                }
                .quform-%1$s-datepicker.quform-theme-bootstrap.quform-datepicker .k-calendar .k-header,
                .quform-%1$s-datepicker.quform-theme-bootstrap.quform-datepicker .k-calendar .k-footer,
                .quform-%1$s-datepicker.quform-theme-bootstrap.quform-datepicker .k-calendar .k-header .k-state-hover {
                    background-color: %2$s;
                }
                .quform-%1$s-timepicker.quform-theme-bootstrap.quform-timepicker.k-popup ul.k-list li.k-item.k-state-selected {
                    color: %2$s;
                }
                .quform-%1$s-datepicker.quform-theme-bootstrap.quform-datepicker .k-calendar td.k-state-focused .k-link,
                .quform-%1$s-datepicker.quform-theme-bootstrap.quform-datepicker .k-calendar td.k-state-selected.k-state-focused .k-link {
                    color: %2$s;
                }
                .quform-%1$s-select2.quform-theme-bootstrap.select2-container--quform .select2-dropdown {
                    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.075) inset, 0 4px 16px -8px %2$s;
                }
                .quform-%1$s-select2.quform-theme-bootstrap.select2-container--quform .select2-dropdown--above {
                    border: 1px solid %2$s;
                    border-bottom: transparent;
                }
                .quform-%1$s-select2.quform-theme-bootstrap.select2-container--quform .select2-dropdown--below {
                    border: 1px solid %2$s;
                    border-top: transparent;
                }
                .quform-%1$s.quform-theme-bootstrap .quform-loading-type-spinner-1 .quform-loading-spinner,
                .quform-%1$s.quform-theme-bootstrap .quform-loading-type-spinner-1 .quform-loading-spinner:after {
                    border-top-color: %2$s;
                }
                .quform-%1$s.quform-theme-bootstrap .quform-loading-type-spinner-2 .quform-loading-spinner {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-bootstrap .quform-loading-type-spinner-3 .quform-loading-spinner,
                .quform-%1$s.quform-theme-bootstrap .quform-loading-type-spinner-3 .quform-loading-spinner:after {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-bootstrap .quform-loading-type-spinner-4 .quform-loading-spinner:after {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-bootstrap .quform-loading-type-spinner-5 .quform-loading-spinner {
                    border-left-color: %2$s;
                }
                .quform-%1$s.quform-theme-bootstrap .quform-loading-type-spinner-6 .quform-loading-spinner-inner {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-bootstrap .quform-loading-type-spinner-7 .quform-loading-spinner-inner,
                .quform-%1$s.quform-theme-bootstrap .quform-loading-type-spinner-7 .quform-loading-spinner-inner:before,
                .quform-%1$s.quform-theme-bootstrap .quform-loading-type-spinner-7 .quform-loading-spinner-inner:after {
                    background-color: %2$s;
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-bootstrap .quform-loading-type-custom .quform-loading-inner {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-bootstrap .quform-referral-link a:hover {
                    color: %2$s;
                }';
                break;
            case 'shine-gradient':
                $css .= '.quform-%1$s.quform-theme-shine-gradient .quform-page-progress-bar {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-shine-gradient .quform-page-progress-tab.quform-current-tab {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-shine-gradient .quform-upload-progress-bar {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-shine-gradient .quform-upload-file {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-shine-gradient .quform-button-style-theme .quform-upload-button,
                .quform-%1$s.quform-theme-shine-gradient .quform-options-style-button .quform-option-label,
                .quform-%1$s.quform-theme-shine-gradient .quform-element-submit.quform-button-style-theme > .quform-button-submit-default > button,
                .quform-%1$s.quform-theme-shine-gradient .quform-element-submit.quform-button-style-theme > .quform-button-next-default > button,
                .quform-%1$s.quform-theme-shine-gradient .quform-element-submit.quform-button-style-theme > .quform-button-back-default > button {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-shine-gradient .quform-button-style-theme .quform-upload-button:hover,
                .quform-%1$s.quform-theme-shine-gradient .quform-options-style-button .quform-option-label:active,
                .quform-%1$s.quform-theme-shine-gradient .quform-options-style-button .quform-option-label:focus,
                .quform-%1$s.quform-theme-shine-gradient .quform-options-style-button .quform-field-checkbox:checked + label,
                .quform-%1$s.quform-theme-shine-gradient .quform-options-style-button .quform-field-radio:checked + label,
                .quform-%1$s.quform-theme-shine-gradient .quform-element-submit.quform-button-style-theme > .quform-button-submit-default > button:hover,
                .quform-%1$s.quform-theme-shine-gradient .quform-element-submit.quform-button-style-theme > .quform-button-next-default > button:hover,
                .quform-%1$s.quform-theme-shine-gradient .quform-element-submit.quform-button-style-theme > .quform-button-back-default > button:hover {
                    background-color: #41484f;
                }
                .quform-%1$s.quform-theme-shine-gradient .quform-referral-link a:hover {
                    color: %2$s;
                }';
                break;
            case 'blend-gradient':
                $css .= '.quform-%1$s.quform-theme-blend-gradient .quform-page-progress-bar {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-blend-gradient .quform-page-progress-tab.quform-current-tab {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-blend-gradient .quform-upload-progress-bar {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-blend-gradient .quform-upload-file {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-blend-gradient .quform-button-style-theme .quform-upload-button,
                .quform-%1$s.quform-theme-blend-gradient .quform-options-style-button .quform-option-label,
                .quform-%1$s.quform-theme-blend-gradient .quform-element-submit.quform-button-style-theme > .quform-button-submit-default > button,
                .quform-%1$s.quform-theme-blend-gradient .quform-element-submit.quform-button-style-theme > .quform-button-next-default > button,
                .quform-%1$s.quform-theme-blend-gradient .quform-element-submit.quform-button-style-theme > .quform-button-back-default > button {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-blend-gradient .quform-button-style-theme .quform-upload-button:hover,
                .quform-%1$s.quform-theme-blend-gradient .quform-options-style-button .quform-option-label:active,
                .quform-%1$s.quform-theme-blend-gradient .quform-options-style-button .quform-option-label:focus,
                .quform-%1$s.quform-theme-blend-gradient .quform-options-style-button .quform-field-checkbox:checked + label,
                .quform-%1$s.quform-theme-blend-gradient .quform-options-style-button .quform-field-radio:checked + label,
                .quform-%1$s.quform-theme-blend-gradient .quform-element-submit.quform-button-style-theme > .quform-button-submit-default > button:hover,
                .quform-%1$s.quform-theme-blend-gradient .quform-element-submit.quform-button-style-theme > .quform-button-next-default > button:hover,
                .quform-%1$s.quform-theme-blend-gradient .quform-element-submit.quform-button-style-theme > .quform-button-back-default > button:hover {
                    background-color: #41484f;
                }
                .quform-%1$s.quform-theme-blend-gradient .quform-referral-link a:hover {
                    color: %2$s;
                }';
                break;
            case 'storm':
                $css .= '.quform-%1$s.quform-theme-storm .quform-page-progress-bar {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-storm .quform-page-progress-tab.quform-current-tab {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-storm .quform-upload-progress-bar {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-storm .quform-upload-file {
                    background-color: %2$s;
                }
                .quform-%1$s-datepicker.quform-theme-storm.quform-datepicker .k-calendar .k-header,
                .quform-%1$s-datepicker.quform-theme-storm.quform-datepicker .k-calendar .k-footer,
                .quform-%1$s-datepicker.quform-theme-storm.quform-datepicker .k-calendar .k-header .k-state-hover {
                    background-color: %2$s;
                }
                .quform-%1$s-datepicker.quform-theme-storm.quform-datepicker .k-calendar .k-content .k-link:hover {
                    color: %2$s;
                }
                .quform-%1$s-timepicker.quform-theme-storm.quform-timepicker.k-popup ul.k-list li.k-item.k-state-selected {
                    color: %2$s;
                }
                .quform-%1$s-datepicker.quform-theme-storm.quform-datepicker .k-calendar td.k-state-focused .k-link,
                .quform-%1$s-datepicker.quform-theme-storm.quform-datepicker .k-calendar td.k-state-selected.k-state-focused .k-link {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-storm .quform-loading-type-spinner-1 .quform-loading-spinner,
                .quform-%1$s.quform-theme-storm .quform-loading-type-spinner-1 .quform-loading-spinner:after {
                    border-top-color: %2$s;
                }
                .quform-%1$s.quform-theme-storm .quform-loading-type-spinner-2 .quform-loading-spinner {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-storm .quform-loading-type-spinner-3 .quform-loading-spinner,
                .quform-%1$s.quform-theme-storm .quform-loading-type-spinner-3 .quform-loading-spinner:after {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-storm .quform-loading-type-spinner-4 .quform-loading-spinner:after {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-storm .quform-loading-type-spinner-5 .quform-loading-spinner {
                    border-left-color: %2$s;
                }
                .quform-%1$s.quform-theme-storm .quform-loading-type-spinner-6 .quform-loading-spinner-inner {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-storm .quform-loading-type-spinner-7 .quform-loading-spinner-inner,
                .quform-%1$s.quform-theme-storm .quform-loading-type-spinner-7 .quform-loading-spinner-inner:before,
                .quform-%1$s.quform-theme-storm .quform-loading-type-spinner-7 .quform-loading-spinner-inner:after {
                    background-color: %2$s;
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-storm .quform-loading-type-custom .quform-loading-inner {
                    color:%2$s;
                }
                .quform-%1$s.quform-theme-storm .quform-referral-link a:hover {
                    color: %2$s;
                }';
                break;
        }

        return sprintf($css, $formId, self::ensureThemeCompatible($primaryColor));
    }

    /**
     * Get the CSS for the theme when using a custom secondary color
     *
     * @param   string  $theme           The theme key
     * @param   int     $formId          The form ID
     * @param   string  $secondaryColor  The secondary CSS color
     * @return  string
     */
    public static function getSecondaryColorCustomCss($theme, $formId, $secondaryColor)
    {
        $css = '';

        if ( ! Quform::isNonEmptyString($secondaryColor)) {
            return $css;
        }

        switch ($theme) {
            case 'light':
                $css .= '.quform-%1$s.quform-theme-light .quform-button-style-theme .quform-upload-button:hover,
                .quform-%1$s.quform-theme-light .quform-options-style-button .quform-option-label:active,
                .quform-%1$s.quform-theme-light .quform-options-style-button .quform-option-label:focus,
                .quform-%1$s.quform-theme-light .quform-options-style-button .quform-field-checkbox:checked + label,
                .quform-%1$s.quform-theme-light .quform-options-style-button .quform-field-radio:checked + label,
                .quform-%1$s.quform-theme-light .quform-element-submit.quform-button-style-theme > .quform-button-submit-default > button:hover,
                .quform-%1$s.quform-theme-light .quform-element-submit.quform-button-style-theme > .quform-button-next-default > button:hover,
                .quform-%1$s.quform-theme-light .quform-element-submit.quform-button-style-theme > .quform-button-back-default > button:hover {
                    background-color: %2$s;
                }';
                break;
            case 'dark':
                $css .= '.quform-%1$s.quform-theme-dark .quform-button-style-theme .quform-upload-button:hover,
                .quform-%1$s.quform-theme-dark .quform-options-style-button .quform-option-label:active,
                .quform-%1$s.quform-theme-dark .quform-options-style-button .quform-option-label:focus,
                .quform-%1$s.quform-theme-dark .quform-options-style-button .quform-field-checkbox:checked + label,
                .quform-%1$s.quform-theme-dark .quform-options-style-button .quform-field-radio:checked + label,
                .quform-%1$s.quform-theme-dark .quform-element-submit.quform-button-style-theme > .quform-button-submit-default > button:hover,
                .quform-%1$s.quform-theme-dark .quform-element-submit.quform-button-style-theme > .quform-button-next-default > button:hover,
                .quform-%1$s.quform-theme-dark .quform-element-submit.quform-button-style-theme > .quform-button-back-default > button:hover {
                    background-color: %2$s;
                }';
                break;
            case 'underlined':
                $css .= '.quform-%1$s.quform-theme-underlined .select2-container--quform.select2-container--open .select2-selection,
                .quform-%1$s.quform-theme-underlined .quform-field-text:active,
                .quform-%1$s.quform-theme-underlined .quform-field-captcha:active,
                .quform-%1$s.quform-theme-underlined .quform-field-password:active,
                .quform-%1$s.quform-theme-underlined .quform-field-textarea:active,
                .quform-%1$s.quform-theme-underlined .quform-field-email:active,
                .quform-%1$s.quform-theme-underlined .quform-field-date:active,
                .quform-%1$s.quform-theme-underlined .quform-field-time:active,
                .quform-%1$s.quform-theme-underlined .quform-field-select:active,
                .quform-%1$s.quform-theme-underlined .quform-field-multiselect:active,
                .quform-%1$s.quform-theme-underlined .quform-field-text:focus,
                .quform-%1$s.quform-theme-underlined .quform-field-captcha:focus,
                .quform-%1$s.quform-theme-underlined .quform-field-password:focus,
                .quform-%1$s.quform-theme-underlined .quform-field-textarea:focus,
                .quform-%1$s.quform-theme-underlined .quform-field-email:focus,
                .quform-%1$s.quform-theme-underlined .quform-field-date:focus,
                .quform-%1$s.quform-theme-underlined .quform-field-time:focus,
                .quform-%1$s.quform-theme-underlined .quform-field-select:focus,
                .quform-%1$s.quform-theme-underlined .quform-field-multiselect:focus {
                    border-color: %2$s;
                }
                .quform-%1$s.quform-theme-underlined .quform-button-style-theme .quform-upload-button:hover,
                .quform-%1$s.quform-theme-underlined .quform-options-style-button .quform-option-label:active,
                .quform-%1$s.quform-theme-underlined .quform-options-style-button .quform-option-label:focus,
                .quform-%1$s.quform-theme-underlined .quform-options-style-button .quform-field-checkbox:checked + label,
                .quform-%1$s.quform-theme-underlined .quform-options-style-button .quform-field-radio:checked + label,
                .quform-%1$s.quform-theme-underlined .quform-element-submit.quform-button-style-theme > .quform-button-submit-default > button:hover,
                .quform-%1$s.quform-theme-underlined .quform-element-submit.quform-button-style-theme > .quform-button-next-default > button:hover,
                .quform-%1$s.quform-theme-underlined .quform-element-submit.quform-button-style-theme > .quform-button-back-default > button:hover {
                    background-color: %2$s;
                    border: 2px solid %2$s;
                }';
                break;
            case 'simple':
                $css .= '.quform-%1$s.quform-theme-simple .quform-button-style-theme .quform-upload-button:hover,
                .quform-%1$s.quform-theme-simple .quform-options-style-button .quform-option-label:active,
                .quform-%1$s.quform-theme-simple .quform-options-style-button .quform-option-label:focus,
                .quform-%1$s.quform-theme-simple .quform-options-style-button .quform-field-checkbox:checked + label,
                .quform-%1$s.quform-theme-simple .quform-options-style-button .quform-field-radio:checked + label,
                .quform-%1$s.quform-theme-simple .quform-element-submit.quform-button-style-theme > .quform-button-submit-default > button:hover,
                .quform-%1$s.quform-theme-simple .quform-element-submit.quform-button-style-theme > .quform-button-next-default > button:hover,
                .quform-%1$s.quform-theme-simple .quform-element-submit.quform-button-style-theme > .quform-button-back-default > button:hover {
                    background-color: %2$s;
                }';
                break;
            case 'react':
                $css .= '.quform-%1$s.quform-theme-react .quform-element-submit.quform-button-style-theme > .quform-button-submit-default > button:hover,
                .quform-%1$s.quform-theme-react .quform-element-submit.quform-button-style-theme > .quform-button-next-default > button:hover,
                .quform-%1$s.quform-theme-react .quform-element-submit.quform-button-style-theme > .quform-button-back-default > button:hover {
                    background-color: %2$s;
                    box-shadow: 0 -3px 0 0 rgba(0, 0, 0, 0.4) inset, 0 2px 3px 0 rgba(0, 0, 0, 0.1);
                }
                .quform-%1$s.quform-theme-react .quform-button-style-theme .quform-upload-button:hover,
                .quform-%1$s.quform-theme-react .quform-options-style-button .quform-option-label:active,
                .quform-%1$s.quform-theme-react .quform-options-style-button .quform-option-label:focus,
                .quform-%1$s.quform-theme-react .quform-options-style-button .quform-field-checkbox:checked + label,
                .quform-%1$s.quform-theme-react .quform-options-style-button .quform-field-radio:checked + label,
                .quform-%1$s.quform-theme-react .quform-element-submit.quform-button-style-theme > .quform-button-submit-default > button:active,
                .quform-%1$s.quform-theme-react .quform-element-submit.quform-button-style-theme > .quform-button-next-default > button:active,
                .quform-%1$s.quform-theme-react .quform-element-submit.quform-button-style-theme > .quform-button-back-default > button:active {
                    background-color: %2$s;
                }';
                break;
            case 'bootstrap':
                $css .= '.quform-%1$s.quform-theme-bootstrap .quform-button-style-theme .quform-upload-button,
                .quform-%1$s.quform-theme-bootstrap .quform-options-style-button .quform-option-label,
                .quform-%1$s.quform-theme-bootstrap .quform-element-submit.quform-button-style-theme > .quform-button-submit-default > button,
                .quform-%1$s.quform-theme-bootstrap .quform-element-submit.quform-button-style-theme > .quform-button-next-default > button,
                .quform-%1$s.quform-theme-bootstrap .quform-element-submit.quform-button-style-theme > .quform-button-back-default > button {
                    border: 1px solid %2$s;
                }
                .quform-%1$s.quform-theme-bootstrap .quform-button-style-theme .quform-upload-button:hover,
                .quform-%1$s.quform-theme-bootstrap .quform-options-style-button .quform-option-label:active,
                .quform-%1$s.quform-theme-bootstrap .quform-options-style-button .quform-option-label:focus,
                .quform-%1$s.quform-theme-bootstrap .quform-options-style-button .quform-field-checkbox:checked + label,
                .quform-%1$s.quform-theme-bootstrap .quform-options-style-button .quform-field-radio:checked + label,
                .quform-%1$s.quform-theme-bootstrap .quform-element-submit.quform-button-style-theme > .quform-button-submit-default > button:hover,
                .quform-%1$s.quform-theme-bootstrap .quform-element-submit.quform-button-style-theme > .quform-button-next-default > button:hover,
                .quform-%1$s.quform-theme-bootstrap .quform-element-submit.quform-button-style-theme > .quform-button-back-default > button:hover {
                    background-color: %2$s;
                    background-position: 0 -15px;
                    border: 1px solid %2$s;
                }
                .quform-%1$s.quform-theme-bootstrap .quform-options-style-button .quform-field-checkbox:checked + label,
                .quform-%1$s.quform-theme-bootstrap .quform-options-style-button .quform-field-radio:checked + label {
                     background-image: none;
                }
                .quform-%1$s.quform-theme-bootstrap .quform-element-submit.quform-button-style-theme > .quform-button-submit-default > button:active,
                .quform-%1$s.quform-theme-bootstrap .quform-element-submit.quform-button-style-theme > .quform-button-next-default > button:active,
                .quform-%1$s.quform-theme-bootstrap .quform-element-submit.quform-button-style-theme > .quform-button-back-default > button:active {
                    background-color: %2$s;
                    border-color: %2$s;
                }
                .quform-%1$s.quform-theme-bootstrap .quform-upload-progress-bar {
                    background-color: %2$s;
                }';
                break;
        }

        return sprintf($css, $formId, self::ensureThemeCompatible($secondaryColor));
    }

    /**
     * Get the CSS for the theme when using a custom primary text color
     *
     * @param   string  $theme                   The theme key
     * @param   int     $formId                  The form ID
     * @param   string  $primaryForegroundColor  The primary text CSS color
     * @return  string
     */
    public static function getPrimaryForegroundColorCustomCss($theme, $formId, $primaryForegroundColor)
    {
        $css = '';

        if ( ! Quform::isNonEmptyString($primaryForegroundColor)) {
            return $css;
        }

        switch ($theme) {
            case 'light':
                $css .= '.quform-%1$s.quform-theme-light .quform-page-progress-bar {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-light .quform-upload-progress-bar {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-light .quform-upload-file {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-light .quform-upload-file-remove:before,
                .quform-%1$s.quform-theme-light .quform-upload-file-remove:after {
                    background-color: %2$s;
                }
                .quform-%1$s-datepicker.quform-theme-light.quform-datepicker .k-calendar .k-header .k-link,
                .quform-%1$s-datepicker.quform-theme-light.quform-datepicker .k-calendar .k-footer .k-link {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-light .quform-button-style-theme .quform-upload-button,
                .quform-%1$s.quform-theme-light .quform-options-style-button .quform-option-label,
                .quform-%1$s.quform-theme-light .quform-element-submit.quform-button-style-theme > .quform-button-submit-default > button,
                .quform-%1$s.quform-theme-light .quform-element-submit.quform-button-style-theme > .quform-button-next-default > button,
                .quform-%1$s.quform-theme-light .quform-element-submit.quform-button-style-theme > .quform-button-back-default > button {
                    color: %2$s;
                }';
                break;
            case 'dark':
                $css .= '.quform-%1$s.quform-theme-dark .quform-page-progress-bar {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-dark .quform-upload-progress-bar {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-dark .quform-upload-file {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-dark .quform-upload-file-remove:before,
                .quform-%1$s.quform-theme-dark .quform-upload-file-remove:after {
                    background-color: %2$s;
                }
                .quform-%1$s-datepicker.quform-theme-dark.quform-datepicker .k-calendar .k-header .k-link,
                .quform-%1$s-datepicker.quform-theme-dark.quform-datepicker .k-calendar .k-footer .k-link {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-dark .quform-button-style-theme .quform-upload-button,
                .quform-%1$s.quform-theme-dark .quform-options-style-button .quform-option-label,
                .quform-%1$s.quform-theme-dark .quform-element-submit.quform-button-style-theme > .quform-button-submit-default > button,
                .quform-%1$s.quform-theme-dark .quform-element-submit.quform-button-style-theme > .quform-button-next-default > button,
                .quform-%1$s.quform-theme-dark .quform-element-submit.quform-button-style-theme > .quform-button-back-default > button {
                    color: %2$s;
                }';
                break;
            case 'hollow':
                $css .= '.quform-%1$s.quform-theme-hollow .quform-page-progress-bar {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-hollow .quform-field-icon {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-hollow .quform-upload-file {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-hollow .quform-upload-file-remove:before,
                .quform-%1$s.quform-theme-hollow .quform-upload-file-remove:after {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-hollow .quform-upload-file-remove:before,
                .quform-%1$s.quform-theme-hollow .quform-upload-file-remove:after {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-hollow .select2-container--quform .select2-selection--multiple .select2-selection__choice {
                    color: %2$s;
                }
                .quform-%1$s-select2.quform-theme-hollow.select2-container--quform .select2-results__option--highlighted[aria-selected] {
                    color: %2$s;
                }
                .quform-%1$s-datepicker.quform-theme-hollow.quform-datepicker .k-calendar .k-header .k-link,
                .quform-%1$s-datepicker.quform-theme-hollow.quform-datepicker .k-calendar .k-footer .k-link {
                    color: %2$s;
                }
                .quform-%1$s-timepicker.quform-theme-hollow.quform-timepicker.k-popup ul.k-list li.k-item.k-state-hover {
                    color: %2$s;
                }
                .quform-%1$s-datepicker.quform-theme-hollow.quform-datepicker .k-calendar td.k-state-focused,
                .quform-%1$s-datepicker.quform-theme-hollow.quform-datepicker .k-calendar td.k-state-selected.k-state-focused {
                    color: %2$s;
                }
                .quform-%1$s-timepicker.quform-theme-hollow.quform-timepicker.k-popup ul.k-list li.k-item.k-state-selected.k-state-hover,
                .quform-%1$s-datepicker.quform-theme-hollow.quform-datepicker .k-calendar td.k-state-focused .k-link,
                .quform-%1$s-datepicker.quform-theme-hollow.quform-datepicker .k-calendar td.k-state-selected.k-state-focused .k-link {
                    color: %2$s;
                }
                .quform-%1$s-datepicker.quform-theme-hollow.quform-datepicker .k-calendar td.k-state-focused .k-link:hover,
                .quform-%1$s-datepicker.quform-theme-hollow.quform-datepicker .k-calendar td.k-state-selected.k-state-focused .k-link:hover {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-hollow .quform-group-style-bordered  > .quform-spacer > .quform-group-title-description .quform-group-title {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-hollow .quform-group-style-bordered  > .quform-spacer > .quform-group-title-description p.quform-group-description {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-hollow .quform-button-style-theme .quform-upload-button,
                .quform-%1$s.quform-theme-hollow .quform-element-submit.quform-button-style-theme > .quform-button-submit-default > button,
                .quform-%1$s.quform-theme-hollow .quform-element-submit.quform-button-style-theme > .quform-button-next-default > button,
                .quform-%1$s.quform-theme-hollow .quform-element-submit.quform-button-style-theme > .quform-button-back-default > button {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-hollow .quform-options-style-button .quform-option-label:active,
                .quform-%1$s.quform-theme-hollow .quform-options-style-button .quform-option-label:focus,
                .quform-%1$s.quform-theme-hollow .quform-options-style-button .quform-field-checkbox:checked + label,
                .quform-%1$s.quform-theme-hollow .quform-options-style-button .quform-field-radio:checked + label {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-hollow .quform-button-style-theme .quform-upload-button:hover,
                .quform-%1$s.quform-theme-hollow .quform-element-submit.quform-button-style-theme > .quform-button-submit-default > button:hover,
                .quform-%1$s.quform-theme-hollow .quform-element-submit.quform-button-style-theme > .quform-button-next-default > button:hover,
                .quform-%1$s.quform-theme-hollow .quform-element-submit.quform-button-style-theme > .quform-button-back-default > button:hover {
                    color: %2$s;
                }';
                break;
            case 'underlined':
                $css .= '.quform-%1$s.quform-theme-underlined .quform-upload-progress-bar {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-underlined .quform-upload-file {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-underlined .quform-upload-file-remove:before,
                .quform-%1$s.quform-theme-underlined .quform-upload-file-remove:after {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-underlined .select2-container--quform .select2-selection--multiple .select2-selection__choice {
                    color: %2$s;
                }
                .quform-%1$s-datepicker.quform-theme-underlined.quform-datepicker .k-calendar .k-header .k-link,
                .quform-%1$s-datepicker.quform-theme-underlined.quform-datepicker .k-calendar .k-footer .k-link {
                    color: %2$s;
                }';
                break;
            case 'simple':
                $css .= '.quform-%1$s.quform-theme-simple .quform-page-progress-bar {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-simple .quform-upload-progress-bar {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-simple .quform-upload-file {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-simple .quform-upload-file-remove:before,
                .quform-%1$s.quform-theme-simple .quform-upload-file-remove:after {
                    background-color: %2$s;
                }
                .quform-%1$s-datepicker.quform-theme-simple.quform-datepicker .k-calendar .k-header .k-link,
                .quform-%1$s-datepicker.quform-theme-simple.quform-datepicker .k-calendar .k-footer .k-link {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-simple .quform-button-style-theme .quform-upload-button,
                .quform-%1$s.quform-theme-simple .quform-options-style-button .quform-option-label,
                .quform-%1$s.quform-theme-simple .quform-element-submit.quform-button-style-theme > .quform-button-submit-default > button,
                .quform-%1$s.quform-theme-simple .quform-element-submit.quform-button-style-theme > .quform-button-next-default > button,
                .quform-%1$s.quform-theme-simple .quform-element-submit.quform-button-style-theme > .quform-button-back-default > button {
                    color: %2$s;
                }';
                break;
            case 'react':
                $css .= '.quform-%1$s.quform-theme-react .quform-page-progress-bar {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-react .quform-upload-progress-bar {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-react .quform-upload-file {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-react .quform-upload-file-remove:before,
                .quform-%1$s.quform-theme-react .quform-upload-file-remove:after {
                    background-color: %2$s;
                }
                .quform-%1$s-datepicker.quform-theme-react.quform-datepicker .k-calendar .k-header .k-link,
                .quform-%1$s-datepicker.quform-theme-react.quform-datepicker .k-calendar .k-footer .k-link {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-react .quform-button-style-theme .quform-upload-button,
                .quform-%1$s.quform-theme-react .quform-options-style-button .quform-option-label,
                .quform-%1$s.quform-theme-react .quform-element-submit.quform-button-style-theme > .quform-button-submit-default > button,
                .quform-%1$s.quform-theme-react .quform-element-submit.quform-button-style-theme > .quform-button-next-default > button,
                .quform-%1$s.quform-theme-react .quform-element-submit.quform-button-style-theme > .quform-button-back-default > button {
                    color: %2$s;
                }';
                break;
            case 'bootstrap':
                $css .= '.quform-%1$s.quform-theme-bootstrap .quform-page-progress-bar {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-bootstrap .quform-button-style-theme .quform-upload-button,
                .quform-%1$s.quform-theme-bootstrap .quform-options-style-button .quform-option-label,
                .quform-%1$s.quform-theme-bootstrap .quform-element-submit.quform-button-style-theme > .quform-button-submit-default > button,
                .quform-%1$s.quform-theme-bootstrap .quform-element-submit.quform-button-style-theme > .quform-button-next-default > button,
                .quform-%1$s.quform-theme-bootstrap .quform-element-submit.quform-button-style-theme > .quform-button-back-default > button {
                    color: %2$s;
                }
                .quform-%1$s-datepicker.quform-theme-bootstrap.quform-datepicker .k-calendar .k-header .k-link,
                .quform-%1$s-datepicker.quform-theme-bootstrap.quform-datepicker .k-calendar .k-footer .k-link {
                    color: %2$s;
                }';
                break;
            case 'shine-gradient':
                $css .= '.quform-%1$s.quform-theme-shine-gradient .quform-page-progress-bar {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-shine-gradient .quform-upload-progress-bar {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-shine-gradient .quform-upload-file {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-shine-gradient .quform-upload-file-remove:before,
                .quform-%1$s.quform-theme-shine-gradient .quform-upload-file-remove:after {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-shine-gradient .quform-button-style-theme .quform-upload-button,
                .quform-%1$s.quform-theme-shine-gradient .quform-options-style-button .quform-option-label,
                .quform-%1$s.quform-theme-shine-gradient .quform-element-submit.quform-button-style-theme > .quform-button-submit-default > button,
                .quform-%1$s.quform-theme-shine-gradient .quform-element-submit.quform-button-style-theme > .quform-button-next-default > button,
                .quform-%1$s.quform-theme-shine-gradient .quform-element-submit.quform-button-style-theme > .quform-button-back-default > button {
                    color: %2$s;
                }';
                break;
            case 'blend-gradient':
                $css .= '.quform-%1$s.quform-theme-blend-gradient .quform-page-progress-bar {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-blend-gradient .quform-upload-progress-bar {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-blend-gradient .quform-upload-file {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-blend-gradient .quform-upload-file-remove:before,
                .quform-%1$s.quform-theme-blend-gradient .quform-upload-file-remove:after {
                    background-color: %2$s;
                }
                .quform-%1$s.quform-theme-blend-gradient .quform-button-style-theme .quform-upload-button,
                .quform-%1$s.quform-theme-blend-gradient .quform-options-style-button .quform-option-label,
                .quform-%1$s.quform-theme-blend-gradient .quform-element-submit.quform-button-style-theme > .quform-button-submit-default > button,
                .quform-%1$s.quform-theme-blend-gradient .quform-element-submit.quform-button-style-theme > .quform-button-next-default > button,
                .quform-%1$s.quform-theme-blend-gradient .quform-element-submit.quform-button-style-theme > .quform-button-back-default > button {
                    color: %2$s;
                }';
                break;
            case 'storm':
                $css .= '.quform-%1$s.quform-theme-storm .quform-page-progress-bar {
                    color: %2$s;
                }
                .quform-%1$s-datepicker.quform-theme-storm.quform-datepicker .k-calendar .k-footer .k-link,
                .quform-%1$s-datepicker.quform-theme-storm.quform-datepicker .k-calendar .k-header .k-link {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-storm .quform-upload-file {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-storm .quform-upload-file-remove:before,
                .quform-%1$s.quform-theme-storm .quform-upload-file-remove:after {
                    background-color: %2$s;
                }';
                break;
        }

        return sprintf($css, $formId, self::ensureThemeCompatible($primaryForegroundColor));
    }

    /**
     * Get the CSS for the theme when using a custom secondary text color
     *
     * @param   string  $theme                     The theme key
     * @param   int     $formId                    The form ID
     * @param   string  $secondaryForegroundColor  The secondary text CSS color
     * @return  string
     */
    public static function getSecondaryForegroundColorCustomCss($theme, $formId, $secondaryForegroundColor)
    {
        $css = '';

        if ( ! Quform::isNonEmptyString($secondaryForegroundColor)) {
            return $css;
        }

        switch ($theme) {
            case 'light':
                $css .= '.quform-%1$s.quform-theme-light .quform-button-style-theme .quform-upload-button:hover,
                .quform-%1$s.quform-theme-light .quform-options-style-button .quform-option-label:active,
                .quform-%1$s.quform-theme-light .quform-options-style-button .quform-option-label:focus,
                .quform-%1$s.quform-theme-light .quform-options-style-button .quform-field-checkbox:checked + label,
                .quform-%1$s.quform-theme-light .quform-options-style-button .quform-field-radio:checked + label,
                .quform-%1$s.quform-theme-light .quform-element-submit.quform-button-style-theme > .quform-button-submit-default > button:hover,
                .quform-%1$s.quform-theme-light .quform-element-submit.quform-button-style-theme > .quform-button-next-default > button:hover,
                .quform-%1$s.quform-theme-light .quform-element-submit.quform-button-style-theme > .quform-button-back-default > button:hover {
                    color: %2$s;
                }';
                break;
            case 'dark':
                $css .= '.quform-%1$s.quform-theme-dark .quform-button-style-theme .quform-upload-button:hover,
                .quform-%1$s.quform-theme-dark .quform-options-style-button .quform-option-label:active,
                .quform-%1$s.quform-theme-dark .quform-options-style-button .quform-option-label:focus,
                .quform-%1$s.quform-theme-dark .quform-options-style-button .quform-field-checkbox:checked + label,
                .quform-%1$s.quform-theme-dark .quform-options-style-button .quform-field-radio:checked + label,
                .quform-%1$s.quform-theme-dark .quform-element-submit.quform-button-style-theme > .quform-button-submit-default > button:hover,
                .quform-%1$s.quform-theme-dark .quform-element-submit.quform-button-style-theme > .quform-button-next-default > button:hover,
                .quform-%1$s.quform-theme-dark .quform-element-submit.quform-button-style-theme > .quform-button-back-default > button:hover {
                    color: %2$s;
                }';
                break;
            case 'underlined':
                $css .= '.quform-%1$s.quform-theme-underlined .quform-button-style-theme .quform-upload-button:hover,
                .quform-%1$s.quform-theme-underlined .quform-options-style-button .quform-option-label:active,
                .quform-%1$s.quform-theme-underlined .quform-options-style-button .quform-option-label:focus,
                .quform-%1$s.quform-theme-underlined .quform-options-style-button .quform-field-checkbox:checked + label,
                .quform-%1$s.quform-theme-underlined .quform-options-style-button .quform-field-radio:checked + label,
                .quform-%1$s.quform-theme-underlined .quform-element-submit.quform-button-style-theme > .quform-button-submit-default > button:hover,
                .quform-%1$s.quform-theme-underlined .quform-element-submit.quform-button-style-theme > .quform-button-next-default > button:hover,
                .quform-%1$s.quform-theme-underlined .quform-element-submit.quform-button-style-theme > .quform-button-back-default > button:hover {
                    color: %2$s;
                }';
                break;
            case 'simple':
                $css .= '.quform-%1$s.quform-theme-simple .quform-button-style-theme .quform-upload-button:hover,
                .quform-%1$s.quform-theme-simple .quform-options-style-button .quform-option-label:active,
                .quform-%1$s.quform-theme-simple .quform-options-style-button .quform-option-label:focus,
                .quform-%1$s.quform-theme-simple .quform-options-style-button .quform-field-checkbox:checked + label,
                .quform-%1$s.quform-theme-simple .quform-options-style-button .quform-field-radio:checked + label,
                .quform-%1$s.quform-theme-simple .quform-element-submit.quform-button-style-theme > .quform-button-submit-default > button:hover,
                .quform-%1$s.quform-theme-simple .quform-element-submit.quform-button-style-theme > .quform-button-next-default > button:hover,
                .quform-%1$s.quform-theme-simple .quform-element-submit.quform-button-style-theme > .quform-button-back-default > button:hover {
                    color: %2$s;
                }';
                break;
            case 'react':
                $css .= '.quform-%1$s.quform-theme-react .quform-element-submit.quform-button-style-theme > .quform-button-submit-default > button:hover,
                .quform-%1$s.quform-theme-react .quform-element-submit.quform-button-style-theme > .quform-button-next-default > button:hover,
                .quform-%1$s.quform-theme-react .quform-element-submit.quform-button-style-theme > .quform-button-back-default > button:hover {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-react .quform-button-style-theme .quform-upload-button:hover,
                .quform-%1$s.quform-theme-react .quform-options-style-button .quform-option-label:active,
                .quform-%1$s.quform-theme-react .quform-options-style-button .quform-option-label:focus,
                .quform-%1$s.quform-theme-react .quform-options-style-button .quform-field-checkbox:checked + label,
                .quform-%1$s.quform-theme-react .quform-options-style-button .quform-field-radio:checked + label,
                .quform-%1$s.quform-theme-react .quform-element-submit.quform-button-style-theme > .quform-button-submit-default > button:active,
                .quform-%1$s.quform-theme-react .quform-element-submit.quform-button-style-theme > .quform-button-next-default > button:active,
                .quform-%1$s.quform-theme-react .quform-element-submit.quform-button-style-theme > .quform-button-back-default > button:active {
                    color: %2$s;
                }';
                break;
            case 'bootstrap':
                $css .= '.quform-%1$s.quform-theme-bootstrap .quform-button-style-theme .quform-upload-button:hover,
                .quform-%1$s.quform-theme-bootstrap .quform-options-style-button .quform-option-label:active,
                .quform-%1$s.quform-theme-bootstrap .quform-options-style-button .quform-option-label:focus,
                .quform-%1$s.quform-theme-bootstrap .quform-options-style-button .quform-field-checkbox:checked + label,
                .quform-%1$s.quform-theme-bootstrap .quform-options-style-button .quform-field-radio:checked + label,
                .quform-%1$s.quform-theme-bootstrap .quform-element-submit.quform-button-style-theme > .quform-button-submit-default > button:hover,
                .quform-%1$s.quform-theme-bootstrap .quform-element-submit.quform-button-style-theme > .quform-button-next-default > button:hover,
                .quform-%1$s.quform-theme-bootstrap .quform-element-submit.quform-button-style-theme > .quform-button-back-default > button:hover {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-bootstrap .quform-element-submit.quform-button-style-theme > .quform-button-submit-default > button:active,
                .quform-%1$s.quform-theme-bootstrap .quform-element-submit.quform-button-style-theme > .quform-button-next-default > button:active,
                .quform-%1$s.quform-theme-bootstrap .quform-element-submit.quform-button-style-theme > .quform-button-back-default > button:active {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-bootstrap .quform-upload-progress-bar {
                    color: %2$s;
                }';
                break;
            case 'shine-gradient':
                $css .= '.quform-%1$s.quform-theme-shine-gradient .quform-button-style-theme .quform-upload-button:hover,
                .quform-%1$s.quform-theme-shine-gradient .quform-options-style-button .quform-option-label:active,
                .quform-%1$s.quform-theme-shine-gradient .quform-options-style-button .quform-option-label:focus,
                .quform-%1$s.quform-theme-shine-gradient .quform-options-style-button .quform-field-checkbox:checked + label,
                .quform-%1$s.quform-theme-shine-gradient .quform-options-style-button .quform-field-radio:checked + label,
                .quform-%1$s.quform-theme-shine-gradient .quform-element-submit.quform-button-style-theme > .quform-button-submit-default > button:hover,
                .quform-%1$s.quform-theme-shine-gradient .quform-element-submit.quform-button-style-theme > .quform-button-next-default > button:hover,
                .quform-%1$s.quform-theme-shine-gradient .quform-element-submit.quform-button-style-theme > .quform-button-back-default > button:hover {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-shine-gradient .quform-input-date .quform-field-icon:hover,
                .quform-%1$s.quform-theme-shine-gradient .quform-input-time .quform-field-icon:hover {
                    color: %2$s;
                }
                .quform-%1$s-timepicker.quform-theme-shine-gradient.quform-timepicker.k-list-container.k-popup,
                .quform-%1$s.quform-theme-shine-gradient .select2-container--quform.select2-container--open .select2-selection,
                .quform-%1$s.quform-theme-shine-gradient .quform-field-text:active,
                .quform-%1$s.quform-theme-shine-gradient .quform-field-captcha:active,
                .quform-%1$s.quform-theme-shine-gradient .quform-field-password:active,
                .quform-%1$s.quform-theme-shine-gradient .quform-field-textarea:active,
                .quform-%1$s.quform-theme-shine-gradient .quform-field-email:active,
                .quform-%1$s.quform-theme-shine-gradient .quform-field-date:active,
                .quform-%1$s.quform-theme-shine-gradient .quform-field-time:active,
                .quform-%1$s.quform-theme-shine-gradient .quform-field-select:active,
                .quform-%1$s.quform-theme-shine-gradient .quform-field-multiselect:active,
                .quform-%1$s.quform-theme-shine-gradient .quform-field-text:focus,
                .quform-%1$s.quform-theme-shine-gradient .quform-field-captcha:focus,
                .quform-%1$s.quform-theme-shine-gradient .quform-field-password:focus,
                .quform-%1$s.quform-theme-shine-gradient .quform-field-textarea:focus,
                .quform-%1$s.quform-theme-shine-gradient .quform-field-email:focus,
                .quform-%1$s.quform-theme-shine-gradient .quform-field-date:focus,
                .quform-%1$s.quform-theme-shine-gradient .quform-field-time:focus,
                .quform-%1$s.quform-theme-shine-gradient .quform-field-select:focus,
                .quform-%1$s.quform-theme-shine-gradient .quform-field-multiselect:focus {
                    color: %2$s;
                }
                .quform-%1$s-select2.quform-theme-shine-gradient.select2-container--quform .select2-results__option--highlighted[aria-selected] {
                    color: %2$s;
                }
                .quform-%1$s-timepicker.quform-theme-shine-gradient.quform-timepicker.k-popup ul.k-list li.k-item.k-state-selected {
                    color: %2$s;
                }
                .quform-%1$s-datepicker.quform-theme-shine-gradient.quform-datepicker .k-calendar td.k-state-focused .k-link,
                .quform-%1$s-datepicker.quform-theme-shine-gradient.quform-datepicker .k-calendar td.k-state-selected.k-state-focused .k-link {
                    color: %2$s;
                }
                .quform-%1$s-datepicker.quform-theme-shine-gradient.quform-datepicker .k-calendar .k-header .k-link,
                .quform-%1$s-datepicker.quform-theme-shine-gradient.quform-datepicker .k-calendar .k-footer .k-link {
                    color: %2$s;
                }';
                break;
            case 'blend-gradient':
                $css .= '.quform-%1$s.quform-theme-blend-gradient .quform-button-style-theme .quform-upload-button:hover,
                .quform-%1$s.quform-theme-blend-gradient .quform-options-style-button .quform-option-label:active,
                .quform-%1$s.quform-theme-blend-gradient .quform-options-style-button .quform-option-label:focus,
                .quform-%1$s.quform-theme-blend-gradient .quform-options-style-button .quform-field-checkbox:checked + label,
                .quform-%1$s.quform-theme-blend-gradient .quform-options-style-button .quform-field-radio:checked + label,
                .quform-%1$s.quform-theme-blend-gradient .quform-element-submit.quform-button-style-theme > .quform-button-submit-default > button:hover,
                .quform-%1$s.quform-theme-blend-gradient .quform-element-submit.quform-button-style-theme > .quform-button-next-default > button:hover,
                .quform-%1$s.quform-theme-blend-gradient .quform-element-submit.quform-button-style-theme > .quform-button-back-default > button:hover {
                    color: %2$s;
                }
                .quform-%1$s.quform-theme-blend-gradient .quform-input-date .quform-field-icon:hover,
                .quform-%1$s.quform-theme-blend-gradient .quform-input-time .quform-field-icon:hover {
                    color: %2$s;
                }
                .quform-%1$s-timepicker.quform-theme-blend-gradient.quform-timepicker.k-list-container.k-popup,
                .quform-%1$s.quform-theme-blend-gradient .select2-container--quform.select2-container--open .select2-selection,
                .quform-%1$s.quform-theme-blend-gradient .quform-field-text:active,
                .quform-%1$s.quform-theme-blend-gradient .quform-field-captcha:active,
                .quform-%1$s.quform-theme-blend-gradient .quform-field-password:active,
                .quform-%1$s.quform-theme-blend-gradient .quform-field-textarea:active,
                .quform-%1$s.quform-theme-blend-gradient .quform-field-email:active,
                .quform-%1$s.quform-theme-blend-gradient .quform-field-date:active,
                .quform-%1$s.quform-theme-blend-gradient .quform-field-time:active,
                .quform-%1$s.quform-theme-blend-gradient .quform-field-select:active,
                .quform-%1$s.quform-theme-blend-gradient .quform-field-multiselect:active,
                .quform-%1$s.quform-theme-blend-gradient .quform-field-text:focus,
                .quform-%1$s.quform-theme-blend-gradient .quform-field-captcha:focus,
                .quform-%1$s.quform-theme-blend-gradient .quform-field-password:focus,
                .quform-%1$s.quform-theme-blend-gradient .quform-field-textarea:focus,
                .quform-%1$s.quform-theme-blend-gradient .quform-field-email:focus,
                .quform-%1$s.quform-theme-blend-gradient .quform-field-date:focus,
                .quform-%1$s.quform-theme-blend-gradient .quform-field-time:focus,
                .quform-%1$s.quform-theme-blend-gradient .quform-field-select:focus,
                .quform-%1$s.quform-theme-blend-gradient .quform-field-multiselect:focus {
                    color: %2$s;
                }
                .quform-%1$s-select2.quform-theme-blend-gradient.select2-container--quform .select2-results__option--highlighted[aria-selected] {
                    color: %2$s;
                }
                .quform-%1$s-timepicker.quform-theme-blend-gradient.quform-timepicker.k-popup ul.k-list li.k-item.k-state-selected {
                    color: %2$s;
                }
                .quform-%1$s-datepicker.quform-theme-blend-gradient.quform-datepicker .k-calendar td.k-state-focused .k-link,
                .quform-%1$s-datepicker.quform-theme-blend-gradient.quform-datepicker .k-calendar td.k-state-selected.k-state-focused .k-link {
                    color: %2$s;
                }
                .quform-%1$s-datepicker.quform-theme-blend-gradient.quform-datepicker .k-calendar .k-header .k-link,
                .quform-%1$s-datepicker.quform-theme-blend-gradient.quform-datepicker .k-calendar .k-footer .k-link {
                    color: %2$s;
                }';
                break;
        }

        return sprintf($css, $formId, self::ensureThemeCompatible($secondaryForegroundColor));
    }

    /**
     * Some WP themes need !important added to the theme colors to work properly
     *
     * @param   string  $color
     * @return  string
     */
    protected static function ensureThemeCompatible($color)
    {
        $needsImportant = apply_filters(
            'quform_theme_colors_important',
            defined('AV_FRAMEWORK_VERSION')
        );

        if ($needsImportant && stripos($color, '!important') === false) {
            $color = "$color !important";
        }

        return $color;
    }
}
