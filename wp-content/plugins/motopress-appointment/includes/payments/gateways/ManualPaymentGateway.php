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
class ManualPaymentGateway extends AbstractPaymentGateway {


	public function __construct() {

		parent::__construct();

		$this->isEnabled = true;
	}

	public function getId(): string {
		return 'manual';
	}

	public function getName(): string {
		return __( 'Manual Payment', 'motopress-appointment' );
	}

	public function isInternal(): bool {
		return true;
	}

	public function isSandboxModeEnabled(): bool {
		return false;
	}

	public function isOnlinePayment() {
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
		// You aren't supposed to be here, go away
		return $payment;
	}
}
