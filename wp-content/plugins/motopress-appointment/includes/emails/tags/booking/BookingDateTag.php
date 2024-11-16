<?php

namespace MotoPress\Appointment\Emails\Tags\Booking;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
class BookingDateTag extends AbstractBookingEntityTag {

	public function getName(): string {
		return 'booking_date';
	}

	protected function description(): string {
		return esc_html__( 'Booking date', 'motopress-appointment' );
	}

	public function getTagContent(): string {
		$id = $this->entity->getId();

		return get_post_time( mpa_date_format(), false, $id, true );
	}
}
