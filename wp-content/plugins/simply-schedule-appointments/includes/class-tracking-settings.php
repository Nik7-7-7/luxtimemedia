<?php
/**
 * Simply Schedule Appointments Tracking Settings.
 *
 * @since   3.0.2
 * @package Simply_Schedule_Appointments
 */

/**
 * Simply Schedule Appointments Tracking Settings.
 *
 * @since 3.0.2
 */
class SSA_Tracking_Settings extends SSA_Settings_Schema {
	/**
	 * Parent plugin class.
	 *
	 * @since 3.0.2
	 *
	 * @var   Simply_Schedule_Appointments
	 */
	protected $plugin = null;

	protected $slug = 'tracking';

	/**
	 * Constructor.
	 *
	 * @since  3.0.2
	 *
	 * @param  Simply_Schedule_Appointments $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		parent::__construct();

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

	public function get_schema() {
		if ( !empty( $this->schema ) ) {
			return $this->schema;
		}

		$this->schema = array(
			'version' => '2019-07-19',
			'fields' => array(
				'enabled' => array(
					'name' => 'enabled',
					'default_value' => false,
				),

				'page_tracking' => array(
					'name' => 'page_tracking',
					'default_value' => true,
				),

				'event_tracking' => array(
					'name' => 'event_tracking',
					'default_value' => true,
				),

			),


		);

		return $this->schema;
	}

}
