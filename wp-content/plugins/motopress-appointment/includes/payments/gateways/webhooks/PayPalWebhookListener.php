<?php

namespace MotoPress\Appointment\Payments\Gateways\Webhooks;

use MotoPress\Appointment\Entities\Payment;
use MotoPress\Appointment\Payments\Gateways\PayPalPaymentGateway;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.6.0
 */
class PayPalWebhookListener extends AbstractWebhooksListener {


	/**
	 * @since 1.6.0
	 */
	public function __construct( PayPalPaymentGateway $gateway ) {
		parent::__construct( $gateway );
	}

	/**
	 * @since 1.6.0
	 *
	 * @return string|false
	 */
	public function parseInput() {
		return @file_get_contents( 'php://input' );
	}

	/**
	 * @since 1.6.0
	 *
	 * @param string $rawInput
	 * @return array|false
	 */
	public function validateInput( $rawInput ) {

		$headers = getallheaders();
		$headers = array_change_key_case( $headers, CASE_UPPER );

		$jsonData = json_decode( $rawInput, true );

		$paypalWebhookInfo = get_option( PayPalPaymentGateway::OPTION_NAME_PAYPAL_WEBHOOK_ID );

		$signatureVerification = new \PayPal\Api\VerifyWebhookSignature();
		$signatureVerification->setAuthAlgo( $headers['PAYPAL-AUTH-ALGO'] );
		$signatureVerification->setTransmissionId( $headers['PAYPAL-TRANSMISSION-ID'] );
		$signatureVerification->setCertUrl( $headers['PAYPAL-CERT-URL'] );
		$signatureVerification->setWebhookId( $paypalWebhookInfo['webhook_id'] );
		$signatureVerification->setTransmissionSig( $headers['PAYPAL-TRANSMISSION-SIG'] );
		$signatureVerification->setTransmissionTime( $headers['PAYPAL-TRANSMISSION-TIME'] );

		$signatureVerification->setRequestBody( $rawInput );

		try {

			/** @var \PayPal\Api\VerifyWebhookSignatureResponse $requestVerification */
			$requestVerification = $signatureVerification->post( $this->gateway->getPayPalAPIContext() );

			if ( 'SUCCESS' == $requestVerification->getVerificationStatus() ) {

				if ( 'PAYMENT.CAPTURE.REFUNDED' == $jsonData['event_type'] &&
					isset( $jsonData['resource']['custom_id'] ) ) {

					return $jsonData;
				}
			}
		} catch ( \Exception $e ) {

			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( $e );

			if ( $e instanceof \PayPal\Exception\PayPalConnectionException ) {

				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( $e->getData() );
			}

			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log, WordPress.PHP.DevelopmentFunctions.error_log_var_export
			error_log( var_export( $jsonData, true ) );
		}

		http_response_code( 400 );
		return false;
	}

	/**
	 * @since 1.6.0
	 *
	 * @param array $eventObject
	 * @return Payment|null
	 */
	public function findPayment( $eventObject ) {

		$payment = null;

		$bookingId = absint( $eventObject['resource']['custom_id'] );
		$booking   = mpa_get_booking( $bookingId );

		if ( null !== $booking ) {

			$payment = $booking->getExpectingPayment();
		}

		if ( null === $payment ) {

			http_response_code( 400 );
		}

		return $payment;
	}

	/**
	 * @since 1.6.0
	 *
	 * @param Payment $payment
	 * @param array $eventObject
	 */
	public function processEvent( $payment, $eventObject ) {

		mpa_payment_manager()->refundPayment(
			$payment,
			esc_html__( 'Payment is refunded from the PayPal side.', 'motopress-appointment' )
		);

		http_response_code( 200 );
	}
}
