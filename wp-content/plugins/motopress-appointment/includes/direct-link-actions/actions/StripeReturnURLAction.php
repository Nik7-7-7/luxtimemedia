<?php

namespace MotoPress\Appointment\DirectLinkActions\Actions;

use MotoPress\Appointment\Entities\Booking;
use MotoPress\Appointment\Entities\InterfaceUniqueEntity;


class StripeReturnURLAction extends AbstractBookingAction {

	const REDIRECT_STATUS_SUCCEEDED = 'succeeded';

	/**
	 * @return string
	 */
	protected function getActionName() {
		return 'stripe-redirect-url';
	}

	/**
	 * @param Booking $booking
	 *
	 * @return void
	 */
	protected function redirectToFailedTransactionPage( $booking ) {
		$paymentFailedTransactionPageURL = mpapp()->directLinkActions()->getFailedTransactionPage()->getActionURL( $booking );
		wp_safe_redirect( $paymentFailedTransactionPageURL );
		exit();
	}

	/**
	 * @param Booking $booking
	 *
	 * @return void
	 */
	protected function redirectToReservationReceivedPage( $booking ) {
		$paymentReceivedPageURL = mpapp()->directLinkActions()->getReservationReceivedPage()->getActionURL( $booking );
		wp_safe_redirect( $paymentReceivedPageURL );
		exit();
	}

	/**
	 * @param Booking $booking
	 */
	protected function action( InterfaceUniqueEntity $booking ) {
		if ( ! empty( $_GET['redirect_status'] ) &&
		     $_GET['redirect_status'] === self::REDIRECT_STATUS_SUCCEEDED
		) {
			$this->redirectToReservationReceivedPage( $booking );
		}

		$this->redirectToFailedTransactionPage( $booking );
	}
}