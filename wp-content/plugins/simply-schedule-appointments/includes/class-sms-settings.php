<?php
/**
 * Simply Schedule Appointments Sms Settings.
 *
 * @since   2.6.7
 * @package Simply_Schedule_Appointments
 */

/**
 * Simply Schedule Appointments Sms Settings.
 *
 * @since 2.6.7
 */
class SSA_Sms_Settings extends SSA_Settings_Schema {
	/**
	 * Parent plugin class.
	 *
	 * @since 2.6.7
	 *
	 * @var   Simply_Schedule_Appointments
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * @since  2.6.7
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
	 * @since  2.6.7
	 */
	public function hooks() {
		
	}

	protected $slug = 'sms';

	public function get_schema() {
		if ( !empty( $this->schema ) ) {
			return $this->schema;
		}

		$this->schema = array(
			'version' => '2019-06-28',
			'fields' => array(
				'enabled' => array(
					'name' => 'enabled',
					'default_value' => false,
				),

				'twilio_test_mode_enabled' => array(
					'name' => 'twilio_test_mode_enabled',
					'default_value' => false,
					'required_capability' => 'ssa_manage_site_settings',
				),

				'twilio_account_sid' => array(
					'name' => 'twilio_account_sid',
					'default_value' => '',
					'validate_callback' => array( 'SSA_Validation', 'validate_string' ),
					'required_capability' => 'ssa_manage_site_settings',
				),

				'twilio_auth_token' => array(
					'name' => 'twilio_auth_token',
					'default_value' => '',
					'validate_callback' => array( 'SSA_Validation', 'validate_string' ),
					'required_capability' => 'ssa_manage_site_settings',
					'writeonly_secret' => true
				),

				'twilio_test_account_sid' => array(
					'name' => 'twilio_test_account_sid',
					'default_value' => '',
					'validate_callback' => array( 'SSA_Validation', 'validate_string' ),
					'required_capability' => 'ssa_manage_site_settings',
				),

				'twilio_test_auth_token' => array(
					'name' => 'twilio_test_auth_token',
					'default_value' => '',
					'validate_callback' => array( 'SSA_Validation', 'validate_string' ),
					'required_capability' => 'ssa_manage_site_settings',
					'writeonly_secret' => true
				),

				'twilio_send_from_phone' => array(
					'name' => 'twilio_send_from_phone',
					'default_value' => '',
					'validate_callback' => array( 'SSA_Validation', 'validate_string' ),
					'required_capability' => 'ssa_manage_site_settings',
				),

				'authorized_date' => array(
					'name' => 'authorized_date',
					'default_value' => '',
					'validate_callback' => array( 'SSA_Validation', 'validate_string' ),
					'required_capability' => 'ssa_manage_appointments',
				),

			),


		);

		return $this->schema;
	}

	public function get_computed_schema() {
		if ( !empty( $this->computed_schema ) ) {
			return $this->computed_schema;
		}

		$this->computed_schema = array(
			'version' => '2023-07-21',
			'fields' => array(

				'opt_in' => array(
					'name' => 'opt_in',
					'get_function' => array( 'SSA_Sms_Settings', 'opt_in' ),
					'get_input' => true,
				),

			),
		);

		return $this->computed_schema;
	}

	/**
	 * Filter to allow customization the Sms opt in value.
	 * By default this would return true, meaning that the "remind me..." checkbox in the customer information screen remains unchecked as the default state.
	 * 
	 * @example Adding a custom sms opt in setting
	 *     add_filter( 'ssa/sms/opt_in', function() {
	 *         return false;
	 *     });
	 * 
	 * @since 6.5.15
	 * 
	 * @return bool
	 */
	public static function opt_in() {
		return apply_filters( 'ssa/sms/opt_in', true );
	}
}
