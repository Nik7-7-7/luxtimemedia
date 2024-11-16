<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Admin_InsertForm
{
    /**
     * @var Quform_Repository
     */
    protected $repository;

    /**
     * @var Quform_Options
     */
    protected $options;

    /**
     * @var boolean
     */
    protected $printedAssets = false;

    /**
     * @param  Quform_Repository  $repository
     * @param  Quform_Options     $options
     */
    public function __construct(Quform_Repository $repository, Quform_Options $options)
    {
        $this->repository = $repository;
        $this->options = $options;
    }

    /**
     * Register the CSS file for the insert button
     */
    public function registerScripts()
    {
        wp_register_style('quform-insert-form', Quform::adminUrl('css/insert-form.min.css'), array(), QUFORM_VERSION);
    }

    /**
     * Displays the insert form button
     */
    public function button()
    {
        if ( ! $this->options->get('insertFormButton') || ! current_user_can('quform_list_forms')) {
            return;
        }

        // Don't display the button in an editor in the form builder page
        if (isset($_GET['page']) && $_GET['page'] == 'quform.forms') {
            return;
        }

        if ( ! $this->printedAssets) {
            wp_print_styles('quform-insert-form');
            $this->printedAssets = true;
        }

        printf(
            '<button type="button" class="button qfb-insert-form-trigger" onclick="%s" data-l10n="%s"><span></span>%s</button>',
            sprintf("!function(o){window.quformInsertForm?window.quformInsertForm.loadPopup():jQuery.getScript('%s',function(){window.quformInsertForm.init(o)})}(this);", esc_js(esc_url(Quform::adminUrl('js/insert-form.min.js')))),
            Quform::escape(wp_json_encode(array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'selectForm' => __('Please select a form first', 'quform'),
                'couldNotSendToEditor' => __('The shortcode could not be sent to the editor, please copy it from the preview area and paste it into the editor', 'quform')
            ))),
            esc_html__('Add Form', 'quform')
        );
    }

    /**
     * Displays the insert form popup
     */
    public function display()
    {
        $orderBy = get_user_meta(get_current_user_id(), 'quform_forms_order_by', true);
        $order = get_user_meta(get_current_user_id(), 'quform_forms_order', true);
        $forms = $this->repository->formsToSelectArray(null, $orderBy, $order);
        ?>
        <div class="qfb-popup qfb-insert-form-popup">
            <div class="qfb-popup-content">
                <span class="qfb-insert-form-cancel"><span></span></span>
                <div class="qfb-settings-heading"><?php esc_html_e('Insert a form', 'quform'); ?></div>
                <?php if (count($forms)) : ?>
                    <div class="qfb-sub-setting">
                        <select id="qfb-insert-form-id">
                            <option value=""><?php esc_html_e('Select a form', 'quform'); ?></option>
                            <?php foreach ($forms as $id => $name) : ?>
                                <option value="<?php echo Quform::escape($id); ?>"><?php echo Quform::escape($name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="qfb-sub-setting">
                        <div class="qfb-sub-setting-label">
                            <label for="qfb-insert-form-show-title"><?php esc_html_e('Show form title', 'quform'); ?></label>
                        </div>
                        <div class="qfb-sub-setting-inner">
                            <div class="qfb-sub-setting-input">
                                <input type="checkbox" id="qfb-insert-form-show-title" class="qfb-mini-toggle" checked>
                                <label for="qfb-insert-form-show-title"></label>
                            </div>
                        </div>
                    </div>
                    <div class="qfb-sub-setting">
                        <div class="qfb-sub-setting-label">
                            <label for="qfb-insert-form-show-description"><?php esc_html_e('Show form description', 'quform'); ?></label>
                        </div>
                        <div class="qfb-sub-setting-inner">
                            <div class="qfb-sub-setting-input">
                                <input type="checkbox" id="qfb-insert-form-show-description" class="qfb-mini-toggle" checked>
                                <label for="qfb-insert-form-show-description"></label>
                            </div>
                        </div>
                    </div>
                    <div class="qfb-sub-setting">
                        <div class="qfb-sub-setting-label">
                            <label for="qfb-insert-form-popup"><?php esc_html_e('Popup form', 'quform'); ?></label>
                        </div>
                        <div class="qfb-sub-setting-inner">
                            <div class="qfb-sub-setting-input">
                                <input type="checkbox" id="qfb-insert-form-popup" class="qfb-mini-toggle">
                                <label for="qfb-insert-form-popup"></label>
                            </div>
                        </div>
                    </div>
                    <div class="qfb-sub-setting">
                        <div class="qfb-sub-setting-label">
                            <label for="qfb-insert-form-content"><?php esc_html_e('Content', 'quform'); ?></label>
                        </div>
                        <div class="qfb-sub-setting-inner">
                            <div class="qfb-sub-setting-input">
                                <textarea id="qfb-insert-form-content"></textarea>
                                <p class="qfb-description"><?php esc_html_e('The text or HTML to trigger the popup, shortcodes can also be used.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="qfb-sub-setting">
                        <div class="qfb-sub-setting-label">
                            <label for="qfb-insert-form-width"><?php esc_html_e('Width (optional)', 'quform'); ?></label>
                        </div>
                        <div class="qfb-sub-setting-inner">
                            <div class="qfb-sub-setting-input">
                                <input type="text" id="qfb-insert-form-width">
                                <p class="qfb-description"><?php esc_html_e('The width of the popup, any CSS width or number is accepted.', 'quform'); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="qfb-sub-setting">
                        <div class="qfb-sub-setting-label">
                            <label for="qfb-insert-form-options"><?php esc_html_e('Options (optional)', 'quform'); ?></label>
                        </div>
                        <div class="qfb-sub-setting-inner">
                            <div class="qfb-sub-setting-input">
                                <input type="text" id="qfb-insert-form-options">
                                <p class="qfb-description">
                                    <?php
                                        printf(
                                            /* translators: %1$s: open link tag, %2$s: close link tag */
                                            esc_html__('JSON encoded options to pass to the popup script, %1$sexamples%2$s.', 'quform'),
                                            '<a href="https://support.themecatcher.net/quform-wordpress-v2/guides/customization/popup-script-options" target="_blank">',
                                            '</a>'
                                        );
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="qfb-insert-form-button-wrap qfb-cf">
                        <button type="button" class="button button-primary qfb-insert-form-go"><?php esc_html_e('Insert', 'quform'); ?></button>
                    </div>
                    <div class="qfb-insert-form-preview">
                        <p><?php esc_html_e('If you are having trouble inserting the form, copy and paste the code below into the page content.', 'quform'); ?></p>
                        <input type="text" id="qfb-insert-form-preview" readonly>
                    </div>
                <?php else : ?>
                    <?php
                        if (current_user_can('quform_add_forms')) {
                            /* translators: %1$s: open link tag, %2$s: close link tag */
                            printf(esc_html__('No forms found, %1$sclick here to create one%2$s.', 'quform'), '<a href="' . esc_url(admin_url('admin.php?page=quform.forms&sp=add')) . '">', '</a>');
                        } else {
                            esc_html_e('No forms found.', 'quform');
                        }
                    ?>
                <?php endif; ?>
            </div>
            <div class="qfb-popup-overlay"></div>
        </div>
        <?php
        wp_die();
    }
}
