<?php

namespace MotoPress\Appointment\Payments\Gateways\Webhooks;

use MotoPress\Appointment\API\StripeAPI;
use MotoPress\Appointment\Entities\Payment;
use MotoPress\Appointment\Payments\Gateways\StripePaymentGateway;
use Stripe\Event;
use Exception;
use stdClass;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.5.0
 */
class StripeWebhooksListener extends AbstractWebhooksListener {

	/**
	 * @since 1.5.0
	 * @var StripePaymentGateway
	 */
	public $gateway = null;

	/**
	 * @since 1.5.0
	 * @var StripeAPI
	 */
	public $api = null;

	/**
	 * @since 1.5.0
	 * @var string
	 * @example 'source.chargeable'
	 */
	public $lastEventType = '';

	/**
	 * @since 1.5.0
	 * @var string
	 * @example 'source'
	 */
	public $lastEventObjectType = '';

	/**
	 * @since 1.5.0
	 * @var bool
	 */
	public $lastEventHasPaymentIntent = false;

	/**
	 * @since 1.5.0
	 *
	 * @param StripePaymentGateway $gateway
	 * @param StripeAPI $api
	 */
	public function __construct( $gateway, $api ) {
		parent::__construct( $gateway );

		$this->api = $api;
	}

	/**
	 * @since 1.5.0
	 *
	 * @return string|false
	 */
	public function parseInput() {

		// Reset last event data
		$this->lastEventObjectType       = '';
		$this->lastEventType             = '';
		$this->lastEventHasPaymentIntent = false;

		return @file_get_contents( 'php://input' );
	}

	/**
	 * @since 1.5.0
	 *
	 * @param string $rawInput
	 * @return stdClass|false
	 */
	public function validateInput( $rawInput ) {

		// See: https://stripe.com/docs/webhooks/signatures
		if ( ! isset( $_SERVER['HTTP_STRIPE_SIGNATURE'] ) ) {
			return false;
		}

		try {
			$this->api->registerApp();

			// See: https://stripe.com/docs/webhooks/go-live#create-endpoint
			$event = Event::constructFrom( json_decode( $rawInput, true ) );

			$this->lastEventType             = $event->type;
			$this->lastEventObjectType       = $event->data->object->object;
			$this->lastEventHasPaymentIntent = ! empty( $event->data->object->payment_intent );

			// Get payment_intent/charge object
			if ( 'payment_intent' === $this->lastEventObjectType || 'charge' === $this->lastEventObjectType ) {
				return $event->data->object;
			} else {
				return false;
			}
		} catch ( Exception $error ) {
			return false;
		}
	}

	/**
	 * @since 1.5.0
	 *
	 * @param stdClass $eventObject
	 * @return Payment|null
	 */
	public function findPayment( $eventObject ) {

		$payment = mpapp()->repositories()->payment()->findByTransactionId( $eventObject->id );

		if ( is_null( $payment ) && $this->lastEventHasPaymentIntent ) {
			$payment = mpapp()->repositories()->payment()->findByTransactionId( $eventObject->payment_intent );
		}

		return $payment;
	}

	/**
	 * @since 1.5.0
	 *
	 * @param Payment $payment
	 * @param stdClass $eventObject
	 */
	public function processEvent( $payment, $eventObject ) {

		switch ( $this->lastEventType ) {
			case 'payment_intent.canceled':
				// Translators: %s: Stripe Payment Intent ID.
				mpa_payment_manager()->failPayment( $payment, sprintf( esc_html__( 'Webhook received. Payment intent %s was canceled by customer.', 'motopress-appointment' ), $eventObject->id ) );

				break;

			case 'payment_intent.payment_failed':
				// Translators: %s: Stripe Payment Intent ID.
				mpa_payment_manager()->failPayment( $payment, sprintf( esc_html__( 'Webhook received. Payment intent %s failed and couldn\'t be processed.', 'motopress-appointment' ), $eventObject->id ) );

				break;

			case 'payment_intent.requires_action':
				// Translators: %s: Stripe Payment Intent ID.
				mpa_payment_manager()->holdPayment( $payment, sprintf( esc_html__( 'Webhook received. Payment intent %s is waiting for customer confirmation.', 'motopress-appointment' ), $eventObject->id ) );

				break;

			case 'payment_intent.succeeded':
				/**
				 * Payment confirmation is executed on the client side.
				 * On the server side for confirmation we executing processPayment() where getting up PaymentIntent object from StripeAPI and we setting up actualy payment status to our back-end.
				 * For successfully paid payments on the client side we are synchronously confirmed their in the processPayment method.
				 * All other payments received the STATUS_ON_HOLD status and will be confirmed via webhook.
				 * @see \MotoPress\Appointment\Payments\Gateways\StripePaymentGateway::processPayment
				 */
				if ( mpapp()->postTypes()->payment()->statuses()::STATUS_ON_HOLD === $payment->getStatus() ) {

					if ( empty( $payment->getPaymentMethod() ) ) {

						// we need to set payment method in case when
						// webhook is executed earlier than processPayment()
						// for example, in case of WeChat payment method
						try {
							$stripeGateway = mpapp()->payments()->stripe();
							$paymentIntent = $stripeGateway->getApi()->getPaymentIntent( $payment->getTransactionId() );
							$stripeGateway->setPaymentMethodToPayment( $paymentIntent, $payment );
							// phpcs:ignore
						} catch ( \Exception $e ) {
							// ignore error
						}
					}

					// Translators: %s: Stripe Payment Intent ID.
					mpa_payment_manager()->completePayment( $payment, sprintf( esc_html__( 'Webhook received. Payment intent %s succeeded.', 'motopress-appointment' ), $eventObject->id ) );
				}

				break;

			case 'charge.failed':
				if ( empty( $payment->getPaymentMethod() ) &&
					! empty( $eventObject->payment_method_details ) &&
					! empty( $eventObject->payment_method_details->type )
				) {

					// we need to set payment method in case when paiment is failed
					// because payment intent does not have payment method and
					// we can't set it in processPayment()
					try {
						$payment->setPaymentMethod( $eventObject->payment_method_details->type, true );
						// phpcs:ignore
					} catch ( \Exception $e ) {
						// ignore error
					}
				}
				// Translators: %s: Stripe Charge ID.
				mpa_payment_manager()->failPayment( $payment, sprintf( esc_html__( 'Webhook received. Charge %s failed.', 'motopress-appointment' ), $eventObject->id ) );

				break;

			case 'charge.refunded':
				// Translators: %s: Stripe Charge ID.
				mpa_payment_manager()->refundPayment( $payment, sprintf( esc_html__( 'Webhook received. Charge %s refunded.', 'motopress-appointment' ), $eventObject->id ) );

				break;
		}
	}
}
