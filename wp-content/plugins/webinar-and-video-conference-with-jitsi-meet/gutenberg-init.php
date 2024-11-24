<?php //phpcs:ignore
/**
 *  Exit if accessed directly
 *
 * @package JITSI_MEET_WP
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Make sure the same class is not loaded twice.
if ( ! class_exists( 'Jitsi_Meet_WP_Gutenberg' ) ) {

	/**
	 * Main Jiti Meet WP Class
	 *
	 * The main class that initiates and runs the Jitsi Meet WP plugin.
	 *
	 * @since 1.0.0
	 */
	class Jitsi_Meet_WP_Gutenberg {
		/**
		 * Instance
		 *
		 * Holds a single instance of the `Jitsi_Meet_WP` class.
		 *
		 * @since 1.0.0
		 *
		 * @access private
		 * @static
		 *
		 * @var Jitsi_Meet_WP A single instance of the class.
		 */
		private static $instance = null;

		/**
		 * Instance
		 *
		 * Ensures only one instance of the class is loaded or can be loaded.
		 *
		 * @return Jitsi_Meet_WP An instance of the class.
		 * @since 1.0.0
		 *
		 * @access public
		 * @static
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Clone
		 *
		 * Disable class cloning.
		 *
		 * @return void
		 * @since 1.0.0
		 *
		 * @access protected
		 */
		public function __clone() {
			// Cloning instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'jitsi-meet-wp' ), '1.0.0' );
		}

		/**
		 * Wakeup
		 *
		 * Disable unserializing the class.
		 *
		 * @return void
		 * @since 1.7.0
		 *
		 * @access protected
		 */
		public function __wakeup() {
			// Unserializing instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'jitsi-meet-wp' ), '1.0.0' );
		}

		/**
		 * Constructor
		 *
		 * Initialize the Jitsi Meet WP plugins.
		 *
		 * @since 1.0.0
		 *
		 * @access public
		 */
		public function __construct() {
			add_action( 'enqueue_block_editor_assets', array( $this, 'jitsi_meet_wp_gutenberg_blocks' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'jitsi_meet_wp_gutenberg_editor_assets' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'jitsi_meet_wp_gutenberg_front_assets' ) );
		}

		/**
		 * Enqueue block script
		 *
		 * @since 1.0.0
		 *
		 * @access public
		 */
		public function jitsi_meet_wp_gutenberg_blocks() {
			wp_register_script( 'jitsi-meet-wp-block', plugins_url( '/blocks/dist/blocks.build.js', __FILE__ ), array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-i18n' ), filemtime( plugin_dir_path( __FILE__ ) . '/blocks/dist/blocks.build.js' ), false );
			wp_localize_script(
				'jitsi-meet-wp-block',
				'jitsi',
				array(
					'meeting_width'       => get_option( 'jitsi_opt_width', 1080 ),
					'meeting_height'      => get_option( 'jitsi_opt_height', 720 ),
					'startwithaudiomuted' => get_option( 'jitsi_opt_start_local_audio_muted', 0 ) ? 1 : 0,
					'startwithvideomuted' => get_option( 'jitsi_opt_startWithVideoMuted', 0 ) ? 1 : 0,
					'startscreensharing'  => get_option( 'jitsi_opt_startScreenSharing', 0 ) ? 1 : 0,
					'invite'              => get_option( 'jitsi_opt_invite', 1 ) ? 1 : 0,
					'domain'              => $this->get_random_domain(),
				)
			);
			wp_enqueue_script( 'jitsi-meet-wp-block' );
		}

		/**
		 * Enqueue editor assets
		 *
		 * @since 1.0.0
		 *
		 * @access public
		 */
		public function jitsi_meet_wp_gutenberg_editor_assets() {
			wp_enqueue_style( 'jitsi-meet-wp-editor-css', plugins_url( '/blocks/dist/blocks.editor.build.css', __FILE__ ), array(), filemtime( plugin_dir_path( __FILE__ ) . '/blocks/dist/blocks.editor.build.css' ) );
		}

		/**
		 * Enqueue frontend assets
		 *
		 * @since 1.0.0
		 *
		 * @access public
		 */
		public function jitsi_meet_wp_gutenberg_front_assets() {
			$jisit_css_ver = gmdate( 'ymd-Gis', filemtime( plugin_dir_path( __FILE__ ) . '/blocks/dist/blocks.style.build.css' ) );

			wp_enqueue_style( 'jitsi-meet-wp', plugins_url( '/blocks/dist/blocks.style.build.css', __FILE__ ), false, $jisit_css_ver );

			if ( is_singular() ) {
				wp_enqueue_script( 'jitsi-8x8-api', 'https://8x8.vc/external_api.js', null, '2.1.2', false );
				wp_enqueue_script( 'jitsi-script', plugins_url( '/blocks/dist/jitsi.js', __FILE__ ), array( 'jquery', 'wp-blocks' ), filemtime( plugin_dir_path( __FILE__ ) . '/blocks/dist/jitsi.js' ), '2.1.2' );
			}
		}

		/**
		 * Get_random_domain.
		 *
		 * This function returns a randomly selected domain from a predefined list of video conferencing domains.
		 *
		 * @return string The randomly selected domain.
		 */
		public function get_random_domain() {
			$domains = array(
				'jitsi.member.fsf.org',
				'meet.evolix.org',
				'webconf.viviers-fibre.net',
				'video.devloprog.org',
			);

			// Shuffle the array..
			shuffle( $domains );

			// Return a random domain.
			return $domains[ array_rand( $domains ) ];
		}
	}
}
