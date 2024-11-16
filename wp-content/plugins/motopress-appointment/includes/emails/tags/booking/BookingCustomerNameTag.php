<?php

namespace MotoPress\Appointment\Emails\Tags\Booking;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.18.0
 */
class BookingCustomerNameTag extends AbstractBookingEntityTag {

	public function getName(): string {
		return 'customer_name';
	}

	protected function description(): string {
		return esc_html__( 'Customer name', 'motopress-appointment' );
	}

	public function getTagContent(): string {
		return $this->getEntity()->getCustomerName();
	}
}