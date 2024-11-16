<?php
/**
 * Simply Schedule Appointments Tracking.
 *
 * @since   3.0.2
 * @package Simply_Schedule_Appointments
 */

/**
 * Simply Schedule Appointments Tracking.
 *
 * @since 3.0.2
 */
class SSA_Tracking {
	/**
	 * Parent plugin class.
	 *
	 * @since 3.0.2
	 *
	 * @var   Simply_Schedule_Appointments
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * @since  3.0.2
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
	 * @since  3.0.2
	 */
	public function hooks() {

	}
}
