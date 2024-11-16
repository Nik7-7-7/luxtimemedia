<?php
if (!defined('ABSPATH'))
    exit;

if (!class_exists('berqCache')) {
    class berqCache
    {
        public $cache_file;
        public $max_page_per_batch = 5;

        function __construct()
        {
            $this->max_page_per_batch = apply_filters( 'berqwp_max_pages_per_batch', 5 );

            add_filter( 'berqwp_ignored_urls_params', [$this, 'ingore_tracking_params'] );

            // $this->cache_file = berqwp_current_page_cache_file();

            // clear cache after migration
            if (get_option('berqwp_site_url', home_url()) !== home_url()) {

                if (get_option('berqwp_site_url', home_url()) !== null) {
                    global $berq_log;
                    $berq_log->info('Home url change detected, flushing all cache');
                }

                $this->delete_cache_files();
                update_option('berqwp_site_url', home_url());
                delete_transient( 'berq_lic_response_cache' );
            }

            global $berqwp_is_dropin;
            if (empty($berqwp_is_dropin)) {
                add_action('template_redirect', [$this, 'html_cache'], 2);
            }

            // Add clear cache link to admin bar
            add_action('admin_bar_menu', [$this, 'add_clear_cache_link_to_admin_bar'], 999);

            // Flush cache
            add_action('admin_post_clear_cache', [$this, 'handle_clear_cache_action']);

            // Purge a page cache
            add_action('admin_post_berq_purge_page', [$this, 'handle_berq_purge_page_action']);

            // Request page cache
            add_action('admin_post_berq_request_cache', [$this, 'handle_berq_request_cache_action']);

            // Clear page cache on update
            add_action('save_post', [$this, 'clear_cache_on_post_udpate'], 10, 3);

            // Automattic cache warmup
            add_action('init', [$this, 'warmup_queue']);
            add_action('warmup_cache_by_slug', [$this, 'handle_warmup_cache_by_slug']);
            add_action('warmup_cache_quickly', 'warmup_cache_by_slug');
            add_action('admin_notices', [$this, 'cache_warmup_admin_notice']);


            add_action('wp_loaded', [$this, 'berqwp_warmup_cache_all_pages']);

            add_action('berqwp_before_update_optimization_mode', [$this, 'delete_cache_files']);

            // Reverse proxy cache support
            add_action('berqwp_stored_page_cache', [$this, 'flush_reverse_proxy_cache']);
            add_action('berqwp_flush_all_cache', [$this, 'flush_reverse_proxy_cache']);
            add_action('berqwp_flush_page_cache', [$this, 'flush_reverse_proxy_cache']);

            // Clear cache warmup lock after storing the cache
            add_action('berqwp_stored_page_cache', 'bwp_clear_warmup_lock');


        }

        function flush_reverse_proxy_cache($slug = '/.*') {

            if (empty($slug)) {
                $slug = '/.*'; // Purge all cache
            }

            if ($slug == '/') {
                $slug = '';
            }

            $page_url = home_url() . $slug;
            berqReverseProxyCache::purge_cache($page_url);
        }

        function ingore_tracking_params($tracking_params) {
            $tracking_params = array_merge($tracking_params, ignoreParams::$query_params);
            return $tracking_params;
        }

        function berqwp_warmup_cache_all_pages()
        {

            if (isset($_GET['run_warmup'])) {

                global $berq_log;
                $berq_log->info('* * * * * * * * * * * * *');
                $berq_log->info('* Manual Cache Warmup');
                $berq_log->info('* * * * * * * * * * * * *');

                // Get all published pages and posts
                $args = array(
                    'post_type' => array('page'),
                    'post_status' => 'publish',
                    'posts_per_page' => -1, // Get all posts
                );
                $query = new WP_Query($args);

                // API endpoint URL
                $api_endpoint = 'https://boost.berqwp.com/photon/';

                if (get_site_url() == 'http://berq-test.local') {
                    $api_endpoint = 'http://dev-berqwp.local/photon/';
                }

                $httpMulti = new bwp_multi_http();


                // Loop through each post/page and warm up the cache
                if ($query->have_posts()) {
                    while ($query->have_posts()) {
                        $query->the_post();
                        $url = trailingslashit(get_permalink()); // Get the permalink of the post/page

                        // $httpMulti->addRequest('GET', $url);
                        $slug = str_replace(home_url(), '', $url);
                        $post_data = berqwp_get_page_params($slug, true);

                        $httpMulti->addRequest('POST', $api_endpoint, $post_data);

                        $berq_log->info("Requesting cache for $slug");
                    }
                }

                $responses = $httpMulti->execute();
                wp_reset_postdata();

            }

        }

        function warmup_queue()
        {
            $wp_content_adv_cache = ABSPATH . 'wp-content/advanced-cache.php';
            $bwp_adv_cache = optifer_PATH . 'advanced-cache.php';

            if (
                (!file_exists($wp_content_adv_cache) && get_option('berqwp_enable_sandbox') !== 1)
                || (file_exists($wp_content_adv_cache) && md5_file($wp_content_adv_cache) !== md5_file($bwp_adv_cache))
                ) {
                // Specify the drop-in file path
                $dropin_file = ABSPATH . 'wp-content/advanced-cache.php';

                // Dynamically create the drop-in file
                $dropin_content = file_get_contents(optifer_PATH . 'advanced-cache.php');

                // Write the drop-in content to the file, replacing any existing file
                file_put_contents($dropin_file, $dropin_content);

                // Enable object cache in wp-config.php
                berqwp_enable_object_cache(true);

                if (function_exists('wp_cache_flush')) {
                    wp_cache_flush(); // Clear the entire object cache.
                }
            }

            if (get_option('berqwp_enable_sandbox') == 1 && file_exists($wp_content_adv_cache)) {
                unlink($wp_content_adv_cache);
            }

            if (false === as_has_scheduled_action('warmup_cache_by_slug')) {
                $post_types = get_option('berqwp_optimize_post_types');

                $args = array(
                    'post_type' => $post_types,
                    'posts_per_page' => -1,
                    'fields' => 'ids',
                    'post_status' => 'publish',
                );

                $query = new WP_Query($args);

                // Calculate the total number of pages
                $total_posts = count($query->posts);

                $posts_per_batch = $this->max_page_per_batch;
                $total_pages = ceil($total_posts / $posts_per_batch);


                // Schedule the events
                for ($page = 1; $page <= $total_pages; $page++) {
                    as_schedule_single_action(time() + (80 * $page), 'warmup_cache_by_slug', array($page));
                }

                wp_reset_postdata();
            }

            if (false === as_has_scheduled_action('warmup_cache_quickly') && bwp_is_home_cached() === false && function_exists('as_enqueue_async_action')) {
                as_enqueue_async_action('warmup_cache_quickly', ['/', true]);
            }



        }

        function can_send_warmup_request()
        {
            $requests_sent = get_transient('berqwp_warmup_requests_count');

            // If we haven't set the rate limiting transient yet, or if we're still below our rate, return true.
            if (false === $requests_sent || $requests_sent < BERQWP_MAX_WARMUP_REQUESTS) {
                return true;
            }

            return false;
        }

        function update_warmup_request_count()
        {
            $requests_sent = get_transient('berqwp_warmup_requests_count');

            if (false === $requests_sent) {
                // If this is the first request in our window, set the transient with a timeout.
                set_transient('berqwp_warmup_requests_count', 1, BERQWP_WARMUP_RATE_LIMIT_WINDOW);
            } else {
                // Otherwise, just increment the counter.
                set_transient('berqwp_warmup_requests_count', ++$requests_sent, BERQWP_WARMUP_RATE_LIMIT_WINDOW);
            }
        }



        function handle_warmup_cache_by_slug($page)
        {
            global $berq_log;
            $berq_log->info("handle_warmup_cache_by_slug triggered");

            // error_log('-page-' . $page);
            if ($this->can_send_warmup_request()) {
                // error_log('page-' . $page);
                $this->warmup_cache($page);
                $this->update_warmup_request_count();
            } else {
                $berq_log->info("handle_warmup_cache_by_slug cache skipped");
                // You've hit the rate limit.
                $retry_after_seconds = 200; // Retry after 10 minutes. You can adjust this time as needed.
                // as_schedule_single_action(time() + $retry_after_seconds, 'warmup_cache_by_slug', array($page));
            }
        }



        function clear_cache_on_post_udpate($post_id, $post, $update)
        {
            // If this is just a revision, don't run the function.
            if (wp_is_post_revision($post_id)) {
                return;
            }

            $post_url = get_permalink($post_id);
            $slug = str_replace(home_url(), '', $post_url);
            $cache_key = md5($slug);
            $cache_directory = optifer_cache . '/html/';
            $cache_file = $cache_directory . $cache_key . '.html';

            global $berq_log;
            $berq_log->info("Post updated, deleting cache for $post_url");

            if (file_exists($cache_file)) {
                unlink($cache_file);
            }

        }

        function flatten_array($array)
        {
            $result = array();
            foreach ($array as $item) {
                if (is_array($item)) {
                    $result = array_merge($result, $this->flatten_array($item));
                } else {
                    $result[] = $item;
                }
            }
            return $result;
        }

        function handle_berq_purge_page_action()
        {
            // Check if the user has the necessary nonce and the action matches
            if (isset($_GET['action']) && $_GET['action'] === 'berq_purge_page' && wp_verify_nonce($_GET['_wpnonce'], 'berq_purge_page_action')) {

                $slug = sanitize_text_field($_GET['uri']);
                $cache_file = optifer_cache . '/html/' . md5($slug) . '.html';
                
                if (is_file($cache_file)) {
                    unlink($cache_file);
                }

                $cache_file = optifer_cache . '/html/' . md5($slug) . '.gz';
                
                if (is_file($cache_file)) {
                    unlink($cache_file);
                }
                
                
                if (function_exists('wp_cache_flush')) {
                    wp_cache_flush(); // Clear the entire object cache.
                }

                do_action('berqwp_flush_page_cache', $slug);

                // Redirect back to the referring page after clearing the cache
                wp_safe_redirect(wp_get_referer());
                exit;
            }
        }
        
        function handle_berq_request_cache_action()
        {
            // Check if the user has the necessary nonce and the action matches
            if (isset($_GET['action']) && $_GET['action'] === 'berq_request_cache' && wp_verify_nonce($_GET['_wpnonce'], 'berq_request_cache_action')) {

                $slug = sanitize_text_field($_GET['uri']);

                as_enqueue_async_action('warmup_cache_quickly', [$slug, true]);
                
                // Redirect back to the referring page after clearing the cache
                wp_safe_redirect(wp_get_referer());
                exit;
            }
        }

        function handle_clear_cache_action()
        {
            // Check if the user has the necessary nonce and the action matches
            if (isset($_GET['action']) && $_GET['action'] === 'clear_cache' && wp_verify_nonce($_GET['_wpnonce'], 'clear_cache_action')) {
                // Call your custom cache clearing function here
                $this->delete_cache_files();

                if (function_exists('wp_cache_flush')) {
                    wp_cache_flush(); // Clear the entire object cache.
                }

                set_transient('berq_cache_cleared_notice', 'true', 60);

                $redirect_url = add_query_arg('berq_clear_cache', '', wp_get_referer());

                // Redirect back to the referring page after clearing the cache
                wp_safe_redirect($redirect_url);
                exit;
            }
        }

        function delete_cache_files()
        {
            global $berq_log;
            $berq_log->info("Flushing all cache.");

            // Define the cache directory
            $cache_directory = optifer_cache . '/html/';

            // Delete all cache files within the directory
            berqwp_unlink_recursive($cache_directory);

            // $files = glob($cache_directory . '*.html');
            // foreach ($files as $file) {
            //     if (is_file($file)) {
            //         unlink($file);
            //     }
            // }

            delete_transient('berqwp_warmup_running');
            delete_transient('cache_warmup_in_progress');
            delete_transient('berqwp_doing_cache_warmup');

            do_action('berqwp_flush_all_cache');
            // do_action('rt_nginx_helper_purge_all');
        }

        function add_clear_cache_link_to_admin_bar()
        {
            global $wp_admin_bar;

            // Check if the user has the capability to clear the cache (adjust the capability as needed)
            if (current_user_can('manage_options')) {
                $wp_admin_bar->add_menu(
                    array(
                        'id' => 'berqWP',
                        'title' => 'BerqWP',
                        'href' => get_admin_url() . '/admin.php?page=berqwp',
                    )
                );

                // Add the sub-menu item
                $wp_admin_bar->add_menu(
                    array(
                        'parent' => 'berqWP',
                        // ID of the parent menu item
                        'id' => 'flush-cache',
                        'title' => 'Flush cache',
                        'href' => wp_nonce_url(admin_url('admin-post.php?action=clear_cache'), 'clear_cache_action'),
                        'meta' => array(
                            'class' => 'clear-cache-link',
                            'title' => 'Clear BerqWP cache',
                        ),
                    )
                );

                if (!is_admin()) {
                    // Add the sub-menu item
                    $wp_admin_bar->add_menu(
                        array(
                            'parent' => 'berqWP',
                            // ID of the parent menu item
                            'id' => 'purge-page',
                            'title' => 'Purge this page',
                            'href' => wp_nonce_url(admin_url('admin-post.php?action=berq_purge_page&uri=' . urlencode($_SERVER['REQUEST_URI'])), 'berq_purge_page_action'),
                            'meta' => array(
                                'class' => 'purge-page-link',
                                'title' => 'Clear this page cache',
                            ),
                        )
                    );

                    // Add the request cache
                    $wp_admin_bar->add_menu(
                        array(
                            'parent' => 'berqWP',
                            // ID of the parent menu item
                            'id' => 'request-page-cache',
                            'title' => 'Request cache',
                            'href' => wp_nonce_url(admin_url('admin-post.php?action=berq_request_cache&uri=' . urlencode($_SERVER['REQUEST_URI'])), 'berq_request_cache_action'),
                            'meta' => array(
                                'class' => 'request-page-cache-link',
                                'title' => 'Request cache for this page',
                            ),
                        )
                    );

                }
            }
        }

        static function is_cache_file_expired($cache_file) {
            $cache_max_life = filemtime($cache_file) + (10 * 60 * 60);
            
            // is still valide
            if ($cache_max_life > time()) {
                return false;
            }

            return true;
        }


        function html_cache()
        {
            // Check if the current user is logged in or if it's a POST request
            if (is_user_logged_in() || $_SERVER['REQUEST_METHOD'] === 'POST') {
                return;
            }

            if (isset($_GET['generating_critical_css'])) {
                return;
            }

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

            if (in_array(home_url() . $slug_uri, $pages_to_exclude)) {
                return;
            }

            // Remove ignored params from the slug
            $slug = berqwp_remove_ignore_params($slug_uri);

            if (isset($_GET['creating_cache'])) {
                return;
            }

            if (get_option('berqwp_enable_sandbox') == 1 && isset($_GET['berqwp'])) {
                $slug = explode('?berqwp', $slug_uri)[0];
            } elseif (get_option('berqwp_enable_sandbox') == 1 && !isset($_GET['creating_cache'])) {
                return;
            }

            if (is_singular()) {
                $post_type = get_post_type();
    
                if (empty($post_type) || !in_array($post_type, get_option('berqwp_optimize_post_types'))) {
                    return;
                }
            } elseif (is_archive()) {
                $queried_object = get_queried_object();
        
                if ($queried_object instanceof WP_Term && !empty($queried_object->taxonomy)) {
                    $current_taxonomy = $queried_object->taxonomy;

                    if (!in_array($current_taxonomy, get_option('berqwp_optimize_taxonomies'))) {
                        return;
                    }
                }
            }


            // Attempt to retrieve the cached HTML from the cache directory
            $cache_directory = optifer_cache . '/html/';

            // Generate a unique cache key based on the current page URL
            $cache_key = md5($slug);
            $cache_file = $cache_directory . $cache_key . '.html';
            
            
            $status_code = http_response_code();
            
            if ($status_code !== 200) {
                return;
            }

            if (berqwp_is_slug_excludable($slug)) {
                return;
            }
            
            if (!isset($_GET['creating_cache'])) {
                if (strpos($slug, '?creating_cache') === false) {
                    // $page_modified_time = berqwp_get_last_modified_timestamp();
                    $cache_life_span = time() - (10 * 60 * 60);
                    
                    // Hook to modify cache lifespan
                    $cache_life_span = apply_filters('berqwp_cache_lifespan', $cache_life_span);
                    
                    if (bwp_is_gzip_supported() && file_exists($cache_directory . $cache_key . '.gz')) {
                        $cache_file = $cache_directory . $cache_key . '.gz';
                        header('Content-Encoding: gzip');
                        header('Content-Type: text/html; charset=UTF-8');
                    }

                    if (
                        (!file_exists($cache_file) ||
                        (file_exists($cache_file) && $this->is_cache_file_expired($cache_file)) ) 
                        && bwp_can_warmup_cache($slug)
                    ) {

                        global $berq_log;
                        $startTime = microtime(true);

                        $args = array(
                            'blocking' => false,
                            'timeout' => 0.01,
                            'body' => array(
                                'slug' => $slug,
                            ),
                        );

                        wp_remote_post(get_site_url() . '/wp-json/optifer/v1/warmup-cache', $args);

                        // Get end time
                        $endTime = microtime(true);

                        // Calculate runtime in milliseconds
                        $runtime = ($endTime - $startTime) * 1000;

                        $berq_log->info("Requesting cache for $slug took $runtime ms from frontend.");

                    }

                }
            }

            // If the cached HTML file exists, serve it and stop further execution
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

                } else {

                    header('Cache-Control: public, max-age=86400');

                    if (file_exists($cache_file)) {
                        readfile($cache_file);
                        exit();
                    }

                }
            }

            if (berqReverseProxyCache::is_reverse_proxy_cache_enabled()) {
                berqReverseProxyCache::handle_bypass();
            }

            return;

        }

        function warmup_cache($page)
        {
            global $berq_log;
            $berq_log->info("* * * * * * * * *");
            $berq_log->info("* Cache Warmup");
            $berq_log->info("* * * * * * * * *");

            $posts_per_batch = $this->max_page_per_batch;

            // if (get_transient('berqwp_doing_cache_warmup')) {
            //     return;
            // }

            // set_transient('berqwp_doing_cache_warmup', true, 120);

            // Set a transient to indicate that the cache warmup is in progress
            set_transient('cache_warmup_in_progress', true, 120);

            // warmup_cache_by_slug('/');

            // $post_types = get_post_types(array('publicly_queryable' => true), 'names');
            $post_types = get_option('berqwp_optimize_post_types');

            // API endpoint URL
            $api_endpoint = 'https://boost.berqwp.com/photon/';

            if (get_site_url() == 'http://berq-test.local') {
                $api_endpoint = 'http://dev-berqwp.local/photon/';
            }

            // Modify photon engine endpoint for testing purposes
            $api_endpoint = apply_filters( 'berqwp_photon_endpoint', $api_endpoint );

            // $post_types[] = 'product';
            $args = array(
                'post_type' => $post_types,
                'posts_per_page' => $posts_per_batch,
                'paged' => $page,
            );

            $query = new WP_Query($args);

            $httpMulti = new bwp_multi_http();

            $post_data = berqwp_get_page_params('/');
            $httpMulti->addRequest('POST', $api_endpoint, $post_data);

            // Loop through all posts and make a GET request
            while ($query->have_posts()) {
                $query->the_post();

                // Get the post URL and make a GET request
                $url = trailingslashit(get_permalink());
                $slug = str_replace(home_url(), '', $url);
                $post_data = berqwp_get_page_params($slug);

                // Skip if page is excluded from cache
                $pages_to_exclude = get_option('berq_exclude_urls', []);
                
                // Attempt to retrieve the cached HTML from the cache directory
                $cache_directory = optifer_cache . '/html/';
                
                // Generate a unique cache key based on the current page URL
                $cache_key = md5($slug);
                $cache_file = $cache_directory . $cache_key . '.html';
                $page_modified_time = berqwp_get_last_modified_timestamp();
                $cache_life_span = time() - (10 * 60 * 60);

                if (!file_exists($cache_file) && bwp_pass_account_requirement() === false) {
                    $berq_log->info("Exiting cache $slug");
                    continue;
                }
                
                if (in_array($url, $pages_to_exclude)) {

                    if (file_exists($cache_file)) {
                        unlink($cache_file);
                    }
                    
                    continue;
                }

                if (berqwp_is_slug_excludable($slug)) {
                    continue;
                }

                // Hook to modify cache lifespan
                $cache_life_span = apply_filters('berqwp_cache_lifespan', $cache_life_span);

                if (
                    !file_exists($cache_file) ||
                    ($this->is_cache_file_expired($cache_file)) ||
                    (file_exists($cache_file) && !empty($page_modified_time) && $page_modified_time > filemtime($cache_file))
                ) {
                    $httpMulti->addRequest('POST', $api_endpoint, $post_data);
    
                    $berq_log->info("Requesting cache for $slug");

                }


            }

            $responses = $httpMulti->execute();

            wp_reset_postdata();

            delete_transient('cache_warmup_in_progress');
            delete_transient('berqwp_doing_cache_warmup');

        }


        function cache_warmup_admin_notice()
        {


            if (get_transient('cache_warmup_in_progress')) {
                ?>
                <div class="notice notice-info is-dismissible">
                    <p>
                        <?php
                        esc_html_e('Cache warmup is in progress... This process could take some time depending on the number of pages on your website.', 'searchpro');
                        ?>
                    </p>
                </div>
                <?php
            }

            if (!get_transient('cache_warmup_in_progress') && isset($_GET['berq_warmingup'])) {
                ?>
                <div class="notice notice-info is-dismissible">
                    <p>
                        <?php esc_html_e('BerqWP is starting cache warmup. Please wait.', 'searchpro'); ?>
                    </p>
                </div>
                <?php
            }

            if (get_transient('berq_cache_cleared_notice')) {
                delete_transient('berq_cache_cleared_notice');
                ?>
                <div class="notice notice-success is-dismissible">
                    <p>
                        <?php esc_html_e('The cache has been cleared. Our automatic cache warm-up system will generate the cache. Alternatively, you can
                        visit any page to create its cache immediately.', 'searchpro'); ?>
                    </p>
                </div>
                <?php
            }
        }

    }

    $cache = new berqCache();

}
