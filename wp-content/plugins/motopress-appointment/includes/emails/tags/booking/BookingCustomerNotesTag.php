<?php

namespace MotoPress\Appointment\Emails\Tags\Booking;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.18.0
 */
class BookingCustomerNotesTag extends AbstractBookingEntityTag {

	public function getName(): string {
		return 'customer_notes';
	}

	protected function description(): string {
		return esc_html__( 'Customer notes', 'motopress-appointment' );
	}

	public function getTagContent(): string {
		return $this->getEntity()->getCustomerNotes();
	}
}