<?php 
if (!defined('ABSPATH')) exit;

class Siteground extends berqIntegrations {
    function __construct() {
        add_action('berqwp_stored_page_cache', [$this, 'flush_page_cache']);
        add_action('berqwp_flush_page_cache', [$this, 'flush_page_cache']);
        add_action('berqwp_flush_all_cache', [$this, 'flush_cache']);
    }

    function flush_cache() {
        $this->purge_siteground_url(trailingslashit( home_url()."/(.*)" ));
    }

    function flush_page_cache($slug) {
        $page_url = get_site_url() . $slug;
        $this->purge_siteground_url($page_url);
    }

    function purge_siteground_url($url) {
        // Check if the site is hosted on SiteGround
        if (!$this->is_siteground()) {
            return false;
        }
    
        // Construct the URL object to parse host and path
        $urlObj = parse_url($url);
        $host = preg_replace("/^www\./", "", $urlObj['host']);
        $path = isset($urlObj['path']) ? $urlObj['path'] : '/';
    
        // If there's a query string, append it to the path
        if (!empty($urlObj['query'])) {
            // $path .= '?' . $urlObj['query'];
            $path .= "(.*)";
        }
    
        try {
            $sock_path = '/chroot/tmp/site-tools.sock'; // Adjust the socket path as per your SiteGround setup
    
            // Check if the socket file exists
            if (!file_exists($sock_path)) {
                return false;
            }
    
            // Open a socket connection
            $sock = stream_socket_client('unix://' . $sock_path, $errno, $errstr, 5);
    
            // Check if the connection was successful
            if (false === $sock) {
                return false;
            }
    
            // Construct the purge request
            $req = array(
                'api' => 'domain-all',
                'cmd' => 'update',
                'settings' => array('json' => 1),
                'params' => array(
                    'flush_cache' => '1',
                    'id' => $host,
                    'path' => $path,
                ),
            );
    
            // Send the request to the socket
            fwrite($sock, json_encode($req, JSON_FORCE_OBJECT) . "\n");
    
            // Read the response from the socket
            $response = fgets($sock, 32 * 1024);
    
            // Close the socket connection
            fclose($sock);
    
            // Decode the response JSON
            $result = @json_decode($response, true);
    
            // Check for errors in the response
            if (false === $result || isset($result['err_code'])) {
                return false;
            }
        } catch (Exception $e) {
            // Handle any exceptions that occur during the process
            return false;
        }
    
        // Return true on successful cache purge
        return true;
    }

    function is_siteground() {
        // Check by hostname or wp-config.php content
        if (strpos(gethostname(), "siteground.eu") !== false) {
            return true;
        }
    
        $configFilePath = ABSPATH . 'wp-config.php';
        if (!file_exists($configFilePath)) {
            return false;
        }
    
        // Check for SiteGround-specific marker in wp-config.php
        $configContent = file_get_contents($configFilePath);
        return strpos($configContent, 'Added by SiteGround WordPress management system') !== false;
    }
}

new Siteground();