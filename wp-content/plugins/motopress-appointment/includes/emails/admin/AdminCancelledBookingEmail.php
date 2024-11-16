<?php

namespace MotoPress\Appointment\Emails\Admin;

use MotoPress\Appointment\Emails\Tags\InterfaceTags;
use MotoPress\Appointment\Helpers\EmailTagsHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.0
 */
class AdminCancelledBookingEmail extends AbstractAdminEmail {
	/**
	 * @return string
	 */
	public function getName() {
		return 'admin_cancelled_booking_email';
	}

	/**
	 * @return string
	 */
	public function getLabel() {
		return esc_html__( 'Canceled Booking', 'motopress-appointment' );
	}

	/**
	 * @return string
	 */
	public function getDescription() {
		return esc_html__( 'Notification to Admin when the customer cancels their booking.', 'motopress-appointment' );
	}

	/**
	 * @return string
	 */
	protected function getDefaultSubject() {
		return esc_html__( '{site_title} - Booking #{booking_id}', 'motopress-appointment' );
	}

	/**
	 * @return string
	 */
	protected function getDefaultHeader() {
		return esc_html__( 'Booking #{booking_id} is canceled', 'motopress-appointment' );
	}

	protected function initTags(): InterfaceTags {
		return EmailTagsHelper::AdminEmailWithPaymentsTags();
	}
}
