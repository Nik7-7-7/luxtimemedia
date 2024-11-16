<?php

namespace MotoPress\Appointment\Emails\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.1.0
 */
class AdminPendingBookingEmail extends AbstractAdminEmail {

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function getName() {
		return 'admin_pending_booking_email';
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function getLabel() {
		return esc_html__( 'New Booking Request (Confirmation by Admin)', 'motopress-appointment' );
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function getDescription() {
		return esc_html__( 'Notification to administrator about a new booking request waiting for confirmation. This email is sent when "Confirmation Mode" is set to Admin confirmation.', 'motopress-appointment' );
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	protected function getDefaultSubject() {
		return esc_html__( '{site_title} - Confirm New Booking #{booking_id}', 'motopress-appointment' );
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	protected function getDefaultHeader() {
		return esc_html__( 'Confirm New Booking #{booking_id}', 'motopress-appointment' );
	}
}
