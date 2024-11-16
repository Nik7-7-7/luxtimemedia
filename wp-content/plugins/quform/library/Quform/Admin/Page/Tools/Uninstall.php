<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Admin_Page_Tools_Uninstall extends Quform_Admin_Page_Tools
{
    /**
     * @var Quform_Options
     */
    protected $options;

    /**
     * @var Quform_Permissions
     */
    protected $permissions;

    /**
     * @var Quform_Uploader
     */
    protected $uploader;

    /**
     * @var Quform_Session
     */
    protected $session;

    /**
     * @var Quform_Upgrader
     */
    protected $upgrader;

    /**
     * @param Quform_ViewFactory $viewFactory
     * @param Quform_Repository  $repository
     * @param Quform_Options     $options
     * @param Quform_Permissions $permissions
     * @param Quform_Uploader    $uploader
     * @param Quform_Session     $session
     * @param Quform_Upgrader    $upgrader
     */
    public function __construct(
        Quform_ViewFactory $viewFactory,
        Quform_Repository $repository,
        Quform_Options $options,
        Quform_Permissions $permissions,
        Quform_Uploader $uploader,
        Quform_Session $session,
        Quform_Upgrader $upgrader
    ) {
        parent::__construct($viewFactory, $repository);

        $this->options = $options;
        $this->permissions = $permissions;
        $this->uploader = $uploader;
        $this->session = $session;
        $this->upgrader = $upgrader;
    }

    public function init()
    {
        $this->template = QUFORM_TEMPLATE_PATH . '/admin/tools/uninstall.php';
    }

    /**
     * Set the page title
     *
     * @return string
     */
    protected function getAdminTitle()
    {
        return __('Uninstall', 'quform');
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
        $extra[40] = sprintf(
            '<div class="qfb-nav-item qfb-nav-page-info"><i class="qfb-nav-page-icon qfb-icon-trash-o"></i><span class="qfb-nav-page-title">%s</span></div>',
            esc_html__('Uninstall', 'quform')
        );

        return parent::getNavHtml($currentForm, $extra);
    }

    /**
     * Process this page and send data to the view
     */
    public function process()
    {
        if ( ! current_user_can('activate_plugins')) {
            wp_die(__( 'You do not have sufficient permissions to access this page.', 'quform'), 403);
        }

        if (Quform::isPostRequest()) {
            if ( ! isset($_POST['qfb_uninstall_confirm']) || $_POST['qfb_uninstall_confirm'] != 1) {
                $this->addMessage('error', __('You must confirm to continue.', 'quform'));
                return;
            }

            if ( ! check_ajax_referer('quform_uninstall', false, false)) {
                $this->addMessage('error', __('Nonce check failed.', 'quform'));
                return;
            }

            deactivate_plugins(QUFORM_BASENAME);

            $this->repository->uninstall();
            $this->options->uninstall();
            $this->permissions->uninstall();
            $this->uploader->uninstall();
            $this->session->uninstall();
            $this->upgrader->uninstall();

            do_action('quform_uninstall');

            wp_safe_redirect(self_admin_url('plugins.php?deactivate=true'));
            exit;
        }
    }
}
