<?php

namespace MotoPress\Appointment\Emails\Tags\Booking;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class BookingCouponDiscountTag extends AbstractBookingEntityTag {

	public function getName(): string {
		return 'booking_coupon_discount';
	}

	protected function description(): string {
		return esc_html__( 'Amount of Discount by Coupon Code', 'motopress-appointment' );
	}

	public function getTagContent(): string {

		$couponDiscount = 0;

		if ( null !== $this->getEntity() ) {

			$couponDiscount = $this->getEntity()->getCouponDiscount();
		}

		return \MotoPress\Appointment\Helpers\PriceCalculationHelper::formatPriceAsHTML(
			$couponDiscount
		);
	}
}
