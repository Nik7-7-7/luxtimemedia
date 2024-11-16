<?php

namespace MotoPress\Appointment\DirectLinkActions\Helpers;

use MotoPress\Appointment\Entities\Booking;

/**
 * @since 1.15.0
 */
class BookingHelper {

	/**
	 * @param Booking $booking
	 *
	 * @return bool
	 */
	public static function isBookingDatePast( Booking $booking ) {
		$reservations = $booking->getReservations();
		foreach ( $reservations as $reservation ) {
			if ( current_datetime() > $reservation->getServiceTime()->getStartTime() ) {
				return true;
			}
		}

		return false;
	}


	/**
	 * @param Booking $booking
	 *
	 * @return bool
	 */
	public static function isCanBeCancelled( $booking ) {
		if ( ! mpapp()->settings()->isUserCanBookingCancellation() ) {
			return false;
		}

		if ( self::isBookingDatePast( $booking ) ) {
			return false;
		}

		return true;
	}
}
