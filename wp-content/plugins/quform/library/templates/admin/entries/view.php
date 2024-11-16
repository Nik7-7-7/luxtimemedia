<?php if (!defined('ABSPATH')) exit;
/* @var bool $showEmptyFields */
/* @var Quform_Admin_Page_Entries_View $page */
/* @var Quform_Options $options */
/* @var Quform_Form $form */
/* @var array $entry */
/* @var array $labels */
/* @var array $notifications */
?><div id="top" class="qfb qfb-cf">
    <?php
        do_action('quform_entry_view_pre_display', $form, $entry);
        do_action('quform_entry_view_pre_display_' . $form->getId(), $form, $entry);
        echo $page->getNavHtml(array('id' => $form->getId(), 'name' => $form->config('name')));
    ?>

    <?php if (current_user_can('quform_edit_entries')) : ?>

        <div class="qfb-fixed-buttons">
            <?php if (count($notifications) && current_user_can('quform_resend_notifications')) : ?>
                <div id="qfb-show-resend-notifications" title="<?php esc_attr_e('Resend notifications', 'quform'); ?>"><i class="qfb-icon qfb-icon-envelope"></i></div>
            <?php endif; ?>
            <a href="<?php echo esc_url(add_query_arg(array('sp' => 'edit'))); ?>" title="<?php esc_attr_e('Edit', 'quform'); ?>"><i class="qfb-icon qfb-icon-pencil"></i></a>
        </div>

        <?php if (count($notifications)) : ?>
            <div id="qfb-resend-notifications" class="qfb-popup" data-eid="<?php echo esc_attr($entry['id']); ?>">
                <div id="qfb-resend-notifications-inner" class="qfb-popup-content">

                    <div class="qfb-settings">

                        <div class="qfb-settings-heading"><i class="qfb-mdi qfb-mdi-mail_outline"></i><?php esc_html_e('Resend Notifications', 'quform'); ?></div>

                        <div class="qfb-setting">
                            <div class="qfb-setting-label">
                                <label><?php esc_html_e('Notifications', 'quform'); ?><span class="qfb-required">*</span></label>
                            </div>
                            <div class="qfb-setting-inner">
                                <div id="qfb-resend-notifications-identifiers" class="qfb-setting-input">

                                    <div>
                                        <input type="checkbox" class="qfb-resend-notifications-identifier-select-all" id="qfb-resend-notifications-identifier-select-all">
                                        <label for="qfb-resend-notifications-identifier-select-all"><?php esc_html_e('All', 'quform'); ?></label>
                                    </div>

                                    <?php foreach ($notifications as $notification) : ?>

                                        <div>
                                            <input type="checkbox" class="qfb-resend-notifications-identifier" id="<?php echo esc_attr('qfb-resend-notification-' . $notification->getIdentifier()); ?>" value="<?php echo esc_attr($notification->getIdentifier()); ?>">
                                            <label for="<?php echo esc_attr('qfb-resend-notification-' . $notification->getIdentifier()); ?>"><?php echo $notification->config('name'); ?></label>
                                        </div>

                                    <?php endforeach; ?>

                                </div>
                            </div>
                        </div>

                        <div class="qfb-resend-notifications-button-container">
                            <span id="qfb-resend-notifications-button" class="qfb-button-green"><i class="qfb-icon qfb-icon-send"></i><?php esc_html_e('Resend', 'quform'); ?></span>
                            <span id="qfb-resend-notifications-loading" class="qfb-loading-spinner"></span>
                        </div>

                    </div>
                </div>

                <div class="qfb-popup-buttons">
                    <div class="qfb-popup-close-button"><i class="qfb-mdi qfb-mdi-close"></i></div>
                </div>

                <div class="qfb-popup-overlay"></div>
            </div>
        <?php endif; ?>

    <?php endif; ?>

    <div class="qfb-cf qfb-entry-wrap">
        <div class="qfb-entry-left">
            <div class="qfb-box">
                <div class="qfb-entry-show-empty-wrap"><form><label><input type="checkbox" value="1" <?php checked($showEmptyFields, '1'); ?> name="show_empty_fields" id="qfb-entry-show-empty-fields"> <?php esc_html_e('Show empty fields', 'quform'); ?></label></form></div>
                <h3 class="qfb-entry-heading qfb-settings-heading"><i class="qfb-mdi qfb-mdi-message"></i><?php esc_html_e('Submitted form data', 'quform'); ?></h3>
                <table class="qfb-entry-table">
                    <?php
                        do_action('quform_entry_view_table_start', $form, $entry);
                        do_action('quform_entry_view_table_start_' . $form->getId(), $form, $entry);

                        foreach ($form->getRecursiveIterator(RecursiveIteratorIterator::SELF_FIRST) as $element) {
                            if ( ! $element instanceof Quform_Element_Field && ! $element instanceof Quform_Element_Container && ! $element instanceof Quform_Element_Html) {
                                continue;
                            }

                            // Skip hidden elements
                            if ($element->isHidden()) {
                                continue;
                            }

                            if ($element instanceof Quform_Element_Html) {
                                if ($element->config('showInEntry')) {
                                    echo sprintf('<tr class="qfb-entry-row qfb-entry-row-html qfb-entry-row-html-%1$s"><td>%2$s</td></tr>', $element->getIdentifier(), $element->getContent());
                                }

                                continue;
                            }

                            // Skip empty elements
                            if ($element->isEmpty() && ! $showEmptyFields) {
                                continue;
                            }

                            if ($element instanceof Quform_Element_Group) {
                                if ($element->config('showLabelInEntry') && Quform::isNonEmptyString($label = $element->getLabel())) {
                                    echo sprintf(
                                        '<tr class="qfb-entry-row qfb-entry-row-%1$s qfb-entry-row-%1$s-%2$s"><th><div class="qfb-entry-group-head">%3$s</div></th></tr>',
                                        $element->config('type'),
                                        $element->getIdentifier(),
                                        Quform::escape($label)
                                    );
                                }
                            } else if ($element instanceof Quform_Element_Field) {
                                if ($element->config('saveToDatabase')) {
                                    echo sprintf('<tr class="qfb-entry-row qfb-entry-row-field-label qfb-entry-row-field-label-%1$s"><th><div class="qfb-entry-element-label">%2$s</div></th></tr>', $element->getIdentifier(), Quform::escape($element->getAdminLabel()));
                                    echo sprintf('<tr class="qfb-entry-row qfb-entry-row-field-value qfb-entry-row-field-value-%1$s"><td>%2$s</td></tr>', $element->getIdentifier(), $element->getValueHtml());
                                }
                            }
                        }

                        do_action('quform_entry_view_table_end', $form, $entry);
                        do_action('quform_entry_view_table_end_' . $form->getId(), $form, $entry);
                    ?>
                </table>
            </div>
        </div>
        <div class="qfb-entry-right">
            <div class="qfb-box">
                <h3 class="qfb-entry-heading qfb-settings-heading"><i class="qfb-mdi qfb-mdi-announcement"></i><?php esc_html_e('Additional information', 'quform'); ?></h3>
                <table class="qfb-entry-table">
                    <?php
                        do_action('quform_entry_view_info_table_start', $form, $entry);
                        do_action('quform_entry_view_info_table_start_' . $form->getId(), $form, $entry);
                    ?>
                    <tr>
                        <th><?php esc_html_e('Form', 'quform'); ?></th>
                        <td>
                            <?php
                                printf(
                                    '<a href="%s">%s</a>',
                                    esc_url(admin_url(sprintf('admin.php?page=quform.forms&sp=edit&id=%d', $form->getId()))),
                                    Quform::isNonEmptyString($form->config('name')) ? esc_html($form->config('name')) : esc_html__('(no title)', 'quform')
                                );
                            ?>
                        </td>
                    </tr>
                    <?php
                        $keys = array(
                            'created_at' => __('Date', 'quform'),
                            'id' => __('Entry ID', 'quform'),
                            'form_url' => __('Form URL', 'quform'),
                            'referring_url' => __('Referring URL', 'quform'),
                            'post_id' => __('Page', 'quform'),
                            'created_by' => __('User', 'quform'),
                            'ip' => __('IP address', 'quform')
                        );

                        foreach ($keys as $key => $label) {
                            if (($value = Quform::get($entry, $key)) && Quform::isNonEmptyString($value) || $showEmptyFields) {
                                if (Quform::isNonEmptyString($value)) {
                                    switch ($key) {
                                        case 'created_at':
                                            $value = $options->formatDate($value);
                                            break;
                                        case 'form_url':
                                        case 'referring_url':
                                            $value = '<a href="' . esc_url($value) . '" target="_blank">' . esc_html($value) . '</a>';
                                            break;
                                        case 'created_by':
                                            $user = get_user_by('id', $value);

                                            if ($user instanceof WP_User) {
                                                $link = get_edit_user_link($user->ID);

                                                if ( ! empty($link)) {
                                                    $value = '<a href="' . esc_url($link) . '" title="' . esc_attr('View user profile', 'quform') . '" target="_blank">' . esc_html($user->user_login) . '</a>';
                                                } else {
                                                    $value = esc_html($user->user_login);
                                                }
                                            } else {
                                                $value = esc_html($value);
                                            }
                                            break;
                                        case 'post_id':
                                            $post = get_post($value);

                                            if ($post instanceof WP_Post) {
                                                $link = get_permalink($post->ID);

                                                if ( ! empty($link)) {
                                                    $value = '<a href="' . esc_url($link) . '" title="' . esc_attr('View page', 'quform') . '" target="_blank">' . esc_html(get_the_title($post->ID)) . '</a>';
                                                } else {
                                                    $value = esc_html(get_the_title($post->ID));
                                                }
                                            } else {
                                                $value = esc_html($value);
                                            }
                                            break;
                                        default:
                                            $value = esc_html($value);
                                            break;
                                    }
                                }

                                echo '<tr><th>' . esc_html($label) . '</th><td>' . $value .'</td></tr>';
                            }
                        }
                    ?>
                    <?php if (count($labels)) : ?>
                        <tr>
                            <th scope="row"><?php esc_html_e('Labels', 'quform'); ?></th>
                            <td class="qfb-single-entry-labels" data-entry-id="<?php echo esc_attr($entry['id']); ?>">
                                <?php echo $page->getEntryLabelsHtml($entry['labels']); ?>
                                <div id="qfb-entry-label-set">
                                    <?php foreach ($labels as $label) : ?>
                                        <div class="qfb-entry-label" data-label="<?php echo Quform::escape(wp_json_encode($label)); ?>" style="background-color: <?php echo Quform::escape($label['color']); ?>;"><span class="qfb-entry-label-name"><?php echo Quform::escape($label['name']); ?></span><i class="qfb-icon qfb-icon-check"></i></div>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php
                        do_action('quform_entry_view_info_table_end', $form, $entry);
                        do_action('quform_entry_view_info_table_end_' . $form->getId(), $form, $entry);
                    ?>
                </table>
            </div>
        </div>
    </div>
    <?php
        do_action('quform_entry_view_post_display', $form, $entry);
        do_action('quform_entry_view_post_display_' . $form->getId(), $form, $entry);
    ?>
    <?php if (current_user_can('quform_edit_entries')) : ?>
        <div class="qfb-entries-view-buttons">
            <a href="<?php echo esc_url(add_query_arg(array('sp' => 'edit'))); ?>" class="qfb-button qfb-button-blue"><i class="qfb-icon qfb-icon-pencil"></i><?php esc_html_e('Edit', 'quform'); ?></a>
        </div>
    <?php endif; ?>
</div>
