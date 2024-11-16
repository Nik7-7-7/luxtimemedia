<?php

namespace MotoPress\Appointment\Emails\Tags\Booking;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
class BookingIdTag extends AbstractBookingEntityTag {

	public function getName(): string {
		return 'booking_id';
	}

	protected function description(): string {
		return esc_html__( 'Booking ID', 'motopress-appointment' );
	}

	public function getTagContent(): string {
		$id = $this->entity->getId();

		return strval( $id );
	}
}
