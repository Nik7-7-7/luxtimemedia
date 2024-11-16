<?php
if (!defined('ABSPATH')) exit;

class WPEngine extends berqIntegrations {
    function __construct() {
        add_action('berqwp_flush_all_cache', [$this, 'flush_cache']);
        add_action('berqwp_stored_page_cache', [$this, 'flush_cache']);
        add_action('berqwp_flush_page_cache', [$this, 'flush_cache']);
    }
    

    function flush_cache() {

        if (class_exists('WpeCommon')) {
            if ( method_exists( 'WpeCommon', 'purge_memcached' ) ) {
                WpeCommon::purge_memcached();
            }
    
            if ( method_exists( 'WpeCommon', 'purge_varnish_cache' ) ) {
                WpeCommon::purge_varnish_cache();
            }
        }
    }

}

new WPEngine();