<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform
{
    /**
     * On plugin activation set the activated flag
     */
    public static function onActivation()
    {
        add_option('quform_activated', '1');
    }

    /**
     * Bootstrap the plugin
     */
    public static function bootstrap()
    {
        $GLOBALS['quform'] = new Quform_Dispatcher(new Quform_Container());
        $GLOBALS['quform']->bootstrap();
    }

    /**
     * Get a service from the container
     *
     * @param   string  $name  The service name
     * @return  mixed          The service instance
     */
    public static function getService($name)
    {
        return $GLOBALS['quform']->getService($name);
    }

    /**
     * Get the URL to the plugin folder
     *
     * @param   string  $path  Extra path to append to the URL
     * @return  string
     */
    public static function url($path = '')
    {
        return Quform::pathExtra(plugins_url(QUFORM_NAME), $path);
    }

    /**
     * Get the URL to the plugin admin folder
     *
     * @param   string  $path  Extra path to append to the URL
     * @return  string
     */
    public static function adminUrl($path = '')
    {
        return Quform::pathExtra(Quform::url('admin'), $path);
    }

    /**
     * Allow users to white-label the plugin name on Quform pages
     *
     * @return string The plugin name
     */
    public static function getPluginName()
    {
        return apply_filters('quform_plugin_name', 'Quform');
    }

    /**
     * Get the IP address of the visitor
     *
     * @return string
     */
    public static function getClientIp()
    {
        $ip = $_SERVER['REMOTE_ADDR'];

        if ( ! empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( ! empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
        }

        $ip = apply_filters('quform_get_client_ip', $ip);

        return (string) $ip;
    }

    /**
     * Get the current URL
     *
     * @return string
     */
    public static function getCurrentUrl()
    {
        $url = '';

        if (isset($_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI'])) {
            $url .= 'http';

            if (is_ssl()) {
                $url .= 's';
            }

            $url .= '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        }

        return $url;
    }

    /**
     * Get the HTTP referer
     *
     * @return string
     */
    public static function getHttpReferer()
    {
        $referer = '';

        if (isset($_SERVER['HTTP_REFERER'])) {
            $referer = $_SERVER['HTTP_REFERER'];
        }

        return (string) $referer;
    }

    /**
     * Get the current post
     *
     * @return WP_Post|null
     */
    public static function getCurrentPost()
    {
        if (in_the_loop()) {
            return get_post();
        }

        $post = get_queried_object();

        if ($post instanceof WP_Post) {
            return $post;
        }

        return null;
    }

    /**
     * Get a property from the current post object
     *
     * @param   string    $property  Which property to get
     * @param   int|null  $postId    The post ID or null to use the current post
     * @return  string
     */
    public static function getPostProperty($property = 'ID', $postId = null)
    {
        $post = ! is_null($postId) ? get_post($postId) : Quform::getCurrentPost();
        $value = '';

        $whitelist = array('ID', 'post_author', 'post_date', 'post_date_gmt', 'post_content', 'post_title',
            'post_excerpt', 'post_status', 'comment_status', 'ping_status', 'post_name', 'to_ping', 'pinged',
            'post_modified', 'post_modified_gmt', 'post_content_filtered', 'post_parent', 'guid', 'menu_order',
            'post_type', 'post_mime_type', 'comment_count'
        );

        if (Quform::isNonEmptyString($property) &&
            in_array($property, $whitelist) &&
            $post instanceof WP_Post &&
            isset($post->{$property})
        ) {
            $value = $post->{$property};
        }

        return (string) $value;
    }

    /**
     * Get the post meta value with the given key from the given post ID or the current post
     *
     * @param   string        $key     The post meta key
     * @param   int|null      $postId  The post ID, if null the current post will be used
     * @return  mixed|string
     */
    public static function getPostMeta($key, $postId = null)
    {
        $post = ! is_null($postId) ? get_post($postId) : Quform::getCurrentPost();
        $value = '';

        if (Quform::isNonEmptyString($key) && $post instanceof WP_Post) {
            $value = get_post_meta($post->ID, $key, true);
        }

        return $value;
    }

    /**
     * Get a property from the current user object
     *
     * @param   string  $property  Which property to get
     * @return  string
     */
    public static function getUserProperty($property = 'ID')
    {
        $user = wp_get_current_user();
        $value = '';

        // Ensure user_pass is never returned
        $whitelist = array('ID', 'user_login', 'user_nicename', 'user_email', 'user_url', 'user_registered', 'display_name');

        if (Quform::isNonEmptyString($property) &&
            in_array($property, $whitelist) &&
            $user->ID > 0 &&
            isset($user->{$property})
        ) {
            $value = $user->{$property};
        }

        return (string) $value;
    }

    /**
     * Get the user meta value with the given key for the current user
     *
     * @param   string        $key  The user meta key
     * @return  mixed|string
     */
    public static function getUserMeta($key)
    {
        $value = '';

        if (Quform::isNonEmptyString($key)) {
            $value = get_user_meta(get_current_user_id(), $key, true);
        }

        return $value;
    }

    /**
     * Convert the given string to studly case
     *
     * @param  string $value
     * @return string
     */
    public static function studlyCase($value)
    {
        $value = ucwords(str_replace(array('-', '_'), ' ', $value));

        return str_replace(' ', '', $value);
    }

    /**
     * Is the current request a GET request?
     *
     * @return bool
     */
    public static function isGetRequest()
    {
        return isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) === 'GET';
    }

    /**
     * Is the current request a POST request?
     *
     * @return bool
     */
    public static function isPostRequest()
    {
        return isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) === 'POST';
    }

    /**
     * Escaping for strings in HTML
     *
     * Identical to esc_html but with double encoding true
     *
     * @param   string  $value
     * @param   int     $flags
     * @return  string
     */
    public static function escape($value, $flags = ENT_QUOTES)
    {
        $value = wp_check_invalid_utf8($value);

        return _wp_specialchars($value, $flags, false, true);
    }

    /**
     * Sanitize multiple classes
     *
     * @param   string|array  $classes  Classes to sanitize
     * @return  string                  The sanitized classes
     */
    public static function sanitizeClass($classes)
    {
        if (is_array($classes)) {
            $classes = join(' ', $classes);
        }

        $classes = preg_split('/\s+/', trim($classes));

        $sanitizedClasses = array();

        foreach ($classes as $class) {
            $sanitizedClass = sanitize_html_class($class);

            if ( ! empty($sanitizedClass)) {
                $sanitizedClasses[] = $sanitizedClass;
            }
        }

        return join(' ', $sanitizedClasses);
    }

    /**
     * Sanitizes a multiline string
     *
     * @param   string  $str
     * @return  string
     */
    public static function sanitizeTextareaField($str)
    {
        if (function_exists('sanitize_textarea_field')) {
            return sanitize_textarea_field($str);
        }

        return join("\n", array_map('sanitize_text_field', explode("\n", $str)));
    }

    /**
     * Get a value from an array, allowing dot notation
     *
     * @param   array   $array
     * @param   string  $key
     * @param   mixed   $default
     * @return  mixed
     */
    public static function get($array, $key = null, $default = null)
    {
        if (is_null($key)) {
            return $array;
        }

        if (isset($array[$key])) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if ( ! is_array($array) || ! array_key_exists($segment, $array)) {
                return $default;
            }

            $array = $array[$segment];
        }

        return $array;
    }

    /**
     * Set an array item to a given value using "dot" notation.
     *
     * If no key is given to the method, the entire array will be replaced.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $value
     * @return array
     */
    public static function set(&$array, $key, $value)
    {
        if (is_null($key)) return $array = $value;

        $keys = explode('.', $key);

        while (count($keys) > 1)
        {
            $key = array_shift($keys);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if ( ! isset($array[$key]) || ! is_array($array[$key]))
            {
                $array[$key] = array();
            }

            $array =& $array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }

    /**
     * Remove one or many array items from a given array using "dot" notation.
     *
     * @param  array  $array
     * @param  array|string  $keys
     * @return void
     */
    public static function forget(&$array, $keys)
    {
        $original =& $array;

        foreach ((array) $keys as $key)
        {
            $parts = explode('.', $key);

            while (count($parts) > 1)
            {
                $part = array_shift($parts);

                if (isset($array[$part]) && is_array($array[$part]))
                {
                    $array =& $array[$part];
                }
            }

            unset($array[array_shift($parts)]);

            // clean up after each pass
            $array =& $original;
        }
    }

    /**
     * Returns true if and only if the given value is a string with at least one character
     *
     * @param   mixed    $value
     * @return  boolean
     */
    public static function isNonEmptyString($value)
    {
        return is_string($value) && $value !== '';
    }

    /**
     * Die and dump arguments, debugging helper method
     */
    public static function dd()
    {
        echo '<pre>';
        foreach (func_get_args() as $arg) {
            var_dump($arg);
        }
        echo '</pre>';
        exit;
    }

    /**
     * Log arguments to the PHP error log
     */
    public static function log()
    {
        foreach (func_get_args() as $arg) {
            ob_start();
            var_dump($arg);
            error_log(ob_get_clean());
        }
    }

    /**
     * Log arguments to the PHP error log only if WP_DEBUG is enabled
     */
    public static function debug()
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            call_user_func_array(array('Quform', 'log'), func_get_args());
        }
    }

    /**
     * Get the length of the given string (multibyte aware)
     *
     * @param   string  $string
     * @return  int
     */
    public static function strlen($string)
    {
        return mb_strlen($string, get_bloginfo('charset'));
    }

    /**
     * Get part of the given string (multibyte aware)
     *
     * @param   string    $string
     * @param   int       $start
     * @param   int|null  $length
     * @return  string
     */
    public static function substr($string, $start, $length = null)
    {
        return mb_substr($string, $start, $length, get_bloginfo('charset'));
    }

    /**
     * Generates an HTML tag
     *
     * @param   string  $tag         The HTML tag
     * @param   array   $attributes  Attributes key => value list for the tag
     * @param   string  $content     Content for non-void elements (not escaped)
     * @return  string
     */
    public static function getHtmlTag($tag, array $attributes = array(), $content = '')
    {
        // https://www.w3.org/TR/html5/syntax.html#void-elements
        $voidElements = array('area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'keygen', 'link', 'meta', 'param', 'source', 'track', 'wbr');

        $tag = Quform::escape(strtolower($tag));

        if (in_array($tag, $voidElements)) {
            $output = sprintf('<%s%s />', $tag, self::parseHtmlAttributes($attributes));
        } else {
            $output = sprintf('<%1$s%2$s>%3$s</%1$s>', $tag, self::parseHtmlAttributes($attributes), $content);
        }

        return $output;
    }

    /**
     * Parse an array of HTML attributes into an attribute string
     *
     * @param   array   $attributes  Attributes key => value list for the tag
     * @return  string
     */
    public static function parseHtmlAttributes(array $attributes)
    {
        $escapedAttributes = array();

        foreach ($attributes as $key => $value) {
            if ($value === true) {
                $escapedAttributes[] = $key;
            } else {
                $escapedAttributes[] = sprintf('%s="%s"', $key, Quform::escape($value));
            }
        }

        $escapedAttributes = count($escapedAttributes) > 0 ? ' ' . implode(' ', $escapedAttributes) : '';

        return $escapedAttributes;
    }

    /**
     * Get random bytes with the given $length
     *
     * @param   int     $length
     * @return  string
     */
    public static function randomBytes($length)
    {
        static $passwordHash;

        if ( ! isset($passwordHash)) {
            if ( ! class_exists('PasswordHash')) {
                require_once ABSPATH . WPINC . '/class-phpass.php';
            }

            $passwordHash = new PasswordHash(8, false);
        }

        return $passwordHash->get_random_bytes($length);
    }

    /**
     * Generate a random string with the given $length
     *
     * @param   int     $length
     * @return  string
     */
    public static function randomString($length)
    {
        $string = '';

        while (($len = strlen($string)) < $length) {
            $size = $length - $len;

            $bytes = Quform::randomBytes($size);

            $string .= substr(str_replace(array('/', '+', '='), '', base64_encode($bytes)), 0, $size);
        }

        return $string;
    }

    /**
     * Set a cookie
     *
     * @param  string  $name        The name of the cookie
     * @param  string  $value       The value of the cookie
     * @param  int     $expire      The time the cookie expires as Unix timestamp
     * @param  bool    $secure      Send the cookie over HTTPS only
     * @param  bool    $httpOnly    Make the cookie only accessible over the HTTP protocol
     * @param  bool    $logFailure  Make a log entry if the cookie could not be created because headers already sent
     */
    public static function setCookie($name, $value, $expire, $secure = false, $httpOnly = false, $logFailure = false)
    {
        if ( ! headers_sent()) {
            setcookie($name, $value, $expire, COOKIEPATH, COOKIE_DOMAIN, $secure, $httpOnly);
        } elseif ($logFailure && defined('WP_DEBUG') && WP_DEBUG) {
            headers_sent($file, $line);
            Quform::log("$name cookie cannot be set - headers already sent by $file on line $line");
        }
    }

    /**
     * Set a cookie using a header
     *
     * @param  string  $name      The name of the cookie
     * @param  string  $value     The value of the cookie
     * @param  int     $expire    The time the cookie expires as Unix timestamp
     * @param  bool    $secure    Whether the cookie should be sent over HTTPS only
     * @param  bool    $httpOnly  Whether the cookie will be made accessible only through the HTTP protocol
     * @param  bool    $sameSite  Whether the cookie will be available for cross-site requests
     */
    public static function setCookieHeader($name, $value, $expire, $secure = false, $httpOnly = false, $sameSite = 'Lax')
    {
        if (headers_sent()) {
            return;
        }

        $reserved_chars_from = array('=', ',', ';', ' ', "\t", "\r", "\n", "\v", "\f");
        $reserved_chars_to = array('%3D', '%2C', '%3B', '%20', '%09', '%0D', '%0A', '%0B', '%0C');

        $str = str_replace($reserved_chars_from, $reserved_chars_to, $name);

        $str .= '=';

        if ((string) $value === '') {
            $str .= 'deleted; expires=' . gmdate('D, d-M-Y H:i:s T', time() - 31536001) . '; Max-Age=0';
        } else {
            $str .= rawurlencode($value);

            if ($expire !== 0) {
                $maxAge = $expire - time();
                $maxAge = 0 >= $maxAge ? 0 : $maxAge;
                $str .= '; expires=' . gmdate('D, d-M-Y H:i:s T', $expire) . '; Max-Age=' . $maxAge;
            }
        }

        if (COOKIEPATH) {
            $str .= '; path=' . COOKIEPATH;
        }

        if (COOKIE_DOMAIN) {
            $str .= '; domain=' . COOKIE_DOMAIN;
        }

        if ($secure) {
            $str .= '; secure';
        }

        if ($httpOnly) {
            $str .= '; httponly';
        }

        if ($sameSite) {
            $str .= '; samesite=' . $sameSite;
        }

        header('Set-Cookie: ' . $str, false);
    }

    /**
     * Ensure the given number $x is between $min and $max inclusive
     *
     * @param   mixed  $x
     * @param   mixed  $min
     * @param   mixed  $max
     * @return  mixed
     */
    public static function clamp($x, $min, $max)
    {
        return min(max($x, $min), $max);
    }

    /**
     * Get the given path with $extra appended
     *
     * @param   string  $path   The path
     * @param   string  $extra  Extra path to append to the path
     * @return  string          The combined path (no trailing slash is added)
     */
    public static function pathExtra($path, $extra = '')
    {
        if (Quform::isNonEmptyString($extra)) {
            $path .= '/' . ltrim($extra, '/');
        }

        return $path;
    }

    /**
     * Get a writable temporary directory
     *
     * @param   string  $extra  Extra path to append to the path
     * @return  string          Path without trailing slash
     */
    public static function getTempDir($extra = '')
    {
        return Quform::pathExtra(untrailingslashit(Quform::wpGetTempDir()), $extra);
    }

    /**
     * Get a writable temporary directory
     *
     * This is a duplicate of the WP function get_temp_dir() because there was an issue with one
     * popular plugin manually firing the wp_ajax_* hooks before WordPress does,
     * causing this plugin to fatal error since this function was not available
     * at that time. So we'll just use the function below in all cases instead of the
     * WP function.
     *
     * @return string
     */
    private static function wpGetTempDir()
    {
        static $temp = '';
        if ( defined('WP_TEMP_DIR') )
            return trailingslashit(WP_TEMP_DIR);

        if ( $temp )
            return trailingslashit( $temp );

        if ( function_exists('sys_get_temp_dir') ) {
            $temp = sys_get_temp_dir();
            if ( @is_dir( $temp ) && wp_is_writable( $temp ) )
                return trailingslashit( $temp );
        }

        $temp = ini_get('upload_tmp_dir');
        if ( @is_dir( $temp ) && wp_is_writable( $temp ) )
            return trailingslashit( $temp );

        $temp = WP_CONTENT_DIR . '/';
        if ( is_dir( $temp ) && wp_is_writable( $temp ) )
            return $temp;

        $temp = '/tmp/'; // Bug fix for the WP version

        return $temp;
    }

    /**
     * Get the URL to the WP uploads directory
     *
     * @param   string  $extra  Extra path to append to the path
     * @return  string
     */
    public static function getUploadsUrl($extra = '')
    {
        $uploads = wp_upload_dir();

        $url = Quform::pathExtra($uploads['baseurl'], $extra);

        $url = apply_filters('quform_uploads_url', $url, $extra);

        return $url;
    }

    /**
     * Get the absolute path to the WordPress upload directory. If the path is not writable it will return false.
     *
     * @param   string        $extra  Extra path to append to the path
     * @return  string|false          The upload path or false on failure
     */
    public static function getUploadsDir($extra = '')
    {
        $uploads = wp_upload_dir();

        if ($uploads['error'] !== false) {
            return false;
        }

        $path = Quform::pathExtra($uploads['basedir'], $extra);

        $path = apply_filters('quform_uploads_dir', $path, $extra);

        return $path;
    }

    /**
     * Is PCRE compiled with Unicode support?
     *
     * @return bool
     */
    public static function hasPcreUnicodeSupport()
    {
        static $hasPcreUnicodeSupport;

        if ($hasPcreUnicodeSupport === null) {
            $hasPcreUnicodeSupport = defined('PREG_BAD_UTF8_OFFSET_ERROR') && @preg_match('/\pL/u', 'a') == 1;
        }

        return $hasPcreUnicodeSupport;
    }

    /**
     * Get the available locales for Kendo scripts
     *
     * @return array
     */
    public static function getLocales()
    {
        return array(
            'aa' => array(
                'name' => 'Afar',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'aa-DJ' => array(
                'name' => 'Afar (Djibouti)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'aa-ER' => array(
                'name' => 'Afar (Eritrea)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'aa-ET' => array(
                'name' => 'Afar (Ethiopia)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'af' => array(
                'name' => 'Afrikaans',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'g:i a',
                'dateTimeFormat' => 'Y-m-d g:i a'
            ),
            'af-NA' => array(
                'name' => 'Afrikaans (Namibia)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'g:i a',
                'dateTimeFormat' => 'Y-m-d g:i a'
            ),
            'af-ZA' => array(
                'name' => 'Afrikaans (South Africa)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'g:i a',
                'dateTimeFormat' => 'Y-m-d g:i a'
            ),
            'agq' => array(
                'name' => 'Aghem',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'agq-CM' => array(
                'name' => 'Aghem (Cameroon)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'ak' => array(
                'name' => 'Akan',
                'dateFormat' => 'Y/m/d',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'Y/m/d g:i A'
            ),
            'ak-GH' => array(
                'name' => 'Akan (Ghana)',
                'dateFormat' => 'Y/m/d',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'Y/m/d g:i A'
            ),
            'sq' => array(
                'name' => 'Albanian',
                'dateFormat' => 'j.n.Y',
                'timeFormat' => 'g:i a',
                'dateTimeFormat' => 'j.n.Y g:i a'
            ),
            'sq-AL' => array(
                'name' => 'Albanian (Albania)',
                'dateFormat' => 'j.n.Y',
                'timeFormat' => 'g:i a',
                'dateTimeFormat' => 'j.n.Y g:i a'
            ),
            'sq-MK' => array(
                'name' => 'Albanian (Former Yugoslav Republic of Macedonia)',
                'dateFormat' => 'j.n.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j.n.Y H:i'
            ),
            'sq-XK' => array(
                'name' => 'Albanian (Kosovo)',
                'dateFormat' => 'j.n.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j.n.Y H:i'
            ),
            'am' => array(
                'name' => 'Amharic',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'am-ET' => array(
                'name' => 'Amharic (Ethiopia)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'ar' => array(
                'name' => 'Arabic',
                'dateFormat' => 'd/m/y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/y h:i A'
            ),
            'ar-DZ' => array(
                'name' => 'Arabic (Algeria)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd-m-Y G:i'
            ),
            'ar-BH' => array(
                'name' => 'Arabic (Bahrain)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/Y h:i A'
            ),
            'ar-TD' => array(
                'name' => 'Arabic (Chad)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'ar-KM' => array(
                'name' => 'Arabic (Comoros)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'ar-DJ' => array(
                'name' => 'Arabic (Djibouti)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'ar-EG' => array(
                'name' => 'Arabic (Egypt)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/Y h:i A'
            ),
            'ar-ER' => array(
                'name' => 'Arabic (Eritrea)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'ar-IQ' => array(
                'name' => 'Arabic (Iraq)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/Y h:i A'
            ),
            'ar-IL' => array(
                'name' => 'Arabic (Israel)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j/n/Y G:i'
            ),
            'ar-JO' => array(
                'name' => 'Arabic (Jordan)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/Y h:i A'
            ),
            'ar-KW' => array(
                'name' => 'Arabic (Kuwait)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/Y h:i A'
            ),
            'ar-LB' => array(
                'name' => 'Arabic (Lebanon)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/Y h:i A'
            ),
            'ar-LY' => array(
                'name' => 'Arabic (Libya)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/Y h:i A'
            ),
            'ar-MR' => array(
                'name' => 'Arabic (Mauritania)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'ar-MA' => array(
                'name' => 'Arabic (Morocco)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd-m-Y G:i'
            ),
            'ar-OM' => array(
                'name' => 'Arabic (Oman)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/Y h:i A'
            ),
            'ar-PS' => array(
                'name' => 'Arabic (Palestinian Territories)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'ar-QA' => array(
                'name' => 'Arabic (Qatar)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/Y h:i A'
            ),
            'ar-SA' => array(
                'name' => 'Arabic (Saudi Arabia)',
                'dateFormat' => 'd/m/y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/y h:i A'
            ),
            'ar-SO' => array(
                'name' => 'Arabic (Somalia)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'ar-SS' => array(
                'name' => 'Arabic (South Sudan)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'ar-SD' => array(
                'name' => 'Arabic (Sudan)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'ar-SY' => array(
                'name' => 'Arabic (Syria)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/Y h:i A'
            ),
            'ar-TN' => array(
                'name' => 'Arabic (Tunisia)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd-m-Y G:i'
            ),
            'ar-AE' => array(
                'name' => 'Arabic (U.A.E.)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/Y h:i A'
            ),
            'ar-001' => array(
                'name' => 'Arabic (World)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'ar-YE' => array(
                'name' => 'Arabic (Yemen)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/Y h:i A'
            ),
            'hy' => array(
                'name' => 'Armenian',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'hy-AM' => array(
                'name' => 'Armenian (Armenia)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'as' => array(
                'name' => 'Assamese',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'A g:i',
                'dateTimeFormat' => 'd-m-Y A g:i'
            ),
            'as-IN' => array(
                'name' => 'Assamese (India)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'A g:i',
                'dateTimeFormat' => 'd-m-Y A g:i'
            ),
            'asa' => array(
                'name' => 'Asu',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'asa-TZ' => array(
                'name' => 'Asu (Tanzania)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'ast' => array(
                'name' => 'Asturian',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'ast-ES' => array(
                'name' => 'Asturian (Spain)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'az' => array(
                'name' => 'Azeri',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'az-Cyrl' => array(
                'name' => 'Azeri (Cyrillic)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd.m.Y G:i'
            ),
            'az-Cyrl-AZ' => array(
                'name' => 'Azeri (Cyrillic, Azerbaijan)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd.m.Y G:i'
            ),
            'az-Latn' => array(
                'name' => 'Azeri (Latin)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'az-Latn-AZ' => array(
                'name' => 'Azeri (Latin, Azerbaijan)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'ksf' => array(
                'name' => 'Bafia',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'ksf-CM' => array(
                'name' => 'Bafia (Cameroon)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'bm' => array(
                'name' => 'Bambara',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'bm-Latn' => array(
                'name' => 'Bambara (Latin)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'bm-Latn-ML' => array(
                'name' => 'Bambara (Latin, Mali)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'bas' => array(
                'name' => 'Basaa',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'bas-CM' => array(
                'name' => 'Basaa (Cameroon)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'ba' => array(
                'name' => 'Bashkir',
                'dateFormat' => 'd.m.y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd.m.y G:i'
            ),
            'ba-RU' => array(
                'name' => 'Bashkir (Russia)',
                'dateFormat' => 'd.m.y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd.m.y G:i'
            ),
            'eu' => array(
                'name' => 'Basque',
                'dateFormat' => 'Y/m/d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y/m/d H:i'
            ),
            'eu-ES' => array(
                'name' => 'Basque (Spain)',
                'dateFormat' => 'Y/m/d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y/m/d H:i'
            ),
            'be' => array(
                'name' => 'Belarusian',
                'dateFormat' => 'd.m.y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.y H:i'
            ),
            'be-BY' => array(
                'name' => 'Belarusian (Belarus)',
                'dateFormat' => 'd.m.y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.y H:i'
            ),
            'bem' => array(
                'name' => 'Bemba',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'bem-ZM' => array(
                'name' => 'Bemba (Zambia)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'bez' => array(
                'name' => 'Bena',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'bez-TZ' => array(
                'name' => 'Bena (Tanzania)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'bn' => array(
                'name' => 'Bengali',
                'dateFormat' => 'd-m-y',
                'timeFormat' => 'H.i',
                'dateTimeFormat' => 'd-m-y H.i'
            ),
            'bn-BD' => array(
                'name' => 'Bengali (Bangladesh)',
                'dateFormat' => 'd-m-y',
                'timeFormat' => 'H.i',
                'dateTimeFormat' => 'd-m-y H.i'
            ),
            'bn-IN' => array(
                'name' => 'Bengali (India)',
                'dateFormat' => 'd-m-y',
                'timeFormat' => 'H.i',
                'dateTimeFormat' => 'd-m-y H.i'
            ),
            'byn' => array(
                'name' => 'Bilen',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'byn-ER' => array(
                'name' => 'Bilen (Eritrea)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'bin' => array(
                'name' => 'Bini',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:iA',
                'dateTimeFormat' => 'j/n/Y g:iA'
            ),
            'bin-NG' => array(
                'name' => 'Bini (Nigeria)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:iA',
                'dateTimeFormat' => 'j/n/Y g:iA'
            ),
            'brx' => array(
                'name' => 'Bodo',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'brx-IN' => array(
                'name' => 'Bodo (India)',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'bs' => array(
                'name' => 'Bosnian',
                'dateFormat' => 'd.m.Y.',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y. H:i'
            ),
            'bs-Cyrl' => array(
                'name' => 'Bosnian (Cyrillic)',
                'dateFormat' => 'j.n.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y G:i'
            ),
            'bs-Cyrl-BA' => array(
                'name' => 'Bosnian (Cyrillic, Bosnia and Herzegovina)',
                'dateFormat' => 'j.n.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y G:i'
            ),
            'bs-Latn' => array(
                'name' => 'Bosnian (Latin)',
                'dateFormat' => 'd.m.Y.',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y. H:i'
            ),
            'bs-Latn-BA' => array(
                'name' => 'Bosnian (Latin, Bosnia and Herzegovina)',
                'dateFormat' => 'd.m.Y.',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y. H:i'
            ),
            'br' => array(
                'name' => 'Breton',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'br-FR' => array(
                'name' => 'Breton (France)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'bg' => array(
                'name' => 'Bulgarian',
                'dateFormat' => 'j.n.Y г.',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y г. G:i'
            ),
            'bg-BG' => array(
                'name' => 'Bulgarian (Bulgaria)',
                'dateFormat' => 'j.n.Y г.',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y г. G:i'
            ),
            'my' => array(
                'name' => 'Burmese',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'my-MM' => array(
                'name' => 'Burmese (Myanmar)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'ca' => array(
                'name' => 'Catalan',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j/n/Y G:i'
            ),
            'ca-AD' => array(
                'name' => 'Catalan (Andorra)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j/n/Y G:i'
            ),
            'ca-FR' => array(
                'name' => 'Catalan (France)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j/n/Y G:i'
            ),
            'ca-IT' => array(
                'name' => 'Catalan (Italy)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j/n/Y G:i'
            ),
            'ca-ES' => array(
                'name' => 'Catalan (Spain)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j/n/Y G:i'
            ),
            'tzm' => array(
                'name' => 'Central Atlas Tamazight',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd-m-Y G:i'
            ),
            'tzm-Arab' => array(
                'name' => 'Central Atlas Tamazight (Arabic)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j/n/Y G:i'
            ),
            'tzm-Arab-MA' => array(
                'name' => 'Central Atlas Tamazight (Arabic, Morocco)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j/n/Y G:i'
            ),
            'tzm-Latn' => array(
                'name' => 'Central Atlas Tamazight (Latin)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd-m-Y G:i'
            ),
            'tzm-Latn-DZ' => array(
                'name' => 'Central Atlas Tamazight (Latin, Algeria)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd-m-Y G:i'
            ),
            'tzm-Latn-MA' => array(
                'name' => 'Central Atlas Tamazight (Latin, Morocco)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'tzm-Tfng' => array(
                'name' => 'Central Atlas Tamazight (Tifinagh)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd-m-Y G:i'
            ),
            'tzm-Tfng-MA' => array(
                'name' => 'Central Atlas Tamazight (Tifinagh, Morocco)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd-m-Y G:i'
            ),
            'ku' => array(
                'name' => 'Central Kurdish',
                'dateFormat' => 'Y/m/d',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'Y/m/d h:i A'
            ),
            'ku-Arab' => array(
                'name' => 'Central Kurdish (Arabic)',
                'dateFormat' => 'Y/m/d',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'Y/m/d h:i A'
            ),
            'ku-Arab-IQ' => array(
                'name' => 'Central Kurdish (Arabic, Iraq)',
                'dateFormat' => 'Y/m/d',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'Y/m/d h:i A'
            ),
            'ce' => array(
                'name' => 'Chechen',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'ce-RU' => array(
                'name' => 'Chechen (Russia)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'chr' => array(
                'name' => 'Cherokee',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'chr-Cher' => array(
                'name' => 'Cherokee',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'chr-Cher-US' => array(
                'name' => 'Cherokee (United States)',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'cgg' => array(
                'name' => 'Chiga',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'cgg-UG' => array(
                'name' => 'Chiga (Uganda)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'zh' => array(
                'name' => 'Chinese',
                'dateFormat' => 'Y/n/j',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'Y/n/j G:i'
            ),
            'zh-CN' => array(
                'name' => 'Chinese (Simplified, China)',
                'dateFormat' => 'Y/n/j',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'Y/n/j G:i'
            ),
            'zh-Hans' => array(
                'name' => 'Chinese (Simplified Han)',
                'dateFormat' => 'Y/n/j',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'Y/n/j G:i'
            ),
            'zh-Hans-HK' => array(
                'name' => 'Chinese (Simplified Han, Hong Kong SAR)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'Ag:i',
                'dateTimeFormat' => 'j/n/Y Ag:i'
            ),
            'zh-Hans-MO' => array(
                'name' => 'Chinese (Simplified Han, Macao SAR)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'Ag:i',
                'dateTimeFormat' => 'j/n/Y Ag:i'
            ),
            'zh-SG' => array(
                'name' => 'Chinese (Simplified, Singapore)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'A g:i',
                'dateTimeFormat' => 'j/n/Y A g:i'
            ),
            'zh-Hant' => array(
                'name' => 'Chinese (Traditional)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j/n/Y G:i'
            ),
            'zh-HK' => array(
                'name' => 'Chinese (Traditional, Hong Kong SAR)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j/n/Y G:i'
            ),
            'zh-MO' => array(
                'name' => 'Chinese (Traditional, Macao SAR)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j/n/Y G:i'
            ),
            'zh-TW' => array(
                'name' => 'Chinese (Traditional, Taiwan)',
                'dateFormat' => 'Y/n/j',
                'timeFormat' => 'A h:i',
                'dateTimeFormat' => 'Y/n/j A h:i'
            ),
            'zh-CHS' => array(
                'name' => 'Chinese (Simplified) (zh-CHS)',
                'dateFormat' => 'Y/n/j',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'Y/n/j G:i'
            ),
            'zh-CHT' => array(
                'name' => 'Chinese (Traditional) (zh-CHT)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j/n/Y G:i'
            ),
            'cu' => array(
                'name' => 'Church Slavic',
                'dateFormat' => 'Y.m.d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y.m.d H:i'
            ),
            'cu-RU' => array(
                'name' => 'Church Slavic (Russia)',
                'dateFormat' => 'Y.m.d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y.m.d H:i'
            ),
            'ksh' => array(
                'name' => 'Colognian',
                'dateFormat' => 'j. n. Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j. n. Y H:i'
            ),
            'ksh-DE' => array(
                'name' => 'Colognian (Germany)',
                'dateFormat' => 'j. n. Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j. n. Y H:i'
            ),
            'kw' => array(
                'name' => 'Cornish',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'kw-GB' => array(
                'name' => 'Cornish (United Kingdom)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'co' => array(
                'name' => 'Corsican',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'co-FR' => array(
                'name' => 'Corsican (France)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'hr' => array(
                'name' => 'Croatian',
                'dateFormat' => 'j.n.Y.',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y. G:i'
            ),
            'hr-BA' => array(
                'name' => 'Croatian (Latin, Bosnia and Herzegovina)',
                'dateFormat' => 'd.m.Y.',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y. H:i'
            ),
            'hr-HR' => array(
                'name' => 'Croatian (Croatia)',
                'dateFormat' => 'j.n.Y.',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y. G:i'
            ),
            'cs' => array(
                'name' => 'Czech',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd.m.Y G:i'
            ),
            'cs-CZ' => array(
                'name' => 'Czech (Czech Republic)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd.m.Y G:i'
            ),
            'da' => array(
                'name' => 'Danish',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'da-DK' => array(
                'name' => 'Danish (Denmark)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'da-GL' => array(
                'name' => 'Danish (Greenland)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g.i A',
                'dateTimeFormat' => 'd/m/Y g.i A'
            ),
            'prs' => array(
                'name' => 'Dari',
                'dateFormat' => 'Y/n/j',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'Y/n/j g:i A'
            ),
            'prs-AF' => array(
                'name' => 'Dari (Afghanistan)',
                'dateFormat' => 'Y/n/j',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'Y/n/j g:i A'
            ),
            'dv' => array(
                'name' => 'Divehi',
                'dateFormat' => 'd/m/y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/y H:i'
            ),
            'dv-MV' => array(
                'name' => 'Divehi (Maldives)',
                'dateFormat' => 'd/m/y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/y H:i'
            ),
            'dua' => array(
                'name' => 'Duala',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'dua-CM' => array(
                'name' => 'Duala (Cameroon)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'nl' => array(
                'name' => 'Dutch',
                'dateFormat' => 'j-n-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j-n-Y H:i'
            ),
            'nl-AW' => array(
                'name' => 'Dutch (Aruba)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'nl-BE' => array(
                'name' => 'Dutch (Belgium)',
                'dateFormat' => 'j/m/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j/m/Y G:i'
            ),
            'nl-BQ' => array(
                'name' => 'Dutch (Bonaire, Sint Eustatius and Saba)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'nl-CW' => array(
                'name' => 'Dutch (Curaçao)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'nl-NL' => array(
                'name' => 'Dutch (Netherlands)',
                'dateFormat' => 'j-n-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j-n-Y H:i'
            ),
            'nl-SR' => array(
                'name' => 'Dutch (Suriname)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'nl-SX' => array(
                'name' => 'Dutch (Sint Maarten)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'dz' => array(
                'name' => 'Dzongkha',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'ཆུ་ཚོད་ g སྐར་མ་ i A',
                'dateTimeFormat' => 'Y-m-d ཆུ་ཚོད་ g སྐར་མ་ i A'
            ),
            'dz-BT' => array(
                'name' => 'Dzongkha (Bhutan)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'ཆུ་ཚོད་ g སྐར་མ་ i A',
                'dateTimeFormat' => 'Y-m-d ཆུ་ཚོད་ g སྐར་མ་ i A'
            ),
            'ebu' => array(
                'name' => 'Embu',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'ebu-KE' => array(
                'name' => 'Embu (Kenya)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en' => array(
                'name' => 'English',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'en-AS' => array(
                'name' => 'English (American Samoa)',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'en-AI' => array(
                'name' => 'English (Anguilla)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-AG' => array(
                'name' => 'English (Antigua & Barbuda)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'en-AU' => array(
                'name' => 'English (Australia)',
                'dateFormat' => 'j/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/m/Y g:i A'
            ),
            'en-AT' => array(
                'name' => 'English (Austria)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-BS' => array(
                'name' => 'English (Bahamas)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'en-BB' => array(
                'name' => 'English (Barbados)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'en-BE' => array(
                'name' => 'English (Belgium)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-BZ' => array(
                'name' => 'English (Belize)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-BM' => array(
                'name' => 'English (Bermuda)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'en-BW' => array(
                'name' => 'English (Botswana)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'en-IO' => array(
                'name' => 'English (British Indian Ocean Territory)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-VG' => array(
                'name' => 'English (British Virgin Islands)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'en-BI' => array(
                'name' => 'English (Burundi)',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'en-CM' => array(
                'name' => 'English (Cameroon)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-CA' => array(
                'name' => 'English (Canada)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'Y-m-d g:i A'
            ),
            'en-029' => array(
                'name' => 'English (Caribbean)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-KY' => array(
                'name' => 'English (Cayman Islands)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'en-CX' => array(
                'name' => 'English (Christmas Island)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-CC' => array(
                'name' => 'English (Cocos (Keeling) Islands)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-CK' => array(
                'name' => 'English (Cook Islands)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-CY' => array(
                'name' => 'English (Cyprus)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'en-DK' => array(
                'name' => 'English (Denmark)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H.i',
                'dateTimeFormat' => 'd/m/Y H.i'
            ),
            'en-DM' => array(
                'name' => 'English (Dominica)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'en-ER' => array(
                'name' => 'English (Eritrea)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'en-150' => array(
                'name' => 'English (Europe)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-FK' => array(
                'name' => 'English (Falkland Islands)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-FJ' => array(
                'name' => 'English (Fiji)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'en-FI' => array(
                'name' => 'English (Finland)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'G.i',
                'dateTimeFormat' => 'd/m/Y G.i'
            ),
            'en-GM' => array(
                'name' => 'English (Gambia)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'en-DE' => array(
                'name' => 'English (Germany)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-GH' => array(
                'name' => 'English (Ghana)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'en-GI' => array(
                'name' => 'English (Gibraltar)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-GD' => array(
                'name' => 'English (Grenada)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'en-GU' => array(
                'name' => 'English (Guam)',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'en-GY' => array(
                'name' => 'English (Guyana)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'en-GG' => array(
                'name' => 'English (Guernsey)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-HK' => array(
                'name' => 'English (Hong Kong)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'en-IN' => array(
                'name' => 'English (India)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'en-ID' => array(
                'name' => 'English (Indonesia)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd/m/Y G:i'
            ),
            'en-IE' => array(
                'name' => 'English (Ireland)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-IM' => array(
                'name' => 'English (Isle of Man)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-IL' => array(
                'name' => 'English (Israel)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd/m/Y G:i'
            ),
            'en-JM' => array(
                'name' => 'English (Jamaica)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'en-JE' => array(
                'name' => 'English (Jersey)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-KE' => array(
                'name' => 'English (Kenya)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-KI' => array(
                'name' => 'English (Kiribati)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'en-LS' => array(
                'name' => 'English (Lesotho)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'en-LR' => array(
                'name' => 'English (Liberia)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'en-MO' => array(
                'name' => 'English (Macao SAR)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'en-MG' => array(
                'name' => 'English (Madagascar)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-MY' => array(
                'name' => 'English (Malaysia)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'en-MW' => array(
                'name' => 'English (Malawi)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'en-MT' => array(
                'name' => 'English (Malta)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-MH' => array(
                'name' => 'English (Marshall Islands)',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'en-MU' => array(
                'name' => 'English (Mauritius)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-FM' => array(
                'name' => 'English (Micronesia)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'en-MS' => array(
                'name' => 'English (Montserrat)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-NA' => array(
                'name' => 'English (Namibia)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'en-NR' => array(
                'name' => 'English (Nauru)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-NL' => array(
                'name' => 'English (Netherlands)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-NZ' => array(
                'name' => 'English (New Zealand)',
                'dateFormat' => 'j/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/m/Y g:i A'
            ),
            'en-NG' => array(
                'name' => 'English (Nigeria)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'en-NU' => array(
                'name' => 'English (Niue)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-NF' => array(
                'name' => 'English (Norfolk Island)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-MP' => array(
                'name' => 'English (Northern Mariana Islands)',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'en-PG' => array(
                'name' => 'English (Papua New Guinea)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'en-PK' => array(
                'name' => 'English (Pakistan)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'en-PW' => array(
                'name' => 'English (Palau)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'en-PN' => array(
                'name' => 'English (Pitcairn Islands)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-PR' => array(
                'name' => 'English (Puerto Rico)',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'en-PH' => array(
                'name' => 'English (Republic of the Philippines)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'en-RW' => array(
                'name' => 'English (Rwanda)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-WS' => array(
                'name' => 'English (Samoa)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'en-SC' => array(
                'name' => 'English (Seychelles)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-SL' => array(
                'name' => 'English (Sierra Leone)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'en-SG' => array(
                'name' => 'English (Singapore)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'en-SX' => array(
                'name' => 'English (Sint Maarten)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-SI' => array(
                'name' => 'English (Slovenia)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-SB' => array(
                'name' => 'English (Solomon Islands)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'en-ZA' => array(
                'name' => 'English (South Africa)',
                'dateFormat' => 'Y/m/d',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'Y/m/d g:i A'
            ),
            'en-SS' => array(
                'name' => 'English (South Sudan)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'en-SH' => array(
                'name' => 'English (St. Helena)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-KN' => array(
                'name' => 'English (St. Kitts & Nevis)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'en-LC' => array(
                'name' => 'English (St. Lucia)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'en-VC' => array(
                'name' => 'English (St. Vincent & Grenadines)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'en-SD' => array(
                'name' => 'English (Sudan)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'en-SZ' => array(
                'name' => 'English (Swaziland)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'en-SE' => array(
                'name' => 'English (Sweden)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'en-CH' => array(
                'name' => 'English (Switzerland)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-TZ' => array(
                'name' => 'English (Tanzania)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-TK' => array(
                'name' => 'English (Tokelau)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-TO' => array(
                'name' => 'English (Tonga)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'en-TT' => array(
                'name' => 'English (Trinidad and Tobago)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'en-TC' => array(
                'name' => 'English (Turks & Caicos Islands)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'en-TV' => array(
                'name' => 'English (Tuvala)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-UG' => array(
                'name' => 'English (Uganda)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-GB' => array(
                'name' => 'English (United Kingdom)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-US' => array(
                'name' => 'English (United States)',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'en-UM' => array(
                'name' => 'English (U.S. Outlying Islands)',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'en-VI' => array(
                'name' => 'English (U.S. Virgin Islands)',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'en-VU' => array(
                'name' => 'English (Vanuatu)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'en-001' => array(
                'name' => 'English (World)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'en-ZM' => array(
                'name' => 'English (Zambia)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'en-ZW' => array(
                'name' => 'English (Zimbabwe)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'eo' => array(
                'name' => 'Esperanto',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'eo-001' => array(
                'name' => 'Esperanto (World)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'et' => array(
                'name' => 'Estonian',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd.m.Y G:i'
            ),
            'et-EE' => array(
                'name' => 'Estonian (Estonia)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd.m.Y G:i'
            ),
            'ee' => array(
                'name' => 'Ewe',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'A \g\a g:i',
                'dateTimeFormat' => 'n/j/Y A \g\a g:i'
            ),
            'ewo' => array(
                'name' => 'Ewondo',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'ewo-CM' => array(
                'name' => 'Ewondo (Cameroon)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'ee-GH' => array(
                'name' => 'Ewe (Ghana)',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'A \g\a g:i',
                'dateTimeFormat' => 'n/j/Y A \g\a g:i'
            ),
            'ee-TG' => array(
                'name' => 'Ewe (Togo)',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'n/j/Y H:i'
            ),
            'fo' => array(
                'name' => 'Faroese',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'fo-DK' => array(
                'name' => 'Faroese (Denmark)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'fo-FO' => array(
                'name' => 'Faroese (Faroe Islands)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'fil' => array(
                'name' => 'Filipino',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'fil-PH' => array(
                'name' => 'Filipino (Philippines)',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'fi' => array(
                'name' => 'Finnish',
                'dateFormat' => 'j.n.Y',
                'timeFormat' => 'G.i',
                'dateTimeFormat' => 'j.n.Y G.i'
            ),
            'fi-FI' => array(
                'name' => 'Finnish (Finland)',
                'dateFormat' => 'j.n.Y',
                'timeFormat' => 'G.i',
                'dateTimeFormat' => 'j.n.Y G.i'
            ),
            'fr' => array(
                'name' => 'French',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-DZ' => array(
                'name' => 'French (Algeria)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'fr-BE' => array(
                'name' => 'French (Belgium)',
                'dateFormat' => 'd-m-y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-y H:i'
            ),
            'fr-BJ' => array(
                'name' => 'French (Benin)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-BI' => array(
                'name' => 'French (Burundi)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-BF' => array(
                'name' => 'French (Burkina Faso)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-CM' => array(
                'name' => 'French (Cameroon)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-CA' => array(
                'name' => 'French (Canada)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'fr-029' => array(
                'name' => 'French (Caribbean)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd/m/Y G:i'
            ),
            'fr-CF' => array(
                'name' => 'French (Central African Republic)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-TD' => array(
                'name' => 'French (Chad)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'fr-KM' => array(
                'name' => 'French (Comoros)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-CG' => array(
                'name' => 'French (Congo - Brazzaville)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-CD' => array(
                'name' => 'French (Congo - Kinshasa)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-CI' => array(
                'name' => 'French (Côte d’Ivoire)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-DJ' => array(
                'name' => 'French (Djibouti)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'fr-GQ' => array(
                'name' => 'French (Equatorial Guinea)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-FR' => array(
                'name' => 'French (France)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-GF' => array(
                'name' => 'French (French Guiana)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-PF' => array(
                'name' => 'French (French Polynesia)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-GA' => array(
                'name' => 'French (Gabon)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-GP' => array(
                'name' => 'French (Guadeloupe)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-GN' => array(
                'name' => 'French (Guinea)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-HT' => array(
                'name' => 'French (Haiti)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-LU' => array(
                'name' => 'French (Luxembourg)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-MG' => array(
                'name' => 'French (Madagascar)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-ML' => array(
                'name' => 'French (Mali)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-MQ' => array(
                'name' => 'French (Martinique)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-MR' => array(
                'name' => 'French (Mauritania)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'fr-MU' => array(
                'name' => 'French (Mauritius)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-YT' => array(
                'name' => 'French (Mayotte)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-MA' => array(
                'name' => 'French (Morocco)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-NC' => array(
                'name' => 'French (New Caledonia)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-NE' => array(
                'name' => 'French (Niger)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-MC' => array(
                'name' => 'French (Principality of Monaco)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-RE' => array(
                'name' => 'French (Réunion)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-RW' => array(
                'name' => 'French (Rwanda)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-SN' => array(
                'name' => 'French (Senegal)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-SC' => array(
                'name' => 'French (Seychelles)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-BL' => array(
                'name' => 'French (St. Barthélemy)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-MF' => array(
                'name' => 'French (St. Martin)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-PM' => array(
                'name' => 'French (St. Pierre & Miquelon)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-CH' => array(
                'name' => 'French (Switzerland)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'fr-SY' => array(
                'name' => 'French (Syria)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'fr-TG' => array(
                'name' => 'French (Togo)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-TN' => array(
                'name' => 'French (Tunisia)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'fr-VU' => array(
                'name' => 'French (Vanuatu)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'fr-WF' => array(
                'name' => 'French (Wallis & Futuna)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fy' => array(
                'name' => 'Frisian',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'fy-NL' => array(
                'name' => 'Frisian (Netherlands)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'fur' => array(
                'name' => 'Friulian',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fur-IT' => array(
                'name' => 'Friulian (Italy)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'ff' => array(
                'name' => 'Fulah',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'ff-CM' => array(
                'name' => 'Fulah (Cameroon)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'ff-GN' => array(
                'name' => 'Fulah (Guinea)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'ff-Latn' => array(
                'name' => 'Fulah (Latin)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'ff-Latn-SN' => array(
                'name' => 'Fulah (Latin, Senegal)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'ff-MR' => array(
                'name' => 'Fulah (Mauritania)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'ff-NG' => array(
                'name' => 'Fulah (Nigeria)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j/n/Y G:i'
            ),
            'gl' => array(
                'name' => 'Galician',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'gl-ES' => array(
                'name' => 'Galician (Spain)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'lg' => array(
                'name' => 'Ganda',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'lg-UG' => array(
                'name' => 'Ganda (Uganda)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'ka' => array(
                'name' => 'Georgian',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'ka-GE' => array(
                'name' => 'Georgian (Georgia)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'de' => array(
                'name' => 'German',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'de-AT' => array(
                'name' => 'German (Austria)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'de-BE' => array(
                'name' => 'German (Belgium)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'de-DE' => array(
                'name' => 'German (Germany)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'de-LI' => array(
                'name' => 'German (Liechtenstein)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'de-LU' => array(
                'name' => 'German (Luxembourg)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'de-CH' => array(
                'name' => 'German (Switzerland)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'el' => array(
                'name' => 'Greek',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i a',
                'dateTimeFormat' => 'j/n/Y g:i a'
            ),
            'el-CY' => array(
                'name' => 'Greek (Cyprus)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i a',
                'dateTimeFormat' => 'j/n/Y g:i a'
            ),
            'el-GR' => array(
                'name' => 'Greek (Greece)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i a',
                'dateTimeFormat' => 'j/n/Y g:i a'
            ),
            'kl' => array(
                'name' => 'Greenlandic',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'kl-GL' => array(
                'name' => 'Greenlandic (Greenland)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'gn' => array(
                'name' => 'Guarani',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'gn-PY' => array(
                'name' => 'Guarani (Paraguay)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'gu' => array(
                'name' => 'Gujarati',
                'dateFormat' => 'd-m-y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-y H:i'
            ),
            'gu-IN' => array(
                'name' => 'Gujarati (India)',
                'dateFormat' => 'd-m-y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-y H:i'
            ),
            'guz' => array(
                'name' => 'Gusii',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'guz-KE' => array(
                'name' => 'Gusii (Kenya)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'ha' => array(
                'name' => 'Hausa',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'ha-Latn' => array(
                'name' => 'Hausa (Latin)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'ha-Latn-GH' => array(
                'name' => 'Hausa (Latin, Ghana)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'ha-Latn-NE' => array(
                'name' => 'Hausa (Latin, Niger)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'ha-Latn-NG' => array(
                'name' => 'Hausa (Latin, Nigeria)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'haw' => array(
                'name' => 'Hawaiian',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'haw-US' => array(
                'name' => 'Hawaiian (United States)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'he' => array(
                'name' => 'Hebrew',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'he-IL' => array(
                'name' => 'Hebrew (Israel)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'hi' => array(
                'name' => 'Hindi',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'hi-IN' => array(
                'name' => 'Hindi (India)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'hu' => array(
                'name' => 'Hungarian',
                'dateFormat' => 'Y. m. d.',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'Y. m. d. G:i'
            ),
            'hu-HU' => array(
                'name' => 'Hungarian (Hungary)',
                'dateFormat' => 'Y. m. d.',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'Y. m. d. G:i'
            ),
            'ibb' => array(
                'name' => 'Ibibio',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:iA',
                'dateTimeFormat' => 'j/n/Y g:iA'
            ),
            'ibb-NG' => array(
                'name' => 'Ibibio (Nigeria)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:iA',
                'dateTimeFormat' => 'j/n/Y g:iA'
            ),
            'is' => array(
                'name' => 'Icelandic',
                'dateFormat' => 'j.n.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j.n.Y H:i'
            ),
            'is-IS' => array(
                'name' => 'Icelandic (Iceland)',
                'dateFormat' => 'j.n.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j.n.Y H:i'
            ),
            'ig' => array(
                'name' => 'Igbo',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'ig-NG' => array(
                'name' => 'Igbo (Nigeria)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'id' => array(
                'name' => 'Indonesian',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H.i',
                'dateTimeFormat' => 'd/m/Y H.i'
            ),
            'id-ID' => array(
                'name' => 'Indonesian (Indonesia)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H.i',
                'dateTimeFormat' => 'd/m/Y H.i'
            ),
            'ia' => array(
                'name' => 'Interlingua',
                'dateFormat' => 'Y/m/d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y/m/d H:i'
            ),
            'ia-001' => array(
                'name' => 'Interlingua (World)',
                'dateFormat' => 'Y/m/d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y/m/d H:i'
            ),
            'ia-FR' => array(
                'name' => 'Interlingua (France)',
                'dateFormat' => 'Y/m/d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y/m/d H:i'
            ),
            'iu' => array(
                'name' => 'Inuktitut',
                'dateFormat' => 'j/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/m/Y g:i A'
            ),
            'iu-Latn' => array(
                'name' => 'Inuktitut (Latin)',
                'dateFormat' => 'j/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/m/Y g:i A'
            ),
            'iu-Latn-CA' => array(
                'name' => 'Inuktitut (Latin, Canada)',
                'dateFormat' => 'j/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/m/Y g:i A'
            ),
            'iu-Cans' => array(
                'name' => 'Inuktitut (Syllabics)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'iu-Cans-CA' => array(
                'name' => 'Inuktitut (Syllabics, Canada)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'ga' => array(
                'name' => 'Irish',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'ga-IE' => array(
                'name' => 'Irish (Ireland)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'it' => array(
                'name' => 'Italian',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'it-IT' => array(
                'name' => 'Italian (Italy)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'it-SM' => array(
                'name' => 'Italian (San Marino)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'it-CH' => array(
                'name' => 'Italian (Switzerland)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'ja' => array(
                'name' => 'Japanese',
                'dateFormat' => 'Y/m/d',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'Y/m/d G:i'
            ),
            'ja-JP' => array(
                'name' => 'Japanese (Japan)',
                'dateFormat' => 'Y/m/d',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'Y/m/d G:i'
            ),
            'jv' => array(
                'name' => 'Javanese',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H.i',
                'dateTimeFormat' => 'd/m/Y H.i'
            ),
            'jv-Java' => array(
                'name' => 'Javanese (Javanese)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H.i',
                'dateTimeFormat' => 'd/m/Y H.i'
            ),
            'jv-Java-ID' => array(
                'name' => 'Javanese (Javanese, Indonesia)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H.i',
                'dateTimeFormat' => 'd/m/Y H.i'
            ),
            'jv-Latn' => array(
                'name' => 'Javanese (Latin)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H.i',
                'dateTimeFormat' => 'd/m/Y H.i'
            ),
            'jv-Latn-ID' => array(
                'name' => 'Javanese (Latin, Indonesia)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H.i',
                'dateTimeFormat' => 'd/m/Y H.i'
            ),
            'dyo' => array(
                'name' => 'Jola-Fonyi',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'dyo-SN' => array(
                'name' => 'Jola-Fonyi (Senegal)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'kea' => array(
                'name' => 'Kabuverdianu',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'kea-CV' => array(
                'name' => 'Kabuverdianu (Cabo Verde)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'kab' => array(
                'name' => 'Kabyle',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'kab-DZ' => array(
                'name' => 'Kabyle (Algeria)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'kkj' => array(
                'name' => 'Kako',
                'dateFormat' => 'd/m Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m Y H:i'
            ),
            'kkj-CM' => array(
                'name' => 'Kako (Cameroon)',
                'dateFormat' => 'd/m Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m Y H:i'
            ),
            'kln' => array(
                'name' => 'Kalenjin',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'kln-KE' => array(
                'name' => 'Kalenjin (Kenya)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'kam' => array(
                'name' => 'Kamba',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'kam-KE' => array(
                'name' => 'Kamba (Kenya)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'kn' => array(
                'name' => 'Kannada',
                'dateFormat' => 'd-m-y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-y H:i'
            ),
            'kn-IN' => array(
                'name' => 'Kannada (India)',
                'dateFormat' => 'd-m-y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-y H:i'
            ),
            'kr' => array(
                'name' => 'Kanuri',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'kr-NG' => array(
                'name' => 'Kanuri (Nigeria)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'ks' => array(
                'name' => 'Kashmiri',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'ks-Deva' => array(
                'name' => 'Kashmiri (Devanagari)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd-m-Y G:i'
            ),
            'ks-Deva-IN' => array(
                'name' => 'Kashmiri (Devanagari, India)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd-m-Y G:i'
            ),
            'ks-Arab' => array(
                'name' => 'Kashmiri (Perso-Arabic)',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'ks-Arab-IN' => array(
                'name' => 'Kashmiri (Perso-Arabic, India)',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'kk' => array(
                'name' => 'Kazakh',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'kk-KZ' => array(
                'name' => 'Kazakh (Kazakhstan)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'km' => array(
                'name' => 'Khmer',
                'dateFormat' => 'd/m/y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd/m/y G:i'
            ),
            'km-KH' => array(
                'name' => 'Khmer (Cambodia)',
                'dateFormat' => 'd/m/y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd/m/y G:i'
            ),
            'quc' => array(
                'name' => 'K\'iche\'',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i a',
                'dateTimeFormat' => 'd/m/Y g:i a'
            ),
            'quc-Latn' => array(
                'name' => 'K\'iche\' (Latin)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i a',
                'dateTimeFormat' => 'd/m/Y g:i a'
            ),
            'quc-Latn-GT' => array(
                'name' => 'K\'iche\' (Latin, Guatemala)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i a',
                'dateTimeFormat' => 'd/m/Y g:i a'
            ),
            'qut' => array(
                'name' => 'K\'iche\' (qut)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i a',
                'dateTimeFormat' => 'd/m/Y g:i a'
            ),
            'qut-GT' => array(
                'name' => 'K\'iche\' (qut, Guatemala)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i a',
                'dateTimeFormat' => 'd/m/Y g:i a'
            ),
            'ki' => array(
                'name' => 'Kikuyu',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'ki-KE' => array(
                'name' => 'Kikuyu (Kenya)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'rw' => array(
                'name' => 'Kinyarwanda',
                'dateFormat' => 'Y/m/d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y/m/d H:i'
            ),
            'rw-RW' => array(
                'name' => 'Kinyarwanda (Rwanda)',
                'dateFormat' => 'Y/m/d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y/m/d H:i'
            ),
            'sw' => array(
                'name' => 'Kiswahili',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'sw-CD' => array(
                'name' => 'Kiswahili (Congo DRC)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'sw-KE' => array(
                'name' => 'Kiswahili (Kenya)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'sw-TZ' => array(
                'name' => 'Kiswahili (Tanzania)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'sw-UG' => array(
                'name' => 'Kiswahili (Uganda)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'kok' => array(
                'name' => 'Konkani',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'kok-IN' => array(
                'name' => 'Konkani (India)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'ko' => array(
                'name' => 'Korean',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'A g:i',
                'dateTimeFormat' => 'Y-m-d A g:i'
            ),
            'ko-KP' => array(
                'name' => 'Korean (North Korea)',
                'dateFormat' => 'Y. n. j.',
                'timeFormat' => 'A g:i',
                'dateTimeFormat' => 'Y. n. j. A g:i'
            ),
            'ko-KR' => array(
                'name' => 'Korean (Korea)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'A g:i',
                'dateTimeFormat' => 'Y-m-d A g:i'
            ),
            'khq' => array(
                'name' => 'Koyra Chiini',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'khq-ML' => array(
                'name' => 'Koyra Chiini (Mali)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'ses' => array(
                'name' => 'Koyraboro Senni',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'ses-ML' => array(
                'name' => 'Koyraboro Senni (Mali)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'ku-Arab-IR' => array(
                'name' => 'Kurdish (Perso-Arabic, Iran)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'nmg' => array(
                'name' => 'Kwasio',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'nmg-CM' => array(
                'name' => 'Kwasio (Cameroon)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'ky' => array(
                'name' => 'Kyrgyz',
                'dateFormat' => 'j-M y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j-M y H:i'
            ),
            'ky-KG' => array(
                'name' => 'Kyrgyz (Kyrgyzstan)',
                'dateFormat' => 'j-M y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j-M y H:i'
            ),
            'lkt' => array(
                'name' => 'Lakota',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'lkt-US' => array(
                'name' => 'Lakota (United States)',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'lag' => array(
                'name' => 'Langi',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'lag-TZ' => array(
                'name' => 'Langi (Tanzania)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'lo' => array(
                'name' => 'Lao',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j/n/Y G:i'
            ),
            'lo-LA' => array(
                'name' => 'Lao (Lao P.D.R.)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j/n/Y G:i'
            ),
            'la' => array(
                'name' => 'Latin',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd/m/Y G:i'
            ),
            'la-001' => array(
                'name' => 'Latin (World)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd/m/Y G:i'
            ),
            'lv' => array(
                'name' => 'Latvian',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'lv-LV' => array(
                'name' => 'Latvian (Latvia)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'ln' => array(
                'name' => 'Lingala',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'ln-AO' => array(
                'name' => 'Lingala (Angola)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'ln-CD' => array(
                'name' => 'Lingala (Congo DRC)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'ln-CF' => array(
                'name' => 'Lingala (Central African Republic)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'ln-CG' => array(
                'name' => 'Lingala (Congo)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'lt' => array(
                'name' => 'Lithuanian',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'lt-LT' => array(
                'name' => 'Lithuanian (Lithuania)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'dsb' => array(
                'name' => 'Lower Sorbian',
                'dateFormat' => 'j. n. Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j. n. Y H:i'
            ),
            'dsb-DE' => array(
                'name' => 'Lower Sorbian (Germany)',
                'dateFormat' => 'j. n. Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j. n. Y H:i'
            ),
            'lu' => array(
                'name' => 'Luba-Katanga',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'lu-CD' => array(
                'name' => 'Luba-Katanga (Congo DRC)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'luo' => array(
                'name' => 'Luo',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'luo-KE' => array(
                'name' => 'Luo (Kenya)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'lb' => array(
                'name' => 'Luxembourgish',
                'dateFormat' => 'd.m.y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.y H:i'
            ),
            'lb-LU' => array(
                'name' => 'Luxembourgish (Luxembourg)',
                'dateFormat' => 'd.m.y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.y H:i'
            ),
            'luy' => array(
                'name' => 'Luyia',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'luy-KE' => array(
                'name' => 'Luyia (Kenya)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'mk' => array(
                'name' => 'Macedonian',
                'dateFormat' => 'd.n.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.n.Y H:i'
            ),
            'mk-MK' => array(
                'name' => 'Macedonian (Former Yugoslav Republic of Macedonia)',
                'dateFormat' => 'd.n.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.n.Y H:i'
            ),
            'jmc' => array(
                'name' => 'Machame',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'jmc-TZ' => array(
                'name' => 'Machame (Tanzania)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'mgh' => array(
                'name' => 'Makhuwa-Meetto',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'mgh-MZ' => array(
                'name' => 'Makhuwa-Meetto (Mozambique)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'kde' => array(
                'name' => 'Makonde',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'kde-TZ' => array(
                'name' => 'Makonde (Tanzania)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'mg' => array(
                'name' => 'Malagasy',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'mg-MG' => array(
                'name' => 'Malagasy (Madagascar)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'ms' => array(
                'name' => 'Malay',
                'dateFormat' => 'j/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/m/Y g:i A'
            ),
            'ms-BN' => array(
                'name' => 'Malay (Brunei)',
                'dateFormat' => 'j/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/m/Y g:i A'
            ),
            'ms-MY' => array(
                'name' => 'Malay (Malaysia)',
                'dateFormat' => 'j/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/m/Y g:i A'
            ),
            'ms-SG' => array(
                'name' => 'Malay (Singapore)',
                'dateFormat' => 'j/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/m/Y g:i A'
            ),
            'ml' => array(
                'name' => 'Malayalam',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'ml-IN' => array(
                'name' => 'Malayalam (India)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'mt' => array(
                'name' => 'Maltese',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'mt-MT' => array(
                'name' => 'Maltese (Malta)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'mni' => array(
                'name' => 'Manipuri',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'mni-IN' => array(
                'name' => 'Manipuri (India)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'gv' => array(
                'name' => 'Manx',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'gv-IM' => array(
                'name' => 'Manx (Isle of Man)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'mi' => array(
                'name' => 'Maori',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i a',
                'dateTimeFormat' => 'd/m/Y g:i a'
            ),
            'mi-NZ' => array(
                'name' => 'Maori (New Zealand)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i a',
                'dateTimeFormat' => 'd/m/Y g:i a'
            ),
            'arn' => array(
                'name' => 'Mapudungun',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd-m-Y G:i'
            ),
            'arn-CL' => array(
                'name' => 'Mapudungun (Chile)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd-m-Y G:i'
            ),
            'mr' => array(
                'name' => 'Marathi',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'mr-IN' => array(
                'name' => 'Marathi (India)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'mas' => array(
                'name' => 'Masai',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'mas-KE' => array(
                'name' => 'Masai (Kenya)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'mas-TZ' => array(
                'name' => 'Masai (Tanzania)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'mzn' => array(
                'name' => 'Mazanderani',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'mzn-IR' => array(
                'name' => 'Mazanderani (Iran)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'mer' => array(
                'name' => 'Meru',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'mer-KE' => array(
                'name' => 'Meru (Kenya)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'mgo' => array(
                'name' => 'Metaʼ',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'mgo-CM' => array(
                'name' => 'Metaʼ (Cameroon)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'moh' => array(
                'name' => 'Mohawk',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'moh-CA' => array(
                'name' => 'Mohawk (Canada)',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'mn' => array(
                'name' => 'Mongolian',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'mn-Cyrl' => array(
                'name' => 'Mongolian (Cyrillic)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'mn-MN' => array(
                'name' => 'Mongolian (Cyrillic, Mongolia)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'mn-Mong' => array(
                'name' => 'Mongolian (Traditional Mongolian)',
                'dateFormat' => 'Y/n/j',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'Y/n/j G:i'
            ),
            'mn-Mong-MN' => array(
                'name' => 'Mongolian (Traditional Mongolian, Mongolia)',
                'dateFormat' => 'Y/n/j',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'Y/n/j G:i'
            ),
            'mn-Mong-CN' => array(
                'name' => 'Mongolian (Traditional Mongolian, China)',
                'dateFormat' => 'Y/n/j',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'Y/n/j G:i'
            ),
            'mfe' => array(
                'name' => 'Morisyen',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'mfe-MU' => array(
                'name' => 'Morisyen (Mauritius)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'mua' => array(
                'name' => 'Mundang',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'mua-CM' => array(
                'name' => 'Mundang (Cameroon)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'naq' => array(
                'name' => 'Nama',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'naq-NA' => array(
                'name' => 'Nama (Namibia)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'nqo' => array(
                'name' => 'N\'ko',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'A h:i',
                'dateTimeFormat' => 'd/m/Y A h:i'
            ),
            'nqo-GN' => array(
                'name' => 'N\'ko (Guinea)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'A h:i',
                'dateTimeFormat' => 'd/m/Y A h:i'
            ),
            'ne' => array(
                'name' => 'Nepali',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'ne-IN' => array(
                'name' => 'Nepali (India)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'Y-m-d g:i A'
            ),
            'ne-NP' => array(
                'name' => 'Nepali (Nepal)',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'nnh' => array(
                'name' => 'Ngiemboon',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'nnh-CM' => array(
                'name' => 'Ngiemboon (Cameroon)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'jgo' => array(
                'name' => 'Ngomba',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'jgo-CM' => array(
                'name' => 'Ngomba (Cameroon)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'nd' => array(
                'name' => 'North Ndebele',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'nd-ZW' => array(
                'name' => 'North Ndebele (Zimbabwe)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'lrc' => array(
                'name' => 'Northern Luri',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'lrc-IR' => array(
                'name' => 'Northern Luri (Iran)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'lrc-IQ' => array(
                'name' => 'Northern Luri (Iraq)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'Y-m-d g:i A'
            ),
            'no' => array(
                'name' => 'Norwegian',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H.i',
                'dateTimeFormat' => 'd.m.Y H.i'
            ),
            'nb' => array(
                'name' => 'Norwegian (Bokmål)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H.i',
                'dateTimeFormat' => 'd.m.Y H.i'
            ),
            'nb-NO' => array(
                'name' => 'Norwegian (Bokmål, Norway)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H.i',
                'dateTimeFormat' => 'd.m.Y H.i'
            ),
            'nb-SJ' => array(
                'name' => 'Norwegian (Bokmål, Svalbard and Jan Mayen)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H.i',
                'dateTimeFormat' => 'd.m.Y H.i'
            ),
            'nn' => array(
                'name' => 'Norwegian (Nynorsk)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'nn-NO' => array(
                'name' => 'Norwegian (Nynorsk, Norway)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'nus' => array(
                'name' => 'Nuer',
                'dateFormat' => 'j/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/m/Y g:i A'
            ),
            'nus-SS' => array(
                'name' => 'Nuer (South Sudan)',
                'dateFormat' => 'j/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/m/Y g:i A'
            ),
            'nyn' => array(
                'name' => 'Nyankole',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'nyn-UG' => array(
                'name' => 'Nyankole (Uganda)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'oc' => array(
                'name' => 'Occitan',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H \h i',
                'dateTimeFormat' => 'd/m/Y H \h i'
            ),
            'oc-FR' => array(
                'name' => 'Occitan (France)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H \h i',
                'dateTimeFormat' => 'd/m/Y H \h i'
            ),
            'or' => array(
                'name' => 'Odia',
                'dateFormat' => 'd-m-y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-y H:i'
            ),
            'or-IN' => array(
                'name' => 'Odia (India)',
                'dateFormat' => 'd-m-y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-y H:i'
            ),
            'om' => array(
                'name' => 'Oromo',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'om-ET' => array(
                'name' => 'Oromo (Ethiopia)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'om-KE' => array(
                'name' => 'Oromo (Kenya)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'os' => array(
                'name' => 'Ossetic',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'os-GE' => array(
                'name' => 'Ossetic (Georgia)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'os-RU' => array(
                'name' => 'Ossetic (Russia)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'pap' => array(
                'name' => 'Papiamento',
                'dateFormat' => 'j-n-Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j-n-Y G:i'
            ),
            'pap-029' => array(
                'name' => 'Papiamento (Caribbean)',
                'dateFormat' => 'j-n-Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j-n-Y G:i'
            ),
            'ps' => array(
                'name' => 'Pashto',
                'dateFormat' => 'Y/n/j',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'Y/n/j G:i'
            ),
            'ps-AF' => array(
                'name' => 'Pashto (Afghanistan)',
                'dateFormat' => 'Y/n/j',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'Y/n/j G:i'
            ),
            'fa' => array(
                'name' => 'Persian',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/Y h:i A'
            ),
            'fa-IR' => array(
                'name' => 'Persian (Iran)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/Y h:i A'
            ),
            'pl' => array(
                'name' => 'Polish',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'pl-PL' => array(
                'name' => 'Polish (Poland)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'pt' => array(
                'name' => 'Portuguese',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'pt-AO' => array(
                'name' => 'Portuguese (Angola)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'pt-BR' => array(
                'name' => 'Portuguese (Brazil)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'pt-CV' => array(
                'name' => 'Portuguese (Cabo Verde)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'pt-GW' => array(
                'name' => 'Portuguese (Guinea-Bissau)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'pt-MO' => array(
                'name' => 'Portuguese (Macao SAR)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i a',
                'dateTimeFormat' => 'd/m/Y g:i a'
            ),
            'pt-MZ' => array(
                'name' => 'Portuguese (Mozambique)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'pt-PT' => array(
                'name' => 'Portuguese (Portugal)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'pt-ST' => array(
                'name' => 'Portuguese (São Tomé and Príncipe)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'pt-TL' => array(
                'name' => 'Portuguese (Timor-Leste)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'prg' => array(
                'name' => 'Prussian',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'prg-001' => array(
                'name' => 'Prussian (World)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'pa' => array(
                'name' => 'Punjabi',
                'dateFormat' => 'd-m-y',
                'timeFormat' => 'A h:i',
                'dateTimeFormat' => 'd-m-y A h:i'
            ),
            'pa-Arab' => array(
                'name' => 'Punjabi (Arabic)',
                'dateFormat' => 'd-m-y',
                'timeFormat' => 'g.i A',
                'dateTimeFormat' => 'd-m-y g.i A'
            ),
            'pa-Arab-PK' => array(
                'name' => 'Punjabi (Arabic, Islamic Republic of Pakistan)',
                'dateFormat' => 'd-m-y',
                'timeFormat' => 'g.i A',
                'dateTimeFormat' => 'd-m-y g.i A'
            ),
            'pa-IN' => array(
                'name' => 'Punjabi (India)',
                'dateFormat' => 'd-m-y',
                'timeFormat' => 'A h:i',
                'dateTimeFormat' => 'd-m-y A h:i'
            ),
            'quz' => array(
                'name' => 'Quechua',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i a',
                'dateTimeFormat' => 'd/m/Y h:i a'
            ),
            'quz-BO' => array(
                'name' => 'Quechua (Bolivia)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i a',
                'dateTimeFormat' => 'd/m/Y h:i a'
            ),
            'quz-EC' => array(
                'name' => 'Quechua (Ecuador)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd/m/Y G:i'
            ),
            'quz-PE' => array(
                'name' => 'Quechua (Peru)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i a',
                'dateTimeFormat' => 'd/m/Y h:i a'
            ),
            'ro' => array(
                'name' => 'Romanian',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'ro-MD' => array(
                'name' => 'Romanian (Moldova)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'ro-RO' => array(
                'name' => 'Romanian (Romania)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'rm' => array(
                'name' => 'Romansh',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'rm-CH' => array(
                'name' => 'Romansh (Switzerland)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'rof' => array(
                'name' => 'Rombo',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'rof-TZ' => array(
                'name' => 'Rombo (Tanzania)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'rn' => array(
                'name' => 'Rundi',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'rn-BI' => array(
                'name' => 'Rundi (Burundi)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'ru' => array(
                'name' => 'Russian',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd.m.Y G:i'
            ),
            'ru-BY' => array(
                'name' => 'Russian (Belarus)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd.m.Y G:i'
            ),
            'ru-KG' => array(
                'name' => 'Russian (Kyrgyzstan)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd.m.Y G:i'
            ),
            'ru-KZ' => array(
                'name' => 'Russian (Kazakhstan)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd.m.Y G:i'
            ),
            'ru-MD' => array(
                'name' => 'Russian (Moldova)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd.m.Y G:i'
            ),
            'ru-RU' => array(
                'name' => 'Russian (Russia)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd.m.Y G:i'
            ),
            'ru-UA' => array(
                'name' => 'Russian (Ukraine)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'rwk' => array(
                'name' => 'Rwa',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'rwk-TZ' => array(
                'name' => 'Rwa (Tanzania)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'ssy' => array(
                'name' => 'Saho',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'ssy-ER' => array(
                'name' => 'Saho (Eritrea)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'sah' => array(
                'name' => 'Sakha',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd.m.Y G:i'
            ),
            'sah-RU' => array(
                'name' => 'Sakha (Russia)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd.m.Y G:i'
            ),
            'saq' => array(
                'name' => 'Samburu',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'saq-KE' => array(
                'name' => 'Samburu (Kenya)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'smn' => array(
                'name' => 'Sami, Inari',
                'dateFormat' => 'j.n.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y G:i'
            ),
            'smn-FI' => array(
                'name' => 'Sami, Inari (Finland)',
                'dateFormat' => 'j.n.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y G:i'
            ),
            'smj' => array(
                'name' => 'Sami, Lule',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'smj-NO' => array(
                'name' => 'Sami, Lule (Norway)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'smj-SE' => array(
                'name' => 'Sami, Lule (Sweden)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'se' => array(
                'name' => 'Sami, Northern',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'se-FI' => array(
                'name' => 'Sami, Northern (Finland)',
                'dateFormat' => 'j.n.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y G:i'
            ),
            'se-NO' => array(
                'name' => 'Sami, Northern (Norway)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'se-SE' => array(
                'name' => 'Sami, Northern (Sweden)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'sms' => array(
                'name' => 'Sami, Skolt',
                'dateFormat' => 'j.n.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y G:i'
            ),
            'sms-FI' => array(
                'name' => 'Sami, Skolt (Finland)',
                'dateFormat' => 'j.n.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y G:i'
            ),
            'sma' => array(
                'name' => 'Sami, Southern',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'sma-NO' => array(
                'name' => 'Sami, Southern (Norway)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'sma-SE' => array(
                'name' => 'Sami, Southern (Sweden)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'sg' => array(
                'name' => 'Sango',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'sg-CF' => array(
                'name' => 'Sango (Central African Republic)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'sbp' => array(
                'name' => 'Sangu',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'sbp-TZ' => array(
                'name' => 'Sangu (Tanzania)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'sa' => array(
                'name' => 'Sanskrit',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'sa-IN' => array(
                'name' => 'Sanskrit (India)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'gd' => array(
                'name' => 'Scottish Gaelic',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'gd-GB' => array(
                'name' => 'Scottish Gaelic (United Kingdom)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'seh' => array(
                'name' => 'Sena',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'seh-MZ' => array(
                'name' => 'Sena (Mozambique)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'sr' => array(
                'name' => 'Serbian',
                'dateFormat' => 'j.n.Y.',
                'timeFormat' => 'H.i',
                'dateTimeFormat' => 'j.n.Y. H.i'
            ),
            'sr-Cyrl' => array(
                'name' => 'Serbian (Cyrillic)',
                'dateFormat' => 'd.m.Y.',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd.m.Y. G:i'
            ),
            'sr-Cyrl-BA' => array(
                'name' => 'Serbian (Cyrillic, Bosnia and Herzegovina)',
                'dateFormat' => 'j.n.Y.',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y. G:i'
            ),
            'sr-Cyrl-XK' => array(
                'name' => 'Serbian (Cyrillic, Kosovo)',
                'dateFormat' => 'j.n.Y.',
                'timeFormat' => 'H.i',
                'dateTimeFormat' => 'j.n.Y. H.i'
            ),
            'sr-Cyrl-ME' => array(
                'name' => 'Serbian (Cyrillic, Montenegro)',
                'dateFormat' => 'j.n.Y.',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y. G:i'
            ),
            'sr-Cyrl-CS' => array(
                'name' => 'Serbian (Cyrillic, Serbia and Montenegro (Former))',
                'dateFormat' => 'j.n.Y.',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y. G:i'
            ),
            'sr-Cyrl-RS' => array(
                'name' => 'Serbian (Cyrillic, Serbia)',
                'dateFormat' => 'd.m.Y.',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd.m.Y. G:i'
            ),
            'sr-Latn' => array(
                'name' => 'Serbian (Latin)',
                'dateFormat' => 'j.n.Y.',
                'timeFormat' => 'H.i',
                'dateTimeFormat' => 'j.n.Y. H.i'
            ),
            'sr-Latn-BA' => array(
                'name' => 'Serbian (Latin, Bosnia and Herzegovina)',
                'dateFormat' => 'j.n.Y.',
                'timeFormat' => 'H.i',
                'dateTimeFormat' => 'j.n.Y. H.i'
            ),
            'sr-Latn-XK' => array(
                'name' => 'Serbian (Latin, Kosovo)',
                'dateFormat' => 'j.n.Y.',
                'timeFormat' => 'H.i',
                'dateTimeFormat' => 'j.n.Y. H.i'
            ),
            'sr-Latn-ME' => array(
                'name' => 'Serbian (Latin, Montenegro)',
                'dateFormat' => 'j.n.Y.',
                'timeFormat' => 'H.i',
                'dateTimeFormat' => 'j.n.Y. H.i'
            ),
            'sr-Latn-CS' => array(
                'name' => 'Serbian (Latin, Serbia and Montenegro (Former))',
                'dateFormat' => 'j.n.Y.',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y. G:i'
            ),
            'sr-Latn-RS' => array(
                'name' => 'Serbian (Latin, Serbia)',
                'dateFormat' => 'j.n.Y.',
                'timeFormat' => 'H.i',
                'dateTimeFormat' => 'j.n.Y. H.i'
            ),
            'nso' => array(
                'name' => 'Sesotho sa Leboa',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'nso-ZA' => array(
                'name' => 'Sesotho sa Leboa (South Africa)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'tn' => array(
                'name' => 'Setswana',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'tn-BW' => array(
                'name' => 'Setswana (Botswana)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'tn-ZA' => array(
                'name' => 'Setswana (South Africa)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'ksb' => array(
                'name' => 'Shambala',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'ksb-TZ' => array(
                'name' => 'Shambala (Tanzania)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'sn' => array(
                'name' => 'Shona',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'sn-Latn' => array(
                'name' => 'Shona (Latin)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'sn-Latn-ZW' => array(
                'name' => 'Shona (Latin, Zimbabwe)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'sd' => array(
                'name' => 'Sindhi',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'sd-Arab' => array(
                'name' => 'Sindhi (Arabic)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'sd-Arab-PK' => array(
                'name' => 'Sindhi (Arabic, Islamic Republic of Pakistan)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'sd-Deva' => array(
                'name' => 'Sindhi (Devanagari)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd/m/Y G:i'
            ),
            'sd-Deva-IN' => array(
                'name' => 'Sindhi (Devanagari, India)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd/m/Y G:i'
            ),
            'si' => array(
                'name' => 'Sinhala',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H.i',
                'dateTimeFormat' => 'Y-m-d H.i'
            ),
            'si-LK' => array(
                'name' => 'Sinhala (Sri Lanka)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H.i',
                'dateTimeFormat' => 'Y-m-d H.i'
            ),
            'ss' => array(
                'name' => 'siSwati',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'ss-ZA' => array(
                'name' => 'siSwati (South Africa)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'ss-SZ' => array(
                'name' => 'siSwati (Swaziland)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'sk' => array(
                'name' => 'Slovak',
                'dateFormat' => 'j.n.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y G:i'
            ),
            'sk-SK' => array(
                'name' => 'Slovak (Slovakia)',
                'dateFormat' => 'j.n.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y G:i'
            ),
            'sl' => array(
                'name' => 'Slovenian',
                'dateFormat' => 'j. m. Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j. m. Y H:i'
            ),
            'sl-SI' => array(
                'name' => 'Slovenian (Slovenia)',
                'dateFormat' => 'j. m. Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j. m. Y H:i'
            ),
            'so' => array(
                'name' => 'Somali',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'so-DJ' => array(
                'name' => 'Somali (Djibouti)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'so-ET' => array(
                'name' => 'Somali (Ethiopia)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'so-KE' => array(
                'name' => 'Somali (Kenya)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'so-SO' => array(
                'name' => 'Somali (Somalia)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'xog' => array(
                'name' => 'Soga',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'xog-UG' => array(
                'name' => 'Soga (Uganda)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'st' => array(
                'name' => 'Sotho',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'st-LS' => array(
                'name' => 'Sotho (Lesotho)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'st-ZA' => array(
                'name' => 'Sotho (South Africa)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'nr' => array(
                'name' => 'South Ndebele',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'nr-ZA' => array(
                'name' => 'South Ndebele (South Africa)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'es' => array(
                'name' => 'Spanish',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd/m/Y G:i'
            ),
            'es-AR' => array(
                'name' => 'Spanish (Argentina)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'es-VE' => array(
                'name' => 'Spanish (Bolivarian Republic of Venezuela)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i a',
                'dateTimeFormat' => 'j/n/Y g:i a'
            ),
            'es-BO' => array(
                'name' => 'Spanish (Bolivia)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'es-CL' => array(
                'name' => 'Spanish (Chile)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd-m-Y G:i'
            ),
            'es-CO' => array(
                'name' => 'Spanish (Colombia)',
                'dateFormat' => 'j/m/Y',
                'timeFormat' => 'g:i a',
                'dateTimeFormat' => 'j/m/Y g:i a'
            ),
            'es-CR' => array(
                'name' => 'Spanish (Costa Rica)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'es-CU' => array(
                'name' => 'Spanish (Cuba)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'es-DO' => array(
                'name' => 'Spanish (Dominican Republic)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i a',
                'dateTimeFormat' => 'j/n/Y g:i a'
            ),
            'es-EC' => array(
                'name' => 'Spanish (Ecuador)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j/n/Y G:i'
            ),
            'es-SV' => array(
                'name' => 'Spanish (El Salvador)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'es-GQ' => array(
                'name' => 'Spanish (Equatorial Guinea)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j/n/Y G:i'
            ),
            'es-GT' => array(
                'name' => 'Spanish (Guatemala)',
                'dateFormat' => 'j/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/m/Y H:i'
            ),
            'es-HN' => array(
                'name' => 'Spanish (Honduras)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'es-419' => array(
                'name' => 'Spanish (Latin America)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'es-MX' => array(
                'name' => 'Spanish (Mexico)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i a',
                'dateTimeFormat' => 'd/m/Y h:i a'
            ),
            'es-NI' => array(
                'name' => 'Spanish (Nicaragua)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'es-PA' => array(
                'name' => 'Spanish (Panama)',
                'dateFormat' => 'm/d/Y',
                'timeFormat' => 'g:i a',
                'dateTimeFormat' => 'm/d/Y g:i a'
            ),
            'es-PY' => array(
                'name' => 'Spanish (Paraguay)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'es-PE' => array(
                'name' => 'Spanish (Peru)',
                'dateFormat' => 'j/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/m/Y H:i'
            ),
            'es-PH' => array(
                'name' => 'Spanish (Philippines)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i a',
                'dateTimeFormat' => 'j/n/Y g:i a'
            ),
            'es-PR' => array(
                'name' => 'Spanish (Puerto Rico)',
                'dateFormat' => 'm/d/Y',
                'timeFormat' => 'g:i a',
                'dateTimeFormat' => 'm/d/Y g:i a'
            ),
            'es-ES' => array(
                'name' => 'Spanish (Spain)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd/m/Y G:i'
            ),
            'es-US' => array(
                'name' => 'Spanish (United States)',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'es-UY' => array(
                'name' => 'Spanish (Uruguay)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j/n/Y G:i'
            ),
            'zgh' => array(
                'name' => 'Standard Morrocan Tamazight',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'zgh-Tfng' => array(
                'name' => 'Standard Morrocan Tamazight (Tifinagh)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'zgh-Tfng-MA' => array(
                'name' => 'Standard Morrocan Tamazight (Tifinagh, Morocco)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'sv' => array(
                'name' => 'Swedish',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'sv-AX' => array(
                'name' => 'Swedish (Åland Islands)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'sv-FI' => array(
                'name' => 'Swedish (Finland)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'sv-SE' => array(
                'name' => 'Swedish (Sweden)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'gsw' => array(
                'name' => 'Swiss German',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'gsw-FR' => array(
                'name' => 'Swiss German (France)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'gsw-LI' => array(
                'name' => 'Swiss German (Liechtenstein)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'gsw-CH' => array(
                'name' => 'Swiss German (Switzerland)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'syr' => array(
                'name' => 'Syriac',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/Y h:i A'
            ),
            'syr-SY' => array(
                'name' => 'Syriac (Syria)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/Y h:i A'
            ),
            'shi' => array(
                'name' => 'Tachelhit',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'shi-Latn' => array(
                'name' => 'Tachelhit (Latin)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'shi-Latn-MA' => array(
                'name' => 'Tachelhit (Latin, Morocco)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'shi-Tfng' => array(
                'name' => 'Tachelhit (Tifinagh)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'shi-Tfng-MA' => array(
                'name' => 'Tachelhit (Tifinagh, Morocco)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'tg' => array(
                'name' => 'Tajik',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'tg-Cyrl' => array(
                'name' => 'Tajik (Cyrillic)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'tg-Cyrl-TJ' => array(
                'name' => 'Tajik (Cyrillic, Tajikistan)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'ta' => array(
                'name' => 'Tamil',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'ta-IN' => array(
                'name' => 'Tamil (India)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'ta-MY' => array(
                'name' => 'Tamil (Malaysia)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'A g:i',
                'dateTimeFormat' => 'j/n/Y A g:i'
            ),
            'ta-SG' => array(
                'name' => 'Tamil (Singapore)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'A g:i',
                'dateTimeFormat' => 'j/n/Y A g:i'
            ),
            'ta-LK' => array(
                'name' => 'Tamil (Sri Lanka)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'tt' => array(
                'name' => 'Tatar',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'tt-RU' => array(
                'name' => 'Tatar (Russia)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'te' => array(
                'name' => 'Telugu',
                'dateFormat' => 'd-m-y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-y H:i'
            ),
            'te-IN' => array(
                'name' => 'Telugu (India)',
                'dateFormat' => 'd-m-y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-y H:i'
            ),
            'teo' => array(
                'name' => 'Teso',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'teo-KE' => array(
                'name' => 'Teso (Kenya)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'teo-UG' => array(
                'name' => 'Teso (Uganda)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'th' => array(
                'name' => 'Thai',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j/n/Y G:i'
            ),
            'th-TH' => array(
                'name' => 'Thai (Thailand)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j/n/Y G:i'
            ),
            'dav' => array(
                'name' => 'Taita',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'dav-KE' => array(
                'name' => 'Taita (Kenya)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'twq' => array(
                'name' => 'Tasawaq',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'twq-NE' => array(
                'name' => 'Tasawaq (Niger)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'bo' => array(
                'name' => 'Tibetan',
                'dateFormat' => 'Y/n/j',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y/n/j H:i'
            ),
            'bo-IN' => array(
                'name' => 'Tibetan (India)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'Y-m-d g:i A'
            ),
            'bo-CN' => array(
                'name' => 'Tibetan (China)',
                'dateFormat' => 'Y/n/j',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y/n/j H:i'
            ),
            'tig' => array(
                'name' => 'Tigre',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'tig-ER' => array(
                'name' => 'Tigre (Eritrea)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'ti' => array(
                'name' => 'Tigrinya',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'ti-ER' => array(
                'name' => 'Tigrinya (Eritrea)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'ti-ET' => array(
                'name' => 'Tigrinya (Ethiopia)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'to' => array(
                'name' => 'Tongan',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'to-TO' => array(
                'name' => 'Tongan (Tonga)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'ts' => array(
                'name' => 'Tsonga',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'ts-ZA' => array(
                'name' => 'Tsonga (South Africa)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'tr' => array(
                'name' => 'Turkish',
                'dateFormat' => 'j.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j.m.Y H:i'
            ),
            'tr-CY' => array(
                'name' => 'Turkish (Cyprus)',
                'dateFormat' => 'j.m.Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j.m.Y g:i A'
            ),
            'tr-TR' => array(
                'name' => 'Turkish (Turkey)',
                'dateFormat' => 'j.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j.m.Y H:i'
            ),
            'tk' => array(
                'name' => 'Turkmen',
                'dateFormat' => 'd.m.y ý.',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.y ý. H:i'
            ),
            'tk-TM' => array(
                'name' => 'Turkmen (Turkmenistan)',
                'dateFormat' => 'd.m.y ý.',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.y ý. H:i'
            ),
            'uk' => array(
                'name' => 'Ukrainian',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd.m.Y G:i'
            ),
            'uk-UA' => array(
                'name' => 'Ukrainian (Ukraine)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd.m.Y G:i'
            ),
            'hsb' => array(
                'name' => 'Upper Sorbian',
                'dateFormat' => 'j.n.Y',
                'timeFormat' => 'G:i \h\o\dź.',
                'dateTimeFormat' => 'j.n.Y G:i \h\o\dź.'
            ),
            'hsb-DE' => array(
                'name' => 'Upper Sorbian (Germany)',
                'dateFormat' => 'j.n.Y',
                'timeFormat' => 'G:i \h\o\dź.',
                'dateTimeFormat' => 'j.n.Y G:i \h\o\dź.'
            ),
            'ur' => array(
                'name' => 'Urdu',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'ur-IN' => array(
                'name' => 'Urdu (India)',
                'dateFormat' => 'j/n/y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/y g:i A'
            ),
            'ur-PK' => array(
                'name' => 'Urdu (Islamic Republic of Pakistan)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'ug' => array(
                'name' => 'Uyghur',
                'dateFormat' => 'Y-n-j',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'Y-n-j G:i'
            ),
            'ug-CN' => array(
                'name' => 'Uyghur (China)',
                'dateFormat' => 'Y-n-j',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'Y-n-j G:i'
            ),
            'uz' => array(
                'name' => 'Uzbek',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'uz-Cyrl' => array(
                'name' => 'Uzbek (Cyrillic)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'uz-Cyrl-UZ' => array(
                'name' => 'Uzbek (Cyrillic, Uzbekistan)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'uz-Latn' => array(
                'name' => 'Uzbek (Latin)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'uz-Latn-UZ' => array(
                'name' => 'Uzbek (Latin, Uzbekistan)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'uz-Arab' => array(
                'name' => 'Uzbek (Perso-Arabic)',
                'dateFormat' => 'd/m Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd/m Y G:i'
            ),
            'uz-Arab-AF' => array(
                'name' => 'Uzbek (Perso-Arabic, Afghanistan)',
                'dateFormat' => 'd/m Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd/m Y G:i'
            ),
            'vai' => array(
                'name' => 'Vai',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'vai-Latn' => array(
                'name' => 'Vai (Latin)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'vai-Latn-LR' => array(
                'name' => 'Vai (Latin, Liberia)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'vai-Vaii' => array(
                'name' => 'Vai (Vai)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'vai-Vaii-LR' => array(
                'name' => 'Vai (Vai, Liberia)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'ca-ES-valencia' => array(
                'name' => 'Valencian (Spain)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j/n/Y G:i'
            ),
            've' => array(
                'name' => 'Venda',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            've-ZA' => array(
                'name' => 'Venda (South Africa)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'vi' => array(
                'name' => 'Vietnamese',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'vi-VN' => array(
                'name' => 'Vietnamese (Vietnam)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'vo' => array(
                'name' => 'Volapük',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'vo-001' => array(
                'name' => 'Volapük (World)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'vun' => array(
                'name' => 'Vunjo',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'vun-TZ' => array(
                'name' => 'Vunjo (Tanzania)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'wae' => array(
                'name' => 'Walser',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'wae-CH' => array(
                'name' => 'Walser (Switzerland)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'cy' => array(
                'name' => 'Welsh',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'cy-GB' => array(
                'name' => 'Welsh (United Kingdom)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'wal' => array(
                'name' => 'Wolaytta',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'wal-ET' => array(
                'name' => 'Wolaytta (Ethiopia)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'wo' => array(
                'name' => 'Wolof',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'wo-SN' => array(
                'name' => 'Wolof (Senegal)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'xh' => array(
                'name' => 'Xhosa',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'xh-ZA' => array(
                'name' => 'Xhosa (South Africa)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'yav' => array(
                'name' => 'Yangben',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'yav-CM' => array(
                'name' => 'Yangben (Cameroon)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'ii' => array(
                'name' => 'Yi',
                'dateFormat' => 'Y/n/j',
                'timeFormat' => 'A g:i',
                'dateTimeFormat' => 'Y/n/j A g:i'
            ),
            'ii-CN' => array(
                'name' => 'Yi (China)',
                'dateFormat' => 'Y/n/j',
                'timeFormat' => 'A g:i',
                'dateTimeFormat' => 'Y/n/j A g:i'
            ),
            'yi' => array(
                'name' => 'Yiddish',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'yi-001' => array(
                'name' => 'Yiddish (World)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'yo' => array(
                'name' => 'Yoruba',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'yo-BJ' => array(
                'name' => 'Yoruba (Benin)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'yo-NG' => array(
                'name' => 'Yoruba (Nigeria)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'dje' => array(
                'name' => 'Zarma',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'dje-NE' => array(
                'name' => 'Zarma (Niger)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'zu' => array(
                'name' => 'Zulu',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'zu-ZA' => array(
                'name' => 'Zulu (South Africa)',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            )
        );
    }

    /**
     * Get the locale data with the given locale code
     *
     * If the locale does not exist, the default en-US locale will be returned
     *
     * @param   string  $locale
     * @return  array
     */
    public static function getLocale($locale = '')
    {
        $locales = Quform::getLocales();

        if ( ! empty($locales[$locale])) {
            return $locales[$locale];
        }

        return $locales['en-US'];
    }

    /**
     * Get the plugin icon SVG in the given color
     *
     * @param   string  $color
     * @return  string
     */
    public static function getPluginIcon($color = '')
    {
        $icon = '<?xml version="1.0" standalone="no"?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 20010904//EN"
 "http://www.w3.org/TR/2001/REC-SVG-20010904/DTD/svg10.dtd">
<svg version="1.0" xmlns="http://www.w3.org/2000/svg"
 width="397.000000pt" height="354.000000pt" viewBox="0 0 397.000000 354.000000"
 preserveAspectRatio="xMidYMid meet">

<g transform="translate(0.000000,354.000000) scale(0.100000,-0.100000)"
fill="#82878c" stroke="none">
<path d="M1660 3530 c-548 -67 -1036 -347 -1337 -768 -146 -204 -244 -433
-295 -687 -32 -160 -32 -451 0 -614 157 -784 810 -1360 1644 -1450 136 -15
2208 -15 2241 0 53 24 57 47 57 304 0 257 -4 280 -57 304 -16 7 -128 11 -319
11 l-295 0 67 83 c226 277 344 569 376 929 19 224 -6 432 -82 659 -206 622
-766 1089 -1450 1210 -131 24 -428 33 -550 19z m400 -635 c135 -21 230 -49
346 -104 139 -67 244 -140 344 -240 451 -454 449 -1114 -5 -1566 -467 -465
-1243 -473 -1726 -18 -148 140 -275 352 -326 548 -22 87 -26 120 -26 255 0
136 4 168 27 255 46 174 144 355 268 490 272 297 692 443 1098 380z"/>
<path d="M1255 2341 c-11 -5 -31 -21 -45 -36 -22 -23 -25 -36 -25 -96 0 -64 2
-71 33 -101 l32 -33 660 0 660 0 32 33 c31 30 33 37 33 102 0 65 -2 72 -33
102 l-32 33 -648 2 c-356 1 -656 -2 -667 -6z"/>
<path d="M1255 1901 c-11 -5 -31 -21 -45 -36 -22 -23 -25 -36 -25 -96 0 -64 2
-71 33 -101 l32 -33 405 0 405 0 32 33 c31 30 33 37 33 102 0 65 -2 72 -33
102 l-32 33 -393 2 c-215 1 -401 -2 -412 -6z"/>
<path d="M1255 1461 c-11 -5 -31 -21 -45 -36 -22 -23 -25 -36 -25 -96 0 -64 2
-71 33 -101 l32 -33 165 0 165 0 32 33 c31 30 33 37 33 102 0 65 -2 72 -33
102 l-32 33 -153 2 c-83 1 -161 -1 -172 -6z"/>
</g>
</svg>';

        if (Quform::isNonEmptyString($color)) {
            $icon = str_replace('fill="#82878c"', sprintf('fill="%s"', $color), $icon);
        }

        return 'data:image/svg+xml;base64,' . base64_encode($icon);
    }

    /**
     * Does the current user have any of the given capabilities?
     *
     * @deprecated  2.1.0
     * @param       array|string  $caps
     * @return      bool
     */
    public static function currentUserCan($caps)
    {
        _deprecated_function(__METHOD__, '2.1.0', 'current_user_can()');

        if ( ! is_user_logged_in()) {
            return false;
        }

        if (current_user_can('quform_full_access')) {
            return true;
        }

        if ( ! is_array($caps)) {
            $caps = array($caps);
        }

        foreach ($caps as $cap) {
            if (current_user_can($cap)) {
                return true;
            }
        }

        return false;
    }

    /**
     * If the value is a non-zero number it will append 'px' otherwise return the value unchanged
     *
     * @param   string  $value
     * @return  string
     */
    public static function addCssUnit($value)
    {
        if (is_numeric($value) && (string) $value !== '0') {
            $value = sprintf('%spx', $value);
        }

        return $value;
    }

    /**
     * Format the given count into thousands if necessary e.g. 1100 becomes 1.1k
     *
     * @param   int     $count
     * @return  string
     */
    public static function formatCount($count)
    {
        if ($count >= 1000000000) {
            $count = floor($count / 100000000) * 100000000;
            $precision = $count % 1000000000 < 100000000 ? 0 : 1;

            /* translators: %s: number (in billions) */
            return sprintf(_x('%sb', 'number ending in b (billions)', 'quform'), number_format_i18n($count / 1000000000, $precision));
        } else if ($count >= 1000000) {
            $count = floor($count / 100000) * 100000;
            $precision = $count % 1000000 < 100000 ? 0 : 1;

            /* translators: %s: number (in millions) */
            return sprintf(_x('%sm', 'number ending in m (millions)', 'quform'), number_format_i18n($count / 1000000, $precision));
        } else if ($count >= 1000) {
            $count = floor($count / 100) * 100;
            $precision = $count % 1000 < 100 ? 0 : 1;

            /* translators: %s: number (in thousands) */
            return sprintf(_x('%sk', 'number ending in k (thousands)', 'quform'), number_format_i18n($count / 1000, $precision));
        } else {
            return $count;
        }
    }

    /**
     * Base 64 encode the given data in a format safe for URLs
     *
     * Credit: http://php.net/manual/en/function.base64-encode.php#103849
     *
     * @param   mixed  $data
     * @return  string
     */
    public static function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Get all pages in an array formatted for select2
     *
     * @deprecated 2.10.0
     * @return array
     */
    public static function getPages()
    {
        $values = array();

        $pages = get_pages(array(
            'number' => 250,
            'sort_column' => 'post_modified',
            'sort_order' => 'DESC'
        ));

        if (is_array($pages) && count($pages)) {
            foreach ($pages as $page) {
                $values[] = array(
                    'id' => (string) $page->ID,
                    'title' => Quform::getPostTitle($page)
                );
            }
        }

        return $values;
    }

    /**
     * Get all posts in an array formatted for select2
     *
     * @deprecated 2.10.0
     * @return array
     */
    public static function getPosts()
    {
        $values = array();

        $posts = get_posts(array(
            'numberposts' => 250,
            'orderby' => 'modified',
            'order' => 'DESC'
        ));

        if (is_array($posts) && count($posts)) {
            foreach ($posts as $post) {
                $values[] = array(
                    'id' => (string) $post->ID,
                    'title' => Quform::getPostTitle($post)
                );
            }
        }

        return $values;
    }

    /**
     * Get the title of the given post
     *
     * @deprecated 2.10.0
     * @param   WP_Post  $post
     * @return  string
     */
    public static function getPostTitle($post)
    {
        $title = '';

        if ($post instanceof WP_Post) {
            /* translators: %d: the post ID */
            $title = $post->post_title === '' ? sprintf(__('(no title) [%d]', 'quform'), $post->ID) : $post->post_title;
        }

        return $title;
    }

    /**
     * Get the title of the post by ID
     *
     * @param   int     $id  The post ID
     * @return  string       The post title
     */
    public static function getPostTitleById($id)
    {
        $post = get_post($id);

        if ($post instanceof WP_Post) {
            /* translators: %d: the post ID */
            $title = Quform::isNonEmptyString($post->post_title) ? $post->post_title : sprintf(__('(no title) [%d]', 'quform'), $post->ID);
        } else {
            /* translators: %d: the post ID */
            $title = sprintf(__('(post not found) [%d]', 'quform'), $id);
        }

        return $title;
    }

    /**
     * Search all public post types for the given search term
     *
     * @param   string  $search
     * @return  array
     */
    public static function searchPosts($search)
    {
        global $wpdb;

        $search = '%' . $wpdb->esc_like($search) . '%';

        $postTypes = get_post_types(array('public' => true));
        unset($postTypes['attachment']);

        $postTypesPlaceholders = join(', ', array_fill(0, count($postTypes), '%s'));

        $args = array($search);

        foreach ($postTypes as $postType) {
            $args[] = $postType;
        }

        $query = $wpdb->prepare("SELECT ID, post_title FROM {$wpdb->posts} WHERE post_title LIKE %s AND post_status = 'publish' AND post_type IN ({$postTypesPlaceholders}) LIMIT 10;", $args);

        $results = $wpdb->get_results($query);

        if (!is_array($results)) {
            $results = array();
        }

        return $results;
    }

    /**
     * Search all users for the given search term
     *
     * @param   string  $search
     * @return  array
     */
    public static function searchUsers($search)
    {
        $users = new WP_User_Query(array(
            'search' => '*' . $search . '*'
        ));

        return $users->get_results();
    }

    /**
     * Format a date
     *
     * @param   string        $format    The format of the returned date
     * @param   DateTime      $date      The DateTime instance representing the moment of time in UTC, or null for the current time
     * @param   DateTimeZone  $timezone  The timezone of the returned date, will default to the WP timezone if omitted
     * @return  string|false             The formatted date or false if there was an error
     */
    public static function date($format, DateTime $date = null, DateTimeZone $timezone = null)
    {
        if ( ! $date) {
            try {
                $date = new DateTime('now', new DateTimeZone('UTC'));
            } catch (Exception $e) {
                return false;
            }
        }

        $timestamp = $date->getTimestamp();

        if ($timestamp === false || ! function_exists('wp_date')) {
            $timezone = $timezone ? $timezone : self::getTimezone();
            $date->setTimezone($timezone);

            return $date->format($format);
        }

        return wp_date($format, $timestamp, $timezone);
    }

    /**
     * Get the WP timezone as a DateTimeZone instance
     *
     * Duplicate of wp_timezone() for WP <5.3.
     *
     * @return DateTimeZone
     */
    public static function getTimezone()
    {
        if (function_exists('wp_timezone')) {
            return wp_timezone();
        }

        return new DateTimeZone(self::getTimezoneString());
    }

    /**
     * Get the WP timezone as a string
     *
     * Duplicate of wp_timezone_string() for WP <5.3.
     *
     * @return string
     */
    public static function getTimezoneString()
    {
        if (function_exists('wp_timezone_string')) {
            return wp_timezone_string();
        }

        $timezone_string = get_option('timezone_string');

        if ($timezone_string) {
            return $timezone_string;
        }

        // PHP versions below 5.5.10 don't support the offset timezone format in the DateTimeZone constructor
        if (version_compare(PHP_VERSION, '5.5.10', '<')) {
            return 'UTC';
        }

        $offset  = (float) get_option('gmt_offset');
        $hours   = (int) $offset;
        $minutes = ($offset - $hours);

        $sign      = ($offset < 0) ? '-' : '+';
        $abs_hour  = abs($hours);
        $abs_mins  = abs($minutes * 60);
        $tz_offset = sprintf('%s%02d:%02d', $sign, $abs_hour, $abs_mins);

        return $tz_offset;
    }

    /**
     * Compare the two given dates
     *
     * Returns:
     *  -1     if first is before second
     *  0      if the dates are the same
     *  1      if first is after the second
     * false   if either date is invalid
     *
     * @param   string  $first   The first date in YYYY-MM-DD format
     * @param   string  $second  The second date in YYYY-MM-DD format
     * @return  int|bool
     */
    public static function compareDates($first, $second)
    {
        try {
            $first = new DateTime($first, new DateTimeZone('UTC'));
            $second = new DateTime($second, new DateTimeZone('UTC'));

            if ($first < $second) {
                return -1;
            } elseif ($first > $second) {
                return 1;
            }

            return 0;
        } catch (Exception $e) {
            return false;
        }
    }
}
