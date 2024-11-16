<?php if (!defined('ABSPATH')) exit;
/** @var Quform_Admin_Page_Tools_ExportEntries $page */
/** @var array $forms */
/** @var array $formatSettings */
?><div class="qfb qfb-cf qfb-page-tools-export-entries">
    <?php
        echo $page->getMessagesHtml();
        echo $page->getNavHtml();
        echo $page->getSubNavHtml();
    ?>

    <div class="qfb-tools-export-entries qfb-cf">

        <?php if (count($forms)) : ?>

            <form method="post">
                <input type="hidden" name="page" value="quform.tools">
                <input type="hidden" name="sp" value="export.entries">
                <?php wp_nonce_field('quform_export_entries'); ?>

                <div class="qfb-settings">

                    <div class="qfb-settings-heading"><i class="qfb-icon qfb-icon-file-excel-o"></i> <?php esc_html_e('Select a form to export entries from', 'quform'); ?></div>

                    <div class="qfb-setting">
                        <div class="qfb-cf">
                            <select id="qfb-export-entries-form-id" name="qfb_form_id">
                                <option value=""><?php esc_html_e('Please select', 'quform'); ?></option>
                                <?php foreach ($forms as $id => $name) : ?>
                                    <option value="<?php echo esc_attr($id); ?>"><?php echo Quform::escape($name); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="qfb-description"><?php esc_html_e('Select the form that you would like export entries from.', 'quform'); ?></p>
                            <div class="qfb-export-entries-loading"></div>
                        </div>
                        <?php if (isset($invalid['qfb_form_id'])) : ?>
                            <div class="qfb-validation-error">
                                <div class="qfb-validation-error-inner"><?php echo esc_html($invalid['qfb_form_id']); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div id="qfb-export-entries-fields-wrap" class="qfb-hidden">

                        <div class="qfb-settings-heading"><?php esc_html_e('Choose which fields to export', 'quform'); ?></div>

                        <div class="qfb-export-all-fields">
                            <label for="qfb-export-all-fields"><input type="checkbox" id="qfb-export-all-fields"><?php esc_html_e('All', 'quform'); ?></label>
                        </div>

                        <div id="qfb-export-entries-fields"></div>

                        <div class="qfb-export-entries-date-wrap">
                            <div class="qfb-settings-heading"><?php esc_html_e('Date range (optional)', 'quform'); ?></div>

                            <div class="qfb-settings">
                                <div class="qfb-setting">
                                    <div class="qfb-sub-setting-inline qfb-cf">
                                        <div class="qfb-sub-setting">
                                            <label for="qfb_date_from"><?php esc_html_e('From', 'quform'); ?></label>
                                            <input type="text" class="qfb-width-300" name="qfb_date_from" id="qfb_date_from">
                                        </div>
                                        <div class="qfb-sub-setting">
                                            <label for="qfb_date_to"><?php echo esc_html_x('To', 'date', 'quform'); ?></label>
                                            <input type="text" class="qfb-width-300" name="qfb_date_to" id="qfb_date_to">
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="qfb-settings">
                            <div class="qfb-settings-heading"><?php esc_html_e('Format', 'quform'); ?></div>

                            <div class="qfb-setting">
                                <div class="qfb-setting-label">
                                    <label for="qfb_format_type"><?php esc_html_e('Choose format', 'quform'); ?></label>
                                </div>
                                <div class="qfb-setting-inner">
                                    <div class="qfb-setting-input qfb-sub-setting-inline qfb-cf">
                                        <div class="qfb-sub-setting">
                                            <select id="qfb_format_type" name="qfb_format_type">
                                                <option value="csv" <?php selected($formatSettings['type'], 'csv'); ?>><?php esc_html_e('Comma Separated Values (.csv)', 'quform'); ?></option>
                                                <option value="xlsx" <?php selected($formatSettings['type'], 'xlsx'); ?>><?php esc_html_e('Excel 2007 (.xlsx)', 'quform'); ?></option>
                                                <option value="xls" <?php selected($formatSettings['type'], 'xls'); ?>><?php esc_html_e('Excel 2005 (.xls)', 'quform'); ?></option>
                                                <option value="ods" <?php selected($formatSettings['type'], 'ods'); ?>><?php esc_html_e('OpenDocument Spreadsheet (.ods)', 'quform'); ?></option>
                                                <option value="html" <?php selected($formatSettings['type'], 'html'); ?>><?php esc_html_e('HTML (.html)', 'quform'); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="qfb-export-entries-csv-settings"<?php echo $formatSettings['type'] != 'csv' ? ' class="qfb-hidden"' : ''; ?>>

                                <div class="qfb-setting">
                                    <div class="qfb-setting-label">
                                        <label for="qfb_format_csv_excel_compatibility"><?php esc_html_e('Excel compatibility', 'quform'); ?></label>
                                    </div>
                                    <div class="qfb-setting-inner">
                                        <div class="qfb-setting-input">
                                            <input type="checkbox" id="qfb_format_csv_excel_compatibility" name="qfb_format_csv_excel_compatibility" value="1" class="qfb-toggle" <?php checked($formatSettings['excelCompatibility']); ?>>
                                            <label for="qfb_format_csv_excel_compatibility"></label>
                                        </div>
                                    </div>
                                </div>

                                <div class="qfb-setting<?php echo $formatSettings['excelCompatibility'] ? ' qfb-hidden' : ''; ?>">
                                    <div class="qfb-setting-label">
                                        <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Each field is separated by the specified delimiter.', 'quform'); ?></div></div>
                                        <label for="qfb_format_csv_delimiter"><?php esc_html_e('Delimiter', 'quform'); ?></label>
                                    </div>
                                    <div class="qfb-setting-inner">
                                        <div class="qfb-setting-input qfb-sub-setting-inline qfb-cf">
                                            <div class="qfb-sub-setting">
                                                <select id="qfb_format_csv_delimiter" name="qfb_format_csv_delimiter">
                                                    <option value="comma" <?php selected($formatSettings['delimiter'], 'comma'); ?>><?php esc_html_e('Comma', 'quform'); ?></option>
                                                    <option value="semicolon" <?php selected($formatSettings['delimiter'], 'semicolon'); ?>><?php esc_html_e('Semicolon', 'quform'); ?></option>
                                                    <option value="tab" <?php selected($formatSettings['delimiter'], 'tab'); ?>><?php esc_html_e('Tab', 'quform'); ?></option>
                                                    <option value="space" <?php selected($formatSettings['delimiter'], 'space'); ?>><?php esc_html_e('Space', 'quform'); ?></option>
                                                    <option value="custom" <?php selected($formatSettings['delimiter'], 'custom'); ?>><?php esc_html_e('Custom', 'quform'); ?></option>
                                                </select>
                                            </div>
                                            <div class="qfb-sub-setting<?php echo $formatSettings['delimiter'] != 'custom' ? ' qfb-hidden' : ''; ?>">
                                                <input type="text" id="qfb_format_csv_delimiter_custom" name="qfb_format_csv_delimiter_custom" value="<?php echo Quform::escape($formatSettings['delimiterCustom']); ?>" class="qfb-width-100">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="qfb-setting<?php echo $formatSettings['excelCompatibility'] ? ' qfb-hidden' : ''; ?>">
                                    <div class="qfb-setting-label">
                                        <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Each field is wrapped in the specified enclosure.', 'quform'); ?></div></div>
                                        <label for="qfb_format_csv_enclosure"><?php esc_html_e('Enclosure', 'quform'); ?></label>
                                    </div>
                                    <div class="qfb-setting-inner">
                                        <div class="qfb-setting-input qfb-sub-setting-inline qfb-cf">
                                            <div class="qfb-sub-setting">
                                                <select id="qfb_format_csv_enclosure" name="qfb_format_csv_enclosure">
                                                    <option value="double-quotes" <?php selected($formatSettings['enclosure'], 'double-quotes'); ?>><?php esc_html_e('Double quotes', 'quform'); ?></option>
                                                    <option value="single-quotes" <?php selected($formatSettings['enclosure'], 'single-quotes'); ?>><?php esc_html_e('Single quotes', 'quform'); ?></option>
                                                    <option value="custom" <?php selected($formatSettings['enclosure'], 'custom'); ?>><?php esc_html_e('Custom', 'quform'); ?></option>
                                                </select>
                                            </div>
                                            <div class="qfb-sub-setting<?php echo $formatSettings['enclosure'] != 'custom' ? ' qfb-hidden' : ''; ?>">
                                                <input type="text" id="qfb_format_csv_enclosure_custom" name="qfb_format_csv_enclosure_custom" value="<?php echo Quform::escape($formatSettings['enclosureCustom']); ?>" class="qfb-width-100" >
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="qfb-setting<?php echo $formatSettings['excelCompatibility'] ? ' qfb-hidden' : ''; ?>">
                                    <div class="qfb-setting-label">
                                        <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Adds the UTF-8 Byte Order Mark to the beginning of the file, which may fix character encoding issues.', 'quform'); ?></div></div>
                                        <label for="qfb_format_csv_use_bom"><?php esc_html_e('Use UTF-8 BOM', 'quform'); ?></label>
                                    </div>
                                    <div class="qfb-setting-inner">
                                        <div class="qfb-setting-input">
                                            <input type="checkbox" id="qfb_format_csv_use_bom" name="qfb_format_csv_use_bom" value="1" class="qfb-toggle" <?php checked($formatSettings['useBom']); ?>>
                                            <label for="qfb_format_csv_use_bom"></label>
                                        </div>
                                    </div>
                                </div>

                                <div class="qfb-setting<?php echo $formatSettings['excelCompatibility'] ? ' qfb-hidden' : ''; ?>">
                                    <div class="qfb-setting-label">
                                        <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Set the line endings of the CSV file.', 'quform'); ?></div></div>
                                        <label for="qfb_format_csv_line_endings"><?php esc_html_e('Line endings', 'quform'); ?></label>
                                    </div>
                                    <div class="qfb-setting-inner">
                                        <div class="qfb-setting-input qfb-sub-setting-inline qfb-cf">
                                            <div class="qfb-sub-setting">
                                                <select id="qfb_format_csv_line_endings" name="qfb_format_csv_line_endings">
                                                    <option value="windows" <?php selected($formatSettings['lineEndings'], 'windows'); ?>><?php esc_html_e('Windows', 'quform'); ?></option>
                                                    <option value="unix" <?php selected($formatSettings['lineEndings'], 'unix'); ?>><?php esc_html_e('Unix / Mac', 'quform'); ?></option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>

                        <div class="qfb-export-entries-buttons">
                            <button class="qfb-button-green" type="submit" name="qfb_do_entries_export" value="1"><i class="qfb-icon qfb-icon-download"></i> <span><?php esc_html_e('Download', 'quform'); ?></span></button>
                        </div>

                    </div>
                </div>
            </form>

        <?php else : ?>
            <div class="qfb-message-box qfb-message-box-info">
                <div class="qfb-message-box-inner"><p><?php
                    if (current_user_can('quform_add_forms')) {
                        /* translators: %1$s: open link tag, %2$s: close link tag */
                        printf(esc_html__('No forms found, %1$sclick here to create one%2$s.', 'quform'), '<a href="' . esc_url(admin_url('admin.php?page=quform.forms&sp=add')) . '">', '</a>');
                    } else {
                        esc_html_e('No forms found.', 'quform');
                    }
                ?></p></div>
            </div>
        <?php endif; ?>

        </div>
</div>
