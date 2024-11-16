<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Admin_Page_Tools_ExportEntries extends Quform_Admin_Page_Tools
{
    /**
     * @var Quform_Repository
     */
    protected $repository;

    /**
     * @var Quform_Entry_Exporter
     */
    protected $exporter;

    /**
     * @var Quform_Form_Factory
     */
    protected $formFactory;

    /**
     * @param  Quform_ViewFactory     $viewFactory
     * @param  Quform_Repository      $repository
     * @param  Quform_Entry_Exporter  $exporter
     * @param  Quform_Form_Factory    $formFactory
     */
    public function __construct(Quform_ViewFactory $viewFactory, Quform_Repository $repository,
                                Quform_Entry_Exporter $exporter, Quform_Form_Factory $formFactory)
    {
        parent::__construct($viewFactory, $repository);

        $this->exporter = $exporter;
        $this->formFactory = $formFactory;
    }

    public function init()
    {
        $this->template = QUFORM_TEMPLATE_PATH  . '/admin/tools/export-entries.php';
    }

    /**
     * Enqueue the page styles
     */
    protected function enqueueStyles()
    {
        wp_enqueue_style('qtip2', Quform::url('css/jquery.qtip.min.css'), array(), '3.0.4');
        wp_enqueue_style('kendo-common-material', Quform::url('css/kendo.common-material.min.css'), array(), '2020.2.617');
        wp_enqueue_style('kendo-material', Quform::url('css/kendo.material.min.css'), array(), '2020.2.617');

        parent::enqueueStyles();
    }

    /**
     * Enqueue the page scripts
     */
    protected function enqueueScripts()
    {
        wp_enqueue_script('qtip2', Quform::url('js/jquery.qtip.min.js'), array('jquery'), '3.0.4', true);
        wp_enqueue_script('kendo-core', Quform::url('js/kendo.core.min.js'), array('jquery'), '2020.2.617', true);
        wp_enqueue_script('kendo-calendar', Quform::url('js/kendo.calendar.min.js'), array('kendo-core'), '2020.2.617', true);
        wp_enqueue_script('kendo-popup', Quform::url('js/kendo.popup.min.js'), array('kendo-core'), '2020.2.617', true);
        wp_enqueue_script('kendo-datepicker', Quform::url('js/kendo.datepicker.min.js'), array('kendo-core', 'kendo-popup', 'kendo-calendar'), '2020.2.617', true);

        parent::enqueueScripts();

        wp_enqueue_script('quform-tools-export-entries', Quform::adminUrl('js/tools.export-entries.min.js'), array('jquery', 'jquery-ui-sortable'), QUFORM_VERSION, true);
        wp_localize_script('quform-tools-export-entries', 'quformToolsExportEntriesL10n', $this->getScriptL10n());
    }

    /**
     * JavaScript l10n
     *
     * @return array
     */
    protected function getScriptL10n()
    {
        return array(
            'exportEntriesNonce' => wp_create_nonce('quform_export_entries'),
            'errorExportingEntries' => __('An error occurred exporting entries', 'quform')
        );
    }

    /**
     * Set the page title
     *
     * @return string
     */
    protected function getAdminTitle()
    {
        return __('Export Entries', 'quform');
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
            '<div class="qfb-nav-item qfb-nav-page-info"><i class="qfb-nav-page-icon qfb-icon qfb-icon-file-excel-o"></i><span class="qfb-nav-page-title">%s</span></div>',
            esc_html__('Export entries', 'quform')
        );

        return parent::getNavHtml($currentForm, $extra);
    }

    /**
     * Process this page and send data to the view
     */
    public function process()
    {
        if ( ! current_user_can('quform_export_entries')) {
            wp_die(__( 'You do not have sufficient permissions to access this page.', 'quform'), 403);
        }

        if (Quform::isPostRequest() && Quform::get($_POST, 'qfb_do_entries_export')) {
            $_POST = wp_unslash($_POST);

            if ( ! wp_verify_nonce(Quform::get($_POST, '_wpnonce'), 'quform_export_entries')) {
                $this->addMessage('error', __('Nonce check failed', 'quform'));
                return;
            }

            $this->saveFormatSettings();
            $this->processExport();
        } else {
            if ( ! extension_loaded('zip')) {
                $this->addMessage('error', __('The Zip extension for PHP was not detected. The exporter requires that this extension is enabled on the server, find out more at Forms &rarr; Settings &rarr; Tweaks &amp; Troubleshooting &rarr; Server Compatibility.', 'quform'));
            }
        }

        $active = apply_filters('quform_export_entries_show_inactive', true) ? null : true;
        $orderBy = get_user_meta(get_current_user_id(), 'quform_forms_order_by', true);
        $order = get_user_meta(get_current_user_id(), 'quform_forms_order', true);

        $this->view->with(array(
            'formatSettings' => $this->loadFormatSettings(),
            'forms' => $this->repository->formsToSelectArray($active, $orderBy, $order)
        ));
    }

    /**
     * Process the export request
     */
    protected function processExport()
    {
        $id = (int) Quform::get($_POST, 'qfb_form_id');

        if ( ! is_numeric($id)) {
            $this->addMessage('error', __('Select a form', 'quform'));
            return;
        }

        $config = $this->repository->getConfig($id);

        if ( ! is_array($config)) {
            $this->addMessage('error', __('The selected form does not exist', 'quform'));
            return;
        }

        $this->exporter->generateExportFile(
            $this->formFactory->create($config),
            $this->sanitizeColumns(Quform::get($_POST, 'qfb_columns')),
            $this->getPreparedFormatSettings(),
            $this->sanitizeDate(Quform::get($_POST, 'qfb_date_from')),
            $this->sanitizeDate(Quform::get($_POST, 'qfb_date_to'))
        );
    }

    /**
     * Sanitize the given columns
     *
     * @param   array|null  $columns
     * @return  array
     */
    protected function sanitizeColumns($columns)
    {
        if ( ! is_array($columns)) {
            $columns = array();
        }

        foreach ($columns as $key => $column) {
            $columns[$key] = sanitize_text_field($columns[$key]);
        }

        return $columns;
    }

    /**
     * Sanitize the given columns
     *
     * @deprecated  2.4.0
     * @param   array|null  $columns
     * @return  array
     */
    protected function sanitiseColumns($columns)
    {
        _deprecated_function(__METHOD__, '2.4.0', 'Quform_Admin_Page_Tools_ExportEntries::sanitizeColumns()');

        return $this->sanitizeColumns($columns);
    }

    /**
     * Sanitize the given date
     *
     * @param   string  $date  The date in the format YYYY-MM-DD
     * @return  string
     */
    protected function sanitizeDate($date)
    {
        $sanitized = '';

        if (Quform::isNonEmptyString($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $sanitized = $date;
        }

        return $sanitized;
    }

    /**
     * Sanitize the given date
     *
     * @deprecated  2.4.0
     * @param   string  $date  The date in the format YYYY-MM-DD
     * @return  string
     */
    protected function sanitiseDate($date)
    {
        _deprecated_function(__METHOD__, '2.4.0', 'Quform_Admin_Page_Tools_ExportEntries::sanitizeDate()');

        return $this->sanitizeDate($date);
    }

    /**
     * Load the format settings
     *
     * Returns saved user settings or the defaults if there are no saved settings
     *
     * @return array
     */
    protected function loadFormatSettings()
    {
        $userSettings = get_user_meta(get_current_user_id(), 'quform_export_entries_format_settings', true);

        if ( ! is_array($userSettings)) {
            $userSettings = array();
        }

        $settings = array(
            'type' => Quform::get($userSettings, 'type', 'csv'),
            'excelCompatibility' => Quform::get($userSettings, 'excelCompatibility', false),
            'delimiter' => Quform::get($userSettings, 'delimiter', 'comma'),
            'delimiterCustom' => Quform::get($userSettings, 'delimiterCustom', ''),
            'enclosure' => Quform::get($userSettings, 'enclosure', 'double-quotes'),
            'enclosureCustom' => Quform::get($userSettings, 'enclosureCustom', ''),
            'useBom' => Quform::get($userSettings, 'useBom', false),
            'lineEndings' => Quform::get($userSettings, 'lineEndings', 'windows')
        );

        return $settings;
    }

    /**
     * Save the format settings for this user
     */
    protected function saveFormatSettings()
    {
        update_user_meta(get_current_user_id(), 'quform_export_entries_format_settings', $this->getSanitizedFormatSettings());
    }

    /**
     * Sanitize and return the submitted format settings
     *
     * @return array
     */
    protected function getSanitizedFormatSettings()
    {
        return array(
            'type' => sanitize_key(Quform::get($_POST, 'qfb_format_type')),
            'excelCompatibility' => Quform::get($_POST, 'qfb_format_csv_excel_compatibility') == '1',
            'delimiter' => sanitize_key(Quform::get($_POST, 'qfb_format_csv_delimiter')),
            'delimiterCustom' => sanitize_text_field(Quform::get($_POST, 'qfb_format_csv_delimiter_custom')),
            'enclosure' => sanitize_key(Quform::get($_POST, 'qfb_format_csv_enclosure')),
            'enclosureCustom' => sanitize_text_field(Quform::get($_POST, 'qfb_format_csv_enclosure_custom')),
            'useBom' => Quform::get($_POST, 'qfb_format_csv_use_bom') == '1',
            'lineEndings' => sanitize_key(Quform::get($_POST, 'qfb_format_csv_line_endings'))
        );
    }

    /**
     * Sanitize and return the submitted format settings
     *
     * @deprecated  2.4.0
     * @return array
     */
    protected function getSanitisedFormatSettings()
    {
        _deprecated_function(__METHOD__, '2.4.0', 'Quform_Admin_Page_Tools_ExportEntries::getSanitizedFormatSettings()');

        return $this->getSanitizedFormatSettings();
    }

    /**
     * Get the prepared format settings to be used in the export generation
     *
     * @return array
     */
    protected function getPreparedFormatSettings()
    {
        $formatSettings = $this->getSanitizedFormatSettings();

        switch ($formatSettings['delimiter']) {
            default:
            case 'comma':
                $formatSettings['delimiter'] = ',';
                break;
            case 'semicolon':
                $formatSettings['delimiter'] = ';';
                break;
            case 'tab':
                $formatSettings['delimiter'] = "\t";
                break;
            case 'space':
                $formatSettings['delimiter'] = ' ';
                break;
            case 'custom':
                $formatSettings['delimiter'] = $formatSettings['delimiterCustom'];
                break;
        }

        unset($formatSettings['delimiterCustom']);

        switch ($formatSettings['enclosure']) {
            default:
            case 'double-quotes':
                $formatSettings['enclosure'] = '"';
                break;
            case 'single-quotes':
                $formatSettings['enclosure'] = "'";
                break;
            case 'custom':
                $formatSettings['enclosure'] = $formatSettings['enclosureCustom'];
                break;
        }

        unset($formatSettings['enclosureCustom']);

        $formatSettings['lineEndings'] = $formatSettings['lineEndings'] == 'windows' ? "\r\n" : "\n";

        return $formatSettings;
    }
}
