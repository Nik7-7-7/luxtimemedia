<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Entry_Exporter
{
    /**
     * @var Quform_Repository
     */
    protected $repository;

    /**
     * @var Quform_Form_Factory
     */
    protected $factory;

    /**
     * @var Quform_Options
     */
    protected $options;

    /**
     * @param  Quform_Repository    $repository
     * @param  Quform_Form_Factory  $factory
     * @param  Quform_Options       $options
     */
    public function __construct(Quform_Repository $repository, Quform_Form_Factory $factory, Quform_Options $options)
    {
        $this->repository = $repository;
        $this->factory = $factory;
        $this->options = $options;
    }

    /**
     * Handle the Ajax request to get the field choices list when exporting entries
     */
    public function getExportFieldList()
    {
        $formId = isset($_POST['form_id']) ? (int) $_POST['form_id'] : 0;

        if ($formId == 0) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Bad request', 'quform')
            ));
        }

        $config = $this->repository->getConfig($formId);

        if ($config === null) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Form not found', 'quform')
            ));
        }

        $form = $this->factory->create($config);

        $fieldList = array();

        foreach ($form->getRecursiveIterator() as $element) {
            if ($element->config('saveToDatabase')) {
                $fieldList[] = array(
                    'label' => $element->getAdminLabel(),
                    'identifier' => $element->getIdentifier(),
                    'value' => 'element_' . $element->getId()
                );

                if ($element instanceof Quform_Element_Name) {
                    foreach (Quform_Element_Name::$partKeys as $partKey => $partName) {
                        $part = $element->getPart($partKey);

                        if ($part instanceof Quform_Element_Field) {
                            $namePartLabel = sprintf(
                                /* translators: %1$s: element admin label, %2$s: name of the part */
                                __('%1$s [%2$s]', 'quform'),
                                $element->getAdminLabel(),
                                $this->getNameElementPartName($partKey)
                            );

                            $fieldList[] = array(
                                'label' => $namePartLabel,
                                'identifier' => $element->getIdentifier(),
                                'value' => 'element_' . $element->getId() . '.' . $part->getId()
                            );
                        }
                    }
                }
            }
        }

        foreach ($this->getCoreEntryColumns() as $key => $label) {
            $fieldList[] = array(
                'value' => $key,
                'label' => $label
            );
        }

        $fieldList = $this->sortFieldList($fieldList, $form->getId());

        $fieldList = apply_filters('quform_export_field_list', $fieldList, $form);
        $fieldList = apply_filters('quform_export_field_list_' . $form->getId(), $fieldList, $form);

        wp_send_json(array(
            'type' => 'success',
            'data' => $fieldList
        ));
    }

    /**
     * Get the name of the part of the name element with the given key
     *
     * @param   int     $partKey
     * @return  string
     */
    protected function getNameElementPartName($partKey)
    {
        $name = '';

        switch ($partKey) {
            case 1:
                $name = __('Prefix', 'quform');
                break;
            case 2:
                $name = __('First', 'quform');
                break;
            case 3:
                $name = __('Middle', 'quform');
                break;
            case 4:
                $name = __('Last', 'quform');
                break;
            case 5:
                $name = __('Suffix', 'quform');
                break;
        }

        return $name;
    }

    /**
     * Sort the field list if the user has previously sorted it
     *
     * @param   array  $fieldList  The current field list
     * @param   int    $formId     The form ID
     * @return  array              The sorted field list
     */
    protected function sortFieldList(array $fieldList, $formId)
    {
        $map = get_user_meta(get_current_user_id(), 'quform_export_field_list_map', true);

        if ( ! is_array($map) || ! isset($map[$formId])) {
            return $fieldList;
        }

        $fields = array_reverse($map[$formId]);

        foreach ($fields as $field) {
            foreach ($fieldList as $key => $fieldListItem) {
                if (isset($fieldListItem['value']) && $field == $fieldListItem['value']) {
                    $swap = array_splice($fieldList, $key, 1); // Pluck the item from the field list
                    array_splice($fieldList, 0, 0, $swap); // Prepend the item to the start of the array
                }
            }
        }

        return $fieldList;
    }

    /**
     * Handle the Ajax request to save the field choices list when they are reordered
     */
    public function saveExportFieldListOrder()
    {
        $formId = isset($_POST['form_id']) ? (int) $_POST['form_id'] : 0;
        $fields = isset($_POST['fields']) && is_string($_POST['fields']) ? json_decode(stripslashes($_POST['fields']), true) : null;

        if ($formId == 0 || ! is_array($fields)) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Bad request', 'quform')
            ));
        }

        if ( ! current_user_can('quform_export_entries')) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Insufficient permissions', 'quform')
            ));
        }

        $map = get_user_meta(get_current_user_id(), 'quform_export_field_list_map', true);

        if ( ! is_array($map)) {
            $map = array();
        }

        $map[$formId] = $fields;

        update_user_meta(get_current_user_id(), 'quform_export_field_list_map', $map);

        wp_send_json(array('type' => 'success'));
    }

    /**
     * Get the list of default entry columns
     *
     * @return array
     */
    protected function getCoreEntryColumns()
    {
        return array(
            'id' => __('Entry ID', 'quform'),
            'ip' => __('IP address', 'quform'),
            'form_url' => __('Form URL', 'quform'),
            'referring_url' => __('Referring URL', 'quform'),
            'post_id' => __('Page', 'quform'),
            'created_by' => __('User', 'quform'),
            'created_at' => __('Date', 'quform'),
            'updated_at' => __('Last modified', 'quform')
        );
    }

    /**
     * Generate the file containing exported entries
     *
     * @param  Quform_Form  $form     The form to export entries from
     * @param  array        $columns  The selected columns for the export file
     * @param  array        $format   The options for the format of the export file
     * @param  string       $from     The date of the earliest entry in the format YYYY-MM-DD
     * @param  string       $to       The date of the latest entry in the format YYYY-MM-DD
     */
    public function generateExportFile(Quform_Form $form, array $columns = array(), $format = array(), $from = '', $to = '')
    {
        $entries = $this->repository->exportEntries($form, $from, $to);

        // Sanitize chosen columns
        $coreColumns = $this->getCoreEntryColumns();
        $cols = array();

        foreach ($columns as $col) {
            if (array_key_exists($col, $coreColumns)) {
                // It's a core column, get the label
                $cols[$col] = $coreColumns[$col];
            } elseif (strpos($col, 'element_') !== false) {
                // It's an element column, so get the element admin label
                $elementId = str_replace('element_', '', $col);
                $partKey = null;
                $heading = '';

                if (strpos($elementId, '.') !== false) {
                    list($elementId, $partKey) = explode('.', $elementId, 2);
                }

                $element = $form->getElementById((int) $elementId);

                if ($element instanceof Quform_Element_Field) {
                    if ($element instanceof Quform_Element_Name && Quform::isNonEmptyString($partKey)) {
                        $part = $element->getPart($partKey);

                        if ($part instanceof Quform_Element_Field) {
                            $heading = sprintf(
                                /* translators: %1$s: element admin label, %2$s: name of the part */
                                __('%1$s [%2$s]', 'quform'),
                                $element->getAdminLabel(),
                                $this->getNameElementPartName($partKey)
                            );
                        }
                    } else {
                        $heading = $element->getAdminLabel();
                    }
                }

                $cols[$col] = $heading;
            }
        }

        require_once QUFORM_LIBRARY_PATH . '/PhpSpreadsheet/vendor/autoload.php';

        PhpOffice\PhpSpreadsheet\Cell\Cell::setValueBinder(new PhpOffice\PhpSpreadsheet\Cell\StringValueBinder());

        $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();

        try {
            $sheet = $spreadsheet->getActiveSheet();
        } catch (Exception $e) {
            wp_die(esc_html__('An error occurred creating the export file.', 'quform'));
            return;
        }

        $headingColumnCount = 1;
        foreach ($cols as $col) {
            $sheet->setCellValueByColumnAndRow($headingColumnCount++, 1, $col);
        }

        $rowCount = 2;

        // Write each entry
        if (is_array($entries)) {
            foreach ($entries as $entry) {
                $row = array();
                $columnCount = 1;

                foreach ($cols as $col => $label) {
                    if (strpos($col, 'element_') !== false) {
                        $entryKey = $col;
                        $elementId = str_replace('element_', '', $col);
                        $partKey = null;
                        $value = '';

                        if (strpos($elementId, '.') !== false) {
                            list($elementId, $partKey) = explode('.', $elementId, 2);
                            $entryKey = "element_$elementId";
                        }

                        if (isset($entry[$entryKey]) && Quform::isNonEmptyString($entry[$entryKey])) {
                            $element = $form->getElementById((int) $elementId);

                            if ($element instanceof Quform_Element_Field) {
                                $element->setValueFromStorage($entry[$entryKey]);

                                if ($element instanceof Quform_Element_Name && Quform::isNonEmptyString($partKey)) {
                                    $part = $element->getPart($partKey);

                                    if ($part instanceof Quform_Element_Field) {
                                        $value = $part->getValueText();
                                    }
                                } else {
                                    $value = $element->getValueText();
                                }
                            }
                        }

                        $row[$col] = $value;
                    } else {
                        $row[$col] = isset($entry[$col]) ? $entry[$col] : '';

                        // Format the date to include the WordPress Timezone offset
                        if ($col == 'created_at' || $col == 'updated_at') {
                            $row[$col] = $this->options->formatDate($row[$col]);
                        }
                    }

                    $row[$col] = apply_filters('quform_entry_exporter_value', $row[$col], $sheet, $columnCount, $rowCount, $row, $col, $form);

                    $sheet->setCellValueByColumnAndRow($columnCount, $rowCount, $row[$col]);
                    $columnCount++;
                }

                $rowCount++;
            }
        }

        switch (Quform::get($format, 'type')) {
            case 'csv':
            default:
                $contentType = 'text/csv';
                $extension = '.csv';
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
                $writer->setExcelCompatibility((bool) Quform::get($format, 'excelCompatibility', false));
                $writer->setDelimiter(Quform::get($format, 'delimiter', ','));
                $writer->setEnclosure(Quform::get($format, 'enclosure', '"'));
                $writer->setUseBOM((bool) Quform::get($format, 'useBom', false));
                $writer->setLineEnding(Quform::get($format, 'lineEndings', "\r\n"));
                break;
            case 'xls':
                $contentType = 'application/vnd.ms-excel';
                $extension = '.xls';
                $writer = new PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
                break;
            case 'xlsx':
                $contentType = 'application/vnd.ms-excel';
                $extension = '.xlsx';
                $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                break;
            case 'ods':
                $contentType = 'application/vnd.oasis.opendocument.spreadsheet';
                $extension = '.ods';
                $writer = new PhpOffice\PhpSpreadsheet\Writer\Ods($spreadsheet);
                break;
            case 'html':
                $contentType = 'text/html';
                $extension = '.html';
                $writer = new PhpOffice\PhpSpreadsheet\Writer\Html($spreadsheet);
                break;
        }

        // Send headers
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . sanitize_file_name($form->config('name')) . '-' . Quform::date('Y-m-d') . $extension . '"');
        header('Cache-Control: private, must-revalidate, max-age=0');

        // Send the file contents
        try {
            $writer->save('php://output');
        } catch (Exception $e) {
            // We can't write to the browser at this point due to previous headers, so just log the error
            Quform::debug(sprintf('Failed to write entry export file: %s', $e->getMessage()));
        }

        exit;
    }
}
