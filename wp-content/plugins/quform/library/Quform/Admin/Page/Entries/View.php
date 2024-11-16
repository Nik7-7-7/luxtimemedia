<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Admin_Page_Entries_View extends Quform_Admin_Page_Entries
{
    /**
     * @var Quform_Form_Factory
     */
    protected $formFactory;

    /**
     * @var Quform_Options
     */
    protected $options;

    /**
     * @param  Quform_ViewFactory   $viewFactory
     * @param  Quform_Repository    $repository
     * @param  Quform_Form_Factory  $formFactory
     * @param  Quform_Options       $options
     */
    public function __construct(Quform_ViewFactory $viewFactory, Quform_Repository $repository,
                                Quform_Form_Factory $formFactory, Quform_Options $options)
    {
        parent::__construct($viewFactory, $repository);

        $this->formFactory = $formFactory;
        $this->options = $options;
    }

    public function init()
    {
        $this->template = QUFORM_TEMPLATE_PATH . '/admin/entries/view.php';
    }

    /**
     * Enqueue the page scripts
     */
    protected function enqueueScripts()
    {
        wp_enqueue_script('js-cookie', Quform::adminUrl('js/js.cookie.min.js'), array(), '2.2.1', true);

        parent::enqueueScripts();

        wp_enqueue_script('quform-entries-view', Quform::adminUrl('js/entries.view.min.js'), array('jquery'), QUFORM_VERSION, true);

        wp_localize_script('quform-entries-view', 'quformEntriesViewL10n', array(
            'resendNotificationsNonce' => wp_create_nonce('quform_resend_notifications')
        ));
    }

    /**
     * Set the page title
     *
     * @return string
     */
    protected function getAdminTitle()
    {
        return __('View Entry', 'quform');
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
        $extra[10] = sprintf(
            '<div class="qfb-nav-item"><a class="qfb-nav-item-link" href="%s"><i class="qfb-icon qfb-icon-arrow-left"></i></a></div>',
            esc_url(add_query_arg(array(
                'id' => $currentForm['id'],
                'sp' => false,
                'eid' => false,
                'read' => false,
                'unread' => false,
                'error' => false,
                'deleted' => false
            )))
        );

        $extra[40] = sprintf(
            '<div class="qfb-nav-item qfb-nav-page-info"><i class="qfb-nav-page-icon qfb-mdi qfb-mdi-message"></i><span class="qfb-nav-page-title">%s</span></div>',
            /* translators: %s: the form name */
            Quform::escape(sprintf(__('Viewing entry for %s', 'quform'), $currentForm['name']))
        );

        return parent::getNavHtml($currentForm, $extra);
    }

    /**
     * Process this page and send data to the view
     */
    public function process()
    {
        if ( ! current_user_can('quform_view_entries')) {
            wp_die(__( 'You do not have sufficient permissions to access this page.', 'quform'), 403);
        }

        $entryId = isset($_GET['eid']) && is_numeric($_GET['eid']) ? (int) $_GET['eid'] : null;

        $entry = $this->repository->findEntry($entryId);

        if ( ! is_array($entry)) {
            wp_die(__("You attempted to edit an item that doesn't exist. Perhaps it was deleted?", 'quform'));
        }

        $formId = $this->repository->getFormIdFromEntryId($entryId);

        $config = $this->repository->getConfig($formId);

        if ( ! is_array($config)) {
            wp_die(__("You attempted to edit an item that doesn't exist. Perhaps it was deleted?", 'quform'));
        }

        $config['entryId'] = $entryId;
        $config['environment'] = 'viewEntry';

        $form = $this->formFactory->create($config);

        $form->setValues(array(), true);

        foreach ($entry as $key => $value) {
            if (preg_match('/element_(\d+)/', $key, $matches)) {
                $elementId = $matches[1];
                $form->setValueFromStorage($elementId, $value);
                unset($entry[$key]);
            }
        }

        // Calculate which elements are hidden by conditional logic and which groups are empty
        $form->calculateElementVisibility();

        // Mark as read
        if ($entry['unread'] == 1) {
            $this->repository->readEntries(array($entry['id']));
        }

        // Get label data from label IDs
        $entry['labels'] = $this->repository->getEntryLabels($entryId);

        $data = array(
            'options' => $this->options,
            'form' => $form,
            'entry' => $entry,
            'showEmptyFields' => Quform::get($_COOKIE, 'qfb-show-empty-fields') ? true : false,
            'labels' => $this->repository->getFormEntryLabels($form->getId()),
            'notifications' => $form->getNotifications()
        );

        $this->view->with($data);
    }
}
