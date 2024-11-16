<?php
if (!defined('ABSPATH')) exit;

class ObjectCachePro extends berqIntegrations {
    function __construct() {
        add_action('berqwp_flush_all_cache', [$this, 'flush_cache']);
        add_action('berqwp_stored_page_cache', [$this, 'flush_cache']);
    }

    function flush_cache()
    {
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush(); // Clear the entire object cache.
        }
    }
}

new ObjectCachePro();