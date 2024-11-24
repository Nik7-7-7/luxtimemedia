<?php //phpcs:ignore
/**
 * Exit if accessed directly
 *
 * @package JITSI_MEET_WP
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Make sure the same class is not loaded twice.
if ( ! class_exists( 'Jitsi_Meet_WP' ) ) {
	/**
	 * Main Jiti Meet WP Class
	 *
	 * The main class that initiates and runs the Jitsi Meet WP plugin.
	 *
	 * @since 1.0.0
	 */
	class Jitsi_Meet_WP {

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
			add_action( 'init', array( $this, 'i18n' ) );
			add_action( 'admin_notices', array( $this, 'print_notices' ), 15 );
			add_filter( 'clean_url', array( $this, 'add_async_forscript' ), 11, 1 );
			add_action( 'admin_enqueue_scripts', array( $this, 'jitsi_meet_wp_editor_scripts' ) );
			$this->jitsi_meet_wp_admin_files();
			add_filter( 'plugin_action_links_' . plugin_basename( JITSI_MEET_WP__FILE__ ), array( $this, 'jitsi_meet_add_action_links' ) );
			add_shortcode( 'jitsi-meet-wp', array( $this, 'jitsi_shortcode_render' ) );
			$this->jitsi_init_elementor();
		}

		/**
		 * Get template
		 *
		 * @param string $name  Template name.
		 *
		 * @return  boolean
		 */
		public function get_template( $name = null ) {

			$template = locate_template( 'webinar-and-video-conference-with-jitsi-meet/' . $name . '.php' );

			if ( ! $template ) {
				$template = JITSI_MEET_WP_TEMPLATES . "/$name.php";
			}

			if ( file_exists( $template ) ) {
				include $template;
			} else {
				return false;
			}
		}

		/**
		 * Add jitsi meet notice
		 *
		 * @param  mixed  $jm_class class.
		 * @param string $message  display message.
		 *
		 * @return  void
		 */
		public function add_notice( $jm_class, $message ) {
			$notices = get_option( sanitize_key( 'jitsi_meet_wp_notices' ), array() );
			if ( is_string( $message ) && is_string( $jm_class )
				&& ! wp_list_filter( $notices, array( 'message' => $message ) ) ) {
				$notices[] = array(
					'message' => $message,
					'class'   => $jm_class,
				);

				update_option( sanitize_key( 'jitsi_meet_wp_notices' ), $notices );
			}
		}

		/**
		 * Display jitsi meet notice
		 *
		 * @return  void
		 */
		public function print_notices() {
			$notices = get_option( sanitize_key( 'jitsi_meet_wp_notices' ), array() );
			foreach ( $notices as $notice ) {
				?>
				<div class="notice notice-large is-dismissible notice-<?php echo esc_attr( $notice['class'] ); ?>">
					<?php echo wp_kses_post( $notice['message'] ); ?>
				</div>
				<?php
				update_option( sanitize_key( 'jitsi_meet_wp_notices' ), array() );
			}
		}

		/**
		 * Load jitsi meet admin files
		 *
		 * @return  void
		 */
		public function jitsi_meet_wp_admin_files() {
			if ( is_admin() ) {
				require_once JITSI_MEET_WP_FILE_PATH . 'inc/admin/class-admin.php';
			}
		}

		/**
		 * Plugin action links
		 *
		 * Jitsi_meet_add_action_links method use for show jitsi meet GET PRO button
		 *
		 * @param array $actions The plugin links.
		 *
		 * @return  mixed
		 */
		public function jitsi_meet_add_action_links( $actions ) {
			$mylinks = array(
				'<a href="' . esc_url( admin_url( 'admin.php?page=jitsi-meet' ) ) . '">' . esc_html( 'Settings' ) . '</a>',
				'<a class="special-action-link" target="_blank" href="' . esc_url( 'https://go.wppool.dev/Gv5' ) . '">' . esc_html( 'Upgrade to Pro' ) . '</a>',
			);
			$actions = array_merge( $actions, $mylinks );
			return $actions;
		}

		/**
		 * Load Textdomain
		 *
		 * Load plugin localization files.
		 *
		 * @since 1.0.0
		 *
		 * @access public
		 */
		public function i18n() {
			load_plugin_textdomain( 'jitsi-meet-wp', false, plugin_basename( __DIR__ ) . '/languages' );
		}

		/**
		 * Editor Scripts
		 */
		public function jitsi_meet_wp_editor_scripts() {
			wp_enqueue_script( 'jitsi-admin-script', plugins_url( '/blocks/dist/jitsi.admin.js', __FILE__ ), array( 'jquery' ), filemtime( plugin_dir_path( __FILE__ ) . '/blocks/dist/jitsi.admin.js' ), true );
			wp_enqueue_script( 'jquery.syotimer', plugins_url( '/assets/js/jquery.syotimer.min.js', __FILE__ ), array( 'jquery' ), '3.1.1', true );

			wp_localize_script(
				'jitsi-admin-script',
				'jitsiMeet',
				array(
					'url'   => esc_url( admin_url( 'admin-ajax.php' ) ),
					'nonce' => wp_create_nonce( 'jitsi_meet_admin_nonce' ),
				)
			);
		}

		/**
		 * Add async forscript
		 *
		 * @param string $url  forscript url.
		 *
		 * @return string
		 */
		public function add_async_forscript( $url ) {
			if ( strpos( $url, '#asyncload' ) === false ) {
				return $url;
			} else {
				return str_replace( '#asyncload', '', $url ) . "' async='async";
			}
		}

		/**
		 * Render meet shortcode
		 *
		 * @param array $args [$args description].
		 *
		 * @return array
		 */
		public function jitsi_shortcode_render( $args ) {
			$params = shortcode_atts(
				array(
					'name'                => '',
					'width'               => 1080,
					'height'              => 720,
					'startwithaudiomuted' => get_option( 'jitsi_opt_start_local_audio_muted', 0 ) ? true : false,
					'startwithvideomuted' => get_option( 'jitsi_opt_startWithVideoMuted', 0 ) ? true : false,
					'startscreensharing'  => get_option( 'jitsi_opt_startScreenSharing', 0 ) ? true : false,
					'invite'              => get_option( 'jitsi_opt_invite', 1 ) ? true : false,
				),
				$args
			);

			if ( is_user_logged_in() ) {
				$current_user          = wp_get_current_user();
				$user_info             = $current_user->display_name . ',' . $current_user->user_email;
				$param_arr['userinfo'] = $user_info;
			}

			// Sanitize all user-provided data.
			$name                = sanitize_text_field( $params['name'] );
			$width               = absint( $params['width'] ); // Ensure it's a positive integer
			$height              = absint( $params['height'] ); // Ensure it's a positive integer
			$startwithaudiomuted = sanitize_key( $params['startwithaudiomuted'] );
			$startwithvideomuted = sanitize_key( $params['startwithvideomuted'] );
			$startscreensharing  = sanitize_key( $params['startscreensharing'] );
			$invite              = sanitize_key( $params['invite'] );

			if ( 0 === $width ) {
				$width = 1080;
			}

			if ( 0 === $height ) {
				$height = 720;
			}

			// phpcs:ignore
			$output = sprintf(
				'<div class="jitsi-wrapper" data-name="%1$s" data-width="%2$d" data-height="%3$d" style="width:%2$dpx" data-mute="%4$s" data-videomute="%5$s" data-screen="%6$s" data-invite="%7$s"></div>',
				esc_attr( $name ),
				esc_attr( $width ),
				esc_attr( $height ),
				esc_attr( $startwithaudiomuted ),
				esc_attr( $startwithvideomuted ),
				esc_attr( $startscreensharing ),
				esc_attr( $invite )
			);

			return $output;
		}


		/**
		 * Elementor
		 */
		public function jitsi_init_elementor() {
			// Check if Elementor installed and activated.
			if ( ! did_action( 'elementor/loaded' ) ) {
				return;
			}
			add_action( 'elementor/widgets/widgets_registered', array( $this, 'jitsi_el_widgets_registered' ) );
		}

		/**
		 * Registered widgets
		 *
		 * @return  void
		 */
		public function jitsi_el_widgets_registered() {
			$this->jitsi_el_include_widgets();
			$this->jitsi_el_register_widgets();
		}

		/**
		 * Include_widgets
		 *
		 * @return  void
		 */
		private function jitsi_el_include_widgets() {
			require_once __DIR__ . '/inc/elementor.php';
		}

		/**
		 * Register_widgets
		 *
		 * @return  void
		 */
		private function jitsi_el_register_widgets() {
			\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Jitsi\Widgets\Jitsi_Elementor() );
		}
	}
}

if ( ! function_exists( 'jitsi_meet_wp' ) ) {
	/**
	 * Jitsi_Meet_WP class instance
	 *
	 * @return  object
	 */
	function jitsi_meet_wp() {
		return Jitsi_Meet_WP::instance();
	}
}

/** Fire off the plugin */
jitsi_meet_wp();
