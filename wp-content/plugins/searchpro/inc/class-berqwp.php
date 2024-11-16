<?php
if (!defined('ABSPATH'))
	exit;

if (!class_exists('berqWP')) {
	class berqWP
	{

		public $is_key_verified = false;
		public $key_response = false;

		public $conflicting_plugins = [
			'autoptimize/autoptimize.php', // Autoptimize
			'wp-super-cache/wp-cache.php', // WP Super Cache
			'w3-total-cache/w3-total-cache.php', // W3 Total Cache
			'wp-fastest-cache/wpFastestCache.php', // WP Fastest Cache
			'litespeed-cache/litespeed-cache.php', // LiteSpeed Cache
			'cache-enabler/cache-enabler.php', // Cache Enabler
			'hummingbird-performance/wp-hummingbird.php', // Hummingbird – Speed up, Cache, Optimize Your CSS and JS
			'sg-cachepress/sg-cachepress.php', // SiteGround Optimizer (for those hosted on SiteGround)
			'wp-rocket/wp-rocket.php', // WP Rocket
			'breeze/breeze.php', // Breeze (Cloudways)
			'comet-cache/comet-cache.php', // Comet Cache
			'hyper-cache/plugin.php', // Hyper Cache
			'simple-cache/simple-cache.php', // Simple Cache
			'wp-optimize/wp-optimize.php', // WP-Optimize
			'swift-performance-lite/performance.php', // Swift Performance Lite
			'nitropack/nitropack.php', // NitroPack
			'jetpack-boost/jetpack-boost.php', // Jetpack Boost
			'tenweb-speed-optimizer/tenweb_speed_optimizer.php', // 10Web Booster
			'speed-booster-pack/speed-booster-pack.php', // Speed booster pack
			'wp-speed-of-light/wp-speed-of-light.php', // WP speed of light
			'speedycache/speedycache.php', // Speedy cache
			'powered-cache/powered-cache.php', // Powered cache
			'clearfy/clearfy.php', // Clearfy
			'rabbit-loader/rabbit-loader.php',
			'psn-pagespeed-ninja/pagespeedninja.php',
			'jch-optimize/jch-optimize.php',
			'cache-enabler/cache-enabler.php',
			'core-web-vitals-pagespeed-booster/core-web-vitals-pagespeed-booster.php',
			'surge/surge.php',
			'speedien/speedien.php',
			'wpspeed/wpspeed.php',
			'debloat/debloat.php',
			'perfmatters/perfmatters.php',
			'phastpress/phastpress.php',
			// 'hide-my-wp/index.php',
		];

		function __construct()
		{

			add_action('init', [$this, 'initialize']);

			// Save settings
			add_action('admin_init', [$this, 'save_settings']);

			require_once optifer_PATH . '/api/register_apis.php';

			add_action('admin_menu', [$this, 'register_menu']);
			// add_action('init', [$this, 'berq_post_types'], 20);
			add_action('admin_notices', [$this, 'notices']);

			add_filter('plugin_action_links_searchpro/berqwp.php', [$this, 'plugin_settings_links']);

			add_action('wp_ajax_berqwp_fetch_remote_html', [$this, 'fetch_remote_html']);
		}

		function fetch_remote_html() {

			check_ajax_referer('wp_rest', 'nonce');
			
			$url = get_option('berqwp_enable_sandbox') ? home_url().'/?berqwp' : home_url();
		
			// $response = wp_remote_get($url);
			$response = bwp_wp_remote_get($url);
		
			if (is_array($response) && !is_wp_error($response)) {
				$html = wp_remote_retrieve_body($response);
				echo $html;
			} else {
				echo 'Error fetching HTML.';
			}
		
			die(); // Always exit in AJAX functions
		}

		function activate_license_from_multi_site()
		{
			if (berq_is_localhost()) {
				return;
			}

			$berqwp_license_key_from_parent = constant('BERQWP_LICENSE_KEY');

			if (!empty($berqwp_license_key_from_parent) && empty(get_option('berqwp_license_key'))) {
				$key = sanitize_text_field($berqwp_license_key_from_parent);
				$key_response = $this->verify_license_key($key, 'slm_activate');


				if (!empty($key_response) && $key_response->result == 'success') {
					update_option('berqwp_license_key', $key);

					if (is_admin()) {
						?>
						<div class="notice notice-success is-dismissible">
							<?php esc_html_e('The BerqWP license has been activated for your parent multisite.', 'searchpro'); ?>
						</div>
						<?php
					}

				} elseif ($key_response->result == 'error') {
					$error = $key_response->message;

					if (is_admin()) {
						?>
						<div class="notice notice-error is-dismissible">
							<?php echo esc_html($error); ?>
						</div>
						<?php

					}
				}
			}
		}

		function initialize()
		{

			// Set default settings
			require_once optifer_PATH . '/inc/initialize.php';

			// Activate the license from parent site
			if (defined('BERQWP_LICENSE_KEY')) {
				$this->activate_license_from_multi_site();
			}

			if (is_admin()) {
				$this->berq_post_types();
			}

			if (get_option('berqwp_disable_emojis') == 1) {
				$this->disable_emoji();
			}

			if (is_admin() && !empty(get_option('berqwp_license_key'))) {
				$license_key = get_option('berqwp_license_key');
				$key_response = $this->verify_license_key($license_key);

				if (!empty($key_response) && $key_response->result == 'success' && $key_response->status == 'active') {
					$this->is_key_verified = true;
					$this->key_response = $key_response;

				} else {
					$this->is_key_verified = false;
				}
			}

			// redirect to berqwp admin page
			if (get_transient('berqwp_redirect')) {
				delete_transient('berqwp_redirect');
				// Set the URL to redirect to after activation
				$redirect_url = admin_url('admin.php?page=berqwp');

				// Redirect after activation
				wp_redirect($redirect_url);

				// Make sure to exit after the redirect
				exit;
			}

			// Deactivate conflicting plugins
			if (is_admin() && isset($_POST['berqwp_plugins_deactivate']) && wp_verify_nonce($_POST['berqwp_plugins_deactivate'], 'berqwp_plugins_deactivate')) {
				foreach ($this->conflicting_plugins as $plugin) {
					// Deactivate each conflicting plugin
					if (is_plugin_active($plugin)) {
						deactivate_plugins($plugin);
					}
				}
				header('location: ' . esc_url(get_site_url() . add_query_arg($_GET)));
				exit;
			}

		}

		function save_settings()
		{
			require_once optifer_PATH . '/admin/save-settings.php';
		}

		function berqwp_cleanup_completed_and_failed_tasks()
		{

			if (!get_transient('berqwp_action_cleanup')) {
				set_transient('berqwp_action_cleanup', true, 60 * 60 * 10);

				global $berq_log;
				$berq_log->info("* * * * * * * * *");
				$berq_log->info("* Task Cleanup");
				$berq_log->info("* * * * * * * * *");

				global $wpdb;

				// Define the hook name
				$hook_name = 'warmup_cache_by_slug';

				// Count the number of completed and failed actions for the given hook
				$count = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(*) FROM {$wpdb->prefix}actionscheduler_actions WHERE hook = %s AND (status = 'complete' OR status = 'failed')",
						$hook_name
					)
				);

				$berq_log->info("Completed + failed task count: $count");
				
				// Check if the count exceeds 50
				if ($count > 50) {
					$delete_count = $count - 50; // Calculate how many rows to delete

					$berq_log->info("Deleting last $delete_count");

					$wpdb->query(
						$wpdb->prepare(
							"DELETE FROM {$wpdb->prefix}actionscheduler_actions WHERE hook = %s AND (status = 'complete' OR status = 'failed') ORDER BY scheduled_date_gmt ASC LIMIT %d",
							$hook_name,
							$delete_count
						)
					);
				}

				// clear logs, keep the last 1000
				if ( class_exists( 'ActionScheduler_QueueRunner' ) ) {
					global $wpdb;
					$table_name = $wpdb->prefix . 'actionscheduler_logs';
				
					// Count the total number of logs
					$total_logs = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );
				
					// Set the limit to keep the latest 1000 logs
					$limit = max( 0, $total_logs - 1000 );
				
					// Delete logs beyond the limit
					$wpdb->query( "DELETE FROM $table_name WHERE log_date_gmt < (SELECT log_date_gmt FROM $table_name ORDER BY log_date_gmt DESC LIMIT 1 OFFSET $limit)" );
				}

				$berq_log->info("* * * * * * * * * *");
				$berq_log->info("* Task Cleanup End");
				$berq_log->info("* * * * * * * * * *");

			}
		}

		function notices()
		{

			if (isset($_GET['dismiss_feedback'])) {
				set_transient( 'bqwp_hide_feedback_notice', true, DAY_IN_SECONDS * 2 );
			}

			if (isset($_GET['page']) && $_GET['page'] == 'berqwp' && !get_transient( 'bqwp_hide_feedback_notice' ) && $this->is_key_verified) {
				$notice = '<div class="notice notice-info bwp_feedback">';
				$notice .= '<p>';
				$notice .= __('🎉 <b>Loving BerqWP\'s performance? 🚀</b> Show some love and help us grow 👉 - <a href="https://wordpress.org/support/plugin/searchpro/reviews/#new-post" target="_blank">Rate BerqWP Plugin</a>. Your insights shape our journey.', 'searchpro');
				$notice .= '<a href="'.get_admin_url().'admin.php?page=berqwp&dismiss_feedback" style="display: table;margin-left: 50px;color: #969595;display: table;">Dismiss</a>';
				$notice .= '</p>';
				$notice .= '</div>';
				echo wp_kses_post($notice);
			}

			if (berq_is_localhost()) {
				?>
				<div class="notice notice-warning">
					<?php
					echo wp_kses_post(__("<p><b>Localhost Detected:</b> BerqWP doesn't operate in a localhost environment.</p>", 'searchpro'));
					?>
				</div>
				<?php
			}


			$plugins_to_deactivate = '';

			foreach ($this->conflicting_plugins as $plugin) {
				if (is_plugin_active($plugin)) {
					$plugins_to_deactivate .= '<li><b>' . basename(dirname($plugin)) . '</b></li>';
				}
			}

			if (!empty($plugins_to_deactivate)) {
				echo "<style>.berqwp-plugin-conflict ul {
					list-style: disc;
					margin-left: 20px;
				}.berqwp-plugin-conflict form {
					padding: 10px;
				}
				.berqwp-plugin-conflict {
					display: grid;
					grid-template-columns: auto min-content;
				}</style>";
				echo '<div class="notice notice-error berqwp-plugin-conflict">';
				echo wp_kses_post(__('<p><strong>BerqWP Plugin Conflict:</strong> The following plugins have a same nature as BerqWP plugin. Having multiple plugins of the same type can cause unexpected results.</p>', 'searchpro'));
				?>
				<form action="<?php echo esc_url(get_site_url() . add_query_arg($_GET)); ?>" method="post">

					<?php
					$my_nonce = wp_create_nonce('berqwp_plugins_deactivate');
					echo '<input type="hidden" name="berqwp_plugins_deactivate" value="' . esc_attr($my_nonce) . '" />';
					?>

					<input type="submit" class="button-secondary alignright" value="Deactivate Conflicting Plugins">
				</form>
				<?php
				echo wp_kses_post("<ul>$plugins_to_deactivate</ul>");
				echo '</div>';
			}
		}

		function berq_post_types()
		{
			// Get post type names
			$post_type_names = get_post_types(array(
				'public' => true,
				'exclude_from_search' => false,
			), 'names');
			unset($post_type_names['attachment']);

			// var_dump($post_type_names);

			// Modify which post types to optimize
			$post_type_names = apply_filters( 'berqwp_post_types', $post_type_names );

			// Save the names in a WordPress option
			update_option('berqwp_post_type_names', $post_type_names);

			//  Cleanup actions
			$this->berqwp_cleanup_completed_and_failed_tasks();
		}

		function plugin_settings_links($links)
		{
			$mylinks = array(
				'<a target="_blank" href="https://berqwp.com/help-center/">' . __('Help Center', 'searchpro') . '</a>',
				'<a href="' . admin_url('admin.php?page=berqwp') . '">' . __('Settings', 'searchpro') . '</a>',
			);

			return array_merge($links, $mylinks);
		}

		function verify_license_key($license_key, $action = 'slm_check') {
			if (empty($license_key)) {
				return;
			}
			
			global $berq_log;
			$transient_key = 'berq_lic_response_cache';
			
			if ($action !== 'slm_check') {
				delete_transient($transient_key);
			}

			$cached_response = get_transient($transient_key);

			if (false === $cached_response) {
				$berq_log->info('Simulating license key check.');

				$cached_response = (object) [
					'result' => 'success',
					'status' => 'active',
					'registered_domain' => $_SERVER['SERVER_NAME']
				];

				if ($action == 'slm_check') {
					set_transient($transient_key, $cached_response, 48 * HOUR_IN_SECONDS);
				}
			} else {
				$berq_log->info('Delivering license key object from the transient cache.');
			}

			return $cached_response;
		}

		// Disable emoji functionality
		function disable_emoji()
		{
			// Remove emoji-related actions and filters
			remove_action('wp_head', 'print_emoji_detection_script', 7);
			remove_action('admin_print_scripts', 'print_emoji_detection_script');
			remove_action('wp_print_styles', 'print_emoji_styles');
			remove_action('admin_print_styles', 'print_emoji_styles');
			remove_filter('the_content_feed', 'wp_staticize_emoji');
			remove_filter('comment_text_rss', 'wp_staticize_emoji');
			remove_filter('wp_mail', 'wp_staticize_emoji_for_email');

			// Remove emoji-related TinyMCE plugins
			add_filter('tiny_mce_plugins', [$this, 'disable_emoji_tinymce']);
		}

		// Filter function to disable emoji-related TinyMCE plugins
		function disable_emoji_tinymce($plugins)
		{
			if (is_array($plugins)) {
				return array_diff($plugins, array('wpemoji'));
			} else {
				return array();
			}
		}

		function get_media_ids(WP_REST_Request $request)
		{
			require_once optifer_PATH . 'api/get_media_ids.php';
		}

		function optimize_images(WP_REST_Request $request)
		{
			require_once optifer_PATH . 'api/optimize_images.php';
		}


		function delete_images(WP_REST_Request $request)
		{
			require_once optifer_PATH . 'api/delete_images.php';
		}

		function clear_cache(WP_REST_Request $request)
		{
			require_once optifer_PATH . 'api/clear_cache.php';
		}

		function warmup_cache(WP_REST_Request $request)
		{
			require_once optifer_PATH . 'api/warmup_cache.php';
		}

		function store_webp(WP_REST_Request $request)
		{
			require_once optifer_PATH . 'api/store_webp.php';
		}

		function store_cache(WP_REST_Request $request)
		{
			require_once optifer_PATH . 'api/store_cache.php';
		}

		function store_javascript_cache(WP_REST_Request $request)
		{
			require_once optifer_PATH . 'api/store_javascript_cache.php';
		}

		function register_menu()
		{
			$svg = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path fill-rule="evenodd" clip-rule="evenodd" d="M6.43896 0H17.561C21.1172 0 24 2.88287 24 6.43903V17.561C24 21.1171 21.1172 24 17.561 24H6.43896C2.88281 24 0 21.1171 0 17.561V6.43903C0 2.88287 2.88281 0 6.43896 0ZM15.7888 4.09753L8.59961 12.7534H12.3517L7.02441 20.4878L16.3903 11.0222L12.7814 10.3799L15.7888 4.09753Z" fill="#1F71FF"/>
			</svg>';

			add_menu_page('BerqWP', 'BerqWP', 'manage_options', 'berqwp', [$this, 'admin_page'], 'data:image/svg+xml;base64,' . base64_encode($svg), 10);
		}

		function admin_page()
		{
			if ($this->is_key_verified) {
				require_once optifer_PATH . 'admin/admin-page.php';
			} else {
				require_once optifer_PATH . 'admin/intro-page.php';

			}

		}


	}

	global $berqWP;
	$berqWP = new berqWP();
}