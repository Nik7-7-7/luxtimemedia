<?php if (!defined('ABSPATH')) exit;
/* @var Quform_Admin_Page_Forms_Edit $page */
/* @var Quform_Builder $builder */
/* @var Quform_Options $options */
/* @var array $form */
?><div id="qfb-top" class="qfb qfb-cf">
    <?php
        echo $page->getMessagesHtml();
        echo $page->getNavHtml($form);
    ?>
    <form id="qfb-form" method="post" autocomplete="off">
        <div id="qfb-wrap-outer">
            <input type="submit" class="qfb-hidden"><!-- Prevent enter key submitting the form -->
            <input type="password" class="qfb-hidden"><!-- Stop Chrome 34+ autofilling -->
            <div id="qfb-wrap" class="qfb-cf">
                <div id="qfb-control-panel">
                    <div class="qfb-tabs-icons">
                        <div id="qfb-tabs-icon-form" class="qfb-current-tab"><i class="qfb-mdi qfb-mdi-build"></i></div>
                        <div id="qfb-tabs-icon-settings"><i class="qfb-mdi qfb-mdi-settings"></i></div>
                    </div>
                    <div id="qfb-page-tabs">
                        <div id="qfb-page-tabs-nav-wrap" class="qfb-cf">
                            <ul id="qfb-page-tabs-nav" class="qfb-cf">
                                <?php
                                foreach ($builder->getFormConfigValue($form, 'elements') as $key => $element) {
                                    echo $builder->getPageTabNavHtml($key, $element['id'], $element['label']);
                                }
                                ?>
                                <li id="qfb-add-page-tab"><div id="qfb-add-page" title="<?php esc_attr_e('Add page', 'quform'); ?>"><i class="qfb-mdi qfb-mdi-add"></i></div></li>
                            </ul>
                        </div>
                    </div>
                    <div class="qfb-control-panel-right-icons">
                        <div id="qfb-preview-refresh" title="<?php esc_attr_e('Refresh preview', 'quform'); ?>"><i class="qfb-mdi qfb-mdi-refresh"></i></div>
                        <div id="qfb-preview-size-phone" title="<?php esc_attr_e('Set preview to phone size', 'quform'); ?>"><i class="qfb-mdi qfb-mdi-phone_iphone"></i></div>
                        <div id="qfb-preview-size-tablet" title="<?php esc_attr_e('Set preview to tablet size', 'quform'); ?>"><i class="qfb-mdi qfb-mdi-tablet_mac"></i></div>
                        <div id="qfb-preview-hide" title="<?php esc_attr_e('Hide preview', 'quform'); ?>"><i class="qfb-icon qfb-icon-eye-slash"></i></div>
                        <div id="qfb-preview-only" title="<?php esc_attr_e('Show preview only', 'quform'); ?>"><i class="qfb-icon qfb-icon-eye"></i></div>
                        <div id="qfb-save-form" class="qfb-animated-save-button" title="<?php esc_attr_e('Save', 'quform'); ?>"><i class="qfb-icon qfb-icon-floppy-o"></i></div>
                    </div>
                </div>
                <div id="qfb-add-elements">
                    <ul id="qfb-add-elements-list" class="qfb-cf">
                        <?php
                        foreach ($builder->getElements() as $key => $element) {
                            if ($key == 'page' || $key == 'column') {
                                continue;
                            }

                            printf(
                                '<li class="qfb-element-tooltip"><div class="qfb-add-element-button" data-type="%s">%s<span class="qfb-tooltip-content">%s</span></div></li>',
                                esc_attr($key),
                                $element['icon'],
                                esc_html($element['name'])
                            );
                        }
                        ?>
                    </ul>
                    <div id="qfb-preview-unsaved-indicator" title="<?php esc_attr_e('The form has unsaved changes.', 'quform'); ?>">
                        <i class="qfb-icon qfb-icon-asterisk"></i>
                    </div>
                </div>
                <div id="qfb-panels" class="qfb-cf">
                    <?php if (is_rtl()) : ?>
                        <div id="qfb-preview-panel"><iframe id="qfb-preview-frame" src="<?php echo esc_url(admin_url('admin.php?page=quform.preview')); ?>"></iframe></div>
                    <?php endif; ?>
                    <div id="qfb-builder-panel">
                        <div id="qfb-tabs">
                            <ul class="qfb-tabs-nav qfb-cf">
                                <li><a id="qfb-edit-form-tab"></a></li>
                                <li><a id="qfb-settings-tab"></a></li>
                            </ul>
                            <div class="qfb-tabs-panel" id="qfb-edit-form">
                                <div id="qfb-elements">
                                    <?php echo $builder->renderFormElements($builder->getFormConfigValue($form, 'elements')); ?>
                                </div>
                            </div>
                            <div class="qfb-tabs-panel" id="qfb-settings">
                                <div id="qfb-form-settings-tabs">
                                    <ul class="qfb-tabs-nav qfb-cf">
                                        <li><a><i class="qfb-mdi qfb-mdi-tune"></i><?php esc_html_e('General', 'quform'); ?></a></li>
                                        <li><a><i class="qfb-mdi qfb-mdi-brush"></i><?php esc_html_e('Style', 'quform'); ?></a></li>
                                        <li><a><i class="qfb-mdi qfb-mdi-mail_outline"></i><?php esc_html_e('Notifications', 'quform'); ?></a></li>
                                        <li><a><i class="qfb-mdi qfb-mdi-check"></i><?php esc_html_e('Confirmations', 'quform'); ?></a></li>
                                        <li><a><i class="qfb-mdi qfb-mdi-pan_tool"></i><?php esc_html_e('Errors', 'quform'); ?></a></li>
                                        <li><a><i class="qfb-mdi qfb-mdi-translate"></i><?php esc_html_e('Language', 'quform'); ?></a></li>
                                        <li><a><i class="qfb-icon qfb-icon-database"></i><?php esc_html_e('Database', 'quform'); ?></a></li>
                                    </ul>
                                    <div class="qfb-tabs-panel">

                                        <div id="qfb-form-general-tabs">

                                            <ul class="qfb-tabs-nav qfb-sub-tabs-nav qfb-cf">
                                                <li><a><i class="qfb-mdi qfb-mdi-settings"></i><?php esc_html_e('Form', 'quform'); ?></a></li>
                                                <li><a><i class="qfb-mdi qfb-mdi-https"></i><?php esc_html_e('Limits', 'quform'); ?></a></li>
                                                <li><a><i class="qfb-mdi qfb-mdi-date_range"></i><?php esc_html_e('Scheduling', 'quform'); ?></a></li>
                                            </ul>

                                            <div class="qfb-tabs-panel">

                                                <div class="qfb-settings">

                                                    <div class="qfb-settings-heading qfb-settings-heading-light-right"><?php esc_html_e('Form settings', 'quform'); ?><i class="qfb-mdi qfb-mdi-settings"></i></div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_name"><?php esc_html_e('Name', 'quform'); ?><span class="qfb-required">*</span></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <input type="text" id="qfb_form_name" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'name')); ?>" maxlength="64">
                                                                <p class="qfb-description"><?php esc_html_e('The name is to help you identify the form.', 'quform'); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('The heading that will show above the form.', 'quform'); ?></div></div>
                                                            <label for="qfb_form_title"><?php esc_html_e('Title', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <div class="qfb-cf">
                                                                    <div class="qfb-title-left">
                                                                        <div class="qfb-title-left-inner">
                                                                            <input type="text" id="qfb_form_title" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'title')); ?>">
                                                                        </div>
                                                                    </div>
                                                                    <div class="qfb-title-right">
                                                                        <?php echo $builder->getTitleTagSelectHtml('qfb_form_title_tag', $builder->getFormConfigValue($form, 'titleTag')); ?>
                                                                    </div>
                                                                </div>
                                                                <p class="qfb-description"><?php esc_html_e('Title to display above the form.', 'quform'); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('The description that will show below the form title.', 'quform'); ?></div></div>
                                                            <label for="qfb_form_description"><?php esc_html_e('Description', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <textarea id="qfb_form_description"><?php echo Quform::escape($builder->getFormConfigValue($form, 'description')); ?></textarea>
                                                                <p class="qfb-description"><?php esc_html_e('Description to display above the form.', 'quform'); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_active"><?php esc_html_e('Active', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <input type="checkbox" class="qfb-toggle" id="qfb_form_active" <?php checked($builder->getFormConfigValue($form, 'active')); ?>>
                                                                <label for="qfb_form_active"></label>
                                                                <p class="qfb-description"><?php esc_html_e('Inactive forms will not appear on the site.', 'quform'); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting<?php echo $builder->getFormConfigValue($form, 'active') ? ' qfb-hidden' : ''; ?>">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_inactive_message"><?php esc_html_e('Inactive message', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <textarea id="qfb_form_inactive_message"><?php echo Quform::escape($builder->getFormConfigValue($form, 'inactiveMessage')); ?></textarea>
                                                                <p class="qfb-description"><?php esc_html_e('Enter a message to display when the form is inactive. HTML and shortcodes can be used.', 'quform'); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_ajax"><?php esc_html_e('Use Ajax', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <input type="checkbox" class="qfb-toggle" id="qfb_form_ajax" <?php checked($builder->getFormConfigValue($form, 'ajax')); ?>>
                                                                <label for="qfb_form_ajax"></label>
                                                                <p class="qfb-description"><?php esc_html_e('If enabled, the form will submit without reloading the page. If disabled, it will also disable the Enhanced file uploader.', 'quform'); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_save_entry"><?php esc_html_e('Save submitted form data', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <input type="checkbox" class="qfb-toggle" id="qfb_form_save_entry" <?php checked($builder->getFormConfigValue($form, 'saveEntry')); ?>>
                                                                <label for="qfb_form_save_entry"></label>
                                                                <p class="qfb-description"><?php esc_html_e('If enabled, the submitted form data will be saved to the database and you will be able to view submitted entries within the WordPress admin.', 'quform'); ?></p>
                                                                <?php if ( ! $options->get('saveEntries')) : ?>
                                                                    <div class="qfb-message-box qfb-message-box-warning">
                                                                        <div class="qfb-message-box-inner">
                                                                            <?php esc_html_e('Saving entries is disabled globally at Forms &rarr; Settings &rarr; Tweaks &amp; Troubleshooting &rarr; Save entries, so this option has no effect.', 'quform'); ?>
                                                                        </div>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_honeypot"><?php esc_html_e('Enable honeypot CAPTCHA', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <input type="checkbox" class="qfb-toggle" id="qfb_form_honeypot" <?php checked($builder->getFormConfigValue($form, 'honeypot')); ?>>
                                                                <label for="qfb_form_honeypot"></label>
                                                                <p class="qfb-description"><?php esc_html_e('A hidden anti-spam measure that requires no user interaction.', 'quform'); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_logic_animation"><?php esc_html_e('Conditional logic animation', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <input type="checkbox" class="qfb-toggle" id="qfb_form_logic_animation" <?php checked($builder->getFormConfigValue($form, 'logicAnimation')); ?>>
                                                                <label for="qfb_form_logic_animation"></label>
                                                                <p class="qfb-description"><?php esc_html_e('If enabled, the fields that are hidden or shown via conditional logic will be animated instead of hidden or shown instantly.', 'quform'); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>

                                            </div>

                                            <div class="qfb-tabs-panel">

                                                <div class="qfb-settings">

                                                    <div class="qfb-settings-heading qfb-settings-heading-light-right"><?php esc_html_e('Limits', 'quform'); ?><i class="qfb-mdi qfb-mdi-https"></i></div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_one_entry_per_user"><?php esc_html_e('One entry per user', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <input type="checkbox" class="qfb-toggle" id="qfb_form_one_entry_per_user" <?php checked($builder->getFormConfigValue($form, 'oneEntryPerUser')); ?>>
                                                                <label for="qfb_form_one_entry_per_user"></label>
                                                                <p class="qfb-description"><?php esc_html_e('Enable this option to allow only one entry per logged-in user or IP address.', 'quform'); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting<?php echo $builder->getFormConfigValue($form, 'oneEntryPerUser') ? '' : ' qfb-hidden'; ?>">
                                                        <div class="qfb-setting-label">
                                                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('If set to "Logged-in user", logged-in users will only be allowed to submit one entry but logged-out users will have no limit. These limits do not apply to administrators.', 'quform'); ?></div></div>
                                                            <label for="qfb_form_one_entry_per"><?php esc_html_e('One entry per', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <select id="qfb_form_one_entry_per">
                                                                    <option value="logged-in-user" <?php selected($builder->getFormConfigValue($form, 'oneEntryPer'), 'logged-in-user'); ?>><?php esc_html_e('Logged-in user', 'quform'); ?></option>
                                                                    <option value="ip-address" <?php selected($builder->getFormConfigValue($form, 'oneEntryPer'), 'ip-address'); ?>><?php esc_html_e('IP address', 'quform'); ?></option>
                                                                </select>
                                                                <p class="qfb-description"><?php esc_html_e('Choose whether to limit to one entry per logged-in user or IP address.', 'quform'); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_limit_entries"><?php esc_html_e('Limit entries', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <input type="checkbox" class="qfb-toggle" id="qfb_form_limit_entries" <?php checked($builder->getFormConfigValue($form, 'limitEntries')); ?>>
                                                                <label for="qfb_form_limit_entries"></label>
                                                                <p class="qfb-description"><?php esc_html_e('Enable this option to limit the maximum number of entries.', 'quform'); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting<?php echo $builder->getFormConfigValue($form, 'limitEntries') ? '' : ' qfb-hidden'; ?>">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_entry_limit"><?php esc_html_e('Entry limit', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <input type="text" id="qfb_form_entry_limit" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'entryLimit')); ?>">
                                                                <p class="qfb-description"><?php esc_html_e('Enter the maximum number of allowed entries for this form.', 'quform'); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting<?php echo $builder->getFormConfigValue($form, 'limitEntries') ? '' : ' qfb-hidden'; ?>">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_entry_limit_reached_message"><?php esc_html_e('Entry limit reached message', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <?php wp_editor($builder->getFormConfigValue($form, 'entryLimitReachedMessage'), 'qfb_form_entry_limit_reached_message', array('editor_height' => 250, 'media_buttons' => false)); ?>
                                                                <p class="qfb-description"><?php esc_html_e('Enter the message that is displayed instead of the form when the maximum number of entries has been reached.', 'quform') ?>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>

                                            </div>

                                            <div class="qfb-tabs-panel">

                                                <div class="qfb-settings">

                                                    <div class="qfb-settings-heading qfb-settings-heading-light-right"><?php esc_html_e('Scheduling', 'quform'); ?><i class="qfb-mdi qfb-mdi-date_range"></i></div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_enable_schedule"><?php esc_html_e('Enable form schedule', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <input type="checkbox" class="qfb-toggle" id="qfb_form_enable_schedule" <?php checked($builder->getFormConfigValue($form, 'enableSchedule')); ?>>
                                                                <label for="qfb_form_enable_schedule"></label>
                                                                <p class="qfb-description"><?php esc_html_e('Enable this option to be able to choose time period when this form will be available for new submissions.', 'quform'); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting<?php echo $builder->getFormConfigValue($form, 'enableSchedule') ? '' : ' qfb-hidden'; ?>">
                                                        <div class="qfb-setting-label">
                                                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('The time is based on the timezone configured in the WordPress general settings.', 'quform'); ?></div></div>
                                                            <label for="qfb_form_schedule_start"><?php esc_html_e('Form opens at', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <input type="text" id="qfb_form_schedule_start" class="qfb-datetimepicker" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'scheduleStart')); ?>">
                                                                <p class="qfb-description"><?php esc_html_e('The date and time when this form will open for new submissions.', 'quform'); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting<?php echo $builder->getFormConfigValue($form, 'enableSchedule') && Quform::isNonEmptyString($builder->getFormConfigValue($form, 'scheduleStart')) ? '' : ' qfb-hidden'; ?>">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_schedule_start_message"><?php esc_html_e('Before form opens message', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <?php wp_editor($builder->getFormConfigValue($form, 'scheduleStartMessage'), 'qfb_form_schedule_start_message', array('editor_height' => 250, 'media_buttons' => false)); ?>
                                                                <p class="qfb-description"><?php esc_html_e('Enter the message that is displayed instead of the form before the form opens.', 'quform') ?>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting<?php echo $builder->getFormConfigValue($form, 'enableSchedule') ? '' : ' qfb-hidden'; ?>">
                                                        <div class="qfb-setting-label">
                                                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('The time is based on the timezone configured in the WordPress general settings.', 'quform'); ?></div></div>
                                                            <label for="qfb_form_schedule_end"><?php esc_html_e('Form closes at', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <input type="text" id="qfb_form_schedule_end" class="qfb-datetimepicker" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'scheduleEnd')); ?>">
                                                                <p class="qfb-description"><?php esc_html_e('The date and time when this form will close for new submissions.', 'quform'); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting<?php echo $builder->getFormConfigValue($form, 'enableSchedule') && Quform::isNonEmptyString($builder->getFormConfigValue($form, 'scheduleEnd')) ? '' : ' qfb-hidden'; ?>">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_schedule_end_message"><?php esc_html_e('After form closes message', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <?php wp_editor($builder->getFormConfigValue($form, 'scheduleEndMessage'), 'qfb_form_schedule_end_message', array('editor_height' => 250, 'media_buttons' => false)); ?>
                                                                <p class="qfb-description"><?php esc_html_e('Enter the message that is displayed instead of the form after the form closes.', 'quform') ?>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                    <div class="qfb-tabs-panel">

                                        <div id="qfb-form-style-tabs">

                                            <ul class="qfb-tabs-nav qfb-sub-tabs-nav qfb-cf">
                                                <li><a><i class="qfb-icon qfb-icon-globe"></i><?php esc_html_e('Global', 'quform'); ?></a></li>
                                                <li><a><i class="qfb-mdi qfb-mdi-label_outline"></i><?php esc_html_e('Labels', 'quform'); ?></a></li>
                                                <li><a><i class="qfb-icon qfb-icon-pencil"></i><?php esc_html_e('Fields', 'quform'); ?></a></li>
                                                <li><a><i class="qfb-icon qfb-icon-hand-pointer-o"></i><?php esc_html_e('Buttons', 'quform'); ?></a></li>
                                                <li><a><i class="qfb-mdi qfb-mdi-compare_arrows"></i><?php esc_html_e('Pages', 'quform'); ?></a></li>
                                                <li><a><i class="qfb-icon qfb-icon-spinner"></i><?php esc_html_e('Loading', 'quform'); ?></a></li>
                                                <li><a><i class="qfb-mdi qfb-mdi-speaker_notes"></i><?php esc_html_e('Tooltips', 'quform'); ?></a></li>
                                            </ul>

                                            <div class="qfb-tabs-panel">

                                                <div class="qfb-settings">

                                                    <div class="qfb-settings-heading qfb-settings-heading-light-right"><?php esc_html_e('Global styles', 'quform'); ?><i class="qfb-icon qfb-icon-globe"></i></div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Themes define the look of the form.', 'quform'); ?></div></div>
                                                            <label for="qfb_form_theme"><?php esc_html_e('Theme', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <select id="qfb_form_theme">
                                                                    <option value=""><?php esc_html_e('None', 'quform'); ?></option>
                                                                    <?php foreach ($builder->getThemes() as $key => $data) : ?>
                                                                        <option value="<?php echo esc_attr($key); ?>" <?php selected($builder->getFormConfigValue($form, 'theme'), $key); ?>><?php echo esc_html($data['name']); ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <label><?php esc_html_e('Theme colors', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <div class="qfb-settings-row qfb-settings-row-2">
                                                                    <div class="qfb-settings-column">
                                                                        <div id="qfb-form-theme-primary-color-tabs">
                                                                            <ul class="qfb-tabs-nav qfb-sub-tabs-nav qfb-cf">
                                                                                <li class="qfb-current-tab"><a><?php esc_html_e('Primary', 'quform'); ?></a></li>
                                                                                <li><a><?php esc_html_e('Primary foreground', 'quform'); ?></a></li>
                                                                            </ul>
                                                                            <div class="qfb-tabs-panel">
                                                                                <input type="text" id="qfb_form_theme_primary_color" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'themePrimaryColor')); ?>" class="qfb-colorpicker">
                                                                            </div>
                                                                            <div class="qfb-tabs-panel">
                                                                                <input type="text" id="qfb_form_theme_primary_foreground_color" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'themePrimaryForegroundColor')); ?>" class="qfb-colorpicker">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="qfb-settings-column">
                                                                        <div id="qfb-form-theme-secondary-color-tabs">
                                                                            <ul class="qfb-tabs-nav qfb-sub-tabs-nav qfb-cf">
                                                                                <li id="qfb-form-theme-secondary-color-tab" class="qfb-current-tab"><a><?php esc_html_e('Secondary', 'quform'); ?><span id="qfb-form-theme-bootstrap-tip" class="qfb-tooltip-icon"><span class="qfb-tooltip-content"><?php esc_html_e('For best results, this color should be a darker version of the Primary color.', 'quform'); ?></span></span></a></li>
                                                                                <li id="qfb-form-theme-secondary-color-tab-foreground"><a><?php esc_html_e('Secondary foreground', 'quform'); ?></a></li>
                                                                            </ul>
                                                                            <div class="qfb-tabs-panel">
                                                                                <input type="text" id="qfb_form_theme_secondary_color" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'themeSecondaryColor')); ?>" class="qfb-colorpicker">
                                                                            </div>
                                                                            <div class="qfb-tabs-panel">
                                                                                <input type="text" id="qfb_form_theme_secondary_foreground_color" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'themeSecondaryForegroundColor')); ?>" class="qfb-colorpicker">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('At what screen width should elements be full width?', 'quform'); ?></div></div>
                                                            <label for="qfb_form_responsive_elements"><?php esc_html_e('Responsive elements', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <?php echo $builder->getResponsiveSelectHtml('qfb_form_responsive_elements', $builder->getFormConfigValue($form, 'responsiveElements'), false); ?>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting<?php echo $builder->getFormConfigValue($form, 'responsiveElements') != 'custom' ? ' qfb-hidden' : ''; ?>">
                                                        <div class="qfb-setting-label">
                                                            <div class="qfb-tooltip-icon">
                                                                <div class="qfb-tooltip-content">
                                                                    <?php
                                                                        printf(
                                                                            /* translators : %s example CSS unit value */
                                                                            esc_html__('Enter a value using any CSS unit, for example: %s', 'quform'),
                                                                            '<code>600px</code>'
                                                                        );
                                                                    ?>
                                                                </div>
                                                            </div>
                                                            <label for="qfb_form_responsive_elements_custom"><?php esc_html_e('Responsive elements custom', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <input type="text" id="qfb_form_responsive_elements_custom" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'responsiveElementsCustom')); ?>">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('At what screen width should column layouts be stacked?', 'quform'); ?></div></div>
                                                            <label for="qfb_form_responsive_columns"><?php esc_html_e('Responsive columns', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <?php echo $builder->getResponsiveSelectHtml('qfb_form_responsive_columns', $builder->getFormConfigValue($form, 'responsiveColumns'), false); ?>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting<?php echo $builder->getFormConfigValue($form, 'responsiveColumns') != 'custom' ? ' qfb-hidden' : ''; ?>">
                                                        <div class="qfb-setting-label">
                                                            <div class="qfb-tooltip-icon">
                                                                <div class="qfb-tooltip-content">
                                                                    <?php
                                                                        printf(
                                                                            /* translators : %s example CSS unit value */
                                                                            esc_html__('Enter a value using any CSS unit, for example: %s', 'quform'),
                                                                            '<code>600px</code>'
                                                                        );
                                                                    ?>
                                                                </div>
                                                            </div>
                                                            <label for="qfb_form_responsive_columns_custom"><?php esc_html_e('Responsive columns custom', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <input type="text" id="qfb_form_responsive_columns_custom" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'responsiveColumnsCustom')); ?>">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <div class="qfb-tooltip-icon">
                                                                <div class="qfb-tooltip-content">
                                                                    <?php
                                                                        printf(
                                                                            /* translators : %s example CSS unit value */
                                                                            esc_html__('Enter a value using any CSS unit, for example: %s', 'quform'),
                                                                            '<code>600px</code>'
                                                                        );
                                                                    ?>
                                                                </div>
                                                            </div>
                                                            <label for="qfb_form_vertical_element_spacing"><?php esc_html_e('Vertical element spacing', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <input type="text" id="qfb_form_vertical_element_spacing" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'verticalElementSpacing')); ?>" placeholder="15px">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Enter a CSS value for the width of this form, the default is 100%. After entering a value you can choose Left, Center or Right position.', 'quform'); ?></div></div>
                                                            <label for="qfb_form_width"><?php esc_html_e('Form width', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <input type="text" id="qfb_form_width" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'width')); ?>">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting<?php echo Quform::isNonEmptyString($builder->getFormConfigValue($form, 'width')) ? '' : ' qfb-hidden'; ?>">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_position"><?php esc_html_e('Form position', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <select id="qfb_form_position">
                                                                    <option value="" <?php selected($builder->getFormConfigValue($form, 'position'), ''); ?>><?php esc_html_e('Left', 'quform'); ?></option>
                                                                    <option value="center" <?php selected($builder->getFormConfigValue($form, 'position'), 'center'); ?>><?php esc_html_e('Center', 'quform'); ?></option>
                                                                    <option value="right" <?php selected($builder->getFormConfigValue($form, 'position'), 'right'); ?>><?php esc_html_e('Right', 'quform'); ?></option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Choose the same background color as the site for an optimal preview.', 'quform'); ?></div></div>
                                                            <label for="qfb_form_preview_color"><?php esc_html_e('Preview background color', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <input type="text" id="qfb_form_preview_color" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'previewColor')); ?>" class="qfb-colorpicker">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Styles entered here will apply to all form elements, you can override these for each element inside the element settings. Once you have added a style, enter the CSS styles in the box.', 'quform'); ?></div></div>
                                                            <label><?php esc_html_e('Global CSS Styles', 'quform'); ?></label>
                                                            <div id="qfb-add-global-style" class="qfb-button-green"><i class="qfb-mdi qfb-mdi-add_circle"></i><?php esc_html_e('Add global style', 'quform'); ?></div>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <div id="qfb-global-style-settings">
                                                                    <div class="qfb-settings">
                                                                        <div class="qfb-setting">
                                                                            <div class="qfb-setting-label">
                                                                                <label for="qfb_global_style_type"><?php esc_html_e('Selector', 'quform'); ?></label>
                                                                            </div>
                                                                            <div class="qfb-setting-inner">
                                                                                <div class="qfb-setting-input">
                                                                                    <select id="qfb_global_style_type" style="width: 100%;">
                                                                                        <option value=""><?php esc_html_e('Please select', 'quform'); ?></option>
                                                                                        <?php
                                                                                            foreach ($builder->getGlobalStyles() as $key => $style) {
                                                                                                printf('<option value="%s">%s</option>', esc_attr($key), esc_html($style['name']));
                                                                                            }
                                                                                        ?>
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="qfb-setting">
                                                                            <div class="qfb-setting-label">
                                                                                <label for="qfb_global_style_css"><?php esc_html_e('CSS', 'quform'); ?></label>
                                                                            </div>
                                                                            <div class="qfb-setting-inner">
                                                                                <div class="qfb-setting-input">
                                                                                    <textarea id="qfb_global_style_css"></textarea>
                                                                                    <?php echo $builder->getCssHelperHtml(); ?>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div id="qfb-global-styles">
                                                                    <?php
                                                                        foreach ($builder->getFormConfigValue($form, 'styles') as $style) {
                                                                            echo $builder->getGlobalStyleHtml($style);
                                                                        }
                                                                    ?>
                                                                </div>
                                                                <div id="qfb-global-styles-empty"<?php echo count($builder->getFormConfigValue($form, 'styles')) ? ' class="qfb-hidden"' : ''; ?>><div class="qfb-message-box qfb-message-box-info"><div class="qfb-message-box-inner"><?php esc_html_e('No active global styles, add one using the "Add global style" button.', 'quform'); ?></div></div></div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>

                                            </div>

                                            <div class="qfb-tabs-panel">

                                                <div class="qfb-settings">

                                                    <div class="qfb-settings-heading qfb-settings-heading-light-right"><?php esc_html_e('Labels', 'quform'); ?><i class="qfb-mdi qfb-mdi-label_outline"></i></div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_label_text_color"><?php esc_html_e('Label text color', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <input type="text" id="qfb_form_label_text_color" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'labelTextColor')); ?>" class="qfb-colorpicker">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_label_position"><?php esc_html_e('Label position', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <select id="qfb_form_label_position">
                                                                    <option value="" <?php selected($builder->getFormConfigValue($form, 'labelPosition'), ''); ?>><?php esc_html_e('Above', 'quform'); ?></option>
                                                                    <option value="left" <?php selected($builder->getFormConfigValue($form, 'labelPosition'), 'left'); ?>><?php esc_html_e('Left', 'quform'); ?></option>
                                                                    <option value="inside" <?php selected($builder->getFormConfigValue($form, 'labelPosition'), 'inside'); ?>><?php esc_html_e('Inside', 'quform'); ?></option>
                                                                </select>
                                                                <p class="qfb-description"><?php esc_html_e('Choose where to display the label relative to the field.', 'quform'); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting<?php echo $builder->getFormConfigValue($form, 'labelPosition') == 'left' ? '' : ' qfb-hidden'; ?>">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_label_width"><?php esc_html_e('Label width', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <input id="qfb_form_label_width" type="text" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'labelWidth')); ?>">
                                                                <p class="qfb-description"><?php printf(esc_html__('Specify the width of the label, any valid CSS width is accepted, e.g. %s.', 'quform'), '<code>200px</code>'); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_required_text"><?php esc_html_e('Required indicator text', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <input type="text" id="qfb_form_required_text" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'requiredText')); ?>">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_required_text_color"><?php esc_html_e('Required text color', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <input type="text" id="qfb_form_required_text_color" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'requiredTextColor')); ?>" class="qfb-colorpicker">
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>

                                            </div>

                                            <div class="qfb-tabs-panel">

                                                <div class="qfb-settings">

                                                    <div class="qfb-settings-heading qfb-settings-heading-light-right"><?php esc_html_e('Fields', 'quform'); ?><i class="qfb-icon qfb-icon-pencil"></i></div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Controls the padding and font size of fields in this form.', 'quform'); ?></div></div>
                                                            <label for="qfb_form_field_size"><?php esc_html_e('Field size', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <?php echo $builder->getSizeSelectHtml('qfb_form_field_size', $builder->getFormConfigValue($form, 'fieldSize'), false); ?>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Controls the width of fields in this form.', 'quform'); ?></div></div>
                                                            <label for="qfb_form_field_width"><?php esc_html_e('Field width', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <?php echo $builder->getFieldWidthSelectHtml('qfb_form_field_width', $builder->getFormConfigValue($form, 'fieldWidth'), false); ?>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting<?php echo $builder->getFormConfigValue($form, 'fieldWidth') != 'custom' ? ' qfb-hidden' : ''; ?>">
                                                        <div class="qfb-setting-label">
                                                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Enter a custom width using any CSS unit.', 'quform'); ?></div></div>
                                                            <label for="qfb_form_field_width_custom"><?php esc_html_e('Field custom width', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <input type="text" id="qfb_form_field_width_custom" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'fieldWidthCustom')); ?>">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_field_background_color"><?php esc_html_e('Field background color', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <div id="qfb-field-background-color-tabs">
                                                                    <ul class="qfb-tabs-nav qfb-sub-tabs-nav qfb-cf">
                                                                        <li class="qfb-current-tab"><a><?php esc_html_e('Default', 'quform'); ?></a></li>
                                                                        <li><a><?php esc_html_e('Hover', 'quform'); ?></a></li>
                                                                        <li><a><?php esc_html_e('Focus', 'quform'); ?></a></li>
                                                                    </ul>
                                                                    <div class="qfb-tabs-panel">
                                                                        <input type="text" id="qfb_form_field_background_color" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'fieldBackgroundColor')); ?>" class="qfb-colorpicker">
                                                                    </div>
                                                                    <div class="qfb-tabs-panel">
                                                                        <input type="text" id="qfb_form_field_background_color_hover" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'fieldBackgroundColorHover')); ?>" class="qfb-colorpicker">
                                                                    </div>
                                                                    <div class="qfb-tabs-panel">
                                                                        <input type="text" id="qfb_form_field_background_color_focus" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'fieldBackgroundColorFocus')); ?>" class="qfb-colorpicker">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_field_border_color"><?php esc_html_e('Field border color', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <div id="qfb-field-border-color-tabs">
                                                                    <ul class="qfb-tabs-nav qfb-sub-tabs-nav qfb-cf">
                                                                        <li class="qfb-current-tab"><a><?php esc_html_e('Default', 'quform'); ?></a></li>
                                                                        <li><a><?php esc_html_e('Hover', 'quform'); ?></a></li>
                                                                        <li><a><?php esc_html_e('Focus', 'quform'); ?></a></li>
                                                                    </ul>
                                                                    <div class="qfb-tabs-panel">
                                                                        <input type="text" id="qfb_form_field_border_color" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'fieldBorderColor')); ?>" class="qfb-colorpicker">
                                                                    </div>
                                                                    <div class="qfb-tabs-panel">
                                                                        <input type="text" id="qfb_form_field_border_color_hover" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'fieldBorderColorHover')); ?>" class="qfb-colorpicker">
                                                                    </div>
                                                                    <div class="qfb-tabs-panel">
                                                                        <input type="text" id="qfb_form_field_border_color_focus" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'fieldBorderColorFocus')); ?>" class="qfb-colorpicker">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_field_text_color"><?php esc_html_e('Field text color', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <div id="qfb-field-text-color-tabs">
                                                                    <ul class="qfb-tabs-nav qfb-sub-tabs-nav qfb-cf">
                                                                        <li class="qfb-current-tab"><a><?php esc_html_e('Default', 'quform'); ?></a></li>
                                                                        <li><a><?php esc_html_e('Hover', 'quform'); ?></a></li>
                                                                        <li><a><?php esc_html_e('Focus', 'quform'); ?></a></li>
                                                                    </ul>
                                                                    <div class="qfb-tabs-panel">
                                                                        <input type="text" id="qfb_form_field_text_color" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'fieldTextColor')); ?>" class="qfb-colorpicker">
                                                                    </div>
                                                                    <div class="qfb-tabs-panel">
                                                                        <input type="text" id="qfb_form_field_text_color_hover" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'fieldTextColorHover')); ?>" class="qfb-colorpicker">
                                                                    </div>
                                                                    <div class="qfb-tabs-panel">
                                                                        <input type="text" id="qfb_form_field_text_color_focus" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'fieldTextColorFocus')); ?>" class="qfb-colorpicker">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Enter any CSS styles to apply to the field placeholder text.', 'quform'); ?></div></div>
                                                            <label for="qfb_form_field_placeholder_styles"><?php esc_html_e('Field placeholder styles', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <textarea id="qfb_form_field_placeholder_styles"><?php echo Quform::escape($builder->getFormConfigValue($form, 'fieldPlaceholderStyles')); ?></textarea>
                                                                <?php echo $builder->getCssHelperHtml(); ?>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>

                                            </div>

                                            <div class="qfb-tabs-panel">

                                                <div class="qfb-settings">

                                                    <div class="qfb-settings-heading qfb-settings-heading-light-right"><?php esc_html_e('Buttons', 'quform'); ?><i class="qfb-icon qfb-icon-hand-pointer-o"></i></div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_button_style"><?php esc_html_e('Button style', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <?php echo $builder->getButtonStyleSelectHtml('qfb_form_button_style', $builder->getFormConfigValue($form, 'buttonStyle'), false, __('None', 'quform')); ?>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Controls the padding and font size of buttons in this form.', 'quform'); ?></div></div>
                                                            <label for="qfb_form_button_size"><?php esc_html_e('Button size', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <?php echo $builder->getSizeSelectHtml('qfb_form_button_size', $builder->getFormConfigValue($form, 'buttonSize'), false); ?>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Controls the width of buttons in this form.', 'quform'); ?></div></div>
                                                            <label for="qfb_form_button_width"><?php esc_html_e('Button width', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <?php echo $builder->getButtonWidthSelectHtml('qfb_form_button_width', $builder->getFormConfigValue($form, 'buttonWidth'), false); ?>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting<?php echo $builder->getFormConfigValue($form, 'buttonWidth') != 'custom' ? ' qfb-hidden' : ''; ?>">
                                                        <div class="qfb-setting-label">
                                                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Enter a custom width using any CSS unit.', 'quform'); ?></div></div>
                                                            <label for="qfb_form_button_width_custom"><?php esc_html_e('Button custom width', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <input type="text" id="qfb_form_button_width_custom" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'buttonWidthCustom')); ?>">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_button_animation"><?php esc_html_e('Button animation', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <select id="qfb_form_button_animation">
                                                                    <option value="" <?php selected($builder->getFormConfigValue($form, 'buttonAnimation'), ''); ?>><?php esc_html_e('None', 'quform'); ?></option>
                                                                    <option value="one" <?php selected($builder->getFormConfigValue($form, 'buttonAnimation'), 'one'); ?>><?php esc_html_e('Blast', 'quform'); ?></option>
                                                                    <option value="two" <?php selected($builder->getFormConfigValue($form, 'buttonAnimation'), 'two'); ?>><?php esc_html_e('Confidence', 'quform'); ?></option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_button_background_color"><?php esc_html_e('Button background color', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <div id="qfb-button-background-color-tabs">
                                                                    <ul class="qfb-tabs-nav qfb-sub-tabs-nav qfb-cf">
                                                                        <li class="qfb-current-tab"><a><?php esc_html_e('Default', 'quform'); ?></a></li>
                                                                        <li><a><?php esc_html_e('Hover', 'quform'); ?></a></li>
                                                                        <li><a><?php esc_html_e('Active', 'quform'); ?></a></li>
                                                                    </ul>
                                                                    <div class="qfb-tabs-panel">
                                                                        <input type="text" id="qfb_form_button_background_color" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'buttonBackgroundColor')); ?>" class="qfb-colorpicker">
                                                                    </div>
                                                                    <div class="qfb-tabs-panel">
                                                                        <input type="text" id="qfb_form_button_background_color_hover" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'buttonBackgroundColorHover')); ?>" class="qfb-colorpicker">
                                                                    </div>
                                                                    <div class="qfb-tabs-panel">
                                                                        <input type="text" id="qfb_form_button_background_color_active" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'buttonBackgroundColorActive')); ?>" class="qfb-colorpicker">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_button_border_color"><?php esc_html_e('Button border color', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <div id="qfb-button-border-color-tabs">
                                                                    <ul class="qfb-tabs-nav qfb-sub-tabs-nav qfb-cf">
                                                                        <li class="qfb-current-tab"><a><?php esc_html_e('Default', 'quform'); ?></a></li>
                                                                        <li><a><?php esc_html_e('Hover', 'quform'); ?></a></li>
                                                                        <li><a><?php esc_html_e('Active', 'quform'); ?></a></li>
                                                                    </ul>
                                                                    <div class="qfb-tabs-panel">
                                                                        <input type="text" id="qfb_form_button_border_color" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'buttonBorderColor')); ?>" class="qfb-colorpicker">
                                                                    </div>
                                                                    <div class="qfb-tabs-panel">
                                                                        <input type="text" id="qfb_form_button_border_color_hover" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'buttonBorderColorHover')); ?>" class="qfb-colorpicker">
                                                                    </div>
                                                                    <div class="qfb-tabs-panel">
                                                                        <input type="text" id="qfb_form_button_border_color_active" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'buttonBorderColorActive')); ?>" class="qfb-colorpicker">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_button_text_color"><?php esc_html_e('Button text color', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <div id="qfb-button-text-color-tabs">
                                                                    <ul class="qfb-tabs-nav qfb-sub-tabs-nav qfb-cf">
                                                                        <li class="qfb-current-tab"><a><?php esc_html_e('Default', 'quform'); ?></a></li>
                                                                        <li><a><?php esc_html_e('Hover', 'quform'); ?></a></li>
                                                                        <li><a><?php esc_html_e('Active', 'quform'); ?></a></li>
                                                                    </ul>
                                                                    <div class="qfb-tabs-panel">
                                                                        <input type="text" id="qfb_form_button_text_color" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'buttonTextColor')); ?>" class="qfb-colorpicker">
                                                                    </div>
                                                                    <div class="qfb-tabs-panel">
                                                                        <input type="text" id="qfb_form_button_text_color_hover" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'buttonTextColorHover')); ?>" class="qfb-colorpicker">
                                                                    </div>
                                                                    <div class="qfb-tabs-panel">
                                                                        <input type="text" id="qfb_form_button_text_color_active" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'buttonTextColorActive')); ?>" class="qfb-colorpicker">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_button_icon_color"><?php esc_html_e('Button icon color', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <div id="qfb-button-icon-color-tabs">
                                                                    <ul class="qfb-tabs-nav qfb-sub-tabs-nav qfb-cf">
                                                                        <li class="qfb-current-tab"><a><?php esc_html_e('Default', 'quform'); ?></a></li>
                                                                        <li><a><?php esc_html_e('Hover', 'quform'); ?></a></li>
                                                                        <li><a><?php esc_html_e('Active', 'quform'); ?></a></li>
                                                                    </ul>
                                                                    <div class="qfb-tabs-panel">
                                                                        <input type="text" id="qfb_form_button_icon_color" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'buttonIconColor')); ?>" class="qfb-colorpicker">
                                                                    </div>
                                                                    <div class="qfb-tabs-panel">
                                                                        <input type="text" id="qfb_form_button_icon_color_hover" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'buttonIconColorHover')); ?>" class="qfb-colorpicker">
                                                                    </div>
                                                                    <div class="qfb-tabs-panel">
                                                                        <input type="text" id="qfb_form_button_icon_color_active" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'buttonIconColorActive')); ?>" class="qfb-colorpicker">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('You can use a custom image or HTML to customize the submit button.', 'quform'); ?></div></div>
                                                            <label for="qfb_form_submit_button_type"><?php esc_html_e('Submit button type', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <select id="qfb_form_submit_button_type">
                                                                    <option value="default" <?php selected($builder->getFormConfigValue($form, 'submitType'), 'default'); ?>><?php esc_html_e('Default button', 'quform'); ?></option>
                                                                    <option value="image" <?php selected($builder->getFormConfigValue($form, 'submitType'), 'image'); ?>><?php esc_html_e('Custom image', 'quform'); ?></option>
                                                                    <option value="html" <?php selected($builder->getFormConfigValue($form, 'submitType'), 'html'); ?>><?php esc_html_e('Custom HTML', 'quform'); ?></option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting<?php echo $builder->getFormConfigValue($form, 'submitType') == 'default' ? '' : ' qfb-hidden'; ?>">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_submit_button_text"><?php esc_html_e('Submit button text', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <input type="text" id="qfb_form_submit_button_text" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'submitText')); ?>">
                                                                <p class="qfb-description">
                                                                    <?php
                                                                        printf(
                                                                            /* translators: %s: the default button text */
                                                                            esc_html__('Change the default text of the button which is "%s".', 'quform'),
                                                                            __('Send', 'quform')
                                                                        );
                                                                    ?>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting<?php echo $builder->getFormConfigValue($form, 'submitType') == 'default' ? '' : ' qfb-hidden'; ?>">
                                                        <div class="qfb-setting-label">
                                                            <label><?php esc_html_e('Submit button icon', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <?php echo $builder->getSelectIconHtml('qfb_form_submit_button_icon', $builder->getFormConfigValue($form, 'submitIcon')); ?>
                                                                <p class="qfb-description"><?php esc_html_e('Choose an icon for the submit button.', 'quform'); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting<?php echo $builder->getFormConfigValue($form, 'submitType') == 'default' ? '' : ' qfb-hidden'; ?>">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_submit_button_icon_position"><?php esc_html_e('Submit button icon position', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <?php echo $builder->getIconPositionSelectHtml('qfb_form_submit_button_icon_position', $builder->getFormConfigValue($form, 'submitIconPosition'), false); ?>
                                                                <p class="qfb-description"><?php esc_html_e('Choose the icon position relative to the button text.', 'quform'); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting<?php echo $builder->getFormConfigValue($form, 'submitType') == 'image' ? '' : ' qfb-hidden'; ?>">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_submit_button_image"><?php esc_html_e('Submit button image URL', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <input type="text" id="qfb_form_submit_button_image" class="qfb-width-300" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'submitImage')); ?>">
                                                                <span id="qfb_form_submit_button_image_browse" class="qfb-button-blue qfb-browse-button"><i class="qfb-mdi qfb-mdi-panorama"></i><?php esc_html_e('Browse', 'quform'); ?></span>
                                                                <p class="qfb-description"><?php esc_html_e('Enter the URL to an image or upload one.', 'quform'); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting<?php echo $builder->getFormConfigValue($form, 'submitType') == 'html' ? '' : ' qfb-hidden'; ?>">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_submit_button_html"><?php esc_html_e('Submit button HTML', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <textarea id="qfb_form_submit_button_html"><?php echo Quform::escape($builder->getFormConfigValue($form, 'submitHtml')); ?></textarea>
                                                                <p class="qfb-description"><?php esc_html_e('Enter custom HTML for the button.', 'quform'); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting<?php echo count($builder->getFormConfigValue($form, 'elements')) < 2 ? ' qfb-hidden' : ''; ?>">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_next_button_type"><?php esc_html_e('Next button type', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <select id="qfb_form_next_button_type">
                                                                    <option value="default" <?php selected($builder->getFormConfigValue($form, 'nextType'), 'default'); ?>><?php esc_html_e('Default button', 'quform'); ?></option>
                                                                    <option value="image" <?php selected($builder->getFormConfigValue($form, 'nextType'), 'image'); ?>><?php esc_html_e('Custom image', 'quform'); ?></option>
                                                                    <option value="html" <?php selected($builder->getFormConfigValue($form, 'nextType'), 'html'); ?>><?php esc_html_e('Custom HTML', 'quform'); ?></option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting<?php echo count($builder->getFormConfigValue($form, 'elements')) < 2 || $builder->getFormConfigValue($form, 'nextType') != 'default' ? ' qfb-hidden' : ''; ?>">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_next_button_text"><?php esc_html_e('Next button text', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <input type="text" id="qfb_form_next_button_text" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'nextText')); ?>">
                                                                <p class="qfb-description"><?php printf(esc_html__('Change the default text of the button which is "%s".', 'quform'), __('Next', 'quform')); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting<?php echo count($builder->getFormConfigValue($form, 'elements')) < 2 || $builder->getFormConfigValue($form, 'nextType') != 'default' ? ' qfb-hidden' : ''; ?>">
                                                        <div class="qfb-setting-label">
                                                            <label><?php esc_html_e('Next button icon', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <?php echo $builder->getSelectIconHtml('qfb_form_next_button_icon', $builder->getFormConfigValue($form, 'nextIcon')); ?>
                                                                <p class="qfb-description"><?php esc_html_e('Choose an icon for the next button.', 'quform'); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting<?php echo count($builder->getFormConfigValue($form, 'elements')) < 2 || $builder->getFormConfigValue($form, 'nextType') != 'default' ? ' qfb-hidden' : ''; ?>">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_next_button_icon_position"><?php esc_html_e('Next button icon position', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <?php echo $builder->getIconPositionSelectHtml('qfb_form_next_button_icon_position', $builder->getFormConfigValue($form, 'nextIconPosition'), false); ?>
                                                                <p class="qfb-description"><?php esc_html_e('Choose the icon position relative to the button text.', 'quform'); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting<?php echo count($builder->getFormConfigValue($form, 'elements')) < 2 || $builder->getFormConfigValue($form, 'nextType') != 'image' ? ' qfb-hidden' : ''; ?>">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_next_button_image"><?php esc_html_e('Next button image URL', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <input type="text" id="qfb_form_next_button_image" class="qfb-width-300" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'nextImage')); ?>">
                                                                <span id="qfb_form_next_button_image_browse" class="qfb-button-blue qfb-browse-button"><i class="qfb-mdi qfb-mdi-panorama"></i><?php esc_html_e('Browse', 'quform'); ?></span>
                                                                <p class="qfb-description"><?php esc_html_e('Enter the URL to an image or upload one.', 'quform'); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting<?php echo count($builder->getFormConfigValue($form, 'elements')) < 2 || $builder->getFormConfigValue($form, 'nextType') != 'html' ? ' qfb-hidden' : ''; ?>">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_next_button_html"><?php esc_html_e('Next button HTML', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <textarea id="qfb_form_next_button_html"><?php echo Quform::escape($builder->getFormConfigValue($form, 'nextHtml')); ?></textarea>
                                                                <p class="qfb-description"><?php esc_html_e('Enter custom HTML for the button.', 'quform'); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting<?php echo count($builder->getFormConfigValue($form, 'elements')) < 2 ? ' qfb-hidden' : ''; ?>">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_back_button_type"><?php esc_html_e('Back button type', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <select id="qfb_form_back_button_type">
                                                                    <option value="default" <?php selected($builder->getFormConfigValue($form, 'backType'), 'default'); ?>><?php esc_html_e('Default button', 'quform'); ?></option>
                                                                    <option value="image" <?php selected($builder->getFormConfigValue($form, 'backType'), 'image'); ?>><?php esc_html_e('Custom image', 'quform'); ?></option>
                                                                    <option value="html" <?php selected($builder->getFormConfigValue($form, 'backType'), 'html'); ?>><?php esc_html_e('Custom HTML', 'quform'); ?></option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting<?php echo count($builder->getFormConfigValue($form, 'elements')) < 2 || $builder->getFormConfigValue($form, 'backType') != 'default' ? ' qfb-hidden' : ''; ?>">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_back_button_text"><?php esc_html_e('Back button text', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <input type="text" id="qfb_form_back_button_text" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'backText')); ?>">
                                                                <p class="qfb-description"><?php printf(esc_html__('Change the default text of the button which is "%s".', 'quform'), __('Back', 'quform')); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting<?php echo count($builder->getFormConfigValue($form, 'elements')) < 2 || $builder->getFormConfigValue($form, 'backType') != 'default' ? ' qfb-hidden' : ''; ?>">
                                                        <div class="qfb-setting-label">
                                                            <label><?php esc_html_e('Back button icon', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <?php echo $builder->getSelectIconHtml('qfb_form_back_button_icon', $builder->getFormConfigValue($form, 'backIcon')); ?>
                                                                <p class="qfb-description"><?php esc_html_e('Choose an icon for the back button.', 'quform'); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting<?php echo count($builder->getFormConfigValue($form, 'elements')) < 2 || $builder->getFormConfigValue($form, 'backType') != 'default' ? ' qfb-hidden' : ''; ?>">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_back_button_icon_position"><?php esc_html_e('Back button icon position', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <?php echo $builder->getIconPositionSelectHtml('qfb_form_back_button_icon_position', $builder->getFormConfigValue($form, 'backIconPosition'), false); ?>
                                                                <p class="qfb-description"><?php esc_html_e('Choose the icon position relative to the button text.', 'quform'); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting<?php echo count($builder->getFormConfigValue($form, 'elements')) < 2 || $builder->getFormConfigValue($form, 'backType') != 'image' ? ' qfb-hidden' : ''; ?>">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_back_button_image"><?php esc_html_e('Back button image URL', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <input type="text" id="qfb_form_back_button_image" class="qfb-width-300" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'backImage')); ?>">
                                                                <span id="qfb_form_back_button_image_browse" class="qfb-button-blue qfb-browse-button"><i class="qfb-mdi qfb-mdi-panorama"></i><?php esc_html_e('Browse', 'quform'); ?></span>
                                                                <p class="qfb-description"><?php esc_html_e('Enter the URL to an image or upload one.', 'quform'); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting<?php echo count($builder->getFormConfigValue($form, 'elements')) < 2 || $builder->getFormConfigValue($form, 'backType') != 'html' ? ' qfb-hidden' : ''; ?>">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_back_button_html"><?php esc_html_e('Back button HTML', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <textarea id="qfb_form_back_button_html"><?php echo Quform::escape($builder->getFormConfigValue($form, 'back.html')); ?></textarea>
                                                                <p class="qfb-description"><?php esc_html_e('Enter custom HTML for the button.', 'quform'); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting<?php echo count($builder->getFormConfigValue($form, 'elements')) < 2 ? ' qfb-hidden' : ''; ?>"">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_back_location"><?php esc_html_e('Back button location', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <select id="qfb_form_back_location">
                                                                    <option value="" <?php selected($builder->getFormConfigValue($form, 'backLocation'), ''); ?>><?php esc_html_e('Before submit/next button (default)', 'quform'); ?></option>
                                                                    <option value="after" <?php selected($builder->getFormConfigValue($form, 'backLocation'), 'numbers'); ?>><?php esc_html_e('After submit/next button', 'quform'); ?></option>
                                                                </select>
                                                                <p class="qfb-description"><?php esc_html_e('Choose the location of the back button relative to the submit/next button.', 'quform'); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Choose the style of Radio Button and Checkbox fields.', 'quform'); ?></div></div>
                                                            <label for="qfb_form_options_style"><?php esc_html_e('Options style', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <?php echo $builder->getOptionsStyleSelectHtml('qfb_form_options_style', $builder->getFormConfigValue($form, 'optionsStyle'), false); ?>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_options_button_style"><?php esc_html_e('Options button style', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <?php echo $builder->getButtonStyleSelectHtml('qfb_form_options_button_style', $builder->getFormConfigValue($form, 'optionsButtonStyle'), false); ?>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Controls the padding and font size of options buttons in this form.', 'quform'); ?></div></div>
                                                            <label for="qfb_form_options_button_size"><?php esc_html_e('Options button size', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <?php echo $builder->getSizeSelectHtml('qfb_form_options_button_size', $builder->getFormConfigValue($form, 'optionsButtonSize'), false); ?>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Controls the width of options buttons in this form.', 'quform'); ?></div></div>
                                                            <label for="qfb_form_options_button_width"><?php esc_html_e('Options button width', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <?php echo $builder->getButtonWidthSelectHtml('qfb_form_options_button_width', $builder->getFormConfigValue($form, 'optionsButtonWidth'), false); ?>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting<?php echo $builder->getFormConfigValue($form, 'optionsButtonWidth') != 'custom' ? ' qfb-hidden' : ''; ?>">
                                                        <div class="qfb-setting-label">
                                                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Enter a custom width using any CSS unit.', 'quform'); ?></div></div>
                                                            <label for="qfb_form_options_button_width_custom"><?php esc_html_e('Options button custom width', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <input type="text" id="qfb_form_options_button_width_custom" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'optionsButtonWidthCustom')); ?>">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Choose the icon position relative to the button text.', 'quform'); ?></div></div>
                                                            <label for="qfb_form_option_button_icon_position"><?php esc_html_e('Options button icon position', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <?php echo $builder->getIconPositionSelectHtml('qfb_form_options_button_icon_position', $builder->getFormConfigValue($form, 'optionsButtonIconPosition'), false); ?>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>

                                            </div>

                                            <div class="qfb-tabs-panel">

                                                <div class="qfb-settings">

                                                    <div class="qfb-settings-heading qfb-settings-heading-light-right"><?php esc_html_e('Page progress', 'quform'); ?><i class="qfb-mdi qfb-mdi-compare_arrows"></i></div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_page_progress_type"><?php esc_html_e('Progress type', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <select id="qfb_form_page_progress_type">
                                                                    <option value="" <?php selected($builder->getFormConfigValue($form, 'pageProgressType'), ''); ?>><?php esc_html_e('None', 'quform'); ?></option>
                                                                    <option value="numbers" <?php selected($builder->getFormConfigValue($form, 'pageProgressType'), 'numbers'); ?>><?php esc_html_e('Progress bar (numbers)', 'quform'); ?></option>
                                                                    <option value="percentage" <?php selected($builder->getFormConfigValue($form, 'pageProgressType'), 'percentage'); ?>><?php esc_html_e('Progress bar (percentage)', 'quform'); ?></option>
                                                                    <option value="tabs" <?php selected($builder->getFormConfigValue($form, 'pageProgressType'), 'tabs'); ?>><?php esc_html_e('Tabs', 'quform'); ?></option>
                                                                </select>
                                                                <p class="qfb-description"><?php esc_html_e('Choose the style of page progress shown at the top of the form.', 'quform'); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>

                                            </div>

                                            <div class="qfb-tabs-panel">

                                                <div class="qfb-settings">

                                                    <div class="qfb-settings-heading qfb-settings-heading-light-right"><?php esc_html_e('Loading', 'quform'); ?><i class="qfb-icon qfb-icon-spinner"></i></div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_loading_type"><?php esc_html_e('Type', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <select id="qfb_form_loading_type">
                                                                    <option value="" <?php selected($builder->getFormConfigValue($form, 'loadingType'), ''); ?>><?php esc_html_e('None', 'quform'); ?></option>
                                                                    <option value="spinner-1" <?php selected($builder->getFormConfigValue($form, 'loadingType'), 'spinner-1'); ?>><?php esc_html_e('Qspinner', 'quform'); ?></option>
                                                                    <option value="spinner-2" <?php selected($builder->getFormConfigValue($form, 'loadingType'), 'spinner-2'); ?>><?php esc_html_e('WordPress Spinner', 'quform'); ?></option>
                                                                    <option value="spinner-3" <?php selected($builder->getFormConfigValue($form, 'loadingType'), 'spinner-3'); ?>><?php esc_html_e('Flying Message', 'quform'); ?></option>
                                                                    <option value="spinner-4" <?php selected($builder->getFormConfigValue($form, 'loadingType'), 'spinner-4'); ?>><?php esc_html_e('Single Dot', 'quform'); ?></option>
                                                                    <option value="spinner-5" <?php selected($builder->getFormConfigValue($form, 'loadingType'), 'spinner-5'); ?>><?php esc_html_e('Spinner classic', 'quform'); ?></option>
                                                                    <option value="spinner-6" <?php selected($builder->getFormConfigValue($form, 'loadingType'), 'spinner-6'); ?>><?php esc_html_e('Tripple Dot', 'quform'); ?></option>
                                                                    <option value="spinner-7" <?php selected($builder->getFormConfigValue($form, 'loadingType'), 'spinner-7'); ?>><?php esc_html_e('Dot', 'quform'); ?></option>
                                                                    <option value="custom" <?php selected($builder->getFormConfigValue($form, 'loadingType'), 'custom'); ?>><?php esc_html_e('Custom...', 'quform'); ?></option>
                                                                </select>
                                                                <p class="qfb-description"><?php esc_html_e('Choose the style of loading indicator to show when the form is processing.', 'quform'); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting<?php echo $builder->getFormConfigValue($form, 'loadingType') != 'custom' ? ' qfb-hidden' : ''; ?>">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_loading_custom"><?php esc_html_e('Custom loading', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <textarea id="qfb_form_loading_custom"><?php echo Quform::escape($builder->getFormConfigValue($form, 'loadingCustom')); ?></textarea>
                                                                <p class="qfb-description"><?php esc_html_e('Text, HTML and shortcodes can be used here.', 'quform'); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting<?php echo Quform::isNonEmptyString($builder->getFormConfigValue($form, 'loadingType')) ? '' : ' qfb-hidden'; ?>">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_loading_position"><?php esc_html_e('Position', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <select id="qfb_form_loading_position">
                                                                    <option value="left" <?php selected($builder->getFormConfigValue($form, 'loadingPosition'), 'left'); ?>><?php esc_html_e('Left', 'quform'); ?></option>
                                                                    <option value="right" <?php selected($builder->getFormConfigValue($form, 'loadingPosition'), 'right'); ?>><?php esc_html_e('Right', 'quform'); ?></option>
                                                                    <option value="center" <?php selected($builder->getFormConfigValue($form, 'loadingPosition'), 'center'); ?>><?php esc_html_e('Center', 'quform'); ?></option>
                                                                    <option value="over-button" <?php selected($builder->getFormConfigValue($form, 'loadingPosition'), 'over-button'); ?>><?php esc_html_e('Over button', 'quform'); ?></option>
                                                                    <option value="over-screen" <?php selected($builder->getFormConfigValue($form, 'loadingPosition'), 'over-screen'); ?>><?php esc_html_e('Over screen', 'quform'); ?></option>
                                                                    <option value="over-form" <?php selected($builder->getFormConfigValue($form, 'loadingPosition'), 'over-form'); ?>><?php esc_html_e('Over form', 'quform'); ?></option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting<?php echo Quform::isNonEmptyString($builder->getFormConfigValue($form, 'loadingType')) ? '' : ' qfb-hidden'; ?>">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_loading_color"><?php esc_html_e('Color', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <input type="text" id="qfb_form_loading_color" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'loadingColor')); ?>" class="qfb-colorpicker">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting<?php echo Quform::isNonEmptyString($builder->getFormConfigValue($form, 'loadingType')) && strpos($builder->getFormConfigValue($form, 'loadingPosition'), 'over') !== false ? '' : ' qfb-hidden'; ?>">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_loading_overlay"><?php esc_html_e('Overlay', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <input type="checkbox" class="qfb-toggle" id="qfb_form_loading_overlay" <?php checked($builder->getFormConfigValue($form, 'loadingOverlay')); ?>>
                                                                <label for="qfb_form_loading_overlay"></label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting<?php echo Quform::isNonEmptyString($builder->getFormConfigValue($form, 'loadingType')) && strpos($builder->getFormConfigValue($form, 'loadingPosition'), 'over') !== false && $builder->getFormConfigValue($form, 'loadingOverlay') ? '' : ' qfb-hidden'; ?>">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_loading_overlay_color"><?php esc_html_e('Overlay color', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <input type="text" id="qfb_form_loading_overlay_color" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'loadingOverlayColor')); ?>" class="qfb-colorpicker">
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>

                                            </div>

                                            <div class="qfb-tabs-panel">

                                                <div class="qfb-settings">

                                                    <div class="qfb-settings-heading qfb-settings-heading-light-right"><?php esc_html_e('Tooltips', 'quform'); ?><i class="qfb-mdi qfb-mdi-speaker_notes"></i></div>

                                                    <div class="qfb-setting">
                                                        <div class="qfb-setting-label">
                                                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e("What's a tooltip? You're looking at one.", 'quform'); ?></div></div>
                                                            <label for="qfb_form_tooltips_enabled"><?php esc_html_e('Enable tooltips', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <input type="checkbox" class="qfb-toggle" id="qfb_form_tooltips_enabled" <?php checked($builder->getFormConfigValue($form, 'tooltipsEnabled')); ?>>
                                                                <label for="qfb_form_tooltips_enabled"></label>
                                                                <p class="qfb-description"><?php esc_html_e('If enabled, when the user hovers over an element with tooltip text set, a tooltip will appear.', 'quform'); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting<?php echo $builder->getFormConfigValue($form, 'tooltipsEnabled') ? '' : ' qfb-hidden'; ?>">
                                                        <div class="qfb-setting-label">
                                                            <div class="qfb-tooltip-icon">
                                                                <div class="qfb-tooltip-content">
                                                                    <?php
                                                                        printf(
                                                                            /* translators: %1$s: open span tag, %2$s: close span tag */
                                                                            esc_html__('If set to %1$sField%2$s, the tooltip will show when the user interacts with the field. If set to %1$sIcon%2$s, the tooltip will be shown when the user interacts with an icon.', 'quform'),
                                                                            '<span class="qfb-bold">',
                                                                            '</span>'
                                                                        );
                                                                    ?>
                                                                </div>
                                                            </div>
                                                            <label for="qfb_form_tooltip_type"><?php esc_html_e('Trigger', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <select id="qfb_form_tooltip_type">
                                                                    <option value="field" <?php selected($builder->getFormConfigValue($form, 'tooltipType'), 'field'); ?>><?php esc_html_e('Field', 'quform'); ?></option>
                                                                    <option value="icon" <?php selected($builder->getFormConfigValue($form, 'tooltipType'), 'icon'); ?>><?php esc_html_e('Icon', 'quform'); ?></option>
                                                                </select>
                                                                <p class="qfb-description"><?php esc_html_e('Choose what the user will be interacting with to show the tooltip.', 'quform'); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting<?php echo $builder->getFormConfigValue($form, 'tooltipsEnabled') ? '' : ' qfb-hidden'; ?>">
                                                        <div class="qfb-setting-label">
                                                            <label for="qfb_form_tooltip_event"><?php esc_html_e('Event', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <select id="qfb_form_tooltip_event">
                                                                    <option value="hover" <?php selected($builder->getFormConfigValue($form, 'tooltipEvent'), 'hover'); ?>><?php esc_html_e('Hover', 'quform'); ?></option>
                                                                    <option value="click" <?php selected($builder->getFormConfigValue($form, 'tooltipEvent'), 'click'); ?>><?php esc_html_e('Click', 'quform'); ?></option>
                                                                </select>
                                                                <p class="qfb-description"><?php esc_html_e('Choose the event that will trigger the tooltip to show.', 'quform'); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting<?php echo $builder->getFormConfigValue($form, 'tooltipsEnabled') ? '' : ' qfb-hidden'; ?>">
                                                        <div class="qfb-setting-label">
                                                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Set the icon to use for the Icon tooltip trigger.', 'quform'); ?></div></div>
                                                            <label><?php esc_html_e('Icon', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">
                                                                <?php echo $builder->getSelectIconHtml('qfb_form_tooltip_icon', $builder->getFormConfigValue($form, 'tooltipIcon')); ?>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="qfb-setting<?php echo $builder->getFormConfigValue($form, 'tooltipsEnabled') ? '' : ' qfb-hidden'; ?>">
                                                        <div class="qfb-setting-label">
                                                            <label><?php esc_html_e('Tooltip style', 'quform'); ?></label>
                                                        </div>
                                                        <div class="qfb-setting-inner">
                                                            <div class="qfb-setting-input">

                                                                <div class="qfb-sub-settings">

                                                                    <div id="qfb-tooltip-example-wrap" class="qfb-sub-setting<?php echo $builder->getFormConfigValue($form, 'tooltipStyle') == 'custom' ? ' qfb-hidden' : '' ?>">
                                                                        <input type="text" id="qfb-tooltip-example" class="qfb-tooltip-example" value="<?php esc_attr_e('Hover me for preview', 'quform'); ?>">
                                                                    </div>

                                                                    <div class="qfb-sub-setting">
                                                                        <div class="qfb-sub-setting-label">
                                                                            <label for="qfb_form_tooltip_style"><?php esc_html_e('Style', 'quform'); ?></label>
                                                                        </div>
                                                                        <div class="qfb-sub-setting-inner">
                                                                            <div class="qfb-sub-setting-input">
                                                                                <select id="qfb_form_tooltip_style">
                                                                                    <optgroup label="<?php esc_attr_e('Quform styles', 'quform'); ?>">
                                                                                        <option value="qtip-quform-dark" <?php selected($builder->getFormConfigValue($form, 'tooltipStyle'), 'qtip-quform-dark'); ?>><?php esc_html_e('Dark', 'quform'); ?> (qtip-quform-dark)</option>
                                                                                    </optgroup>
                                                                                    <optgroup label="<?php esc_attr_e('CSS2 styles', 'quform'); ?>">
                                                                                        <option value="qtip-cream" <?php selected($builder->getFormConfigValue($form, 'tooltipStyle'), 'qtip-cream'); ?>><?php esc_html_e('Cream', 'quform'); ?> (qtip-cream)</option>
                                                                                        <option value="qtip-plain" <?php selected($builder->getFormConfigValue($form, 'tooltipStyle'), 'qtip-plain'); ?>><?php esc_html_e('Plain', 'quform'); ?> (qtip-plain)</option>
                                                                                        <option value="qtip-light" <?php selected($builder->getFormConfigValue($form, 'tooltipStyle'), 'qtip-light'); ?>><?php esc_html_e('Light', 'quform'); ?> (qtip-light)</option>
                                                                                        <option value="qtip-dark" <?php selected($builder->getFormConfigValue($form, 'tooltipStyle'), 'qtip-dark'); ?>><?php esc_html_e('Dark', 'quform'); ?> (qtip-dark)</option>
                                                                                        <option value="qtip-red" <?php selected($builder->getFormConfigValue($form, 'tooltipStyle'), 'qtip-red'); ?>><?php esc_html_e('Red', 'quform'); ?> (qtip-red)</option>
                                                                                        <option value="qtip-green" <?php selected($builder->getFormConfigValue($form, 'tooltipStyle'), 'qtip-green'); ?>><?php esc_html_e('Green', 'quform'); ?> (qtip-green)</option>
                                                                                        <option value="qtip-blue" <?php selected($builder->getFormConfigValue($form, 'tooltipStyle'), 'qtip-blue'); ?>><?php esc_html_e('Blue', 'quform'); ?> (qtip-blue)</option>
                                                                                    </optgroup>
                                                                                    <optgroup label="<?php esc_attr_e('CSS3 styles', 'quform'); ?>">
                                                                                        <option value="qtip-youtube" <?php selected($builder->getFormConfigValue($form, 'tooltipStyle'), 'qtip-youtube'); ?>><?php esc_html_e('YouTube', 'quform'); ?> (qtip-youtube) </option>
                                                                                        <option value="qtip-jtools" <?php selected($builder->getFormConfigValue($form, 'tooltipStyle'), 'qtip-jtools'); ?>><?php esc_html_e('jTools', 'quform'); ?> (qtip-jtools)</option>
                                                                                        <option value="qtip-cluetip" <?php selected($builder->getFormConfigValue($form, 'tooltipStyle'), 'qtip-cluetip'); ?>><?php esc_html_e('Cluetip', 'quform'); ?> (qtip-cluetip)</option>
                                                                                        <option value="qtip-tipped" <?php selected($builder->getFormConfigValue($form, 'tooltipStyle'), 'qtip-tipped'); ?>><?php esc_html_e('Tipped', 'quform'); ?> (qtip-tipped)</option>
                                                                                        <option value="qtip-tipsy" <?php selected($builder->getFormConfigValue($form, 'tooltipStyle'), 'qtip-tipsy'); ?>><?php esc_html_e('Tipsy', 'quform'); ?> (qtip-tipsy)</option>
                                                                                    </optgroup>
                                                                                    <option value="custom" <?php selected($builder->getFormConfigValue($form, 'tooltipStyle'), 'custom'); ?>><?php esc_html_e('Custom class', 'quform'); ?></option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="qfb-sub-setting<?php echo $builder->getFormConfigValue($form, 'tooltipStyle') != 'custom' ? ' qfb-hidden' : '' ?>">
                                                                        <div class="qfb-sub-setting-label">
                                                                            <label for="qfb_form_tooltip_custom"><?php esc_html_e('Custom class', 'quform'); ?></label>
                                                                        </div>
                                                                        <div class="qfb-sub-setting-inner">
                                                                            <div class="qfb-sub-setting-input">
                                                                                <input type="text" id="qfb_form_tooltip_custom" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'tooltipCustom')); ?>">
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="qfb-sub-setting">
                                                                        <div class="qfb-sub-setting-label">
                                                                            <label for="qfb_form_tooltip_my"><?php esc_html_e('Tip position', 'quform'); ?></label>
                                                                        </div>
                                                                        <div class="qfb-sub-setting-inner">
                                                                            <div class="qfb-sub-setting-input">
                                                                                <select id="qfb_form_tooltip_my">
                                                                                    <option value="left center" <?php selected($builder->getFormConfigValue($form, 'tooltipMy'), 'left center'); ?>><?php esc_html_e('left center', 'quform'); ?></option>
                                                                                    <option value="left top" <?php selected($builder->getFormConfigValue($form, 'tooltipMy'), 'left top'); ?>><?php esc_html_e('left top', 'quform'); ?></option>
                                                                                    <option value="top left" <?php selected($builder->getFormConfigValue($form, 'tooltipMy'), 'top left'); ?>><?php esc_html_e('top left', 'quform'); ?></option>
                                                                                    <option value="top center" <?php selected($builder->getFormConfigValue($form, 'tooltipMy'), 'top center'); ?>><?php esc_html_e('top center', 'quform'); ?></option>
                                                                                    <option value="top right" <?php selected($builder->getFormConfigValue($form, 'tooltipMy'), 'top right'); ?>><?php esc_html_e('top right', 'quform'); ?></option>
                                                                                    <option value="right top" <?php selected($builder->getFormConfigValue($form, 'tooltipMy'), 'right top'); ?>><?php esc_html_e('right top', 'quform'); ?></option>
                                                                                    <option value="right center" <?php selected($builder->getFormConfigValue($form, 'tooltipMy'), 'right center'); ?>><?php esc_html_e('right center', 'quform'); ?></option>
                                                                                    <option value="right bottom" <?php selected($builder->getFormConfigValue($form, 'tooltipMy'), 'right bottom'); ?>><?php esc_html_e('right bottom', 'quform'); ?></option>
                                                                                    <option value="bottom right" <?php selected($builder->getFormConfigValue($form, 'tooltipMy'), 'bottom right'); ?>><?php esc_html_e('bottom right', 'quform'); ?></option>
                                                                                    <option value="bottom center" <?php selected($builder->getFormConfigValue($form, 'tooltipMy'), 'bottom center'); ?>><?php esc_html_e('bottom center', 'quform'); ?></option>
                                                                                    <option value="bottom left" <?php selected($builder->getFormConfigValue($form, 'tooltipMy'), 'bottom left'); ?>><?php esc_html_e('bottom left', 'quform'); ?></option>
                                                                                    <option value="left bottom" <?php selected($builder->getFormConfigValue($form, 'tooltipMy'), 'left bottom'); ?>><?php esc_html_e('left bottom', 'quform'); ?></option>
                                                                                    <option value="center" <?php selected($builder->getFormConfigValue($form, 'tooltipMy'), 'center'); ?>><?php esc_html_e('center', 'quform'); ?></option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="qfb-sub-setting">
                                                                        <div class="qfb-sub-setting-label">
                                                                            <label for="qfb_form_tooltip_at"><?php esc_html_e('Position on input', 'quform'); ?></label>
                                                                        </div>
                                                                        <div class="qfb-sub-setting-inner">
                                                                            <div class="qfb-sub-setting-input">
                                                                                <select id="qfb_form_tooltip_at">
                                                                                    <option value="left center" <?php selected($builder->getFormConfigValue($form, 'tooltipAt'), 'left center'); ?>><?php esc_html_e('left center', 'quform'); ?></option>
                                                                                    <option value="left top" <?php selected($builder->getFormConfigValue($form, 'tooltipAt'), 'left top'); ?>><?php esc_html_e('left top', 'quform'); ?></option>
                                                                                    <option value="top left" <?php selected($builder->getFormConfigValue($form, 'tooltipAt'), 'top left'); ?>><?php esc_html_e('top left', 'quform'); ?></option>
                                                                                    <option value="top center" <?php selected($builder->getFormConfigValue($form, 'tooltipAt'), 'top center'); ?>><?php esc_html_e('top center', 'quform'); ?></option>
                                                                                    <option value="top right" <?php selected($builder->getFormConfigValue($form, 'tooltipAt'), 'top right'); ?>><?php esc_html_e('top right', 'quform'); ?></option>
                                                                                    <option value="top right" <?php selected($builder->getFormConfigValue($form, 'tooltipAt'), 'top right'); ?>><?php esc_html_e('right top', 'quform'); ?></option>
                                                                                    <option value="right center" <?php selected($builder->getFormConfigValue($form, 'tooltipAt'), 'right center'); ?>><?php esc_html_e('right center', 'quform'); ?></option>
                                                                                    <option value="right bottom" <?php selected($builder->getFormConfigValue($form, 'tooltipAt'), 'right bottom'); ?>><?php esc_html_e('right bottom', 'quform'); ?></option>
                                                                                    <option value="bottom right" <?php selected($builder->getFormConfigValue($form, 'tooltipAt'), 'bottom right'); ?>><?php esc_html_e('bottom right', 'quform'); ?></option>
                                                                                    <option value="bottom center" <?php selected($builder->getFormConfigValue($form, 'tooltipAt'), 'bottom center'); ?>><?php esc_html_e('bottom center', 'quform'); ?></option>
                                                                                    <option value="bottom left" <?php selected($builder->getFormConfigValue($form, 'tooltipAt'), 'bottom left'); ?>><?php esc_html_e('bottom left', 'quform'); ?></option>
                                                                                    <option value="left bottom" <?php selected($builder->getFormConfigValue($form, 'tooltipAt'), 'left bottom'); ?>><?php esc_html_e('left bottom', 'quform'); ?></option>
                                                                                    <option value="center" <?php selected($builder->getFormConfigValue($form, 'tooltipAt'), 'center'); ?>><?php esc_html_e('center', 'quform'); ?></option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="qfb-sub-setting">
                                                                        <div class="qfb-sub-setting-label">
                                                                            <label for="qfb_form_tooltip_shadow"><?php esc_html_e('CSS3 Shadow', 'quform'); ?></label>
                                                                        </div>
                                                                        <div class="qfb-sub-setting-inner">
                                                                            <div class="qfb-sub-setting-input">
                                                                                <input type="checkbox" class="qfb-toggle" id="qfb_form_tooltip_shadow" <?php checked($builder->getFormConfigValue($form, 'tooltipShadow')); ?>>
                                                                                <label for="qfb_form_tooltip_shadow"></label>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="qfb-sub-setting">
                                                                        <div class="qfb-sub-setting-label">
                                                                            <label for="qfb_form_tooltip_rounded"><?php esc_html_e('CSS3 Rounded Corners', 'quform'); ?></label>
                                                                        </div>
                                                                        <div class="qfb-sub-setting-inner">
                                                                            <div class="qfb-sub-setting-input">
                                                                                <input type="checkbox" class="qfb-toggle" id="qfb_form_tooltip_rounded" <?php checked($builder->getFormConfigValue($form, 'tooltipRounded')); ?>>
                                                                                <label for="qfb_form_tooltip_rounded"></label>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <p class="qfb-description"><?php esc_html_e('The CSS3 effects may not work with some styles.', 'quform'); ?></p>

                                                                </div>

                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                    <div class="qfb-tabs-panel">

                                        <div class="qfb-settings">

                                            <div class="qfb-settings-heading qfb-settings-heading-light-rightqfb-settings-heading qfb-settings-heading-light-rightqfb-settings-heading qfb-settings-heading-light-right"><?php esc_html_e('Notifications', 'quform'); ?><i class="qfb-mdi qfb-mdi-mail_outline"></i></div>

                                            <div class="qfb-setting">
                                                <div id="qfb-notifications">
                                                    <?php
                                                        foreach ($builder->getFormConfigValue($form, 'notifications') as $notification) {
                                                            echo $builder->getNotificationHtml($notification);
                                                        }
                                                    ?>
                                                </div>
                                                <div id="qfb-no-notifications"<?php echo count($builder->getFormConfigValue($form, 'notifications')) ? ' class="qfb-hidden"' : ''; ?>><div class="qfb-message-box qfb-message-box-info"><div class="qfb-message-box-inner"><?php esc_html_e('No notifications have been set up.', 'quform'); ?></div></div></div>

                                                <button type="button" id="qfb-add-notification" class="qfb-button-green"><i class="qfb-mdi qfb-mdi-add_circle"></i><?php esc_html_e('Add Notification', 'quform'); ?></button>
                                            </div>

                                        </div>

                                    </div>

                                    <div class="qfb-tabs-panel">

                                        <div class="qfb-settings">

                                            <div class="qfb-settings-heading qfb-settings-heading-light-right"><?php esc_html_e('Confirmations', 'quform'); ?><i class="qfb-mdi qfb-mdi-check"></i></div>

                                            <div class="qfb-setting">
                                                <div id="qfb-confirmations">
                                                    <?php
                                                        foreach ($builder->getFormConfigValue($form, 'confirmations') as $confirmation) {
                                                            echo $builder->getConfirmationHtml($confirmation);
                                                        }
                                                    ?>
                                                </div>

                                                <button type="button" id="qfb-add-confirmation" class="qfb-button-green"><i class="qfb-mdi qfb-mdi-add_circle"></i><?php esc_html_e('Add Confirmation', 'quform'); ?></button>
                                            </div>

                                        </div>

                                    </div>

                                    <div class="qfb-tabs-panel">

                                        <div class="qfb-settings">

                                            <div class="qfb-settings-heading qfb-settings-heading-light-right"><?php esc_html_e('Error options', 'quform'); ?><i class="qfb-mdi qfb-mdi-pan_tool"></i></div>

                                            <div class="qfb-setting">
                                                <div class="qfb-setting-label">
                                                    <label for="qfb_form_errors_position"><?php esc_html_e('Validation error position', 'quform'); ?></label>
                                                </div>
                                                <div class="qfb-setting-inner">
                                                    <div class="qfb-setting-input">
                                                        <select id="qfb_form_errors_position">
                                                            <option value="" <?php selected($builder->getFormConfigValue($form, 'errorsPosition'), ''); ?>><?php esc_html_e('Full width (default)', 'quform'); ?></option>
                                                            <option value="left" <?php selected($builder->getFormConfigValue($form, 'errorsPosition'), 'left'); ?>><?php esc_html_e('Align left', 'quform'); ?></option>
                                                            <option value="absolute" <?php selected($builder->getFormConfigValue($form, 'errorsPosition'), 'absolute'); ?>><?php esc_html_e('Position absolute', 'quform'); ?></option>
                                                        </select>
                                                        <p class="qfb-description"><?php esc_html_e('Choose the position of the validation errors.', 'quform'); ?></p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="qfb-setting">
                                                <div class="qfb-setting-label">
                                                    <label><?php esc_html_e('Validation error icon', 'quform'); ?></label>
                                                </div>
                                                <div class="qfb-setting-inner">
                                                    <div class="qfb-setting-input">
                                                        <?php echo $builder->getSelectIconHtml('qfb_form_errors_icon', $builder->getFormConfigValue($form, 'errorsIcon')); ?>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="qfb-setting">
                                                <div class="qfb-setting-label">
                                                    <label for="qfb_form_error_enabled"><?php esc_html_e('Display error message', 'quform'); ?></label>
                                                </div>
                                                <div class="qfb-setting-inner">
                                                    <div class="qfb-setting-input">
                                                        <input type="checkbox" class="qfb-toggle" id="qfb_form_error_enabled" <?php checked($builder->getFormConfigValue($form, 'errorEnabled')); ?>>
                                                        <label for="qfb_form_error_enabled"></label>
                                                        <p class="qfb-description"><?php esc_html_e('If enabled, an error message will be displayed above the form if there was a validation error.', 'quform'); ?></p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="qfb-setting<?php echo $builder->getFormConfigValue($form, 'errorEnabled') ? '' : ' qfb-hidden'; ?>">
                                                <div class="qfb-setting-label">
                                                    <label for="qfb_form_error_title"><?php esc_html_e('Error message title', 'quform'); ?></label>
                                                </div>
                                                <div class="qfb-setting-inner">
                                                    <div class="qfb-setting-input">
                                                        <input type="text" id="qfb_form_error_title" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'errorTitle')); ?>">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="qfb-setting<?php echo $builder->getFormConfigValue($form, 'errorEnabled') ? '' : ' qfb-hidden'; ?>">
                                                <div class="qfb-setting-label">
                                                    <label for="qfb_form_error_content"><?php esc_html_e('Error message content', 'quform'); ?></label>
                                                </div>
                                                <div class="qfb-setting-inner">
                                                    <div class="qfb-setting-input">
                                                        <textarea id="qfb_form_error_content"><?php echo Quform::escape($builder->getFormConfigValue($form, 'errorContent')); ?></textarea>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>

                                    </div>

                                    <div class="qfb-tabs-panel">

                                        <div class="qfb-settings">

                                            <div class="qfb-settings-heading qfb-settings-heading-light-right"><?php esc_html_e('Language options', 'quform'); ?><i class="qfb-mdi qfb-mdi-translate"></i></div>

                                            <div class="qfb-setting">
                                                <div class="qfb-setting-label">
                                                    <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('The description that will show below the form title.', 'quform'); ?></div></div>
                                                    <label for="qfb_form_locale"><?php esc_html_e('Locale', 'quform'); ?></label>
                                                </div>
                                                <div class="qfb-setting-inner">
                                                    <div class="qfb-setting-input">
                                                        <select id="qfb_form_locale" style="width: 100%;">
                                                            <?php foreach ($builder->getLocales() as $key => $locale) : ?>
                                                                <option value="<?php echo esc_attr($key); ?>" <?php selected($builder->getFormConfigValue($form, 'locale'), $key); ?>><?php echo esc_html($locale['name']); ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <p class="qfb-description"><?php esc_html_e('The Locale determines the language for Datepickers and Timepickers, and the date and time formats for this form. If set to Default it will use the Locale from the Forms &rarr; Settings page.', 'quform'); ?></p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="qfb-setting">
                                                <div class="qfb-setting-label"><label for="qfb_form_rtl"><?php esc_html_e('Enable RTL support', 'quform'); ?></label></div>
                                                <div class="qfb-setting-inner">
                                                    <div class="qfb-setting-input">
                                                        <select id="qfb_form_rtl">
                                                            <option value="global" <?php selected($builder->getFormConfigValue($form, 'rtl'), 'global'); ?>><?php esc_html_e('Global setting', 'quform'); ?></option>
                                                            <option value="enabled" <?php selected($builder->getFormConfigValue($form, 'rtl'), 'enabled'); ?>><?php esc_html_e('Enabled', 'quform'); ?></option>
                                                            <option value="disabled" <?php selected($builder->getFormConfigValue($form, 'rtl'), 'disabled'); ?>><?php esc_html_e('Disabled', 'quform'); ?></option>
                                                        </select>
                                                        <p class="qfb-description"><?php esc_html_e('Enable this option if the site language is RTL. The Global setting can be configured on the plugin Settings page.', 'quform'); ?></p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="qfb-setting">
                                                <div class="qfb-setting-label"><label><?php esc_html_e('Date & time format (JS)', 'quform'); ?></label></div>
                                                <div class="qfb-setting-inner">
                                                    <div class="qfb-setting-input">
                                                        <div class="qfb-settings-row qfb-settings-row-3">
                                                            <div class="qfb-settings-column">
                                                                <label for="qfb_form_date_format_js"><?php esc_html_e('Date', 'quform'); ?></label>
                                                                <input type="text" id="qfb_form_date_format_js" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'dateFormatJs')); ?>">
                                                            </div>
                                                            <div class="qfb-settings-column">
                                                                <label for="qfb_form_time_format_js"><?php esc_html_e('Time', 'quform'); ?></label>
                                                                <input type="text" id="qfb_form_time_format_js" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'timeFormatJs')); ?>">
                                                            </div>
                                                            <div class="qfb-settings-column">
                                                                <label for="qfb_form_date_time_format_js"><?php esc_html_e('DateTime', 'quform'); ?></label>
                                                                <input type="text" id="qfb_form_date_time_format_js" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'dateTimeFormatJs')); ?>">
                                                            </div>
                                                        </div>
                                                        <p class="qfb-description">
                                                            <?php
                                                                printf(
                                                                    /* translators: %1$s: open link tag, %2$s: close link tag */
                                                                    esc_html__('Sets the default format for dates and times when displayed in the form. See %1$sthis page%2$s for more information about custom formats. If empty, the formats will be inherited from the options at Forms &rarr; Settings &rarr; Global &rarr; Date & time format (JS).', 'quform'),
                                                                    '<a href="https://docs.telerik.com/kendo-ui/framework/globalization/dateformatting#custom" target="_blank">',
                                                                    '</a>'
                                                                );
                                                            ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="qfb-setting">
                                                <div class="qfb-setting-label"><label><?php esc_html_e('Date & time format (PHP)', 'quform'); ?></label></div>
                                                <div class="qfb-setting-inner">
                                                    <div class="qfb-setting-input">
                                                        <div class="qfb-settings-row qfb-settings-row-3">
                                                            <div class="qfb-settings-column">
                                                                <label for="qfb_form_date_format"><?php esc_html_e('Date', 'quform'); ?></label>
                                                                <input type="text" id="qfb_form_date_format" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'dateFormat')); ?>">
                                                            </div>
                                                            <div class="qfb-settings-column">
                                                                <label for="qfb_form_time_format"><?php esc_html_e('Time', 'quform'); ?></label>
                                                                <input type="text" id="qfb_form_time_format" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'timeFormat')); ?>">
                                                            </div>
                                                            <div class="qfb-settings-column">
                                                                <label for="qfb_form_date_time_format"><?php esc_html_e('DateTime', 'quform'); ?></label>
                                                                <input type="text" id="qfb_form_date_time_format" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'dateTimeFormat')); ?>">
                                                            </div>
                                                        </div>
                                                        <p class="qfb-description">
                                                            <?php
                                                                printf(
                                                                    /* translators: %1$s: open link tag, %2$s: close link tag */
                                                                    esc_html__('Sets the default format for dates and times when displayed in notification emails and when viewing entries. See %1$sthis page%2$s for more information about custom formats. If empty, the formats will be inherited from the options at Forms &rarr; Settings &rarr; Global &rarr; Date & time format (PHP).', 'quform'),
                                                                    '<a href="https://wordpress.org/documentation/article/customize-date-and-time-format/" target="_blank">',
                                                                    '</a>'
                                                                );
                                                            ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>

                                        <div class="qfb-settings qfb-form-translations">

                                            <div class="qfb-settings-heading"><i class="qfb-mdi qfb-mdi-translate"></i><?php esc_html_e('Translations', 'quform'); ?></div>

                                            <div class="qfb-setting">
                                                <div class="qfb-setting-label">
                                                    <label for="qfb_form_message_required"><?php esc_html_e('This field is required', 'quform'); ?></label>
                                                </div>
                                                <div class="qfb-setting-inner">
                                                    <div class="qfb-setting-input">
                                                        <input type="text" id="qfb_form_message_required" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'messageRequired')); ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="qfb-setting<?php echo (count($builder->getFormConfigValue($form, 'elements')) < 2) || ($builder->getFormConfigValue($form, 'pageProgressType') != 'numbers') ? ' qfb-hidden' : ''; ?>">
                                                <div class="qfb-setting-label">
                                                    <div class="qfb-tooltip-icon">
                                                        <div class="qfb-tooltip-content">
                                                            <div class="qfb-tooltip-title"><?php esc_html_e('Variables', 'quform'); ?></div>
                                                            <pre><?php
                                                                echo esc_html($builder->formatVariables(array(
                                                                    '%1$s' => __('The current page', 'quform'),
                                                                    '%2$s' => __('The total number of pages', 'quform')
                                                                )));
                                                            ?></pre>
                                                        </div>
                                                    </div>
                                                    <label for="qfb_form_page_progress_numbers_text"><?php esc_html_e('Page %1$s of %2$s', 'quform'); ?></label>
                                                </div>
                                                <div class="qfb-setting-inner">
                                                    <div class="qfb-setting-input">
                                                        <input type="text" id="qfb_form_page_progress_numbers_text" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'pageProgressNumbersText')); ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="qfb-setting<?php echo $builder->getFormConfigValue($form, 'oneEntryPerUser') ? '' : ' qfb-hidden'; ?>">
                                                <div class="qfb-setting-label">
                                                    <label for="qfb_form_only_one_submission_allowed"><?php esc_html_e('Only one submission is allowed.', 'quform'); ?></label>
                                                </div>
                                                <div class="qfb-setting-inner">
                                                    <div class="qfb-setting-input">
                                                        <input type="text" id="qfb_form_only_one_submission_allowed" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'onlyOneSubmissionAllowed')); ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="qfb-setting<?php echo $builder->getFormConfigValue($form, 'limitEntries') ? '' : ' qfb-hidden'; ?>">
                                                <div class="qfb-setting-label">
                                                    <label for="qfb_form_this_form_is_currently_closed"><?php esc_html_e('This form is currently closed for submissions.', 'quform'); ?></label>
                                                </div>
                                                <div class="qfb-setting-inner">
                                                    <div class="qfb-setting-input">
                                                        <input type="text" id="qfb_form_this_form_is_currently_closed" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'thisFormIsCurrentlyClosed')); ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="qfb-setting<?php echo $builder->getFormConfigValue($form, 'enableSchedule') && Quform::isNonEmptyString($builder->getFormConfigValue($form, 'scheduleStart')) ? '' : ' qfb-hidden'; ?>">
                                                <div class="qfb-setting-label">
                                                    <label for="qfb_form_form_is_not_yet_open_for_submissions"><?php esc_html_e('This form is not yet open for submissions.', 'quform'); ?></label>
                                                </div>
                                                <div class="qfb-setting-inner">
                                                    <div class="qfb-setting-input">
                                                        <input type="text" id="qfb_form_form_is_not_yet_open_for_submissions" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'formIsNotYetOpenForSubmissions')); ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="qfb-setting<?php echo $builder->getFormConfigValue($form, 'enableSchedule') && Quform::isNonEmptyString($builder->getFormConfigValue($form, 'scheduleEnd')) ? '' : ' qfb-hidden'; ?>">
                                                <div class="qfb-setting-label">
                                                    <label for="qfb_form_form_is_no_longer_open_for_submissions"><?php esc_html_e('This form is no longer open for submissions.', 'quform'); ?></label>
                                                </div>
                                                <div class="qfb-setting-inner">
                                                    <div class="qfb-setting-input">
                                                        <input type="text" id="qfb_form_form_is_no_longer_open_for_submissions" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'formIsNoLongerOpenForSubmissions')); ?>">
                                                    </div>
                                                </div>
                                            </div>

                                        </div>

                                    </div>

                                    <div class="qfb-tabs-panel">

                                        <div class="qfb-settings">

                                            <div class="qfb-setting">
                                                <div class="qfb-message-box qfb-message-box-info">
                                                    <div class="qfb-message-box-inner">
                                                        <p><?php esc_html_e('This section enables you to save form data to a custom database. This is not related to the saving of submitted entries, you can do both at the same time. You can use this functionality to save submitted form data to the table of another plugin for example.', 'quform'); ?></p>
                                                        <p><?php esc_html_e('This tool will not create the database table or fields for you - they should already exist. You can then choose to save a form value using the button below, just enter the name of the database field you would like to save to and choose the value from the dropdown menu.', 'quform'); ?></p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="qfb-settings-heading"><i class="qfb-icon qfb-icon-database"></i><?php esc_html_e('Custom database settings (MySQL)', 'quform'); ?></div>

                                            <div class="qfb-setting">
                                                <div class="qfb-setting-label">
                                                    <label for="qfb_form_db_enabled"><?php esc_html_e('Enabled', 'quform'); ?></label>
                                                </div>
                                                <div class="qfb-setting-inner">
                                                    <div class="qfb-setting-input">
                                                        <input type="checkbox" class="qfb-toggle" id="qfb_form_db_enabled" <?php checked($builder->getFormConfigValue($form, 'databaseEnabled')); ?>>
                                                        <label for="qfb_form_db_enabled"></label>
                                                        <p class="qfb-description"><?php esc_html_e('Enable this option to save to a custom database table.', 'quform'); ?></p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="qfb-setting">
                                                <div class="qfb-setting-label">
                                                    <label for="qfb_form_db_wordpress"><?php esc_html_e('Use WordPress database', 'quform'); ?></label>
                                                </div>
                                                <div class="qfb-setting-inner">
                                                    <div class="qfb-setting-input">
                                                        <input type="checkbox" class="qfb-toggle" id="qfb_form_db_wordpress" <?php checked($builder->getFormConfigValue($form, 'databaseWordpress')); ?>>
                                                        <label for="qfb_form_db_wordpress"></label>
                                                        <p class="qfb-description"><?php esc_html_e('If enabled, the data will be inserted into a table you specify below, inside the WordPress database. Disable to specify custom database credentials.', 'quform'); ?></p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="qfb-setting<?php echo $builder->getFormConfigValue($form, 'databaseWordpress') ? ' qfb-hidden' : ''; ?>">
                                                <div class="qfb-setting-label">
                                                    <label for="qfb_form_db_host"><?php esc_html_e('Host', 'quform'); ?></label>
                                                </div>
                                                <div class="qfb-setting-inner">
                                                    <div class="qfb-setting-input">
                                                        <input type="text" id="qfb_form_db_host" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'databaseHost')); ?>">
                                                        <p class="qfb-description"><?php esc_html_e('The host of the MySQL server, usually localhost.', 'quform'); ?></p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="qfb-setting<?php echo $builder->getFormConfigValue($form, 'databaseWordpress') ? ' qfb-hidden' : ''; ?>">
                                                <div class="qfb-setting-label">
                                                    <label for="qfb_form_db_username"><?php esc_html_e('Username', 'quform'); ?></label>
                                                </div>
                                                <div class="qfb-setting-inner">
                                                    <div class="qfb-setting-input">
                                                        <input type="text" id="qfb_form_db_username" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'databaseUsername')); ?>">
                                                        <p class="qfb-description"><?php esc_html_e('The user must have permission to insert data to the database.', 'quform'); ?></p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="qfb-setting<?php echo $builder->getFormConfigValue($form, 'databaseWordpress') ? ' qfb-hidden' : ''; ?>">
                                                <div class="qfb-setting-label">
                                                    <label><?php esc_html_e('Password', 'quform'); ?></label>
                                                </div>
                                                <div class="qfb-setting-inner">
                                                    <div class="qfb-setting-input">
                                                        <?php if ($builder->getFormConfigValue($form, 'databasePassword')) : ?>
                                                            <span class="qfb-floated-text-beside-button" id="qfb-form-db-password-message"><?php esc_html_e('A password is saved but hidden for security reasons.', 'quform'); ?></span><div class="qfb-button" id="qfb-form-db-change-password"><?php esc_html_e('Change password', 'quform'); ?></div>
                                                        <?php else : ?>
                                                            <?php echo $builder->getDbPasswordHtml(); ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="qfb-setting<?php echo $builder->getFormConfigValue($form, 'databaseWordpress') ? ' qfb-hidden' : ''; ?>">
                                                <div class="qfb-setting-label">
                                                    <label for="qfb_form_db_database"><?php esc_html_e('Database', 'quform'); ?></label>
                                                </div>
                                                <div class="qfb-setting-inner">
                                                    <div class="qfb-setting-input">
                                                        <input type="text" id="qfb_form_db_database" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'databaseDatabase')); ?>">
                                                        <p class="qfb-description"><?php esc_html_e('The name of the database.', 'quform'); ?></p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="qfb-setting">
                                                <div class="qfb-setting-label">
                                                    <label for="qfb_form_db_table"><?php esc_html_e('Table', 'quform'); ?></label>
                                                </div>
                                                <div class="qfb-setting-inner">
                                                    <div class="qfb-setting-input">
                                                        <input type="text" id="qfb_form_db_table" value="<?php echo Quform::escape($builder->getFormConfigValue($form, 'databaseTable')); ?>">
                                                        <p class="qfb-description"><?php esc_html_e('The name of the database table.', 'quform'); ?></p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="qfb-settings-heading"><i class="qfb-icon qfb-icon-floppy-o"></i><?php esc_html_e('What to save', 'quform'); ?></div>

                                            <div class="qfb-setting">
                                                <div class="qfb-setting-label">
                                                    <div id="qfb-form-db-add-column" class="qfb-button-green"><i class="qfb-mdi qfb-mdi-add_circle"></i><?php esc_html_e('Add column', 'quform'); ?></div>
                                                </div>
                                                <div class="qfb-setting-inner">
                                                    <div class="qfb-setting-input">
                                                        <div id="qfb-form-db-columns">
                                                            <?php
                                                                foreach ($builder->getFormConfigValue($form, 'databaseColumns') as $column) {
                                                                    echo $builder->getDbColumnHtml($column);
                                                                }
                                                            ?>
                                                        </div>
                                                        <div id="qfb-form-db-columns-empty" class="qfb-message-box qfb-message-box-info<?php if (count($builder->getFormConfigValue($form, 'databaseColumns'))) echo ' qfb-hidden'; ?>"><div class="qfb-message-box-inner"><?php esc_html_e('You are not currently saving any submitted form values.', 'quform'); ?></div></div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if ( ! is_rtl()) : ?>
                        <div id="qfb-preview-panel"><iframe id="qfb-preview-frame" src="<?php echo esc_url(admin_url('admin.php?page=quform.preview')); ?>"></iframe></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php include QUFORM_TEMPLATE_PATH . '/admin/element-settings.php'; ?>
            <?php include QUFORM_TEMPLATE_PATH . '/admin/confirmation-settings.php'; ?>
            <?php include QUFORM_TEMPLATE_PATH . '/admin/notification-settings.php'; ?>
            <div id="qfb-insert-variable" class="qfb-insert-variable-menu">
                <div class="qfb-insert-variable-heading"><?php esc_html_e('Submitted Form Value', 'quform'); ?></div>
                <div id="qfb-insert-variable-element"></div>
                <?php
                foreach ($builder->getVariables() as $variable) {
                    echo '<div class="qfb-insert-variable-heading">' . esc_html($variable['heading']) . '</div>';
                    foreach ($variable['variables'] as $tag => $description) {
                        echo '<div class="qfb-variable" data-tag="' .  esc_attr($tag) . '">' . esc_html($description) . '</div>';
                    }
                }
                ?>
            </div>
            <div id="qfb-insert-variable-pre-process" class="qfb-insert-variable-menu">
                <?php
                foreach ($builder->getPreProcessVariables() as $variable) {
                    echo '<div class="qfb-insert-variable-heading">' . esc_html($variable['heading']) . '</div>';
                    foreach ($variable['variables'] as $tag => $description) {
                        echo '<div class="qfb-variable" data-tag="' .  esc_attr($tag) . '">' . esc_html($description) . '</div>';
                    }
                }
                ?>
            </div>
            <div id="qfb-icon-selector-popup" class="qfb-popup">
                <div class="qfb-popup-content">

                    <div class="qfb-icon-selector">

                        <div class="qfb-icon-selector-search"><input type="text" placeholder="<?php esc_attr_e('Search', 'quform'); ?>"></div>

                        <div class="qfb-icon-selector-icons qfb-cf">
                            <?php foreach ($builder->getQuformIcons() as $icon) : ?>
                                <div class="qfb-icon-selector-icon" data-quform-icon="<?php echo esc_attr($icon); ?>" title="<?php echo esc_attr(ucfirst(str_replace(array('-', '_'), ' ', preg_replace('/^qicon\-/', '', $icon)))); ?>">
                                    <i class="<?php echo esc_attr($icon); ?>"></i>
                                </div>
                            <?php endforeach; ?>
                            <?php foreach ($builder->getFontAwesomeIcons() as $icon) : ?>
                                <div class="qfb-icon-selector-icon" data-quform-icon="fa <?php echo esc_attr($icon); ?>" title="<?php echo esc_attr(ucfirst(str_replace('-', ' ', preg_replace('/^fa\-/', '', $icon)))); ?>">
                                    <i class="<?php echo esc_attr('qfb-icon qfb-icon-' . preg_replace('/^fa-/', '', $icon)); ?>"></i>
                                </div>
                            <?php endforeach; ?>
                        </div>

                    </div>

                </div>

                <div class="qfb-popup-buttons">
                    <div title="<?php esc_attr_e('Close', 'quform'); ?>" class="qfb-popup-close-button"><i class="qfb-mdi qfb-mdi-close"></i></div>
                </div>

                <div class="qfb-popup-overlay"></div>
            </div>

            <div id="qfb-add-to-website-popup" class="qfb-popup">

                <div class="qfb-popup-content">

                    <div class="qfb-settings">

                        <div class="qfb-settings-heading"><i class="qfb-icon qfb-icon-code"></i> <?php esc_html_e('Shortcode', 'quform'); ?></div>

                        <p class="qfb-description qfb-below-heading"><?php esc_html_e('To add this form into a post or page, copy and paste one of the shortcodes below into the post or page content.', 'quform'); ?></p>

                        <div class="qfb-setting">
                            <div class="qfb-setting-label"><label for="qfb-add-shortcode-form"><?php esc_html_e('Standard form', 'quform'); ?></label></div>
                            <div class="qfb-setting-inner">
                                <div class="qfb-setting-input">
                                    <input type="text" id="qfb-add-shortcode-form" class="qfb-code-input" readonly value="<?php echo Quform::escape(sprintf('[quform id="%s" name="%s"]', $builder->getFormConfigValue($form, 'id'), $builder->getFormConfigValue($form, 'name'))); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="qfb-setting">
                            <div class="qfb-setting-label"><label for="qfb-add-shortcode-popup"><?php esc_html_e('Popup form', 'quform'); ?></label></div>
                            <div class="qfb-setting-inner">
                                <div class="qfb-setting-input">
                                    <input type="text" id="qfb-add-shortcode-popup" class="qfb-code-input" readonly value="<?php echo Quform::escape(sprintf('[quform_popup id="%s" name="%s"]%s[/quform_popup]', $builder->getFormConfigValue($form, 'id'), $builder->getFormConfigValue($form, 'name'), __('Click me', 'quform'))); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="qfb-settings-heading"><i class="qfb-icon qfb-icon-bars"></i> <?php esc_html_e('Widget', 'quform'); ?></div>

                        <div class="qfb-setting">
                            <p class="qfb-description qfb-below-heading"><?php esc_html_e('To add this form as a widget, go to the Appearance &rarr; Widgets on the WordPress menu. Find the widget with the title "Quform" (or "Quform Popup" for a popup form) and simply drag it to a widget enabled area, then select this form in the widget settings.', 'quform'); ?></p>
                        </div>

                        <div class="qfb-settings-heading"><i class="qfb-icon qfb-icon-file-code-o"></i> <?php esc_html_e('PHP', 'quform'); ?></div>

                        <div class="qfb-setting">

                            <p class="qfb-description qfb-below-heading"><?php esc_html_e('To add this form to a PHP file, use one of the PHP snippets below.', 'quform'); ?></p>

                            <div class="qfb-setting">
                                <div class="qfb-setting-label"><label for="qfb-add-php-form"><?php esc_html_e('Standard form', 'quform'); ?></label></div>
                                <div class="qfb-setting-inner">
                                    <div class="qfb-setting-input">
                                        <input type="text" id="qfb-add-php-form" class="qfb-code-input" readonly value="<?php echo Quform::escape(sprintf('<?php echo do_shortcode(\'[quform id="%s" name="%s"]\'); ?>', $builder->getFormConfigValue($form, 'id'), $builder->getFormConfigValue($form, 'name'))); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="qfb-setting">
                                <div class="qfb-setting-label"><label for="qfb-add-php-popup"><?php esc_html_e('Popup form', 'quform'); ?></label></div>
                                <div class="qfb-setting-inner">
                                    <div class="qfb-setting-input">
                                        <input type="text" id="qfb-add-php-popup" class="qfb-code-input" readonly value="<?php echo Quform::escape(sprintf('<?php echo do_shortcode(\'[quform_popup id="%s" name="%s"]%s[/quform_popup]\'); ?>', $builder->getFormConfigValue($form, 'id'), $builder->getFormConfigValue($form, 'name'), __('Click me', 'quform'))); ?>">
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>

                <div class="qfb-popup-buttons">
                    <div title="<?php esc_attr_e('Close', 'quform'); ?>" class="qfb-popup-close-button"><i class="qfb-mdi qfb-mdi-close"></i></div>
                </div>

                <div class="qfb-popup-overlay"></div>

            </div>

            <div id="qfb-bulk-options" class="qfb-popup">
                <div class="qfb-popup-content">

                    <div class="qfb-settings-heading"><i class="qfb-icon qfb-icon-archive"></i> <?php esc_html_e('Add bulk options', 'quform'); ?></div>

                    <p class="qfb-description"><?php esc_html_e('Click a category on the left hand side to insert predefined options. You can edit the options on the right hand side or enter your own options, one per line.', 'quform'); ?></p>

                    <div class="qfb-cf bulk-options-wrap">
                        <div class="qfb-bulk-options-right">
                            <textarea id="qfb_bulk_options_textarea"></textarea>
                        </div>
                        <div class="qfb-bulk-options-left">
                            <ul>
                                <li><div data-key="existing" class="qfb-button qfb-add-bulk-option-button"><?php esc_html_e('Existing Options', 'quform'); ?></div></li>
                                <?php foreach ($builder->getBulkOptions() as $key => $data) : ?>
                                    <li><div data-key="<?php echo esc_attr($key); ?>" class="qfb-button qfb-add-bulk-option-button"><?php echo esc_html($data['name']); ?></div></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_bulk_options_clear"><?php esc_html_e('Overwrite existing options', 'quform'); ?></label>
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Removes any existing options before adding', 'quform'); ?></div></div>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="checkbox" id="qfb_bulk_options_clear" class="qfb-toggle">
                                <label for="qfb_bulk_options_clear"></label>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-bulk-options-buttons-wrap qfb-cf">
                        <div id="qfb-insert-bulk-options" class="qfb-button qfb-button-blue"><i class="qfb-mdi qfb-mdi-done"></i> <?php esc_html_e('Add options', 'quform'); ?></div>
                        <div id="qfb-close-bulk-options" class="qfb-button"><?php esc_html_e('Cancel', 'quform'); ?></div>
                    </div>

                </div>

                <div class="qfb-popup-overlay"></div>
            </div>

            <div id="qfb-option-settings" class="qfb-popup">
                <div class="qfb-popup-content">

                    <div class="qfb-settings">

                        <div class="qfb-settings-heading"><i class="qfb-mdi qfb-mdi-settings"></i> <?php esc_html_e('Option settings', 'quform'); ?></div>

                        <div class="qfb-setting">
                            <div class="qfb-setting-label">
                                <label for="qfb_option_label"><?php esc_html_e('Label', 'quform'); ?></label>
                            </div>
                            <div class="qfb-setting-inner">
                                <div class="qfb-setting-input">
                                    <input type="text" id="qfb_option_label">
                                </div>
                            </div>
                        </div>

                        <div class="qfb-setting">
                            <div class="qfb-setting-label">
                                <label for="qfb_option_value"><?php esc_html_e('Value', 'quform'); ?></label>
                            </div>
                            <div class="qfb-setting-inner">
                                <div class="qfb-setting-input">
                                    <input type="text" id="qfb_option_value">
                                </div>
                            </div>
                        </div>

                        <div class="qfb-setting">
                            <div class="qfb-setting-label">
                                <label><?php esc_html_e('Image', 'quform'); ?></label>
                            </div>
                            <div class="qfb-setting-inner">
                                <div class="qfb-setting-input">
                                    <input type="text" id="qfb_option_image" class="qfb-width-350">
                                    <span id="qfb_option_image_browse" class="qfb-button-blue qfb-browse-button"><i class="qfb-mdi qfb-mdi-panorama"></i><?php esc_html_e('Browse', 'quform'); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="qfb-setting">
                            <div class="qfb-setting-label">
                                <label><?php esc_html_e('Image (when selected)', 'quform'); ?></label>
                            </div>
                            <div class="qfb-setting-inner">
                                <div class="qfb-setting-input">
                                    <input type="text" id="qfb_option_image_selected" class="qfb-width-350">
                                    <span id="qfb_option_image_selected_browse" class="qfb-button-blue qfb-browse-button"><i class="qfb-mdi qfb-mdi-panorama"></i><?php esc_html_e('Browse', 'quform'); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="qfb-setting">
                            <div class="qfb-setting-label">
                                <label for="qfb_option_width"><?php esc_html_e('Width', 'quform'); ?></label>
                            </div>
                            <div class="qfb-setting-inner">
                                <div class="qfb-setting-input">
                                    <input type="text" id="qfb_option_width">
                                </div>
                            </div>
                        </div>

                        <div class="qfb-setting">
                            <div class="qfb-setting-label">
                                <label for="qfb_option_height"><?php esc_html_e('Height', 'quform'); ?></label>
                            </div>
                            <div class="qfb-setting-inner">
                                <div class="qfb-setting-input">
                                    <input type="text" id="qfb_option_height">
                                </div>
                            </div>
                        </div>

                        <div class="qfb-setting">
                            <div class="qfb-setting-label">
                                <label><?php esc_html_e('Icon', 'quform'); ?></label>
                            </div>
                            <div class="qfb-setting-inner">
                                <div class="qfb-setting-input">
                                    <?php echo $builder->getSelectIconHtml('qfb_option_icon'); ?>
                                </div>
                            </div>
                        </div>

                        <div class="qfb-setting">
                            <div class="qfb-setting-label">
                                <label><?php esc_html_e('Icon (when selected)', 'quform'); ?></label>
                            </div>
                            <div class="qfb-setting-inner">
                                <div class="qfb-setting-input">
                                    <?php echo $builder->getSelectIconHtml('qfb_option_icon_selected'); ?>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="qfb-popup-buttons">
                    <div title="<?php esc_attr_e('Save', 'quform'); ?>" class="qfb-popup-save-button"><i class="qfb-mdi qfb-mdi-check"></i></div>
                    <div title="<?php esc_attr_e('Close', 'quform'); ?>" class="qfb-popup-close-button"><i class="qfb-mdi qfb-mdi-close"></i></div>
                </div>

                <div class="qfb-popup-overlay"></div>
            </div>
        </div>
    </form>
    <script>
        //<![CDATA[
        jQuery(function () {
            quform.builder.init(<?php echo wp_json_encode($form); ?>);
        });
        //]]>
    </script>
</div>
