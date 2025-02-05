<?php

namespace MotoPress\Appointment\Payments\Gateways;

use MotoPress\Appointment\Entities\Booking;
use MotoPress\Appointment\Entities\Payment;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.5.0
 */
class BankPaymentGateway extends AbstractInstructionPaymentGateway {


	public function getId(): string {
		return 'bank';
	}

	public function getName(): string {
		return __( 'Direct Bank Transfer', 'motopress-appointment' );
	}

	public function getDefaultPublicName(): string {
		return __( 'Direct bank transfer', 'motopress-appointment' );
	}

	protected function getDefaultDescription(): string {
		return __( 'Make your payment directly into our bank account. Please use your Booking ID as the payment reference.', 'motopress-appointment' );
	}

	public function isSupportsSandbox(): bool {
		return false;
	}

	/**
	 * Creates pending payment transaction which will be processed later.
	 * @param array $paymentData - can contains gateway specific data from frontend
	 * (for example, payment transaction id, token, payment intent id and so on)
	 * @return mixed any gateway specific data needed on frontend
	 */
	public function startPayment( Booking $booking, string $currencyCode, float $payingAmount, array $paymentData ) {

		parent::startPayment( $booking, $currencyCode, $payingAmount, $paymentData );

		return '';
	}

	/**
	 * @param Payment $payment
	 * @param array $paymentData[ 'booking' => Booking, ... any gateway specific data from frontend ]
	 * @return Payment
	 * @throws \Exception if something goes wrong
	 */
	public function processPayment( $payment, $paymentData ) {

		mpa_payment_manager()->holdPayment( $payment );
		return $payment;
	}
}
