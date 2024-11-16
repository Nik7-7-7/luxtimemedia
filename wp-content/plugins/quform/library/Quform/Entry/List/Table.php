<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Entry_List_Table extends WP_List_Table
{
    /**
     * @var Quform_Admin_Page_Entries_List
     */
    protected $page;

    /**
     * @var Quform_Form
     */
    protected $form;

    /**
     * @var Quform_Repository
     */
    protected $repository;

    /**
     * @var Quform_Options
     */
    protected $options;

    /**
     * @var array
     */
    protected $labels;

    /**
     * @var string|null
     */
    protected $view;

    /**
     * @param  Quform_Admin_Page_Entries_List  $page
     * @param  Quform_Form                     $form
     * @param  Quform_Repository               $repository
     * @param  Quform_Options                  $options
     */
    public function __construct(Quform_Admin_Page_Entries_List $page, Quform_Form $form, Quform_Repository $repository, Quform_Options $options)
    {
        parent::__construct(array(
            'singular' => 'qfb-entry',
            'plural' => 'qfb-entries'
        ));

        $this->page = $page;
        $this->form = $form;
        $this->repository = $repository;
        $this->options = $options;
        $this->labels = $this->repository->getFormEntryLabels($form->getId());
    }

    /**
     * Prepares the list of items for displaying
     */
    public function prepare_items()
    {
        $this->view = Quform::get($_GET, 'view');
        $perPage = $this->get_items_per_page('quform_entries_per_page');

        $args = array(
            'unread' => null,
            'orderby' => $this->getOrderBy(strtolower((string) Quform::get($_GET, 'orderby'))),
            'order' => $this->getOrder(strtolower((string) Quform::get($_GET, 'order'))),
            'status' => 'normal',
            'limit' => $perPage,
            'offset' => ($this->get_pagenum() - 1) * $perPage,
            'search' => isset($_GET['s']) && Quform::isNonEmptyString($_GET['s']) ? wp_unslash($_GET['s']) : '',
            'labels' => isset($_GET['labels']) && is_array($_GET['labels']) ? $_GET['labels'] : array(),
            'label_operator' => isset($_GET['label_operator']) ? $_GET['label_operator'] : 'OR'
        );

        switch ($this->view) {
            case 'read':
                $args['unread'] = false;
                break;
            case 'unread':
                $args['unread'] = true;
                break;
            case 'trashed':
                $args['status'] = 'trash';
                break;
        }

        $args = apply_filters('quform_entry_list_table_args', $args, $this, $this->form);
        $args = apply_filters('quform_entry_list_table_args_' . $this->form->getId(), $args, $this, $this->form);

        $this->items = $this->repository->listEntries($this->form, $this->getColumns(), $args);

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
        $isSearch = (isset($_GET['s']) && Quform::isNonEmptyString($_GET['s'])) || isset($_GET['labels']);
        $views = array();

        $views['all'] = sprintf(
            '<a href="%s" class="%s">%s <span class="count">(%s)</span></a>',
            esc_url(admin_url(sprintf('admin.php?page=quform.entries&id=%d', $this->form->getId()))),
            $this->view === null && !$isSearch ? 'qfb-current' : '',
            esc_html__('All', 'quform'),
            number_format_i18n($this->repository->getEntryCount($this->form->getId()))
        );

        $views['unread'] = sprintf(
            '<a href="%s" class="%s">%s <span class="count">(%s)</span></a>',
            esc_url(admin_url(sprintf('admin.php?page=quform.entries&id=%d&view=unread', $this->form->getId()))),
            $this->view === 'unread' && !$isSearch ? 'qfb-current' : '',
            esc_html__('Unread', 'quform'),
            number_format_i18n($this->repository->getEntryCount($this->form->getId(), true))
        );

        $views['read'] = sprintf(
            '<a href="%s" class="%s">%s <span class="count">(%s)</span></a>',
            esc_url(admin_url(sprintf('admin.php?page=quform.entries&id=%d&view=read', $this->form->getId()))),
            $this->view === 'read' && !$isSearch ? 'qfb-current' : '',
            esc_html__('Read', 'quform'),
            number_format_i18n($this->repository->getEntryCount($this->form->getId(), false))
        );

        $views['trash'] = sprintf(
            '<a href="%s" class="%s">%s <span class="count">(%s)</span></a>',
            esc_url(admin_url(sprintf('admin.php?page=quform.entries&id=%d&view=trashed', $this->form->getId()))),
            $this->view === 'trashed' && !$isSearch ? 'qfb-current' : '',
            esc_html__('Trash', 'quform'),
            number_format_i18n($this->repository->getEntryCount($this->form->getId(), null, 'trash'))
        );

        if ($isSearch) {
            if (isset($_GET['s']) && Quform::isNonEmptyString($_GET['s'])) {
                /* translators: %s: the search term */
                $searchText = sprintf(__('Search results for &#8220;%s&#8221;', 'quform'), wp_unslash($_GET['s']));
            } else {
                $searchText = __('Search results', 'quform');
            }

            $views['search'] = sprintf(
                '<a class="qfb-current">%s <span class="count">(%s)</span></a>',
                esc_html($searchText),
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
        $columns = array('cb' => '<input type="checkbox" />');

        if (count($this->labels)) {
            $columns['labels'] = '';
        }

        if ($this->view != 'trashed') {
            $columns['icon'] = '';
        }

        foreach ($this->getColumns() as $column) {
            $columns[$column] = Quform::escape($this->getColumnLabel($column));
        }

        return $columns;
    }

    /**
     * Get the saved table layout columns or the default layout
     *
     * @return array
     */
    protected function getColumns()
    {
        $columns = $this->form->config('entriesTableColumns');

        if (count($columns)) {
            $columns = array_values(array_filter($columns, array($this, 'filterMissingElement')));
        } else {
            $columns = $this->getDefaultColumns();
        }

        $columns = apply_filters('quform_entry_list_table_columns', $columns, $this, $this->form);
        $columns = apply_filters('quform_entry_list_table_columns_' . $this->form->getId(), $columns, $this, $this->form);

        return $columns;
    }

    /**
     * Filter any elements that do not exist from the columns
     *
     * @param   string  $column
     * @return  bool
     */
    protected function filterMissingElement($column)
    {
        if (preg_match('/^element_\d+/', $column)) {
            $elementId = (int) str_replace('element_', '', $column);
            $element = $this->form->getElementById($elementId);

            if ( ! $element instanceof Quform_Element_Field) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the label for the given column
     *
     * @param   string  $column  The column key
     * @return  string
     */
    public function getColumnLabel($column)
    {
        $label = '';

        if (preg_match('/^element_\d+/', $column)) {
            static $adminLabelMap;

            if (!is_array($adminLabelMap)) {
                $adminLabelMap = array();

                foreach ($this->form->getRecursiveIterator(RecursiveIteratorIterator::SELF_FIRST) as $element) {
                    if ($element instanceof Quform_Element_Field) {
                        $adminLabelMap['element_' . $element->getId()] = $element->getAdminLabel();
                    }
                }
            }

            if (isset($adminLabelMap[$column])) {
                $label = $adminLabelMap[$column];
            }
        } else {
            switch ($column) {
                case 'created_at':
                    $label = __('Date', 'quform');
                    break;
                case 'updated_at':
                    $label = __('Last modified', 'quform');
                    break;
                case 'form_url':
                    $label = __('Form URL', 'quform');
                    break;
                case 'referring_url':
                    $label = __('Referring URL', 'quform');
                    break;
                case 'post_id':
                    $label = __('Page', 'quform');
                    break;
                case 'created_by':
                    $label = __('User', 'quform');
                    break;
                case 'id':
                    $label = __('ID', 'quform');
                    break;
                case 'ip':
                    $label = __('IP address', 'quform');
                    break;
            }
        }

        return $label;
    }

    /*
     * Get the list of default columns
     *
     * The list includes the created date and the first 4 fields
     *
     * @return array
     */
    protected function getDefaultColumns()
    {
        $columns = array('created_at');
        $i = 0;

        foreach ($this->form->getRecursiveIterator() as $element) {
            if ($element instanceof Quform_Element_Field && $element->config('saveToDatabase')) {
                $columns[] = sprintf('element_%d', $element->getId());
            }

            $i++;

            if ($i > 3) {
                break;
            }
        }

        return $columns;
    }

    /**
     * Get the list of all available columns
     *
     * @return array
     */
    protected function getAllColumns()
    {
        $columns = array('created_at', 'form_url', 'referring_url', 'post_id', 'created_by', 'updated_at', 'ip', 'id');

        foreach ($this->form->getRecursiveIterator() as $element) {
            if ($element instanceof Quform_Element_Field && $element->config('saveToDatabase')) {
                $columns[] = sprintf('element_%d', $element->getId());
            }
        }

        return $columns;
    }

    /**
     * Get the HTML for the column layout sortable
     *
     * @return string
     */
    public function getColumnLayoutSortableHtml()
    {
        $columns = $this->getColumns();

        $output = '<div class="qfb-entries-table-layout-active">';
        $output .= sprintf('<div class="qfb-entries-table-layout-heading">%s</div>', esc_html__('Active columns', 'quform'));
        $output .= '<div id="qfb-active-columns">';

        foreach ($columns as $column) {
            $output .= sprintf(
                '<div class="qfb-button" data-column="%s">%s</div>',
                Quform::escape($column),
                Quform::escape($this->getColumnLabel($column))
            );
        }

        $output .= '</div></div>';
        $output .= '<div class="qfb-entries-table-layout-inactive">';
        $output .= sprintf('<div class="qfb-entries-table-layout-heading">%s</div>', esc_html__('Inactive columns', 'quform'));
        $output .= '<div id="qfb-inactive-columns">';

        foreach ($this->getAllColumns() as $column) {
            if (in_array($column, $columns)) {
                continue;
            }

            $output .= sprintf(
                '<div class="qfb-button" data-column="%s">%s</div>',
                Quform::escape($column),
                Quform::escape($this->getColumnLabel($column))
            );
        }

        $output .= '</div></div>';

        return $output;
    }

    /**
     * Gets the name of the default primary column
     *
     * @return string Name of the default primary column
     */
    protected function get_default_primary_column_name() {
        $columns = $this->get_columns();
        $column = '';

        if (empty($columns)) {
            return $column;
        }

        foreach ($columns as $col => $column_name) {
            if ('cb' == $col || 'icon' == $col || 'labels' == $col) {
                continue;
            }

            $column = $col;
            break;
        }

        return $column;
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
        $columns = array();

        foreach ($this->getColumns() as $column) {
            if ($column == 'created_at') {
                $columns[$column] = array($column, ! ($orderBy == $column && ! $isAsc)); // Default desc
            } else {
                $columns[$column] = array($column, $orderBy == $column && $isAsc);
            }
        }

        return $columns;
    }

    /**
     * Generates content for a single row of the table
     *
     * @param array $item The current item
     */
    public function single_row($item)
    {
        $this->form->setEntryId((int) $item['id']);

        echo sprintf('<tr%s>', $item['unread'] == '1' ? ' class="qfb-entry-unread"' : '');
        $this->single_row_columns( $item );
        echo '</tr>';
    }

    /**
     * Get the checkbox column content for the given item
     *
     * @param   array   $item  The current item
     * @return  string
     */
    protected function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="eids[]" value="%s" />', $item['id']);
    }

    /**
     * Get the icon column content for the given item
     *
     * @param   array   $item     The current item
     * @param   string  $classes  The cell classes
     * @return  string
     */
    protected function _column_icon($item, $classes)
    {
        $output = sprintf('<th class="%s">', esc_attr($classes));

        if ($item['unread'] == '1') {
            $output .= '<i class="qfb-icon qfb-icon-envelope"></i>';
        } else {
            $output .= '<i class="qfb-icon qfb-icon-envelope-open-o"></i>';
        }

        $output .= '</th>';

        return $output;
    }

    /**
     * Get the labels column content for the given item
     *
     * @param   array   $item     The current item
     * @param   string  $classes  The cell classes
     * @return  string
     */
    protected function _column_labels($item, $classes)
    {
        $output = sprintf('<th class="%s" data-entry-id="%d">', esc_attr($classes), esc_attr($item['id']));

        $labels = array();

        if (Quform::isNonEmptyString($item['labels'])) {
            foreach (explode(',', $item['labels']) as $label) {
                $label = $this->getLabelById($label);

                if (is_array($label)) {
                    $labels[] = $label;
                }
            }
        }

        $output .= $this->page->getEntryLabelsHtml($labels);

        $output .= '</th>';

        return $output;
    }

    /**
     * Get the label data by ID
     *
     * @param   int         $id
     * @return  array|null
     */
    protected function getLabelById($id)
    {
        foreach ($this->labels as $label) {
            if ($id == $label['id']) {
                return $label;
            }
        }

        return null;
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

        if ($item['status'] == 'normal') {
            if (current_user_can('quform_view_entries')) {
                $actions['view'] = sprintf(
                    '<a href="%s" aria-label="%s">%s</a>',
                    esc_url(add_query_arg(array('sp' => 'view', 'eid' => $item['id'], 'id' => false))),
                    esc_attr__('View this entry', 'quform'),
                    esc_html__('View', 'quform')
                );
            }

            if (current_user_can('quform_edit_entries')) {
                $actions['edit'] = sprintf(
                    '<a href="%s" aria-label="%s">%s</a>',
                    esc_url(add_query_arg(array('sp' => 'edit', 'eid' => $item['id'], 'id' => false))),
                    esc_attr__('Edit this entry', 'quform'),
                    esc_html__('Edit', 'quform')
                );
            }

            if (current_user_can('quform_view_entries')) {
                if ($item['unread'] == '1') {
                    $actions['read'] = sprintf(
                        '<a href="%s" aria-label="%s">%s</a>',
                        esc_url(add_query_arg(array('action' => 'read', 'eid' => $item['id'], '_wpnonce' => wp_create_nonce('quform_read_entry_' . $item['id'])))),
                        esc_attr__('Mark this entry as read', 'quform'),
                        esc_html__('Mark as read', 'quform')
                    );
                } else {
                    $actions['unread'] = sprintf(
                        '<a href="%s" aria-label="%s">%s</a>',
                        esc_url(add_query_arg(array('action' => 'unread', 'eid' => $item['id'], '_wpnonce' => wp_create_nonce('quform_unread_entry_' . $item['id'])))),
                        esc_attr__('Mark this entry as unread', 'quform'),
                        esc_html__('Mark as unread', 'quform')
                    );
                }
            }

            if (current_user_can('quform_delete_entries')) {
                $actions['trash'] = sprintf(
                    '<a href="%s" aria-label="%s">%s</a>',
                    esc_url(add_query_arg(array('action' => 'trash', 'eid' => $item['id'], '_wpnonce' => wp_create_nonce('quform_trash_entry_' . $item['id'])))),
                    esc_attr__('Move this entry to the Trash', 'quform'),
                    esc_html__('Trash', 'quform')
                );
            }
        } elseif ($item['status'] == 'trash') {
            if (current_user_can('quform_delete_entries')) {
                $actions['untrash'] = sprintf(
                    '<a href="%s" aria-label="%s">%s</a>',
                    esc_url(add_query_arg(array('action' => 'untrash', 'eid' => $item['id'], '_wpnonce' => wp_create_nonce('quform_untrash_entry_' . $item['id'])))),
                    esc_attr__('Restore this entry from the Trash', 'quform'),
                    esc_html__('Restore', 'quform')
                );

                $actions['delete'] = sprintf(
                    '<a href="%s" aria-label="%s">%s</a>',
                    esc_url(add_query_arg(array('action' => 'delete', 'eid' => $item['id'], '_wpnonce' => wp_create_nonce('quform_delete_entry_' . $item['id'])))),
                    esc_attr__('Delete this entry permanently', 'quform'),
                    esc_html__('Delete permanently', 'quform')
                );
            }
        }

        return $this->row_actions($actions);
    }

    /**
     * Get the column cell content
     *
     * @param   array   $item        The item being acted upon
     * @param   string  $columnName  Current column name
     * @return  string
     */
    protected function column_default($item, $columnName)
    {
        if (preg_match('/^element_\d+/', $columnName)) {
            return $this->columnElement($item, $columnName);
        }

        $output = '';

        switch ($columnName) {
            case 'created_at':
                $output = esc_html($this->options->formatDate($item['created_at'], true));
                break;
            case 'updated_at':
                $output = esc_html($this->options->formatDate($item['updated_at'], true));
                break;
            case 'form_url':
                if ( ! empty($item['form_url'])) {
                    $output = '<a href="' . esc_url($item['form_url']) . '" target="_blank">' . esc_html($item['form_url']) . '</a>';
                }
                break;
            case 'referring_url':
                if ( ! empty($item['referring_url'])) {
                    $output = '<a href="' . esc_url($item['referring_url']) . '" target="_blank">' . esc_html($item['referring_url']) . '</a>';
                }
                break;
            case 'created_by':
                $user = get_user_by('id', $item['created_by']);

                if ($user instanceof WP_User) {
                    $link = get_edit_user_link($user->ID);

                    if ( ! empty($link)) {
                        $output = '<a href="' . esc_url($link) . '" title="' . esc_attr('View user profile', 'quform') . '" target="_blank">' . esc_html($user->user_login) . '</a>';
                    } else {
                        $output = esc_html($user->user_login);
                    }
                }
                break;
            case 'post_id':
                $post = get_post($item['post_id']);

                if ($post instanceof WP_Post) {
                    $link = get_permalink($post->ID);

                    if ( ! empty($link)) {
                        $output = '<a href="' . esc_url($link) . '" title="' . esc_attr('View page', 'quform') . '" target="_blank">' . esc_html(get_the_title($post->ID)) . '</a>';
                    } else {
                        $output = esc_html(get_the_title($post->ID));
                    }
                }
                break;
            default:
                $output = isset($item[$columnName]) ? esc_html($item[$columnName]) : '';
                break;

        }

        return $this->linkIfPrimaryColumn($output, $item, $columnName);
    }

    /**
     * Get the column cell content for an element
     *
     * @param   array   $item        The item being acted upon
     * @param   string  $columnName  Current column name
     * @return  string
     */
    protected function columnElement($item, $columnName)
    {
        $elementId = (int) str_replace('element_', '', $columnName);
        $element = $this->form->getElementById($elementId);
        $output = '';

        if ($element instanceof Quform_Element_Field) {
            if (isset($item[$columnName])) {
                $element->setValueFromStorage($item[$columnName]);
            } else {
                $element->setValue($element->getEmptyValue());
            }

            $output = $element->getValueHtml();
        }

        return $this->linkIfPrimaryColumn($output, $item, $columnName);
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
            if (current_user_can('quform_delete_entries')) {
                $actions['untrash'] = __('Restore', 'quform');
                $actions['delete'] = __('Delete permanently', 'quform');
            }
        } else {
            $actions['read'] = __('Mark as read', 'quform');
            $actions['unread'] = __('Mark as unread', 'quform');

            if (current_user_can('quform_delete_entries')) {
                $actions['trash'] = __('Move to Trash', 'quform');
            }
        }

        return $actions;
    }

    /**
     * Get the HTML for the label search
     *
     * @return string
     */
    public function getLabelSearchHtml()
    {
        if ( ! count($this->labels)) {
            return '';
        }

        $selectedLabels = isset($_GET['labels']) && is_array($_GET['labels']) ? $_GET['labels'] : array();

        $output = '<div id="qfb-entry-label-search-trigger" class="qfb-entry-label-search-trigger"><i class="qfb-icon qfb-icon-tags"></i></div>';
        $output .= '<div id="qfb-entry-label-search" class="qfb-entry-label-search">';

        foreach ($this->labels as $label) {
            $output .= sprintf(
                '<div class="qfb-entry-label%s" style="background-color:%s" data-id="%d"><span class="qfb-entry-label-name">%s</span><i class="qfb-icon qfb-icon-check"></i></div>',
                in_array($label['id'], $selectedLabels) ? ' qfb-label-selected' : '',
                Quform::escape($label['color']),
                Quform::escape($label['id']),
                Quform::escape($label['name'])
            );
        }

        $output .= '<select id="qfb-entry-label-search-ids" name="labels[]" multiple>';

        foreach ($this->labels as $label) {
            $output .= sprintf(
                '<option value="%s"%s>%s</option>',
                Quform::escape($label['id']),
                in_array($label['id'], $selectedLabels) ? ' selected="selected"' : '',
                Quform::isNonEmptyString($label['name']) ? Quform::escape($label['name']) : Quform::escape(__('Untitled', 'quform'))
            );
        }

        $output .= '</select>';

        $output .= '<select name="label_operator">';

        $output .= sprintf(
            '<option value="or"%s>%s</option>',
            isset($_GET['label_operator']) && $_GET['label_operator'] == 'or' ? ' selected="selected"' : '',
            esc_html__('Match any label', 'quform')
        );

        $output .= sprintf(
            '<option value="and"%s>%s</option>',
            isset($_GET['label_operator']) && $_GET['label_operator'] == 'and' ? ' selected="selected"' : '',
            esc_html__('Match all labels', 'quform')
        );

        $output .= '</select>';

        $output .= sprintf('<input type="submit" class="button" value="%s">', esc_attr__('Search', 'quform'));

        $output .= '</div>';

        return $output;
    }

    /**
     * Wrap the given content in a link to view the entry if this column is the primary column
     *
     * @param   string  $output      The current output for this column cell
     * @param   array   $item        The entry item data
     * @param   string  $columnName  The name of the column
     * @return  string
     */
    protected function linkIfPrimaryColumn($output, $item, $columnName)
    {
        if ($columnName == $this->get_default_primary_column_name()) {
            $output = sprintf(
                '<strong><a href="%s" aria-label="%s">%s</a></strong>',
                esc_url(add_query_arg(array('sp' => 'view', 'eid' => $item['id'], 'id' => false))),
                esc_attr__('View this entry', 'quform'),
                $output
            );
        }

        return $output;
    }

    /**
     * Message to be displayed when there are no forms
     */
    public function no_items() {
        if (isset($_GET['s']) && Quform::isNonEmptyString($_GET['s'])) {
            esc_html_e('Your search did not match any entries.', 'quform');
        } elseif ($this->view == 'trashed') {
            esc_html_e('No entries found in Trash.', 'quform');
        } else {
            esc_html_e('No entries found.', 'quform');
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
        $userOrderBy = get_user_meta($currentUserId, 'quform_entries_order_by', true);

        if (Quform::isNonEmptyString($requestedOrderBy)) {
            $orderBy = $requestedOrderBy;

            if ($requestedOrderBy != $userOrderBy) {
                update_user_meta($currentUserId, 'quform_entries_order_by', $requestedOrderBy);
            }
        } elseif (Quform::isNonEmptyString($userOrderBy)) {
            $orderBy = $userOrderBy;
        } else {
            $orderBy = 'created_at';
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
        $userOrder = get_user_meta($currentUserId, 'quform_entries_order', true);

        if (Quform::isNonEmptyString($requestedOrder)) {
            $order = $requestedOrder;

            if ($requestedOrder != $userOrder) {
                update_user_meta($currentUserId, 'quform_entries_order', $requestedOrder);
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
        if ($which == 'top' && $this->view == 'trashed' && count($this->items) && current_user_can('quform_delete_entries')) {
            submit_button(__('Empty Trash', 'quform'), 'apply', 'delete_all', false);
        }
    }
}
