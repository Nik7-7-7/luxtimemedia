<?php if (!defined('ABSPATH')) exit; ?><div class="qfb qfb-cf qfb-page-tools-export-form">
    <?php echo $page->getNavHtml(); ?>
    <?php echo $page->getSubNavHtml(); ?>

    <div class="qfb-tools-export-form qfb-cf">
        <?php if (count($forms)) : ?>
            <div class="qfb-settings">
                <div class="qfb-settings-heading"><i class="qfb-icon qfb-icon-file-code-o"></i> <?php esc_html_e('Select a form to export', 'quform'); ?></div>

                <div class="qfb-setting">
                    <select id="qfb-export-form">
                        <option value=""><?php esc_html_e('Select a form', 'quform'); ?></option>
                        <?php foreach ($forms as $id => $name) : ?>
                            <option value="<?php echo esc_attr($id); ?>"><?php echo Quform::escape($name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="qfb-setting">
                    <div id="qfb-export-form-button" class="qfb-button-green"><i class="qfb-mdi qfb-mdi-open_in_browser"></i> <?php esc_html_e('Export', 'quform'); ?></div><span id="qfb-export-form-loading"></span>
                </div>

                <div id="qfb-export-form-data-wrap" class="qfb-hidden">

                    <div class="qfb-settings-heading"><?php esc_html_e('Form export data', 'quform'); ?></div>

                    <div class="qfb-setting">
                        <ol>
                            <li><?php esc_html_e('Click inside the box to select all text', 'quform'); ?></li>
                            <li><?php esc_html_e('Copy the text inside the box and paste it into the box on the Forms &rarr; Tools &rarr; Import Form page on another website or into an empty .txt file to make a backup.', 'quform'); ?></li>
                        </ol>
                    </div>

                    <div class="qfb-setting">
                        <textarea id="qfb-export-form-data" class="qfb-export-data"></textarea>
                    </div>
                </div>
            </div>

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
