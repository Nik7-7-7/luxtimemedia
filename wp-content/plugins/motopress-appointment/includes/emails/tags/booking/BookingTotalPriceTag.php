<?php

namespace MotoPress\Appointment\Emails\Tags\Booking;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
class BookingTotalPriceTag extends AbstractBookingEntityTag {

	public function getName(): string {
		return 'booking_total_price';
	}

	protected function description(): string {
		return esc_html__( 'Booking total price', 'motopress-appointment' );
	}

	public function getTagContent(): string {

		$totalPrice = $this->getEntity()->getTotalPrice();

		return \MotoPress\Appointment\Helpers\PriceCalculationHelper::formatPriceAsHTML(
			$totalPrice
		);
	}
}
