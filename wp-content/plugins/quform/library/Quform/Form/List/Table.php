<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Form_List_Table extends WP_List_Table
{
    /**
     * @var Quform_Repository
     */
    protected $repository;

    /**
     * @var Quform_Options
     */
    protected $options;

    /**
     * @var string|null
     */
    protected $view;

    /**
     * @param  Quform_Repository  $repository
     * @param  Quform_Options     $options
     */
    public function __construct(Quform_Repository $repository, Quform_Options $options)
    {
        parent::__construct(array(
            'singular' => 'qfb-form',
            'plural' => 'qfb-forms'
        ));

        $this->repository = $repository;
        $this->options = $options;
    }

    /**
     * Prepares the list of items for displaying
     */
    public function prepare_items()
    {
        $this->view = Quform::get($_GET, 'view');
        $perPage = $this->get_items_per_page('quform_forms_per_page');

        $args = array(
            'active' => null,
            'orderby' => $this->getOrderBy(strtolower((string) Quform::get($_GET, 'orderby'))),
            'order' => $this->getOrder(strtolower((string) Quform::get($_GET, 'order'))),
            'trashed' => false,
            'limit' => $perPage,
            'offset' => ($this->get_pagenum() - 1) * $perPage,
            'search' => isset($_GET['s']) && Quform::isNonEmptyString($_GET['s']) ? wp_unslash($_GET['s']) : ''
        );

        switch ($this->view) {
            case 'active':
                $args['active'] = true;
                break;
            case 'inactive':
                $args['active'] = false;
                break;
            case 'trashed':
                $args['trashed'] = true;
                break;
        }

        $this->items = $this->repository->getForms($args);

        $foundItems = $this->repository->getFoundRows();

        $this->set_pagination_args(array(
            'total_items' => $foundItems,
            'total_pages' => ceil($foundItems / $args['limit']),
            'per_page' => $args['limit']
        ));
    }

    /**
     * Display the list of views available on this table
     */
    public function views()
    {
        $views = $this->get_views();

        if (empty($views)) {
            return;
        }

        echo '<div class="qfb-sub-nav qfb-cf">';
        echo '<ul class="qfb-sub-nav-ul">';

        foreach ($views as $class => $view) {
            printf('<li class="qfb-view-%s">%s</li>', $class, $view);
        }

        echo '</ul>';
        echo '</div>';
    }

    /**
     * Get an associative array ( id => link ) with the list of views available on this table
     *
     * @return array
     */
    protected function get_views()
    {
        $isSearch = isset($_GET['s']) && Quform::isNonEmptyString($_GET['s']);
        $views = array();

        $views['all'] = sprintf(
            '<a href="%s" class="%s">%s <span class="count">(%s)</span></a>',
            esc_url(admin_url('admin.php?page=quform.forms')),
            $this->view === null && !$isSearch ? 'qfb-current' : '',
            esc_html__('All', 'quform'),
            number_format_i18n($this->repository->count())
        );

        $views['active'] = sprintf(
            '<a href="%s" class="%s">%s <span class="count">(%s)</span></a>',
            esc_url(admin_url('admin.php?page=quform.forms&view=active')),
            $this->view === 'active' && !$isSearch ? 'qfb-current' : '',
            esc_html__('Active', 'quform'),
            number_format_i18n($this->repository->count(true))
        );

        $views['inactive'] = sprintf(
            '<a href="%s" class="%s">%s <span class="count">(%s)</span></a>',
            esc_url(admin_url('admin.php?page=quform.forms&view=inactive')),
            $this->view === 'inactive' && !$isSearch ? 'qfb-current' : '',
            esc_html__('Inactive', 'quform'),
            number_format_i18n($this->repository->count(false))
        );

        $views['trash'] = sprintf(
            '<a href="%s" class="%s">%s <span class="count">(%s)</span></a>',
            esc_url(admin_url('admin.php?page=quform.forms&view=trashed')),
            $this->view === 'trashed' && !$isSearch ? 'qfb-current' : '',
            esc_html__('Trash', 'quform'),
            number_format_i18n($this->repository->count(null, true))
        );

        if ($isSearch) {
            $views['search'] = sprintf(
                '<a class="qfb-current">%s <span class="count">(%s)</span></a>',
                esc_html(sprintf(__('Search results for &#8220;%s&#8221;', 'quform'), wp_unslash($_GET['s']))),
                number_format_i18n($this->_pagination_args['total_items'])
            );
        }

        return $views;
    }

    /**
     * Get the list of columns
     *
     * @return array
     */
    public function get_columns()
    {
        return array(
            'cb' => '<input type="checkbox" />',
            'name' => esc_html__('Name', 'quform'),
            'shortcode' => '',
            'entries' => esc_html__('Entries', 'quform'),
            'active' => esc_html__('Active', 'quform'),
            'updated_at' => esc_html__('Last modified', 'quform')
        );
    }

    /**
     * Get the checkbox column content for the given item
     *
     * @param   array   $item
     * @return  string
     */
    protected function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="ids[]" value="%s" />', $item['id']);
    }

    /**
     * Get the name column content for the given item
     *
     * @param   array   $item
     * @return  string
     */
    protected function column_name($item)
    {
        $output = '<strong>';

        if (current_user_can('quform_edit_forms') && $item['trashed'] != '1') {
            $output .= sprintf(
                '<a href="%s" aria-label="%s">%s</a>',
                esc_url(add_query_arg(array('id' => $item['id']), admin_url('admin.php?page=quform.forms&sp=edit'))),
                /* translators: %s: the form name */
                sprintf(esc_attr__('Edit form &#8220;%s&#8221;', 'quform'), Quform::escape($item['name'])),
                Quform::escape($item['name'])
            );
        } else {
            $output .= Quform::escape($item['name']);
        }

        $output .= '</strong>';

        return $output;
    }

    /**
     * Get the shortcode column content for the given item
     *
     * @param   array   $item
     * @return  string
     */
    protected function column_shortcode($item)
    {
        $shortcode = sprintf('[quform id="%s" name="%s"]', $item['id'], $item['name']);

        $output = sprintf(
            '<input type="text" value="%s" size="%s" readonly>',
            Quform::escape($shortcode),
            esc_attr(Quform::strlen($shortcode))
        );

        return $output;
    }

    /**
     * Generates and display row actions links for the list table
     *
     * @param   array   $item         The item being acted upon
     * @param   string  $column_name  Current column name
     * @param   string  $primary      Primary column name
     * @return  string                The row actions HTML, or an empty string if the current column is not the primary column
     */
    protected function handle_row_actions($item, $column_name, $primary)
    {
        if ($column_name != $primary) {
            return '';
        }

        $actions = array();

        if ($item['trashed'] == '0') {
            if (current_user_can('quform_edit_forms')) {
                $actions['edit'] = sprintf(
                    '<a href="%s" aria-label="%s">%s</a>',
                    esc_url(add_query_arg(array('id' => $item['id']), admin_url('admin.php?page=quform.forms&sp=edit'))),
                    sprintf(esc_attr__('Edit form &#8220;%s&#8221;', 'quform'), Quform::escape($item['name'])),
                    esc_html__('Edit', 'quform')
                );
            }

            if (current_user_can('quform_view_entries')) {
                $actions['entries'] = sprintf(
                    '<a href="%s" aria-label="%s">%s</a>',
                    esc_url(add_query_arg(array('id' => $item['id']), admin_url('admin.php?page=quform.entries'))),
                    /* translators: %s: the form name */
                    sprintf(esc_attr__('View submitted entries for &#8220;%s&#8221;', 'quform'), Quform::escape($item['name'])),
                    esc_html__('Entries', 'quform')
                );
            }

            if (current_user_can('quform_edit_forms')) {
                if ($item['active'] == '1') {
                    $deactivateUrl = admin_url('admin.php?page=quform.forms&action=deactivate');
                    $deactivateNonce = wp_create_nonce('quform_deactivate_form_' . $item['id']);

                    $actions['deactivate'] = sprintf(
                        '<a href="%s" aria-label="%s">%s</a>',
                        esc_url(add_query_arg(array('id' => $item['id'], '_wpnonce' => $deactivateNonce), $deactivateUrl)),
                        /* translators: %s: the form name */
                        sprintf(esc_attr__('Deactivate form &#8220;%s&#8221;', 'quform'), Quform::escape($item['name'])),
                        esc_html__('Deactivate', 'quform')
                    );
                } else {
                    $activateUrl = admin_url('admin.php?page=quform.forms&action=activate');
                    $activateNonce = wp_create_nonce('quform_activate_form_' . $item['id']);

                    $actions['activate'] = sprintf(
                        '<a href="%s" aria-label="%s">%s</a>',
                        esc_url(add_query_arg(array('id' => $item['id'], '_wpnonce' => $activateNonce), $activateUrl)),
                        /* translators: %s: the form name */
                        sprintf(esc_attr__('Activate form &#8220;%s&#8221;', 'quform'), Quform::escape($item['name'])),
                        esc_html__('Activate', 'quform')
                    );
                }
            }

            if (current_user_can('quform_add_forms')) {
                $duplicateUrl = admin_url('admin.php?page=quform.forms&action=duplicate');
                $duplicateNonce = wp_create_nonce('quform_duplicate_form_' . $item['id']);

                $actions['duplicate'] = sprintf(
                    '<a href="%s" aria-label="%s">%s</a>',
                    esc_url(add_query_arg(array('id' => $item['id'], '_wpnonce' => $duplicateNonce), $duplicateUrl)),
                    /* translators: %s: the form name */
                    sprintf(esc_attr__('Duplicate form &#8220;%s&#8221;', 'quform'), Quform::escape($item['name'])),
                    esc_html__('Duplicate', 'quform')
                );
            }

            if (current_user_can('quform_delete_forms')) {
                $trashUrl = admin_url('admin.php?page=quform.forms&action=trash');
                $trashNonce = wp_create_nonce('quform_trash_form_' . $item['id']);

                $actions['trash'] = sprintf(
                    '<a href="%s" aria-label="%s">%s</a>',
                    esc_url(add_query_arg(array('id' => $item['id'], '_wpnonce' => $trashNonce), $trashUrl)),
                    /* translators: %s: the form name */
                    sprintf(esc_attr__('Move form &#8220;%s&#8221; to the Trash', 'quform'), Quform::escape($item['name'])),
                    esc_html__('Trash', 'quform')
                );
            }
        } else {
            if (current_user_can('quform_delete_forms')) {
                $untrashUrl = admin_url('admin.php?page=quform.forms&action=untrash');
                $untrashNonce = wp_create_nonce('quform_untrash_form_' . $item['id']);

                $actions['untrash'] = sprintf(
                    '<a href="%s" aria-label="%s">%s</a>',
                    esc_url(add_query_arg(array('id' => $item['id'], '_wpnonce' => $untrashNonce), $untrashUrl)),
                    /* translators: %s: the form name */
                    sprintf(esc_attr__('Restore form &#8220;%s&#8221; from the Trash', 'quform'), Quform::escape($item['name'])),
                    esc_html__('Restore', 'quform')
                );

                $deleteUrl = admin_url('admin.php?page=quform.forms&action=delete');
                $deleteNonce = wp_create_nonce('quform_delete_form_' . $item['id']);

                $actions['delete'] = sprintf(
                    '<a href="%s" aria-label="%s">%s</a>',
                    esc_url(add_query_arg(array('id' => $item['id'], '_wpnonce' => $deleteNonce), $deleteUrl)),
                    /* translators: %s: the form name */
                    sprintf(esc_attr__('Delete form &#8220;%s&#8221; permanently', 'quform'), Quform::escape($item['name'])),
                    esc_html__('Delete permanently', 'quform')
                );
            }
        }

        return $this->row_actions($actions);
    }

    /**
     * Get the entries column content for the given item
     *
     * @param   array   $item
     * @return  string
     */
    protected function column_entries($item)
    {
        if ($item['unread'] > 0) {
            $count = sprintf(
                '<strong>%s (%s)</strong>',
                /* translators: %s: the number of unread entries */
                esc_html(sprintf(_n('%s unread', '%s unread', $item['unread'], 'quform'), number_format_i18n($item['unread']))),
                /* translators: %s: the number entries */
                esc_html(sprintf(_n('%s entry', '%s entries', $item['entries'], 'quform'), number_format_i18n($item['entries'])))
            );
        } else {
            /* translators: %s: the number entries */
            $count = esc_html(sprintf(_n('%s entry', '%s entries', $item['entries'], 'quform'), number_format_i18n($item['entries'])));
        }

        if (current_user_can('quform_view_entries')) {
            $output = sprintf(
                '<a href="%s">%s</a>',
                esc_url(admin_url('admin.php?page=quform.entries&id=' . $item['id'])),
                $count
            );
        } else {
            $output = $count;
        }

        return $output;
    }

    /**
     * Get the active column content for the given item
     *
     * @param   array   $item
     * @return  string
     */
    protected function column_active($item)
    {
        return $item['active'] == '1' ? esc_html__('Yes', 'quform') : esc_html__('No', 'quform');
    }

    /**
     * Get the updated_at column content for the given item
     *
     * @param   array   $item
     * @return  string
     */
    protected function column_updated_at($item)
    {
        return esc_html($this->options->formatDate($item['updated_at'], true));
    }

    /**
     * Get the list of sortable columns
     *
     * @return array
     */
    protected function get_sortable_columns()
    {
        $orderBy = $this->getOrderBy();
        $isAsc = $this->getOrder() == 'asc';

        return array(
            'name' => array('name', $orderBy == 'name' && $isAsc),
            'shortcode' => array('id', $orderBy == 'id' && $isAsc),
            'entries' => array('entries', ! ($orderBy == 'entries' && ! $isAsc)), // Default desc
            'active' => array('active', $orderBy == 'active' && $isAsc),
            'updated_at' => array('updated_at', ! ($orderBy == 'updated_at' && ! $isAsc)) // Default desc
        );
    }

    /**
     * Get an associative array ( option_name => option_title ) with the list
     * of bulk actions available on this table
     *
     * @return array
     */
    protected function get_bulk_actions()
    {
        $actions = array();

        if ($this->view == 'trashed') {
            if (current_user_can('quform_delete_forms')) {
                $actions['untrash'] = __('Restore', 'quform');
                $actions['delete'] = __('Delete permanently', 'quform');
            }
        } else {
            if (current_user_can('quform_edit_forms')) {
                $actions['activate'] = __('Activate', 'quform');
                $actions['deactivate'] = __('Deactivate', 'quform');
            }

            if (current_user_can('quform_add_forms')) {
                $actions['duplicate'] = __('Duplicate', 'quform');
            }

            if (current_user_can('quform_delete_forms')) {
                $actions['trash'] = __('Move to Trash', 'quform');
            }
        }

        return $actions;
    }

    /**
     * Message to be displayed when there are no forms
     */
    public function no_items() {
        if (isset($_GET['s']) && Quform::isNonEmptyString($_GET['s'])) {
            esc_html_e('Your search did not match any forms.', 'quform');
        } elseif ($this->view == 'trashed') {
            esc_html_e('No forms found in Trash.', 'quform');
        } elseif (current_user_can('quform_add_forms')) {
            printf(
                /* translators: %1$s: open link tag, %2$s close link tag */
                esc_html__('No forms found, %1$sclick here%2$s to create one.', 'quform'),
                sprintf('<a href="%s">', esc_url(admin_url('admin.php?page=quform.forms&sp=add'))),
                '</a>'
            );
        } else {
            esc_html_e('No forms found.', 'quform');
        }
    }

    /**
     * Displays the search box
     *
     * Duplicate of the parent function, but still shows the search box if there are no items
     *
     * @param string $text     The 'submit' button label.
     * @param string $input_id ID attribute value for the search input field.
     */
    public function search_box( $text, $input_id ) {
        $input_id = $input_id . '-search-input';

        if ( ! empty( $_REQUEST['orderby'] ) )
            echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
        if ( ! empty( $_REQUEST['order'] ) )
            echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
        if ( ! empty( $_REQUEST['post_mime_type'] ) )
            echo '<input type="hidden" name="post_mime_type" value="' . esc_attr( $_REQUEST['post_mime_type'] ) . '" />';
        if ( ! empty( $_REQUEST['detached'] ) )
            echo '<input type="hidden" name="detached" value="' . esc_attr( $_REQUEST['detached'] ) . '" />';
        ?>
        <p class="search-box">
            <label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo $text; ?>:</label>
            <input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" />
            <?php submit_button( $text, '', '', false, array( 'id' => 'search-submit' ) ); ?>
        </p>
        <?php
    }

    /**
     * Get the order by value
     *
     * Gets the user meta setting if a value is saved
     *
     * @param   string  $requestedOrderBy  The requested order by from $_GET
     * @return  string
     */
    protected function getOrderBy($requestedOrderBy = '')
    {
        $currentUserId = get_current_user_id();
        $userOrderBy = get_user_meta($currentUserId, 'quform_forms_order_by', true);

        if (Quform::isNonEmptyString($requestedOrderBy)) {
            $orderBy = $requestedOrderBy;

            if ($requestedOrderBy != $userOrderBy) {
                update_user_meta($currentUserId, 'quform_forms_order_by', $requestedOrderBy);
            }
        } elseif (Quform::isNonEmptyString($userOrderBy)) {
            $orderBy = $userOrderBy;
        } else {
            $orderBy = 'updated_at';
        }

        return $orderBy;
    }

    /**
     * Get the order value ('asc' or 'desc')
     *
     * Gets the user meta setting if a value is saved
     *
     * @param   string  $requestedOrder  The requested order from $_GET
     * @return  string
     */
    protected function getOrder($requestedOrder = '')
    {
        $currentUserId = get_current_user_id();
        $userOrder = get_user_meta($currentUserId, 'quform_forms_order', true);

        if (Quform::isNonEmptyString($requestedOrder)) {
            $order = $requestedOrder;

            if ($requestedOrder != $userOrder) {
                update_user_meta($currentUserId, 'quform_forms_order', $requestedOrder);
            }
        } elseif (Quform::isNonEmptyString($userOrder)) {
            $order = $userOrder;
        } else {
            $order = 'desc';
        }

        return $order;
    }

    /**
     * Add an Empty Trash button to the Trash view
     *
     * @param string $which
     */
    protected function extra_tablenav($which)
    {
        if ($which == 'top' && $this->view == 'trashed' && count($this->items) && current_user_can('quform_delete_forms')) {
            submit_button(__('Empty Trash', 'quform'), 'apply', 'delete_all', false);
        }
    }
}
