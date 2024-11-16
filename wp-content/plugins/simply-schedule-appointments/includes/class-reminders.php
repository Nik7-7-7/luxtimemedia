<?php
/**
 * Simply Schedule Appointments Reminders.
 *
 * @since   2.8.0
 * @package Simply_Schedule_Appointments
 */

/**
 * Simply Schedule Appointments Reminders.
 *
 * @since 2.8.0
 */
class SSA_Reminders {
	/**
	 * Parent plugin class.
	 *
	 * @since 2.8.0
	 *
	 * @var   Simply_Schedule_Appointments
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * @since  2.8.0
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
	 * @since  2.8.0
	 */
	public function hooks() {

	}
}
