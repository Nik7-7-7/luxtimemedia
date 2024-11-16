<?php

namespace MotoPress\Appointment\Emails\Customer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * New booking email (confirmation by admin).
 *
 * @since 1.1.0
 */
class CustomerPendingBookingEmail extends AbstractCustomerEmail {

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function getName() {
		return 'customer_pending_booking_email';
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function getLabel() {
		return esc_html__( 'Booking Request (Confirmation by Admin)', 'motopress-appointment' );
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function getDescription() {
		return esc_html__( 'Notification to customer that a booking request is received. This email is sent when "Confirmation Mode" is set to Admin confirmation.', 'motopress-appointment' );
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
		return esc_html__( 'Booking #{booking_id} has been received', 'motopress-appointment' );
	}
}
