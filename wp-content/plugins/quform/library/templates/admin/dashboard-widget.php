<?php if (!defined('ABSPATH')) exit; ?><table class="widefat">
    <tr>
        <th class="qfb-db-widget-name-th"><?php esc_html_e('Form', 'quform'); ?></th>
        <th class="qfb-db-widget-unread-th"><?php esc_html_e('Unread', 'quform'); ?></th>
        <th>&nbsp;</th>
    </tr>
    <?php foreach ($forms as $form) : ?>
        <?php $url = esc_url(admin_url('admin.php?page=quform.entries&id=' . $form['id'])); ?>
        <tr>
            <td class="qfb-db-widget-name"><a href="<?php echo $url; ?>"><?php echo Quform::escape($form['name']); ?></a></td>
            <td class="qfb-db-widget-unread"><a href="<?php echo $url; ?>"><?php echo $form['entries']; ?></a></td>
            <td class="qfb-db-widget-link"><a class="qfb-button" href="<?php echo $url; ?>"><span class="dashicons dashicons-email-alt"></span></a></td>
        </tr>
    <?php endforeach; ?>
</table>