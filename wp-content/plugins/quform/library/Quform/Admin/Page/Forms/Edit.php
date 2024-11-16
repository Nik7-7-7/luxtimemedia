<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Admin_Page_Forms_Edit extends Quform_Admin_Page
{
    /**
     * @var Quform_Builder
     */
    protected $builder;

    /**
     * @var Quform_License
     */
    protected $license;

    /**
     * @var Quform_Options
     */
    protected $options;

    /**
     * @param  Quform_ViewFactory $viewFactory
     * @param  Quform_Repository  $repository
     * @param  Quform_Builder     $builder
     * @param  Quform_License     $license
     * @param  Quform_Options     $options
     */
    public function __construct(Quform_ViewFactory $viewFactory, Quform_Repository $repository, Quform_Builder $builder,
                                Quform_License $license, Quform_Options $options)
    {
        parent::__construct($viewFactory, $repository);

        $this->builder = $builder;
        $this->viewFactory = $viewFactory;
        $this->license = $license;
        $this->options = $options;
    }

    public function init()
    {
        $this->template = QUFORM_TEMPLATE_PATH . '/admin/forms/edit.php';
    }

    protected function enqueueStyles()
    {
        wp_enqueue_style('qtip2', Quform::url('css/jquery.qtip.min.css'), array(), '3.0.4');
        wp_enqueue_style('quform-select2', Quform::url('css/select2.min.css'), array(), '4.0.13');
        wp_enqueue_style('spectrum', Quform::adminUrl('css/spectrum.min.css'), array(), '1.8.1');
        wp_enqueue_style('quform-icons', Quform::url('css/quform-icons.min.css'), array(), QUFORM_VERSION);
        wp_enqueue_style('kendo-common-material', Quform::url('css/kendo.common-material.min.css'), array(), '2020.2.617');
        wp_enqueue_style('kendo-material', Quform::url('css/kendo.material.min.css'), array(), '2020.2.617');

        parent::enqueueStyles();

        wp_enqueue_style('quform-builder', Quform::adminUrl('css/builder.min.css'), array(), QUFORM_VERSION);
    }

    protected function enqueueScripts()
    {
        parent::enqueueScripts();

        wp_enqueue_script('jeditable', Quform::adminUrl('js/jquery.jeditable.min.js'), array('jquery'), '2.0.17', true);
        wp_enqueue_script('spectrum', Quform::adminUrl('js/spectrum.min.js'), array(), '1.8.1', true);
        wp_enqueue_script('qtip2', Quform::url('js/jquery.qtip.min.js'), array('jquery'), '3.0.4', true);
        wp_enqueue_script('quform-select2', Quform::url('js/select2.min.js'), array('jquery'), '4.0.13', true);
        wp_enqueue_script('themecatcher-tabs', Quform::adminUrl('js/tc.tabs.min.js'), array('jquery'), '1.0.0', true);
        wp_enqueue_script('kendo-core', Quform::url('js/kendo.core.min.js'), array('jquery'), '2020.2.617', true);
        wp_enqueue_script('kendo-data', Quform::url('js/kendo.data.min.js'), array('kendo-core'), '2020.2.617', true);
        wp_enqueue_script('kendo-tabstrip', Quform::url('js/kendo.tabstrip.min.js'), array('kendo-data'), '2020.2.617', true);
        wp_enqueue_script('kendo-userevents', Quform::url('js/kendo.userevents.min.js'), array('kendo-core'), '2020.2.617', true);
        wp_enqueue_script('kendo-draganddrop', Quform::url('js/kendo.draganddrop.min.js'), array('kendo-core', 'kendo-userevents'), '2020.2.617', true);
        wp_enqueue_script('kendo-resizable', Quform::url('js/kendo.resizable.min.js'), array('kendo-core', 'kendo-draganddrop'), '2020.2.617', true);
        wp_enqueue_script('kendo-splitter', Quform::url('js/kendo.splitter.min.js'), array('kendo-resizable'), '2020.2.617', true);
        wp_enqueue_script('kendo-calendar', Quform::url('js/kendo.calendar.min.js'), array(), '2020.2.617', true);
        wp_enqueue_script('kendo-popup', Quform::url('js/kendo.popup.min.js'), array(), '2020.2.617', true);
        wp_enqueue_script('kendo-datepicker', Quform::url('js/kendo.datepicker.min.js'), array(), '2020.2.617', true);
        wp_enqueue_script('kendo-timepicker', Quform::url('js/kendo.timepicker.min.js'), array(), '2020.2.617', true);
        wp_enqueue_script('kendo-datetimepicker', Quform::url('js/kendo.datetimepicker.min.js'), array(), '2020.2.617', true);
        wp_enqueue_script('quform-builder', Quform::adminUrl('js/builder.all.min.js'), array('jquery', 'jquery-color', 'json2', 'jquery-ui-draggable', 'jquery-ui-sortable', 'underscore'), QUFORM_VERSION, true);

        wp_localize_script('quform-builder', 'quformBuilderL10n', $this->builder->getScriptL10n());
    }

    /**
     * Set the page title
     *
     * @return string
     */
    protected function getAdminTitle()
    {
        return __('Edit Form', 'quform');
    }

    /**
     * Get the HTML for the admin navigation menu
     *
     * @param   array|null  $currentForm  The data for the current form (if any)
     * @param   array       $extra        Extra HTML to add to the nav, the array key is the hook position
     * @return  string
     */
    public function getNavHtml(array $currentForm = null, array $extra = array())
    {
        ob_start();
        $shortcode = sprintf('[quform id="%d" name="%s"]', $currentForm['id'], $currentForm['name']);
        ?>
        <div class="qfb-nav-item">
            <a id="qfb-add-to-website" class="qfb-nav-item-link" title="<?php esc_html_e('Add to website', 'quform'); ?>"><i class="qfb-mdi qfb-mdi-add_to_queue"></i></a>
        </div>
        <div class="qfb-nav-form-info"><i class="qfb-nav-form-id"><?php echo Quform::escape($currentForm['id']); ?></i><span id="qfb-nav-form-name" class="qfb-nav-form-name"><?php echo Quform::escape($currentForm['name']); ?></span><input type="text" id="qfb-nav-form-shortcode" class="qfb-nav-form-shortcode" readonly value="<?php echo Quform::escape($shortcode); ?>" size="<?php echo Quform::strlen($shortcode); ?>"></div>
        <?php

        return parent::getNavHtml($currentForm, array(40 => ob_get_clean()));
    }

    /**
     * Process this page and send data to the view
     */
    public function process()
    {
        if ( ! current_user_can('quform_edit_forms')) {
            wp_die(__( 'You do not have sufficient permissions to access this page.', 'quform'), 403);
        }

        if ( ! isset($_GET['id']) || ! is_array($config = $this->repository->getConfig((int) $_GET['id']))) {
            wp_die(__("You attempted to edit an item that doesn't exist. Perhaps it was deleted?", 'quform'));
        }

        if ($config['trashed']) {
            wp_die(__("You can't edit this item because it is in the Trash. Please restore it and try again.", 'quform'));
        }

        $this->updateUserRecentForms($config['id']);

        if ( ! $this->license->isValid()) {
            /* translators: %1$s: open link tag, %2$s: close link tag, %3$s: open purchase link tag, %4$s: close purchase link tag */
            $this->addMessage('error', '<strong>' . sprintf(esc_html__('You are using an unlicensed version. Please %1$senter your license key%2$s or %3$spurchase a license key%4$s.', 'quform'), '<a href="' . esc_url(admin_url('admin.php?page=quform.settings#license-updates')) .'">', '</a>', '<a href="https://www.quform.com/buy.php"  target="_blank">', '</a>') . '</strong>');
        }

        $this->view->with(array(
            'form' => $this->builder->prepare($config),
            'builder' => $this->builder,
            'options' => $this->options
        ));
    }

    /**
     * Update the user meta storing recently edited forms
     *
     * @param int $formId
     */
    protected function updateUserRecentForms($formId)
    {
        if ( ! $this->options->get('toolbarMenu')) {
            return;
        }

        $currentUserId = get_current_user_id();
        $recentFormIds = get_user_meta($currentUserId, 'quform_recent_forms', true);

        if ( ! is_array($recentFormIds)) {
            $recentFormIds = array();
        }

        $index = array_search($formId, $recentFormIds);

        if ($index !== false) {
            unset($recentFormIds[$index]);
            $recentFormIds = array_values($recentFormIds);
        }

        array_unshift($recentFormIds, $formId);

        update_user_meta($currentUserId, 'quform_recent_forms', array_slice($recentFormIds, 0, 8));
    }
}
