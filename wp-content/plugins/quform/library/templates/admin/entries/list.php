<?php if (!defined('ABSPATH')) exit;
/* @var Quform_Admin_Page_Entries_List $page
 * @var Quform_Entry_List_Table $table
 * @var Quform_Form $form
 */
?><div class="qfb qfb-cf">
    <?php
        echo $page->getMessagesHtml();
        echo $page->getNavHtml(array('id' => $form->getId(), 'name' => $form->config('name')));
    ?>

    <form method="get">
        <input type="hidden" name="page" value="quform.entries">
        <input type="hidden" id="qfb_form_id" name="id" value="<?php echo (int) $form->getId(); ?>">

        <?php
            // Remove action results from sortable headers and row actions
            $_SERVER['REQUEST_URI'] = remove_query_arg(array('read', 'unread', 'trashed', 'untrashed', 'deleted', 'error'), $_SERVER['REQUEST_URI']);
        ?>

        <div class="qfb-entries-list-search">
            <?php
                $table->search_box(esc_html__('Search', 'quform'), 'qfb-search-entries');
                echo $table->getLabelSearchHtml();
            ?>
        </div>

        <?php
            $table->views();
            $table->display();
        ?>

    </form>

    <?php if (count($labels)) : ?>
        <div id="qfb-entry-label-set">
            <?php foreach ($labels as $label) : ?>
                <div class="qfb-entry-label" data-label="<?php echo Quform::escape(wp_json_encode($label)); ?>" style="background-color: <?php echo Quform::escape($label['color']); ?>;"><span class="qfb-entry-label-name"><?php echo Quform::escape($label['name']); ?></span><i class="qfb-icon qfb-icon-check"></i></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <div id="qfb-entries-table-settings" class="qfb-popup">
        <div id="qfb-entries-table-settings-inner" class="qfb-popup-content">
            <div class="qfb-settings">

                <div class="qfb-settings-heading"><i class="qfb-mdi qfb-mdi-list"></i><?php esc_html_e('Table options', 'quform'); ?></div>

                <div class="qfb-setting">
                    <div class="qfb-setting-label">
                        <label><?php esc_html_e('Labels', 'quform'); ?></label>
                        <div id="qfb-entry-labels-add" class="qfb-button-green"><i class="qfb-mdi qfb-mdi-add_circle"></i><?php esc_html_e('Add New', 'quform'); ?></div>
                    </div>
                    <div class="qfb-setting-inner">
                        <div class="qfb-setting-input">
                            <div id="qfb-entry-labels-edit">
                                <?php
                                    foreach ($labels as $label) {
                                        echo $page->getEntryLabelEditHtml($label);
                                    }
                                ?>
                            </div>
                            <div id="qfb-entry-labels-edit-empty"<?php echo count($labels) ? ' class="qfb-hidden"' : ''; ?>><div class="qfb-message-box qfb-message-box-info"><div class="qfb-message-box-inner"><?php esc_html_e('There are no labels, add one using the "Add New" button.', 'quform'); ?></div></div></div>
                        </div>
                    </div>
                </div>

                <div class="qfb-setting">
                    <div class="qfb-setting-label">
                        <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Drag and drop the columns to customize the table layout.', 'quform'); ?></div></div>
                        <label><?php esc_html_e('Column layout', 'quform'); ?></label>
                    </div>
                    <div class="qfb-setting-inner">
                        <div class="qfb-setting-input">
                            <div id="qfb-setup-entries-table-layout" class="qfb-button qfb-hidden"><?php esc_html_e('Edit table column layout', 'quform'); ?></div>
                            <span id="qfb-setup-entries-table-layout-loading"></span>
                            <div class="qfb-entries-table-layout qfb-cf">
                                <?php echo $table->getColumnLayoutSortableHtml(); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="qfb-setting">
                    <div class="qfb-setting-label">
                        <label for="qfb_entries_per_page"><?php esc_html_e('Entries per page', 'quform'); ?></label>
                    </div>
                    <div class="qfb-setting-inner">
                        <div class="qfb-setting-input">
                            <input type="text" id="qfb_entries_per_page" value="<?php echo Quform::escape($perPage); ?>">
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="qfb-popup-buttons">
            <div class="qfb-popup-save-button"><i class="qfb-mdi qfb-mdi-check"></i></div>
            <div class="qfb-popup-close-button"><i class="qfb-mdi qfb-mdi-close"></i></div>
        </div>

        <div class="qfb-popup-overlay"></div>
    </div>
</div>
