<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
abstract class Quform_Admin_Page
{
    const BAD_REQUEST = 1;

    const NO_PERMISSION = 2;

    const NONCE_CHECK_FAILED = 3;

    /**
     * The path to the view template
     *
     * @var string
     */
    protected $template;

    /**
     * The view instance for this page
     *
     * @var Quform_View
     */
    protected $view;

    /**
     * @var Quform_ViewFactory
     */
    protected $viewFactory;

    /**
     * Notification messages to be displayed
     *
     * @var array
     */
    protected $messages = array();

    /**
     * @var Quform_Repository
     */
    protected $repository;

    /**
     * @param Quform_ViewFactory $viewFactory
     * @param Quform_Repository  $repository
     */
    public function __construct(Quform_ViewFactory $viewFactory, Quform_Repository $repository)
    {
        $this->viewFactory = $viewFactory;
        $this->repository = $repository;
    }

    /**
     * Sets up the page and view
     *
     * @return  $this
     */
    public function bootstrap()
    {
        $this->init();

        $this->view = $this->viewFactory->create($this->template, array('page' => $this));

        return $this;
    }

    /**
     * Initialize this page
     *
     * Subclasses can override this method to add their own bootstrap functionality.
     */
    public function init()
    {
        // Override in subclass to run on instantiation
    }

    /**
     * Enqueue the page assets
     */
    public function enqueueAssets()
    {
        $this->enqueueStyles();
        $this->enqueueScripts();
    }

    /**
     * Enqueue the page styles
     */
    protected function enqueueStyles()
    {
        wp_enqueue_style('qfb-fontello', Quform::url('fonts/fontello/css/qfb-icons.css'), array(), QUFORM_VERSION);

        $loadLegacyIcons = false;

        if (defined('QUFORM_MAILCHIMP_VERSION') && version_compare(QUFORM_MAILCHIMP_VERSION, '1.2.0', '<=')) {
            $loadLegacyIcons = true;
        }

        if (defined('QUFORM_ZAPIER_VERSION') && version_compare(QUFORM_ZAPIER_VERSION, '1.0.3', '<=')) {
            $loadLegacyIcons = true;
        }

        if (apply_filters('quform_load_legacy_icons', $loadLegacyIcons)) {
            wp_enqueue_style('material-icons-legacy-quform', Quform::adminUrl('fonts/material-icons.legacy.min.css'), array(), QUFORM_VERSION);
        }

        wp_enqueue_style('material-icons-quform', Quform::adminUrl('fonts/material-icons.min.css'), array(), QUFORM_VERSION);
        wp_enqueue_style('quform-admin', Quform::adminUrl('css/admin.min.css'), array(), QUFORM_VERSION);

        if (is_rtl()) {
            wp_enqueue_style('quform-rtl', Quform::adminUrl('css/rtl.min.css'), array(), QUFORM_VERSION);
        }
    }

    /**
     * Enqueue the page scripts
     */
    protected function enqueueScripts()
    {
        wp_enqueue_script('jquery-scroll-to', Quform::url('js/jquery.scrollTo.min.js'), array('jquery'), '2.1.2', true);
        wp_enqueue_script('quform-core', Quform::adminUrl('js/core.min.js'), array('jquery'), QUFORM_VERSION, true);

        wp_localize_script('quform-core', 'quformCoreL10n', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'pluginUrl' => Quform::url(),
            'adminImagesUrl' => Quform::adminUrl('images/'),
            'ajaxError' => __('Ajax error', 'quform'),
            'thisFieldIsRequired' => __('This field is required', 'quform'),
            'invalidEmailAddress' => __('Invalid email address', 'quform'),
            'selectImage' => __('Select Image', 'quform'),
            'select' => __('Select', 'quform'),
            'noResultsFound' => __('No results found.', 'quform'),
            'searchingDots' => __('Searching...', 'quform'),
            'errorLoading' => __('The results could not be loaded.', 'quform'),
            /* translators: %s: the minimum number of characters */
            'inputTooShort' => __('Please enter %s or more characters.', 'quform'),
            'removeAllItems' => __('Remove all items', 'quform')
        ));
    }

    /**
     * Process this page
     *
     * Subclasses can override this method to add their own process functionality.
     */
    public function process()
    {
        // Override in subclass to run on process
    }

    /**
     * Subclasses can override this method to set a custom page title
     *
     * @return string
     */
    protected function getAdminTitle()
    {
        return '';
    }

    /**
     * Override the page title if the current page has a custom title
     *
     * @param   string  $adminTitle  The current admin title
     * @return  string               The new admin title
     */
    public function setAdminTitle($adminTitle)
    {
        $title = $this->getAdminTitle();

        if (Quform::isNonEmptyString($title)) {
            /* translators: %1$s: the page title, %2$s the site name */
            $adminTitle = sprintf(__('%1$s &lsaquo; %2$s &#8212; WordPress', 'quform'), esc_html($title), esc_html(get_bloginfo('name')));
        }

        return $adminTitle;
    }

    /**
     * Renders this page's view
     *
     * @return string
     */
    public function display()
    {
        return $this->view->render();
    }

    /**
     * Add a notification message
     *
     * @param   string|array  $type     Type of message e.g. 'error' or 'success'
     * @param   string        $message  The message content
     * @return  $this
     */
    public function addMessage($type, $message = '')
    {
        if (is_array($type)) {
            return $this->addMessage($type['type'], $type['message']);
        } else {
            $message = array('type' => $type, 'message' => $message);
            $this->messages[] = $message;
        }

        return $this;
    }

    /**
     * Get the notification messages for this page
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Get the HTML for the page messages
     *
     * @return string
     */
    public function getMessagesHtml()
    {
        $output = '';

        if (count($this->getMessages())) {
            $output .= '<div id="qfb-page-messages" class="qfb-page-messages">';

            foreach ($this->getMessages() as $message) {
                $output .= '<div class="qfb-message-box qfb-message-box-' . $message['type'] . '"><div class="qfb-message-box-inner">' . $message['message'] . '</div></div>';
            }

            $output .= '</div>';
        }

        return $output;
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
        $links = array(
            array(
                'cap' => 'quform_view_dashboard',
                'href' => admin_url('admin.php?page=quform.dashboard'),
                'class' => 'dashboard',
                'icon' => '<i class="qfb-mdi qfb-mdi-dashboard"></i>',
                'text' => __('Dashboard', 'quform')
            ),
            array(
                'cap' => 'quform_list_forms',
                'href' => admin_url('admin.php?page=quform.forms'),
                'class' => 'forms',
                'icon' => '<i class="qfb-mdi qfb-mdi-view_stream"></i>',
                'text' => __('Forms', 'quform')
            ),
            array(
                'cap' => 'quform_view_entries',
                'href' => admin_url('admin.php?page=quform.entries'),
                'class' => 'entries',
                'icon' => '<i class="qfb-mdi qfb-mdi-message"></i>',
                'text' => __('Entries', 'quform')
            ),
            array(
                'cap' => 'quform_view_tools',
                'href' => admin_url('admin.php?page=quform.tools'),
                'class' => 'tools',
                'icon' => '<i class="qfb-mdi qfb-mdi-build"></i>',
                'text' => __('Tools', 'quform')
            ),
            array(
                'cap' => 'quform_settings',
                'href' => admin_url('admin.php?page=quform.settings'),
                'class' => 'settings',
                'icon' => '<i class="qfb-mdi qfb-mdi-settings"></i>',
                'text' => __('Settings', 'quform')
            ),
            array(
                'cap' => 'quform_help',
                'href' => admin_url('admin.php?page=quform.help'),
                'class' => 'help',
                'icon' => '<i class="qfb-mdi qfb-mdi-help_outline"></i>',
                'text' => __('Help', 'quform')
            )
        );

        $visible = array();
        foreach ($links as $link) {
            if (current_user_can($link['cap'])) {
                $visible[] = $link;
            }
        }

        if ( ! count($visible)) {
            return '';
        }

        $currentUserId = get_current_user_id();
        $userOrderBy = get_user_meta($currentUserId, 'quform_forms_order_by', true);
        $userOrder = get_user_meta($currentUserId, 'quform_forms_order', true);

        $forms = $this->repository->getForms(array(
            'orderby' => in_array($userOrderBy, array('id', 'name', 'entries', 'active', 'created_at', 'updated_at')) ? $userOrderBy : 'updated_at',
            'order' => in_array($userOrder, array('asc', 'desc')) ? strtoupper($userOrder) : 'DESC',
            'limit' => 50
        ));

        ob_start();
        ?>
        <div id="qfb-nav" class="qfb-cf">
            <?php
                printf(
                    '<a class="qfb-logo"%s></a>',
                    current_user_can('quform_view_dashboard') ? sprintf(' href="%s"', esc_url(admin_url('admin.php?page=quform.dashboard'))) : ''
                );
            ?>

            <?php echo $this->getExtraHtml(10, $extra); ?>

            <div class="qfb-nav-item qfb-nav-item-menu">
                <a class="qfb-nav-item-link qfb-nav-popup-trigger"><i class="qfb-icon qfb-icon-bars"></i></a>
                <div class="qfb-nav-popup-content">
                    <ul class="qfb-nav-menu">
                        <?php
                            foreach ($visible as $item) {
                                echo '<li class="qfb-page-' . esc_attr($item['class']) . '"><a href="' . esc_url($item['href']) . '">' . $item['icon'] . ' <span>' . esc_html($item['text']) . '</span></a></li>';
                            }
                        ?>
                    </ul>
                </div>
            </div>

            <?php echo $this->getExtraHtml(20, $extra); ?>

            <?php if (current_user_can('quform_view_entries') || current_user_can('quform_edit_forms')) : ?>

                <div class="qfb-nav-item qfb-nav-item-form-switcher">
                    <a class="qfb-nav-item-link qfb-nav-popup-trigger"><i class="qfb-icon qfb-icon-folder-open-o"></i></a>
                    <div class="qfb-form-switcher qfb-nav-popup-content">
                        <ul class="qfb-nav-menu qfb-cf">
                            <?php
                                if (is_array($currentForm) && ! empty($currentForm['id'])) {
                                    echo $this->getFormSwitcherItemHtml($currentForm, true);
                                }

                                foreach ($forms as $form) {
                                    if (is_array($currentForm) && ! empty($currentForm['id']) && $form['id'] == $currentForm['id']) {
                                        continue;
                                    }

                                    echo $this->getFormSwitcherItemHtml($form);
                                }
                            ?>
                            <li class="qfb-cf qfb-form-switcher-add-form-button"><?php printf('<a href="%s">%s</a>', esc_url(admin_url('admin.php?page=quform.forms&sp=add')), esc_html__('Add New', 'quform')); ?></li>
                        </ul>
                    </div>
                </div>

            <?php endif; ?>

            <?php echo $this->getExtraHtml(30, $extra); ?>

            <?php if (current_user_can('quform_add_forms')) : ?>
                <div class="qfb-nav-item qfb-nav-item-add">
                    <a class="qfb-nav-item-link" title="<?php esc_attr_e('Create a new form', 'quform'); ?>" href="<?php echo esc_url(admin_url('admin.php?page=quform.forms&sp=add')); ?>"><i class="qfb-mdi qfb-mdi-add_circle"></i></a>
                </div>
            <?php endif; ?>

            <?php echo $this->getExtraHtml(40, $extra); ?>

            <div class="qfb-nav-item qfb-nav-item-right qfb-nav-item-help">
                <a class="qfb-nav-item-link qfb-nav-popup-trigger"><i class="qfb-icon qfb-icon-question"></i></a>
                <div class="qfb-nav-popup-content">
                    <ul class="qfb-nav-menu qfb-cf">
                        <li class="qfb-cf"><a href="https://support.themecatcher.net/" target="_blank"><i class="qfb-icon qfb-icon-life-ring"></i><?php esc_html_e('Visit help site', 'quform'); ?></a></li>
                        <li class="qfb-cf"><a href="https://support.themecatcher.net/quform-wordpress-v2" target="_blank"><i class="qfb-icon qfb-icon-book"></i><?php esc_html_e('View documentation', 'quform'); ?></a></li>
                        <li class="qfb-cf"><a href="https://support.themecatcher.net/forums" target="_blank"><i class="qfb-icon qfb-icon-comments"></i><?php esc_html_e('Support forum', 'quform'); ?></a></li>
                        <li class="qfb-cf"><a href="https://www.youtube.com/channel/UCoyKi9_M7ECFYK-w7ZqNMVg" target="_blank"><i class="qfb-icon qfb-icon-play"></i><?php esc_html_e('Video tutorials', 'quform'); ?></a></li>
                    </ul>
                </div>

            </div>

            <?php echo $this->getExtraHtml(50, $extra); ?>

        </div>
        <?php

        return ob_get_clean();
    }

    /**
     * Get the HTML for a single form list item in the form switcher
     *
     * @param   array   $form       The form config
     * @param   bool    $highlight  Whether to highlight the item
     * @return  string
     */
    protected function getFormSwitcherItemHtml(array $form, $highlight = false)
    {
        $name = Quform::isNonEmptyString($form['name']) ? $form['name'] : __('(no title)', 'quform');

        if ($this instanceof Quform_Admin_Page_Entries_List ||
            $this instanceof Quform_Admin_Page_Entries_View ||
            $this instanceof Quform_Admin_Page_Entries_Edit ||
            ! current_user_can('quform_edit_forms') // Implies they can edit entries - permissions checked in caller
        ) {
            $linkUrl = sprintf(admin_url('admin.php?page=quform.entries&id=%d'), $form['id']);
        } else {
            $linkUrl = sprintf(admin_url('admin.php?page=quform.forms&sp=edit&id=%d'), $form['id']);
        }

        $output = sprintf('<li class="qfb-cf%s">', $highlight ? ' qfb-highlight' : '');
        $output .= sprintf('<a title="%s" href="%s">', Quform::escape($name), esc_url($linkUrl));
        $output .= Quform::escape($name);
        $output .= '<span class="qfb-fade-overflow"></span></a>';

        $output .= '<span class="qfb-form-switcher-icons">';

        if (current_user_can('quform_view_entries')) {
            $output .= sprintf('<a href="%s"><i title="%s" class="qfb-mdi qfb-mdi-chat"></i></a>', esc_url(admin_url('admin.php?page=quform.entries&id=' . $form['id'])), esc_attr__('View Entries', 'quform'));
        }

        if (current_user_can('quform_edit_forms')) {
            $output .= sprintf('<a href="%s"><i title="%s" class="qfb-icon qfb-icon-pencil"></i></a>', esc_url(admin_url('admin.php?page=quform.forms&sp=edit&id=' . $form['id'])), esc_attr__('Edit', 'quform'));
        }

        $output .= '</span>';

        $output .= '</li>';

        return $output;
    }

    /**
     * Get extra HTML for the nav and call hooks
     *
     * @param   int    $index
     * @param   array  $extra
     * @return  string
     */
    protected function getExtraHtml($index, array $extra)
    {
        ob_start();

        if (isset($extra[$index])) {
            echo $extra[$index];
        }

        do_action('quform_admin_nav', $index, $this);

        return ob_get_clean();
    }
}
