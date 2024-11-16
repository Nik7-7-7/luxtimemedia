<?php

namespace MotoPress\Appointment\Emails\Tags\Booking;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.18.0
 */
class BookingCustomerEmailTag extends AbstractBookingEntityTag {

	public function getName(): string {
		return 'customer_email';
	}

	protected function description(): string {
		return esc_html__( 'Customer email', 'motopress-appointment' );
	}

	public function getTagContent(): string {
		return $this->getEntity()->getCustomerEmail();
	}
}