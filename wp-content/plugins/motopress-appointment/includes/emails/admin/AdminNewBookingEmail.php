<?php

namespace MotoPress\Appointment\Emails\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.1.0
 */
class AdminNewBookingEmail extends AbstractAdminEmail {

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function getName() {
		return 'admin_new_booking_email';
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function getLabel() {
		return esc_html__( 'New Booking (Confirmed Automatically)', 'motopress-appointment' );
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function getDescription() {
		return esc_html__( 'Notification to administrator about a new booking. This email is sent when "Confirmation Mode" is set to Auto.', 'motopress-appointment' );
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	protected function getDefaultSubject() {
		return esc_html__( '{site_title} - New Booking #{booking_id}', 'motopress-appointment' );
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	protected function getDefaultHeader() {
		return esc_html__( 'New Booking #{booking_id}', 'motopress-appointment' );
	}
}
