<?php if (!defined('ABSPATH')) exit;
/** @var Quform_Admin_Page_Tools_Uninstall $page */
?><div class="qfb qfb-cf qfb-page-tools-uninstall">
    <?php
        echo $page->getMessagesHtml();
        echo $page->getNavHtml();
        echo $page->getSubNavHtml();
    ?>

    <form method="post">

        <?php wp_nonce_field('quform_uninstall'); ?>

        <div class="qfb-settings">

            <div class="qfb-settings-heading">
                <i class="qfb-icon qfb-icon-trash-o"></i>
                <?php
                    echo esc_html(
                        /* translators: %s: the plugin name */
                        sprintf(__('Uninstall %s', 'quform'),
                        Quform::getPluginName()
                        )
                    );
                ?>
            </div>

            <div class="qfb-setting">

                <div class="qfb-message-box qfb-message-box-error">
                    <div class="qfb-message-box-inner">
                        <?php esc_html_e('WARNING: All forms, entries and settings will be deleted and cannot be recovered unless you have a backup of the database.', 'quform'); ?>
                    </div>
                </div>

            </div>

            <div class="qfb-setting">

                <div class="qfb-setting-label">
                    <label for="qfb_uninstall_confirm"><?php esc_html_e('Confirm', 'quform'); ?></label>
                </div>
                <div class="qfb-setting-inner">
                    <div class="qfb-setting-input">
                        <input type="checkbox" id="qfb_uninstall_confirm" name="qfb_uninstall_confirm" class="qfb-toggle" value="1">
                        <label for="qfb_uninstall_confirm"></label>
                        <p class="qfb-description"><?php esc_html_e('Confirm that you want to delete all forms and settings, and deactivate the plugin. You can delete the plugin afterwards from the Plugins page.', 'quform'); ?></p>
                    </div>
                </div>

            </div>

            <button type="submit" class="qfb-button-green"><i class="qfb-mdi qfb-mdi-delete_forever"></i> <?php esc_attr_e('Uninstall', 'quform'); ?></button>

        </div>

    </form>

</div>
