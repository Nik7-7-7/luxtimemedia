<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Admin_Page_Controller
{
    /**
     * The current page
     *
     * @var Quform_Admin_Page
     */
    protected $page;

    /**
     * @var Quform_Admin_Page_Factory
     */
    protected $pageFactory;

    /**
     * @param Quform_Admin_Page_Factory $pageFactory
     */
    public function __construct(Quform_Admin_Page_Factory $pageFactory)
    {
        $this->pageFactory = $pageFactory;
    }

    /**
     * Process the current page
     *
     * @param object $screen The current screen object
     */
    public function process($screen)
    {
        if ($this->isCorePage($screen->id)) {
            $this->page = $this->pageFactory->create($this->screenIdToPageName($screen->id));
            $this->page->bootstrap()->process();
        }
    }

    /**
     * Enqueue the page assets
     */
    public function enqueueAssets()
    {
        if ($this->page instanceof Quform_Admin_Page) {
            $this->page->enqueueAssets();
        }
    }

    /**
     * Override the admin page title
     *
     * @param   string  $adminTitle
     * @return  string
     */
    public function setAdminTitle($adminTitle)
    {
        if ($this->page instanceof Quform_Admin_Page) {
            return $this->page->setAdminTitle($adminTitle);
        }

        return $adminTitle;
    }

    /**
     * Set a custom body class
     *
     * @param   string  $classes
     * @return  string
     */
    public function addBodyClass($classes)
    {
        if ($this->page instanceof Quform_Admin_Page) {
            $classes .= sprintf(' %s', sanitize_title(get_class($this->page)));
        }

        return $classes;
    }

    /**
     * Render the page
     */
    public function display()
    {
        echo $this->page->display();
    }

    /**
     * Add the WordPress administration menu pages
     */
    public function createMenus()
    {
        add_menu_page(
            __('Dashboard', 'quform'),
            __('Forms', 'quform'),
            'quform_view_dashboard',
            'quform.dashboard',
            array($this, 'display'),
            $this->getMenuIcon(),
            '30.249829482347'
        );

        do_action('quform_admin_menu', 5);

        add_submenu_page(
            'quform.dashboard',
            __('Dashboard', 'quform'),
            __('Dashboard', 'quform'),
            'quform_view_dashboard',
            'quform.dashboard',
            array($this, 'display')
        );

        do_action('quform_admin_menu', 10);

        add_submenu_page(
            'quform.dashboard',
            __('Forms', 'quform'),
            __('Forms', 'quform'),
            'quform_list_forms',
            'quform.forms',
            array($this, 'display')
        );

        do_action('quform_admin_menu', 15);

        add_submenu_page(
            'quform.dashboard',
            __('Add New Form', 'quform'),
            __('Add New', 'quform'),
            'quform_add_forms',
            'quform.forms&sp=add',
            array($this, 'display')
        );

        do_action('quform_admin_menu', 20);

        add_submenu_page(
            'quform.dashboard',
            __('Entries', 'quform'),
            __('Entries', 'quform'),
            'quform_view_entries',
            'quform.entries',
            array($this, 'display')
        );

        do_action('quform_admin_menu', 25);

        add_submenu_page(
            'quform.dashboard',
            __('Tools', 'quform'),
            __('Tools', 'quform'),
            'quform_view_tools',
            'quform.tools',
            array($this, 'display')
        );

        do_action('quform_admin_menu', 30);

        add_submenu_page(
            'quform.dashboard',
            __('Settings', 'quform'),
            __('Settings', 'quform'),
            'quform_settings',
            'quform.settings',
            array($this, 'display')
        );

        do_action('quform_admin_menu', 35);

        add_submenu_page(
            'quform.dashboard',
            __('Help', 'quform'),
            __('Help', 'quform'),
            'quform_help',
            'quform.help',
            array($this, 'display')
        );

        do_action('quform_admin_menu', 40);

        add_submenu_page(
            '',
            __('Preview', 'quform'),
            __('Preview', 'quform'),
            'quform_edit_forms',
            'quform.preview',
            array($this, 'display')
        );

        do_action('quform_admin_menu', 45);
    }

    /**
     * Add the "Form" link to the "+ New" menu in the admin bar
     *
     * @param WP_Admin_Bar $adminBar
     */
    public function addNewFormToAdminBar(WP_Admin_Bar $adminBar)
    {
        if (current_user_can('quform_add_forms')) {
            $adminBar->add_node(array(
                'id' => 'quform-add-new-form',
                'title' => __('Form', 'quform'),
                'href' => admin_url('admin.php?page=quform.forms&sp=add'),
                'parent' => 'new-content'
            ));
        }
    }

    /**
     * Get the menu icon SVG
     *
     * @return string
     */
    protected function getMenuIcon()
    {
        $color = '';

        if (in_array(Quform::get($_GET, 'page'), array('quform.dashboard', 'quform.forms', 'quform.entries', 'quform.tools', 'quform.settings', 'quform.help'))) {
            $color = '#ffffff';
        }

        $color = apply_filters('quform_admin_menu_icon_color', $color);

        return Quform::getPluginIcon($color);
    }

    /**
     * Convert the given screen ID into a page name
     *
     * @param   string  $id  The screen ID, e.g. toplevel_page_quform.forms
     * @return  string       The last part of the page class e.g. Forms
     */
    protected function screenIdToPageName($id)
    {
        $name = preg_replace('/^.+_page_quform\.(.+)$/', '$1', $id);

        return Quform::studlyCase($name);
    }

    /**
     * Is the given screen ID one of the plugin core pages
     *
     * @param   string  $id  The screen ID, e.g. toplevel_page_quform.forms
     * @return  bool
     */
    protected function isCorePage($id)
    {
        return (bool) preg_match('/_page_quform\.(dashboard|forms|entries|tools|settings|help|preview)$/', $id);
    }

    /**
     * Get the subpage query var
     *
     * @return string|null
     */
    protected function getSubpage()
    {
        return Quform::get($_GET, 'sp');
    }
}
