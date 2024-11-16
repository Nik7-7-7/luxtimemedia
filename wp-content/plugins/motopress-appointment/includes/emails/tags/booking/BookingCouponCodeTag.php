<?php

namespace MotoPress\Appointment\Emails\Tags\Booking;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class BookingCouponCodeTag extends AbstractBookingEntityTag {

	public function getName(): string {
		return 'booking_coupon_code';
	}

	protected function description(): string {
		return esc_html__( 'Coupon code', 'motopress-appointment' );
	}

	public function getTagContent(): string {

		$couponCode = '';

		if ( null !== $this->getEntity() && $this->getEntity()->hasCoupon() ) {

			$coupon = $this->getEntity()->getCoupon();

			if ( null !== $coupon ) {

				$couponCode = $coupon->getCode();
			}
		}

		return $couponCode;
	}
}
