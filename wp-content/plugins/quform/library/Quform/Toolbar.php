<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Toolbar
{
    /**
     * @var Quform_Options
     */
    protected $options;

    /**
     * @var Quform_Repository
     */
    protected $repository;

    /**
     * @param  Quform_Options     $options
     * @param  Quform_Repository  $repository
     */
    public function __construct(Quform_Options $options, Quform_Repository $repository)
    {
        $this->options = $options;
        $this->repository = $repository;
    }

    /**
     * Add a form management menu and links to the toolbar
     *
     * @param WP_Admin_Bar $adminBar
     */
    public function addNodes(WP_Admin_Bar $adminBar)
    {
        if ( ! $this->options->get('toolbarMenu') || ! $this->userHasAnyCapability()) {
            return;
        }

        if (current_user_can('quform_add_forms')) {
            $adminBar->add_node(array(
                'id' => 'quform-new-form',
                'title' => Quform::escape(__('Form', 'quform')),
                'href' => admin_url('admin.php?page=quform.forms&sp=add'),
                'parent' => 'new-content'
            ));
        }

        if (current_user_can('quform_edit_forms') || current_user_can('quform_view_entries')) {
            $forms = $this->repository->getFormsByUpdatedAt();

            if (is_array($forms) && count($forms)) {
                $adminBar->add_node(array(
                    'id' => 'quform-recent-forms',
                    'parent' => 'quform',
                    'group' => true
                ));

                $currentUserId = get_current_user_id();
                $recentFormIds = get_user_meta($currentUserId, 'quform_recent_forms', true);

                if ( ! is_array($recentFormIds) || ! count($recentFormIds)) {
                    $recentFormIds = array();

                    foreach ($forms as $form) {
                        $recentFormIds[] = $form['id'];
                    }

                    if (count($recentFormIds)) {
                        update_user_meta($currentUserId, 'quform_recent_forms', array_slice($recentFormIds, 0, 8));
                    }
                }

                if (count($recentFormIds)) {

                    foreach ($recentFormIds as $recentFormId) {

                        foreach ($forms as $form) {
                            if ($form['id'] == $recentFormId) {
                                $adminBar->add_node(array(
                                    'id' => sprintf('quform-form-%d', $form['id']),
                                    'title' => Quform::escape($form['name']),
                                    'href' => current_user_can('quform_edit_forms') ? admin_url(sprintf('admin.php?page=quform.forms&sp=edit&id=%d', $form['id'])) : '',
                                    'parent' => 'quform-recent-forms'
                                ));

                                if (current_user_can('quform_edit_forms')) {
                                    $adminBar->add_node(array(
                                        'id' => sprintf('quform-form-%d-edit', $form['id']),
                                        'title' => Quform::escape(__('Edit', 'quform')),
                                        'href' => admin_url(sprintf('admin.php?page=quform.forms&sp=edit&id=%d', $form['id'])),
                                        'parent' => sprintf('quform-form-%d', $form['id'])
                                    ));
                                }

                                if (current_user_can('quform_view_entries')) {
                                    $adminBar->add_node(array(
                                        'id' => sprintf('quform-form-%d-entries', $form['id']),
                                        'title' => Quform::escape(__('Entries', 'quform')),
                                        'href' => admin_url(sprintf('admin.php?page=quform.entries&id=%d', $form['id'])),
                                        'parent' => sprintf('quform-form-%d', $form['id'])
                                    ));
                                }
                            }
                        }
                    }
                }
            }
        }

        $adminBar->add_node(array(
            'id' => 'quform',
            'title' => sprintf('<span class="quform-toolbar-icon ab-item svg"></span><span class="ab-label">%s</span>', Quform::escape(__('Forms', 'quform'))),
            'href' => current_user_can('quform_view_dashboard') ? admin_url('admin.php?page=quform.dashboard') : false,
            'parent' => false,
            'meta' => array('class' => 'quform-toolbar-menu')
        ));

        if (current_user_can('quform_view_dashboard')) {
            $adminBar->add_node(array(
                'id' => 'quform-dashboard',
                'title' => Quform::escape(__('Dashboard', 'quform')),
                'href' => admin_url('admin.php?page=quform.dashboard'),
                'parent' => 'quform'
            ));
        }

        if (current_user_can('quform_list_forms')) {
            $adminBar->add_node(array(
                'id' => 'quform-forms',
                'title' => Quform::escape(__('Forms', 'quform')),
                'href' => admin_url('admin.php?page=quform.forms'),
                'parent' => 'quform'
            ));
        }

        if (current_user_can('quform_add_forms')) {
            $adminBar->add_node(array(
                'id' => 'quform-add-new',
                'title' => Quform::escape(__('Add New', 'quform')),
                'href' => admin_url('admin.php?page=quform.forms&sp=add'),
                'parent' => 'quform'
            ));
        }

        if (current_user_can('quform_settings')) {
            $adminBar->add_node(array(
                'id' => 'quform-settings',
                'title' => Quform::escape(__('Settings', 'quform')),
                'href' => admin_url('admin.php?page=quform.settings'),
                'parent' => 'quform'
            ));
        }
    }

    /**
     * Print the styles for the toolbar menu icon
     */
    public function printStyles()
    {
        if ( ! $this->options->get('toolbarMenu') || ! $this->userHasAnyCapability()) {
            return;
        }

        ?>
        <style>
            #wpadminbar .quform-toolbar-menu .quform-toolbar-icon {
                float: left;
                width: 27px;
                height: 27px;
                background: url('<?php echo esc_html(Quform::getPluginIcon()); ?>') no-repeat 0px 7px;
                background-size: 20px;
            }
        </style>
        <?php
    }

    /**
     * Returns true only if the user has the capability to access at least one of the links within the toolbar menu
     *
     * @return bool
     */
    protected function userHasAnyCapability()
    {
        return
            current_user_can('quform_view_dashboard') ||
            current_user_can('quform_list_forms') ||
            current_user_can('quform_add_forms') ||
            current_user_can('quform_edit_forms') ||
            current_user_can('quform_view_entries') ||
            current_user_can('quform_settings');
    }
}
