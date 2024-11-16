<?php

namespace MotoPress\Appointment\Emails\Tags\Booking;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
class BookingLeftToPayTag extends AbstractBookingEntityTag {

	public function getName(): string {
		return 'booking_left_to_pay';
	}

	protected function description(): string {
		return esc_html__( 'Left to pay', 'motopress-appointment' );
	}

	public function getTagContent(): string {
		$leftToPay = $this->entity->getToPayPrice();

		return mpa_tmpl_price( $leftToPay, array( 'literal_free' => false ) );
	}
}
