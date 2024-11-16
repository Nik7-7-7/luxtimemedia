<?php if (!defined('ABSPATH')) exit;
/** @var Quform_Admin_Page_Tools_Home $page */
/** @var array $tools */
?><div class="qfb qfb-cf qfb-page-tools-home">
    <?php
        echo $page->getMessagesHtml();
        echo $page->getNavHtml();
        echo $page->getSubNavHtml();
    ?>

    <div class="qfb-tools qfb-cf">
        <?php if (count($tools)) : ?>
            <?php foreach ($tools as $tool) : ?>
                <div class="qfb-tool">
                    <div class="qfb-tool-box qfb-box">
                        <div class="qfb-tool-icon"><a href="<?php echo esc_url($tool['url']); ?>"><?php echo $tool['icon']; ?></a></div>
                        <div class="qfb-tool-title"><a href="<?php echo esc_url($tool['url']); ?>" title="<?php echo esc_attr($tool['title']); ?>"><?php echo esc_html($tool['title']); ?></a></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="qfb-message-box qfb-message-box-info">
                <div class="qfb-message-box-inner">
                    <p><?php esc_html_e('You do not have permission to access any tools.', 'quform'); ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
