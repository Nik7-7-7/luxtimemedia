<?php

function bwp_is_ajax() {
    return 
        (function_exists("wp_doing_ajax") && wp_doing_ajax()) ||
        (defined('DOING_AJAX') && DOING_AJAX) ||
        (!empty($_SERVER["HTTP_X_REQUESTED_WITH"]) && $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") ||
        (!empty($_SERVER["REQUEST_URI"]) && basename($_SERVER["REQUEST_URI"]) == "admin-ajax.php") ||
        !empty($_GET["wc-ajax"]);
}

function bwp_is_user_logged_in() {
    foreach ($_COOKIE as $key => $value) {
        if (strpos($key, 'wordpress_logged_in_') === 0) {
            return true;
        }
    }
    return false;
}

function dropin_remove_ignore_params($slug)
{
    $tracking_params = ignoreParams::$query_params;

    // Parse the provided slug
    $url_parts = parse_url($slug);
    
    // Get the current URL parameters
    $url_params = array();
    if (isset($url_parts['query'])) {
        parse_str($url_parts['query'], $url_params);
    }
    
    // Remove specified tracking parameters from the URL
    foreach ($tracking_params as $param) {
        $param = trim($param);
        if (isset($url_params[$param])) {
            unset($url_params[$param]);
        }
    }

    // Build the new query string
    $new_query_string = http_build_query($url_params);

    // Reconstruct the URL with the new query string
    $new_slug = $url_parts['path'];
    if (!empty($new_query_string)) {
        $new_slug .= '?' . $new_query_string;
    }

    return $new_slug;
}