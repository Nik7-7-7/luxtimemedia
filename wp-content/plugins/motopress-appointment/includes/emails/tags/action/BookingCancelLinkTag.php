<?php

namespace MotoPress\Appointment\Emails\Tags\Action;

use MotoPress\Appointment\Emails\Tags\Booking\AbstractBookingEntityTag;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
class BookingCancelLinkTag extends AbstractBookingEntityTag {

	public function getName(): string {
		return 'booking_cancel_link';
	}

	protected function description(): string {
		return esc_html__( 'Booking cancel link', 'motopress-appointment' );
	}

	protected function getTagContent(): string {
		$booking = $this->getEntity();

		return mpapp()->directLinkActions()->getBookingCancellationPage()->getActionURL( $booking );
	}
}
