<?php if (!defined('ABSPATH')) exit;
/* @var Quform_Admin_Page_Settings $page */
/* @var Quform_License $license */
/* @var Quform_Options $options */
?><div class="qfb qfb-cf">
    <?php
        echo $page->getMessagesHtml();
        echo $page->getNavHtml();
    ?>

    <form method="post" class="qfb-settings-form">

        <div class="qfb-fixed-buttons">
            <div id="qfb-fixed-save-button" class="qfb-animated-save-button" title="<?php esc_attr_e('Save', 'quform'); ?>"><i class="qfb-icon qfb-icon-floppy-o"></i></div>
        </div>

        <div id="qfb-settings-tabs">
            <ul class="qfb-tabs-nav qfb-cf">
                <li class="qfb-current-tab"><a><?php esc_html_e('Global', 'quform'); ?></a></li>
                <li><a><?php esc_html_e('License &amp; Updates', 'quform'); ?></a></li>
                <li><a><?php esc_html_e('reCAPTCHA', 'quform'); ?></a></li>
                <li><a><?php esc_html_e('Performance', 'quform'); ?></a></li>
                <li><a><?php esc_html_e('Permissions', 'quform'); ?></a></li>
                <li><a><?php esc_html_e('Custom CSS & JS', 'quform'); ?></a></li>
                <li><a><?php esc_html_e('Tweaks & Troubleshooting', 'quform'); ?></a></li>
                <li><a><?php esc_html_e('Referral Program', 'quform'); ?></a></li>
            </ul>

            <div class="qfb-tabs-panel">

                <div class="qfb-settings">

                    <div class="qfb-settings-heading"><i class="qfb-mdi qfb-mdi-mail_outline"></i><?php esc_html_e('Email', 'quform'); ?></div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label"><label for="qfb_default_email_address" class="qfb-bold"><?php esc_html_e('Default recipient', 'quform'); ?><span class="qfb-required">*</span></label></div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <div class="qfb-settings-row qfb-settings-row-2">
                                    <div class="qfb-settings-column">
                                        <input type="text" id="qfb_default_email_address" placeholder="<?php esc_attr_e('Email address (required)', 'quform'); ?>" value="<?php echo Quform::escape($options->get('defaultEmailAddress')); ?>">
                                    </div>
                                    <div class="qfb-settings-column">
                                        <input type="text" id="qfb_default_email_name" placeholder="<?php esc_attr_e('Name (optional)', 'quform'); ?>" value="<?php echo Quform::escape($options->get('defaultEmailName')); ?>">
                                    </div>
                                </div>
                                <p class="qfb-description"><?php esc_html_e('Set the default recipient email address for notifications.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label"><label for="qfb_default_from_email_address" class="qfb-bold"><?php esc_html_e('Default "From"', 'quform'); ?><span class="qfb-required">*</span></label></div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <div class="qfb-settings-row qfb-settings-row-2">
                                    <div class="qfb-settings-column">
                                        <input type="text" id="qfb_default_from_email_address" placeholder="<?php esc_attr_e('Email address (required)', 'quform'); ?>" value="<?php echo Quform::escape($options->get('defaultFromEmailAddress')); ?>">
                                    </div>
                                    <div class="qfb-settings-column">
                                        <input type="text" id="qfb_default_from_email_name" placeholder="<?php esc_attr_e('Name (optional)', 'quform'); ?>" value="<?php echo Quform::escape($options->get('defaultFromEmailName')); ?>">
                                    </div>
                                </div>

                                <p class="qfb-description"><?php esc_html_e('Set the default "From" email address for notifications. It is recommended to set this to an existing email address with the same domain as the site.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-settings-heading"><i class="qfb-icon qfb-icon-globe"></i><?php esc_html_e('Regional', 'quform'); ?></div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label"><label for="qfb_locale"><?php esc_html_e('Locale', 'quform'); ?></label></div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <select id="qfb_locale" class="qfb-width-400">
                                    <?php foreach (Quform::getLocales() as $key => $locale) : ?>
                                        <option value="<?php echo esc_attr($key); ?>" <?php selected($options->get('locale'), $key); ?>><?php echo esc_html($locale['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="qfb-description"><?php esc_html_e('The Locale determines the language for Datepickers and Timepickers, and the date and time formats for the forms.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label"><label for="qfb_rtl"><?php esc_html_e('RTL support', 'quform'); ?></label></div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <select id="qfb_rtl">
                                    <option value="" <?php selected($options->get('rtl'), ''); ?>><?php esc_html_e('Autodetect', 'quform'); ?></option>
                                    <option value="enabled" <?php selected($options->get('rtl'), 'enabled'); ?>><?php esc_html_e('Enabled', 'quform'); ?></option>
                                    <option value="disabled" <?php selected($options->get('rtl'), 'disabled'); ?>><?php esc_html_e('Disabled', 'quform'); ?></option>
                                </select>
                                <p class="qfb-description"><?php esc_html_e('Enable this option if the site language is RTL, you can also override this for each form at Edit Form &rarr; Settings &rarr; Language.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label"><label><?php esc_html_e('Date & time format (JS)', 'quform'); ?></label></div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <div class="qfb-sub-setting-inline qfb-cf">
                                    <div class="qfb-sub-setting">
                                        <label for="qfb_date_format_js"><?php esc_html_e('Date', 'quform'); ?></label>
                                        <input type="text" id="qfb_date_format_js" value="<?php echo Quform::escape($options->get('dateFormatJs')); ?>">
                                    </div>
                                    <div class="qfb-sub-setting">
                                        <label for="qfb_time_format_js"><?php esc_html_e('Time', 'quform'); ?></label>
                                        <input type="text" id="qfb_time_format_js" value="<?php echo Quform::escape($options->get('timeFormatJs')); ?>">
                                    </div>
                                    <div class="qfb-sub-setting">
                                        <label for="qfb_date_time_format_js"><?php esc_html_e('DateTime', 'quform'); ?></label>
                                        <input type="text" id="qfb_date_time_format_js" value="<?php echo Quform::escape($options->get('dateTimeFormatJs')); ?>">
                                    </div>
                                </div>
                                <p class="qfb-description">
                                    <?php
                                        printf(
                                            /* translators: %1$s: open link tag, %2$s: close link tag */
                                            esc_html__('Sets the default format for dates and times when displayed in the form. See %1$sthis page%2$s for more information about custom formats. If empty, the Locale will determine the formats according to regional standards.', 'quform'),
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
                                <div class="qfb-sub-setting-inline qfb-cf">
                                    <div class="qfb-sub-setting">
                                        <label for="qfb_date_format"><?php esc_html_e('Date', 'quform'); ?></label>
                                        <input type="text" id="qfb_date_format" value="<?php echo Quform::escape($options->get('dateFormat')); ?>">
                                    </div>
                                    <div class="qfb-sub-setting">
                                        <label for="qfb_time_format"><?php esc_html_e('Time', 'quform'); ?></label>
                                        <input type="text" id="qfb_time_format" value="<?php echo Quform::escape($options->get('timeFormat')); ?>">
                                    </div>
                                    <div class="qfb-sub-setting">
                                        <label for="qfb_date_time_format"><?php esc_html_e('DateTime', 'quform'); ?></label>
                                        <input type="text" id="qfb_date_time_format" value="<?php echo Quform::escape($options->get('dateTimeFormat')); ?>">
                                    </div>
                                </div>
                                <p class="qfb-description">
                                    <?php
                                        printf(
                                            /* translators: %1$s: open link tag, %2$s: close link tag */
                                            esc_html__('Sets the default format for dates and times when displayed in notification emails and when viewing entries. See %1$sthis page%2$s for more information about custom formats. If empty, the Locale will determine the formats according to regional standards.', 'quform'),
                                            '<a href="https://wordpress.org/documentation/article/customize-date-and-time-format/" target="_blank">',
                                            '</a>'
                                        );
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <div class="qfb-tabs-panel">

                <div class="qfb-settings">

                    <div class="qfb-settings-heading"><i class="qfb-mdi qfb-mdi-vpn_key"></i><?php esc_html_e('Product license', 'quform'); ?></div>

                    <p class="qfb-description qfb-below-heading">
                        <?php
                            printf(
                                /* translators: %1$s: open span tag, %2$s: close span tag, %3$s: open link tag, %4$s: close link tag */
                                esc_html__('A valid license key entitles you to support and enables automatic upgrades. %1$sA license key may only be used for one installation of WordPress at a time%2$s. If you have previously verified a license key for another website, and use it again here, the plugin will be licensed here and become unlicensed on the other website. Please enter your CodeCanyon Quform license key, you can find your key by following the instructions on %3$sthis page%4$s.', 'quform'),
                                '<span class="qfb-bold">',
                                '</span>',
                                '<a href="https://support.themecatcher.net/quform-wordpress-v2/basics/getting-started/activating-the-license" target="_blank">',
                                '</a>'
                            );
                        ?>
                    </p>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label"><label><?php esc_html_e('License status', 'quform'); ?></label></div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <div class="qfb-license-status">
                                    <div class="qfb-message-box <?php echo $license->isValid() ? 'qfb-message-box-success' : 'qfb-message-box-error'; ?>">
                                        <div class="qfb-message-box-inner"><?php echo esc_html($license->getStatusString()); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label"><label for="qfb_purchase_code"><?php esc_html_e('Enter license key', 'quform'); ?></label></div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <div class="qfb-cf"><input id="qfb_purchase_code" type="text" class="qfb-width-400"><span class="qfb-button" id="qfb-settings-verify"><?php esc_html_e('Verify', 'quform'); ?></span><span id="qfb-settings-verify-loading"></span> </div>
                                <div id="qfb-settings-verify-message" class="qfb-settings-verify-message">
                                    <div class="qfb-message-box"><div class="qfb-message-box-inner"></div></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-settings-heading"><i class="qfb-mdi qfb-mdi-update"></i><?php esc_html_e('Updates', 'quform'); ?></div>

                    <?php if ($license->getStatus() == 'bundled') : ?>
                        <div class="qfb-message-box qfb-message-box-info">
                            <div class="qfb-message-box-inner">
                                <?php
                                    printf(
                                        /* translators: %1$s: open link tag, %2$s: close link tag */
                                        esc_html__('Automatic updates are not possible because the plugin was bundled in a package. You should check with the package author to get the latest version of Quform. Alternatively you can %1$spurchase a license%2$s to enable automatic updates.', 'quform'),
                                        '<a href="https://www.quform.com/buy.php"  target="_blank">',
                                        '</a>'
                                    )
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label"><label><?php esc_html_e('Check for update', 'quform'); ?></label></div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <div class="qfb-cf">
                                    <span class="qfb-floated-text-beside-button">
                                        <?php
                                            printf(
                                                /* translators: %s: the current plugin version */
                                                esc_html__('You are using version %s', 'quform'),
                                                QUFORM_VERSION
                                            );
                                        ?>
                                    </span>
                                    <span class="qfb-button" id="qfb-settings-update"><?php esc_html_e('Check for update', 'quform'); ?></span><span id="qfb-settings-update-loading"></span></div>
                                <div id="qfb-settings-update-message" class="qfb-settings-update-message">
                                    <div class="qfb-message-box"><div class="qfb-message-box-inner"></div></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <div class="qfb-tabs-panel">

                <div class="qfb-settings">

                    <div class="qfb-settings-heading"><i class="qfb-mdi qfb-mdi-face"></i><?php esc_html_e('reCAPTCHA', 'quform'); ?></div>

                    <p class="qfb-description qfb-below-heading">
                        <?php
                            printf(
                                /* translators: %1$s: open link tag, %2$s: close link tag */
                                esc_html__('To use the reCAPTCHA element in a form you need to %1$screate the API keys%2$s. Once you have the Site and Secret key, enter them below.', 'quform'),
                                '<a href="https://www.google.com/recaptcha/admin" target="_blank">',
                                '</a>'
                            );
                       ?>
                    </p>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label"><label for="qfb_recaptcha_site_key"><?php esc_html_e('Site key', 'quform'); ?></label></div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_recaptcha_site_key" value="<?php echo Quform::escape($options->get('recaptchaSiteKey')); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label"><label for="qfb_recaptcha_secret_key"><?php esc_html_e('Secret key', 'quform'); ?></label></div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_recaptcha_secret_key" value="<?php echo Quform::escape($options->get('recaptchaSecretKey')); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="qfb-settings-heading"><i class="qfb-icon qfb-icon-hand-paper-o"></i><?php esc_html_e('hCaptcha', 'quform'); ?></div>

                    <p class="qfb-description qfb-below-heading">
                        <?php
                            printf(
                                /* translators: %1$s: open link tag, %2$s: close link tag, %3$s: open link tag, %4$s: close link tag */
                                esc_html__('To use the hCaptcha element in a form you need will need to enter the %1$sSite key%2$s and %3$sSecret key%4$s from the hCaptcha settings.', 'quform'),
                                '<a href="https://dashboard.hcaptcha.com/sites" target="_blank">',
                                '</a>',
                                '<a href="https://dashboard.hcaptcha.com/settings" target="_blank">',
                                '</a>'
                            );
                       ?>
                    </p>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label"><label for="qfb_hcaptcha_site_key"><?php esc_html_e('Site key', 'quform'); ?></label></div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_hcaptcha_site_key" value="<?php echo Quform::escape($options->get('hcaptchaSiteKey')); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label"><label for="qfb_hcaptcha_secret_key"><?php esc_html_e('Secret key', 'quform'); ?></label></div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_hcaptcha_secret_key" value="<?php echo Quform::escape($options->get('hcaptchaSecretKey')); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="qfb-settings-heading"><i class="qfb-mdi qfb-mdi-cloud"></i><?php esc_html_e('Cloudflare Turnstile', 'quform'); ?></div>

                    <p class="qfb-description qfb-below-heading">
                        <?php
                            printf(
                                /* translators: %1$s: open link tag, %2$s: close link tag */
                                esc_html__('To use the Cloudflare Turnstile element in a form you need will need to enter the Site key and Secret key from the %1$sCloudflare Turnstile settings%2$s.', 'quform'),
                                '<a href="https://dash.cloudflare.com/" target="_blank">',
                                '</a>'
                            );
                       ?>
                    </p>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label"><label for="qfb_turnstile_site_key"><?php esc_html_e('Site key', 'quform'); ?></label></div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_turnstile_site_key" value="<?php echo Quform::escape($options->get('turnstileSiteKey')); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label"><label for="qfb_turnstile_secret_key"><?php esc_html_e('Secret key', 'quform'); ?></label></div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_turnstile_secret_key" value="<?php echo Quform::escape($options->get('turnstileSecretKey')); ?>">
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <div class="qfb-tabs-panel">

                <div class="qfb-settings">

                    <div class="qfb-settings-heading"><i class="qfb-icon qfb-icon-sliders"></i><?php esc_html_e('When to load scripts/styles', 'quform'); ?></div>

                    <p class="qfb-description qfb-below-heading"><?php esc_html_e('Choose which pages to load the plugin scripts and styles, these are required for the form to work properly, so you can choose only the pages containing a form to speed up the other pages on your site. Autodetect will only load the scripts if a form is found in the page content or in a widget.', 'quform'); ?></p>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label"><label for="qfb_load_scripts"><?php esc_html_e('When to load scripts/styles', 'quform'); ?></label></div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <select id="qfb_load_scripts">
                                    <option value="always" <?php selected($options->get('loadScripts'), 'always'); ?>><?php esc_html_e('Always', 'quform'); ?></option>
                                    <option value="autodetect" <?php selected($options->get('loadScripts'), 'autodetect'); ?>><?php esc_html_e('Autodetect', 'quform'); ?></option>
                                    <option value="custom" <?php selected($options->get('loadScripts'), 'custom'); ?>><?php esc_html_e('Only on specific pages', 'quform'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting<?php echo $options->get('loadScripts') == 'custom' ? '' : ' qfb-hidden'; ?>">
                        <div class="qfb-setting-label"><label for="qfb_load_scripts_custom"><?php esc_html_e('Choose pages', 'quform'); ?></label></div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <select id="qfb_load_scripts_custom" data-placeholder="<?php esc_attr_e('Choose pages', 'quform'); ?>" multiple style="width: 100%;">
                                    <?php
                                        foreach ($options->get('loadScriptsCustom') as $postId) {
                                            printf(
                                                '<option value="%s" selected="selected">%s</option>',
                                                esc_attr($postId),
                                                esc_html(Quform::getPostTitleById((int) $postId))
                                            );
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-settings-heading"><i class="qfb-mdi qfb-mdi-merge_type"></i><?php esc_html_e('Combine scripts', 'quform'); ?></div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label"><label for="qfb_combine_css" class="qfb-bold"><?php esc_html_e('Combine CSS', 'quform'); ?></label></div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="checkbox" class="qfb-toggle" id="qfb_combine_css" <?php checked($options->get('combineCss')); ?>>
                                <label for="qfb_combine_css"></label>
                                <p class="qfb-description"><?php esc_html_e('Combine the CSS into a single file to increase page loading speed.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label"><label for="qfb_combine_js" class="qfb-bold"><?php esc_html_e('Combine JavaScript', 'quform'); ?></label></div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="checkbox" class="qfb-toggle" id="qfb_combine_js" <?php checked($options->get('combineJs')); ?>>
                                <label for="qfb_combine_js"></label>
                                <p class="qfb-description"><?php esc_html_e('Combine the JavaScript into a single file to increase page loading speed.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-settings-heading"><i class="qfb-mdi qfb-mdi-power_settings_new"></i><?php esc_html_e('Disable third-party scripts', 'quform'); ?></div>

                    <p class="qfb-description qfb-below-heading"><?php esc_html_e('You can disable any stylesheet or script used by the Quform plugin by turning the option off below. Disabling the script will disable the functionality of the feature, unless the script is provided from another source. Most scripts are only loaded when they are used by an active form.', 'quform'); ?></p>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label><?php esc_html_e('CSS', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <?php foreach ($disableableStyles as $key => $style) : ?>
                                <div class="qfb-disableable-script qfb-cf<?php echo isset($style['hidden']) && $style['hidden'] ? ' qfb-hidden' : ''; ?>">
                                    <div class="qfb-disableable-script-toggle">
                                        <input type="checkbox" class="qfb-mini-toggle" id="qfb_enable_style_<?php echo esc_attr($key); ?>" <?php checked(! $style['disabled']); ?>>
                                        <label for="qfb_enable_style_<?php echo esc_attr($key); ?>"></label>
                                    </div>
                                    <label class="qfb-disableable-script-label" for="qfb_enable_style_<?php echo esc_attr($key); ?>">
                                        <span class="qfb-disableable-script-name"><?php echo esc_html($style['name']); ?></span>
                                        <span class="qfb-disableable-script-version"><?php echo esc_html($style['version']); ?></span>
                                        <span class="qfb-disableable-script-tooltip"><i class="qfb-mdi qfb-mdi-help_outline"></i><span class="qfb-tooltip-content"><?php echo esc_html($style['tooltip']); ?></span></span>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label><?php esc_html_e('JavaScript', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <?php foreach ($disableableScripts as $key => $script) : ?>
                                <div class="qfb-disableable-script qfb-cf<?php echo isset($script['hidden']) && $script['hidden'] ? ' qfb-hidden' : ''; ?>">
                                    <div class="qfb-disableable-script-toggle">
                                        <input type="checkbox" class="qfb-mini-toggle" id="qfb_enable_script_<?php echo esc_attr($key); ?>" <?php checked(! $script['disabled']); ?>>
                                        <label for="qfb_enable_script_<?php echo esc_attr($key); ?>"></label>
                                    </div>
                                    <label class="qfb-disableable-script-label" for="qfb_enable_script_<?php echo esc_attr($key); ?>">
                                        <span class="qfb-disableable-script-name"><?php echo esc_html($script['name']); ?></span>
                                        <span class="qfb-disableable-script-version"><?php echo esc_html($script['version']); ?></span>
                                        <span class="qfb-disableable-script-tooltip"><i class="qfb-mdi qfb-mdi-help_outline"></i><span class="qfb-tooltip-content"><?php echo esc_html($script['tooltip']); ?></span></span>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <div class="qfb-tabs-panel">

                <div class="qfb-settings">

                    <div class="qfb-settings-heading"><i class="qfb-mdi qfb-mdi-group_add"></i><?php esc_html_e('Permissions', 'quform'); ?></div>

                    <p class="qfb-description qfb-below-heading"><?php esc_html_e('These options allow you to give permissions for other Roles to access parts of the plugin.', 'quform'); ?></p>

                    <div class="qfb-setting">

                        <div class="qfb-table qfb-permissions-table">
                            <div class="qfb-table-row">
                                <div class="qfb-table-cell"></div>
                                <?php foreach ($caps as $capName) : ?>
                                    <div class="qfb-table-cell qfb-permissions-capability-name"><?php echo esc_html($capName); ?></div>
                                <?php endforeach; ?>
                            </div>

                            <?php foreach ($roles as $roleKey => $role) : ?>
                                <?php
                                    if ($roleKey === 'administrator') {
                                        continue;
                                    }
                                ?>
                                <div class="qfb-table-row">
                                    <div class="qfb-table-cell qfb-permissions-role-name"><?php echo esc_html($role['name']); ?></div>
                                    <?php foreach ($caps as $cap => $capName) : ?>
                                        <?php
                                            $id = sprintf('qfb-capability-%s-%s', $roleKey, $cap);
                                            $checked = isset($role['capabilities'][$cap]) && $role['capabilities'][$cap] ? ' checked' : '';
                                        ?>
                                        <div class="qfb-table-cell">
                                            <input type="checkbox" id="<?php echo esc_attr($id); ?>" class="qfb-permissions-capability qfb-mini-toggle" data-capability="<?php echo esc_attr($cap); ?>" data-role="<?php echo esc_attr($roleKey); ?>" <?php echo $checked; ?>>
                                            <label for="<?php echo esc_attr($id); ?>"></label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>

                        </div>

                    </div>

                </div>

            </div>

            <div class="qfb-tabs-panel">

                <div class="qfb-settings">

                    <div class="qfb-settings-heading"><i class="qfb-icon qfb-icon-code"></i><?php esc_html_e('Custom CSS', 'quform'); ?></div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label"><label><?php esc_html_e('Custom CSS', 'quform'); ?></label></div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <div id="qfb-custom-css-tabs">

                                    <ul class="qfb-tabs-nav qfb-cf">
                                        <li><a><?php esc_html_e('All devices', 'quform'); ?></a></li>
                                        <li><a><?php esc_html_e('Tablets only', 'quform'); ?></a></li>
                                        <li><a><?php esc_html_e('Phones only', 'quform'); ?></a></li>
                                    </ul>

                                    <div class="qfb-tabs-panel">
                                        <textarea id="qfb_custom_css" class="qfb-code-input"><?php echo Quform::escape($options->get('customCss')); ?></textarea>
                                        <p class="qfb-description"><?php esc_html_e('Enter any custom CSS for all devices.', 'quform'); ?></p>
                                    </div>

                                    <div class="qfb-tabs-panel">
                                        <textarea id="qfb_custom_css_tablet" class="qfb-code-input"><?php echo Quform::escape($options->get('customCssTablet')); ?></textarea>
                                        <p class="qfb-description">
                                            <?php
                                                printf(
                                                    /* translators: %1$s: lower device pixel width, %2$s: higher device pixel width */
                                                    esc_html__('Enter any custom CSS for devices with a width from %1$s to %2$s.', 'quform'),
                                                    '569px',
                                                    '1024px'
                                                );
                                            ?>
                                        </p>
                                    </div>

                                    <div class="qfb-tabs-panel">
                                        <textarea id="qfb_custom_css_phone" class="qfb-code-input"><?php echo Quform::escape($options->get('customCssPhone')); ?></textarea>
                                        <p class="qfb-description">
                                            <?php
                                                printf(
                                                    /* translators: %s: the device pixel width */
                                                    esc_html__('Enter any custom CSS for devices with a width up to %s.', 'quform'),
                                                    '568px'
                                                );
                                            ?>
                                        </p>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-settings-heading"><i class="qfb-icon qfb-icon-code"></i><?php esc_html_e('Custom JavaScript', 'quform'); ?></div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label"><label for="qfb_custom_js"><?php esc_html_e('Custom JavaScript', 'quform'); ?></label></div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <textarea id="qfb_custom_js" class="qfb-code-input"><?php echo Quform::escape($options->get('customJs')); ?></textarea>
                                <p class="qfb-description">
                                    <?php
                                        printf(
                                            /* translators: %s: example of the tag to not enter */
                                            esc_html__('Enter any custom JavaScript to be added to the site, do not enter %s tags.', 'quform'),
                                            '<code>&lt;script&gt;</code>'
                                        );
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <div class="qfb-tabs-panel">

                <div class="qfb-settings">

                    <div class="qfb-settings-heading"><i class="qfb-mdi qfb-mdi-build"></i><?php esc_html_e('Tweaks', 'quform'); ?></div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label"><label for="qfb_popup_enabled" class="qfb-bold"><?php esc_html_e('Enable popup script', 'quform'); ?></label></div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="checkbox" class="qfb-toggle" id="qfb_popup_enabled" <?php checked($options->get('popupEnabled')); ?>>
                                <label for="qfb_popup_enabled"></label>
                                <p class="qfb-description"><?php esc_html_e('This option is enabled automatically when you add a popup form to a page or when you add a Quform Popup widget. If this did not happen for some reason you can enable this option to manually enable the popup script.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting<?php echo $options->get('popupEnabled') ? '' : ' qfb-hidden'; ?>">
                        <div class="qfb-setting-label"><label for="qfb_popup_script"><?php esc_html_e('Popup script', 'quform'); ?></label></div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <select id="qfb_popup_script">
                                    <option value="fancybox-3" <?php selected($options->get('popupScript'), 'fancybox-3'); ?>><?php esc_html_e('Fancybox 3', 'quform'); ?></option>
                                    <option value="fancybox-2" <?php selected($options->get('popupScript'), 'fancybox-2'); ?>><?php esc_html_e('Fancybox 2 (default)', 'quform'); ?></option>
                                    <option value="fancybox-1" <?php selected($options->get('popupScript'), 'fancybox-1'); ?>><?php esc_html_e('Fancybox 1', 'quform'); ?></option>
                                    <option value="magnific-popup" <?php selected($options->get('popupScript'), 'magnific-popup'); ?>><?php esc_html_e('Magnific Popup', 'quform'); ?></option>
                                </select>
                                <p class="qfb-description"><?php esc_html_e('Select which script to use when displaying the form in a popup.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label"><label for="qfb_raw_fix"><?php esc_html_e('Raw fix', 'quform'); ?></label></div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input id="qfb_raw_fix" type="checkbox" class="qfb-toggle" <?php checked($options->get('rawFix')); ?>>
                                <label for="qfb_raw_fix"></label>
                                <p class="qfb-description"><?php esc_html_e('Try enabling this if you have unwanted extra spacing in the form.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label"><label for="qfb_scroll_offset"><?php esc_html_e('Scroll offset', 'quform'); ?></label></div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input id="qfb_scroll_offset" type="text" value="<?php echo Quform::escape($options->get('scrollOffset')); ?>" class="qfb-width-75">
                                <p class="qfb-description"><?php esc_html_e('The number of pixels above the target message where smooth scrolling should end. If you have a fixed or sticky header on your site you should increase the number so that messages are not hidden below the header.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label"><label for="qfb_scroll_speed"><?php esc_html_e('Scroll speed', 'quform'); ?></label></div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input id="qfb_scroll_speed" type="text" value="<?php echo Quform::escape($options->get('scrollSpeed')); ?>" class="qfb-width-75">
                                <p class="qfb-description"><?php esc_html_e('The animation speed for smooth scrolling, in milliseconds (1000 = 1 second).', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label"><label for="qfb_allow_all_file_types"><?php esc_html_e('Allow uploading all file types', 'quform'); ?></label></div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input id="qfb_allow_all_file_types" type="checkbox" class="qfb-toggle" <?php checked($options->get('allowAllFileTypes')); ?>>
                                <label for="qfb_allow_all_file_types"></label>
                                <p class="qfb-description"><?php esc_html_e('To protect your site, potentially risky file types are not allowed to be uploaded in a File Upload field, enable this option to stop the protection.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label"><label for="qfb_show_edit_link"><?php esc_html_e('Show edit link', 'quform'); ?></label></div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input id="qfb_show_edit_link" type="checkbox" class="qfb-toggle" <?php checked($options->get('showEditLink')); ?>>
                                <label for="qfb_show_edit_link"></label>
                                <p class="qfb-description"><?php esc_html_e('Show a link to edit the form at the bottom of forms.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label"><label for="qfb_csrf_protection"><?php esc_html_e('CSRF protection', 'quform'); ?></label></div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input id="qfb_csrf_protection" type="checkbox" class="qfb-toggle" <?php checked($options->get('csrfProtection')); ?>>
                                <label for="qfb_csrf_protection"></label>
                                <p class="qfb-description"><?php esc_html_e('Protect form submissions against cross-site request forgery.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label"><label for="qfb_support_page_caching"><?php esc_html_e('Support page caching', 'quform'); ?></label></div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input id="qfb_support_page_caching" type="checkbox" class="qfb-toggle" <?php checked($options->get('supportPageCaching')); ?>>
                                <label for="qfb_support_page_caching"></label>
                                <p class="qfb-description"><?php esc_html_e('Fixes issues with form submissions when the page is cached.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label"><label for="qfb_toolbar_menu"><?php esc_html_e('Toolbar menu', 'quform'); ?></label></div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input id="qfb_toolbar_menu" type="checkbox" class="qfb-toggle" <?php checked($options->get('toolbarMenu')); ?>>
                                <label for="qfb_toolbar_menu"></label>
                                <p class="qfb-description"><?php esc_html_e('Adds a form management menu to the toolbar.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label"><label for="qfb_dashboard_widget"><?php esc_html_e('Dashboard widget', 'quform'); ?></label></div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input id="qfb_dashboard_widget" type="checkbox" class="qfb-toggle" <?php checked($options->get('dashboardWidget')); ?>>
                                <label for="qfb_dashboard_widget"></label>
                                <p class="qfb-description"><?php esc_html_e('Adds a widget to the WordPress dashboard to see unread entries.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label"><label for="qfb_insert_form_button"><?php esc_html_e('Insert form button', 'quform'); ?></label></div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input id="qfb_insert_form_button" type="checkbox" class="qfb-toggle" <?php checked($options->get('insertFormButton')); ?>>
                                <label for="qfb_insert_form_button"></label>
                                <p class="qfb-description"><?php esc_html_e('Adds a button to insert a form above the WordPress editor.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label"><label for="qfb_prevent_fouc"><?php esc_html_e('Prevent FOUC', 'quform'); ?></label></div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input id="qfb_prevent_fouc" type="checkbox" class="qfb-toggle" <?php checked($options->get('preventFouc')); ?>>
                                <label for="qfb_prevent_fouc"></label>
                                <p class="qfb-description"><?php esc_html_e('Hides the form until it has been set up to prevent a flash of unstyled content (FOUC).', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label"><label for="qfb_secure_api_requests"><?php esc_html_e('Secure API requests', 'quform'); ?></label></div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input id="qfb_secure_api_requests" type="checkbox" class="qfb-toggle" <?php checked($options->get('secureApiRequests')); ?>>
                                <label for="qfb_secure_api_requests"></label>
                                <p class="qfb-description"><?php esc_html_e('If you are having problems verifying the license key or updating the plugin you can try turning this off. Requests made to the Quform API will be sent via HTTP instead of HTTPS.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label"><label for="qfb_save_entries"><?php esc_html_e('Save entries', 'quform'); ?></label></div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input id="qfb_save_entries" type="checkbox" class="qfb-toggle" <?php checked($options->get('saveEntries')); ?>>
                                <label for="qfb_save_entries"></label>
                                <p class="qfb-description"><?php esc_html_e('Turn off this option to stop saving submitted form data on all forms.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label"><label for="qfb_save_ip_addresses"><?php esc_html_e('Save IP addresses', 'quform'); ?></label></div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input id="qfb_save_ip_addresses" type="checkbox" class="qfb-toggle" <?php checked($options->get('saveIpAddresses')); ?>>
                                <label for="qfb_save_ip_addresses"></label>
                                <p class="qfb-description"><?php esc_html_e('Turn off this option to stop IP addresses being saved with the form entry data.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label"><label for="qfb_always_show_full_dates"><?php esc_html_e('Always show full dates', 'quform'); ?></label></div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input id="qfb_always_show_full_dates" type="checkbox" class="qfb-toggle" <?php checked($options->get('alwaysShowFullDates')); ?>>
                                <label for="qfb_always_show_full_dates"></label>
                                <p class="qfb-description"><?php esc_html_e('When viewing entries, only the time is shown if the entry was submitted today. Enable this option to always show the full date.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-settings-heading"><i class="qfb-mdi qfb-mdi-cached"></i><?php esc_html_e('Cache', 'quform'); ?></div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label"><label><?php esc_html_e('Script cache', 'quform'); ?></label></div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <div class="qfb-rebuild-script-cache-wrap qfb-cf">
                                    <div class="qfb-button" id="qfb-rebuild-script-cache"><?php esc_html_e('Rebuild script cache', 'quform'); ?></div><span id="qfb-rebuild-script-cache-loading"></span>
                                </div>
                                <p class="qfb-description"><?php esc_html_e('Rebuilds the form custom CSS files and the feature cache which ensures that all of the scripts needed by the active forms are loaded on the site.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-settings-heading"><i class="qfb-mdi qfb-mdi-verified_user"></i><?php esc_html_e('Server compatibility', 'quform'); ?></div>

                    <?php foreach ($requirements as $requirement) : ?>

                        <div class="qfb-setting">
                            <div class="qfb-setting-label"><label><?php echo esc_html($requirement['name']); ?></label></div>
                            <div class="qfb-setting-inner">
                                <div class="qfb-setting-input">
                                    <div class="qfb-cf">
                                        <div class="qfb-server-requirement-state">
                                            <?php if (isset($requirement['error'])) : ?>
                                                <i class="qfb-mdi qfb-mdi-warning qfb-server-requirement-warning"></i>
                                            <?php else : ?>
                                                <i class="qfb-mdi qfb-mdi-done qfb-server-requirement-ok"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="qfb-server-requirement-info">
                                            <div class="qfb-server-requirement-info-inner">
                                                <code><?php echo esc_html($requirement['info']); ?></code>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if (isset($requirement['error'])) : ?>
                                        <div class="qfb-server-requirement-message qfb-cf"><div class="qfb-server-requirement-error"><?php echo esc_html($requirement['error']); ?></div><div class="qfb-server-requirement-help"><a href="https://support.themecatcher.net/quform-wordpress-v2/troubleshooting/common-problems/server-compatibility" target="_blank" title="<?php esc_attr_e('Get help with this error', 'quform'); ?>"><i class="qfb-icon qfb-icon-life-ring"></i></a></div></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>

                </div>

            </div>

            <div class="qfb-tabs-panel">

                <div class="qfb-settings">

                    <div class="qfb-settings-heading"><i class="qfb-mdi qfb-mdi-attach_money"></i><?php esc_html_e('Make money and support Quform!', 'quform'); ?></div>

                    <p class="qfb-description qfb-below-heading">
                        <?php
                            printf(
                                /* translators: %%: percentage sign (must be doubled), %1$s: open link tag to program, %2$s: close link tag, %3$s: open link tag to Quform, %4$s: close link tag, %5$s: open link tag to program terms, %6$s: close link tag */
                                esc_html__('Enable this option and you will receive 30%% of the first deposit or purchase amount from any referrals when users click on the referral link. You will first need to sign up for the %1$sEnvato Market affiliate program%2$s, then create a link to the %3$sQuform Landing Page%4$s. This affiliate program is run by Envato, we have no control over payments or issues that may arise, so you would need to contact Envato if you need help. See the %5$sAffiliate Program General Terms%6$s for more information.', 'quform'),
                                '<a href="https://envato.com/market/affiliate-program/">',
                                '</a>',
                                '<a href="https://codecanyon.net/item/quform-wordpress-form-builder/706149">',
                                '</a>',
                                '<a href="https://envato.com/legal/affiliate/" target="_blank">',
                                '</a>'
                            );
                        ?>
                    </p>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_referral_enabled"><?php esc_html_e('Display a referral link', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="checkbox" class="qfb-toggle" id="qfb_referral_enabled" <?php checked($options->get('referralEnabled')); ?>>
                                <label for="qfb_referral_enabled"></label>
                                <p class="qfb-description"><?php esc_html_e('Displays a Quform referral link under forms, with the text you specify below.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_referral_text"><?php esc_html_e('Referral link text', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_referral_text" value="<?php echo Quform::escape($options->get('referralText')); ?>">
                                <p class="qfb-description"><?php esc_html_e("This is the text that will link to the Quform purchase page, it's displayed under your form.", 'quform'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-setting-label">
                            <label for="qfb_referral_link"><?php esc_html_e('Referral link', 'quform'); ?></label>
                        </div>
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb_referral_link" value="<?php echo Quform::escape($options->get('referralLink')); ?>">
                                <p class="qfb-description">
                                    <?php
                                        printf(
                                            /* translators: %s: example link URL */
                                            esc_html__('Enter your Envato Market referral link to the Quform Landing Page, for example: %s', 'quform'),
                                            '<code>https://1.envato.market/qqmKb</code>'
                                        );
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>

        <div class="qfb-save-settings-wrap qfb-cf"><span id="qfb-save-settings" class="qfb-button-green"><i class="qfb-icon qfb-icon-floppy-o"></i> <?php esc_attr_e('Save', 'quform'); ?></span><span class="qfb-save-settings-loading"></span></div>
    </form>
</div>
