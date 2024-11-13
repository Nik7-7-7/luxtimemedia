<?php
/**
 * Simply Schedule Appointments Debug.
 *
 * @since   4.0.1
 * @package Simply_Schedule_Appointments
 */

/**
 * Simply Schedule Appointments Debug.
 *
 * @since 4.0.1
 */
class SSA_Debug {
	/**
	 * Parent plugin class.
	 *
	 * @since 4.0.1
	 *
	 * @var   Simply_Schedule_Appointments
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * @since  4.0.1
	 *
	 * @param  Simply_Schedule_Appointments $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  4.0.1
	 */
	public function hooks() {
		add_action( 'admin_init', array( $this, 'debug_settings' ) );
		add_action( 'init', 	  array( $this, 'display_ssa_debug_logs' ) );
	}

	public function debug_settings() {
		if ( ! isset( $_GET['ssa-debug-settings'] ) ) {
			return;
		}

		if ( ! current_user_can( 'ssa_manage_site_settings' ) ) {
			return;
		}

		$settings = $this->plugin->settings->get();
		if ( ! empty( $_GET['ssa-debug-settings'] ) ) {
			if ( empty( $settings[esc_attr( $_GET['ssa-debug-settings'] )] ) ) {
				die( 'setting slug not found' ); // phpcs:ignore
			}
			$settings = $settings[esc_attr( $_GET['ssa-debug-settings'] )];
		}
		echo '<pre>'.print_r($settings, true).'</pre>'; // phpcs:ignore
		exit;
	}

	public function display_ssa_debug_logs() {
		if ( empty( $_GET['ssa-debug-logs'] )) {
			return;
		}

		if ( self::get_site_unique_hash_for_debug_logs() !== esc_attr( $_GET['ssa-debug-logs'] ) ) {
			return;
		}
		
		if ( isset( $_GET['revisions'] ) ) {
			$this->display_ssa_revisions_logs();
			return;
		}

		if ( isset( $_GET['revisions_meta'] ) ) {
			$this->display_ssa_revisions_meta_logs();
			return;
		}

		$path = $this->plugin->support_status->get_log_file_path( 'debug' );
		if ( file_exists( $path ) && is_readable( $path ) ) {
			$content = file_get_contents( $path );
			echo '<pre>'.print_r($content, true).'</pre>'; // phpcs:ignore
			exit;
		}

	}

	public static function get_site_unique_hash_for_debug_logs() {
		return SSA_Utils::site_unique_hash( 'ssa-debug-logs' );
	}

	public function display_ssa_revisions_logs() {

		$args = array();

		if ( ! empty( $_GET['appointment_id'] )) {
			$args['appointment_id'] = esc_attr( $_GET['appointment_id'] );
		}
		if ( ! empty( $_GET['appointment_type_id'] )) {
			$args['appointment_type_id'] = esc_attr( $_GET['appointment_type_id'] );
		}
		if ( ! empty( $_GET['user_id'] )) {
			$args['user_id'] = esc_attr( $_GET['user_id'] );
		}
		if ( ! empty( $_GET['staff_id'] )) {
			$args['staff_id'] = esc_attr( $_GET['staff_id'] );
		}
		if ( ! empty( $_GET['id'] )) {
			$args['id'] = esc_attr( $_GET['id'] );
		}

		$args = array_merge( array(
			'orderby' => 'id',
			'order'   => 'DESC',
			'number'	=> 100
		), $args );

		$revisions = $this->plugin->revision_model->query( $args );

		if ( empty( $revisions ) ) {
			echo 'No revisions have been found';
			exit;
		}

		$revisions = array_reverse( $revisions );	

		ob_start();
		include $this->plugin->dir('templates/ssa-logs/revisions.php');
		echo ob_get_clean();
		exit;
	}

	public function display_ssa_revisions_meta_logs() {
		$args = array();

		if ( ! empty( $_GET['revision_id'] )) {
			$args['revision_id'] = esc_attr( $_GET['revision_id'] );
		}
		if ( ! empty( $_GET['meta_key'] )) {
			$args['meta_key'] = esc_attr( $_GET['meta_key'] );
		}
		if ( ! empty( $_GET['meta_value_before'] )) {
			$args['meta_value_before'] = esc_attr( $_GET['meta_value_before'] );
		}
		if ( ! empty( $_GET['meta_value'] )) {
			$args['meta_value'] = esc_attr( $_GET['meta_value'] );
		}
		if ( ! empty( $_GET['id'] )) {
			$args['id'] = esc_attr( $_GET['id'] );
		}

		$args = array_merge( array(
			'orderby' => 'id',
			'order'   => 'DESC',
			'number'	=> 100
		), $args );

		$revisions_meta = $this->plugin->revision_meta_model->query( $args );

		if ( empty( $revisions_meta ) ) {
			echo 'No revisions meta have been found';
			exit;
		}

		$revisions_meta = array_reverse( $revisions_meta );	

		ob_start();
		include $this->plugin->dir('templates/ssa-logs/revisions-meta.php');
		echo ob_get_clean();
		exit;
	}

}
