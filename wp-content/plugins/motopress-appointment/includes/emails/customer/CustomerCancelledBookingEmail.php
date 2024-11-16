<?php

namespace MotoPress\Appointment\Emails\Customer;

use MotoPress\Appointment\Emails\Tags\InterfaceTags;
use MotoPress\Appointment\Helpers\EmailTagsHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.1.0
 */
class CustomerCancelledBookingEmail extends AbstractCustomerEmail {

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function getName() {
		return 'customer_cancelled_booking_email';
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function getLabel() {
		return esc_html__( 'Canceled Booking', 'motopress-appointment' );
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function getDescription() {
		return esc_html__( 'Notification to customer that a booking is canceled.', 'motopress-appointment' );
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	protected function getDefaultSubject() {
		return esc_html__( '{site_title} - Booking #{booking_id}', 'motopress-appointment' );
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	protected function getDefaultHeader() {
		return esc_html__( 'Booking #{booking_id} is canceled', 'motopress-appointment' );
	}

	protected function initTags(): InterfaceTags {
		return EmailTagsHelper::CustomerEmailCancelledBookingTags();
	}
}
