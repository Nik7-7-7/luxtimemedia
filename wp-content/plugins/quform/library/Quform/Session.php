<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Session
{
    /**
     * @var string
     */
    protected $name;

    /**
     * The name of the session table
     *
     * @var string
     */
    protected $table;

    /**
     * @var string
     */
    protected $id;

    /**
     * The session data
     *
     * @var array
     */
    protected $data = array();

    /**
     * The lifetime of the session in seconds
     *
     * @var int
     */
    protected $lifetime;

    /**
     * Is the session started?
     *
     * @var bool
     */
    protected $started = false;

    /**
     * Has the session data been modified?
     *
     * @var bool
     */
    protected $dirty = false;

    /**
     * Get the name of the session table with the WP prefix added
     *
     * @return string
     */
    protected function getTableName()
    {
        global $wpdb;

        return $wpdb->prefix . 'quform_sessions';
    }

    /**
     * Get the serialized session data
     *
     * @param   string  $sessionId
     * @return  string
     */
    protected function read($sessionId)
    {
        global $wpdb;
        $data = '';

        $session = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $this->getTableName() . " WHERE id = %s", $sessionId), ARRAY_A);

        if ( ! is_null($session) && isset($session['payload'])) {
            $data = base64_decode($session['payload']);
        }

        return $data;
    }

    /**
     * Write the serialized session data
     *
     * @param  string  $sessionId
     * @param  string  $data
     */
    protected function write($sessionId, $data)
    {
        global $wpdb;

        if (apply_filters('quform_suppress_session_write_errors', true)) {
            $suppress_errors = $wpdb->suppress_errors();
        }

        $query = "INSERT INTO {$this->getTableName()} (`id`, `payload`, `last_activity`) VALUES (%s, %s, %s)
                  ON DUPLICATE KEY UPDATE `payload` = VALUES(`payload`), `last_activity` = VALUES(`last_activity`)";

        $wpdb->query($wpdb->prepare($query, $sessionId, base64_encode($data), time()));

        if (isset($suppress_errors)) {
            $wpdb->suppress_errors($suppress_errors);
        }
    }

    /**
     * Destroy the session with the given ID
     *
     * @param string $sessionId
     */
    protected function destroy($sessionId)
    {
        global $wpdb;

        $wpdb->delete($this->getTableName(), array('id' => $sessionId));
    }

    /**
     * Garbage collection
     */
    public function gc()
    {
        global $wpdb;

        $wpdb->query("DELETE FROM " . $this->getTableName() . " WHERE last_activity <= " . (time() - $this->lifetime));
    }

    /**
     * Set the session ID
     *
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get the session ID
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Determine if this is a valid session ID.
     *
     * @param   string  $id
     * @return  bool
     */
    public function isValidId($id)
    {
        return is_string($id) && preg_match('/^[a-zA-Z0-9]{40}$/', $id);
    }

    /**
     * Get a new, random session ID
     *
     * @return string
     */
    protected function generateSessionId()
    {
        return Quform::randomString(40);
    }

    /**
     * Start the session
     *
     * @return bool
     */
    public function start()
    {
        $this->name = 'quform_session_' . COOKIEHASH;
        $this->lifetime = apply_filters('quform_session_lifetime', 86400); // 24 hours in seconds

        $id = Quform::get($_COOKIE, $this->name);

        if ( ! $this->isValidId($id)) {
            $id = $this->generateSessionId();

            $action = is_admin() ? 'admin_init' : 'send_headers';

            add_action($action, array($this, 'setSessionCookie'));
        }

        $this->setId($id);

        $data = $this->read($this->getId());

        $this->data = Quform::isNonEmptyString($data) ? unserialize($data) : array();

        if ( ! $this->has('_token')) {
            $this->regenerateToken();
        }

        return $this->started = true;
    }

    /**
     * Set the session cookie
     */
    public function setSessionCookie() {
        $set = true;

        if (wp_doing_ajax() && Quform::get($_GET, 'action') != 'quform_support_page_caching') {
            $set = false;
        }

        if (!is_admin() && is_404()) {
            $set = false;
        }

        if (apply_filters('quform_set_session_cookie', $set)) {
            $this->setSessionIdCookie($this->getId());
        }
    }

    /**
     * Set the cookie to store the session ID
     *
     * @param string $id
     */
    protected function setSessionIdCookie($id)
    {
        $expire = apply_filters('quform_session_cookie_expire', 0);
        $secure = apply_filters('quform_session_cookie_secure', is_ssl());
        $httpOnly = apply_filters('quform_session_cookie_http_only', true);
        $sameSite = apply_filters('quform_session_cookie_same_site', $secure ? 'None' : 'Lax');

        Quform::setCookieHeader($this->name, $id, $expire, $secure, $httpOnly, $sameSite);
    }

    /**
     * Save the session data, only if something has changed
     */
    public function save()
    {
        if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/.well-known/acme-challenge/') === 0) {
            return; // Fix for Let's Encrypt causing "Commands out of sync" query error on some servers
        }

        if ($this->dirty && $this->started) {
            $this->write($this->id, serialize($this->data));
            $this->dirty = false;
        }
    }

    /**
     * Regenerate the CSRF token value
     */
    public function regenerateToken()
    {
        $this->put('_token', Quform::randomString(40));
    }

    /**
     * Get the CSRF token value
     *
     * @return string
     */
    public function getToken()
    {
        return $this->get('_token');
    }

    /**
     * Does the session data contain the given key?
     *
     * @param   string  $key
     * @return  bool
     */
    public function has($key)
    {
        return ! is_null($this->get($key));
    }

    /**
     * Get the session data with the given key
     *
     * @param   string|null  $key      The key within the session data
     * @param   mixed|null   $default  The default to return if the key does not exist
     * @return  mixed
     */
    public function get($key = null, $default = null)
    {
        return Quform::get($this->data, $key, $default);
    }

    /**
     * Set the session data with the given key
     *
     * @param  string  $key    The key within the session data
     * @param  mixed   $value  The value to set
     */
    public function set($key, $value)
    {
        Quform::set($this->data, $key, $value);
        $this->dirty = true;
    }

    /**
     * Put a key / value pair or array of key / value pairs into the session
     *
     * @param  string|array  $key
     * @param  mixed|null  	 $value
     * @return void
     */
    public function put($key, $value = null)
    {
        if ( ! is_array($key)) $key = array($key => $value);

        foreach ($key as $arrayKey => $arrayValue) {
            $this->set($arrayKey, $arrayValue);
        }
    }

    /**
     * Remove the item(s) with the given key(s) from the session
     *
     * @param array|string $keys
     */
    public function forget($keys)
    {
        Quform::forget($this->data, $keys);
        $this->dirty = true;
    }

    /**
     * Schedule the garbage collection task
     */
    protected function scheduleGc()
    {
        if ( ! wp_next_scheduled('quform_session_gc')) {
            wp_schedule_event(time() + (12 * HOUR_IN_SECONDS), 'twicedaily', 'quform_session_gc');
        }
    }

    /**
     * Unschedule the garbage collection task
     */
    protected function unscheduleGc()
    {
        if ($timestamp = wp_next_scheduled('quform_session_gc')) {
            wp_unschedule_event($timestamp, 'quform_session_gc');
        }
    }

    /**
     * On plugin activation, schedule the garbage collection task
     */
    public function activate()
    {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $sql = "CREATE TABLE " . $this->getTableName() . " (
            id VARCHAR(40) NOT NULL,
            payload longtext NOT NULL,
            last_activity INT UNSIGNED NOT NULL,
            UNIQUE KEY id (id)
        ) " . $wpdb->get_charset_collate() . ";";

        dbDelta($sql);

        $this->scheduleGc();
    }

    /**
     * On plugin deactivation, unschedule the garbage collection task and cleanup
     */
    public function deactivate()
    {
        $this->unscheduleGc();
        $this->gc();
    }

    /**
     * On plugin uninstall, unschedule the garbage collection task and remove the session table
     */
    public function uninstall()
    {
        global $wpdb;

        $this->unscheduleGc();

        $wpdb->query("DROP TABLE IF EXISTS " . $this->getTableName());
    }

    /**
     * Drop the session database table when a site is deleted
     *
     * @param   array  $tables
     * @return  array
     */
    public function dropTableOnSiteDeletion($tables)
    {
        $tables[] = $this->getTableName();

        return $tables;
    }
}
