<?php

namespace MotoPress\Appointment\DirectLinkActions\Actions;

use MotoPress\Appointment\DirectLinkActions\Helpers\BookingHelper;
use MotoPress\Appointment\Entities\Booking;
use MotoPress\Appointment\PostTypes\Statuses\BookingStatuses;
use MotoPress\Appointment\Entities\InterfaceUniqueEntity;

/**
 * @since 1.15.0
 */
class BookingCancellationAction extends AbstractBookingAction {

	/**
	 * @return string
	 */
	protected function getActionName() {
		return 'booking-cancellation';
	}

	/**
	 * @param Booking $booking
	 *
	 * @return void
	 */
	protected function redirectToBookingCancelledPage( $booking ) {
		$bookingCancelledPageURL = mpapp()->directLinkActions()->getBookingCancelledPage()->getActionURL( $booking );
		wp_safe_redirect( $bookingCancelledPageURL );
		exit();
	}

	/**
	 * @param Booking $booking
	 *
	 * @return void
	 */
	protected function redirectToBookingCancellationPage( $booking ) {
		$bookingCancellationPageURL = mpapp()->directLinkActions()->getBookingCancellationPage()->getActionURL( $booking );
		wp_safe_redirect( $bookingCancellationPageURL );
		exit();
	}

	/**
	 * @param Booking $booking
	 */
	protected function action( InterfaceUniqueEntity $booking ) {
		if ( ! BookingHelper::isCanBeCancelled( $booking ) ) {
			$this->redirectToBookingCancellationPage( $booking );
		}

		$saved = mpa_update_status( $booking, BookingStatuses::STATUS_CANCELLED );

		if ( true === $saved ) {
			$customerId = $booking->getCustomerId();
			mpapp()->repositories()->customer()->updateLastActive( $customerId );

			$email = mpapp()->emails()->adminCancelledBooking();
			mpapp()->emailsDispatcher()->triggerEmail( $email, $booking );
		}

		$this->redirectToBookingCancelledPage( $booking );
	}
}
