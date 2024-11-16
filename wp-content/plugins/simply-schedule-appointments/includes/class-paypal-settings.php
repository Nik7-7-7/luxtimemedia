<?php
/**
 * Simply Schedule Appointments Paypal Settings.
 *
 * @since   2.0.1
 * @package Simply_Schedule_Appointments
 */

/**
 * Simply Schedule Appointments Paypal Settings.
 *
 * @since 2.0.1
 */
class SSA_Paypal_Settings extends SSA_Settings_Schema {
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

	protected $slug = 'paypal';
	protected $parent_slug = 'payments';

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

				'title' => array(
					'name' => 'title',
					'default_value' => 'PayPal',
					'validate_callback' => array( 'SSA_Validation', 'validate_string' ),
				),

				'description' => array(
					'name' => 'description',
					'default_value' => __( 'Pay via PayPal; you can pay with your credit card if you don\'t have a PayPal account.', 'simply-schedule-appointments' ),
					'validate_callback' => array( 'SSA_Validation', 'validate_string' ),
				),

				'email' => array(
					'name' => 'email',
					'default_value' => '',
					'validate_callback' => array( 'SSA_Validation', 'validate_string' ),
				),

				'sandbox_enabled' => array(
					'name' => 'sandbox_enabled',
					'default_value' => false,
				),

				'identity_token' => array(
					'name' => 'identity_token',
					'default_value' => '',
					'validate_callback' => array( 'SSA_Validation', 'validate_string' ),
					'required_capability' => 'ssa_manage_site_settings',
					'writeonly_secret' => true
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
			'version' => '2018-12-05',
			'fields' => array(
				'ipn_url' => array(
					'name' => 'ipn_url',

					'get_function' => array( $this->plugin->paypal, 'get_ipn_url' ),
					'get_input_path' => 'webhook',

				),
			),
		);

		return $this->computed_schema;
	}
}
