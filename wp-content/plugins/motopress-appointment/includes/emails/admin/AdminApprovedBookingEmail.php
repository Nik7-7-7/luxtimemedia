<?php

namespace MotoPress\Appointment\Emails\Admin;

use MotoPress\Appointment\Emails\Tags\InterfaceTags;
use MotoPress\Appointment\Helpers\EmailTagsHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.5.0
 */
class AdminApprovedBookingEmail extends AbstractAdminEmail {

	/**
	 * @since 1.5.0
	 *
	 * @return string
	 */
	public function getName() {
		return 'admin_approved_booking_email';
	}

	/**
	 * @since 1.5.0
	 *
	 * @return string
	 */
	public function getLabel() {
		return esc_html__( 'New Booking (Confirmation upon Payment)', 'motopress-appointment' );
	}

	/**
	 * @since 1.5.0
	 *
	 * @return string
	 */
	public function getDescription() {
		return esc_html__( 'Notification to administrator about a new booking after payment. This email is sent when "Confirmation Mode" is set to Confirmation upon payment.', 'motopress-appointment' );
	}

	/**
	 * @return string
	 *
	 * @since 1.5.0
	 */
	protected function getDefaultSubject() {
		return esc_html__( '{site_title} - New Booking #{booking_id}', 'motopress-appointment' );
	}

	/**
	 * @return string
	 *
	 * @since 1.5.0
	 */
	protected function getDefaultHeader() {
		return esc_html__( 'New Booking #{booking_id}', 'motopress-appointment' );
	}

	protected function initTags(): InterfaceTags {
		return EmailTagsHelper::AdminEmailWithPaymentsTags();
	}
}
