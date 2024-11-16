<?php

namespace MotoPress\Appointment\Emails\Customer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * New booking email (confirmed automatically).
 *
 * @since 1.1.0
 */
class CustomerNewBookingEmail extends AbstractCustomerEmail {

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function getName() {
		return 'customer_new_booking_email';
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
		return esc_html__( 'Notification to customer about a new booking. This email is sent when "Confirmation Mode" is set to Auto.', 'motopress-appointment' );
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
		return esc_html__( 'Booking #{booking_id}', 'motopress-appointment' );
	}
}
