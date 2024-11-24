<?php //phpcs:ignore
/**
 * Plugin Name:       Webinar and Video Conference with Jitsi Meet
 * Plugin URI:        https://jitsi-meet-wp.wppool.dev/
 * Description:       Host live webinars, conferences, online classes, video calls directly on your WordPress website with gutenberg block
 * Version:           2.6.18
 * Author:            WPPOOL
 * Author URI:        https://wppool.dev
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       jitsi-meet-wp
 * Requires at least: 5.2
 * Tested up to:      6.7
 *
 *  @package JITSI_MEET_WP
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'JITSI_MEET_WP_VERSION', '2.6.18' );
define( 'JITSI_MEET_WP__FILE__', __FILE__ );
define( 'JITSI_MEET_WP_DIR_PATH', plugin_dir_path( JITSI_MEET_WP__FILE__ ) );
define( 'JITSI_MEET_WP_FILE_PATH', plugin_dir_path( __FILE__ ) );
define( 'JITSI_MEET_WP_URL', plugins_url( '', __FILE__ ) );
define( 'JITSI_MEET_WP_TEMPLATES', JITSI_MEET_WP_DIR_PATH . '/templates' );
define( 'JITSI_MEET_WP_ASSETS', JITSI_MEET_WP_URL . '/assets' );


/**
 * [jitsi_meet_wp_begin]
 *
 * @return  void
 */
function jitsi_meet_wp_begin() {
	$plugin_list    = get_option( 'active_plugins' );
	$jitsi_pro      = 'webinar-and-video-conference-with-jitsi-meet-pro/jitsi-meet-wp.php';
	$jitsi_ultimate = 'webinar-and-video-conference-with-jitsi-meet-ultimate/jitsi-meet-wp.php';
	$jitsi_premium  = 'webinar-and-video-conference-with-jitsi-meet-premium/jitsi-meet-wp.php';
	if ( ! in_array( $jitsi_pro, $plugin_list, true ) && ! in_array( $jitsi_ultimate, $plugin_list, true ) && ! in_array( $jitsi_premium, $plugin_list, true ) ) {
		/**
		 * Check for Gutenberg existence
		 * And prevent further execution if doesn't exist.
		 */
		if ( ! function_exists( 'register_block_type' ) ) {
			include_once JITSI_MEET_WP_DIR_PATH . 'init.php';
		} else {
			include_once JITSI_MEET_WP_DIR_PATH . 'init.php';
			include_once JITSI_MEET_WP_DIR_PATH . 'gutenberg-init.php';
			Jitsi_Meet_WP_Gutenberg::instance();
		}
	}
}

/**
 * Jitsi meet plugin activate.
 *
 * @return  void
 */
function jitsi_meet_plugin_activate() {
	update_option( 'jitsi_meet_wp_version', JITSI_MEET_WP_VERSION );
	set_transient( 'jitsi_meet_wp_review_notice_interval', 'off', 7 * DAY_IN_SECONDS );
	set_transient( 'jitsi_meet_wp_affiliate_notice_interval', 'off', 14 * DAY_IN_SECONDS );

	$install_date = get_option( 'jitsi_meet_wp_install_time' );

	if ( empty( $install_date ) ) {
		update_option( 'jitsi_meet_wp_install_time', time() );
	}

	if ( get_option( 'jitsi_meet_welcome_redirect' ) !== 'occured' ) {
		add_option( 'jitsi_meet_welcome_redirect', true );
	}
}

register_activation_hook( __FILE__, 'jitsi_meet_plugin_activate' );

add_action( 'plugins_loaded', 'jitsi_meet_wp_begin', 20 );


/**
 * Initialize the plugin tracker
 *
 * @return mixed
 */
function jitsi_meet_wp_appsero_init_tracker() {
	if ( ! class_exists( 'Appsero\Client' ) ) {
		require_once __DIR__ . '/inc/appsero/src/Client.php';
	}

	$client = new Appsero\Client( '00788961-f5f7-4117-8a26-a99508aa506b', 'Webinar and Video Conference with Jitsi Meet', JITSI_MEET_WP__FILE__ );

	// Active insights.
	$client->insights()->init();
}

/**
 * Initialize the wppool sdk
 *
 * @return void
 */
function jitsi_meet_wppool_sdk_init() {
	// Require WPPOOL SDK files.

	if ( file_exists( JITSI_MEET_WP_FILE_PATH . 'inc/wppool/class-plugin.php' ) ) {
		require_once JITSI_MEET_WP_FILE_PATH . 'inc/wppool/class-plugin.php';
	}

	if ( function_exists( 'wppool_plugin_init' ) ) {
		$jitsi_meet_plugin = wppool_plugin_init( 'webinar_and_video_conference_with_jitsi_meet', plugin_dir_url( JITSI_MEET_WP__FILE__ ) . '/inc/wppool/background-image.png' );

		if ( $jitsi_meet_plugin && is_object( $jitsi_meet_plugin ) && method_exists( $jitsi_meet_plugin, 'set_campaign' ) ) {
			$to       = '2024-12-04 16:00:00';
			$from     = '2024-11-21 16:00:00';
			$cta_text = esc_html__( 'Grab Your Deals', 'jitsi-meet-wp' );

			$campain_image = plugin_dir_url( JITSI_MEET_WP__FILE__ ) . '/inc/wppool/black-friday.png';
			$jitsi_meet_plugin->set_campaign( $campain_image, $to, $from, $cta_text );
		}
	}
}

jitsi_meet_wp_appsero_init_tracker();
jitsi_meet_wppool_sdk_init();
