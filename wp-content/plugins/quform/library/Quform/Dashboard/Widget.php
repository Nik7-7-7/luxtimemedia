<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Dashboard_Widget
{
    /**
     * @var Quform_Repository
     */
    protected $repository;

    /**
     * @var Quform_ViewFactory
     */
    protected $viewFactory;

    /**
     * @var string
     */
    protected $template;

    /**
     * @var Quform_Options
     */
    protected $options;

    /**
     * @param Quform_Repository  $repository
     * @param Quform_ViewFactory $viewFactory
     * @param Quform_Options     $options
     */
    public function __construct(Quform_Repository $repository, Quform_ViewFactory $viewFactory, Quform_Options $options)
    {
        $this->repository = $repository;
        $this->viewFactory = $viewFactory;
        $this->options = $options;
        $this->template = QUFORM_TEMPLATE_PATH . '/admin/dashboard-widget.php';
    }

    public function setup()
    {
        if (
            ! $this->options->get('dashboardWidget') ||
            ! current_user_can('quform_view_entries') ||
            ! $this->repository->getAllUnreadEntriesCount() > 0
        ) {
            // The widget is turned off, user doesn't have permission to view entries or there are no unread entries
            return;
        }

        wp_enqueue_style('qfb-dashboard', Quform::adminUrl('css/dashboard.min.css'), array(), QUFORM_VERSION);
        wp_add_dashboard_widget('qfb-dashboard-widget', Quform::getPluginName(), array($this, 'display'));
    }

    public function display()
    {
        $data = array(
            'forms' => $this->repository->getAllFormsWithUnreadEntries()
        );

        echo $this->viewFactory->create($this->template, $data)->render();
    }
}
