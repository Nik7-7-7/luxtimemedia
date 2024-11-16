<?php

namespace MotoPress\Appointment\Emails\Tags\Booking;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
class BookingAlreadyPaidTag extends AbstractBookingEntityTag {

	public function getName(): string {
		return 'booking_already_paid';
	}

	protected function description(): string {
		return esc_html__( 'Already paid', 'motopress-appointment' );
	}

	public function getTagContent(): string {

		$alreadyPaid = $this->entity->getPaidPrice();

		return mpa_tmpl_price( $alreadyPaid, array( 'literal_free' => false ) );
	}
}
