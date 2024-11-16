<?php
/**
 * Simply Schedule Appointments Payments.
 *
 * @since   2.0.1
 * @package Simply_Schedule_Appointments
 */

/**
 * Simply Schedule Appointments Payments.
 *
 * @since 2.0.1
 */
class SSA_Payments {
	/**
	 * Parent plugin class.
	 *
	 * @since 2.0.1
	 *
	 * @var   Simply_Schedule_Appointments
	 */
	protected $plugin = null;

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
		/* Appointment Hooks */
		add_filter( 'ssa/appointment/before_insert', array( $this, 'filter_appointment_insert_status' ), 10, 1 );
		add_filter( 'ssa/appointment/before_update', array( $this, 'filter_appointment_update_status' ), 10, 3 );

		add_action( 'ssa/appointment/after_insert', array( $this, 'schedule_pending_payment_cleanup' ), 10, 2 );
		add_action( 'ssa/appointment/after_update', array( $this, 'schedule_pending_payment_cleanup' ), 10, 3 );
		add_action( 'ssa_cleanup_pending_payments', array( $this, 'cleanup_pending_payments' ), 10, 2 );

		add_filter('ssa/templates/get_template_vars', array($this, 'add_appointment_template_vars'), 10, 2);
		/* Payment Hooks */
		add_action( 'ssa/payment/after_insert', array( $this, 'update_appointment_payment_received' ), 5, 2 );
		add_action( 'ssa/payment/after_update', array( $this, 'update_appointment_payment_received' ), 5, 2 );
		add_action( 'ssa/payment/after_insert', array( $this, 'remove_pending_payment_status_after_successful_payment' ), 10, 3 );
		add_action( 'ssa/payment/after_update', array( $this, 'remove_pending_payment_status_after_successful_payment' ), 10, 3 );
	}

	public function add_appointment_template_vars($vars, $template) {
		if (!empty($vars['Appointment']['payment_received']) && $vars['Appointment']['payment_received'] > 0) {
			$vars['Appointment']['payment_status'] = __( 'Paid', 'simply-schedule-appointments' );
		} else {
			$vars['Appointment']['payment_status'] = __( 'Unpaid', 'simply-schedule-appointments' );
		}
		
		return $vars;
	}

	public function schedule_pending_payment_cleanup( $appointment_id, $data, $data_before = array() ) {
		if ( empty( $data['status'] ) || 'pending_payment' !== $data['status'] ) {
			return;
		}

		if ( !empty( $data_before['status'] ) && $data_before['status'] === 'pending_payment' ) {
			return;
		}

		$payload = array();

		$seconds_to_hold_pending_payment = 30 * 60; // 30 minute default
		if ( ! empty( $data['payment_method'] ) && $data['payment_method'] == 'paypal' ) {
			// Paypal can hold e-check payments for up to 6 days! 
			// https://www.paypal.com/us/smarthelp/article/i-sent-an-echeck-but-the-payment-is-pending.-why-faq572
			$seconds_to_hold_pending_payment = 9 * DAY_IN_SECONDS;
		}
		$seconds_to_hold_pending_payment = apply_filters( 'ssa/payments/pending_payment_duration', $seconds_to_hold_pending_payment, $appointment_id, $data );
		
		$appointment_update_data = array(
			'expiration_date' => gmdate( 'Y-m-d H:i:s', time() + $seconds_to_hold_pending_payment ),
		);

		$response = $this->plugin->appointment_model->update( $appointment_id, $appointment_update_data );

		ssa_queue_action( 'appointment_booked_pending_payment', 'ssa_cleanup_pending_payments', 10, $payload, 'appointment', $appointment_id, 'payments', array(
			'date_queued' => gmdate( 'Y-m-d H:i:s', time() + $seconds_to_hold_pending_payment ),
		) );
	}

	public function cleanup_pending_payments( $payload, $async_action ) {
		$appointments_pending_payment = $this->plugin->appointment_model->query( array(
			'status' => 'pending_payment',
			'id' => $async_action['object_id'],
		) );

		foreach ($appointments_pending_payment as $key => $appointment) {
			if ( empty( $appointment['status'] ) || 'pending_payment' !== $appointment['status'] ) {
				continue;
			}

			$appointment_update_data = array(
				'expiration_date' => false,
				'status' => 'abandoned',
			);

			$response = $this->plugin->appointment_model->update( $appointment['id'], $appointment_update_data );
			ssa_complete_action( $async_action['id'], $response );
		}
	}

	public function update_appointment_payment_received( $payment_id, $data ) {
		if ( empty( $data['appointment_id'] ) ) {
			$payment = new SSA_Payment_Object( $payment_id );
			$appointment_id = $payment->appointment_id;
			if ( empty( $appointment_id ) ) {
				return;
			}

			$data['appointment_id'] = $appointment_id;
		}

		$payments = $this->plugin->payment_model->query( array(
			'appointment_id' => $data['appointment_id'],
		) );

		$payment_received = 0.00;
		foreach ($payments as $key => $payment) {
			if ( $payment['status'] !== 'succeeded' && $payment['status'] !== 'refunded' ) {
				continue;
			}

			if ( ! empty( $payment['amount_refunded'] ) && $payment['amount_refunded'] > 0 ) {
				$payment_received -= (float)$payment['amount_refunded'];
			}


			if ( empty( $payment['amount_paid'] ) || $payment['amount_paid'] < 0 ) {
				continue;
			}

			$payment_received += (float)$payment['amount_paid'];
		}

		$appointment = new SSA_Appointment_Object( $data['appointment_id'] );
		
		$appointment_update_data = array(
			'payment_received' => $payment_received,
		);
		
		// TODO: potentially capture refunds and affect status
		// if ( $payment_received <= 0 ) {
			// 	$appointment_update_data['status'] = 'refunded';
			// }
			
		// if this is an incoming webhook of stripe PaymentIntent.failed and the appointment has no previous successful payments mark the appointment as abandoned
		// only if event type is payment_intent.payment_failed mark the appointment as abandoned
		if ( isset( $_GET['ssa-listener'] ) && $_GET['ssa-listener'] === 'stripe' && $payment_received == 0 ) {
			if ( property_exists( $appointment, "status" ) && 'pending_payment' === $appointment->status ) {
				$payload = json_decode( @file_get_contents( 'php://input' ) );
				if( isset( $payload->type ) && $payload->type === "payment_intent.payment_failed" ){
					$appointment_update_data = array_merge($appointment_update_data,array(
						'expiration_date' => false,
						'status' => 'abandoned',
					));
				}
			}
		}
		
		$response = $this->plugin->appointment_model->update( $data['appointment_id'], $appointment_update_data );
		

		$rescheduled_to_appointment_id = $appointment->rescheduled_to_appointment_id;
		if ( empty( $rescheduled_to_appointment_id ) ) {
			return;
		}

		$response = $this->plugin->appointment_model->update( $rescheduled_to_appointment_id, array(
			'date_modified' => gmdate( 'Y-m-d H:i:s' ),  // This will force the new/rescheduled appointment to check and see if it should be marked as booked now that the payment amount is updated on the original/canceled appointment
		) );

	}

	public function remove_pending_payment_status_after_successful_payment( $payment_id, $data, $data_before = array() ) {
		if ( empty( $data['status'] ) || 'succeeded' !== $data['status'] ) {
			return;
		}

		if ( empty( $data['appointment_id'] ) ) {
			if ( ! empty( $data_before['appointment_id'] ) ) {
				$data['appointment_id'] = $data_before['appointment_id'];
			}

			if ( empty( $data['appointment_id'] ) ) {
				return;
			}
		}

		$appointment_obj = new SSA_Appointment_Object( $data['appointment_id'] );
		if ( $appointment_obj->status !== 'pending_payment' && $appointment_obj->status !== 'abandoned' ) {
			return;
		}
		
		if ( $appointment_obj->status === 'pending_payment' ) {
			remove_filter( 'ssa/appointment/before_update', array( $this, 'filter_appointment_update_status' ), 10, 3 );
			$appointment_update_data = array(
				'expiration_date' => false,
				'status' => 'booked',
			);
			$response = $this->plugin->appointment_model->update( $data['appointment_id'], $appointment_update_data );
			add_filter( 'ssa/appointment/before_update', array( $this, 'filter_appointment_update_status' ), 10, 3 );

			return;
		} else if ( $appointment_obj->status === 'abandoned' ) {
			if ( $this->plugin->appointment_model->is_prospective_appointment_available( $appointment_obj->get_appointment_type(), $appointment_obj->start_date_datetime ) ) {
				remove_filter('ssa/appointment/before_update', array($this, 'filter_appointment_update_status'), 10, 3);
				$appointment_update_data = array(
					'expiration_date' => false,
					'status' => 'booked',
				);
				$response = $this->plugin->appointment_model->update($data['appointment_id'], $appointment_update_data);
				add_filter('ssa/appointment/before_update', array($this, 'filter_appointment_update_status'), 10, 3);
			} else {
				remove_filter('ssa/appointment/before_update', array($this, 'filter_appointment_update_status'), 10, 3);
				$appointment_update_data = array(
					'status' => 'canceled',
				);
				$response = $this->plugin->appointment_model->update($data['appointment_id'], $appointment_update_data);
				add_filter('ssa/appointment/before_update', array($this, 'filter_appointment_update_status'), 10, 3);
			}
		} else {
			ssa_debug_log( $appointment_obj, 10, 'Appointment status != abandoned or pending_payment' );
		}
	}

	public function filter_appointment_insert_status( $data ) {
		if ( ! ssa()->settings_installed->is_enabled( 'stripe' ) && ! ssa()->settings_installed->is_enabled( 'paypal' ) ) {
			return $data;
		}

		if ( empty( $data['appointment_type_id'] ) ) {
			return $data;
		}

		if ( ! empty( $data['status'] ) && $data['status'] === 'pending_form' ) {
			// Payments aren't handled when using our form integration
			// so we won't override the status in this case
			return $data;
		}

		$appointment_type_obj = new SSA_Appointment_Type_Object( $data['appointment_type_id'] );
		$payments = $appointment_type_obj->payments;
		if ( empty( $payments['payment_required'] ) ) {
			return $data;
		}

		// price should be parsed as float for the rare cases where the price is less than 1.00
		if ( $payments['payment_required'] === 'required' && ! empty( (float) $payments['price'] ) ) {
			$data['status'] = 'pending_payment';
		}

		if ( $payments['payment_required'] === 'optional' && !empty( $data['payment_method'] && "pay_later" !== $data["payment_method"] ) ) {
				$data['status'] = 'pending_payment';
		}

		if ( $data['status'] === 'pending_payment' && ! empty( $data['rescheduled_from_appointment_id'] ) ) {
			$original_appointment_obj = new SSA_Appointment_Object( (int) $data['rescheduled_from_appointment_id'] );

			$data['status'] = $original_appointment_obj->status;
			$data['expiration_date'] = false;
			$data['payment_received'] = $original_appointment_obj->payment_received;
			$data['payment_method'] = $original_appointment_obj->payment_method;
		}

		return $data;
	}

	public function filter_appointment_update_status( $data, $data_before, $appointment_id ) {
		if ( ! ssa()->settings_installed->is_enabled( 'stripe' ) && ! ssa()->settings_installed->is_enabled( 'paypal' ) ) {
			return $data;
		}

		if ( current_user_can( 'ssa_manage_appointments' ) ) {
			return $data;
		}

		if ( in_array( $data_before['status'], array( 'booked', 'canceled' ) ) ) {
			return $data;
		}

		if ( ! empty( $data['status'] ) && in_array( $data['status'], array( 'abandoned', 'canceled' ) ) ) {
			// If appointment is being marked canceled or abandoned, don't override
			return $data;
		}

		if ( ( ! empty( $data['status'] ) && $data['status'] === 'pending_form' ) || ( ! empty( $data_before['status'] ) && $data_before['status'] === 'pending_form' ) ) {
			// Payments aren't handled when using our form integration
			// so we won't override the status in this case
			return $data;
		}

		$appointment = new SSA_Appointment_Object( $appointment_id );
		if ( empty( $data['appointment_type_id'] ) ) {
			$appointment_type_id = $appointment->appointment_type_id;
			if ( empty( $appointment_type_id ) ) {
				return $data;
			}

			$data['appointment_type_id'] = $appointment_type_id;
		}

		$appointment_type_obj = new SSA_Appointment_Type_Object( $data['appointment_type_id'] );
		$payments = $appointment_type_obj->payments;
		if ( empty( $payments['payment_required'] ) ) {
			return $data;
		}

		if ( $payments['payment_required'] === 'required' ) {
			$data['status'] = 'pending_payment';
		} elseif ( ! empty( $data["payment_method"] ) && "pay_later" == $data["payment_method"] && "pending_payment" == $appointment->status) {
			// optional or no payment required
			// switched from a pending_payment attempt (Stripe modal) to "Pay Later"
			$data['status'] = 'booked';
			$data['expiration_date'] = false;
		} else {
			$data['status'] = 'pending_payment';
		}

		if ( ! isset( $data['rescheduled_from_appointment_id'] ) ) {
			$data['rescheduled_from_appointment_id'] = $appointment->rescheduled_from_appointment_id;
		}
		if ( $data['status'] === 'pending_payment' && ! empty( $data['rescheduled_from_appointment_id'] ) ) {
			$original_appointment_obj = new SSA_Appointment_Object( (int) $data['rescheduled_from_appointment_id'] );

			if ( $original_appointment_obj->status === 'canceled' && $original_appointment_obj->payment_received > 0 ) {
				$data['status'] = 'booked';
				$data['expiration_date'] = false;
			}
		}

		return $data;
	}

	/**
	 * Given an specific appointment and payment gateway, process the payment and return the payment data.
	 *
	 * @param  int    $appointment_id  The appointment ID.
	 * @param  string $gateway         The payment method.
	 * @param  array  $gateway_payload The gateway payload.
	 *
	 * @return array                   The payment data.
	 */
	public function process_payment( $appointment_id, $gateway, $gateway_payload ) {
		$wp_error     = new WP_Error();
		
		if(isset( $gateway_payload['error'] ) && !empty( $gateway_payload['error'] ) ){
			ssa_debug_log( print_r( $gateway_payload, true ), 10 ); // phpcs:ignore
			$wp_error->add( 400, $gateway_payload['error'] );
			return $wp_error;
		}
		
		$payment_data = array(
			'payment_mode'           => '', // live or test.
			'gateway_transaction_id' => '',
			'user_email'             => '',
			'user_ip'                => '',
			'currency'               => '',
			'payment_total'          => '',
			'status'                 => '', // succeeded, failed.
			'payment_meta'           => array(
				'outcome' => array(),
				'paid'    => '',
			),
		);

		if ( empty( $gateway ) ) {
			$wp_error->add( 400, 'No payment gateway specified' );
			return $wp_error;
		}

		$args = array(
			'amount'   => $this->get_amount_for_appointment_id( $appointment_id ),
			'currency' => $this->get_currency_for_appointment_id( $appointment_id ),
		);

		$payment_data['currency']         = $args['currency'];
		$payment_data['amount_attempted'] = $args['amount'];

		if ( 'stripe' === $gateway ) {
			if($args['currency'] !== 'JPY'){
				$args['amount'] = round( 100 * $args['amount'] ); // Stripe wants to receive it in pennies.
			}

			$payment_intent = $this->plugin->stripe->process_payment( $appointment_id, $gateway_payload, $args );
			if ( ! empty( $payment_intent->livemode ) && ( true === $payment_intent->livemode || 'true' === $payment_intent->livemode ) ) {
				$payment_data['payment_mode'] = 'live';
			} else {
				$payment_data['payment_mode'] = 'test';
			}

			if ( empty( $payment_intent ) || is_a( $payment_intent, 'Exception' ) ) {
				$payment_data['status']                             = 'failed';
				$payment_data['payment_meta']['outcome']['message'] = __( 'An error occurred processing your payment', 'simply-schedule-appointments' );
				$payment_data['payment_meta']['paid']               = false;
				$payment_data['payment_meta']['captured']           = false;

				try {
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

					if ( ! empty( $payment_intent->last_payment_error->code ) ) {
						$stripe_code = $payment_intent->last_payment_error->code;
					} else if ( method_exists( $payment_intent, 'getStripeCode' ) ) {
						$stripe_code = $payment_intent->getStripeCode();
					}

					if ( ! empty( $payment_intent->last_payment_error->decline_code ) ) {
						$decline_code = $payment_intent->last_payment_error->decline_code;
					} else if ( method_exists( $payment_intent, 'getDeclineCode' ) ) {
						$decline_code = $payment_intent->getDeclineCode();
					}


					$payment_data['gateway_response']           = $gateway_payload;
					$payment_data['gateway_transaction_id']     = empty( $payment_data['gateway_response'] ) ? "" : $payment_data['gateway_response']['id'];

					if ( ( isset( $stripe_code ) && strpos( $stripe_code, 'decline' ) !== false ) || ( isset( $decline_code ) && ! empty( $decline_code ) ) ) {
						$payment_data['payment_meta']['outcome']['code']    = $stripe_code;
						$payment_data['payment_meta']['outcome']['message'] = __( 'The credit card was declined', 'simply-schedule-appointments' );
						$payment_data['payment_meta']['outcome']['decline_code'] = $decline_code;
					}
				} catch ( Exception $e ) { // phpcs:ignore
					// Silently continue.
				}
			} else {
				// \Stripe\PaymentIntent contains a \Stripe\Charge Object that we can use to pull information about the charge.
				$current_charge = null;
				if ( ! empty( $payment_intent->charges ) ) {
					// Old Stripe API version ~2020
					$current_charge = $payment_intent->charges->first();
				} else if ( ! empty( $payment_intent->latest_charge ) ) {
					// New Stripe API version ~2022
					$current_charge = $payment_intent->latest_charge;
				}

				if ( ! empty( $current_charge ) ) {
					$payment_data['gateway_transaction_id'] = $payment_intent->id;
					$payment_data['gateway_charge_id']      = $current_charge->id;
					$payment_data['status']                 = $payment_intent->status;
	
					$payment_data['gateway_response'] = $payment_intent->toArray();
	
					if($args['currency'] === 'JPY'){
						$payment_data['amount_paid']     = (float) $payment_intent->amount;
					}else{
						$payment_data['amount_paid']     = (float) $payment_intent->amount / 100.00;
					}
		
					$payment_data['amount_refunded'] = $current_charge->amount_refunded;
	
					$payment_data['payment_meta']['outcome']      = $current_charge->outcome;
					$payment_data['payment_meta']['paid']         = $current_charge->paid;
					$payment_data['payment_meta']['amount']       = $current_charge->amount;
					$payment_data['payment_meta']['captured']     = $current_charge->captured;
					$payment_data['payment_meta']['failure_code'] = $current_charge->failure_code;
				}
			}
		} elseif ( 'paypal' === $gateway ) {
			if ( $this->plugin->paypal->is_test_mode() ) {
				$payment_data['payment_mode'] = 'test';
			} else {
				$payment_data['payment_mode'] = 'live';
			}

			$payment_data['status'] = 'pending';

			$payment_data['gateway_transaction_id'] = '';
			$payment_data['gateway_payload']        = '';
			$payment_data['gateway_response']       = '';
			$payment_data['amount_paid']            = 0.00;
			$payment_data['amount_refunded']        = 0.00;
		}

		return $payment_data;
	}

	public function get_amount_for_appointment_id( $appointment_id ) {
		$amount = 0;

		try {
			$appointment_obj = new SSA_Appointment_Object( $appointment_id );
		} catch ( Exception $e ) {
			return $amount;
		}

		$payment_settings = $appointment_obj->get_appointment_type()->payments;
		if ( ! empty( $payment_settings['price'] ) ) {
			$amount = (float)$payment_settings['price'];
		}

		return $amount;
	}

	public function get_currency_for_appointment_id( $appointment_id ) {
		$currency = 'USD';

		try {
			$appointment_obj = new SSA_Appointment_Object( $appointment_id );
		} catch ( Exception $e ) {
			return $currency;
		}

		$payment_settings = $appointment_obj->get_appointment_type()->payments;
		if ( ! empty( $payment_settings['currency'] ) ) {
			$currency = (string)$payment_settings['currency'];
		}

		return $currency;
	}

	public function generate_unique_purchase_key( $data = array() ) {
		$hash = SSA_Utils::hash( json_encode( $data ) . time() . rand( 0, 100000000 ) );
		$purchase_key = substr( $hash, 0, 10 );

		return $purchase_key;
	}
}
