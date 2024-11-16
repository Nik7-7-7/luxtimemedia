<?php
if (!defined('ABSPATH')) exit;
/* @var Quform_Builder $builder */
?><div id="qfb-confirmation-settings" class="qfb-popup">
    <div id="qfb-confirmation-settings-inner" class="qfb-popup-content">

        <div class="qfb-settings">

            <div class="qfb-settings-heading"><i class="qfb-mdi qfb-mdi-check"></i><?php esc_html_e('Confirmation settings', 'quform'); ?></div>

            <div class="qfb-setting">
                <div class="qfb-setting-label">
                    <label for="qfb_confirmation_name"><?php esc_html_e('Name', 'quform'); ?><span class="qfb-required">*</span></label>
                </div>
                <div class="qfb-setting-inner">
                    <div class="qfb-setting-input">
                        <input type="text" id="qfb_confirmation_name">
                    </div>
                </div>
            </div>

            <div class="qfb-setting">
                <div class="qfb-setting-label">
                    <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('You can disable this confirmation by turning this off.', 'quform'); ?></div></div>
                    <label for="qfb_confirmation_enabled"><?php esc_html_e('Enabled', 'quform'); ?></label>
                </div>
                <div class="qfb-setting-inner">
                    <div class="qfb-setting-input">
                        <input type="checkbox" class="qfb-toggle" id="qfb_confirmation_enabled">
                        <label for="qfb_confirmation_enabled"></label>
                    </div>
                </div>
            </div>

            <div class="qfb-setting">
                <div class="qfb-setting-label">
                    <label for="qfb_confirmation_type"><?php esc_html_e('Type', 'quform'); ?></label>
                </div>
                <div class="qfb-setting-inner">
                    <div class="qfb-setting-input">
                        <select id="qfb_confirmation_type">
                            <option value="message"><?php esc_html_e('Display a message', 'quform'); ?></option>
                            <option value="message-redirect-page"><?php esc_html_e('Display a message then redirect to another page', 'quform'); ?></option>
                            <option value="message-redirect-url"><?php esc_html_e('Display a message then redirect to a custom URL', 'quform'); ?></option>
                            <option value="redirect-page"><?php esc_html_e('Redirect to another page', 'quform'); ?></option>
                            <option value="redirect-url"><?php esc_html_e('Redirect to a custom URL', 'quform'); ?></option>
                            <option value="reload"><?php esc_html_e('Reload the page', 'quform'); ?></option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="qfb-setting">
                <div class="qfb-setting-label">
                    <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Set the HTML content of the message using this field.', 'quform'); ?></div></div>
                    <label><?php esc_html_e('Message', 'quform'); ?><span class="qfb-required">*</span></label>
                </div>
                <div class="qfb-setting-inner">
                    <div class="qfb-setting-input">
                        <div class="qfb-textarea-variable qfb-editor-variable">
                            <?php echo $builder->getInsertVariableHtml('qfb_confirmation_message'); ?>
                            <?php wp_editor('', 'qfb_confirmation_message', array('editor_height' => 300)); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="qfb-setting">
                <div class="qfb-setting-label">
                    <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Automatically adds line breaks, you might want to disable this when using HTML to prevent unwanted extra spacing.', 'quform'); ?></div></div>
                    <label for="qfb_confirmation_message_auto_format"><?php esc_html_e('Auto formatting', 'quform'); ?></label>
                </div>
                <div class="qfb-setting-inner">
                    <div class="qfb-setting-input">
                        <input type="checkbox" class="qfb-toggle" id="qfb_confirmation_message_auto_format">
                        <label for="qfb_confirmation_message_auto_format"></label>
                    </div>
                </div>
            </div>

            <div class="qfb-setting">
                <div class="qfb-setting-label">
                    <label><?php esc_html_e('Message icon', 'quform'); ?></label>
                </div>
                <div class="qfb-setting-inner">
                    <div class="qfb-setting-input">
                        <?php echo $builder->getSelectIconHtml('qfb_confirmation_message_icon'); ?>
                    </div>
                </div>
            </div>

            <div class="qfb-setting">
                <div class="qfb-setting-label">
                    <label for="qfb_confirmation_message_position"><?php esc_html_e('Message position', 'quform'); ?></label>
                </div>
                <div class="qfb-setting-inner">
                    <div class="qfb-setting-input">
                        <select id="qfb_confirmation_message_position">
                            <option value="above"><?php esc_html_e('Above the form', 'quform'); ?></option>
                            <option value="below"><?php esc_html_e('Below the form', 'quform'); ?></option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="qfb-setting">
                <div class="qfb-setting-label">
                    <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('The confirmation message will fade out and disappear after this number of seconds. Set to 0 to disable the timeout.', 'quform'); ?></div></div>
                    <label for="qfb_confirmation_message_timeout"><?php esc_html_e('Message timeout', 'quform'); ?></label>
                </div>
                <div class="qfb-setting-inner">
                    <div class="qfb-setting-input">
                        <input type="text" id="qfb_confirmation_message_timeout">
                    </div>
                </div>
            </div>

            <div class="qfb-setting">
                <div class="qfb-setting-label">
                    <label for="qfb_confirmation_redirect_page"><?php esc_html_e('Page', 'quform'); ?></label>
                </div>
                <div class="qfb-setting-inner">
                    <div class="qfb-setting-input">
                        <select id="qfb_confirmation_redirect_page" style="width: 100%;">
                            <option value=""><?php esc_html_e('Please select', 'quform'); ?></option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="qfb-setting">
                <div class="qfb-setting-label">
                    <label for="qfb_confirmation_redirect_url"><?php esc_html_e('URL', 'quform'); ?></label>
                </div>
                <div class="qfb-setting-inner">
                    <div class="qfb-setting-input">
                        <input type="text" id="qfb_confirmation_redirect_url">
                    </div>
                </div>
            </div>

            <div class="qfb-setting">
                <div class="qfb-setting-label">
                    <div class="qfb-tooltip-icon">
                        <div class="qfb-tooltip-content">
                            <?php esc_html_e('Add extra data to the redirect URL, see the example below.', 'quform'); ?>
                            <pre>fullname={element|id:3|Name}&amp;email={element|id:4|Email}</pre>
                        </div>
                    </div>
                    <label for="qfb_confirmation_redirect_query"><?php esc_html_e('Query string', 'quform'); ?></label>
                </div>
                <div class="qfb-setting-inner">
                    <div class="qfb-setting-input">
                        <div class="qfb-textarea-variable">
                            <?php echo $builder->getInsertVariableHtml('qfb_confirmation_redirect_query'); ?>
                            <textarea id="qfb_confirmation_redirect_query"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="qfb-setting">
                <div class="qfb-setting-label">
                    <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Wait this number of seconds before redirecting.', 'quform'); ?></div></div>
                    <label for="qfb_confirmation_redirect_delay"><?php esc_html_e('Redirect delay', 'quform'); ?></label>
                </div>
                <div class="qfb-setting-inner">
                    <div class="qfb-setting-input">
                        <input type="text" id="qfb_confirmation_redirect_delay">
                    </div>
                </div>
            </div>

            <div class="qfb-setting">
                <div class="qfb-setting-label">
                    <label><?php esc_html_e('Logic rules', 'quform'); ?></label>
                    <div class="qfb-add-logic-rule-wrap qfb-cf">
                        <a id="qfb-add-confirmation-logic-rule" class="qfb-button-green"><i class="qfb-mdi qfb-mdi-add_circle"></i><?php esc_html_e('Add logic rule', 'quform'); ?></a>
                    </div>
                </div>
                <div class="qfb-setting-inner">
                    <div class="qfb-setting-input">
                        <div id="qfb-confirmation-logic" class="qfb-logic qfb-cf"></div>
                    </div>
                </div>
            </div>

            <div class="qfb-setting">
                <div class="qfb-setting-label">
                    <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Makes the form hidden when using this confirmation.', 'quform'); ?></div></div>
                    <label for="qfb_confirmation_hide_form"><?php esc_html_e('Hide form', 'quform'); ?></label>
                </div>
                <div class="qfb-setting-inner">
                    <div class="qfb-setting-input">
                        <input type="checkbox" class="qfb-toggle" id="qfb_confirmation_hide_form">
                        <label for="qfb_confirmation_hide_form"></label>
                    </div>
                </div>
            </div>

            <div class="qfb-setting">
                <div class="qfb-setting-label">
                    <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Choose what to do with the form values when using this confirmation.', 'quform'); ?></div></div>
                    <label for="qfb_notification_reset_form"><?php esc_html_e('Reset form values', 'quform'); ?></label>
                </div>
                <div class="qfb-setting-inner">
                    <div class="qfb-setting-input">
                        <select id="qfb_notification_reset_form">
                            <option value=""><?php esc_html_e('Reset form values to default', 'quform'); ?></option>
                            <option value="clear"><?php esc_html_e('Clear form values', 'quform'); ?></option>
                            <option value="keep"><?php esc_html_e('Keep form values', 'quform'); ?></option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="qfb-setting">
                <div class="qfb-setting-label">
                    <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('The unique identifier, you may need this for advanced usage.', 'quform'); ?></div></div>
                    <label for="qfb-confirmation-unique-id"><?php esc_html_e('Unique ID', 'quform'); ?></label>
                </div>
                <div class="qfb-setting-inner">
                    <div class="qfb-setting-input">
                        <input type="text" id="qfb-confirmation-unique-id" class="qfb-code-input" readonly>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <div class="qfb-popup-buttons">
        <div class="qfb-popup-save-button"><i class="qfb-mdi qfb-mdi-check"></i></div>
        <div class="qfb-popup-close-button"><i class="qfb-mdi qfb-mdi-close"></i></div>
    </div>

    <div class="qfb-popup-overlay"></div>
</div>
