<?php if (!defined('ABSPATH')) exit; ?><div class="qfb qfb-cf qfb-page-tools-migrate">
    <?php echo $page->getNavHtml(); ?>
    <?php echo $page->getSubNavHtml(); ?>

    <div class="qfb-tools-migrate qfb-cf">

        <div class="qfb-settings">

            <div class="qfb-settings-heading"><i class="qfb-icon qfb-icon-suitcase"></i> <?php esc_html_e('Migrate from Quform 1.x', 'quform'); ?></div>

            <p class="qfb-description qfb-below-heading"><?php esc_html_e('The migration tool will copy forms and entries from Quform 1.x into Quform 2.x. It will not copy existing widgets or shortcodes from the 1.x version, you will need to manually add these after the migration is complete.', 'quform'); ?></p>

            <?php if (count($oldForms)) : ?>
                <div class="qfb-setting">
                    <div class="qfb-setting-label">
                        <label for="qfb_migrate_forms"><?php esc_html_e('Migrate forms', 'quform'); ?></label>
                    </div>
                    <div class="qfb-setting-inner">
                        <div class="qfb-setting-input">
                            <select id="qfb_migrate_forms">
                                <option value="all"><?php esc_html_e('All forms', 'quform'); ?></option>
                                <option value="specific"><?php esc_html_e('Only specific forms...', 'quform'); ?></option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="qfb-setting qfb-hidden">
                    <div class="qfb-setting-label">
                        <label for="qfb_migrate_forms_custom"><?php esc_html_e('Which forms?', 'quform'); ?></label>
                    </div>
                    <div class="qfb-setting-inner">
                        <div class="qfb-setting-input">
                            <select id="qfb_migrate_forms_custom" multiple>
                                <?php foreach ($oldForms as $config) : ?>
                                    <option value="<?php echo (int) $config['id']; ?>"><?php echo Quform::escape($config['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="qfb-setting">
                    <div class="qfb-setting-label">
                        <label for="qfb_migrate_entries"><?php esc_html_e('Migrate entries', 'quform'); ?></label>
                    </div>
                    <div class="qfb-setting-inner">
                        <div class="qfb-setting-input">
                            <input type="checkbox" id="qfb_migrate_entries" class="qfb-toggle" checked>
                            <label for="qfb_migrate_entries"></label>
                        </div>
                    </div>
                </div>

                <div class="qfb-setting">
                    <div class="qfb-setting-label">
                        <label for="qfb_migrate_license"><?php esc_html_e('Migrate license key', 'quform'); ?></label>
                    </div>
                    <div class="qfb-setting-inner">
                        <div class="qfb-setting-input">
                            <input type="checkbox" id="qfb_migrate_license" class="qfb-toggle" checked>
                            <label for="qfb_migrate_license"></label>
                        </div>
                    </div>
                </div>

                <div class="qfb-setting">
                    <div class="qfb-setting-label">
                        <label for="qfb_migrate_recaptcha_keys"><?php esc_html_e('Migrate reCAPTCHA keys', 'quform'); ?></label>
                    </div>
                    <div class="qfb-setting-inner">
                        <div class="qfb-setting-input">
                            <input type="checkbox" id="qfb_migrate_recaptcha_keys" class="qfb-toggle" checked>
                            <label for="qfb_migrate_recaptcha_keys"></label>
                        </div>
                    </div>
                </div>

                <div class="qfb-setting">
                    <div id="qfb-migrate-start" class="qfb-button-green"><i class="qfb-icon qfb-icon-suitcase"></i> <?php esc_html_e('Start migration', 'quform'); ?></div>
                </div>

            <?php else : ?>

                <div class="qfb-message-box qfb-message-box-info"><div class="qfb-message-box-inner"><?php esc_html_e('Migration is not possible because no forms from Quform 1.x were found in the database.', 'quform'); ?></div></div>

            <?php endif; ?>

            <div class="qfb-settings-heading"><i class="qfb-mdi qfb-mdi-playlist_add"></i> <?php esc_html_e('Import a single Quform 1.x form', 'quform'); ?></div>

            <p class="qfb-description qfb-below-heading"><?php esc_html_e('Paste in the data generated from the Forms &rarr; Export Form page into the box below to import a form.', 'quform'); ?></p>

            <div class="qfb-setting">
                <textarea id="qfb-import-form-data" class="qfb-import-data"></textarea>
            </div>

            <div class="qfb-setting">
                <div id="qfb-import-form-button" class="qfb-button-green"><i class="qfb-mdi qfb-mdi-playlist_add"></i> <?php esc_html_e('Import', 'quform'); ?></div>
            </div>

        </div>

    </div>

    <div id="qfb-migrate-progress" class="qfb-popup">
        <div id="qfb-migrate-progress-inner" class="qfb-popup-content">

            <div class="qfb-migrate-progress-bar"><div id="qfb-migrate-progress-bar-inner" class="qfb-migrate-progress-bar-inner"></div></div>

            <div class="qfb-migrate-current-task">
                <div id="qfb-migrate-current-task" class="qfb-message-box qfb-message-box-info"><div id="qfb-migrate-current-task-text" class="qfb-message-box-inner"></div></div>
            </div>

            <div id="qfb-migrate-stop" class="qfb-migrate-stop qfb-button-blue"><i class="qfb-mdi qfb-mdi-pan_tool"></i><span id="qfb-migrate-stop-text"><?php esc_html_e('Stop migration', 'quform'); ?></span></div>

            <div id="qfb-migrate-close" class="qfb-migrate-close qfb-button-blue"><i class="qfb-mdi qfb-mdi-close"></i><span><?php esc_html_e('Close', 'quform'); ?></span></div>

            <div id="qfb-migrate-errors" class="qfb-migrate-errors"></div>

        </div>

        <div class="qfb-popup-overlay"></div>
    </div>

</div>
