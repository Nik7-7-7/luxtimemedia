<?php
/**
 * Simply Schedule Appointments Zoom Settings.
 *
 * @since   3.7.1
 * @package Simply_Schedule_Appointments
 */

/**
 * Simply Schedule Appointments Zoom Settings.
 *
 * @since 3.7.1
 */
class SSA_Zoom_Settings extends SSA_Settings_Schema {
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

	protected $slug = 'zoom';

	public function get_schema() {
		if ( !empty( $this->schema ) ) {
			return $this->schema;
		}

		$this->schema = array(
			'version' => '2021-06-17',
			'fields' => array(
				'enabled' => array(
					'name' => 'enabled',
					'default_value' => false,
				),

				'auth_code' => array(
					'name' => 'auth_code',
					'default_value' => '',
					'validate_callback' => array( 'SSA_Validation', 'validate_string' ),
					'encrypt' => true,
					'required_capability' => 'ssa_manage_site_settings',
					'writeonly_secret' => true
				),

				'access_token' => array(
					'name' => 'access_token',
					'default_value' => '',
					'validate_callback' => array( 'SSA_Validation', 'validate_string' ),
					'encrypt' => true,
					'required_capability' => 'ssa_manage_site_settings',
					'writeonly_secret' => true
				),

				'access_token_expires' => array(
					'name' => 'access_token_expires',
					'default_value' => '',
					'validate_callback' => array( 'SSA_Validation', 'validate_string' ),
					'required_capability' => 'ssa_manage_site_settings',
				),
			),


		);

		return $this->schema;
	}

}
