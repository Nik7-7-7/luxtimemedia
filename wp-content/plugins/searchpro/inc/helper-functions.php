<?php
if (!defined('ABSPATH'))
    exit;


function berqwp_is_slug_excludable($slug)
{
    if (empty($slug)) {
        return true;
    }

    $exclude_items = ["elementor_library=", "add_to_wishlist=", "robots.txt", ".html", ".php", "run_warmup=", "et-compare-page=", "add_to_compare=", "min_price=", "max_price=", "view_mode=", "view_mode_smart=", "et_columns-count=", "add_to_wishlist=", "et-wishlist-page=", "remove_wishlist=", "stock_status=", "page_id=", "?p="];

    $exclude_items = apply_filters('berqwp_exclude_slug_match', $exclude_items);

    foreach ($exclude_items as $item) {
        if (strpos($slug, $item) !== false) {
            return true;
        }
    }

    return false;
}

function berqwp_get_page_params($slug, $is_forced = false) {
    if (empty($slug)) {
        return;
    }

    $url = home_url() . $slug;
    $slug_md5 = md5($slug);

    $cache_directory = optifer_cache . '/html/';
    $cache_file = $cache_directory . $slug_md5 . '.html';
    $key = uniqid();
    $cache_max_life = @filemtime($cache_file) + (10 * 60 * 60);

    if (!file_exists($cache_file) || (file_exists($cache_file) && $cache_max_life < time())) {
        // Priority 1
        $key = '';
    }

    $optimization_mode = get_option('berq_opt_mode');

    if ($optimization_mode == 4) {
        $optimization_mode = 'aggressive';
    } elseif ($optimization_mode == 3) {
        $optimization_mode = 'blaze';
    } elseif ($optimization_mode == 2) {
        $optimization_mode = 'medium';
    } elseif ($optimization_mode == 1) {
        $optimization_mode = 'basic';
    }

    // Data to send as POST parameters
    $post_data = array(
        'license_key'                   => get_option('berqwp_license_key'),
        'page_url'                      => $url,
        'page_slug'                     => $slug,
        'site_url'                      => home_url(),
        'webp_max_width'                => (int) get_option('berqwp_webp_max_width'),
        'webp_quality'                  => (int) get_option('berqwp_webp_quality'),
        'img_lazyloading'               => get_option('berqwp_image_lazyloading'),
        'youtube_lazyloading'           => get_option('berqwp_lazyload_youtube_embed'),
        'js_mode'                       => get_option('berqwp_javascript_execution_mode'),
        'key'                           => $key,
        'interaction_delay'             => get_option('berqwp_interaction_delay'),
        'cache_js'                      => true,
        'use_cdn'                       => get_option('berqwp_enable_cdn'),
        'opt_mode'                      => $optimization_mode,
        'disable_webp'                  => get_option('berqwp_disable_webp'),
        'js_css_exclude_urls'           => get_option('berq_exclude_js_css', []),
        'preload_fontfaces'             => get_option('berqwp_preload_fontfaces'),
        // 'mobile_lcp'                 => json_encode($mobile_lcp),
        // 'desktop_lcp'                => json_encode($desktop_lcp),
        'version'                       => BERQWP_VERSION
    );

    if (defined('BERQ_STAGING') || $is_forced) {
        $post_data['run_queue'] = 1;
        $post_data['doing_queue'] = true;
    }

    return $post_data;


}

function bwp_pass_account_requirement() {
    global $berqWP, $berq_log;

    $license_key = get_option('berqwp_license_key');
	$key_response = $berqWP->verify_license_key($license_key);

    if  ($key_response->result !== 'success' || $key_response->status !== 'active') {
        $berq_log->error("account requirement: license verification failed");
        return false;
    }

    if ($key_response->product_ref == 'Free Account' && bwp_cached_pages_count() >= 10) {
        return false;
    }

    if ($key_response->product_ref == 'Starter' && bwp_cached_pages_count() >= 100) {
        return false;
    }

    return true;
}

function warmup_cache_by_slug($slug, $is_forced = false)
{
    if (empty($slug)) {
        return;
    }

    if (berqwp_is_slug_excludable($slug)) {
        return;
    }

    $slug_md5 = md5($slug);

    $cache_directory = optifer_cache . '/html/';
    $cache_file = $cache_directory . $slug_md5 . '.html';
    $cache_life_span = time() - (10 * 60 * 60);

    if (!file_exists($cache_file) && bwp_pass_account_requirement() === false) {
        return;
    }

    // Return if page is excluded from cache
    $pages_to_exclude = get_option('berq_exclude_urls', []);

    if (in_array(home_url() . $slug, $pages_to_exclude)) {
        return;
    }
    
    // Hook to modify cache lifespan
    $cache_life_span = apply_filters('berqwp_cache_lifespan', $cache_life_span);

    if (file_exists($cache_file) && filemtime($cache_file) > $cache_life_span) {
        return;
    }

    // API endpoint URL
    $api_endpoint = 'https://boost.berqwp.com/photon/';
    
    if (get_site_url() == 'http://berq-test.local') {
        $api_endpoint = 'http://dev-berqwp.local/photon/';
    }

    // Modify photon engine endpoint for testing purposes
    $api_endpoint = apply_filters( 'berqwp_photon_endpoint', $api_endpoint );

    $post_data = berqwp_get_page_params($slug, $is_forced);
    
    // Set up the request arguments
    $args = array(
        'body'              => $post_data,  // Pass the POST data here
        'method'            => 'POST',
        // 'blocking'          => false,
        'timeout'           => $is_forced === true ? 20 : 0.1,
        'headers' => array(
            'Content-Type' => 'application/x-www-form-urlencoded', // Adjust content type if needed
        ),
    );

    // Send the POST request
    $response = wp_remote_post($api_endpoint, $args);

    // Check for errors and handle the response
    if (is_wp_error($response)) {
        // There was an error with the request
        error_log('Error: ' . $response->get_error_message());
    }


}

function bwp_is_home_cached() {
    $slug_md5 = md5('/');
    $cache_directory = optifer_cache . '/html/';
    $cache_file = $cache_directory . $slug_md5 . '.html';

    return file_exists($cache_file);
}

function berq_is_localhost()
{
    $whitelist = array('127.0.0.1', '::1');

    if (in_array($_SERVER['REMOTE_ADDR'], $whitelist)) {
        return true;
    }

    return false;
}

function berqwp_remove_ignore_params($slug)
{
    // List of tracking parameters to remove
    $tracking_params = get_option('berq_ignore_urls_params', []);

    $tracking_params = apply_filters( 'berqwp_ignored_urls_params', $tracking_params );

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

function berqwp_is_sub_dir_wp()
{
    // remove http
    $site_url = explode('//', home_url())[1];
    $break_slash = explode('/', $site_url);

    return count($break_slash) > 1;
}

function berqwp_current_page_cache_file()
{
    $slug_uri = $_SERVER['REQUEST_URI'];

    // if wordpress is installed in a sub directory
    if (berqwp_is_sub_dir_wp()) {
        // Parse strings to extract paths
        $path1 = explode('/', parse_url(home_url(), PHP_URL_PATH));
        $path2 = explode('/', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

        // Find the common part of the paths
        $commonPath = implode('/', array_intersect($path1, $path2));

        // Subtract the common part from the first string
        $slug_uri = str_replace($commonPath, '', $_SERVER['REQUEST_URI']);
    }

    // Return if page is excluded from cache
    $pages_to_exclude = get_option('berq_exclude_urls', []);

    if (in_array(get_site_url() . $slug_uri, $pages_to_exclude)) {
        return;
    }


    $slug = berqwp_remove_ignore_params($slug_uri);

    if (isset($_GET['creating_cache'])) {
        return;
    }

    if (get_option('berqwp_enable_sandbox') == 1 && isset($_GET['berqwp'])) {
        $slug = explode('?berqwp', $slug_uri)[0];
    } elseif (get_option('berqwp_enable_sandbox') == 1 && !isset($_GET['creating_cache'])) {
        return;
    }


    // Attempt to retrieve the cached HTML from the cache directory
    $cache_directory = optifer_cache . '/html/';

    // Generate a unique cache key based on the current page URL
    $cache_key = md5($slug);
    $cache_file = $cache_directory . $cache_key . '.html';

    return $cache_file;

}

function berqwp_get_LCP_details($url, $device = 'mobile')
{
    $google_pagespeed_api_url = "https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=$url&strategy=$device ";

    // Send a GET request to the Google PageSpeed Insights API
    // $response = wp_remote_get($google_pagespeed_api_url, array('timeout' => 60));
    $response = bwp_wp_remote_get($google_pagespeed_api_url, array('timeout' => 60));

    if (is_wp_error($response)) {
        return 'Error: ' . $response->get_error_message();
    }

    // Convert the JSON response to a PHP array
    $body = wp_remote_retrieve_body($response);
    $output = json_decode($body, true);

    // Get the LCP data        
    return $output['lighthouseResult']['audits']['largest-contentful-paint-element']['details']['items'][0]['items'][0]['node'];
}

function berqwp_enable_object_cache($enable) {
    global $berq_log;
    $berq_log->info("Updating wp-config.php");

    // Specify the wp-config.php file path
    $wp_config_file = ABSPATH . 'wp-config.php';

    // Read the contents of wp-config.php
    $wp_config_content = file_get_contents($wp_config_file);

    // Find the position of the first PHP tag using a regular expression
    preg_match('/<\?php/', $wp_config_content, $matches, PREG_OFFSET_CAPTURE);

    // Check if the PHP opening tag exists
    if (!empty($matches)) {
        $first_php_comment_position = $matches[0][1] + 5; // Move past the length of '<?php'

        // Check if the WP_CACHE definition exists in the file
        if (strpos($wp_config_content, "define('WP_CACHE'") === false && strpos($wp_config_content, "define( 'WP_CACHE' ") === false) {
            // If not, add the definition right after the opening PHP tag
            $wp_config_content = substr_replace($wp_config_content, "\n"
                                . "// Enable or disable BerqWP object cache\n"
                                . "define('WP_CACHE', " . ($enable ? 'true' : 'false') . ");\n",
                                $first_php_comment_position, 0);
        } else {
            // Otherwise, enable or disable the existing definition
            // $wp_config_content = preg_replace(
            //     "/define\('WP_CACHE', [^\n]*\);/",
            //     "define('WP_CACHE', " . ($enable ? 'true' : 'false') . ");",
            //     $wp_config_content
            // );

            $wp_config_content = preg_replace(
                "/define\(\s*'WP_CACHE'\s*,\s*[^\n]*\);/",
                "define('WP_CACHE', " . ($enable ? 'true' : 'false') . ");",
                $wp_config_content
            );
        }

        // Write the modified content back to wp-config.php
        file_put_contents($wp_config_file, $wp_config_content);
    } else {

        global $berq_log;
        $berq_log->error("Error: PHP opening tag not found in wp-config.php");
        
    }
}

// Copied from Nginx Helper plugin
function berqwp_unlink_recursive( $dir ) {

    if ( ! is_dir( $dir ) ) {
        return;
    }

    $dh = opendir( $dir );

    if ( ! $dh ) {
        return;
    }

    // phpcs:ignore -- WordPress.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition -- Variable assignment required for recursion.
    while ( false !== ( $obj = readdir( $dh ) ) ) {

        if ( '.' === $obj || '..' === $obj ) {
            continue;
        }

        if ( ! @unlink( $dir . '/' . $obj ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
            berqwp_unlink_recursive( $dir . '/' . $obj, false );
        }
    }

    closedir( $dh );
}

function berqwp_get_last_modified_timestamp() {
    global $post;

    // Check if it's a single post or page
    if (is_singular()) {
        return get_the_modified_time('U', $post->ID); // 'U' format parameter returns Unix timestamp
    }

    // Check if it's a taxonomy term
    if (is_tax() || is_category() || is_tag()) {
        $term = get_queried_object(); // Get the current term object

        // For tags, get the last modified date of the most recent post associated with the tag
        if (is_tag()) {
            $args = array(
                'tag_id' => $term->term_id,
                'posts_per_page' => 1,
                'orderby' => 'modified',
                'order' => 'DESC',
                'fields' => 'ids', // Return only post IDs to reduce overhead
            );
            $posts = get_posts($args);
            if ($posts) {
                $latest_post_id = $posts[0];
                return get_the_modified_time('U', $latest_post_id); // 'U' format parameter returns Unix timestamp
            }
        }

        // For category archives, get the last modified date of the most recent post within the category
        if (is_category()) {
            $args = array(
                'category' => $term->term_id,
                'posts_per_page' => 1,
                'orderby' => 'modified',
                'order' => 'DESC',
                'fields' => 'ids', // Return only post IDs to reduce overhead
            );
            $posts = get_posts($args);
            if ($posts) {
                $latest_post_id = $posts[0];
                return get_the_modified_time('U', $latest_post_id); // 'U' format parameter returns Unix timestamp
            }
        }

        return strtotime($term->modified); // Convert modified date to timestamp
    }

    // Check if it's an archive
    if (is_archive()) {
        // For other archives
        $archive_id = get_queried_object_id(); // Get the ID of the current archive
        $archive = get_post($archive_id); // Get the archive post object
        return strtotime($archive->post_modified); // Convert modified date to timestamp
    }

    // For other cases (fallback)
    return false;
}

function bwp_is_gzip_supported() {
    return function_exists('gzencode') && isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false;
}

function bwp_cached_pages_count() {
    $cache_directory = optifer_cache . DIRECTORY_SEPARATOR . 'html';
    $cache_files = glob($cache_directory . DIRECTORY_SEPARATOR . "*.html");
    return count($cache_files);
}

function bwp_wp_remote_get($url, $args = array()) {
    // Default arguments
    $defaults = array(
        'headers' => array(
            'User-Agent' => 'BerqWP Bot', // Customize user agent if needed
        ),
    );

    // Merge provided arguments with defaults
    $args = wp_parse_args($args, $defaults);

    if (empty($args['timeout'])) {
        $args['timeout'] = 30;
    }

    // Initialize cURL session
    $ch = curl_init();

    // Set the URL
    curl_setopt($ch, CURLOPT_URL, $url);

    // Set to return the transfer as a string
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Set timeout in seconds
    curl_setopt($ch, CURLOPT_TIMEOUT, $args['timeout']);

    // Set the user-agent
    curl_setopt($ch, CURLOPT_USERAGENT, $args['headers']['User-Agent']);

    // Include header in the output
    curl_setopt($ch, CURLOPT_HEADER, true);

    // Execute the request
    $response = curl_exec($ch);

    // Check for errors
    if (curl_errno($ch)) {
        $error_message = curl_error($ch);
        curl_close($ch);
        return new WP_Error('curl_error', $error_message);
    }

    // Close cURL session
    curl_close($ch);

    // Separate headers and body
    list($headers, $body) = explode("\r\n\r\n", $response, 2);

    // Parse headers into array
    $header_lines = explode("\r\n", $headers);
    $headers = array();
    foreach ($header_lines as $line) {
        $parts = explode(':', $line, 2);
        if (count($parts) == 2) {
            $headers[trim($parts[0])] = trim($parts[1]);
        }
    }

    // Construct response array similar to wp_remote_get
    $response = array(
        'headers' => $headers,
        'body' => $body,
        'response' => array(
            'code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
            'message' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
        ),
        'cookies' => array(),
        'filename' => '',
    );

    return $response;
}

function bwp_is_openlitespeed_server() {
    return isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'LiteSpeed') !== false;
}

function verify_request_origin($request) {
    // Check the referrer header to ensure the request is coming from the same site
    $referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    $site_url = get_site_url();

    // Optionally, check the origin header
    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

    // Ensure the request comes from the same site
    if (strpos($referrer, $site_url) !== 0 && strpos($origin, $site_url) !== 0) {
        return new WP_Error('rest_forbidden', esc_html__('You cannot access this resource.', 'searchpro'), array('status' => 403));
    }

    // Ensure the origin is a subdomain of berqwp.com
    if (!preg_match('/^https?:\/\/([a-z0-9-]+\.)?berqwp\.com$/', $origin)) {
        return new WP_Error('rest_forbidden', esc_html__('You cannot access this resource.', 'searchpro'), array('status' => 403));
    }

    return true;
}

function berq_rest_permission_callback(WP_REST_Request $request) {
    // Get the nonce from the request
    $nonce = $request->get_header('X-WP-Nonce');

    // Verify the nonce
    if (!wp_verify_nonce($nonce, 'wp_rest')) {
        return new WP_Error('rest_invalid_nonce', __('Invalid nonce', 'searchpro'), array('status' => 403));
    }

    return true; // Return true to allow the request
}

function berq_rest_verify_license_callback(WP_REST_Request $request) {
    $license_key_hash = sanitize_text_field($request->get_param('license_key_hash'));

    if (empty($license_key_hash) || $license_key_hash !== md5(get_option('berqwp_license_key'))) {
        global $berq_log;
        $berq_log->error("Exiting... Invalid license key.");
        return new WP_Error('rest_invalid_nonce', __('Invalid license key', 'searchpro'), array('status' => 403));

    }

    return true; // Return true to allow the request
}

function bwp_dash_notification($msg = '', $status = 'warning') {
    ?>
    <div class="berqwp-notification <?php echo esc_attr($status)?>">
        <?php 

        echo "<div class='icon'>";
        if ($status == 'warning') {
            echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M256 32c14.2 0 27.3 7.5 34.5 19.8l216 368c7.3 12.4 7.3 27.7 .2 40.1S486.3 480 472 480L40 480c-14.3 0-27.6-7.7-34.7-20.1s-7-27.8 .2-40.1l216-368C228.7 39.5 241.8 32 256 32zm0 128c-13.3 0-24 10.7-24 24l0 112c0 13.3 10.7 24 24 24s24-10.7 24-24l0-112c0-13.3-10.7-24-24-24zm32 224a32 32 0 1 0 -64 0 32 32 0 1 0 64 0z"/></svg>';
        } elseif ($status == 'error') {
            echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M256 0c53 0 96 43 96 96l0 3.6c0 15.7-12.7 28.4-28.4 28.4l-135.1 0c-15.7 0-28.4-12.7-28.4-28.4l0-3.6c0-53 43-96 96-96zM41.4 105.4c12.5-12.5 32.8-12.5 45.3 0l64 64c.7 .7 1.3 1.4 1.9 2.1c14.2-7.3 30.4-11.4 47.5-11.4l112 0c17.1 0 33.2 4.1 47.5 11.4c.6-.7 1.2-1.4 1.9-2.1l64-64c12.5-12.5 32.8-12.5 45.3 0s12.5 32.8 0 45.3l-64 64c-.7 .7-1.4 1.3-2.1 1.9c6.2 12 10.1 25.3 11.1 39.5l64.3 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-64 0c0 24.6-5.5 47.8-15.4 68.6c2.2 1.3 4.2 2.9 6 4.8l64 64c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0l-63.1-63.1c-24.5 21.8-55.8 36.2-90.3 39.6L272 240c0-8.8-7.2-16-16-16s-16 7.2-16 16l0 239.2c-34.5-3.4-65.8-17.8-90.3-39.6L86.6 502.6c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3l64-64c1.9-1.9 3.9-3.4 6-4.8C101.5 367.8 96 344.6 96 320l-64 0c-17.7 0-32-14.3-32-32s14.3-32 32-32l64.3 0c1.1-14.1 5-27.5 11.1-39.5c-.7-.6-1.4-1.2-2.1-1.9l-64-64c-12.5-12.5-12.5-32.8 0-45.3z"/></svg>';
        } elseif ($status == 'info') {
            echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 512"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M96 64c0-17.7-14.3-32-32-32S32 46.3 32 64l0 256c0 17.7 14.3 32 32 32s32-14.3 32-32L96 64zM64 480a40 40 0 1 0 0-80 40 40 0 1 0 0 80z"/></svg>';
        } else {
            return;
        }
        echo "</div>";
        echo esc_html($msg); 
        ?>
    </div>
    <?php
}

function bwp_can_warmup_cache($slug) {

    if (get_transient( 'bwp_warmup_lock_'.md5($slug) ) === false) {

        set_transient( 'bwp_warmup_lock_'.md5($slug), true, 100 );
        return true;
        
    }

    return false;
}

function bwp_clear_warmup_lock($slug) {
    delete_transient( 'bwp_warmup_lock_'.md5($slug) );
}