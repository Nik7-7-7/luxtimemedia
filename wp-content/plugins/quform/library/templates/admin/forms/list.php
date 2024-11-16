<?php if (!defined('ABSPATH')) exit;
/** @var Quform_Admin_Page_Forms_List $page */
/** @var Quform_Form_List_Table $table */
?><div class="qfb qfb-cf">
    <?php
        echo $page->getMessagesHtml();
        echo $page->getNavHtml();
    ?>
    <form method="get">
        <input type="hidden" name="page" value="quform.forms">
        <?php
            // Remove action results from sortable headers and row actions
            $_SERVER['REQUEST_URI'] = remove_query_arg(array('activated', 'deactivated', 'duplicated', 'trashed', 'untrashed', 'deleted', 'error'), $_SERVER['REQUEST_URI']);

            $table->search_box(esc_html__('Search', 'quform'), 'qfb-search-forms');
            $table->views();
            $table->display();
        ?>
    </form>

    <div id="qfb-forms-table-settings" class="qfb-popup">
        <div class="qfb-popup-content">
            <div class="qfb-settings">

                <div class="qfb-settings-heading"><i class="qfb-mdi qfb-mdi-list"></i><?php esc_html_e('Table options', 'quform'); ?></div>

                <div class="qfb-setting">
                    <div class="qfb-setting-label">
                        <label for="qfb_forms_per_page"><?php esc_html_e('Forms per page', 'quform'); ?></label>
                    </div>
                    <div class="qfb-setting-inner">
                        <div class="qfb-setting-input">
                            <input type="text" id="qfb_forms_per_page" value="<?php echo Quform::escape($perPage); ?>">
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

    <div id="qfb-add-new-form" class="qfb-popup">
        <div id="qfb-add-new-form-inner" class="qfb-popup-content">
            <div class="qfb-forms-add">
                <div class="qfb-settings">

                    <div class="qfb-settings-heading"><?php esc_html_e('Create a form', 'quform'); ?></div>
                    <div class="qfb-settings-subheading"><?php esc_html_e('Enter a name to help you identify the form', 'quform'); ?></div>

                    <div class="qfb-setting qfb-setting-label-above">
                        <div class="qfb-setting-inner">
                            <div class="qfb-setting-input">
                                <input type="text" id="qfb-forms-add-name" placeholder="<?php esc_attr_e('e.g. Contact form', 'quform'); ?>" maxlength="64">
                            </div>
                        </div>
                    </div>

                    <div class="qfb-setting">
                        <div class="qfb-forms-add-buttons qfb-cf">
                            <div id="qfb-forms-add-submit"><i class="qfb-mdi qfb-mdi-add_circle"></i><?php esc_html_e('Add Form', 'quform'); ?></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="qfb-popup-buttons">
            <div class="qfb-popup-close-button"><i class="qfb-mdi qfb-mdi-close"></i></div>
        </div>

        <div class="qfb-popup-overlay"></div>
    </div>
</div>
