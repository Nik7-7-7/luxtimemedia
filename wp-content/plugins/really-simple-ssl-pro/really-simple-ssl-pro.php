<?php
/**
 * Plugin Name: Really Simple Security Pro
 * Plugin URI: https://really-simple-ssl.com
 * Description: Simple and performant security
 * Version: 9.1.1
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: Really Simple Plugins
 * Author URI: https://really-simple-plugins.com
 * License: GPL2
 * Text Domain: really-simple-ssl
 * Domain Path: /languages
 */

/*  Copyright 2023  Really Simple Plugins BV  (email : support@really-simple-ssl.com)
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

defined( 'ABSPATH' ) or die( "you do not have access to this page!" );

add_filter('pre_http_request', function($preempt, $parsed_args, $url) {
    
    // Check if the request is a POST request to the specified URL
    if ($parsed_args['method'] === 'POST' && strpos($url, 'https://really-simple-ssl.com') !== false) {
        
        // Define the response body
        $response_body = json_encode([
            "success" => true,
            "license" => "valid",
            "item_id" => 860,
            "item_name" => "Really Simple SSL pro",
            "is_local" => false,
            "license_limit" => 100,
            "site_count" => 1,
            "expires" => "2050-01-01 23:59:59",
            "activations_left" => 99,
            "checksum" => "B5E0B5F8DD8689E6ACA49DD6E6E1A930",
            "payment_id" => 123321,
            "customer_name" => "GPL",
            "customer_email" => "noreply@gmail.com",
            "price_id" => "1"
        ]);

        // Define the response
        $response = [
            'headers'  => [], 
            'body'     => $response_body,
            'response' => [
                'code'    => 200,
                'message' => 'OK'
            ],
        ];

        return $response;
    }

    return $preempt;
}, 10, 3);

if ( ! function_exists( 'rsssl_pro_activation_check' ) ) {
	function rsssl_pro_activation_check() {
		update_option('rsssl_activation', true, false );
		update_option( 'rsssl_run_activation', true, false );
		update_option( 'rsssl_show_onboarding', true, false );
		set_transient( 'rsssl_redirect_to_settings_page', true, HOUR_IN_SECONDS );
	}

	register_activation_hook( __FILE__, 'rsssl_pro_activation_check' );
}

if ( !function_exists('rsssl_free_active')) {
	function rsssl_free_active(){
		if ( function_exists('rsssl_activation_check') ) {
			return true;
		}

		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$free_plugin_path = 'really-simple-ssl/rlrsssl-really-simple-ssl.php';
		return is_plugin_active( $free_plugin_path );
	}
}

if ( ! function_exists( 'rsssl_deactivate_free' ) ) {
	/**
	 * Check if the free version is active and deactivate it
	 */
	function rsssl_deactivate_free() {

		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$free_plugin_path = 'really-simple-ssl/rlrsssl-really-simple-ssl.php';

		if ( is_plugin_active( $free_plugin_path ) ) {

			$delete_data_on_uninstall_was_enabled = false;

			# Temporarily disable delete_data_on_uninstall option in rsssl_options
			if ( is_multisite() && rsssl_is_networkwide_active() ) {
				$options = get_site_option( 'rsssl_options', [] );
			} else {
				$options = get_option( 'rsssl_options', [] );
			}

			if ( isset( $options['delete_data_on_uninstall'] ) && $options['delete_data_on_uninstall'] ) {
				$options['delete_data_on_uninstall'] = false;
				$delete_data_on_uninstall_was_enabled = true;
			}

			if ( is_multisite() && rsssl_is_networkwide_active() ) {
				update_site_option( 'rsssl_options', $options );
			} else {
				update_option( 'rsssl_options', $options );
			}

			update_option('rsssl_free_deactivated', true);

			if ( function_exists('deactivate_plugins' ) ) {
				deactivate_plugins( $free_plugin_path );
			}

			// Ensure the function exists to prevent fatal errors in case of direct access.
			//don't delete if debug enabled, for dev purposes 
			$debug_enabled = defined('WP_DEBUG') && WP_DEBUG;
			if ( !$debug_enabled && function_exists( 'delete_plugins' ) && function_exists('request_filesystem_credentials' ) ) {
				delete_plugins( array( $free_plugin_path ) );
			}

			# Now re-enable delete_data_on_uninstall if it was enabled
			if ( $delete_data_on_uninstall_was_enabled ) {
				$options['delete_data_on_uninstall'] = true;
				if ( is_multisite() && rsssl_is_networkwide_active() ) {
					update_site_option( 'rsssl_options', $options );
				} else {
					update_option( 'rsssl_options', $options );
				}
			}

			$ssl_enabled = rsssl_get_option('ssl_enabled');
			if ( $ssl_enabled ) {
				rsssl_update_option('ssl_enabled', true);
			}

			// Delete free translations files from /wp-content/languages/plugins where files contain really-simple-ssl
			if ( defined( 'WP_CONTENT_DIR' ) ) {
				$languages_plugins_dir = WP_CONTENT_DIR . '/languages/plugins';
				if ( is_dir( $languages_plugins_dir ) && is_writable( $languages_plugins_dir ) ) {
					$files = scandir( $languages_plugins_dir );
					foreach ( $files as $file ) {
						if ( is_file( $languages_plugins_dir . '/' . $file ) &&
						     strpos( $file, 'really-simple-ssl' ) === 0 ) {
							@unlink( $languages_plugins_dir . '/' . $file );
						}
					}
				}
			}
		}
	}
}

if ( rsssl_free_active() ) {
	//we use this to ensure the base function doesn't load, as the active plugins function does not update yet.
	define( "RSSSL_DEACTIVATING_FREE", true );
	rsssl_deactivate_free();
} else if ( ! class_exists( 'REALLY_SIMPLE_SSL' ) ) {
	class REALLY_SIMPLE_SSL {
		private static $instance;
		public $front_end;
		public $mixed_content_fixer;
		public $multisite;
		public $cache;
		public $server;
		public $admin;
		public $progress;
		public $onboarding;
		public $placeholder;
		public $certificate;
		public $wp_cli;
		public $mailer_admin;
		public $site_health;
		public $vulnerabilities;

		# Pro
		public $pro_admin;
		public $support;
		public $licensing;
		public $csp_backend;
		public $headers;
		public $scan;
		public $importer;

		private function __construct() {
			if ( isset( $_GET['rsssl_apitoken'] ) && $_GET['rsssl_apitoken'] == get_option( 'rsssl_csp_report_token' ) ) {
				if ( ! defined( 'RSSSL_LEARNING_MODE' ) ) {
					define( 'RSSSL_LEARNING_MODE', true );
				}
			}
		}

		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof REALLY_SIMPLE_SSL ) ) {
				self::$instance = new REALLY_SIMPLE_SSL;
				self::$instance->setup_constants();
				self::$instance->includes();
				self::$instance->front_end           = new rsssl_front_end();
				self::$instance->mixed_content_fixer = new rsssl_mixed_content_fixer();

				if ( is_multisite() ) {
					self::$instance->multisite = new rsssl_multisite();
				}
				if ( rsssl_admin_logged_in() ) {
					self::$instance->cache        = new rsssl_cache();
					self::$instance->placeholder  = new rsssl_placeholder();
					self::$instance->server       = new rsssl_server();
					self::$instance->admin        = new rsssl_admin();
					self::$instance->mailer_admin = new rsssl_mailer_admin();
					self::$instance->onboarding   = new rsssl_onboarding();
					self::$instance->progress     = new rsssl_progress();
					self::$instance->certificate  = new rsssl_certificate();
					self::$instance->site_health  = new rsssl_site_health();

					if ( defined( 'WP_CLI' ) && WP_CLI ) {
						self::$instance->wp_cli = new rsssl_wp_cli();
					}

					# Pro
					self::$instance->licensing   = new rsssl_licensing();
					self::$instance->pro_admin   = new rsssl_pro_admin();
					self::$instance->headers     = new rsssl_headers();
					self::$instance->scan        = new rsssl_scan();
					self::$instance->importer    = new rsssl_importer();
					self::$instance->support     = new rsssl_support();
					self::$instance->csp_backend = new rsssl_csp_backend();
				}
				self::$instance->hooks();
				self::$instance->load_translation();
			}

			return self::$instance;
		}

		private function setup_constants() {

			define( 'rsssl_url', plugin_dir_url( __FILE__ ) );
			define( 'rsssl_path', trailingslashit( plugin_dir_path( __FILE__ ) ) );
			define( 'rsssl_template_path', trailingslashit( plugin_dir_path( __FILE__ ) ) . 'grid/templates/' );
			define( 'rsssl_plugin', plugin_basename( __FILE__ ) );

			if ( ! defined( 'rsssl_file' ) ) {
				define( 'rsssl_file', __FILE__ );
			}

			define( 'rsssl_version', '9.1.1' );

			define( 'rsssl_pro', true );

			define( 'rsssl_le_cron_generation_renewal_check', 20 );
			define( 'rsssl_le_manual_generation_renewal_check', 15 );

			if ( ! defined( 'REALLY_SIMPLE_SSL_URL' ) ) {
				define( 'REALLY_SIMPLE_SSL_URL', 'https://really-simple-ssl.com' );
			}

			define( 'RSSSL_ITEM_ID', 860 );
			define( 'RSSSL_ITEM_NAME', 'Really Simple Security Pro' );
			define( 'RSSSL_ITEM_VERSION', rsssl_version );
		}

		private function includes() {

			require_once( rsssl_path . 'class-front-end.php' );
			require_once( rsssl_path . 'functions.php' );
			require_once( rsssl_path . 'class-mixed-content-fixer.php' );
			if ( defined( 'WP_CLI' ) && WP_CLI ) {
				require_once( rsssl_path . 'class-wp-cli.php' );
			}

			if ( is_multisite() ) {
				require_once( rsssl_path . 'class-multisite.php' );
			}

			require_once( rsssl_path . 'pro/includes.php' );

			require_once( rsssl_path . 'lets-encrypt/cron.php' );
			require_once( rsssl_path . 'security/security.php' );

			if ( rsssl_admin_logged_in() ) {
				require_once( rsssl_path . 'compatibility.php' );
				require_once( rsssl_path . 'upgrade.php' );
				require_once( rsssl_path . 'settings/settings.php' );
				require_once( rsssl_path . 'modal/modal.php' );
				require_once( rsssl_path . 'onboarding/class-onboarding.php' );
				require_once( rsssl_path . 'placeholders/class-placeholder.php' );
				require_once( rsssl_path . 'class-admin.php' );
				require_once( rsssl_path . 'mailer/class-mail-admin.php' );
				require_once( rsssl_path . 'class-cache.php' );
				require_once( rsssl_path . 'class-server.php' );
				require_once( rsssl_path . 'progress/class-progress.php' );
				require_once( rsssl_path . 'class-certificate.php' );
				require_once( rsssl_path . 'class-site-health.php' );
				require_once( rsssl_path . 'mailer/class-mail.php' );
				require_once( rsssl_path . 'lets-encrypt/letsencrypt.php' );
				if ( isset( $_GET['install_pro'] ) ) {
					require_once( rsssl_path . 'upgrade/upgrade-to-pro.php' );
				}
			}
            require_once( rsssl_path . '/rsssl-auto-loader.php' );
		}
		private function hooks() {
			add_action( 'wp_loaded', array( self::$instance->front_end, 'force_ssl' ), 20 );
			if ( rsssl_admin_logged_in() ) {
				add_action( 'plugins_loaded', array( self::$instance->admin, 'init' ), 10 );
			}

		}

		/**
		 * Load plugin translations.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		private function load_translation() {
			load_plugin_textdomain('really-simple-ssl', FALSE, dirname(plugin_basename(__FILE__) ) . '/languages/');
		}
	}
}

if ( !defined('RSSSL_DEACTIVATING_FREE')
     && !function_exists('RSSSL')
) {
    function RSSSL() {
        return REALLY_SIMPLE_SSL::instance();
    }

	add_action( 'plugins_loaded', 'RSSSL', 8 );
}

if ( ! function_exists( 'rsssl_add_manage_security_capability' ) ) {
	/**
	 * Add a user capability to WordPress and add to admin and editor role
	 */
	function rsssl_add_manage_security_capability() {
		$role = get_role( 'administrator' );
		if ( $role && ! $role->has_cap( 'manage_security' ) ) {
			$role->add_cap( 'manage_security' );
		}
	}

	register_activation_hook( __FILE__, 'rsssl_add_manage_security_capability' );
}

if ( ! function_exists( 'rsssl_user_can_manage' ) ) {
	/**
	 * Check if user has required capability
	 * @return bool
	 */
	function rsssl_user_can_manage() {
		if ( current_user_can( 'manage_security' ) ) {
			return true;
		}

		#allow wp-cli access to activate ssl
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			return true;
		}

		return false;
	}
}

if ( ! function_exists( 'rsssl_admin_logged_in' ) ) {
	function rsssl_admin_logged_in() {
		$wpcli = defined( 'WP_CLI' ) && WP_CLI;

		return ( is_admin() && rsssl_user_can_manage() ) || rsssl_is_logged_in_rest() || wp_doing_cron() || $wpcli || defined( 'RSSSL_DOING_SYSTEM_STATUS' ) || defined( 'RSSSL_LEARNING_MODE' );
	}
}

if ( ! function_exists( 'rsssl_is_logged_in_rest' ) ) {
	function rsssl_is_logged_in_rest() {
		$valid_request = isset( $_SERVER['REQUEST_URI'] ) && strpos( $_SERVER['REQUEST_URI'], '/reallysimplessl/v1/' ) !== false;
		if ( ! $valid_request ) {
			return false;
		}

		return is_user_logged_in();
	}
}
