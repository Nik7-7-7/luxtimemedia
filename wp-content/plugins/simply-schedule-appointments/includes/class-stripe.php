<?php
/**
 * Simply Schedule Appointments Stripe.
 *
 * @since   2.0.1
 * @package Simply_Schedule_Appointments
 */

/**
 * Simply Schedule Appointments Stripe.
 *
 * @since 2.0.1
 */
class SSA_Stripe {
	/**
	 * Parent plugin class.
	 *
	 * @since 2.0.1
	 *
	 * @var   Simply_Schedule_Appointments
	 */
	protected $plugin = null;
	public $parent_slug = 'payments';

	/**
	 * Constructor.
	 *
	 * @since  5.6.0
	 *
	 * @var  \Stripe\StripeClient
	 */
	protected $stripe = null;

	/**
	 * Stripe API Key.
	 *
	 * @since 2.0.1
	 *
	 * @var string
	 */
	protected $api_key = null;

	/**
	 * Stripe Webhook Signing Key.
	 *
	 * @since 2.0.1
	 *
	 * @var string
	 */
	protected $webhook_secret = null;

	/**
	 * Constructor.
	 *
	 * @since  2.0.1
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
	 * @since  2.0.1
	 */
	public function hooks() {
		add_action( 'init', array( $this, 'listen_for_stripe_ipn' ) );
		add_action( 'ssa_verify_stripe_ipn', array( $this, 'process_stripe_ipn' ) );
		add_action( 'rest_api_init', array( $this, 'register_custom_routes' ) );
	}

	/**
	 * Register our custom routes.
	 *
	 * @since  5.6.0
	 */
	public function register_custom_routes() {
		$namespace = 'ssa/v1';
		$base      = 'stripe';

		register_rest_route(
			$namespace,
			'/' . $base . '/create',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_payment_intent' ),
					'permission_callback' => '__return_true',
					'args'                => array(
						'context' => array(
							'default' => 'view',
						),
					),
				),
			)
		);
	}

	/**
	 * Create a Stripe payment intent.
	 *
	 * @since  5.6.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_REST_Response $response The response data.
	 */
	public function create_payment_intent( WP_REST_Request $request ) {
		$this->api_init();

		$receipt_email = $request->get_param( 'receipt_email' );
		$amount        = $request->get_param( 'price' );
		$currency      = $request->get_param( 'currency' );
		$name          = $request->get_param( 'name' ) || null;
		$settings      = $this->plugin->stripe_settings->get();
		$appointment_id = $request->get_param( 'appointment_id' );
		$appointment_token = $request->get_param( 'appointment_token' );

		$errors_map = array(
			'receipt_email' => __( 'Receipt email is required.', 'simply-schedule-appointments' ),
			'price'         => __( 'Amount is required.', 'simply-schedule-appointments' ),
			'currency'      => __( 'Currency is required.', 'simply-schedule-appointments' ),
		);

		foreach ( $errors_map as $key => $error ) {
			if ( ! $request->get_param( $key ) ) {
				return new WP_REST_Response(
					array(
						'success' => false,
						'error'   => $error,
					),
					400
				);
			}
		}

		// Create a PaymentIntent with amount and currency.
		$args = array(
			'amount'               => $amount,
			'currency'             => $currency,
			'receipt_email'        => $receipt_email,
			'payment_method_types' => array( 'card' ),
			// make it a guest payment, so that Stripe groups by card number, email, or phone
			'customer'             => null,
			'description'          => 'Appointment Booked',
			'metadata' => [ 
				'appointment_id' => $appointment_id, 
				'appointment_token' => $appointment_token,
				'home_url' => home_url(),
				'booking_post_id' => $request->get_param( 'booking_post_id' ),
			],
		);

		// confirm webhook secret ( corresponding to current mode: test or live ) is set in settings
		// if the webhook secret is not properly setup, we don't want to support methods that rely on webhooks
		$ready_for_webhooks=false;
		if ( ! empty( $settings['webhook_secret'] && false === $settings['test_mode_enabled'] ) || ( ! empty( $settings['test_webhook_secret'] ) && true === $settings['test_mode_enabled'] ) ) {
			$ready_for_webhooks=true;
		}
		
		if( $currency==="EUR" && $ready_for_webhooks ){
			$args['payment_method_types'][] ='ideal'; 
		}

		if ( ! empty( $settings['statement_descriptor'] ) ) {
			// if card one of payment methods, set statement descriptor suffix
			// reference the Stripe API docs for more info on statement descriptors
			// https://support.stripe.com/questions/use-of-the-statement-descriptor-parameter-on-paymentintents-for-card-charges
			if( in_array( 'card', $args['payment_method_types'] ) ){
				// extract first 10 characters of previously saved statement_descriptor to keep the complete statement descriptor under 22 characters and avoid errors
				$args['statement_descriptor_suffix'] = substr( $settings['statement_descriptor'], 0, 10 );
			} else {
				// if not, set statement descriptor
				$args['statement_descriptor'] = $settings['statement_descriptor'];
			}
		}

		try {
			$payment_intent = \Stripe\PaymentIntent::create( $args );

			$client_secret = $payment_intent->client_secret;

			return new WP_REST_Response(
				array(
					'success'       => true,
					'client_secret' => $client_secret,
				),
				200
			);
		} catch ( Error $e ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'error'   => $e->getMessage(),
				),
				400
			);
		}
	}

	/**
	 * Return webhook listener URL.
	 */
	public function get_webhook_listener_url() {
		$url = home_url( '?ssa-listener=stripe' );

		return $url;
	}


	/**
	 * Listen For Stripe IPN
	 *
	 * Listens for a Stripe IPN requests and then sends to the processing function.
	 *
	 * @access      private
	 * @since       1.0
	 * @return      void
	 */

	 public function listen_for_stripe_ipn() {
		if ( isset( $_GET['ssa-listener'] ) && $_GET['ssa-listener'] === 'stripe' ) {
			if ( $this->plugin->settings_installed->is_enabled( 'stripe' ) ) {
				do_action( 'ssa_verify_stripe_ipn' );
			}
		}
	}
	
	public function is_card_payment_method($payload_decoded){
		if( isset( $payload_decoded->data->object->payment_method )){
			$response = wp_remote_get("https://api.stripe.com/v1/payment_methods/" . $payload_decoded->data->object->payment_method, array(
				'headers' => array(
					'Authorization'=>'Bearer ' . $this->api_key,
				),
			));
			$payment_method_type = json_decode( wp_remote_retrieve_body( $response ) )->type;
			if( 'card' === $payment_method_type ){
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Process Stripe IPN
	 *
	 * @access      private
	 * @since       6.5.0
	 * @return      void
	 */
	public function process_stripe_ipn() {
		$payload    = @file_get_contents( 'php://input' );
		$payload_decoded = json_decode( $payload );
		// we only process the following events - others can be considered noise
		if( !empty( $payload_decoded->data->object->metadata->home_url ) && ( $payload_decoded->data->object->metadata->home_url != home_url() || !in_array( $payload_decoded->type, array( 'payment_intent.succeeded', 'payment_intent.payment_failed', 'payment_intent.canceled' ) ) ) ){
			return;
		}

		if ( empty( $_SERVER['HTTP_STRIPE_SIGNATURE'] ) ) {
			http_response_code( 400 );
			echo json_encode( new WP_Error( 400, __( 'Stripe webhook missing a signature', 'simply-schedule-appointments' ) ) );
			exit();
		}
		$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
		$event      = null;

		$this->api_init();
		
		// should never happen - we don't add payment methods that rely on webhook secrets unless the value is set
		if( empty( $this->webhook_secret ) ){
			// show error notice in admin dashboard
			$this->plugin->error_notices->add_error_notice( 'stripe_invalid_webhook_secret' );
			// respond with status 400 if webhook secret is not set and an error
			// this will show the admin a message on their stripe dashboard
			http_response_code( 400 );
			echo json_encode( new WP_Error( 400, __( 'Your SSA Stripe settings are missing the webhook secret', 'simply-schedule-appointments' ) ) );
			exit();
		}
		
		// verify the signature of the webhook
		\Stripe\Stripe::setApiKey( $this->api_key );
		
		try {
			$event = \Stripe\Webhook::constructEvent(
				$payload,
				$sig_header,
				$this->webhook_secret
			);
		} catch ( \UnexpectedValueException $e ) {
			// Invalid payload
			http_response_code( 400 );
			exit();
		} catch ( \Stripe\Exception\SignatureVerificationException $e ) {
			// may indicate an invalid webhook secret value - show error notice
			$this->plugin->error_notices->add_error_notice( 'stripe_invalid_webhook_secret' );
			// Invalid signature
			http_response_code( 400 );
			exit();
		}
		$this->plugin->error_notices->delete_error_notice( 'stripe_invalid_webhook_secret' );

		if( $this->is_card_payment_method( $payload_decoded ) ){
			return;
		}

		$payment_intent = $event->data->object; // contains a \Stripe\PaymentIntent
		$this->handlePaymentIntent( $payment_intent );
		http_response_code( 200 );
	}

	public function handlePaymentIntent( $payment_intent ) {
		if ( empty( $payment_intent->metadata->appointment_id ) || empty( $payment_intent->metadata->appointment_token ) ) {
			ssa_debug_log( 'Stripe webhook missing appointment_id or appointment_token', 10 );
			ssa_debug_log( $payment_intent, 10 );
			echo 'Stripe webhook missing appointment_id or appointment_token';
			exit();
		}
		$data = array(
			'appointment_id'    => $payment_intent->metadata->appointment_id,
			'appointment_token' => $payment_intent->metadata->appointment_token,
			'gateway'           => 'stripe',
			'gateway_payload'   => $payment_intent,
		);
		$this->plugin->payment_model->insert( $data );
	}
	
	/**
	 * Process payment, validate it, and return normalized payment fields to store in Payment data/record.
	 *
	 * @param  array $appointment_id The appointment ID.
	 * @param  array $gateway_payload The gateway payload.
	 * @param  array $args Additional arguments.
	 *
	 * @return \Stripe\PaymentIntent | Exception $payment_intent The payment intent if valid. Otherwise, an Exception.
	 **/
	public function process_payment( $appointment_id = null, $gateway_payload = array(), $args = array() ) {
		$this->api_init();
		// If payload doesn't containt Payment Intent id, bail.
		if ( ! isset( $gateway_payload['id'] ) || ! $gateway_payload['id'] ) {
			return false;
		}

		$payment_intent_id = $gateway_payload['id'];

		try {
			// Get the PaymentIntent Object to match.
			$payment_intent = $this->stripe->paymentIntents->retrieve( $payment_intent_id, array(
				'expand' => array( 'latest_charge', 'last_payment_error' ), 
			) );

			// Validate PaymentIntent Object.
			$valid = $this->validate_payment_intent( $payment_intent, $gateway_payload, $args );

			if ( ! $valid ) {
				return false;
			}

			return $payment_intent;
		} catch ( Exception $e ) {
			ssa_debug_log("SSA failed to validate a Stripe payment.");
			ssa_debug_log( 'Exception: ' . print_r( $e, true ), 100 ); // phpcs:ignore 
			return $e;
		}
	}

	/**
	 * Given a Stripe PaymentIntent object, validate it comparing with the payload.
	 *
	 * @since 5.6.0
	 *
	 * @param  Stripe\PaymentIntent $payment_intent  Stripe PaymentIntent object.
	 * @param  array                $gateway_payload Payload from Stripe.
	 * @param  array                $args            Optional. Additional arguments.
	 *
	 * @return bool                                  True if valid.
	 * @throws Exception                             If invalid.
	 */
	public function validate_payment_intent( $payment_intent = null, $gateway_payload = null, $args = array() ) {
		if ( apply_filters( 'ssa/stripe/skip_payment_validation', false, $payment_intent, $gateway_payload, $args ) ) {
			return true;
		}

		// Use the gateway_payload to validate the Payment Intent by comparing all the main data points.
		// Check if amount is the same.
		if ( (int) $payment_intent->amount !== (int) $gateway_payload['amount'] ) {
			$expected_amount = (int) $gateway_payload['amount'];
			$actual_amount = (int) $payment_intent->amount;
			throw new Exception( "Payment amount does not match amount requested. Expected: $expected_amount, Actual: $actual_amount" );
		}
		
		if ( ! isset( $args['amount'] ) ) {
			throw new Exception( 'Payment argument amount not set.' );
		}
		
		// Check if amount attempted is the same price as the price defined on the appointment type.
		if ( (int) $payment_intent->amount !== (int) $args['amount'] ) {
			$expected_amount = (int) $args['amount'];
			$actual_amount = (int) $payment_intent->amount;
			throw new Exception( "Payment amount does not match amount requested. Expected: $expected_amount, Actual: $actual_amount" );
		}
		
		// Check if currency is the same.
		if ( $payment_intent->currency !== $gateway_payload['currency'] ) {
			throw new Exception( 'Payment currency does not match.' );
		}

		// Check if payment_method is the same.
		if ( $payment_intent->payment_method !== $gateway_payload['payment_method'] ) {
			// here we used to throw new Exception( 'Payment method does not match.' );
			// it seems the payment will still go through if the payment method is different
			// so SSA should not fail the payment if the payment method is different
			ssa_debug_log( 'Payment method does not match. Expected: ' . $gateway_payload['payment_method'] . ', Actual: ' . $payment_intent->payment_method, 100 ); // phpcs:ignore
			ssa_debug_log( 'Payment intent: ' . print_r( $payment_intent, true ), 100 ); // phpcs:ignore
			ssa_debug_log( 'Gateway payload: ' . print_r( $gateway_payload, true ), 100 ); // phpcs:ignore
		}

		// Check if receipt_email is the same.
		if ( $payment_intent->receipt_email !== $gateway_payload['receipt_email'] ) {
			throw new Exception( 'Receipt email does not match.' );
		}

		// Check if status is succeeded.
		if ( 'succeeded' !== $payment_intent->status ) {
			throw new Exception( 'Payment status is not succeeded.' );
		}

		return true;
	}

	/**
	 * Initialize Stripe SDK.
	 *
	 * @since 2.0.1
	 *
	 * @return void
	 */
	public function api_init() {
		if ( ! empty( $this->api_key ) ) {
			return;
		}

		$stripe_settings = $this->plugin->stripe_settings->get();
		if ( empty( $stripe_settings ) ) {
			return;
		}

		if ( ! empty( $stripe_settings['test_mode_enabled'] ) ) {
			$this->api_key = $stripe_settings['test_secret_key'];
			$this->webhook_secret = $stripe_settings['test_webhook_secret'];
		} else {
			$this->api_key = $stripe_settings['secret_key'];
			$this->webhook_secret = $stripe_settings['webhook_secret'];
		}

		/*
		BEGIN temporary fix for curl errors

		Reference:
		https://github.com/stripe/stripe-php/issues/918#issuecomment-615494259

		https://gist.github.com/cklosowski/0870581fe828bdec82367c84524c17d1
		*/
		$curl = new \Stripe\HttpClient\CurlClient();
		$curl->setEnablePersistentConnections( false );
		\Stripe\ApiRequestor::setHttpClient( $curl );
		/* END temporary fix for curl errors */

		\Stripe\Stripe::setAppInfo(
			'Simply Schedule Appointments',
			Simply_Schedule_Appointments::VERSION,
			'https://simplyscheduleappointments.com',
			'pp_partner_H8hiAulIf74USx'
		);

		\Stripe\Stripe::setApiKey( $this->api_key );

		$this->stripe = new \Stripe\StripeClient( $this->api_key );
	}

}
