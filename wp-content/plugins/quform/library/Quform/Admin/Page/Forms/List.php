<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Admin_Page_Forms_List extends Quform_Admin_Page
{
    /**
     * @var Quform_ScriptLoader
     */
    protected $scriptLoader;

    /**
     * @var Quform_Form_List_Table
     */
    protected $table;

    /**
     * @param  Quform_ViewFactory      $viewFactory
     * @param  Quform_Repository       $repository
     * @param  Quform_ScriptLoader     $scriptLoader
     * @param  Quform_Form_List_Table  $table
     */
    public function __construct(Quform_ViewFactory $viewFactory, Quform_Repository $repository,
                                Quform_ScriptLoader $scriptLoader, Quform_Form_List_Table $table)
    {
        parent::__construct($viewFactory, $repository);

        $this->scriptLoader = $scriptLoader;
        $this->table = $table;
    }

    public function init()
    {
        $this->template = QUFORM_TEMPLATE_PATH .  '/admin/forms/list.php';
    }

    protected function enqueueScripts()
    {
        parent::enqueueScripts();

        wp_enqueue_script('quform-forms', Quform::adminUrl('js/forms.list.min.js'), array('jquery'), QUFORM_VERSION, true);

        wp_localize_script('quform-forms', 'quformFormsListL10n', array(
            'singleConfirmDelete' => __('Are you sure you want to delete this form? All saved settings, elements and entries for this form will be lost and this cannot be undone.', 'quform'),
            'pluralConfirmDelete' => __('Are you sure you want to delete these forms? All saved settings, elements and entries for these forms will be lost and this cannot be undone.', 'quform'),
            'saveFormsTableSettingsNonce' => wp_create_nonce('quform_save_forms_table_settings'),
            'addFormNonce' => wp_create_nonce('quform_add_form'),
            'errorAddingForm' => __('An error occurred adding the form', 'quform')
        ));
    }

    /**
     * Process this page and send data to the view
     */
    public function process()
    {
        if ( ! current_user_can('quform_list_forms')) {
            wp_die(__( 'You do not have sufficient permissions to access this page.', 'quform'), 403);
        }

        $this->processActions();

        $this->addPageMessages();

        $this->table->prepare_items();

        $perPage = get_user_meta(get_current_user_id(), 'quform_forms_per_page', true);
        if ( ! is_numeric($perPage)) {
            $perPage = 20;
        }

        $this->view->with(array(
            'table' => $this->table,
            'perPage' => $perPage
        ));

        add_filter('removable_query_args', array($this, 'removableQueryArgs'));
    }

    /**
     * Process actions on the form list
     */
    protected function processActions()
    {
        $nonce = Quform::get($_GET, '_wpnonce');
        $action = null;
        $ids = array();

        if (isset($_GET['id'])) {
            $action = Quform::get($_GET, 'action');
            $ids = (int) $_GET['id'];
        } elseif (isset($_GET['ids'])) {
            $action = $this->getBulkAction();
            $ids = (array) Quform::get($_GET, 'ids');
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

        $returnUrl = remove_query_arg(array('action', 'action2', 'id', 'ids', 'activated', 'deactivated', 'duplicated', 'trashed', 'restored', 'deleted', 'error'), wp_get_referer());

        switch ($action) {
            case 'activate':
                $result = $this->processActivateAction($ids, $nonce);
                $returnUrl = add_query_arg($result, $returnUrl);
                break;
            case 'deactivate':
                $result = $this->processDeactivateAction($ids, $nonce);
                $returnUrl = add_query_arg($result, $returnUrl);
                break;
            case 'duplicate':
                $result = $this->processDuplicateAction($ids, $nonce);
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
                $ids = $this->repository->getTrashedFormIds();
                $result = $this->processDeleteAction($ids, $nonce);
                $returnUrl = add_query_arg($result, $returnUrl);
                break;
        }

        wp_safe_redirect(esc_url_raw($returnUrl));
        exit;
    }

    /**
     * Process activating forms
     *
     * @param   int|array  $ids    The form ID or array of IDs
     * @param   string     $nonce  The nonce to check for validity
     * @return  array              The result message
     */
    protected function processActivateAction($ids, $nonce)
    {
        if (is_array($ids)) {
            $nonceAction = 'bulk-qfb-forms';
        } else {
            $nonceAction = 'quform_activate_form_' . $ids;
            $ids = array($ids);
        }

        if ( ! $nonce ||  ! count($ids)) {
            return array('error' => self::BAD_REQUEST);
        }

        if ( ! current_user_can('quform_edit_forms')) {
            return array('error' => self::NO_PERMISSION);
        }

        if ( ! wp_verify_nonce($nonce, $nonceAction)) {
            return array('error' => self::NONCE_CHECK_FAILED);
        }

        $count = $this->repository->activateForms($ids);

        $this->scriptLoader->handleActivateForms($ids);

        return array('activated' => $count);
    }

    /**
     * Process deactivating forms
     *
     * @param   int|array  $ids    The form ID or array of IDs
     * @param   string     $nonce  The nonce to check for validity
     * @return  array              The result message
     */
    protected function processDeactivateAction($ids, $nonce)
    {
        if (is_array($ids)) {
            $nonceAction = 'bulk-qfb-forms';
        } else {
            $nonceAction = 'quform_deactivate_form_' . $ids;
            $ids = array($ids);
        }

        if ( ! $nonce ||  ! count($ids)) {
            return array('error' => self::BAD_REQUEST);
        }

        if ( ! current_user_can('quform_edit_forms')) {
            return array('error' => self::NO_PERMISSION);
        }

        if ( ! wp_verify_nonce($nonce, $nonceAction)) {
            return array('error' => self::NONCE_CHECK_FAILED);
        }

        $count = $this->repository->deactivateForms($ids);

        $this->scriptLoader->handleDeactivateForms($ids);

        return array('deactivated' => $count);
    }

    /**
     * Process duplicating forms
     *
     * @param   int|array  $ids    The form ID or array of IDs
     * @param   string     $nonce  The nonce to check for validity
     * @return  array              The result message
     */
    protected function processDuplicateAction($ids, $nonce)
    {
        if (is_array($ids)) {
            $nonceAction = 'bulk-qfb-forms';
        } else {
            $nonceAction = 'quform_duplicate_form_' . $ids;
            $ids = array($ids);
        }

        if ( ! $nonce ||  ! count($ids)) {
            return array('error' => self::BAD_REQUEST);
        }

        if ( ! current_user_can('quform_add_forms')) {
            return array('error' => self::NO_PERMISSION);
        }

        if ( ! wp_verify_nonce($nonce, $nonceAction)) {
            return array('error' => self::NONCE_CHECK_FAILED);
        }

        $newIds = $this->repository->duplicateForms($ids);

        $this->scriptLoader->handleDuplicateForms($newIds);

        return array('duplicated' => count($newIds));
    }

    /**
     * Process trashing forms
     *
     * @param   int|array  $ids    The form ID or array of IDs
     * @param   string     $nonce  The nonce to check for validity
     * @return  array              The result message
     */
    protected function processTrashAction($ids, $nonce)
    {
        if (is_array($ids)) {
            $nonceAction = 'bulk-qfb-forms';
        } else {
            $nonceAction = 'quform_trash_form_' . $ids;
            $ids = array($ids);
        }

        if ( ! $nonce || ! count($ids)) {
            return array('error' => self::BAD_REQUEST);
        }

        if ( ! current_user_can('quform_delete_forms')) {
            return array('error' => self::NO_PERMISSION);
        }

        if ( ! wp_verify_nonce($nonce, $nonceAction)) {
            return array('error' => self::NONCE_CHECK_FAILED);
        }

        $count = $this->repository->trashForms($ids);

        $this->scriptLoader->handleTrashForms($ids);

        return array('trashed' => $count);
    }

    /**
     * Process un-trashing forms
     *
     * @param   int|array  $ids    The form ID or array of IDs
     * @param   string     $nonce  The nonce to check for validity
     * @return  array              The result message
     */
    protected function processUntrashAction($ids, $nonce)
    {
        if (is_array($ids)) {
            $nonceAction = 'bulk-qfb-forms';
        } else {
            $nonceAction = 'quform_untrash_form_' . $ids;
            $ids = array($ids);
        }

        if ( ! $nonce || ! count($ids)) {
            return array('error' => self::BAD_REQUEST);
        }

        if ( ! current_user_can('quform_delete_forms')) {
            return array('error' => self::NO_PERMISSION);
        }

        if ( ! wp_verify_nonce($nonce, $nonceAction)) {
            return array('error' => self::NONCE_CHECK_FAILED);
        }

        $count = $this->repository->untrashForms($ids);

        $this->scriptLoader->handleUntrashForms($ids);

        return array('untrashed' => $count);
    }

    /**
     * Process deleting forms
     *
     * @param   int|array  $ids    The form ID or array of IDs
     * @param   string     $nonce  The nonce to check for validity
     * @return  array              The result message
     */
    protected function processDeleteAction($ids, $nonce)
    {
        if (is_array($ids)) {
            $nonceAction = 'bulk-qfb-forms';
        } else {
            $nonceAction = 'quform_delete_form_' . $ids;
            $ids = array($ids);
        }

        if ( ! $nonce || ! count($ids)) {
            return array('error' => self::BAD_REQUEST);
        }

        if ( ! current_user_can('quform_delete_forms')) {
            return array('error' => self::NO_PERMISSION);
        }

        if ( ! wp_verify_nonce($nonce, $nonceAction)) {
            return array('error' => self::NONCE_CHECK_FAILED);
        }

        $count = $this->repository->deleteForms($ids);

        return array('deleted' => $count);
    }

    /**
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
        $activated = (int) Quform::get($_GET, 'activated');
        if ($activated > 0) {
            /* translators: %s: the number of forms */
            $this->addMessage('success', sprintf(_n('%s form activated', '%s forms activated', $activated, 'quform'), number_format_i18n($activated)));
        }

        $deactivated = (int) Quform::get($_GET, 'deactivated');
        if ($deactivated > 0) {
            /* translators: %s: the number of forms */
            $this->addMessage('success', sprintf(_n('%s form deactivated', '%s forms deactivated', $deactivated, 'quform'), number_format_i18n($deactivated)));
        }

        $duplicated = (int) Quform::get($_GET, 'duplicated');
        if ($duplicated > 0) {
            /* translators: %s: the number of forms */
            $this->addMessage('success', sprintf(_n('%s form duplicated', '%s forms duplicated', $duplicated, 'quform'), number_format_i18n($duplicated)));
        }

        $trashed = (int) Quform::get($_GET, 'trashed');
        if ($trashed > 0) {
            /* translators: %s: the number of forms */
            $this->addMessage('success', sprintf(_n('%s form moved to the Trash', '%s forms moved to the Trash', $trashed, 'quform'), number_format_i18n($trashed)));
        }

        $untrashed = (int) Quform::get($_GET, 'untrashed');
        if ($untrashed > 0) {
            /* translators: %s: the number of forms */
            $this->addMessage('success', sprintf(_n('%s form restored', '%s forms restored', $untrashed, 'quform'), number_format_i18n($untrashed)));
        }

        $deleted = (int) Quform::get($_GET, 'deleted');
        if ($deleted > 0) {
            /* translators: %s: the number of forms */
            $this->addMessage('success', sprintf(_n('%s form deleted', '%s forms deleted', $deleted, 'quform'), number_format_i18n($deleted)));
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
        $args[] = 'deactivated';
        $args[] = 'duplicated';

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
        $extra[40] = sprintf('<div class="qfb-nav-item qfb-nav-page-info"><i class="qfb-nav-page-icon qfb-mdi qfb-mdi-view_stream"></i><span class="qfb-nav-page-title">%s</span></div>', esc_html__('Forms', 'quform'));
        $extra[50] = '<div class="qfb-nav-item qfb-nav-item-right"><a id="qfb-show-forms-table-settings" class="qfb-nav-item-link"><i class="qfb-mdi qfb-mdi-settings"></i></a></div>';

        return parent::getNavHtml($currentForm, $extra);
    }
}
