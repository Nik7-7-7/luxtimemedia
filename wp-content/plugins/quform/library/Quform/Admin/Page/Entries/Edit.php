<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Admin_Page_Entries_Edit extends Quform_Admin_Page_Entries
{
    /**
     * @var Quform_Form_Factory
     */
    protected $formFactory;

    /**
     * @var Quform_Uploader
     */
    protected $uploader;

    /**
     * @var Quform_Session
     */
    protected $session;

    /**
     * @param  Quform_ViewFactory  $viewFactory
     * @param  Quform_Repository   $repository
     * @param  Quform_Form_Factory $formFactory
     * @param  Quform_Uploader     $uploader
     * @param  Quform_Session      $session
     */
    public function __construct(Quform_ViewFactory $viewFactory, Quform_Repository $repository,
                                Quform_Form_Factory $formFactory, Quform_Uploader $uploader, Quform_Session $session)
    {
        parent::__construct($viewFactory, $repository);

        $this->formFactory = $formFactory;
        $this->uploader = $uploader;
        $this->session = $session;
    }

    public function init()
    {
        $this->template = QUFORM_TEMPLATE_PATH . '/admin/entries/edit.php';
    }

    /**
     * Enqueue the page styles
     */
    protected function enqueueStyles() {
        wp_enqueue_style('quform-select2', Quform::url('css/select2.min.css'), array(), '4.0.13');

        parent::enqueueStyles();
    }

    /**
     * Enqueue the page scripts
     */
    protected function enqueueScripts()
    {
        wp_deregister_script('jquery-form');
        wp_enqueue_script('jquery-form', Quform::url('js/jquery.form.min.js'), array('jquery'), '4.3.0', true);
        wp_enqueue_script('quform-select2', Quform::url('js/select2.min.js'), array('jquery'), '4.0.13', true);

        parent::enqueueScripts();

        wp_enqueue_script('quform-entries-edit', Quform::adminUrl('js/entries.edit.min.js'), array('jquery'), QUFORM_VERSION, true);

        wp_localize_script('quform-entries-edit', 'quformEntriesEditL10n', array(
            'none' => __('None', 'quform'),
            'searchUsersNonce' => wp_create_nonce('quform_entries_search_users')
        ));
    }

    /**
     * Set the page title
     *
     * @return string
     */
    protected function getAdminTitle()
    {
        return __('Edit Entry', 'quform');
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
            Quform::escape(sprintf(__('Editing entry for %s', 'quform'), $currentForm['name']))
        );

        return parent::getNavHtml($currentForm, $extra);
    }

    /**
     * Process this page and send data to the view
     */
    public function process()
    {
        if ( ! current_user_can('quform_edit_entries')) {
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

        $uniqueId = Quform_Form::generateUniqueId();

        while ($this->session->has(sprintf('quform-%s', $uniqueId))) {
            $uniqueId = Quform_Form::generateUniqueId();
        }

        $config['uniqueId'] = $uniqueId;
        $config['entryId'] = $entryId;
        $config['environment'] = 'editEntry';

        $form = $this->formFactory->create($config);

        $form->setValues(array(), true);

        foreach ($entry as $key => $value) {
            if (preg_match('/element_(\d+)/', $key, $matches)) {
                $form->setValueFromStorage($matches[1], $value);
                unset($entry[$key]);
            }
        }

        // Save currently saved uploads into session
        $this->uploader->saveFileUploadValuesIntoSession($form);

        // Mark as read
        if ($entry['unread'] == 1) {
            $this->repository->readEntries(array($entry['id']));
        }

        // Get label data from label IDs
        $entry['labels'] = $this->repository->getEntryLabels($entryId);

        $data = array(
            'form' => $form,
            'entry' => $entry,
            'labels' => $this->repository->getFormEntryLabels($form->getId())
        );

        $this->view->with($data);
    }
}
