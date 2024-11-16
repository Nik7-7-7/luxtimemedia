<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Repository
{
    /**
     * Get the name of the forms table with the WP prefix added
     *
     * @return string
     */
    public function getFormsTableName()
    {
        global $wpdb;

        return $wpdb->prefix . 'quform_forms';
    }

    /**
     * Get the name of the entries table with the WP prefix added
     *
     * @return string
     */
    public function getEntriesTableName()
    {
        global $wpdb;

        return $wpdb->prefix . 'quform_entries';
    }

    /**
     * Get the name of the entry data table with the WP prefix added
     *
     * @return string
     */
    public function getEntryDataTableName()
    {
        global $wpdb;

        return $wpdb->prefix . 'quform_entry_data';
    }

    /**
     * Get the name of the entry labels table with the WP prefix added
     *
     * @return string
     */
    public function getEntryLabelsTableName()
    {
        global $wpdb;

        return $wpdb->prefix . 'quform_entry_labels';
    }

    /**
     * Get the name of the entry label mapping table with the WP prefix added
     *
     * @return string
     */
    public function getEntryEntryLabelsTableName()
    {
        global $wpdb;

        return $wpdb->prefix . 'quform_entry_entry_labels';
    }

    /**
     * Get all form rows
     *
     * @param   bool|null  $active   Select all (null), only active (true) or inactive (false) forms
     * @param   string     $orderBy  Order by this column
     * @param   string     $order    Order 'ASC' or 'DESC'
     * @return  array
     */
    public function all($active = null, $orderBy = 'id', $order = 'ASC')
    {
        global $wpdb;

        $sql = "SELECT forms.*, COALESCE(e.cnt, 0) AS entries FROM " . $this->getFormsTableName() . " forms
            LEFT JOIN ( SELECT form_id, COUNT(*) AS cnt FROM " . $this->getEntriesTableName() . " WHERE status = 'normal' GROUP BY form_id ) e
            ON forms.id = e.form_id
            WHERE forms.trashed = 0";

        if ($active !== null) {
            $sql .= $wpdb->prepare(" AND active = %d", $active ? 1 : 0);
        }

        $orderBy = in_array($orderBy, array('id', 'name', 'entries', 'active', 'created_at', 'updated_at')) ? $orderBy : 'updated_at';
        $order = strtoupper($order);
        $order = in_array($order, array('ASC', 'DESC')) ? $order : 'DESC';

        $sql .= " ORDER BY $orderBy $order";

        return $wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Get form rows, including counts of read and unread entries
     *
     * @deprecated  2.1.0
     * @param       array  $args  The query args
     * @return      array
     */
    public function getFormsForListTable(array $args = array())
    {
        _deprecated_function(__METHOD__, '2.1.0', 'Quform_Repository::getForms()');

        return $this->getForms($args);
    }

    /**
     * Get form rows, including counts of read and unread entries
     *
     * @param   array  $args  The query args
     * @return  array
     */
    public function getForms(array $args = array())
    {
        global $wpdb;

        $args = wp_parse_args($args, array(
            'active' => null,
            'orderby' => 'updated_at',
            'order' => 'DESC',
            'trashed' => false,
            'offset' => 0,
            'limit' => 20,
            'search' => ''
        ));

        $sql = "SELECT SQL_CALC_FOUND_ROWS f.id, f.name, f.active, f.trashed, f.updated_at,
                COALESCE(e.cnt, 0) AS entries,
                COALESCE(u.cnt, 0) AS unread
                FROM " . $this->getFormsTableName() . " f
                LEFT JOIN ( SELECT form_id, COUNT(*) AS cnt FROM " . $this->getEntriesTableName() . " WHERE status = 'normal' GROUP BY form_id ) e
                ON f.id = e.form_id
                LEFT JOIN ( SELECT form_id, COUNT(*) AS cnt FROM " . $this->getEntriesTableName() . " WHERE status = 'normal' AND unread = 1 GROUP BY form_id ) u
                ON f.id = u.form_id";

        $where = array($wpdb->prepare('trashed = %d', $args['trashed'] ? 1 : 0));

        if ($args['active'] !== null) {
            $where[] = $wpdb->prepare('active = %d', $args['active'] ? 1 : 0);
        }

        if (Quform::isNonEmptyString($args['search'])) {
            $searchColumns = array();

            if (is_numeric($args['search'])) {
                $searchColumns[] = $wpdb->prepare("id = %d", $args['search']);
            }

            $searchColumns[] = $wpdb->prepare("name LIKE '%s'", '%' . $wpdb->esc_like($args['search']) . '%');

            $where[] = '(' . join(' OR ', $searchColumns) . ')';
        }

        $sql .= " WHERE " . join(' AND ', $where);

        // Sanitize order/limit
        $args['orderby'] = in_array($args['orderby'], array('id', 'name', 'entries', 'active', 'created_at', 'updated_at')) ? $args['orderby'] : 'updated_at';
        $args['order'] = strtoupper($args['order']);
        $args['order'] = in_array($args['order'], array('ASC', 'DESC')) ? $args['order'] : 'DESC';
        $args['limit'] = (int) $args['limit'];
        $args['offset'] = (int) $args['offset'];

        $sql .= " ORDER BY `{$args['orderby']}` {$args['order']} LIMIT {$args['limit']} OFFSET {$args['offset']}";

        return $wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Get all form configs
     *
     * @param   bool|null  $active  Select all (null), only active (true) or inactive (false) forms
     * @return  array
     */
    public function allForms($active = null)
    {
        $forms = array();
        $rows = $this->all($active);

        foreach ($rows as $row) {
            $config = maybe_unserialize(base64_decode($row['config']));

            if (is_array($config)) {
                $config = $this->addRowDataToConfig($row, $config);
                $forms[] = $config;
            }
        }

        return $forms;
    }

    /**
     * Get the array of form configs with the given IDs
     *
     * @param   array  $ids
     * @return  array
     */
    public function getFormsById(array $ids)
    {
        global $wpdb;
        $forms = array();
        $ids = $this->sanitizeIds($ids);

        if (empty($ids)) {
            return $forms;
        }

        $joinedIds = $this->joinIds($ids);

        $sql = "SELECT * FROM " . $this->getFormsTableName() . " WHERE id IN ($joinedIds)";

        $rows = $wpdb->get_results($sql, ARRAY_A);

        if (is_array($rows)) {
            foreach ($rows as $row) {
                $config = maybe_unserialize(base64_decode($row['config']));
                $config = $this->addRowDataToConfig($row, $config);
                $forms[] = $config;
            }
        }

        return $forms;
    }

    /**
     * Get the array of form IDs that are trashed
     *
     * @return int[]
     */
    public function getTrashedFormIds()
    {
        global $wpdb;
        $sql = "SELECT id FROM " . $this->getFormsTableName() . " WHERE trashed = 1";

        $ids = $wpdb->get_col($sql);

        return array_map('intval', $ids);
    }

    /**
     * Get the forms list in array format to be easily used by an HTML &lt;select&gt;
     *
     * @param   bool|null  $active   Select all (null), only active (true) or inactive (false) forms
     * @param   string     $orderBy  Order by this column
     * @param   string     $order    Order 'ASC' or 'DESC'
     * @return  array
     */
    public function formsToSelectArray($active = null, $orderBy = 'updated_at', $order = 'DESC')
    {
        $rows = $this->all($active, $orderBy, $order);
        $forms = array();

        foreach ($rows as $row) {
            if ($row['active']) {
                $forms[$row['id']] = $row['name'];
            } else {
                /* translators: %s: the form name */
                $forms[$row['id']] = sprintf(__('%s (inactive)', 'quform'), $row['name']);
            }
        }

        return $forms;
    }

    /**
     * Get the count of forms
     *
     * @param   bool|null  $active   Select all (null), only active (true) or inactive (false) forms
     * @param   bool       $trashed  Select trashed forms
     * @return  int
     */
    public function count($active = null, $trashed = false)
    {
        global $wpdb;

        $sql = "SELECT COUNT(*) FROM " . $this->getFormsTableName();

        $where = array($wpdb->prepare('trashed = %d', $trashed ? 1 : 0));

        if ($active !== null) {
            $where[] = $wpdb->prepare('active = %d', $active ? 1 : 0);
        }

        $sql .= " WHERE " . join(' AND ', $where);

        return (int) $wpdb->get_var($sql);
    }

    /**
     * Does the form exist with the given ID?
     *
     * @param   int   $id
     * @return  bool
     */
    public function exists($id)
    {
        global $wpdb;

        $sql = $wpdb->prepare("SELECT id FROM " . $this->getFormsTableName() . " WHERE id = %d", (int) $id);

        return $wpdb->get_var($sql) !== null;
    }

    /**
     * Does the entry exist with the given ID?
     *
     * @param   int   $id
     * @return  bool
     */
    public function entryExists($id)
    {
        global $wpdb;

        $sql = $wpdb->prepare("SELECT id FROM " . $this->getEntriesTableName() . " WHERE id = %d", (int) $id);

        return $wpdb->get_var($sql) !== null;
    }

    /**
     * Find a form by ID
     *
     * @param   int  $id
     * @return  array|null
     */
    public function find($id)
    {
        global $wpdb;

        $sql = "SELECT * FROM " . $this->getFormsTableName() . " WHERE id = %d";

        return $wpdb->get_row($wpdb->prepare($sql, $id), ARRAY_A);
    }

    /**
     * Get the first non-trashed form row
     *
     * @return array|null
     */
    public function first()
    {
        global $wpdb;

        $sql = "SELECT * FROM " . $this->getFormsTableName() . " WHERE trashed = 0 LIMIT 1";

        return $wpdb->get_row($sql, ARRAY_A);
    }

    /**
     * Get the config array for the form with the given ID
     *
     * @param   int         $id  The form ID
     * @return  array|null       The config array or null if the form doesn't exist
     */
    public function getConfig($id)
    {
        $row = $this->find($id);

        if ($row === null) {
            return null;
        }

        $config = maybe_unserialize(base64_decode($row['config']));

        if (is_array($config)) {
            $config = $this->addRowDataToConfig($row, $config);
        } else {
            $config = null;
        }

        return $config;
    }

    /**
     * Get the config array for the first form in the database
     *
     * @return array|null The config array or null if the form doesn't exist
     */
    public function firstConfig()
    {
        $row = $this->first();

        if ($row === null) {
            return null;
        }

        $config = maybe_unserialize(base64_decode($row['config']));
        $config = $this->addRowDataToConfig($row, $config);

        return $config;
    }

    /**
     * On plugin activation - create the database tables
     */
    public function activate()
    {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $this->createFormsTable();
        $this->createEntriesTable();
        $this->createEntryDataTable();
        $this->createEntryLabelsTable();
        $this->createEntryEntryLabelsTable();
    }

    /**
     * Create the forms table
     */
    protected function createFormsTable()
    {
        global $wpdb;

        $sql = "CREATE TABLE " . $this->getFormsTableName() . " (
            id int UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(64) NOT NULL,
            config longtext NOT NULL,
            active boolean NOT NULL DEFAULT 1,
            trashed boolean NOT NULL DEFAULT 0,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY active (active),
            KEY trashed (trashed)
        ) " . $wpdb->get_charset_collate() . ";";

        dbDelta($sql);
    }

    /**
     * Create the entries table
     */
    protected function createEntriesTable()
    {
        global $wpdb;

        $sql = "CREATE TABLE " . $this->getEntriesTableName() . " (
            id int UNSIGNED NOT NULL AUTO_INCREMENT,
            form_id int UNSIGNED NOT NULL,
            unread tinyint (1) UNSIGNED NOT NULL DEFAULT 1,
            ip varchar(45) NOT NULL,
            form_url varchar(512) NOT NULL,
            referring_url varchar(512) NOT NULL,
            post_id bigint(20) UNSIGNED,
            created_by bigint(20) UNSIGNED,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'normal',
            PRIMARY KEY  (id),
            KEY form_id (form_id),
            KEY status (status)
        ) " . $wpdb->get_charset_collate() . ";";

        dbDelta($sql);
    }

    /**
     * Create the entry data table
     */
    protected function createEntryDataTable()
    {
        global $wpdb;

        $sql = "CREATE TABLE " . $this->getEntryDataTableName() . " (
            entry_id int UNSIGNED NOT NULL,
            element_id int UNSIGNED NOT NULL,
            value mediumtext,
            PRIMARY KEY  (entry_id,element_id),
            KEY element_id (element_id)
        ) " . $wpdb->get_charset_collate() . ";";

        dbDelta($sql);
    }

    /**
     * Create the entry data table
     */
    protected function createEntryLabelsTable()
    {
        global $wpdb;

        $sql = "CREATE TABLE " . $this->getEntryLabelsTableName() . " (
            id int UNSIGNED NOT NULL AUTO_INCREMENT,
            form_id int UNSIGNED NOT NULL,
            name varchar(128) NOT NULL,
            color varchar (32) NOT NULL,
            PRIMARY KEY  (id),
            KEY form_id (form_id)
        ) " . $wpdb->get_charset_collate() . ";";

        dbDelta($sql);
    }

    /**
     * Create the entry data table
     */
    protected function createEntryEntryLabelsTable()
    {
        global $wpdb;

        $sql = "CREATE TABLE " . $this->getEntryEntryLabelsTableName() . " (
            entry_id int UNSIGNED NOT NULL,
            entry_label_id int UNSIGNED NOT NULL,
            PRIMARY KEY  (entry_id,entry_label_id),
            KEY entry_label_id (entry_label_id)
        ) " . $wpdb->get_charset_collate() . ";";

        dbDelta($sql);
    }

    /**
     * Get the database version from the wpdb object
     */
    public function getDbVersion()
    {
        global $wpdb;

        return $wpdb->db_version();
    }

    /**
     * Add the form with the given config
     *
     * @param   array       $config  The form config to add
     * @return  array|bool           The new form config with new auto-generated ID or false on failure
     */
    public function add(array $config)
    {
        global $wpdb;

        // Temporarily save the config parts that are part of the table row and unset them
        $name = $config['name'];
        $active = $config['active'];
        $trashed = $config['trashed'];
        unset($config['id'], $config['name'], $config['active'], $config['trashed']);

        $time = Quform::date('Y-m-d H:i:s', null, new DateTimeZone('UTC'));

        $result = $wpdb->insert($this->getFormsTableName(), array(
            'config' => base64_encode(serialize($config)),
            'name' => Quform::substr($name, 0, 64),
            'active' => $active,
            'trashed' => $trashed,
            'created_at' => $time,
            'updated_at' => $time
        ));

        if ($result === false) {
            return false;
        }

        $config['id'] = $wpdb->insert_id;

        // Restore the config parts that are part of the table row
        $config['name'] = $name;
        $config['active'] = $active;
        $config['trashed'] = $trashed;

        $defaultEntryLabels = array(
            array(
                'name' => __('Starred', 'quform'),
                'color' => '#F2D600'
            )
        );

        $defaultEntryLabels = apply_filters('quform_default_entry_labels', $defaultEntryLabels, $config);

        if (is_array($defaultEntryLabels)) {
            $this->setFormEntryLabels($config['id'], $defaultEntryLabels);
        }

        do_action('quform_add_form', $config);

        return $config;
    }

    /**
     * Save the form with the given config and return the config
     *
     * If the 'id' is present in the config array it will update the form
     * otherwise a new one will be created
     *
     * @param   array  $config  The config
     * @return  array           The updated config array
     */
    public function save(array $config)
    {
        global $wpdb;

        // Temporarily save the config parts that are part of the table row and unset them
        $id = $config['id'];
        $name = $config['name'];
        $active = $config['active'];
        $trashed = $config['trashed'];

        unset($config['id'], $config['name'], $config['active'], $config['trashed']);

        $updateValues = array(
            'config' => base64_encode(serialize($config)),
            'name' => Quform::substr($name, 0, 64),
            'active' => $active,
            'trashed' => $trashed,
            'updated_at' => Quform::date('Y-m-d H:i:s', null, new DateTimeZone('UTC'))
        );

        $updateWhere = array(
            'id' => $id
        );

        $wpdb->update($this->getFormsTableName(), $updateValues, $updateWhere);

        // Restore the config parts that are part of the table row
        $config['id'] = $id;
        $config['name'] = $name;
        $config['active'] = $active;
        $config['trashed'] = $trashed;

        do_action('quform_save_form', $config);

        return $config;
    }

    /**
     * Save the entry with the given configuration
     *
     * @param   array     $config   The entry config
     * @param   int|null  $entryId  The entry ID if we are updating a saved entry, null for new entries
     * @return  array               The entry config with new ID if we are adding a new entry
     */
    public function saveEntry(array $config, $entryId = null)
    {
        global $wpdb;

        if (isset($config['data']) && is_array($config['data'])) {
            $data = $config['data'];
            unset($config['data']);
        } else {
            $data = array();
        }

        if ($entryId === null || ! $this->entryExists($entryId)) {
            // Entry doesn't exist in the database, create it to get an ID
            $wpdb->insert($this->getEntriesTableName(), $config);
            $entryId = $wpdb->insert_id;
        } else {
            $wpdb->update($this->getEntriesTableName(), $config, array('id' => $entryId));
        }

        if (count($data)) {
            $this->saveEntryData($entryId, $data);
            $config['data'] = $data;
        }

        $config['id'] = $entryId;

        return $config;
    }

    /**
     * Save the given data to the entry with the given ID
     *
     * @param  int    $entryId
     * @param  array  $data
     */
    public function saveEntryData($entryId, array $data)
    {
        if ( ! count($data)) {
            return;
        }

        global $wpdb;

        $sql = "INSERT INTO " . $this->getEntryDataTableName() . " (entry_id, element_id, value) VALUES ";
        $values = array();
        $placeholders = array();

        foreach ($data as $elementId => $value) {
            $placeholders[] = "(%d, %d, %s)";
            array_push($values, $entryId, $elementId, $value);
        }

        $sql .= $wpdb->prepare(implode(', ', $placeholders), $values);

        $sql .= " ON DUPLICATE KEY UPDATE value = VALUES(value);";

        $wpdb->query($sql);
    }

    /**
     * Activate forms with the given IDs
     *
     * @param   array  $ids  The array of form IDs
     * @return  int          The number of affected rows
     */
    public function activateForms(array $ids)
    {
        global $wpdb;

        $ids = $this->sanitizeIds($ids);

        if (empty($ids)) {
            return 0;
        }

        $joinedIds = $this->joinIds($ids);

        $sql = "UPDATE " . $this->getFormsTableName() . " SET active = 1 WHERE id IN ($joinedIds)";

        $affectedRows = (int) $wpdb->query($sql);

        foreach ($ids as $id) {
            do_action('quform_form_activated', $id);
        }

        return $affectedRows;
    }

    /**
     * Deactivate forms with the given IDs
     *
     * @param   array  $ids  The array of form IDs
     * @return  int          The number of affected rows
     */
    public function deactivateForms(array $ids)
    {
        global $wpdb;

        $ids = $this->sanitizeIds($ids);

        if (empty($ids)) {
            return 0;
        }

        $joinedIds = $this->joinIds($ids);

        $sql = "UPDATE " . $this->getFormsTableName() . " SET active = 0 WHERE id IN ($joinedIds)";

        $affectedRows = (int) $wpdb->query($sql);

        foreach ($ids as $id) {
            do_action('quform_form_deactivated', $id);
        }

        return $affectedRows;
    }

    /**
     * Trash forms with the given IDs
     *
     * @param   array  $ids  The array of form IDs
     * @return  int          The number of affected rows
     */
    public function trashForms(array $ids)
    {
        global $wpdb;

        $ids = $this->sanitizeIds($ids);

        if (empty($ids)) {
            return 0;
        }

        $joinedIds = $this->joinIds($ids);

        $sql = "UPDATE " . $this->getFormsTableName() . " SET trashed = 1 WHERE id IN ($joinedIds)";

        $affectedRows = (int) $wpdb->query($sql);

        foreach ($ids as $id) {
            do_action('quform_form_trashed', $id);
        }

        return $affectedRows;
    }

    /**
     * Untrash forms with the given IDs
     *
     * @param   array  $ids  The array of form IDs
     * @return  int          The number of affected rows
     */
    public function untrashForms(array $ids)
    {
        global $wpdb;

        $ids = $this->sanitizeIds($ids);

        if (empty($ids)) {
            return 0;
        }

        $joinedIds = $this->joinIds($ids);

        $sql = "UPDATE " . $this->getFormsTableName() . " SET trashed = 0 WHERE id IN ($joinedIds)";

        $affectedRows = (int) $wpdb->query($sql);

        foreach ($ids as $id) {
            do_action('quform_form_untrashed', $id);
        }

        return $affectedRows;
    }

    /**
     * Delete the forms with the IDs in the given array
     *
     * @param   array  $ids  The array of form IDs
     * @return  int          The number of deleted rows
     */
    public function deleteForms(array $ids)
    {
        global $wpdb;

        $ids = $this->sanitizeIds($ids);

        if (empty($ids)) {
            return 0;
        }

        $joinedIds = $this->joinIds($ids);

        // Delete entry label association
        $wpdb->query("DELETE FROM " . $this->getEntryEntryLabelsTableName() . " WHERE entry_id IN (SELECT id FROM " . $this->getEntriesTableName() . " WHERE form_id IN ($joinedIds))");

        // Delete entry labels
        $wpdb->query("DELETE FROM " . $this->getEntryLabelsTableName() . " WHERE form_id IN ($joinedIds)");

        // Delete entry data
        $wpdb->query("DELETE FROM " . $this->getEntryDataTableName() . " WHERE entry_id IN (SELECT id FROM " . $this->getEntriesTableName() . " WHERE form_id IN ($joinedIds))");

        // Delete entries
        $wpdb->query("DELETE FROM " . $this->getEntriesTableName() . " WHERE form_id IN ($joinedIds)");

        // Delete the forms
        $affectedRows = (int) $wpdb->query("DELETE FROM " . $this->getFormsTableName() . " WHERE id IN ($joinedIds)");

        foreach ($ids as $id) {
            do_action('quform_form_deleted', $id);
        }

        return $affectedRows;
    }

    /**
     * Duplicate the forms with the IDs in the given array
     *
     * @param   array  $ids  The form ID
     * @return  array        The array of new form IDs
     */
    public function duplicateForms(array $ids)
    {
        $ids = $this->sanitizeIds($ids);
        $newIds = array();

        foreach ($ids as $id) {
            $config = $this->getConfig($id);

            if ( ! is_array($config)) {
                continue;
            }

            $config['active'] = true;
            /* translators: %s: the form name */
            $config['name'] = sprintf(_x('%s duplicate', 'form name duplicate', 'quform'), $config['name']);

            $config = $this->add($config);

            if (is_array($config)) {
                $newIds[] = $config['id'];

                if (apply_filters('quform_duplicate_form_entry_labels', true)) {
                    $entryLabels = $this->getFormEntryLabels($id);

                    foreach ($entryLabels as $key => $entryLabel) {
                        unset($entryLabels[$key]['id'], $entryLabels[$key]['form_id']);
                    }

                    $this->setFormEntryLabels($config['id'], $entryLabels);
                }
            }
        }

        return $newIds;
    }

    /**
     * Escape and join an array of IDs for use in an IN clause
     *
     * @param   array   $ids  The array of IDs
     * @return  string        The sanitized string for the IN clause
     */
    protected function joinIds(array $ids)
    {
        $ids = array_map('esc_sql', $ids);
        $ids = join(',', $ids);

        return $ids;
    }

    /**
     * Prepare an array of IDs for use in an IN clause
     *
     * @param   array   $ids  The array of IDs
     * @return  string        The sanitized string for the IN clause
     */
    protected function prepareIds(array $ids)
    {
        return $this->joinIds($this->sanitizeIds($ids));
    }

    /**
     * Sanitize the array of IDs ensuring they are all integers
     *
     * @param   array   $ids  The array of IDs
     * @return  array         The array of sanitized IDs
     */
    protected function sanitizeIds(array $ids)
    {
        $sanitized = array();

        foreach ($ids as $id) {
            if ( ! is_numeric($id)) {
                continue;
            }

            $id = (int) $id;

            if ($id > 0) {
                $sanitized[] = $id;
            }
        }

        $sanitized = array_unique($sanitized);

        return $sanitized;
    }

    /**
     * Sanitize the array of IDs ensuring they are all integers
     *
     * @deprecated 2.4.0
     * @param   array   $ids  The array of IDs
     * @return  array         The array of sanitized IDs
     */
    protected function sanitiseIds(array $ids)
    {
        _deprecated_function(__METHOD__, '2.4.0', 'Quform_Repository::sanitizeIds()');

        return $this->sanitizeIds($ids);
    }

    /**
     * Get the entries for a specific form
     *
     * No longer used internally, but used by custom code examples on Gist.
     *
     * @param   Quform_Form  $form  The form instance
     * @param   array        $args  The query args
     * @return  array|null
     */
    public function getEntries(Quform_Form $form, array $args = array())
    {
        global $wpdb;

        $args = wp_parse_args($args, array(
            'unread' => null,
            'orderby' => 'created_at',
            'order' => 'DESC',
            'status' => 'normal',
            'offset' => 0,
            'limit' => 20,
            'search' => '',
            'labels' => array(),
            'label_operator' => 'OR',
            'created_by' => null
        ));

        $sql = "SELECT SQL_CALC_FOUND_ROWS `entries`.*";

        $searchColumns = array(
            'entries.ip',
            'entries.form_url',
            'entries.referring_url',
            'entries.post_id',
            'entries.created_by',
            'entries.created_at',
            'entries.updated_at'
        );

        $validOrderBy = array(
            'id',
            'ip',
            'form_url',
            'referring_url',
            'post_id',
            'created_by',
            'created_at',
            'updated_at'
        );

        foreach ($form->getRecursiveIterator() as $element) {
            if ($element->config('saveToDatabase')) {
                $sql .= $wpdb->prepare(", GROUP_CONCAT(DISTINCT IF (data.element_id = %d, data.value, NULL)) AS element_%d", $element->getId(), $element->getId());
                $searchColumns[] = $wpdb->prepare("element_%d", $element->getId());
                $validOrderBy[] = $wpdb->prepare("element_%d", $element->getId());
            }
        }

        $sql .= ", GROUP_CONCAT(DISTINCT eel.entry_label_id) AS labels";

        $whereClause = array($wpdb->prepare("entries.form_id = %d", $form->getId()));

        if ($args['unread'] !== null) {
            $whereClause[] = $wpdb->prepare("entries.unread = %d", $args['unread'] ? 1 : 0);
        }

        if (Quform::isNonEmptyString($args['status'])) {
            $whereClause[] = $wpdb->prepare("entries.status = %s", $args['status']);
        }

        $sql .= " FROM " . $this->getEntriesTableName() . " entries
LEFT JOIN " . $this->getEntryDataTableName() . " data ON data.entry_id = entries.id
LEFT JOIN " . $this->getEntryEntryLabelsTableName() . " eel ON eel.entry_id = entries.id
WHERE " . join(' AND ', $whereClause) . "
GROUP BY entries.id";

        // Search clause
        if (Quform::isNonEmptyString($args['search']) || count($args['labels']) || is_numeric($args['created_by'])) {
            $sql .= " HAVING ";
            $having = array();

            if (Quform::isNonEmptyString($args['search'])) {
                $filteredSearchColumns = array();

                if (is_numeric($args['search'])) {
                    $filteredSearchColumns[] = $wpdb->prepare('entries.id = %d', $args['search']);
                }

                $search = $wpdb->esc_like($args['search']);

                foreach ($searchColumns as $searchColumn) {
                    // Bug fix for searching non-Latin characters on a datetime column
                    if (($searchColumn == 'entries.created_at' || $searchColumn == 'entries.updated_at') && preg_match('/[^\d\-: ]/', $search)) {
                        continue;
                    }

                    $filteredSearchColumns[] = $wpdb->prepare("$searchColumn LIKE '%s'", '%' . $search . '%');
                }

                $having[] = '(' . join(' OR ', $filteredSearchColumns) . ')';
            }

            if (count($args['labels'])) {
                $labels = array();

                foreach ($args['labels'] as $label) {
                    $label = (int) $label;

                    $labels[] = $wpdb->prepare(
                        "(labels LIKE '%s' OR labels LIKE '%s' OR labels LIKE '%s' OR labels LIKE '%s')",
                        $label,
                        '%,' . $label,
                        $label . ',%',
                        '%,' . $label . ',%'
                    );
                }

                $args['label_operator'] = strtoupper($args['label_operator']) == 'AND' ? 'AND' : 'OR';

                $having[] = '(' . join(sprintf(' %s ', $args['label_operator']), $labels) . ')';
            }

            if (is_numeric($args['created_by'])) {
                $having[] = '(' . $wpdb->prepare("entries.created_by = %d", (int) $args['created_by']) . ')';
            }

            $sql .= join(' AND ', $having);
        }

        // Sanitize order/limit
        $args['orderby'] = in_array($args['orderby'], $validOrderBy) ? $args['orderby'] : 'created_at';
        $args['order'] = strtoupper($args['order']);
        $args['order'] = in_array($args['order'], array('ASC', 'DESC')) ? $args['order'] : 'DESC';
        $args['limit'] = (int) $args['limit'];
        $args['offset'] = (int) $args['offset'];

        // Order by
        $sql .= " ORDER BY `{$args['orderby']}` {$args['order']}";

        // Limit and offset
        if ($args['limit'] > 0) {
            $sql .= " LIMIT {$args['limit']} OFFSET {$args['offset']}";
        }

        // Maximum display length of a single field value
        $wpdb->query('SET @@GROUP_CONCAT_MAX_LEN = 65535');

        return $wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Get the entries for a specific form for the list view
     *
     * @param   Quform_Form  $form     The form instance
     * @param   array        $columns  Limit the query to select only these columns
     * @param   array        $args     The query args
     * @return  array|null
     */
    public function listEntries(Quform_Form $form, array $columns, array $args = array())
    {
        global $wpdb;

        $args = wp_parse_args($args, array(
            'unread' => null,
            'orderby' => 'created_at',
            'order' => 'DESC',
            'status' => 'normal',
            'offset' => 0,
            'limit' => 20,
            'search' => '',
            'labels' => array(),
            'label_operator' => 'OR',
            'created_by' => null
        ));

        $sql = "SELECT SQL_CALC_FOUND_ROWS `entries`.*";

        $searchColumns = array(
            'entries.ip',
            'entries.form_url',
            'entries.referring_url',
            'entries.post_id',
            'entries.created_by',
            'entries.created_at',
            'entries.updated_at'
        );

        $validOrderBy = array(
            'id',
            'ip',
            'form_url',
            'referring_url',
            'post_id',
            'created_by',
            'created_at',
            'updated_at'
        );

        foreach ($columns as $column) {
            if (preg_match("/^element_(\d+)$/", $column, $matches)) {
                $sql .= $wpdb->prepare(", GROUP_CONCAT(DISTINCT IF (data.element_id = %d, data.value, NULL)) AS element_%d", $matches[1], $matches[1]);
                $validOrderBy[] = $wpdb->prepare("element_%d", $matches[1]);
            }
        }

        $sql .= ", GROUP_CONCAT(DISTINCT eel.entry_label_id) AS labels";

        $whereClause = array($wpdb->prepare("entries.form_id = %d", $form->getId()));

        if ($args['unread'] !== null) {
            $whereClause[] = $wpdb->prepare("entries.unread = %d", $args['unread'] ? 1 : 0);
        }

        if (Quform::isNonEmptyString($args['status'])) {
            $whereClause[] = $wpdb->prepare("entries.status = %s", $args['status']);
        }

        if (is_numeric($args['created_by'])) {
            $whereClause[] = $wpdb->prepare("entries.created_by = %d", (int) $args['created_by']);
        }

        if (Quform::isNonEmptyString($args['search'])) {
            $searchWhereClause = array();

            if (is_numeric($args['search'])) {
                $searchWhereClause[] = $wpdb->prepare('entries.id = %d', $args['search']);
            }

            $search = $wpdb->esc_like($args['search']);

            foreach ($searchColumns as $searchColumn) {
                // Bug fix for searching non-Latin characters on a datetime column
                if (($searchColumn == 'entries.created_at' || $searchColumn == 'entries.updated_at') && preg_match('/[^\d\-: ]/', $search)) {
                    continue;
                }

                $searchWhereClause[] = $wpdb->prepare("$searchColumn LIKE '%s'", '%' . $search . '%');
            }

            $searchWhereClause[] = $wpdb->prepare(
                "(entries.id IN (
                    SELECT DISTINCT entry_id
                    FROM " . $this->getEntryDataTableName() . " sed
                    LEFT JOIN " . $this->getEntriesTableName() . " se
                    ON sed.entry_id = se.id
                    WHERE se.form_id = %d AND `value` LIKE '%s'
                ))",
                $form->getId(),
                '%' . $search . '%'
            );

            $whereClause[] = '(' . join(' OR ', $searchWhereClause) . ')';
        }

        $sql .= " FROM " . $this->getEntriesTableName() . " entries
LEFT JOIN " . $this->getEntryDataTableName() . " data ON data.entry_id = entries.id
LEFT JOIN " . $this->getEntryEntryLabelsTableName() . " eel ON eel.entry_id = entries.id
WHERE " . join(' AND ', $whereClause) . "
GROUP BY entries.id";

        // Search clause
        if (count($args['labels'])) {
            $labels = array();

            foreach ($args['labels'] as $label) {
                $label = (int) $label;

                $labels[] = $wpdb->prepare(
                    "(labels LIKE '%s' OR labels LIKE '%s' OR labels LIKE '%s' OR labels LIKE '%s')",
                    $label,
                    '%,' . $label,
                    $label . ',%',
                    '%,' . $label . ',%'
                );
            }

            $args['label_operator'] = strtoupper($args['label_operator']) == 'AND' ? 'AND' : 'OR';

            if (count($labels)) {
                $sql .= ' HAVING (' . join(sprintf(' %s ', $args['label_operator']), $labels) . ')';
            }
        }

        // Sanitize order/limit
        $args['orderby'] = in_array($args['orderby'], $validOrderBy) ? $args['orderby'] : 'created_at';
        $args['order'] = strtoupper($args['order']);
        $args['order'] = in_array($args['order'], array('ASC', 'DESC')) ? $args['order'] : 'DESC';
        $args['limit'] = (int) $args['limit'];
        $args['offset'] = (int) $args['offset'];

        // Order by
        $sql .= " ORDER BY `{$args['orderby']}` {$args['order']}";

        // Limit and offset
        if ($args['limit'] > 0) {
            $sql .= " LIMIT {$args['limit']} OFFSET {$args['offset']}";
        }

        // Maximum display length of a single field value
        $wpdb->query('SET @@GROUP_CONCAT_MAX_LEN = 65535');

        return $wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Get the number of found rows from the last query
     *
     * @return int
     */
    public function getFoundRows()
    {
        global $wpdb;

        return (int) $wpdb->get_var("SELECT FOUND_ROWS()");
    }

    /**
     * Get the count of entries for the given form ID
     *
     * @param   int        $formId
     * @param   bool|null  $unread  Get the count of all (null), unread (true) or read (false) entries
     * @param   string     $status  Filter the result by status
     * @return  int
     */
    public function getEntryCount($formId, $unread = null, $status = 'normal')
    {
        global $wpdb;

        $whereClause = array($wpdb->prepare("form_id = %d", (int) $formId));

        if (is_bool($unread)) {
            $whereClause[] = $wpdb->prepare("unread = %d", $unread ? 1 : 0);
        }

        if (Quform::isNonEmptyString($status)) {
            $whereClause[] = $wpdb->prepare("status = %s", $status);
        }

        $count = $wpdb->get_var("SELECT COUNT(*) FROM " . $this->getEntriesTableName() . " WHERE " . join(' AND ', $whereClause));

        return (int) $count;
    }

    /**
     * Get the IDs of all entries with the given status for the given form
     *
     * @param   int     $formId  The ID of the form
     * @param   string  $status  Filter the result by status
     * @return  int[]
     */
    public function getEntryIdsByStatus($formId, $status)
    {
        global $wpdb;

        $sql = "SELECT id FROM " . $this->getEntriesTableName() . " WHERE form_id = %d AND status = %s";

        $ids = $wpdb->get_col($wpdb->prepare($sql, $formId, $status));

        return array_map('intval', $ids);
    }

    /**
     * Get the entry label data from the given label IDs
     *
     * @param   int    $entryId
     * @return  array
     */
    public function getEntryLabels($entryId)
    {
        global $wpdb;

        $sql = $wpdb->prepare("SELECT * FROM " . $this->getEntryLabelsTableName() . " WHERE `id` IN (SELECT entry_label_id FROM " . $this->getEntryEntryLabelsTableName() . " WHERE entry_id = %d)", $entryId);

        $labels = $wpdb->get_results($sql, ARRAY_A);

        if ( ! is_array($labels)) {
            $labels = array();
        }

        return $labels;
    }

    /**
     * Get the entry labels for the given form
     *
     * @param   int    $formId
     * @return  array
     */
    public function getFormEntryLabels($formId)
    {
        global $wpdb;

        $sql = $wpdb->prepare("SELECT * FROM " . $this->getEntryLabelsTableName() . " WHERE `form_id` = %d", $formId);

        $labels = $wpdb->get_results($sql, ARRAY_A);

        if ( ! is_array($labels)) {
            $labels = array();
        }

        return $labels;
    }

    /**
     * Set the entry labels for the given form
     *
     * @param  int    $formId
     * @param  array  $labels
     */
    public function setFormEntryLabels($formId, array $labels)
    {
        global $wpdb;

        $ids = array();
        $values = array();

        foreach ($labels as $label) {
            if (isset($label['id'])) {
                $ids[] = $label['id'];
            }

            $values[] = $wpdb->prepare(
                '(%d, %d, %s, %s)',
                Quform::get($label, 'id'), // If id omitted, trigger auto increment
                $formId,
                $label['name'],
                $label['color']
            );
        }

        $ids = $this->sanitizeIds($ids);

        if (empty($ids)) {
            // No old IDs - delete all labels
            $wpdb->query($wpdb->prepare(
                "DELETE el, eel FROM `" . $this->getEntryLabelsTableName() . "` el LEFT JOIN `" . $this->getEntryEntryLabelsTableName() . "` eel ON el.id = eel.entry_label_id WHERE el.form_id = %d",
                $formId
            ));
        } else {
            $joinedIds = $this->joinIds($ids);

            // Some old IDs - delete those not in the list
            $wpdb->query($wpdb->prepare(
                "DELETE el, eel FROM `" . $this->getEntryLabelsTableName() . "` el LEFT JOIN `" . $this->getEntryEntryLabelsTableName() . "` eel ON el.id = eel.entry_label_id WHERE el.form_id = %d AND el.id NOT IN ($joinedIds)",
                $formId
            ));
        }

        if (count($values)) {
            $sql = "INSERT INTO " . $this->getEntryLabelsTableName() . " (id, form_id, name, color) VALUES ";
            $sql .= join(', ', $values);
            $sql .= " ON DUPLICATE KEY UPDATE form_id = VALUES(form_id), name = VALUES(name), color = VALUES(color)";

            $wpdb->query($sql);
        }
    }

    /**
     * Delete all entry labels for the given form ID
     *
     * @deprecated  2.2.0
     * @param       int    $formId
     */
    public function deleteFormEntryLabels($formId)
    {
        _deprecated_function(__METHOD__, '2.2.0', 'Quform_Repository::setFormEntryLabels($formId, array())');

        $this->setFormEntryLabels($formId, array());
    }

    /**
     * Add the given entry label to the given entry
     *
     * @param  int  $entryId
     * @param  int  $entryLabelId
     */
    public function addEntryEntryLabel($entryId, $entryLabelId)
    {
        global $wpdb;

        $wpdb->query($wpdb->prepare("INSERT IGNORE INTO " . $this->getEntryEntryLabelsTableName() . " (entry_id, entry_label_id) VALUES (%d, %d)", $entryId, $entryLabelId));
    }

    /**
     * Delete the given entry label from the given entry
     *
     * @param  int  $entryId
     * @param  int  $entryLabelId
     */
    public function deleteEntryEntryLabel($entryId, $entryLabelId)
    {
        global $wpdb;

        $wpdb->query($wpdb->prepare("DELETE FROM " . $this->getEntryEntryLabelsTableName() . " WHERE entry_id = %d AND entry_label_id = %d", $entryId, $entryLabelId));
    }

    /**
     * Get the form ID from the entry ID
     *
     * @param   int  $entryId
     * @return  int
     */
    public function getFormIdFromEntryId($entryId)
    {
        global $wpdb;

        $entryId = (int) $entryId;

        $formId = $wpdb->get_var($wpdb->prepare("SELECT form_id FROM " . $this->getEntriesTableName() . " WHERE `id` = %d", $entryId));

        return (int) $formId;
    }

    /**
     * Get the entry with the given ID
     *
     * @param   int         $entryId
     * @return  array|null
     */
    public function findEntry($entryId)
    {
        global $wpdb;

        $entry = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM " . $this->getEntriesTableName() . " WHERE id = %d",
                $entryId
            ),
            ARRAY_A
        );

        if (is_array($entry)) {
            $data = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT `element_id`, `value` FROM " . $this->getEntryDataTableName() . " WHERE `entry_id` = %d",
                    $entryId
                ),
                ARRAY_A
            );

            if (is_array($data)) {
                foreach ($data as $datum) {
                    $entry['element_' . $datum['element_id']] = $datum['value'];
                }
            }

            return $entry;
        }

        return null;
    }

    /**
     * Is there an existing entry with the same value as the given element
     *
     * @param   Quform_Element_Field  $element
     * @return  bool
     */
    public function hasDuplicateEntry(Quform_Element_Field $element)
    {
        global $wpdb;

        $query = "SELECT `e`.`id` FROM `" . $this->getEntryDataTableName() . "` ed LEFT JOIN `" .
            $this->getEntriesTableName() . "` e ON `ed`.`entry_id` = `e`.`id`
WHERE `e`.`form_id` = %d
AND `ed`.`element_id` = %d
AND `ed`.`value` = '%s'";

        $args = array(
            $element->getForm()->getId(),
            $element->getId(),
            $element->getValueForStorage()
        );

        $entryId = $element->getForm()->getEntryId();

        if (is_numeric($entryId) && $entryId > 0) {
            $query .= " AND `e`.`id` != %d";
            $args[] = $entryId;
        }

        $result = $wpdb->get_row($wpdb->prepare($query, $args));

        return $result !== null;
    }

    /**
     * Mark the entries with the IDs in the given array as read
     *
     * @param   array  $ids  The array of entry IDs
     * @return  int          The number of affected rows
     */
    public function readEntries(array $ids)
    {
        global $wpdb;

        $ids = $this->sanitizeIds($ids);

        if (empty($ids)) {
            return 0;
        }

        $joinedIds = $this->joinIds($ids);

        $sql = "UPDATE " . $this->getEntriesTableName() . " SET unread = 0 WHERE id IN ($joinedIds)";

        $affectedRows = (int) $wpdb->query($sql);

        foreach ($ids as $id) {
            do_action('quform_entry_read', $id);
        }

        return $affectedRows;
    }

    /**
     * Mark the entries with the IDs in the given array as unread
     *
     * @param   array  $ids  The array of entry IDs
     * @return  int          The number of affected rows
     */
    public function unreadEntries(array $ids)
    {
        global $wpdb;

        $ids = $this->sanitizeIds($ids);

        if (empty($ids)) {
            return 0;
        }

        $joinedIds = $this->joinIds($ids);

        $sql = "UPDATE " . $this->getEntriesTableName() . " SET unread = 1 WHERE id IN ($joinedIds)";

        $affectedRows = (int) $wpdb->query($sql);

        foreach ($ids as $id) {
            do_action('quform_entry_unread', $id);
        }

        return $affectedRows;
    }

    /**
     * Trash the entries with the IDs in the given array
     *
     * @param   array  $ids  The array of entry IDs
     * @return  int          The number of deleted rows
     */
    public function trashEntries(array $ids)
    {
        global $wpdb;

        $ids = $this->sanitizeIds($ids);

        if (empty($ids)) {
            return 0;
        }

        $joinedIds = $this->joinIds($ids);

        $sql = "UPDATE " . $this->getEntriesTableName() . " SET status = 'trash' WHERE id IN ($joinedIds)";

        $affectedRows = (int) $wpdb->query($sql);

        foreach ($ids as $id) {
            do_action('quform_entry_trashed', $id);
        }

        return $affectedRows;
    }

    /**
     * Untrash the entries with the IDs in the given array
     *
     * @param   array  $ids  The array of entry IDs
     * @return  int          The number of deleted rows
     */
    public function untrashEntries(array $ids)
    {
        global $wpdb;

        $ids = $this->sanitizeIds($ids);

        if (empty($ids)) {
            return 0;
        }

        $joinedIds = $this->joinIds($ids);

        $sql = "UPDATE " . $this->getEntriesTableName() . " SET status = 'normal' WHERE id IN ($joinedIds)";

        $affectedRows = (int) $wpdb->query($sql);

        foreach ($ids as $id) {
            do_action('quform_entry_untrashed', $id);
        }

        return $affectedRows;
    }

    /**
     * Delete the entries with the IDs in the given array
     *
     * @param   array  $ids  The array of entry IDs
     * @return  int          The number of deleted rows
     */
    public function deleteEntries(array $ids)
    {
        global $wpdb;

        $ids = $this->sanitizeIds($ids);

        if (empty($ids)) {
            return 0;
        }

        $joinedIds = $this->joinIds($ids);

        foreach ($ids as $id) {
            do_action('quform_pre_delete_entry', $id);
        }

        // Delete entry label association
        $wpdb->query("DELETE FROM " . $this->getEntryEntryLabelsTableName() . " WHERE entry_id IN ($joinedIds)");

        // Delete entry data
        $wpdb->query("DELETE FROM " . $this->getEntryDataTableName() . " WHERE entry_id IN ($joinedIds)");

        // Delete the entries
        $affectedRows = (int) $wpdb->query("DELETE FROM " . $this->getEntriesTableName() . " WHERE id IN ($joinedIds)");

        foreach ($ids as $id) {
            do_action('quform_entry_deleted', $id);
        }

        return $affectedRows;
    }

    /**
     * Get the count of all unread entries
     *
     * @return int
     */
    public function getAllUnreadEntriesCount()
    {
        global $wpdb;

        $sql = "SELECT COUNT(*) FROM " . $this->getEntriesTableName() . " WHERE unread = 1 AND status = 'normal';";

        return $wpdb->get_var($sql);
    }

    /**
     * @return mixed
     */
    public function getAllFormsWithUnreadEntries()
    {
        global $wpdb;

        $sql = "SELECT f.id, f.name, (SELECT COUNT(*) FROM " . $this->getEntriesTableName() . " WHERE form_id = f.id AND unread = 1 AND status = 'normal') AS entries FROM " . $this->getFormsTableName() . " f HAVING entries > 0;";

        return $wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Get the most recent entries
     *
     * @param   int|null  $count  Limit to this number of entries
     * @return  array
     */
    public function getRecentEntries($count = null)
    {
        global $wpdb;

        $sql = "SELECT f.name, e.* FROM " . $this->getEntriesTableName() . " e LEFT JOIN " . $this->getFormsTableName() . " f ON e.form_id = f.id WHERE status = 'normal' ORDER BY e.created_at DESC";

        if (is_numeric($count)) {
            $sql .= $wpdb->prepare(" LIMIT %d", $count);
        }

        return $wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * @param   array  $row
     * @param   array  $config
     * @return  array
     */
    protected function addRowDataToConfig(array $row, array $config)
    {
        $config['id'] = (int) $row['id'];
        $config['name'] = $row['name'];
        $config['active'] = $row['active'] == 1;
        $config['trashed'] = $row['trashed'] == 1;
        $config['createdAt'] = $row['created_at'];
        $config['updatedAt'] = $row['updated_at'];

        return $config;
    }

    /**
     * Get the entry export
     *
     * @param Quform_Form $form
     * @param string      $from
     * @param string      $to
     *
     * @return array|null
     */
    public function exportEntries(Quform_Form $form, $from = '', $to = '')
    {
        global $wpdb;

        // Build the query
        $sql = "SELECT `entries`.*";

        foreach ($form->getRecursiveIterator() as $element) { // TODO it's leaves_only so not including list element
            if ($element->config('saveToDatabase')) { // TODO, only query element IDs that are given in the columns?
                $sql .= ", GROUP_CONCAT(if (`data`.`element_id` = {$element->getId()}, value, NULL)) AS `element_{$element->getId()}`";
            }
        }

        $sql .= $wpdb->prepare(" FROM `" . $this->getEntriesTableName() . "` `entries`
LEFT JOIN `" . $this->getEntryDataTableName() . "` `data` ON `data`.`entry_id` = `entries`.`id`
WHERE `entries`.`form_id` = %d AND `entries`.`status` = 'normal'", $form->getId());

        $dateParts = array();

        if ($from) {
            $dateParts[] = $wpdb->prepare('`entries`.`created_at` >= %s', get_gmt_from_date($from . ' 00:00:00'));
        }

        if ($to) {
            $dateParts[] = $wpdb->prepare('`entries`.`created_at` <= %s', get_gmt_from_date($to . ' 23:59:59'));
        }

        if (count($dateParts)) {
            $sql .= ' AND (' .  join(' AND ', $dateParts) . ')';
        }

        $sql .= " GROUP BY `entries`.`id`;";

        $sql = apply_filters('quform_export_entries_query_' . $form->getId(), $sql, $form, $from, $to);

        $wpdb->query('SET @@GROUP_CONCAT_MAX_LEN = 65535');

        return $wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Get the forms ordered by last updated
     *
     * @return array|null
     */
    public function getFormsByUpdatedAt()
    {
        global $wpdb;

        $sql = "SELECT `id`, `name` FROM " . $this->getFormsTableName() . " WHERE `trashed` = 0 AND `active` = 1 ORDER BY `updated_at` DESC";

        return $wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Does an entry exist for the given form and user?
     *
     * @param   int   $formId     The form ID
     * @param   int   $createdBy  The user ID
     * @return  bool
     */
    public function entryExistsByFormIdAndCreatedBy($formId, $createdBy)
    {
        global $wpdb;

        $sql = $wpdb->prepare(
            "SELECT id FROM " . $this->getEntriesTableName() . " WHERE form_id = %d AND created_by = %d AND status = 'normal'",
            $formId,
            $createdBy
        );

        return $wpdb->get_var($sql) !== null;
    }

    /**
     * Does an entry exist for the given form and IP address?
     *
     * @param   int     $formId     The form ID
     * @param   string  $ipAddress  The IP address
     * @return  bool
     */
    public function entryExistsByFormIdAndIpAddress($formId, $ipAddress)
    {
        global $wpdb;

        $sql = $wpdb->prepare(
            "SELECT id FROM " . $this->getEntriesTableName() . " WHERE form_id = %d AND ip = %s AND status = 'normal'",
            $formId,
            $ipAddress
        );

        return $wpdb->get_var($sql) !== null;
    }

    /**
     * Called when the plugin is uninstalled from the Tools page
     */
    public function uninstall()
    {
        global $wpdb;

        // Remove the forms tables
        foreach ($this->getTables() as $table) {
            $wpdb->query("DROP TABLE IF EXISTS `$table`");
        }

        // Remove the user options
        delete_metadata('user', 0, 'quform_recent_forms', '', true);
        delete_metadata('user', 0, 'quform_forms_order_by', '', true);
        delete_metadata('user', 0, 'quform_forms_order', '', true);
        delete_metadata('user', 0, 'quform_forms_per_page', '', true);
        delete_metadata('user', 0, 'quform_entries_order_by', '', true);
        delete_metadata('user', 0, 'quform_entries_order', '', true);
        delete_metadata('user', 0, 'quform_entries_per_page', '', true);
        delete_metadata('user', 0, 'quform_export_entries_format_settings', '', true);
    }

    /**
     * Get the list of database tables
     *
     * @return array
     */
    protected function getTables()
    {
        return array(
            $this->getFormsTableName(),
            $this->getEntriesTableName(),
            $this->getEntryDataTableName(),
            $this->getEntryLabelsTableName(),
            $this->getEntryEntryLabelsTableName()
        );
    }

    /**
     * Drop the database tables when a site is deleted
     *
     * @param   array  $tables
     * @return  array
     */
    public function dropTablesOnSiteDeletion($tables)
    {
        return array_merge($tables, $this->getTables());
    }
}
