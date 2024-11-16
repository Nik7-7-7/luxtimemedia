<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Admin_Page_Entries_List extends Quform_Admin_Page_Entries
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
     * @param  Quform_ViewFactory       $viewFactory
     * @param  Quform_Repository        $repository
     * @param  Quform_Form_Factory      $formFactory
     * @param  Quform_Options           $options
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
        $this->template = QUFORM_TEMPLATE_PATH . '/admin/entries/list.php';
    }

    /**
     * Enqueue the page styles
     */
    protected function enqueueStyles()
    {
        wp_enqueue_style('qtip2', Quform::url('css/jquery.qtip.min.css'), array(), '3.0.4');
        wp_enqueue_style('spectrum', Quform::adminUrl('css/spectrum.min.css'), array(), '1.8.1');

        parent::enqueueStyles();
    }

    /**
     * Enqueue the page scripts
     */
    protected function enqueueScripts()
    {
        wp_enqueue_script('jeditable', Quform::adminUrl('js/jquery.jeditable.min.js'), array('jquery'), '2.0.17', true);
        wp_enqueue_script('qtip2', Quform::url('js/jquery.qtip.min.js'), array('jquery'), '3.0.4', true);
        wp_enqueue_script('spectrum', Quform::adminUrl('js/spectrum.min.js'), array(), '1.8.1', true);

        parent::enqueueScripts();

        wp_enqueue_script('quform-entries-list', Quform::adminUrl('js/entries.list.min.js'), array('jquery', 'jquery-ui-sortable'), QUFORM_VERSION, true);

        wp_localize_script('quform-entries-list', 'quformEntriesListL10n', array(
            'singleDeleteEntryMessage' => __('Are you sure you want to delete this entry? All data for this entry will be lost and this cannot be undone.', 'quform'),
            'pluralDeleteEntryMessage' => __('Are you sure you want to delete these entries? All data for these entries will be lost and this cannot be undone.', 'quform'),
            'saveEntriesTableSettingsNonce' => wp_create_nonce('quform_save_entries_table_settings'),
            'entryLabelEditHtml' => $this->getEntryLabelEditHtml()
        ));
    }

    /**
     * Process this page and send data to the view
     */
    public function process()
    {
        if ( ! current_user_can('quform_view_entries')) {
            wp_die(__( 'You do not have sufficient permissions to access this page.', 'quform'), 403);
        }

        // Deal with read/unread/delete actions
        $this->processActions();

        if (isset($_GET['id']) && Quform::isNonEmptyString($_GET['id'])) {
            $config = $this->repository->getConfig((int) $_GET['id']);

            if ($config === null) {
                wp_die(esc_html__('The form could not be found. Perhaps it was deleted?', 'quform'));
            }
        } else {
            $config = $this->repository->firstConfig();

            if ($config === null) {
                // There are no forms, redirect
                wp_safe_redirect(admin_url('admin.php?page=quform.forms'));
                exit;
            }
        }

        $config['environment'] = 'listEntry';

        $form = $this->formFactory->create($config);

        $this->addPageMessages();

        $table = new Quform_Entry_List_Table($this, $form, $this->repository, $this->options);
        $table->prepare_items();

        $perPage = get_user_meta(get_current_user_id(), 'quform_entries_per_page', true);
        if ( ! is_numeric($perPage)) {
            $perPage = 20;
        }

        $this->view->with(array(
            'form' => $form,
            'table' => $table,
            'perPage' => $perPage,
            'labels' => $this->repository->getFormEntryLabels($form->getId())
        ));

        add_filter('removable_query_args', array($this, 'removableQueryArgs'));
    }

    protected function processActions()
    {
        $nonce = Quform::get($_GET, '_wpnonce');
        $action = null;
        $ids = array();

        if (isset($_GET['eid'])) {
            $action = Quform::get($_GET, 'action');
            $ids = (int) $_GET['eid'];
        } elseif (isset($_GET['eids'])) {
            $action = $this->getBulkAction();
            $ids = (array) Quform::get($_GET, 'eids');
            $ids = array_map('intval', $ids);
        } elseif (isset($_GET['delete_all'])) {
            $action = 'delete_all';
        }

        if ($action == null) {
            if (Quform::get($_GET, '_wp_http_referer')) {
                wp_safe_redirect(esc_url_raw(remove_query_arg(array('_wp_http_referer', '_wpnonce'), wp_unslash($_SERVER['REQUEST_URI']))));
                exit;
            }

            return;
        }

        $returnUrl = remove_query_arg(array('action', 'action2', 'eid', 'eids', 'read', 'unread', 'trashed', 'deleted', 'error'), wp_get_referer());

        switch ($action) {
            case 'read':
                $result = $this->processReadAction($ids, $nonce);
                $returnUrl = add_query_arg($result, $returnUrl);
                break;
            case 'unread':
                $result = $this->processUnreadAction($ids, $nonce);
                $returnUrl = add_query_arg($result, $returnUrl);
                break;
            case 'trash':
                $result = $this->processTrashAction($ids, $nonce);
                $returnUrl = add_query_arg($result, $returnUrl);
                break;
            case 'untrash':
                $result = $this->processUntrashAction($ids, $nonce);
                $returnUrl = add_query_arg($result, $returnUrl);
                break;
            case 'delete':
                $result = $this->processDeleteAction($ids, $nonce);
                $returnUrl = add_query_arg($result, $returnUrl);
                break;
            case 'delete_all':
                $ids = $this->repository->getEntryIdsByStatus((int) Quform::get($_GET, 'id'), 'trash');
                $result = $this->processDeleteAction($ids, $nonce);
                $returnUrl = add_query_arg($result, $returnUrl);
                break;
        }

        wp_safe_redirect(esc_url_raw($returnUrl));
        exit;
    }

    /**
     * Process marking entries as read
     *
     * @param   int|array  $entryIds
     * @param   string     $nonce
     * @return  array      The result message
     */
    protected function processReadAction($entryIds, $nonce)
    {
        if (is_array($entryIds)) {
            $nonceAction = 'bulk-qfb-entries';
        } else {
            $nonceAction = 'quform_read_entry_' . $entryIds;
            $entryIds = array($entryIds);
        }

        if ( ! $nonce || ! count($entryIds)) {
            return array('error' => self::BAD_REQUEST);
        }

        if ( ! current_user_can('quform_view_entries')) {
            return array('error' => self::NO_PERMISSION);
        }

        if ( ! wp_verify_nonce($nonce, $nonceAction)) {
            return array('error' => self::NONCE_CHECK_FAILED);
        }

        $count = $this->repository->readEntries($entryIds);

        return array('read' => $count);
    }

    /**
     * Process marking entries as unread
     *
     * @param   int|array  $entryIds
     * @param   string     $nonce
     * @return  array      The result message
     */
    protected function processUnreadAction($entryIds, $nonce)
    {
        if (is_array($entryIds)) {
            $nonceAction = 'bulk-qfb-entries';
        } else {
            $nonceAction = 'quform_unread_entry_' . $entryIds;
            $entryIds = array($entryIds);
        }

        if ( ! $nonce || ! count($entryIds)) {
            return array('error' => self::BAD_REQUEST);
        }

        if ( ! current_user_can('quform_view_entries')) {
            return array('error' => self::NO_PERMISSION);
        }

        if ( ! wp_verify_nonce($nonce, $nonceAction)) {
            return array('error' => self::NONCE_CHECK_FAILED);
        }

        $count = $this->repository->unreadEntries($entryIds);

        return array('unread' => $count);
    }

    /**
     * Process trashing entries
     *
     * @param   int|array  $entryIds
     * @param   string     $nonce
     * @return  array      The result message
     */
    protected function processTrashAction($entryIds, $nonce)
    {
        if (is_array($entryIds)) {
            $nonceAction = 'bulk-qfb-entries';
        } else {
            $nonceAction = 'quform_trash_entry_' . $entryIds;
            $entryIds = array($entryIds);
        }

        if ( ! $nonce || ! count($entryIds)) {
            return array('error' => self::BAD_REQUEST);
        }

        if ( ! current_user_can('quform_delete_entries')) {
            return array('error' => self::NO_PERMISSION);
        }

        if ( ! wp_verify_nonce($nonce, $nonceAction)) {
            return array('error' => self::NONCE_CHECK_FAILED);
        }

        $count = $this->repository->trashEntries($entryIds);

        return array('trashed' => $count);
    }

    /**
     * Process trashing entries
     *
     * @param   int|array  $entryIds
     * @param   string     $nonce
     * @return  array      The result message
     */
    protected function processUntrashAction($entryIds, $nonce)
    {
        if (is_array($entryIds)) {
            $nonceAction = 'bulk-qfb-entries';
        } else {
            $nonceAction = 'quform_untrash_entry_' . $entryIds;
            $entryIds = array($entryIds);
        }

        if ( ! $nonce || ! count($entryIds)) {
            return array('error' => self::BAD_REQUEST);
        }

        if ( ! current_user_can('quform_delete_entries')) {
            return array('error' => self::NO_PERMISSION);
        }

        if ( ! wp_verify_nonce($nonce, $nonceAction)) {
            return array('error' => self::NONCE_CHECK_FAILED);
        }

        $count = $this->repository->untrashEntries($entryIds);

        return array('untrashed' => $count);
    }

    /**
     * Process deleting entries
     *
     * @param   int|array  $entryIds
     * @param   string     $nonce
     * @return  array      The result message
     */
    protected function processDeleteAction($entryIds, $nonce)
    {
        if (is_array($entryIds)) {
            $nonceAction = 'bulk-qfb-entries';
        } else {
            $nonceAction = 'quform_delete_entry_' . $entryIds;
            $entryIds = array($entryIds);
        }

        if ( ! $nonce || ! count($entryIds)) {
            return array('error' => self::BAD_REQUEST);
        }

        if ( ! current_user_can('quform_delete_entries')) {
            return array('error' => self::NO_PERMISSION);
        }

        if ( ! wp_verify_nonce($nonce, $nonceAction)) {
            return array('error' => self::NONCE_CHECK_FAILED);
        }

        $count = $this->repository->deleteEntries($entryIds);

        return array('deleted' => $count);
    }

    /**
     * Get the name of the bulk action
     *
     * @return string|null
     */
    protected function getBulkAction()
    {
        $action = null;

        $a1 = Quform::get($_GET, 'action', '-1');
        $a2 = Quform::get($_GET, 'action2', '-1');

        if ($a1 != '-1') {
            $action = $a1;
        } elseif ($a2 != '-1') {
            $action = $a2;
        }

        return $action;
    }

    /**
     * Add messages to the page based on the query vars
     */
    protected function addPageMessages()
    {
        $read = (int) Quform::get($_GET, 'read');
        if ($read > 0) {
            /* translators: %s: the number of entries */
            $this->addMessage('success', sprintf(_n('%s entry marked as read', '%s entries marked as read', $read, 'quform'), number_format_i18n($read)));
        }

        $unread = (int) Quform::get($_GET, 'unread');
        if ($unread > 0) {
            /* translators: %s: the number of entries */
            $this->addMessage('success', sprintf(_n('%s entry marked as unread', '%s entries marked as unread', $unread, 'quform'), number_format_i18n($unread)));
        }

        $trashed = (int) Quform::get($_GET, 'trashed');
        if ($trashed > 0) {
            /* translators: %s: the number of entries */
            $this->addMessage('success', sprintf(_n('%s entry moved to the Trash', '%s entries moved to the Trash', $trashed, 'quform'), number_format_i18n($trashed)));
        }

        $untrashed = (int) Quform::get($_GET, 'untrashed');
        if ($untrashed > 0) {
            /* translators: %s: the number of entries */
            $this->addMessage('success', sprintf(_n('%s entry restored', '%s entries restored', $untrashed, 'quform'), number_format_i18n($untrashed)));
        }

        $deleted = (int) Quform::get($_GET, 'deleted');
        if ($deleted > 0) {
            /* translators: %s: the number of entries */
            $this->addMessage('success', sprintf(_n('%s entry deleted', '%s entries deleted', $deleted, 'quform'), number_format_i18n($deleted)));
        }

        switch ((int) Quform::get($_GET, 'error')) {
            case self::BAD_REQUEST:
                $this->addMessage('error', __('Bad request.', 'quform'));
                break;
            case self::NO_PERMISSION:
                $this->addMessage('error', __('You do not have permission to do this.', 'quform'));
                break;
            case self::NONCE_CHECK_FAILED:
                $this->addMessage('error', __('Nonce check failed.', 'quform'));
                break;
        }
    }

    /**
     * Additional query arguments that can be hidden by history.replaceState
     *
     * @param   array  $args
     * @return  array
     */
    public function removableQueryArgs($args)
    {
        $args[] = 'read';
        $args[] = 'unread';

        return $args;
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
            '<div class="qfb-nav-item qfb-nav-page-info"><i class="qfb-nav-page-icon qfb-mdi qfb-mdi-message"></i><span class="qfb-nav-page-title">%s</span></div>',
            /* translators: %s: the form name */
            Quform::escape(sprintf(__('Entries for %s', 'quform'), $currentForm['name']))
        );

        $extra[50] = '<div class="qfb-nav-item qfb-nav-item-right"><a id="qfb-show-entries-table-settings" class="qfb-nav-item-link"><i class="qfb-mdi qfb-mdi-settings"></i></a></div>';

        return parent::getNavHtml($currentForm, $extra);
    }

    /**
     * Get the HTML for an entry label for the entry label settings
     *
     * @param   array|null  $label
     * @return  string
     */
    public function getEntryLabelEditHtml(array $label = null)
    {
        $output = sprintf(
            '<div class="qfb-entry-label-edit qfb-cf"%s%s>',
            is_array($label) ? sprintf(' data-label="%s"', Quform::escape(wp_json_encode($label))) : '',
            is_array($label) ? sprintf(' style="background-color: %s;"', Quform::escape($label['color'])) : ''
        );

        $output .= sprintf(
            '<span class="qfb-entry-label-edit-name" title="%s">%s</span>',
            esc_attr__('Click to edit name', 'quform'),
            is_array($label) ? Quform::escape($label['name']) : ''
        );

        $output .= '<div class="qfb-entry-label-edit-actions">';
        $output .= '<span class="qfb-entry-label-edit-action-color"><i class="qfb-mdi qfb-mdi-format_color_fill"></i></span>';
        $output .= '<span class="qfb-entry-label-edit-action-duplicate"><i class="qfb-mdi qfb-mdi-content_copy"></i></span>';
        $output .= '<span class="qfb-entry-label-edit-action-remove"><i class="qfb-icon qfb-icon-trash"></i></span>';
        $output .= '</div></div>';

        return $output;
    }
}
