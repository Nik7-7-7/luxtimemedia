<?php
/**
 * Simply Schedule Appointments Stripe Settings.
 *
 * @since   2.0.1
 * @package Simply_Schedule_Appointments
 */

/**
 * Simply Schedule Appointments Stripe Settings.
 *
 * @since 2.0.1
 */
class SSA_Stripe_Settings extends SSA_Settings_Schema {
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

	protected $slug = 'stripe';
	protected $parent_slug = 'payments';

	public function get_schema() {
		if ( !empty( $this->schema ) ) {
			return $this->schema;
		}

		$this->schema = array(
			// YYYY-MM-DD
			'version' => '2023-04-20',
			'fields' => array(
				'enabled' => array(
					'name' => 'enabled',
					'default_value' => false,
				),

				'title' => array(
					'name' => 'title',
					'default_value' => __( 'Credit card', 'simply-schedule-appointments' ),
					'validate_callback' => array( 'SSA_Validation', 'validate_string' ),
				),

				'description' => array(
					'name' => 'description',
					'default_value' => __( 'Pay with Credit Card', 'simply-schedule-appointments' ),
					'validate_callback' => array( 'SSA_Validation', 'validate_string' ),
				),

				'statement_descriptor' => array(
					'name' => 'statement_descriptor',
					'default_value' => '',
					'validate_callback' => array( 'SSA_Validation', 'validate_string' ),
				),

				'publishable_key' => array(
					'name' => 'publishable_key',
					'default_value' => '',
					'validate_callback' => array( 'SSA_Validation', 'validate_string' ),
					'encrypt' => true,
				),

				'secret_key' => array(
					'name' => 'secret_key',
					'default_value' => '',
					'validate_callback' => array( 'SSA_Validation', 'validate_string' ),
					'required_capability' => 'ssa_manage_site_settings',
					'encrypt' => true,
				),

				'webhook_secret' => array(
					'name' => 'webhook_secret',
					'default_value' => '',
					'validate_callback' => array( 'SSA_Validation', 'validate_string' ),
					'required_capability' => 'ssa_manage_site_settings',
					'encrypt' => true,
				),

				'test_mode_enabled' => array(
					'name' => 'test_mode_enabled',
					'default_value' => false,
				),

				'test_publishable_key' => array(
					'name' => 'test_publishable_key',
					'default_value' => '',
					'validate_callback' => array( 'SSA_Validation', 'validate_string' ),
					'encrypt' => true,
				),

				'test_secret_key' => array(
					'name' => 'test_secret_key',
					'default_value' => '',
					'validate_callback' => array( 'SSA_Validation', 'validate_string' ),
					'required_capability' => 'ssa_manage_site_settings',
					'encrypt' => true,
				),
				'test_webhook_secret' => array(
					'name' => 'test_webhook_secret',
					'default_value' => '',
					'validate_callback' => array( 'SSA_Validation', 'validate_string' ),
					'required_capability' => 'ssa_manage_site_settings',
					'encrypt' => true,
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
				'webhook' => array(
					'name' => 'webhook',

					'get_function' => array( $this->plugin->stripe, 'get_webhook_listener_url' ),
					'get_input_path' => 'webhook',

				),
			),
		);

		return $this->computed_schema;
	}

	public function validate( $new_settings ) {
		// we validate values against schema first, and return any schema mismatch errors
		$schema_invalid_fields = parent::validate( $new_settings );
		if ( is_wp_error( $schema_invalid_fields ) ) {
			return $schema_invalid_fields;
		}

		// add warning if test mode is enabled
		if( isset( $new_settings['test_mode_enabled'] ) && true === $new_settings['test_mode_enabled'] ) {
			$this->plugin->error_notices->add_error_notice('stripe_test_mode_active');
		} else {
			$this->plugin->error_notices->delete_error_notice('stripe_test_mode_active');
		}
		
		// At this point we know all fields conform to the schema validation
		// We can do API specific validation
		$api_test_results = $this->test_api( $new_settings );
		if ( is_wp_error( $api_test_results ) ) {
			return $api_test_results;
		}
	}

	public function test_api( $new_settings ) {
		$wp_error = new WP_Error();

		// only test if the secret key is set
		if ( ! empty( $new_settings[ "secret_key" ] ) ){

			$response = wp_remote_get( 'https://api.stripe.com/v1/charges', array(
				'headers' => array(
					'Authorization'=>'Bearer ' . $new_settings['secret_key'],
				)
			) );
			$response = json_decode( wp_remote_retrieve_body( $response ) );
			if ( isset( $response->error ) && ! empty( $response->error->message ) ) {
				$wp_error->add( 422, $this->slug . ' ' . __( 'settings are invalid' ) );
				$wp_error->add( 422, array( 'secret_key', __( 'The secret key was rejected by Stripe' ) ) );
			}
		}
		// only test if the secret key is set
		if ( ! empty( $new_settings[ "test_secret_key" ] ) ){
			$response = wp_remote_get( 'https://api.stripe.com/v1/charges', array(
				'headers' => array(
					'Authorization'=>'Bearer ' . $new_settings['test_secret_key'],
				)
			) );
			$response = json_decode( wp_remote_retrieve_body( $response ) );
			if ( isset( $response->error ) && ! empty( $response->error->message ) ) {
				$wp_error->add( 422, $this->slug . ' ' . __( 'settings are invalid' ) );
				$wp_error->add( 422, array( 'test_secret_key', __( 'The secret key was rejected by Stripe' ) ) );
			}
		}
		
		// if test webhook secret is set, validate it against Stripe API
		if( ! empty( $new_settings["test_mode_enabled"] ) && !empty( $new_settings["test_secret_key"] ) && !empty( $new_settings["test_webhook_secret"] ) ){
			// add the error
			$this->plugin->error_notices->add_error_notice( 'stripe_invalid_webhook_secret' );
			// delegate clearing the error to the webhook
			$trigger_webhook = wp_remote_post('https://api.stripe.com/v1/payment_intents',array(
				'headers' => array(
					'Authorization'=>'Bearer ' . $new_settings['test_secret_key'],
				),
				'body' => array(
					'shipping[address][line1]' => '510+Townsend+St',
					'shipping[address][state]' => 'CA',
					'shipping[address][postal_code]' => '94103',
					'shipping[address][city]' => 'San+Francisco',
					'shipping[address][country]' => 'US',
					'shipping[name]' => 'Jenny+Rosen',
					'description' => '(created+by+Stripe+CLI)',
					'currency' => 'usd',
					'payment_method' => 'pm_card_visa',
					'confirm' => 'true',
					'amount' => '100',
					'payment_method_types[]' => 'card',
					'metadata' => [
						'home_url' => home_url()
					],
				)
			));
		}
		
		if ( ! empty( $wp_error->errors ) ) {
			return $wp_error;
		}
	}

}
