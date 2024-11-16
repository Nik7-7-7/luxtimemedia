<?php 
if (!defined('ABSPATH')) exit;

class CloudflarePageCache extends berqIntegrations {
    function __construct() {
        add_action('berqwp_flush_all_cache', [$this, 'flush_cache']);
        add_action('berqwp_stored_page_cache', [$this, 'flush_cache']);
        add_action('berqwp_flush_page_cache', [$this, 'flush_cache']);
    }

    function flush_cache() {
        if (class_exists('SW_CLOUDFLARE_PAGECACHE') && isset($GLOBALS['sw_cloudflare_pagecache'])) {
            $GLOBALS['sw_cloudflare_pagecache']->objects['cache_controller']->purge_all();
        }
    }
}

new CloudflarePageCache();