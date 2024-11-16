<?php

class berqReverseProxyCache
{
    public static function bypass()
    {
        add_action('send_headers', [self::class, 'handle_bypass']);
    }

    public static function handle_bypass()
    {
        if (!self::is_reverse_proxy_cache_enabled()) {
            return false;
        }

        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('X-Bypass-Cache: ' . time());
        header('Vary: X-Bypass-Cache');
    }

    public static function is_reverse_proxy_cache_enabled()
    {
        $custom_cache_header = get_option('berqwp_custom_cache_header');
        return isset($_SERVER['HTTP_X_CACHE']) ||
            isset($_SERVER['HTTP_X_VARNISH']) ||
            isset($_SERVER['HTTP_CF_CACHE_STATUS']) ||
            isset($_SERVER['HTTP_X_CACHE_STATUS']) ||
            isset($_SERVER['HTTP_X_FASTCGI_CACHE']) ||
            ($custom_cache_header && isset($_SERVER[$custom_cache_header]));
    }

    public static function purge_varnish_cache($url) {
        
        // Parse the URL to extract the host for the Host header
        $parse_url = parse_url($url);
        $host = $parse_url['host'];

        $headers = [
            "Host: $host"
        ];

        // Purge varnish cache for whole website
        if (strpos($url, '.*') !== false) {
            $headers['X-Ban-Url'] = '.*';
            $url = home_url();
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'BAN');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200;
    }

    public static function purge_cache($url)
    {
        if (!self::is_reverse_proxy_cache_enabled()) {
            return false;
        }

        $parsed_url = parse_url($url);
        $host = $parsed_url['host'];

        wp_remote_request($url, array(
            'method' => 'PURGE',
            'headers' => array(
                'Host' => $host
            )
        )
        );

        self::purge_varnish_cache($url);
    }
}