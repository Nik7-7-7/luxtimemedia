<?php
if (!defined('ABSPATH')) exit;
/* @var Quform_Builder $builder */
/* @var Quform_Options $options */
?><div id="qfb-element-settings" class="qfb-popup">
    <div id="qfb-element-settings-tabs" class="qfb-popup-content">
        <div class="qfb-settings-heading">
            <span class="qfb-element-settings-icon"><i class="qfb-mdi qfb-mdi-settings"></i></span><span id="qfb-element-settings-title"><?php esc_html_e('Element settings', 'quform'); ?></span><span id="qfb-element-settings-element-icon"></span><span id="qfb-element-settings-element-label"></span>
        </div>

        <ul id="qfb-element-settings-tabs-nav" class="qfb-tabs-nav qfb-cf">
            <li><a><?php esc_html_e('Basic', 'quform'); ?></a></li>
            <li><a><?php esc_html_e('Styles', 'quform'); ?></a></li>
            <li><a><?php esc_html_e('Labels', 'quform'); ?></a></li>
            <li><a><?php esc_html_e('Logic', 'quform'); ?></a></li>
            <li><a><?php esc_html_e('Data', 'quform'); ?></a></li>
            <li><a><?php esc_html_e('Advanced', 'quform'); ?></a></li>
            <li id="qfb-element-settings-tab-translations"><a><?php esc_html_e('Translations', 'quform'); ?></a></li>
        </ul>

        <div class="qfb-tabs-panel">
            <div class="qfb-element-settings-inner">

                <div class="qfb-settings">

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_label"><?php esc_html_e('Label', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_label">
                                <p id="qfb-button-label-hint" class="qfb-description"><?php esc_html_e('Hint: the button text can be changed at Settings - Style - Buttons.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon">
                                <div class="qfb-tooltip-content">
                                    <?php esc_html_e('These are the choices that the user will be able to choose from.', 'quform'); ?>
                                    <br><br>
                                    <?php
                                        printf(
                                            /* translators : %1$s open span tag, %2$s close span tag */
                                            esc_html__('The %1$sCustomize values%2$s setting allows you to have a different value being submitted than the value that is displayed to the user.', 'quform'),
                                            '<span class="qfb-bold">',
                                            '</span>'
                                        );
                                    ?>
                                </div>
                            </div>
                            <label><?php esc_html_e('Options', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner qfb-options">
                            <div class="qfb-setting-input">
                                <div class="qfb-options-empty qfb-message-box qfb-message-box-info"><div class="qfb-message-box-inner"><?php esc_html_e('There are no options, click "Add option" to add one.', 'quform'); ?></div></div>
                                <div class="qfb-options-inner">
                                    <div class="qfb-options-heading qfb-cf">
                                        <div class="qfb-options-heading-left">
                                            <div class="qfb-options-heading-left-inner">
                                                <div class="qfb-settings-row qfb-settings-row-2">
                                                    <div class="qfb-settings-column"><span class="qfb-options-heading-option"><?php esc_html_e('Label', 'quform'); ?></span></div>
                                                    <div class="qfb-settings-column"><span class="qfb-options-heading-value"><?php esc_html_e('Value', 'quform'); ?></span></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="qfb-options" class="qfb-options-list"></div>
                                </div>
                                <div class="qfb-customise-values-wrap">
                                    <label><?php esc_html_e('Customize values', 'quform'); ?><span class="qfb-tooltip"><i class="qfb-icon qfb-icon-question"></i><span class="qfb-tooltip-content"><?php esc_html_e('If enabled, the submitted option value can be different from the option label.', 'quform'); ?></span></span></label>
                                    <input type="checkbox" id="qfb_customise_values" class="qfb-customise-values qfb-toggle">
                                    <label for="qfb_customise_values"></label>
                                </div>
                                <div class="qfb-options-right-buttons">
                                    <div id="qfb-add-option-button" class="qfb-button-green"><i class="qfb-mdi qfb-mdi-add_circle"></i> <?php esc_html_e('Add option', 'quform'); ?></div>
                                    <div id="qfb-add-optgroup-button" class="qfb-button"><i class="qfb-icon qfb-icon-list-alt"></i>  <?php esc_html_e('Add optgroup', 'quform'); ?></div>
                                    <div id="qfb-add-bulk-options-button" class="qfb-button"><i class="qfb-icon qfb-icon-archive"></i>  <?php esc_html_e('Add bulk options', 'quform'); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Adds an option to the top of the list to let the user choose no value. The text can be changed on the Translations tab.', 'quform'); ?></div></div>
                            <label for="qfb_none_option"><?php esc_html_e('"Please select" option', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="checkbox" id="qfb_none_option" class="qfb-toggle">
                                <label for="qfb_none_option"></label>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('The title will appear at the top of the group.', 'quform'); ?></div></div>
                            <label for="qfb_group_title"><?php esc_html_e('Title', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <div class="qfb-cf">
                                    <div class="qfb-title-left">
                                        <div class="qfb-title-left-inner">
                                            <input type="text" id="qfb_group_title">
                                        </div>
                                    </div>
                                    <div class="qfb-title-right">
                                        <?php echo $builder->getTitleTagSelectHtml('qfb_group_title_tag'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('The description will appear below the title at the top of the group.', 'quform'); ?></div></div>
                            <label for="qfb_group_description"><?php esc_html_e('Description', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <textarea id="qfb_group_description"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('The title will appear at the top of the page.', 'quform'); ?></div></div>
                            <label for="qfb_page_title"><?php esc_html_e('Title', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <div class="qfb-cf">
                                    <div class="qfb-title-left">
                                        <div class="qfb-title-left-inner">
                                            <input type="text" id="qfb_page_title">
                                        </div>
                                    </div>
                                    <div class="qfb-title-right">
                                        <?php echo $builder->getTitleTagSelectHtml('qfb_page_title_tag'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('The description will appear below the title at the top of the page.', 'quform'); ?></div></div>
                            <label for="qfb_page_description"><?php esc_html_e('Description', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <textarea id="qfb_page_description"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label><?php esc_html_e('Description', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <div id="qfb-description-tabs">
                                    <ul class="qfb-tabs-nav qfb-sub-tabs-nav qfb-cf">
                                        <li><a><?php esc_html_e('Below field', 'quform'); ?></a></li>
                                        <li><a><?php esc_html_e('Above field', 'quform'); ?></a></li>
                                    </ul>
                                    <div class="qfb-tabs-panel">
                                        <textarea id="qfb_description"></textarea>
                                    </div>
                                    <div class="qfb-tabs-panel">
                                        <textarea id="qfb_description_above"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('If enabled, the user must fill out this field.', 'quform'); ?></div></div>
                            <label for="qfb_required"><?php esc_html_e('Required', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="checkbox" id="qfb_required" class="qfb-toggle">
                                <label for="qfb_required"></label>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_recaptcha_provider"><?php esc_html_e('Provider', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <select id="qfb_recaptcha_provider">
                                    <option value="recaptcha"><?php esc_html_e('reCAPTCHA', 'quform'); ?></option>
                                    <option value="hcaptcha"><?php esc_html_e('hCaptcha', 'quform'); ?></option>
                                    <option value="turnstile"><?php esc_html_e('Cloudflare Turnstile', 'quform'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_recaptcha_version"><?php esc_html_e('Version', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <select id="qfb_recaptcha_version">
                                    <option value="v2"><?php echo esc_html_x('v2', 'reCAPTCHA v2', 'quform'); ?></option>
                                    <option value="v3"><?php echo esc_html_x('v3', 'reCAPTCHA v3', 'quform'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_recaptcha_size"><?php esc_html_e('Size', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <select id="qfb_recaptcha_size">
                                    <option value="normal"><?php echo esc_html_x('Normal', 'reCAPTCHA size', 'quform'); ?></option>
                                    <option value="compact"><?php echo esc_html_x('Compact', 'reCAPTCHA size', 'quform'); ?></option>
                                    <option value="invisible"><?php echo esc_html_x('Invisible', 'reCAPTCHA size', 'quform'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_recaptcha_type"><?php esc_html_e('Type', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <select id="qfb_recaptcha_type">
                                    <option value="image"><?php echo esc_html_x('Image', 'reCAPTCHA type', 'quform'); ?></option>
                                    <option value="audio"><?php echo esc_html_x('Audio', 'reCAPTCHA type', 'quform'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_recaptcha_theme"><?php esc_html_e('Theme', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <select id="qfb_recaptcha_theme">
                                    <option value="light"><?php echo esc_html_x('Light', 'reCAPTCHA theme', 'quform'); ?></option>
                                    <option value="dark"><?php echo esc_html_x('Dark', 'reCAPTCHA theme', 'quform'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_recaptcha_badge"><?php esc_html_e('Badge', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <select id="qfb_recaptcha_badge">
                                    <option value="bottomright"><?php esc_html_e('Bottom right', 'quform'); ?></option>
                                    <option value="bottomleft"><?php esc_html_e('Bottom left', 'quform'); ?></option>
                                    <option value="inline"><?php esc_html_e('Inline', 'quform'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_recaptcha_lang"><?php esc_html_e('Language', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <select id="qfb_recaptcha_lang">
                                    <?php foreach ($builder->getRecaptchaLanguages() as $key => $lang) : ?>
                                        <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($lang); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_hcaptcha_lang"><?php esc_html_e('Language', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <select id="qfb_hcaptcha_lang">
                                    <?php foreach ($builder->getHcaptchaLanguages() as $key => $lang) : ?>
                                        <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($lang); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_turnstile_lang"><?php esc_html_e('Language', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <select id="qfb_turnstile_lang">
                                    <?php foreach ($builder->getTurnstileLanguages() as $key => $lang) : ?>
                                        <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($lang); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_recaptcha_threshold"><?php esc_html_e('Threshold', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_recaptcha_threshold">
                                <p class="qfb-description"><?php esc_html_e('Submissions with a score lower than this will be rejected (1.0 is very likely a good interaction, 0.0 is very likely a bot).', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_html_content"><?php esc_html_e('HTML', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <div class="qfb-textarea-variable qfb-editor-variable">
                                    <?php echo $builder->getInsertVariableHtml('qfb_html_content', true); ?>
                                    <?php wp_editor('', 'qfb_html_content', array('editor_height' => 300)); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Automatically adds line breaks, you might want to disable this when using HTML to prevent unwanted extra spacing.', 'quform'); ?></div></div>
                            <label for="qfb_html_auto_format"><?php esc_html_e('Auto formatting', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="checkbox" id="qfb_html_auto_format" class="qfb-toggle">
                                <label for="qfb_html_auto_format"></label>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_submit_button_type"><?php esc_html_e('Submit button type', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <select id="qfb_submit_button_type">
                                    <option value="inherit"><?php esc_html_e('Inherit', 'quform'); ?></option>
                                    <option value="default"><?php esc_html_e('Default button', 'quform'); ?></option>
                                    <option value="image"><?php esc_html_e('Custom image', 'quform'); ?></option>
                                    <option value="html"><?php esc_html_e('Custom HTML', 'quform'); ?></option>
                                </select>
                                <p class="qfb-description"><?php esc_html_e('"Inherit" will use the settings at Settings - Style - Buttons.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_submit_button_text"><?php esc_html_e('Submit button text', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_submit_button_text">
                                <p class="qfb-description"><?php esc_html_e('Change the default text of the button.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label><?php esc_html_e('Submit button icon', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <?php echo $builder->getSelectIconHtml('qfb_submit_button_icon'); ?>
                                <p class="qfb-description"><?php esc_html_e('Choose an icon for the button.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_submit_button_icon_position"><?php esc_html_e('Submit button icon position', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <?php echo $builder->getIconPositionSelectHtml('qfb_submit_button_icon_position'); ?>
                                <p class="qfb-description"><?php esc_html_e('Choose the icon position relative to the button text.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_submit_button_image"><?php esc_html_e('Submit button image URL', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_submit_button_image" class="qfb-width-350">
                                <span id="qfb_submit_button_image_browse" class="qfb-button-blue qfb-browse-button"><i class="qfb-mdi qfb-mdi-panorama"></i><?php esc_html_e('Browse', 'quform'); ?></span>
                                <p class="qfb-description"><?php esc_html_e('Enter the URL to an image or upload one.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_submit_button_html"><?php esc_html_e('Submit button HTML', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <textarea id="qfb_submit_button_html"></textarea>
                                <p class="qfb-description"><?php esc_html_e('Enter custom HTML for the button.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_next_button_type"><?php esc_html_e('Next button type', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <select id="qfb_next_button_type">
                                    <option value="inherit"><?php esc_html_e('Inherit', 'quform'); ?></option>
                                    <option value="default"><?php esc_html_e('Default button', 'quform'); ?></option>
                                    <option value="image"><?php esc_html_e('Custom image', 'quform'); ?></option>
                                    <option value="html"><?php esc_html_e('Custom HTML', 'quform'); ?></option>
                                </select>
                                <p class="qfb-description"><?php esc_html_e('"Inherit" will use the settings at Settings - Style - Buttons.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_next_button_text"><?php esc_html_e('Next button text', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_next_button_text">
                                <p class="qfb-description"><?php esc_html_e('Change the default text of the button.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label><?php esc_html_e('Next button icon', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <?php echo $builder->getSelectIconHtml('qfb_next_button_icon'); ?>
                                <p class="qfb-description"><?php esc_html_e('Choose an icon for the button.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_next_button_icon_position"><?php esc_html_e('Next button icon position', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <?php echo $builder->getIconPositionSelectHtml('qfb_next_button_icon_position'); ?>
                                <p class="qfb-description"><?php esc_html_e('Choose the icon position relative to the button text.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_next_button_image"><?php esc_html_e('Next button image URL', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_next_button_image" class="qfb-width-350">
                                <span id="qfb_next_button_image_browse" class="qfb-button-blue qfb-browse-button"><i class="qfb-mdi qfb-mdi-panorama"></i><?php esc_html_e('Browse', 'quform'); ?></span>
                                <p class="qfb-description"><?php esc_html_e('Enter the URL to an image or upload one.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_next_button_html"><?php esc_html_e('Next button HTML', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <textarea id="qfb_next_button_html"></textarea>
                                <p class="qfb-description"><?php esc_html_e('Enter custom HTML for the button.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_back_button_type"><?php esc_html_e('Back button type', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <select id="qfb_back_button_type">
                                    <option value="inherit"><?php esc_html_e('Inherit', 'quform'); ?></option>
                                    <option value="default"><?php esc_html_e('Default button', 'quform'); ?></option>
                                    <option value="image"><?php esc_html_e('Custom image', 'quform'); ?></option>
                                    <option value="html"><?php esc_html_e('Custom HTML', 'quform'); ?></option>
                                </select>
                                <p class="qfb-description"><?php esc_html_e('"Inherit" will use the settings at Settings &rarr; Style &rarr; Buttons.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_back_button_text"><?php esc_html_e('Back button text', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_back_button_text">
                                <p class="qfb-description"><?php esc_html_e('Change the default text of the button.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label><?php esc_html_e('Back button icon', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <?php echo $builder->getSelectIconHtml('qfb_back_button_icon'); ?>
                                <p class="qfb-description"><?php esc_html_e('Choose an icon for the back button.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_back_button_icon_position"><?php esc_html_e('Back button icon position', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <?php echo $builder->getIconPositionSelectHtml('qfb_back_button_icon_position'); ?>
                                <p class="qfb-description"><?php esc_html_e('Choose the icon position relative to the button text.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_back_button_image"><?php esc_html_e('Back button image URL', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_back_button_image" class="qfb-width-350">
                                <span id="qfb_back_button_image_browse" class="qfb-button-blue qfb-browse-button"><i class="qfb-mdi qfb-mdi-panorama"></i><?php esc_html_e('Browse', 'quform'); ?></span>
                                <p class="qfb-description"><?php esc_html_e('Enter the URL to an image or upload one.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_back_button_html"><?php esc_html_e('Back button HTML', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <textarea id="qfb_back_button_html"></textarea>
                                <p class="qfb-description"><?php esc_html_e('Enter custom HTML for the button.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label><?php esc_html_e('Parts', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <div id="qfb-name-tabs">
                                    <ul class="qfb-tabs-nav qfb-cf">
                                        <li><a><?php esc_html_e('Prefix', 'quform'); ?></a></li>
                                        <li><a><?php esc_html_e('First', 'quform'); ?></a></li>
                                        <li><a><?php esc_html_e('Middle', 'quform'); ?></a></li>
                                        <li><a><?php esc_html_e('Last', 'quform'); ?></a></li>
                                        <li><a><?php esc_html_e('Suffix', 'quform'); ?></a></li>
                                    </ul>
                                    <div class="qfb-tabs-panel">
                                        <div class="qfb-sub-setting">
                                            <div class="qfb-sub-setting-label">
                                                <label for="qfb_name_prefix_enabled"><?php esc_html_e('Enabled', 'quform'); ?></label>
                                            </div>
                                            <div class="qfb-sub-setting-inner">
                                                <div class="qfb-sub-setting-input">
                                                    <input type="checkbox" id="qfb_name_prefix_enabled" class="qfb-toggle">
                                                    <label for="qfb_name_prefix_enabled"></label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="qfb-sub-setting">
                                            <div class="qfb-sub-setting-label">
                                                <label for="qfb_name_prefix_required"><?php esc_html_e('Required', 'quform'); ?></label>
                                            </div>
                                            <div class="qfb-sub-setting-inner">
                                                <div class="qfb-sub-setting-input">
                                                    <input type="checkbox" id="qfb_name_prefix_required" class="qfb-toggle">
                                                    <label for="qfb_name_prefix_required"></label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="qfb-sub-setting">
                                            <div class="qfb-sub-setting-label">
                                                <label><?php esc_html_e('Options', 'quform'); ?></label>
                                            </div>
                                            <div class="qfb-sub-setting-inner qfb-options">
                                                <div class="qfb-sub-setting-input">
                                                    <div class="qfb-options-empty qfb-message-box qfb-message-box-info"><div class="qfb-message-box-inner"><?php esc_html_e('There are no options, click "Add option" to add one.', 'quform'); ?></div></div>
                                                    <div class="qfb-options-inner">
                                                        <div class="qfb-options-heading qfb-cf">
                                                            <div class="qfb-options-heading-left">
                                                                <div class="qfb-options-heading-left-inner">
                                                                    <div class="qfb-settings-row qfb-settings-row-2">
                                                                        <div class="qfb-settings-column"><span class="qfb-options-heading-option"><?php esc_html_e('Label', 'quform'); ?></span></div>
                                                                        <div class="qfb-settings-column"><span class="qfb-options-heading-value"><?php esc_html_e('Value', 'quform'); ?></span></div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div id="qfb-name-prefix-options" class="qfb-options-list"></div>
                                                    </div>
                                                    <div class="qfb-customise-values-wrap">
                                                        <label><?php esc_html_e('Customize values', 'quform'); ?><span class="qfb-tooltip"><i class="qfb-icon qfb-icon-question"></i><span class="qfb-tooltip-content"><?php esc_html_e('If enabled, the submitted option value can be different from the option label.', 'quform'); ?></span></span></label>
                                                        <input type="checkbox" id="qfb_customise_name_prefix_values" class="qfb-customise-values qfb-toggle">
                                                        <label for="qfb_customise_name_prefix_values"></label>
                                                    </div>
                                                    <div class="qfb-options-right-buttons">
                                                        <div id="qfb-add-name-prefix-option-button" class="qfb-button-green"><i class="qfb-mdi qfb-mdi-add_circle"></i><?php esc_html_e('Add option', 'quform'); ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="qfb-sub-setting">
                                            <div class="qfb-sub-setting-label">
                                                <label for="qfb_name_prefix_none_option"><?php esc_html_e('"Please select" option', 'quform'); ?><span class="qfb-tooltip"><i class="qfb-icon qfb-icon-question"></i><span class="qfb-tooltip-content"><?php esc_html_e('Adds an option to the top of the list to let the user choose no value. The text can be changed on the Translations tab.', 'quform'); ?></span></span></label>
                                            </div>
                                            <div class="qfb-sub-setting-inner">
                                                <div class="qfb-sub-setting-input">
                                                    <input type="checkbox" id="qfb_name_prefix_none_option" class="qfb-toggle">
                                                    <label for="qfb_name_prefix_none_option"></label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="qfb-sub-setting">
                                            <div class="qfb-sub-setting-label">
                                                <label><?php esc_html_e('Sub label', 'quform'); ?></label>
                                            </div>
                                            <div class="qfb-sub-setting-inner">
                                                <div class="qfb-sub-setting-input">
                                                    <div id="qfb-name-prefix-sub-label-tabs">
                                                        <ul class="qfb-tabs-nav qfb-sub-tabs-nav qfb-cf">
                                                            <li><a><?php esc_html_e('Below field', 'quform'); ?></a></li>
                                                            <li><a><?php esc_html_e('Above field', 'quform'); ?></a></li>
                                                        </ul>
                                                        <div class="qfb-tabs-panel">
                                                            <input type="text" id="qfb_name_prefix_sub_label">
                                                        </div>
                                                        <div class="qfb-tabs-panel">
                                                            <input type="text" id="qfb_name_prefix_sub_label_above">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="qfb-sub-setting">
                                            <div class="qfb-sub-setting-label">
                                                <label for="qfb_name_prefix_custom_class"><?php esc_html_e('Custom CSS class', 'quform'); ?></label>
                                            </div>
                                            <div class="qfb-sub-setting-inner">
                                                <div class="qfb-sub-setting-input">
                                                    <div id="qfb-name-prefix-custom-class-tabs">
                                                        <ul class="qfb-tabs-nav qfb-sub-tabs-nav qfb-cf">
                                                            <li><a><?php esc_html_e('Field', 'quform'); ?></a></li>
                                                            <li><a><?php esc_html_e('Wrapper', 'quform'); ?></a></li>
                                                        </ul>
                                                        <div class="qfb-tabs-panel">
                                                            <input type="text" id="qfb_name_prefix_custom_class">
                                                        </div>
                                                        <div class="qfb-tabs-panel">
                                                            <input type="text" id="qfb_name_prefix_custom_element_class">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="qfb-tabs-panel">
                                        <div class="qfb-sub-setting">
                                            <div class="qfb-sub-setting-label">
                                                <label for="qfb_name_first_enabled"><?php esc_html_e('Enabled', 'quform'); ?></label>
                                            </div>
                                            <div class="qfb-sub-setting-inner">
                                                <div class="qfb-sub-setting-input">
                                                    <input type="checkbox" id="qfb_name_first_enabled" class="qfb-toggle">
                                                    <label for="qfb_name_first_enabled"></label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="qfb-sub-setting">
                                            <div class="qfb-sub-setting-label">
                                                <label for="qfb_name_first_required"><?php esc_html_e('Required', 'quform'); ?></label>
                                            </div>
                                            <div class="qfb-sub-setting-inner">
                                                <div class="qfb-sub-setting-input">
                                                    <input type="checkbox" id="qfb_name_first_required" class="qfb-toggle">
                                                    <label for="qfb_name_first_required"></label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="qfb-sub-setting">
                                            <div class="qfb-sub-setting-label">
                                                <label for="qfb_name_first_placeholder"><?php esc_html_e('Placeholder', 'quform'); ?></label>
                                            </div>
                                            <div class="qfb-sub-setting-inner">
                                                <div class="qfb-sub-setting-input">
                                                    <input type="text" id="qfb_name_first_placeholder">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="qfb-sub-setting">
                                            <div class="qfb-sub-setting-label">
                                                <label><?php esc_html_e('Sub label', 'quform'); ?></label>
                                            </div>
                                            <div class="qfb-sub-setting-inner">
                                                <div class="qfb-sub-setting-input">
                                                    <div id="qfb-name-first-sub-label-tabs">
                                                        <ul class="qfb-tabs-nav qfb-sub-tabs-nav qfb-cf">
                                                            <li><a><?php esc_html_e('Below field', 'quform'); ?></a></li>
                                                            <li><a><?php esc_html_e('Above field', 'quform'); ?></a></li>
                                                        </ul>
                                                        <div class="qfb-tabs-panel">
                                                            <input type="text" id="qfb_name_first_sub_label">
                                                        </div>
                                                        <div class="qfb-tabs-panel">
                                                            <input type="text" id="qfb_name_first_sub_label_above">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="qfb-sub-setting">
                                            <div class="qfb-sub-setting-label">
                                                <label for="qfb_name_first_default_value"><?php esc_html_e('Default value', 'quform'); ?></label>
                                            </div>
                                            <div class="qfb-sub-setting-inner">
                                                <div class="qfb-sub-setting-input">
                                                    <div class="qfb-input-variable">
                                                        <input type="text" id="qfb_name_first_default_value">
                                                        <?php echo $builder->getInsertVariableHtml('qfb_name_first_default_value', true); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="qfb-sub-setting">
                                            <div class="qfb-sub-setting-label">
                                                <label for="qfb_name_first_custom_class"><?php esc_html_e('Custom CSS class', 'quform'); ?></label>
                                            </div>
                                            <div class="qfb-sub-setting-inner">
                                                <div class="qfb-sub-setting-input">
                                                    <div id="qfb-name-first-custom-class-tabs">
                                                        <ul class="qfb-tabs-nav qfb-sub-tabs-nav qfb-cf">
                                                            <li><a><?php esc_html_e('Field', 'quform'); ?></a></li>
                                                            <li><a><?php esc_html_e('Wrapper', 'quform'); ?></a></li>
                                                        </ul>
                                                        <div class="qfb-tabs-panel">
                                                            <input type="text" id="qfb_name_first_custom_class">
                                                        </div>
                                                        <div class="qfb-tabs-panel">
                                                            <input type="text" id="qfb_name_first_custom_element_class">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="qfb-sub-setting">
                                            <div class="qfb-sub-setting-label">
                                                <label for="qfb_autocomplete"><?php esc_html_e('Autocomplete', 'quform'); ?><span class="qfb-tooltip"><i class="qfb-icon qfb-icon-question"></i><span class="qfb-tooltip-content"><?php esc_html_e('Choose which type of information that browsers can autofill into this field.', 'quform'); ?></span></span></label>
                                            </div>
                                            <div class="qfb-sub-setting-inner">
                                                <div class="qfb-sub-setting-input">
                                                    <div class="qfb-autocomplete-left">
                                                        <div class="qfb-autocomplete-left-inner">
                                                            <input type="text" id="qfb_name_first_autocomplete">
                                                        </div>
                                                    </div>
                                                    <div class="qfb-autocomplete-right">
                                                        <select class="qfb-autocomplete-insert" data-target="#qfb_name_first_autocomplete">
                                                            <option value=""><?php esc_html_e('Insert value', 'quform'); ?></option>
                                                            <?php foreach ($builder->getAutocompleteValues() as $value) : ?>
                                                                <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($value); ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="qfb-tabs-panel">
                                        <div class="qfb-sub-setting">
                                            <div class="qfb-sub-setting-label">
                                                <label for="qfb_name_middle_enabled"><?php esc_html_e('Enabled', 'quform'); ?></label>
                                            </div>
                                            <div class="qfb-sub-setting-inner">
                                                <div class="qfb-sub-setting-input">
                                                    <input type="checkbox" id="qfb_name_middle_enabled" class="qfb-toggle">
                                                    <label for="qfb_name_middle_enabled"></label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="qfb-sub-setting">
                                            <div class="qfb-sub-setting-label">
                                                <label for="qfb_name_middle_required"><?php esc_html_e('Required', 'quform'); ?></label>
                                            </div>
                                            <div class="qfb-sub-setting-inner">
                                                <div class="qfb-sub-setting-input">
                                                    <input type="checkbox" id="qfb_name_middle_required" class="qfb-toggle">
                                                    <label for="qfb_name_middle_required"></label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="qfb-sub-setting">
                                            <div class="qfb-sub-setting-label">
                                                <label for="qfb_name_middle_placeholder"><?php esc_html_e('Placeholder', 'quform'); ?></label>
                                            </div>
                                            <div class="qfb-sub-setting-inner">
                                                <div class="qfb-sub-setting-input">
                                                    <input type="text" id="qfb_name_middle_placeholder">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="qfb-sub-setting">
                                            <div class="qfb-sub-setting-label">
                                                <label><?php esc_html_e('Sub label', 'quform'); ?></label>
                                            </div>
                                            <div class="qfb-sub-setting-inner">
                                                <div class="qfb-sub-setting-input">
                                                    <div id="qfb-name-middle-sub-label-tabs">
                                                        <ul class="qfb-tabs-nav qfb-sub-tabs-nav qfb-cf">
                                                            <li><a><?php esc_html_e('Below field', 'quform'); ?></a></li>
                                                            <li><a><?php esc_html_e('Above field', 'quform'); ?></a></li>
                                                        </ul>
                                                        <div class="qfb-tabs-panel">
                                                            <input type="text" id="qfb_name_middle_sub_label">
                                                        </div>
                                                        <div class="qfb-tabs-panel">
                                                            <input type="text" id="qfb_name_middle_sub_label_above">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="qfb-sub-setting">
                                            <div class="qfb-sub-setting-label">
                                                <label for="qfb_name_middle_default_value"><?php esc_html_e('Default value', 'quform'); ?></label>
                                            </div>
                                            <div class="qfb-sub-setting-inner">
                                                <div class="qfb-sub-setting-input">
                                                    <div class="qfb-input-variable">
                                                        <input type="text" id="qfb_name_middle_default_value">
                                                        <?php echo $builder->getInsertVariableHtml('qfb_name_middle_default_value', true); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="qfb-sub-setting">
                                            <div class="qfb-sub-setting-label">
                                                <label for="qfb_name_middle_custom_class"><?php esc_html_e('Custom CSS class', 'quform'); ?></label>
                                            </div>
                                            <div class="qfb-sub-setting-inner">
                                                <div class="qfb-sub-setting-input">
                                                    <div id="qfb-name-middle-custom-class-tabs">
                                                        <ul class="qfb-tabs-nav qfb-sub-tabs-nav qfb-cf">
                                                            <li><a><?php esc_html_e('Field', 'quform'); ?></a></li>
                                                            <li><a><?php esc_html_e('Wrapper', 'quform'); ?></a></li>
                                                        </ul>
                                                        <div class="qfb-tabs-panel">
                                                            <input type="text" id="qfb_name_middle_custom_class">
                                                        </div>
                                                        <div class="qfb-tabs-panel">
                                                            <input type="text" id="qfb_name_middle_custom_element_class">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="qfb-sub-setting">
                                            <div class="qfb-sub-setting-label">
                                                <label for="qfb_autocomplete"><?php esc_html_e('Autocomplete', 'quform'); ?><span class="qfb-tooltip"><i class="qfb-icon qfb-icon-question"></i><span class="qfb-tooltip-content"><?php esc_html_e('Choose which type of information that browsers can autofill into this field.', 'quform'); ?></span></span></label>
                                            </div>
                                            <div class="qfb-sub-setting-inner">
                                                <div class="qfb-sub-setting-input">
                                                    <div class="qfb-autocomplete-left">
                                                        <div class="qfb-autocomplete-left-inner">
                                                            <input type="text" id="qfb_name_middle_autocomplete">
                                                        </div>
                                                    </div>
                                                    <div class="qfb-autocomplete-right">
                                                        <select class="qfb-autocomplete-insert" data-target="#qfb_name_middle_autocomplete">
                                                            <option value=""><?php esc_html_e('Insert value', 'quform'); ?></option>
                                                            <?php foreach ($builder->getAutocompleteValues() as $value) : ?>
                                                                <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($value); ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="qfb-tabs-panel">
                                        <div class="qfb-sub-setting">
                                            <div class="qfb-sub-setting-label">
                                                <label for="qfb_name_last_enabled"><?php esc_html_e('Enabled', 'quform'); ?></label>
                                            </div>
                                            <div class="qfb-sub-setting-inner">
                                                <div class="qfb-sub-setting-input">
                                                    <input type="checkbox" id="qfb_name_last_enabled" class="qfb-toggle">
                                                    <label for="qfb_name_last_enabled"></label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="qfb-sub-setting">
                                            <div class="qfb-sub-setting-label">
                                                <label for="qfb_name_last_required"><?php esc_html_e('Required', 'quform'); ?></label>
                                            </div>
                                            <div class="qfb-sub-setting-inner">
                                                <div class="qfb-sub-setting-input">
                                                    <input type="checkbox" id="qfb_name_last_required" class="qfb-toggle">
                                                    <label for="qfb_name_last_required"></label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="qfb-sub-setting">
                                            <div class="qfb-sub-setting-label">
                                                <label for="qfb_name_last_placeholder"><?php esc_html_e('Placeholder', 'quform'); ?></label>
                                            </div>
                                            <div class="qfb-sub-setting-inner">
                                                <div class="qfb-sub-setting-input">
                                                    <input type="text" id="qfb_name_last_placeholder">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="qfb-sub-setting">
                                            <div class="qfb-sub-setting-label">
                                                <label><?php esc_html_e('Sub label', 'quform'); ?></label>
                                            </div>
                                            <div class="qfb-sub-setting-inner">
                                                <div class="qfb-sub-setting-input">
                                                    <div id="qfb-name-last-sub-label-tabs">
                                                        <ul class="qfb-tabs-nav qfb-sub-tabs-nav qfb-cf">
                                                            <li><a><?php esc_html_e('Below field', 'quform'); ?></a></li>
                                                            <li><a><?php esc_html_e('Above field', 'quform'); ?></a></li>
                                                        </ul>
                                                        <div class="qfb-tabs-panel">
                                                            <input type="text" id="qfb_name_last_sub_label">
                                                        </div>
                                                        <div class="qfb-tabs-panel">
                                                            <input type="text" id="qfb_name_last_sub_label_above">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="qfb-sub-setting">
                                            <div class="qfb-sub-setting-label">
                                                <label for="qfb_name_last_default_value"><?php esc_html_e('Default value', 'quform'); ?></label>
                                            </div>
                                            <div class="qfb-sub-setting-inner">
                                                <div class="qfb-sub-setting-input">
                                                    <div class="qfb-input-variable">
                                                        <input type="text" id="qfb_name_last_default_value">
                                                        <?php echo $builder->getInsertVariableHtml('qfb_name_last_default_value', true); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="qfb-sub-setting">
                                            <div class="qfb-sub-setting-label">
                                                <label for="qfb_name_last_custom_class"><?php esc_html_e('Custom CSS class', 'quform'); ?></label>
                                            </div>
                                            <div class="qfb-sub-setting-inner">
                                                <div class="qfb-sub-setting-input">
                                                    <div id="qfb-name-last-custom-class-tabs">
                                                        <ul class="qfb-tabs-nav qfb-sub-tabs-nav qfb-cf">
                                                            <li><a><?php esc_html_e('Field', 'quform'); ?></a></li>
                                                            <li><a><?php esc_html_e('Wrapper', 'quform'); ?></a></li>
                                                        </ul>
                                                        <div class="qfb-tabs-panel">
                                                            <input type="text" id="qfb_name_last_custom_class">
                                                        </div>
                                                        <div class="qfb-tabs-panel">
                                                            <input type="text" id="qfb_name_last_custom_element_class">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="qfb-sub-setting">
                                            <div class="qfb-sub-setting-label">
                                                <label for="qfb_autocomplete"><?php esc_html_e('Autocomplete', 'quform'); ?><span class="qfb-tooltip"><i class="qfb-icon qfb-icon-question"></i><span class="qfb-tooltip-content"><?php esc_html_e('Choose which type of information that browsers can autofill into this field.', 'quform'); ?></span></span></label>
                                            </div>
                                            <div class="qfb-sub-setting-inner">
                                                <div class="qfb-sub-setting-input">
                                                    <div class="qfb-autocomplete-left">
                                                        <div class="qfb-autocomplete-left-inner">
                                                            <input type="text" id="qfb_name_last_autocomplete">
                                                        </div>
                                                    </div>
                                                    <div class="qfb-autocomplete-right">
                                                        <select class="qfb-autocomplete-insert" data-target="#qfb_name_last_autocomplete">
                                                            <option value=""><?php esc_html_e('Insert value', 'quform'); ?></option>
                                                            <?php foreach ($builder->getAutocompleteValues() as $value) : ?>
                                                                <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($value); ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="qfb-tabs-panel">
                                        <div class="qfb-sub-setting">
                                            <div class="qfb-sub-setting-label">
                                                <label for="qfb_name_suffix_enabled"><?php esc_html_e('Enabled', 'quform'); ?></label>
                                            </div>
                                            <div class="qfb-sub-setting-inner">
                                                <div class="qfb-sub-setting-input">
                                                    <input type="checkbox" id="qfb_name_suffix_enabled" class="qfb-toggle">
                                                    <label for="qfb_name_suffix_enabled"></label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="qfb-sub-setting">
                                            <div class="qfb-sub-setting-label">
                                                <label for="qfb_name_suffix_required"><?php esc_html_e('Required', 'quform'); ?></label>
                                            </div>
                                            <div class="qfb-sub-setting-inner">
                                                <div class="qfb-sub-setting-input">
                                                    <input type="checkbox" id="qfb_name_suffix_required" class="qfb-toggle">
                                                    <label for="qfb_name_suffix_required"></label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="qfb-sub-setting">
                                            <div class="qfb-sub-setting-label">
                                                <label for="qfb_name_suffix_placeholder"><?php esc_html_e('Placeholder', 'quform'); ?></label>
                                            </div>
                                            <div class="qfb-sub-setting-inner">
                                                <div class="qfb-sub-setting-input">
                                                    <input type="text" id="qfb_name_suffix_placeholder">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="qfb-sub-setting">
                                            <div class="qfb-sub-setting-label">
                                                <label><?php esc_html_e('Sub label', 'quform'); ?></label>
                                            </div>
                                            <div class="qfb-sub-setting-inner">
                                                <div class="qfb-sub-setting-input">
                                                    <div id="qfb-name-suffix-sub-label-tabs">
                                                        <ul class="qfb-tabs-nav qfb-sub-tabs-nav qfb-cf">
                                                            <li><a><?php esc_html_e('Below field', 'quform'); ?></a></li>
                                                            <li><a><?php esc_html_e('Above field', 'quform'); ?></a></li>
                                                        </ul>
                                                        <div class="qfb-tabs-panel">
                                                            <input type="text" id="qfb_name_suffix_sub_label">
                                                        </div>
                                                        <div class="qfb-tabs-panel">
                                                            <input type="text" id="qfb_name_suffix_sub_label_above">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="qfb-sub-setting">
                                            <div class="qfb-sub-setting-label">
                                                <label for="qfb_name_suffix_default_value"><?php esc_html_e('Default value', 'quform'); ?></label>
                                            </div>
                                            <div class="qfb-sub-setting-inner">
                                                <div class="qfb-sub-setting-input">
                                                    <div class="qfb-input-variable">
                                                        <input type="text" id="qfb_name_suffix_default_value">
                                                        <?php echo $builder->getInsertVariableHtml('qfb_name_suffix_default_value', true); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="qfb-sub-setting">
                                            <div class="qfb-sub-setting-label">
                                                <label for="qfb_name_suffix_custom_class"><?php esc_html_e('Custom CSS class', 'quform'); ?></label>
                                            </div>
                                            <div class="qfb-sub-setting-inner">
                                                <div class="qfb-sub-setting-input">
                                                    <div id="qfb-name-suffix-custom-class-tabs">
                                                        <ul class="qfb-tabs-nav qfb-sub-tabs-nav qfb-cf">
                                                            <li><a><?php esc_html_e('Field', 'quform'); ?></a></li>
                                                            <li><a><?php esc_html_e('Wrapper', 'quform'); ?></a></li>
                                                        </ul>
                                                        <div class="qfb-tabs-panel">
                                                            <input type="text" id="qfb_name_suffix_custom_class">
                                                        </div>
                                                        <div class="qfb-tabs-panel">
                                                            <input type="text" id="qfb_name_suffix_custom_element_class">
                                                        </div>
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
                            <label for="qfb_row_column_size"><?php esc_html_e('Column size', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <select id="qfb_row_column_size">
                                    <option value="fixed"><?php esc_html_e('Fixed width', 'quform'); ?></option>
                                    <option value="float"><?php esc_html_e('Float', 'quform'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_row_column_count"><?php esc_html_e('Number of columns', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <select id="qfb_row_column_count">
                                    <?php foreach (range(1, 20) as $value) : ?>
                                        <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($value); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label><?php esc_html_e('Customize column widths', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <div id="qfb-row-column-customise"></div>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('At what screen width should the column layout be stacked?', 'quform'); ?></div></div>
                            <label for="qfb_responsive_columns"><?php esc_html_e('Responsive columns', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <?php echo $builder->getResponsiveSelectHtml('qfb_responsive_columns'); ?>
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
                            <label for="qfb_responsive_columns_custom"><?php esc_html_e('Responsive columns custom', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_responsive_columns_custom">
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>

        <div class="qfb-tabs-panel">

            <div class="qfb-element-settings-inner">

                <div class="qfb-settings">

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label><?php esc_html_e('Label icon', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <?php echo $builder->getSelectIconHtml('qfb_label_icon'); ?>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label><?php esc_html_e('Field icons', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <div class="qfb-settings-row qfb-settings-row-2">
                                    <div class="qfb-settings-column">
                                        <label><?php esc_html_e('Left', 'quform'); ?></label>
                                        <?php echo $builder->getSelectIconHtml('qfb_field_icon_left'); ?>
                                    </div>
                                    <div class="qfb-settings-column">
                                        <label><?php esc_html_e('Right', 'quform'); ?></label>
                                        <?php echo $builder->getSelectIconHtml('qfb_field_icon_right'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_options_layout"><?php esc_html_e('Options layout', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <select id="qfb_options_layout">
                                    <option value="block"><?php esc_html_e('One option per line', 'quform'); ?></option>
                                    <option value="inline"><?php esc_html_e('Inline', 'quform'); ?></option>
                                    <?php foreach (range(2, 20) as $i) : ?>
                                        <option value="<?php echo esc_attr($i); ?>">
                                            <?php
                                                echo esc_html(
                                                    sprintf(
                                                        /* translators: %s: the number of columns */
                                                        _n('%s column', '%s columns', $i, 'quform'),
                                                        number_format_i18n($i)
                                                    )
                                                );
                                            ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('At what screen width should the column layout be stacked?', 'quform'); ?></div></div>
                            <label for="qfb_options_layout_responsive_columns"><?php esc_html_e('Responsive columns', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <?php echo $builder->getResponsiveSelectHtml('qfb_options_layout_responsive_columns', '', false); ?>
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
                            <label for="qfb_options_layout_responsive_columns_custom"><?php esc_html_e('Responsive columns custom', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_options_layout_responsive_columns_custom">
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Choose the options style, you can set a background image or icon by editing the settings for each individual option.', 'quform'); ?></div></div>
                            <label for="qfb_options_style"><?php esc_html_e('Options style', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <?php echo $builder->getOptionsStyleSelectHtml('qfb_options_style'); ?>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_enhanced_upload_enabled"><?php esc_html_e('Enable enhanced uploader', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="checkbox" id="qfb_enhanced_upload_enabled" class="qfb-toggle">
                                <label for="qfb_enhanced_upload_enabled"></label>
                                <p class="qfb-description"><?php esc_html_e('Enables the enhanced file uploader which shows the upload progress in modern browsers, and lets users add or remove files.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_enhanced_upload_style"><?php esc_html_e('Enhanced uploader style', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <select id="qfb_enhanced_upload_style">
                                    <option value="button"><?php esc_html_e('Button', 'quform'); ?></option>
                                    <option value="dropzone"><?php esc_html_e('Dropzone', 'quform'); ?></option>
                                </select>
                                <p class="qfb-description"><?php esc_html_e('Both options support dropping files but the dropzone has a larger area.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_options_button_style"><?php esc_html_e('Options button style', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <?php echo $builder->getButtonStyleSelectHtml('qfb_options_button_style'); ?>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_options_button_size"><?php esc_html_e('Options button size', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <div class="qfb-input-variable">
                                    <?php echo $builder->getSizeSelectHtml('qfb_options_button_size'); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_options_button_width"><?php esc_html_e('Options button width', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <div class="qfb-input-variable">
                                    <?php echo $builder->getButtonWidthSelectHtml('qfb_options_button_width'); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Enter a custom width using any CSS unit.', 'quform'); ?></div></div>
                            <label for="qfb_options_button_width_custom"><?php esc_html_e('Options button custom width', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_options_button_width_custom">
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_options_button_icon_position"><?php esc_html_e('Options button icon position', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <?php echo $builder->getIconPositionSelectHtml('qfb_options_button_icon_position'); ?>
                                <p class="qfb-description"><?php esc_html_e('Choose the icon position relative to the button text.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('"Inherit" will inherit the value from the form settings.', 'quform'); ?></div></div>
                            <label for="qfb_button_style"><?php esc_html_e('Button style', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <?php echo $builder->getButtonStyleSelectHtml('qfb_button_style'); ?>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Controls the padding and font size for the upload button. "Inherit" will inherit the value from the form settings.', 'quform'); ?></div></div>
                            <label for="qfb_button_size"><?php esc_html_e('Button size', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <div class="qfb-input-variable">
                                    <?php echo $builder->getSizeSelectHtml('qfb_button_size'); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('"Inherit" will inherit the value from the form settings.', 'quform'); ?></div></div>
                            <label for="qfb_button_width"><?php esc_html_e('Button width', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <div class="qfb-input-variable">
                                    <?php echo $builder->getButtonWidthSelectHtml('qfb_button_width'); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Enter a custom width using any CSS unit.', 'quform'); ?></div></div>
                            <label for="qfb_button_width_custom"><?php esc_html_e('Button custom width', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_button_width_custom">
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label><?php esc_html_e('Button icon', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <?php echo $builder->getSelectIconHtml('qfb_button_icon'); ?>
                                <p class="qfb-description"><?php esc_html_e('Choose an icon for the upload button.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_button_icon_position"><?php esc_html_e('Button icon position', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <?php echo $builder->getIconPositionSelectHtml('qfb_button_icon_position', '', false); ?>
                                <p class="qfb-description"><?php esc_html_e('Choose the icon position relative to the button text.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Controls the padding and font size for this field. "Inherit" will inherit the value from the parent group or page settings.', 'quform'); ?></div></div>
                            <label for="qfb_field_size"><?php esc_html_e('Field size', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <div class="qfb-input-variable">
                                    <?php echo $builder->getSizeSelectHtml('qfb_field_size'); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Controls the padding and font size for fields in this group. "Inherit" will inherit the value from the parent group or page settings.', 'quform'); ?></div></div>
                            <label for="qfb_group_field_size"><?php esc_html_e('Field size', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <div class="qfb-input-variable">
                                    <?php echo $builder->getSizeSelectHtml('qfb_group_field_size'); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Controls the padding and font size for fields in this page. "Inherit" will inherit the value from the form settings.', 'quform'); ?></div></div>
                            <label for="qfb_page_field_size"><?php esc_html_e('Field size', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <div class="qfb-input-variable">
                                    <?php echo $builder->getSizeSelectHtml('qfb_page_field_size'); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_upload_list_layout"><?php esc_html_e('Upload list layout', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <div class="qfb-input-variable">
                                    <select id="qfb_upload_list_layout">
                                        <option value=""><?php esc_html_e('Inline', 'quform'); ?></option>
                                        <option value="block"><?php esc_html_e('One per line', 'quform'); ?></option>
                                    </select>
                                    <p class="qfb-description"><?php esc_html_e('The layout of the files in the upload list.', 'quform'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_upload_list_size"><?php esc_html_e('Upload list size', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <div class="qfb-input-variable">
                                    <?php echo $builder->getSizeSelectHtml('qfb_upload_list_size', '', false); ?>
                                    <p class="qfb-description"><?php esc_html_e('The size of a file in the upload list.', 'quform'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('"Inherit" will inherit the value from the parent group or page settings.', 'quform'); ?></div></div>
                            <label for="qfb_field_width"><?php esc_html_e('Field width', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <div class="qfb-input-variable">
                                    <?php echo $builder->getFieldWidthSelectHtml('qfb_field_width'); ?>
                                    <p id="qfb-file-field-width-description" class="qfb-description qfb-hidden"><?php esc_html_e('The width only applies when the enhanced uploader option is disabled or not supported in the browser.', 'quform'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('"Inherit" will inherit the value from the parent group or page settings.', 'quform'); ?></div></div>
                            <label for="qfb_group_field_width"><?php esc_html_e('Field width', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <div class="qfb-input-variable">
                                    <?php echo $builder->getFieldWidthSelectHtml('qfb_group_field_width'); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('"Inherit" will inherit the value from the form settings.', 'quform'); ?></div></div>
                            <label for="qfb_page_field_width"><?php esc_html_e('Field width', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <div class="qfb-input-variable">
                                    <?php echo $builder->getFieldWidthSelectHtml('qfb_page_field_width'); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Enter a custom width using any CSS unit.', 'quform'); ?></div></div>
                            <label for="qfb_field_width_custom"><?php esc_html_e('Field custom width', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_field_width_custom">
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Enable the WordPress editor on the textarea field.', 'quform'); ?></div></div>
                            <label for="qfb_enable_editor"><?php esc_html_e('Enable editor', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="checkbox" id="qfb_enable_editor" class="qfb-toggle">
                                <label for="qfb_enable_editor"></label>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Enable the enhanced select menu script.', 'quform'); ?></div></div>
                            <label for="qfb_enhanced_select_enabled"><?php esc_html_e('Enable enhanced select', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="checkbox" id="qfb_enhanced_select_enabled" class="qfb-toggle">
                                <label for="qfb_enhanced_select_enabled"></label>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Enable searching within the enhanced select.', 'quform'); ?></div></div>
                            <label for="qfb_enhanced_select_search"><?php esc_html_e('Enhanced select search', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="checkbox" id="qfb_enhanced_select_search" class="qfb-toggle">
                                <label for="qfb_enhanced_select_search"></label>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_captcha_length"><?php esc_html_e('Number of characters', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input class="qfb-width-75" type="text" id="qfb_captcha_length">
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_captcha_width"><?php esc_html_e('Image width', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input class="qfb-width-75" type="text" id="qfb_captcha_width"><span class="qfb-setting-unit">px</span>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_captcha_height"><?php esc_html_e('Image height', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input class="qfb-width-75" type="text" id="qfb_captcha_height"><span class="qfb-setting-unit">px</span>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_captcha_bg_color"><?php esc_html_e('Background color', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_captcha_bg_color" class="qfb-colorpicker">
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_captcha_text_color"><?php esc_html_e('Text color', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_captcha_text_color" class="qfb-colorpicker">
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_captcha_font"><?php esc_html_e('Font', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <select id="qfb_captcha_font">
                                    <option value="Base_02.ttf">Base 02</option>
                                    <option value="coolvetica_rg.ttf">Coolvetica</option>
                                    <option value="Diesel.ttf">Diesel</option>
                                    <option value="Dirty_Ego.ttf">Dirty Ego</option>
                                    <option value="Distress.ttf">Distress</option>
                                    <option value="Dotmatrix_5.ttf">Dotmatrix 5</option>
                                    <option value="DS_Moster.ttf">DS Moster</option>
                                    <option value="Phinster.ttf">Phinster</option>
                                    <option value="Rolling_Stone.ttf">Rolling Stone</option>
                                    <option value="Sabotage.ttf">Sabotage</option>
                                    <option value="Sketch_Heavy.ttf">Sketch Heavy</option>
                                    <option value="Typist.ttf">Typist</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('The font size for each character will be randomly chosen between the minimum and maximum.', 'quform'); ?></div></div>
                            <label for="qfb_captcha_min_font_size"><?php esc_html_e('Minimum font size', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input class="qfb-width-75" type="text" id="qfb_captcha_min_font_size"><span class="qfb-setting-unit">pt</span>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('The font size for each character will be randomly chosen between the minimum and maximum.', 'quform'); ?></div></div>
                            <label for="qfb_captcha_max_font_size"><?php esc_html_e('Maximum font size', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input class="qfb-width-75" type="text" id="qfb_captcha_max_font_size"><span class="qfb-setting-unit">pt</span>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content">
                                    <?php esc_html_e('The angle for each character will be randomly chosen between the minimum and maximum.', 'quform'); ?>
                                </div></div>
                            <label for="qfb_captcha_min_angle"><?php esc_html_e('Minimum letter rotation (degrees)', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input class="qfb-width-75" type="text" id="qfb_captcha_min_angle">
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('The angle for each character will be randomly chosen between the minimum and maximum.', 'quform'); ?></div></div>
                            <label for="qfb_captcha_max_angle"><?php esc_html_e('Maximum letter rotation (degrees)', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input class="qfb-width-75" type="text" id="qfb_captcha_max_angle">
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('The generated image will be double the size of the image on the page to support Retina devices.', 'quform'); ?></div></div>
                            <label for="qfb_captcha_retina"><?php esc_html_e('Retina image', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="checkbox" id="qfb_captcha_retina" class="qfb-toggle">
                                <label for="qfb_captcha_retina"></label>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_group_style"><?php esc_html_e('Group style', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <select id="qfb_group_style">
                                    <option value="plain"><?php esc_html_e('Plain', 'quform'); ?></option>
                                    <option value="bordered"><?php esc_html_e('Bordered', 'quform'); ?></option>
                                </select>
                                <p class="qfb-description"><?php esc_html_e('Plain groups have no additional styling. Bordered groups have a border and padding.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_border_color"><?php esc_html_e('Border color', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_border_color" class="qfb-colorpicker">
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_background_color"><?php esc_html_e('Background color', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_background_color" class="qfb-colorpicker">
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('At what screen width should the column layout be stacked?', 'quform'); ?></div></div>
                            <label for="qfb_name_responsive_columns"><?php esc_html_e('Responsive columns', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <?php echo $builder->getResponsiveSelectHtml('qfb_name_responsive_columns'); ?>
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
                            <label for="qfb_name_responsive_columns_custom"><?php esc_html_e('Responsive columns custom', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_name_responsive_columns_custom">
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_custom_class"><?php esc_html_e('Custom CSS class', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <div id="qfb-custom-class-tabs">
                                    <ul class="qfb-tabs-nav qfb-sub-tabs-nav qfb-cf">
                                        <li><a><?php esc_html_e('Field', 'quform'); ?></a></li>
                                        <li><a><?php esc_html_e('Wrapper', 'quform'); ?></a></li>
                                    </ul>
                                    <div class="qfb-tabs-panel">
                                        <input type="text" id="qfb_custom_class">
                                    </div>
                                    <div class="qfb-tabs-panel">
                                        <input type="text" id="qfb_custom_element_class">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon">
                                <div class="qfb-tooltip-content">
                                    <?php
                                        printf(
                                            /* translators: %1$s: open code tag, %2$s: close code tag */
                                            esc_html__('Sets the height of the field, enter a number to show that number of options at one time. Enter %1$sauto%2$s to set the height to show all options.', 'quform'),
                                            '<code>',
                                            '</code>'
                                        );
                                    ?>
                                </div>
                            </div>
                            <label for="qfb_size_attribute"><?php esc_html_e('Size attribute', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_size_attribute">
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Once you have added a style, enter the CSS styles.', 'quform'); ?></div></div>
                            <label><?php esc_html_e('CSS styles', 'quform'); ?></label>
                            <div id="qfb-add-style" class="qfb-button-green"><i class="qfb-mdi qfb-mdi-add_circle"></i><?php esc_html_e('Add a style', 'quform'); ?></div>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <div id="qfb-style-settings">
                                    <div class="qfb-settings">
                                        <div class="qfb-setting">
                                            <div class="qfb-setting-label">
                                                <label for="qfb_style_type"><?php esc_attr_e('Selector', 'quform'); ?></label>
                                            </div>
                                            <div class="qfb-setting-inner">
                                                <div class="qfb-setting-input">
                                                    <select id="qfb_style_type" style="width: 100%;"></select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="qfb-setting">
                                            <div class="qfb-setting-label">
                                                <label for="qfb_style_css"><?php esc_attr_e('CSS', 'quform'); ?></label>
                                            </div>
                                            <div class="qfb-setting-inner">
                                                <div class="qfb-setting-input">
                                                    <textarea id="qfb_style_css"></textarea>
                                                    <?php echo $builder->getCssHelperHtml(); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="qfb-styles"></div>
                                <div id="qfb-styles-empty"><div class="qfb-message-box qfb-message-box-info"><div class="qfb-message-box-inner"><?php esc_html_e('No active styles, add one using the "Add a style" button.', 'quform'); ?></div></div></div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>

        <div class="qfb-tabs-panel">
            <div class="qfb-element-settings-inner">

                <div class="qfb-settings">

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('The placeholder text will appear inside the field until the user starts to type.', 'quform'); ?></div></div>
                            <label for="qfb_placeholder"><?php esc_html_e('Placeholder', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <div class="qfb-input-variable">
                                    <input type="text" id="qfb_placeholder">
                                    <?php echo $builder->getInsertVariableHtml('qfb_placeholder', true); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('The sub label is small text shown below or above the field.', 'quform'); ?></div></div>
                            <label><?php esc_html_e('Sub label', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <div id="qfb-sub-label-tabs">
                                    <ul class="qfb-tabs-nav qfb-sub-tabs-nav qfb-cf">
                                        <li><a><?php esc_html_e('Below field', 'quform'); ?></a></li>
                                        <li><a><?php esc_html_e('Above field', 'quform'); ?></a></li>
                                    </ul>
                                    <div class="qfb-tabs-panel">
                                        <input type="text" id="qfb_sub_label">
                                    </div>
                                    <div class="qfb-tabs-panel">
                                        <input type="text" id="qfb_sub_label_above">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('The admin label will be shown in place of the label throughout the plugin, in the notification email and when viewing submitted form entries.', 'quform'); ?></div></div>
                            <label for="qfb_admin_label"><?php esc_html_e('Admin label', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_admin_label">
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon">
                                <div class="qfb-tooltip-content">
                                    <?php
                                        printf(
                                            /* translators: %1$s: open span tag, %2$s: close span tag */
                                            esc_html__('The tooltip text will appear next to your element in a tooltip when the user hovers it with their mouse. You can customize the look of tooltips by going to %1$sSettings &rarr; Style &rarr; Tooltips%2$s', 'quform'),
                                            '<span class="qfb-bold">',
                                            '</span>'
                                        );
                                    ?>
                                </div>
                            </div>
                            <label for="qfb_tooltip"><?php esc_html_e('Tooltip text', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_tooltip">
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon">
                                <div class="qfb-tooltip-content">
                                    <?php
                                        printf(
                                            /* translators: %1$s: open span tag, %2$s: close span tag */
                                            esc_html__('If set to %1$sInherit%2$s, the setting will be inherited from the global form settings. If set to %1$sField%2$s, the tooltip will show when the user interacts with the field. If set to %1$sIcon%2$s, the tooltip will be shown when the user interacts with an icon.', 'quform'),
                                            '<span class="qfb-bold">',
                                            '</span>'
                                        );
                                    ?>
                                </div>
                            </div>
                            <label for="qfb_tooltip_type"><?php esc_html_e('Tooltip trigger', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <select id="qfb_tooltip_type"></select>
                                <p class="qfb-description"><?php esc_html_e('Choose what the user will be interacting with to show the tooltip.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon">
                                <div class="qfb-tooltip-content">
                                    <?php
                                        printf(
                                            /* translators: %1$s: open span tag, %2$s: close span tag */
                                            esc_html__('If set to %1$sInherit%2$s, the setting will be inherited from the global form settings.', 'quform'),
                                            '<span class="qfb-bold">',
                                            '</span>'
                                        );
                                    ?>
                                </div>
                            </div>
                            <label for="qfb_tooltip_event"><?php esc_html_e('Tooltip event', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <select id="qfb_tooltip_event">
                                    <option value="inherit"><?php esc_html_e('Inherit', 'quform'); ?></option>
                                    <option value="hover"><?php esc_html_e('Hover', 'quform'); ?></option>
                                    <option value="click"><?php esc_html_e('Click', 'quform'); ?></option>
                                </select>
                                <p class="qfb-description"><?php esc_html_e('Choose the event that will trigger the tooltip to show.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Choose where to display the label relative to the field. "Inherit" means that the label position will be inherited from the global form settings or parent group/page.', 'quform'); ?></div></div>
                            <label for="qfb_label_position"><?php esc_html_e('Label position', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <select id="qfb_label_position"></select>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon">
                                <div class="qfb-tooltip-content">
                                    <?php
                                        printf(
                                            /* translators: %s: CSS width code example */
                                            esc_html__('Specify the width of the label, any valid CSS width is accepted, e.g. %s.', 'quform'),
                                            '<code>200px</code>'
                                        );
                                    ?>
                                </div>
                            </div>
                            <label for="qfb_label_width"><?php esc_html_e('Label width', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_label_width" class="qfb-width-100">
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Shows the Label as a sub-heading in the default notification email.', 'quform'); ?></div></div>
                            <label for="qfb_show_label_in_email"><?php esc_html_e('Show label in email', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="checkbox" id="qfb_show_label_in_email" class="qfb-toggle">
                                <label for="qfb_show_label_in_email"></label>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Shows the Label as a sub-heading when viewing the entry.', 'quform'); ?></div></div>
                            <label for="qfb_show_label_in_entry"><?php esc_html_e('Show label in entry', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="checkbox" id="qfb_show_label_in_entry" class="qfb-toggle">
                                <label for="qfb_show_label_in_entry"></label>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>

        <div class="qfb-tabs-panel">

            <div class="qfb-element-settings-inner">

                <div class="qfb-settings">

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_logic_enabled"><?php esc_html_e('Enable conditional logic', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="checkbox" id="qfb_logic_enabled" class="qfb-toggle">
                                <label for="qfb_logic_enabled"></label>
                                <p class="qfb-description"><?php esc_html_e('Create rules to show or hide this element depending on the values of other fields.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label><?php esc_html_e('Logic rules', 'quform'); ?></label>
                            <div class="qfb-add-logic-rule-wrap qfb-cf">
                                <a id="qfb-add-logic-rule" class="qfb-button-green"><i class="qfb-mdi qfb-mdi-add_circle"></i><?php esc_html_e('Add logic rule', 'quform'); ?></a>
                            </div>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <div id="qfb-logic" class="qfb-logic qfb-cf"></div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>

        <div class="qfb-tabs-panel">

            <div class="qfb-element-settings-inner">

                <div class="qfb-settings">

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Checks that the submitted value is one of the configured options. Disable this if the options are set dynamically.', 'quform'); ?></div></div>
                            <label for="qfb_in_array_validator"><?php esc_html_e('Validate submitted value', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="checkbox" id="qfb_in_array_validator" class="qfb-toggle">
                                <label for="qfb_in_array_validator"></label>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('The default value is the value that the field has before the user has entered anything.', 'quform'); ?></div></div>
                            <label for="qfb_default_value"><?php esc_html_e('Default value', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <div class="qfb-input-variable">
                                    <input type="text" id="qfb_default_value">
                                    <?php echo $builder->getInsertVariableHtml('qfb_default_value', true); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('The default value is the value that the field has before the user has entered anything.', 'quform'); ?></div></div>
                            <label for="qfb_default_value_textarea"><?php esc_html_e('Default value', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <div class="qfb-textarea-variable">
                                    <?php echo $builder->getInsertVariableHtml('qfb_default_value_textarea', true); ?>
                                    <textarea id="qfb_default_value_textarea"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('The default value is the value that the element is given before the user has changed anything.', 'quform'); ?></div></div>
                            <label for="qfb_default_value_date"><?php esc_html_e('Default value', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_default_value_date" placeholder="YYYY-MM-DD">
                                <p class="qfb-description"><?php esc_html_e('Enter the value in the format YYYY-MM-DD, enter {today} to use the current date.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('The default value is the value that the element is given before the user has changed anything', 'quform'); ?></div></div>
                            <label for="qfb_default_value_time"><?php esc_html_e('Default value', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_default_value_time" placeholder="HH:MM">
                                <p class="qfb-description"><?php esc_html_e('Enter the value in the 24 hour format HH:MM, enter {now} to use the current time.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Allows the default value of the field to be set dynamically via a URL parameter, shortcode attribute or filter hook.', 'quform'); ?></div></div>
                            <label for="qfb_dynamic_default_value"><?php esc_html_e('Dynamic default value', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="checkbox" id="qfb_dynamic_default_value" class="qfb-toggle">
                                <label for="qfb_dynamic_default_value"></label>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon">
                                <div class="qfb-tooltip-content">
                                    <?php
                                        printf(
                                            /* translators: %s: example parameter query string */
                                            esc_html__('This is the name of the parameter that you will use to set the default. For example, in the URL you can set the value using %s.', 'quform'),
                                            '<span class="qfb-bold">?parameter_name=my_value</span>'
                                        );
                                    ?>
                                </div>
                            </div>
                            <label for="qfb_dynamic_key"><?php esc_html_e('Parameter name', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_dynamic_key">
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Choose which type of information that browsers can autofill into this field.', 'quform'); ?></div></div>
                            <label for="qfb_autocomplete"><?php esc_html_e('Autocomplete', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <div class="qfb-autocomplete-left">
                                    <div class="qfb-autocomplete-left-inner">
                                        <input type="text" id="qfb_autocomplete">
                                    </div>
                                </div>
                                <div class="qfb-autocomplete-right">
                                    <select class="qfb-autocomplete-insert" data-target="#qfb_autocomplete">
                                        <option value=""><?php esc_html_e('Insert value', 'quform'); ?></option>
                                        <?php foreach ($builder->getAutocompleteValues() as $value) : ?>
                                            <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($value); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Limits the number of characters that can be entered into the field. The error message can be changed on the Translations tab.', 'quform'); ?></div></div>
                            <label for="qfb_max_length"><?php esc_html_e('Maximum length', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_max_length">
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_upload_minimum_files"><?php esc_html_e('Minimum number of files', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input class="qfb-width-75" type="text" id="qfb_upload_minimum_files">
                                <p class="qfb-description"><?php esc_html_e('Enter the minimum number of uploaded files required, enter 0 to have no specific number of files required. One file will still be required if this field is Required.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_upload_maximum_files"><?php esc_html_e('Maximum number of files', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input class="qfb-width-75" type="text" id="qfb_upload_maximum_files">
                                <p class="qfb-description"><?php esc_html_e('Enter the maximum number of uploaded files allowed, enter 0 for no limit.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_upload_allowed_extensions"><?php esc_html_e('Allowed file extensions', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_upload_allowed_extensions">
                                <p class="qfb-description">
                                    <?php
                                        printf(
                                            /* translators: %s: example file extensions value */
                                            esc_html__('Enter the file extension excluding the dots and separated by commas e.g. %s.', 'quform'),
                                            '<code>jpg, jpeg, png, gif</code>'
                                        );
                                    ?>
                                    <br><br>
                                    <?php if ( ! $options->get('allowAllFileTypes')) : ?>
                                        <?php esc_html_e('To protect your site, potentially risky file types are not allowed to be uploaded in a File Upload field. To stop this protection, enable the option at Forms &rarr; Settings &rarr; Tweaks &amp; Troubleshooting &rarr; Allow uploading all file types.', 'quform'); ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_upload_maximum_size"><?php esc_html_e('Maximum allowed file size', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input class="qfb-width-75" type="text" id="qfb_upload_maximum_size"><span class="qfb-input-suffix"><?php esc_html_e('MB', 'quform'); ?></span>
                                <p class="qfb-description"><?php esc_html_e('Enter the maximum size of a file in MB.', 'quform'); ?> <a href="https://support.themecatcher.net/quform-wordpress-v2/basics/elements/file-upload#maximum-allowed-file-size" target="_blank"><?php esc_html_e('Important information', 'quform'); ?></a>.</p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_save_to_server"><?php esc_html_e('Save uploaded files to the server', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="checkbox" id="qfb_save_to_server" class="qfb-toggle">
                                <label for="qfb_save_to_server"></label>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon">
                                <div class="qfb-tooltip-content">
                                    <div class="qfb-tooltip-title"><?php esc_html_e('Variables', 'quform'); ?></div>
                                    <pre><?php
                                        echo esc_html($builder->formatVariables(array(
                                            '{form_id}' => __('the form ID', 'quform'),
                                            '{year}' => __('the current year', 'quform'),
                                            '{month}' => __('the current month', 'quform'),
                                            '{day}' => __('the current day', 'quform')
                                        )));
                                    ?></pre>
                                </div>
                            </div>
                            <label for="qfb_save_path"><?php esc_html_e('Path to save uploaded files', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_save_path">
                                <p class="qfb-description"><?php esc_html_e('The path to save the files inside the WordPress uploads directory.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_add_to_media_library"><?php esc_html_e('Add to media library', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="checkbox" id="qfb_add_to_media_library" class="qfb-toggle">
                                <label for="qfb_add_to_media_library"></label>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('The range of dates that can be selected.', 'quform'); ?></div></div>
                            <label for="qfb_date_min"><?php esc_html_e('Date range', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <div class="qfb-settings-row qfb-settings-row-2">
                                    <div class="qfb-settings-column">
                                        <label for="qfb_date_min"><?php esc_html_e('Minimum date', 'quform'); ?></label>
                                        <input type="text" id="qfb_date_min" placeholder="YYYY-MM-DD">
                                    </div>
                                    <div class="qfb-settings-column">
                                        <label for="qfb_date_max"><?php esc_html_e('Maximum date', 'quform'); ?></label>
                                        <input type="text" id="qfb_date_max" placeholder="YYYY-MM-DD">
                                    </div>
                                </div>
                                <p class="qfb-description"><?php esc_html_e('Enter the value in the format YYYY-MM-DD, enter {today} to use the current date.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_date_view_start"><?php esc_html_e('Start view', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <select id="qfb_date_view_start">
                                    <option value="month"><?php esc_html_e('Days of the month', 'quform'); ?></option>
                                    <option value="year"><?php esc_html_e('Months of the year', 'quform'); ?></option>
                                    <option value="decade"><?php esc_html_e('Years of the decade', 'quform'); ?></option>
                                    <option value="century"><?php esc_html_e('Decades of the century', 'quform'); ?></option>
                                </select>
                                <p class="qfb-description"><?php esc_html_e('Specifies the start view when the datepicker is first opened.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Specifies the depth of the view that should trigger choosing the date.', 'quform'); ?></div></div>
                            <label for="qfb_date_view_depth"><?php esc_html_e('View depth', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <select id="qfb_date_view_depth">
                                    <option value="month"><?php esc_html_e('Days of the month', 'quform'); ?></option>
                                    <option value="year"><?php esc_html_e('Months of the year', 'quform'); ?></option>
                                    <option value="decade"><?php esc_html_e('Years of the decade', 'quform'); ?></option>
                                    <option value="century"><?php esc_html_e('Decades of the century', 'quform'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_date_show_footer"><?php esc_html_e('Show today link', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="checkbox" id="qfb_date_show_footer" class="qfb-toggle">
                                <label for="qfb_date_show_footer"></label>
                                <p class="qfb-description"><?php esc_html_e('Show a link to choose today\'s date in the footer of the datepicker.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_date_locale"><?php esc_html_e('Locale', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <select id="qfb_date_locale" style="width: 100%;">
                                    <?php foreach ($builder->getLocales() as $key => $locale) : ?>
                                        <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($locale['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="qfb-description"><?php esc_html_e('The Locale determines the language for the Datepicker, and the date format for this element. If set to Default it will use the Locale from Edit Form &rarr; Settings &rarr; Language.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_date_format_js"><?php esc_html_e('Date format (JS)', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_date_format_js">
                                <p class="qfb-description">
                                    <?php
                                        printf(
                                            /* translators: %1$s: open link tag, %2$s: close link tag */
                                            esc_html__('The format of the date when displayed in the form. See %1$sthis page%2$s for how to set a custom date format. If empty, the format will be inherited from the option at Edit Form &rarr; Settings &rarr; Language &rarr; Date & time format (JS) &rarr; Date.', 'quform'),
                                            '<a href="https://docs.telerik.com/kendo-ui/framework/globalization/dateformatting#custom" target="_blank">',
                                            '</a>'
                                        );
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_date_format"><?php esc_html_e('Date format (PHP)', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_date_format">
                                <p class="qfb-description">
                                    <?php
                                        printf(
                                            /* translators: %1$s: open link tag, %2$s: close link tag */
                                            esc_html__('The format of the date when displayed in notifications and when viewing entries. See %1$sthis page%2$s for how to set a custom date format. If empty, the format will be inherited from the option at Edit Form &rarr; Settings &rarr; Language &rarr; Date & time format (PHP) &rarr; Date.', 'quform'),
                                            '<a href="https://wordpress.org/documentation/article/customize-date-and-time-format/" target="_blank">',
                                            '</a>'
                                        );
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('The minimum and maximum time that can be selected.', 'quform'); ?></div></div>
                            <label for="qfb_time_min"><?php esc_html_e('Time range', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <div class="qfb-settings-row qfb-settings-row-2">
                                    <div class="qfb-settings-column">
                                        <label for="qfb_time_min"><?php esc_html_e('Minimum time', 'quform'); ?></label>
                                        <input type="text" id="qfb_time_min" placeholder="HH:MM">
                                    </div>
                                    <div class="qfb-settings-column">
                                        <label for="qfb_time_max"><?php esc_html_e('Maximum time', 'quform'); ?></label>
                                        <input type="text" id="qfb_time_max" placeholder="HH:MM">
                                    </div>
                                </div>
                                <p class="qfb-description"><?php esc_html_e('Enter the value in the 24 hour format HH:MM, enter {now} to use the current time.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_time_interval"><?php esc_html_e('Minute interval', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_time_interval" placeholder="30">
                                <p class="qfb-description"><?php esc_html_e('Enter the minute interval between each time in the list.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_time_locale"><?php esc_html_e('Locale', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <select id="qfb_time_locale" style="width: 100%;">
                                    <?php foreach ($builder->getLocales() as $key => $locale) : ?>
                                        <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($locale['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="qfb-description"><?php esc_html_e('The Locale determines the language for the Timepicker, and the time format for this element. If set to Default it will use the Locale from Edit Form &rarr; Settings &rarr; Language.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_time_format_js"><?php esc_html_e('Time format (JS)', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_time_format_js">
                                <p class="qfb-description">
                                    <?php
                                        printf(
                                            /* translators: %1$s: open link tag, %2$s: close link tag */
                                            esc_html__('The format of the time when displayed in the form. See %1$sthis page%2$s for how to set a custom time format. If empty, the format will be inherited from the option at Edit Form &rarr; Settings &rarr; Language &rarr; Date & time format (JS) &rarr; Time.', 'quform'),
                                            '<a href="https://docs.telerik.com/kendo-ui/framework/globalization/dateformatting#custom" target="_blank">',
                                            '</a>'
                                        );
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_time_format"><?php esc_html_e('Time format (PHP)', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_time_format">
                                <p class="qfb-description">
                                    <?php
                                        printf(
                                            /* translators: %1$s: open link tag, %2$s: close link tag */
                                            esc_html__('The format of the time when displayed in notifications and when viewing entries. See %1$sthis page%2$s for how to set a custom time format. If empty, the format will be inherited from the option at Edit Form &rarr; Settings &rarr; Language &rarr; Date & time format (PHP) &rarr; Time.', 'quform'),
                                            '<a href="https://wordpress.org/documentation/article/customize-date-and-time-format/" target="_blank">',
                                            '</a>'
                                        );
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('If enabled, the datepicker will be opened automatically when the user interacts with the field.', 'quform'); ?></div></div>
                            <label for="qfb_date_auto_open"><?php esc_html_e('Auto open datepicker', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="checkbox" id="qfb_date_auto_open" class="qfb-toggle">
                                <label for="qfb_date_auto_open"></label>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('If enabled, the timepicker will be opened automatically when the user interacts with the field.', 'quform'); ?></div></div>
                            <label for="qfb_time_auto_open"><?php esc_html_e('Auto open timepicker', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="checkbox" id="qfb_time_auto_open" class="qfb-toggle">
                                <label for="qfb_time_auto_open"></label>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('If enabled, the form will be submitted when an option is chosen. This requires JavaScript to be enabled.', 'quform'); ?></div></div>
                            <label for="qfb_submit_on_choice"><?php esc_html_e('Submit on choice', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="checkbox" id="qfb_submit_on_choice" class="qfb-toggle">
                                <label for="qfb_submit_on_choice"></label>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_read_only"><?php esc_html_e('Read only', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="checkbox" id="qfb_read_only" class="qfb-toggle">
                                <label for="qfb_read_only"></label>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('If enabled, the submitted element data will be shown in the default notification email.', 'quform'); ?></div></div>
                            <label for="qfb_show_in_email"><?php esc_html_e('Show in email', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="checkbox" id="qfb_show_in_email" class="qfb-toggle">
                                <label for="qfb_show_in_email"></label>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('The content to display for this HTML element in plain text notification emails.', 'quform'); ?></div></div>
                            <label for="qfb_plain_text_content"><?php esc_html_e('Plain text content', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <textarea id="qfb_plain_text_content"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('If enabled, the content will be shown when viewing an entry.', 'quform'); ?></div></div>
                            <label for="qfb_show_in_entry"><?php esc_html_e('Show in entry', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="checkbox" id="qfb_show_in_entry" class="qfb-toggle">
                                <label for="qfb_show_in_entry"></label>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('If enabled, the submitted element data will be saved to the database and shown when viewing an entry.', 'quform'); ?></div></div>
                            <label for="qfb_save_to_database"><?php esc_html_e('Save to database', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="checkbox" id="qfb_save_to_database" class="qfb-toggle">
                                <label for="qfb_save_to_database"></label>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>

        <div class="qfb-tabs-panel">
            <div class="qfb-element-settings-inner">

                <div class="qfb-settings">

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('The unique identifier, you may need this for advanced usage.', 'quform'); ?></div></div>
                            <label for="qfb-element-unique-id"><?php esc_html_e('Unique ID', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb-element-unique-id" class="qfb-code-input" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Choose who can see this element in the form.', 'quform'); ?></div></div>
                            <label for="qfb_visibility"><?php esc_html_e('Visibility', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <select id="qfb_visibility">
                                    <option value=""><?php esc_html_e('Everyone', 'quform'); ?></option>
                                    <option value="admin-only"><?php esc_html_e('Admin only', 'quform'); ?></option>
                                    <option value="logged-in-only"><?php esc_html_e('Logged in users only', 'quform'); ?></option>
                                    <option value="logged-out-only"><?php esc_html_e('Logged out users only', 'quform'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Filters allow you to strip various characters from the submitted form data.', 'quform'); ?></div></div>
                            <label><?php esc_html_e('Filters', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <?php include QUFORM_TEMPLATE_PATH . '/admin/filter-settings.php'; ?>
                                <div id="qfb-filters"></div>
                                <div id="qfb-filters-empty"><div class="qfb-message-box qfb-message-box-info"><div class="qfb-message-box-inner"><?php esc_html_e('No active filters, add one using the buttons below.', 'quform'); ?></div></div></div>
                                <div class="qfb-add-filters qfb-cf">
                                    <?php
                                        foreach ($builder->getFilters() as $key => $filter) {
                                            echo '<span class="qfb-button qfb-add-filter' . (isset($filter['tooltip']) ? ' qfb-tooltip' : '') . '" data-type="' . esc_attr($key) . '">' . esc_html($filter['name']) . (isset($filter['tooltip']) ? sprintf('<span class="qfb-tooltip-content">%s</span>', esc_html($filter['tooltip'])) : '') . '</span>';
                                        }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Validators checks whether the data entered by the user is valid.', 'quform'); ?></div></div>
                            <label><?php esc_html_e('Validators', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <?php include QUFORM_TEMPLATE_PATH . '/admin/validator-settings.php'; ?>
                                <div id="qfb-validators"></div>
                                <div id="qfb-validators-empty"><div class="qfb-message-box qfb-message-box-info"><div class="qfb-message-box-inner"><?php esc_html_e('No active validators, add one using the buttons below.', 'quform'); ?></div></div></div>
                                <div class="qfb-add-validators qfb-cf">
                                    <?php
                                        foreach ($builder->getValidators() as $key => $validator) {
                                            echo '<span class="qfb-button qfb-add-validator' . (isset($validator['tooltip']) ? ' qfb-tooltip' : '') . '" data-type="' . esc_attr($key) . '">' . esc_html($validator['name']) . (isset($validator['tooltip']) ? sprintf('<span class="qfb-tooltip-content">%s</span>', esc_html($validator['tooltip'])) : '') . '</span>';
                                        }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>

        <div class="qfb-tabs-panel">
            <div class="qfb-element-settings-inner">

                <div class="qfb-settings qfb-translations">

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_none_option_text"><?php esc_html_e('Please select', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_none_option_text">
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_enhanced_select_placeholder"><?php esc_html_e('Please select', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_enhanced_select_placeholder">
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_enhanced_select_no_results_found"><?php esc_html_e('No results found.', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_enhanced_select_no_results_found">
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_name_prefix_none_option_text"><?php esc_html_e('Please select', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_name_prefix_none_option_text">
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_browse_text"><?php echo esc_html_x('Browse...', 'for a file to upload', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_browse_text">
                            </div>
                        </div>
                    </div>

                    <?php
                        // Translations for elements that have built-in validators
                        // 'class' => array('messageKeyConstant' => 'tooltip')
                        $validatorTranslations = array(
                            'Quform_Validator_Required' => array(
                                'REQUIRED' => array()
                            ),
                            'Quform_Validator_Email' => array(
                                'INVALID_FORMAT' => array()
                            ),
                            'Quform_Validator_Captcha' => array(
                                'NOT_MATCH' => array()
                            ),
                            'Quform_Validator_Length' => array(
                                'TOO_LONG' => array(
                                    '%value%' => __('the submitted value', 'quform'),
                                    '%max%' => __('the maximum allowed length', 'quform'),
                                    '%length%' => __('the length of the submitted value', 'quform')
                                )
                            ),
                            'Quform_Validator_FileUpload' => array(
                                'NUM_REQUIRED' => array(
                                    '%min%' => __('the minimum number of required files', 'quform')
                                ),
                                'TOO_MANY' => array(
                                    '%max%' => __('the maximum number of required files', 'quform')
                                ),
                                'TOO_BIG_FILENAME' => array(
                                    '%filename%' => __('the filename', 'quform')
                                ),
                                'TOO_BIG' => array(),
                                'NOT_ALLOWED_TYPE_FILENAME' => array(
                                    '%filename%' => __('the filename', 'quform')
                                ),
                                'NOT_ALLOWED_TYPE' => array()
                            ),
                            'Quform_Validator_Recaptcha' => array(
                                'MISSING_INPUT_SECRET' => array(),
                                'INVALID_INPUT_SECRET' => array(),
                                'MISSING_INPUT_RESPONSE' => array(),
                                'INVALID_INPUT_RESPONSE' => array(),
                                'ERROR' => array(),
                                'SCORE_TOO_LOW' => array()
                            ),
                            'Quform_Validator_Date' => array(
                                'INVALID_DATE' => array(),
                                'TOO_EARLY' => array(
                                    '%min%' => __('the minimum date', 'quform')
                                ),
                                'TOO_LATE' => array(
                                    '%max%' => __('the maximum date', 'quform')
                                )
                            ),
                            'Quform_Validator_Time' => array(
                                'INVALID_TIME' => array(),
                                'TOO_EARLY' => array(
                                    '%min%' => __('the minimum time', 'quform')
                                ),
                                'TOO_LATE' => array(
                                    '%max%' => __('the maximum time', 'quform')
                                )
                            )
                        );
                    ?>

                    <?php
                        foreach ($validatorTranslations as $class => $messages) :
                            foreach ($messages as $constant => $variables) :
                                $key = constant($class . '::' . $constant); ?>
                                <div class="qfb-setting">
                                    <div class="qfb-setting-label">
                                        <?php if (count($variables)) : ?>
                                            <div class="qfb-tooltip-icon">
                                                <div class="qfb-tooltip-content">
                                                    <div class="qfb-tooltip-title"><?php esc_html_e('Variables', 'quform'); ?></div>
                                                    <pre><?php echo esc_html($builder->formatVariables($variables)); ?></pre>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <label for="qfb_message_<?php echo esc_attr($key); ?>"><?php echo esc_html(call_user_func(array($class, 'getMessageTemplates'), $key)); ?></label>
                                    </div>
                                    <div class="qfb-setting-inner">
                                        <div class="qfb-setting-input">
                                            <input type="text" id="qfb_message_<?php echo esc_attr($key); ?>">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                    <?php endforeach; ?>

                </div>

            </div>
        </div>

    </div>

    <div class="qfb-popup-buttons">
        <div title="<?php esc_attr_e('Save', 'quform'); ?>" class="qfb-popup-save-button"><i class="qfb-mdi qfb-mdi-check"></i></div>
        <div title="<?php esc_attr_e('Close', 'quform'); ?>" class="qfb-popup-close-button"><i class="qfb-mdi qfb-mdi-close"></i></div>
        <div title="<?php esc_attr_e('Previous element', 'quform'); ?>" id="qfb-element-settings-prev"><i class="qfb-mdi qfb-mdi-navigate_before"></i></div>
        <div title="<?php esc_attr_e('Next element', 'quform'); ?>" id="qfb-element-settings-next"><i class="qfb-mdi qfb-mdi-navigate_next"></i></div>
    </div>

    <div class="qfb-popup-overlay"></div>
</div>
