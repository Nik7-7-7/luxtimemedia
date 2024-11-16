<?php
/**
 * BerqWP Advanced Cache Drop-in
*/

if (!defined('ABSPATH')) exit;

require_once ABSPATH . '/wp-content/plugins/searchpro/inc/class-ignoreparams.php';
require_once ABSPATH . '/wp-content/plugins/searchpro/inc/dropin-functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !bwp_is_user_logged_in() && !bwp_is_ajax()) {
    $slug = $_SERVER['REQUEST_URI'];
    $slug = dropin_remove_ignore_params($slug);
    $cache_key = md5($slug);
    $cache_directory = ABSPATH . '/wp-content/cache/berqwp/html/';
    $cache_file = $cache_directory . $cache_key . '.html';
    $gzip_support = function_exists('gzencode') && isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false;
    $cache_max_life = @filemtime($cache_file) + (10 * 60 * 60);
    $is_litespeed = isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'LiteSpeed') !== false;

    if (file_exists($cache_file) && $cache_max_life > time()) {
        if ($gzip_support && file_exists($cache_directory . $cache_key . '.gz') && !$is_litespeed) {
            $cache_file = $cache_directory . $cache_key . '.gz';
            header('Content-Encoding: gzip');
            header('Content-Type: text/html; charset=UTF-8');
        }
    
        
        if (!isset($_GET['creating_cache']) && file_exists($cache_file)) {
            $lastModified = filemtime($cache_file);
            $etag = md5_file($cache_file);
            header('ETag: ' . $etag);
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');
        
            // Check if the client has a cached copy and if it's still valid using Last-Modified
            if ((isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $lastModified) || (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === $etag)) {
                // The client's cache is still valid based on Last-Modified, respond with a 304 Not Modified
                header('HTTP/1.1 304 Not Modified');
                header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
                header('Cache-Control: no-cache, must-revalidate');
                exit();
        
            } 

            header('Cache-Control: public, max-age=86400');
    
            if (file_exists($cache_file)) {
                readfile($cache_file);
                exit();
            }
        }

    }
    
}

add_action('template_redirect', function () {
    if (class_exists('berqCache') && !is_admin()) {
        global $berqwp_is_dropin;
        $berqwp_is_dropin = true;
        $berqCache = new berqCache();
        $berqCache->html_cache();
    }
});
