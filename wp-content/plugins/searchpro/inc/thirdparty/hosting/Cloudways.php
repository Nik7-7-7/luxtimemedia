<?php 
if (!defined('ABSPATH')) exit;

class Cloudways extends berqIntegrations {
    function __construct() {
        add_action('berqwp_stored_page_cache', [$this, 'flush_page_cache']);
        add_action('berqwp_flush_page_cache', [$this, 'flush_page_cache']);
        add_action('berqwp_flush_all_cache', [$this, 'flush_page_cache']);
    }

    function flush_page_cache($slug = '/') {
        $page_url = trailingslashit( home_url() . $slug );
        $this->flush_cloudways_varnish($page_url);
    }

    function flush_cloudways_varnish($url = '') {

        if (!$this->is_varnish_running()) {
            return;
        }

        if (empty($url)) {
            $url = home_url();
        }

        $parse_url = parse_url($url);
        $host = $parse_url['host'];
        
        // Setup the request to purge Varnish cache
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PURGE');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Host: $host"
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode === 200;
    }

    function is_varnish_running() {
        // Check if the 'X-Varnish' or 'X-Cache' headers are present in the response headers
        if (isset($_SERVER['HTTP_X_VARNISH']) || isset($_SERVER['HTTP_X_CACHE'])) {
            return true;
        }
    
        return false;
    }

    function is_cloudways_hosting() {
        if (isset($_SERVER['cw_allowed_ip'])) {
            return true;
        }
    
        return false;
    }
}

new Cloudways();