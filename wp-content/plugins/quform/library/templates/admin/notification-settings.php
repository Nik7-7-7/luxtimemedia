<?php
if (!defined('ABSPATH')) exit;
/* @var Quform_Builder $builder */
?><div id="qfb-notification-settings" class="qfb-popup">
    <div id="qfb-notification-settings-inner" class="qfb-popup-content">

        <div class="qfb-settings">

            <div class="qfb-settings-heading"><i class="qfb-mdi qfb-mdi-rate_review"></i><?php esc_html_e('Notification settings', 'quform'); ?></div>

            <div class="qfb-setting">
                <div class="qfb-setting-label">
                    <label for="qfb_notification_name"><?php esc_html_e('Name', 'quform'); ?><span class="qfb-required">*</span></label>
                </div>
                <div class="qfb-setting-inner">
                    <div class="qfb-setting-input">
                        <input type="text" id="qfb_notification_name">
                    </div>
                </div>
            </div>

            <div class="qfb-setting">
                <div class="qfb-setting-label">
                    <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('You can stop this notification being sent by turning this off.', 'quform'); ?></div></div>
                    <label for="qfb_notification_enabled"><?php esc_html_e('Enabled', 'quform'); ?></label>
                </div>
                <div class="qfb-setting-inner">
                    <div class="qfb-setting-input">
                        <input type="checkbox" class="qfb-toggle" id="qfb_notification_enabled">
                        <label for="qfb_notification_enabled"></label>
                    </div>
                </div>
            </div>

            <div class="qfb-setting">
                <div class="qfb-setting-label">
                    <label for="qfb_notification_subject"><?php esc_html_e('Subject', 'quform'); ?><span class="qfb-required">*</span></label>
                </div>
                <div class="qfb-setting-inner">
                    <div class="qfb-setting-input">
                        <div class="qfb-input-variable">
                            <input type="text" id="qfb_notification_subject">
                            <?php echo $builder->getInsertVariableHtml('qfb_notification_subject'); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="qfb-setting">
                <div class="qfb-setting-label">
                    <label for="qfb_notification_format"><?php esc_html_e('Format', 'quform'); ?></label>
                </div>
                <div class="qfb-setting-inner">
                    <div class="qfb-setting-input">
                        <select id="qfb_notification_format">
                            <option value="html"><?php esc_html_e('HTML', 'quform'); ?></option>
                            <option value="text"><?php esc_html_e('Plain text', 'quform'); ?></option>
                            <option value="multipart"><?php esc_html_e('Multipart', 'quform'); ?></option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="qfb-setting">
                <div class="qfb-setting-label">
                    <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Set the HTML content of the message using this field.', 'quform'); ?></div></div>
                    <label><?php esc_html_e('Message (HTML)', 'quform'); ?><span class="qfb-required">*</span></label>
                </div>
                <div class="qfb-setting-inner">
                    <div class="qfb-setting-input">
                        <div class="qfb-textarea-variable qfb-editor-variable">
                            <?php echo $builder->getInsertVariableHtml('qfb_notification_html'); ?>
                            <?php wp_editor('', 'qfb_notification_html', array('editor_height' => 300)); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="qfb-setting">
                <div class="qfb-setting-label">
                    <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Automatically adds line breaks, you might want to disable this when using HTML to prevent unwanted extra spacing.', 'quform'); ?></div></div>
                    <label for="qfb_notification_auto_format"><?php esc_html_e('Auto formatting', 'quform'); ?></label>
                </div>
                <div class="qfb-setting-inner">
                    <div class="qfb-setting-input">
                        <input type="checkbox" class="qfb-toggle" id="qfb_notification_auto_format">
                        <label for="qfb_notification_auto_format"></label>
                    </div>
                </div>
            </div>

            <div class="qfb-setting">
                <div class="qfb-setting-label">
                    <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('The padding is the spacing around the email content, enter a number for pixels or any other CSS unit.', 'quform'); ?></div></div>
                    <label for="qfb_notification_padding"><?php esc_html_e('Padding', 'quform'); ?></label>
                </div>
                <div class="qfb-setting-inner">
                    <div class="qfb-setting-input">
                        <input type="text" id="qfb_notification_padding">
                    </div>
                </div>
            </div>

            <div class="qfb-setting">
                <div class="qfb-setting-label">
                    <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Enable this option if the notification language is RTL. Choosing "Inherit" will inherit the value from the form settings.', 'quform'); ?></div></div>
                    <label for="qfb_notification_rtl"><?php esc_html_e('RTL', 'quform'); ?></label>
                </div>
                <div class="qfb-setting-inner">
                    <div class="qfb-setting-input">
                        <select id="qfb_notification_rtl">
                            <option value="inherit"><?php esc_html_e('Inherit', 'quform'); ?></option>
                            <option value="yes"><?php esc_html_e('Yes', 'quform'); ?></option>
                            <option value="no"><?php esc_html_e('No', 'quform'); ?></option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="qfb-setting">
                <div class="qfb-setting-label">
                    <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Set the plain text content of the message using this field.', 'quform'); ?></div></div>
                    <label for="qfb_notification_text"><?php esc_html_e('Message (plain text)', 'quform'); ?><span class="qfb-required">*</span></label>
                </div>
                <div class="qfb-setting-inner">
                    <div class="qfb-setting-input">
                        <div class="qfb-textarea-variable">
                            <?php echo $builder->getInsertVariableHtml('qfb_notification_text'); ?>
                            <textarea id="qfb_notification_text"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="qfb-setting">
                <div class="qfb-setting-label">
                    <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content">
                        <strong><?php echo esc_html_x('To', 'email', 'quform'); ?></strong> - <?php esc_html_e('Add email addresses to which this email will be sent to.', 'quform'); ?><br><br>
                        <strong><?php esc_html_e('CC', 'quform'); ?></strong> - <?php esc_html_e('Add email addresses to Carbon Copy on this email.', 'quform'); ?><br><br>
                        <strong><?php esc_html_e('BCC', 'quform'); ?></strong> - <?php esc_html_e('Add email addresses to Blind Carbon Copy on this email.', 'quform'); ?><br><br>
                        <strong><?php esc_html_e('Reply-To', 'quform'); ?></strong> - <?php esc_html_e('Adds a "Reply-To" email address. If not set, replying to the email will reply to the "From" address.', 'quform'); ?>
                    </div></div>
                    <label><?php esc_html_e('Recipient', 'quform'); ?><span class="qfb-required">*</span></label>
                    <div id="qfb-add-recipient" class="qfb-button-green"><i class="qfb-mdi qfb-mdi-add_circle"></i><?php esc_html_e('Add recipient', 'quform'); ?></div>
                </div>
                <div class="qfb-setting-inner">
                    <div class="qfb-setting-input">
                        <div id="qfb-notification-recipients"></div>
                    </div>
                </div>
            </div>

            <div class="qfb-setting">
                <div class="qfb-setting-label">
                    <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Send the form data to different email addresses depending on the submitted form values.', 'quform'); ?></div></div>
                    <label for="qfb_notification_conditionals"><?php esc_html_e('Enable conditional recipients', 'quform'); ?></label>
                </div>
                <div class="qfb-setting-inner">
                    <div class="qfb-setting-input">
                        <input type="checkbox" class="qfb-toggle" id="qfb_notification_conditionals">
                        <label for="qfb_notification_conditionals"></label>
                    </div>
                </div>
            </div>

            <div class="qfb-setting">
                <div class="qfb-setting-label">
                    <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content">
                        <?php esc_html_e('Send the form data to different email addresses depending on the submitted form values.', 'quform'); ?><br><br>
                        <strong><?php echo esc_html_x('To', 'email', 'quform'); ?></strong> - <?php esc_html_e('Add email addresses to which this email will be sent to.', 'quform'); ?><br><br>
                        <strong><?php esc_html_e('CC', 'quform'); ?></strong> - <?php esc_html_e('Add email addresses to Carbon Copy on this email.', 'quform'); ?><br><br>
                        <strong><?php esc_html_e('BCC', 'quform'); ?></strong> - <?php esc_html_e('Add email addresses to Blind Carbon Copy on this email.', 'quform'); ?><br><br>
                        <strong><?php esc_html_e('Reply-To', 'quform'); ?></strong> - <?php esc_html_e('Adds a "Reply-To" email address. If not set, replying to the email will reply to the "From" address', 'quform'); ?>
                    </div></div>
                    <label><?php esc_html_e('Conditional recipients', 'quform'); ?></label>
                    <div id="qfb-add-conditional" class="qfb-button-green"><i class="qfb-mdi qfb-mdi-add_circle"></i><?php esc_html_e('Add conditional', 'quform'); ?></div>
                </div>
                <div class="qfb-setting-inner">
                    <div class="qfb-setting-input">
                        <div id="qfb-notification-conditionals-wrap" class="qfb-conditionals"></div>
                    </div>
                </div>
            </div>

            <div class="qfb-setting">
                <div class="qfb-setting-label">
                    <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Choose what do to if there are no recipients found after processing the conditional recipients.', 'quform'); ?></div></div>
                    <label for="qfb_notification_conditional_fallback"><?php esc_html_e('Conditional fallback', 'quform'); ?></label>
                </div>
                <div class="qfb-setting-inner">
                    <div class="qfb-setting-input">
                        <select id="qfb_notification_conditional_fallback">
                            <option value=""><?php esc_html_e('Send to the main notification recipients (default)', 'quform'); ?></option>
                            <option value="do_not_send"><?php esc_html_e('Do not send this notification', 'quform'); ?></option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="qfb-setting">
                <div class="qfb-setting-label">
                    <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Sets the email "From" address. The email address set here will be shown as the sender of the email.', 'quform'); ?></div></div>
                    <label><?php esc_html_e('"From" address', 'quform'); ?><span class="qfb-required">*</span></label>
                </div>
                <div class="qfb-setting-inner">
                    <div class="qfb-setting-input">
                        <div id="qfb-notification-from">
                            <div class="qfb-recipient">
                                <div class="qfb-recipient-inner qfb-cf">
                                    <div class="qfb-settings-row qfb-settings-row-2">
                                        <div class="qfb-settings-column">
                                            <div class="qfb-input-variable">
                                                <input id="qfb-notification-from-address" class="qfb-recipient-address" type="text" placeholder="<?php esc_attr_e('Email (required)', 'quform'); ?>">
                                                <?php echo $builder->getInsertVariableHtml('qfb-notification-from-address'); ?>
                                            </div>
                                        </div>
                                        <div class="qfb-settings-column">
                                            <div class="qfb-input-variable">
                                                <input id="qfb-notification-from-name" class="qfb-recipient-name" type="text" placeholder="<?php esc_attr_e('Name (optional)', 'quform'); ?>">
                                                <?php echo $builder->getInsertVariableHtml('qfb-notification-from-name'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="qfb-setting">
                <div class="qfb-setting-label">
                    <label for="qfb_notification_logic_enabled"><?php esc_html_e('Enable conditional logic', 'quform'); ?></label>
                </div>
                <div class="qfb-setting-inner">
                    <div class="qfb-setting-input">
                        <input type="checkbox" class="qfb-toggle" id="qfb_notification_logic_enabled">
                        <label for="qfb_notification_logic_enabled"></label>
                    </div>
                </div>
            </div>

            <div class="qfb-setting">
                <div class="qfb-setting-label">
                    <label><?php esc_html_e('Logic rules', 'quform'); ?></label>
                    <div class="qfb-add-logic-rule-wrap qfb-cf">
                        <a id="qfb-add-notification-logic-rule" class="qfb-button-green"><i class="qfb-mdi qfb-mdi-add_circle"></i><?php esc_html_e('Add logic rule', 'quform'); ?></a>
                    </div>
                </div>
                <div class="qfb-setting-inner">
                    <div class="qfb-setting-input">
                        <div id="qfb-notification-logic" class="qfb-logic qfb-cf"></div>
                    </div>
                </div>
            </div>

            <div class="qfb-setting">
                <div class="qfb-setting-label">
                    <label><?php esc_html_e('Attachments', 'quform'); ?></label>
                    <div class="qfb-add-notification-attachment-wrap qfb-cf">
                        <a id="qfb-add-notification-attachment" class="qfb-button-green"><i class="qfb-mdi qfb-mdi-add_circle"></i><?php esc_html_e('Add attachment', 'quform'); ?></a>
                    </div>
                </div>
                <div class="qfb-setting-inner">
                    <div class="qfb-setting-input">
                        <div id="qfb-notification-attachments-wrap"></div>
                    </div>
                </div>
            </div>

            <div class="qfb-setting">
                <div class="qfb-setting-label">
                    <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('The unique identifier, you may need this for advanced usage.', 'quform'); ?></div></div>
                    <label for="qfb-notification-unique-id"><?php esc_html_e('Unique ID', 'quform'); ?></label>
                </div>
                <div class="qfb-setting-inner">
                    <div class="qfb-setting-input">
                        <input type="text" id="qfb-notification-unique-id" class="qfb-code-input" readonly>
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
