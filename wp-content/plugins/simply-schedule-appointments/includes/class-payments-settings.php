<?php
/**
 * Simply Schedule Appointments Payments Settings.
 *
 * @since   2.0.1
 * @package Simply_Schedule_Appointments
 */

/**
 * Simply Schedule Appointments Payments Settings.
 *
 * @since 2.0.1
 */
class SSA_Payments_Settings extends SSA_Settings_Schema {
	/**
	 * Parent plugin class.
	 *
	 * @since 0.0.3
	 *
	 * @var   Simply_Schedule_Appointments
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * @since  0.0.3
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
	 * @since  0.0.3
	 */
	public function hooks() {
		
	}

	protected $slug = 'payments';

	public function get_schema() {
		if ( !empty( $this->schema ) ) {
			return $this->schema;
		}

		$this->schema = array(
			'version' => '2018-10-23',
			'fields' => array(
				'enabled' => array(
					'name' => 'enabled',
					'default_value' => false,
				),

				'default_currency' => array(
					'name' => 'default_currency',
					'default_value' => 'USD',
					'validate_callback' => array( 'SSA_Validation', 'validate_string' ),
				),

			),
		);

		return $this->schema;
	}

}
