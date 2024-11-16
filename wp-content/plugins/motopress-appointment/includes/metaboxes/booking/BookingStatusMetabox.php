<?php

namespace MotoPress\Appointment\Metaboxes\Booking;

use MotoPress\Appointment\Metaboxes\SubmitMetabox;
use MotoPress\Appointment\Helpers\AvailabilityHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
class BookingStatusMetabox extends SubmitMetabox {

	/**
	 * @param int $postId
	 * @param \WP_Post $post
	 *
	 * @return bool
	 * @throws \Exception
	 */
	protected function canSave( int $postId, \WP_Post $post ): bool {
		$canSave = parent::canSave( $postId, $post );

		$values = $this->parseValues( $postId, $post );

		if ( ! isset( $values['post_status'] ) ) {
			return true;
		}

		$booking = mpapp()->repositories()->booking()->findById( $postId );

		if ( ! $booking ) {
			return true;
		}

		// status was not changed
		if ( $booking->getStatus() === $values['post_status'] ) {
			return true;
		}

		$unblockedTimeSlotsStatuses = mpapp()->postTypes()->booking()->statuses()->getUnblockedTimeSlotsStatuses();

		// if current booking has a time-slot conflict which any bookings,
		// prevent updating post status and show error notice
		if ( ! in_array( $values['post_status'], $unblockedTimeSlotsStatuses ) &&
		     ! AvailabilityHelper::isSlotsAvailableForBooking( $booking->getReservations() )
		) {
			$errorMessage = __( "Can't update a status of this booking because the time is already booked.", 'motopress-appointment' );
			throw new \Exception( $errorMessage );
		}

		return $canSave;
	}
}
