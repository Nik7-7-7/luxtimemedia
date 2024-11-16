<?php if (!defined('ABSPATH')) exit; ?><div class="qfb qfb-cf qfb-page-tools-import-form">
    <?php echo $page->getNavHtml(); ?>
    <?php echo $page->getSubNavHtml(); ?>

    <div class="qfb-tools-import-form qfb-cf">

        <div class="qfb-settings">

            <div class="qfb-settings-heading"><i class="qfb-mdi qfb-mdi-playlist_add"></i> <?php esc_html_e('Import form data', 'quform'); ?></div>

            <p class="qfb-description qfb-below-heading"><?php esc_html_e('Paste in the data generated from the Forms &rarr; Export Form page into the box below to import a form.', 'quform'); ?></p>

            <div class="qfb-setting">
                <textarea id="qfb-import-form-data" class="qfb-import-data"></textarea>
            </div>

            <div class="qfb-setting">
                <div id="qfb-import-form-button" class="qfb-button-green"><i class="qfb-mdi qfb-mdi-playlist_add"></i> <?php esc_html_e('Import', 'quform'); ?></div><span id="qfb-import-form-loading"></span>
            </div>

        </div>

    </div>

</div>
