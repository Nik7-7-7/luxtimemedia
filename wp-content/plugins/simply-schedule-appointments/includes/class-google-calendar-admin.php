<?php
/**
 * Simply Schedule Appointments Google Calendar Admin.
 *
 * @since   0.6.0
 * @package Simply_Schedule_Appointments
 */

/**
 * Simply Schedule Appointments Google Calendar Admin.
 *
 * @since 0.6.0
 */
class SSA_Google_Calendar_Admin {
	/**
	 * Parent plugin class.
	 *
	 * @since 0.6.0
	 *
	 * @var   Simply_Schedule_Appointments
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * @since  0.6.0
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
	 * @since  0.6.0
	 */
	public function hooks() {
		// Profile screen
		// add_action( 'show_user_profile', array( $this, 'show_profile' ) );
		// add_action( 'edit_user_profile', array( $this, 'show_profile' ) );
		// add_action( 'personal_options_update', array( $this, 'save_profile' ) );
		// add_action( 'edit_user_profile_update', array( $this, 'save_profile' ) );
	}

}
