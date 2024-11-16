<?php
/**
 * Simply Schedule Appointments Paypal.
 *
 * @since   2.0.1
 * @package Simply_Schedule_Appointments
 */

/**
 * Simply Schedule Appointments Paypal.
 *
 * @since 2.0.1
 */
class SSA_Paypal {
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
		add_action( 'init', array( $this, 'listen_for_paypal_ipn' ) );
		add_action( 'ssa_verify_paypal_ipn', array( $this, 'process_paypal_ipn' ) );
		add_action( 'ssa_paypal_web_accept', array( $this, 'process_paypal_web_accept_and_cart' ), 10, 2 );
	}

	public function get_ipn_url() {
		$url = SSA_Bootstrap::maybe_fix_protocol( home_url( '?ssa-listener=paypal' ), 'https' );
		
		return $url;
	}

	/**
	 * Listen For PayPal IPN
	 *
	 * Listens for a PayPal IPN requests and then sends to the processing function.
	 *
	 * @access      private
	 * @since       1.0 
	 * @return      void
	*/

	public function listen_for_paypal_ipn() {
		if( isset( $_GET['ssa-listener'] ) && $_GET['ssa-listener'] == 'paypal' ) {
			if ( $this->plugin->settings_installed->is_enabled( 'paypal' ) ) {
				do_action( 'ssa_verify_paypal_ipn' );
			}
		}
	}

	public function is_test_mode() {
		$settings = $this->plugin->paypal_settings->get();
		if ( ! empty( $settings['sandbox_enabled'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * identifies whether the payment corresponds to an SSA appointment or not
	 * NOTE: we validate the amount inside of function: process_paypal_web_accept_and_cart
	 */
	
	public function confirm_payment_for_ssa( $encoded_data_array ) {
		$wp_error = new WP_Error();
		
		// validate: has payment_id
		if ( ! isset ( $encoded_data_array['custom'] ) || empty ( $encoded_data_array['custom'] ) ){
			$wp_error->add( 400, 'PayPal IPN payment_id is missing or empty' );
			return $wp_error;
		}
		
		// validate payment exists in ssa_payments
		$payment_id = $encoded_data_array['custom'];
		
		if ( strpos( $encoded_data_array['custom'], 'ssa_' ) !== 0 ) {
			// if not prefixed with ssa_ return an error
			$wp_error->add( 400, 'PayPal IPN payment_id '. $payment_id .' is not prefixed with ssa_' );
			return $wp_error;
		}
		
		$payment_id = str_replace('ssa_', '', $payment_id);
		
		$existing_payment = $this->plugin->payment_model->query(array(
			'id' => $payment_id
		));
		
		if ( empty( $existing_payment ) ) {
			$wp_error->add( 400, 'PayPal IPN payment_id '. $payment_id .' does not exist in ssa_payments' );
			return $wp_error;
		}
		
		return true;
	}

	/**
	 * Process PayPal IPN
	 *
	 * @access      private
	 * @since       1.0 
	 * @return      void
	*/

	public function process_paypal_ipn() {
		// Check the request method is POST
		if ( isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] != 'POST' ) {
			return;
		}

		ssa_debug_log( 'ssa_process_paypal_ipn() running during PayPal IPN processing' );

		// Set initial post data to empty string
		$post_data = '';

		// Fallback just in case post_max_size is lower than needed
		if ( ini_get( 'allow_url_fopen' ) ) {
			$post_data = file_get_contents( 'php://input' );
		} else {
			// If allow_url_fopen is not enabled, then make sure that post_max_size is large enough
			ini_set( 'post_max_size', '12M' );
		}
		// Start the encoded data collection with notification command
		$encoded_data = 'cmd=_notify-validate';

		// Get current arg separator
		$arg_separator = $this->get_php_arg_separator_output();

		// Verify there is a post_data
		if ( $post_data || strlen( $post_data ) > 0 ) {
			// Append the data
			$encoded_data .= $arg_separator . $post_data;
		} else {
			// Check if POST is empty
			if ( empty( $_POST ) ) {
				// Nothing to do
				return;
			} else {
				// Loop through each POST
				foreach ( $_POST as $key => $value ) {
					// Encode the value and append the data
					$encoded_data .= $arg_separator . "$key=" . urlencode( $value );
				}
			}
		}

		// Convert collected post data to an array
		parse_str( $encoded_data, $encoded_data_array );

		foreach ( $encoded_data_array as $key => $value ) {

			if ( false !== strpos( $key, 'amp;' ) ) {
				$new_key = str_replace( '&amp;', '&', $key );
				$new_key = str_replace( 'amp;', '&', $new_key );

				unset( $encoded_data_array[ $key ] );
				$encoded_data_array[ $new_key ] = $value;
			}

		}

		/**
		 * PayPal Web IPN Verification
		 *
		 * Allows filtering the IPN Verification data that PayPal passes back in via IPN with PayPal Standard
		 *
		 * @since 2.8.13
		 *
		 * @param array $data      The PayPal Web Accept Data
		 */
		$encoded_data_array = apply_filters( 'ssa_process_paypal_ipn_data', $encoded_data_array );
 		// phpcs:ignore
		 ssa_debug_log( 'encoded_data_array data array: ' . print_r( $encoded_data_array, true ) );
		 
		 $verify_should_process = $this->confirm_payment_for_ssa( $encoded_data_array );
		 if ( is_wp_error( $verify_should_process ) ) {
			// phpcs:ignore
			ssa_debug_log( 'Skipped processing payment - details: ' . print_r( $verify_should_process, true ) );
			return;
		}


		// Validate the IPN
		$remote_post_vars = array(
			'method'      => 'POST',
			'timeout'     => 45,
			'redirection' => 5,
			'httpversion' => '1.1',
			'blocking'    => true,
			'headers'     => array(
				'host'         => 'www.paypal.com',
				'connection'   => 'close',
				'content-type' => 'application/x-www-form-urlencoded',
				'post'         => '/cgi-bin/webscr HTTP/1.1',
				'user-agent'   => 'SSA IPN Verification/' . Simply_Schedule_Appointments::VERSION . '; ' . get_bloginfo( 'url' )

			),
			'sslverify'   => false,
			'body'        => $encoded_data_array
		);
 		// phpcs:ignore
		ssa_debug_log( 'Attempting to verify PayPal IPN. Data sent for verification: ' . print_r( $remote_post_vars, true ) );

		// Get response
		$api_response = wp_remote_post( $this->get_paypal_redirect( true, true ), $remote_post_vars );

		if ( is_wp_error( $api_response ) ) {
			// ssa_record_gateway_error( __( 'IPN Error', 'simply-schedule-appointments' ), sprintf( __( 'Invalid IPN verification response. IPN data: %s', 'simply-schedule-appointments' ), json_encode( $api_response ) ) );
			ssa_debug_log( 'Invalid IPN verification response. IPN data: ' . print_r( $api_response, true ) ); // phpcs:ignore

			return; // Something went wrong
		}

		if ( wp_remote_retrieve_body( $api_response ) !== 'VERIFIED' && ssa_get_option( 'disable_paypal_verification', false ) ) {
			// ssa_record_gateway_error( __( 'IPN Error', 'simply-schedule-appointments' ), sprintf( __( 'Invalid IPN verification response. IPN data: %s', 'simply-schedule-appointments' ), json_encode( $api_response ) ) );
			ssa_debug_log( 'Invalid IPN verification response. IPN data: ' . print_r( $api_response, true ) ); // phpcs:ignore

			return; // Response not okay
		}

		ssa_debug_log( 'IPN verified successfully' );

		// Check if $post_data_array has been populated
		if ( ! is_array( $encoded_data_array ) && ! empty( $encoded_data_array ) ) {
			return;
		}

		$defaults = array(
			'txn_type'       => '',
			'payment_status' => ''
		);

		$encoded_data_array = wp_parse_args( $encoded_data_array, $defaults );

		$payment_id = 0;

		// if ( ! empty( $encoded_data_array[ 'parent_txn_id' ] ) ) {
		// 	$payment_id = ssa_get_purchase_id_by_transaction_id( $encoded_data_array[ 'parent_txn_id' ] );
		// } elseif ( ! empty( $encoded_data_array[ 'txn_id' ] ) ) {
		// 	$payment_id = ssa_get_purchase_id_by_transaction_id( $encoded_data_array[ 'txn_id' ] );
		// }

		if ( empty( $payment_id ) ) {
			$payment_id = ! empty( $encoded_data_array[ 'custom' ] ) ? absint( str_replace('ssa_', '', $encoded_data_array[ 'custom' ] ) ) : 0;
		}

		if ( has_action( 'ssa_paypal_' . $encoded_data_array['txn_type'] ) ) {
			// Allow PayPal IPN types to be processed separately
			do_action( 'ssa_paypal_' . $encoded_data_array['txn_type'], $encoded_data_array, $payment_id );
		} else {
			// Fallback to web accept just in case the txn_type isn't present
			do_action( 'ssa_paypal_web_accept', $encoded_data_array, $payment_id );
		}
		exit;

// 		$listener = new SSA_Paypal_Ipn_Listener();

// 		if( $this->is_test_mode() ) {
// 			$listener->use_sandbox = true;
// 		}

// 		$listener->use_ssl = false;

// 		// $listener->use_curl = false;
// error_log( 'process' );
		
// 		try {
// 			$listener->requirePostMethod();
// 			$verified = $listener->processIpn();
// 		} catch( Exception $e ) {
// 			wp_mail( get_bloginfo('admin_email'), __( 'Paypal IPN Error', 'simply-schedule-appointments' ), $e->getMessage() );
// 			exit(0);
// 		}

// 		if( $verified ) {
// error_log( 'verified' );
// error_log( print_r( $_POST, true ) );
// 			$payment_id 		= (int) sanitize_text_field( $_POST['custom'] );
// 			$appointment_id	 	= (int) sanitize_text_field( $_POST['item_number'] );
// 			if ( empty( $appointment_id ) ) {
// 				return;
// 			}
// 			$appointment_obj 	= new SSA_Appointment_Object( $appointment_id );
			
// 			$paypal_amount   	= $_POST['mc_gross'];
// 			$payment_status 	= $_POST['payment_status'];
// 			$currency_code		= $_POST['mc_currency'];

// 			// retrieve the meta info for this payment
// 			$payment = $this->plugin->payment_model->get( $payment_id );
// 			if ( empty( $payment['gateway'] ) || 'paypal' != $payment['gateway'] ) {
// 				return;
// 			}

// 			$payment_amount = $payment['amount_attempted'];

// 			$appointment_type_payments = $appointment_obj->get_appointment_type()->payments;
// 			if ( empty( $appointment_type_payments['currency'] ) ) {
// 				return;
// 			}

// 			if( $currency_code != $appointment_type_payments['currency'] ) {
// 				return; // the currency code is invalid
// 			}
// 			if( $paypal_amount != $payment_amount ) {
// 				return; // the prices don't match
// 			}

// 			if( isset( $_POST['txn_type'] ) && $_POST['txn_type'] == 'web_accept' ) {

// 				$status = strtolower( $payment_status );

// 				if( $status == 'completed' ) {
// 					// set the payment to complete. This also sends the emails
// 					if ( $payment['status'] == 'pending' ) {
// 						$this->plugin->payments->update( $payment_id, array(
// 							'status' => 'succeeded',
// 						) );
// 					}

// 				} else if( $status == 'refunded' ) {
// 					// this refund process doesn't work yet
// 				}
// 			}

// 		} else {
// 			// wp_mail( get_bloginfo('admin_email'), __( 'Invalid IPN', 'edd' ), $listener->getTextReport() );
// 		}
	}

	/**
	 * Get PayPal Redirect
	 *
	 * @param bool    $ssl_check Is SSL?
	 * @param bool    $ipn       Is this an IPN verification check?
	 * @return string
	 */
	public function get_paypal_redirect( $ssl_check = false, $ipn = false ) {

		$protocol = 'http://';
		if ( is_ssl() || ! $ssl_check ) {
			$protocol = 'https://';
		}

		// Check the current payment mode
		if ( $this->is_test_mode() ) {

			// Test mode

			if( $ipn ) {

				$paypal_uri = 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';

			} else {

				$paypal_uri = $protocol . 'www.sandbox.paypal.com/cgi-bin/webscr';

			}

		} else {

			// Live mode

			if( $ipn ) {

				$paypal_uri = 'https://ipnpb.paypal.com/cgi-bin/webscr';

			} else {

				$paypal_uri = $protocol . 'www.paypal.com/cgi-bin/webscr';

			}

		}

		return apply_filters( 'ssa_paypal_uri', $paypal_uri, $ssl_check, $ipn );
	}

	/**
	 * Get PHP Arg Separator Output
	 *
	 * @since 1.0.8.3
	 * @return string Arg separator output
	 */
	public function get_php_arg_separator_output() {
		return ini_get( 'arg_separator.output' );
	}

	public function process_paypal_web_accept_and_cart( $data, $payment_id ) {
		/**
		 * PayPal Web Accept Data
		 *
		 * Allows filtering the Web Accept data that PayPal passes back in via IPN with PayPal Standard
		 *
		 * @since 2.8.13
		 *
		 * @param array $data      The PayPal Web Accept Data
		 * @param int  $payment_id The Payment ID associated with this IPN request
		 */
		$data = apply_filters( 'ssa_paypal_web_accept_and_cart_data', $data, $payment_id );

		if ( $data['txn_type'] != 'web_accept' && $data['txn_type'] != 'cart' && $data['payment_status'] != 'Refunded' ) {
			return;
		}

		if( empty( $payment_id ) ) {
			return;
		}

		$payment = new SSA_Payment_Object( $payment_id );

		// Collect payment details
		$purchase_key   = isset( $data['invoice'] ) ? $data['invoice'] : $data['item_number'];
		$paypal_amount  = $data['mc_gross'];
		$payment_status = strtolower( $data['payment_status'] );
		$currency_code  = strtolower( $data['mc_currency'] );
		$business_email = isset( $data['business'] ) && is_email( $data['business'] ) ? trim( $data['business'] ) : trim( $data['receiver_email'] );


		if ( $payment->gateway != 'paypal' ) {
			return; // this isn't a PayPal standard IPN
		}

		// Verify payment recipient
		if ( strcasecmp( $business_email, trim( $this->plugin->paypal_settings->get()['email'] ) ) != 0 ) {
			ssa_debug_log( 'Invalid business email in IPN response. IPN data: ' . print_r( $data, true ) ); // phpcs:ignore
			$payment->update( array(
				'status' => 'failed',
				'notes' => __( 'Payment failed due to invalid PayPal business email.', 'simply-schedule-appointments' ),
			) );
			return;
		}

		// Verify payment currency
		if ( $currency_code != strtolower( $payment->currency ) ) {
			ssa_debug_log( 'Invalid currency in IPN response. IPN data: ' . print_r( $data, true ) ); // phpcs:ignore
			$payment->update( array(
				'status' => 'failed',
				'notes' => __( 'Payment failed due to invalid currency in PayPal IPN.', 'simply-schedule-appointments' ),
			) );

			return;
		}

// TODO: Add customer support for multiple emails
		// if( empty( $customer ) ) {

		// 	$customer = new SSA_Customer( $payment->customer_id );

		// }

		// Record the payer email on the SSA_Customer record if it is different than the email entered on checkout
		// if( ! empty( $data['payer_email'] ) && ! in_array( strtolower( $data['payer_email'] ), array_map( 'strtolower', $customer->emails ) ) ) {

		// 	$customer->add_email( strtolower( $data['payer_email'] ) );

		// }
// END TODO

		if ( $payment_status == 'refunded' || $payment_status == 'reversed' ) {
			// TODO
			$payment->update( array(
				'status' => 'refunded',
				'amount_refunded' => number_format( (float) $paypal_amount, 2 ),
			) );

			// Process a refund
			// ssa_process_paypal_refund( $data, $payment_id );
		} else {
			if ( $payment->status === 'succeeded' ) {
				return; // Only complete payments once
			}

			// Retrieve the total purchase amount (before PayPal)
			$payment_amount = $payment->amount_attempted;

			if ( number_format( (float) $paypal_amount, 2 ) < number_format( (float) $payment_amount, 2 ) ) {
				// The prices don't match
				ssa_debug_log( 'Invalid payment amount in IPN response. IPN data: ' . printf( $data, true ) );
				$payment->update( array(
					'status' => 'failed',
					'notes' => __( 'Payment failed due to invalid amount in PayPal IPN.', 'simply-schedule-appointments' ),
				) );
				return;
			}
			if ( $purchase_key != $payment->purchase_key ) {
				// Purchase keys don't match
				ssa_debug_log( 'Invalid purchase key in IPN response. IPN data: ' . printf( $data, true ) );
				$payment->update( array(
					'status' => 'failed',
					'notes' => __( 'Payment failed due to invalid purchase key in PayPal IPN.', 'simply-schedule-appointments' ),
				) );
				return;
			}

			if ( 'completed' == $payment_status || $this->is_test_mode() ) {
				$payment->update( array(
					'status' => 'succeeded',
					'notes' => sprintf( __( 'PayPal Transaction ID: %s', 'simply-schedule-appointments' ) , $data['txn_id'] ),
					'gateway_transaction_id' => $data['txn_id'],
					'amount_paid' => number_format( (float) $paypal_amount, 2 ),
				) );
			} else if ( 'pending' == $payment_status && isset( $data['pending_reason'] ) ) {

				// Look for possible pending reasons, such as an echeck

				$note = '';

				switch( strtolower( $data['pending_reason'] ) ) {

					case 'echeck' :

						$note = __( 'Payment made via eCheck and will clear automatically in 5-8 days', 'simply-schedule-appointments' );
						$payment->update( array(
							'status' => 'processing',
						) );
						break;

					case 'address' :

						$note = __( 'Payment requires a confirmed customer address and must be accepted manually through PayPal', 'simply-schedule-appointments' );

						break;

					case 'intl' :

						$note = __( 'Payment must be accepted manually through PayPal due to international account regulations', 'simply-schedule-appointments' );

						break;

					case 'multi-currency' :

						$note = __( 'Payment received in non-shop currency and must be accepted manually through PayPal', 'simply-schedule-appointments' );

						break;

					case 'paymentreview' :
					case 'regulatory_review' :

						$note = __( 'Payment is being reviewed by PayPal staff as high-risk or in possible violation of government regulations', 'simply-schedule-appointments' );

						break;

					case 'unilateral' :

						$note = __( 'Payment was sent to non-confirmed or non-registered email address.', 'simply-schedule-appointments' );

						break;

					case 'upgrade' :

						$note = __( 'PayPal account must be upgraded before this payment can be accepted', 'simply-schedule-appointments' );

						break;

					case 'verify' :

						$note = __( 'PayPal account is not verified. Verify account in order to accept this payment', 'simply-schedule-appointments' );

						break;

					case 'other' :

						$note = __( 'Payment is pending for unknown reasons. Contact PayPal support for assistance', 'simply-schedule-appointments' );

						break;

				}

				if( ! empty( $note ) ) {

					ssa_debug_log( 'Payment not marked as completed because: ' . $note );
					$payment->update( array(
						'notes' => $note,
					) );


				}

			}
		}
	}

}
