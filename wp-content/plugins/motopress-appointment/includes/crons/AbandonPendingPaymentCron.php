<?php

namespace MotoPress\Appointment\Crons;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.5.0
 */
class AbandonPendingPaymentCron extends AbstractWPCron {


	public static function getCronActionHookName(): string {
		return 'mpa_abandon_pending_payment_cron';
	}


	public static function getCronStartIntervalInSeconds(): int {
		return mpapp()->settings()->getPendingPaymentTime() * MINUTE_IN_SECONDS;
	}


	public static function getCronStartIntervalId(): string {
		return 'mpa_pending_payment_interval';
	}


	public static function getCronStartIntervalDescription(): string {
		return 'Pending Payment Time set in Appointment Booking Settings.';
	}


	protected function executeCron() {

		$expiredPayments = mpapp()->repositories()->payment()->findAllExpired();

		foreach ( $expiredPayments as $payment ) {
			mpa_payment_manager()->abandonPayment( $payment, esc_html__( 'The customer didn\'t complete the payment in time.', 'motopress-appointment' ) );

			$payment->removeExpirationTime();
		}

		// Stop this cron if there are no more pending payments
		if ( ! mpapp()->repositories()->payment()->havePendingPayments() ) {
			$this->unschedule();
		}
	}
}
