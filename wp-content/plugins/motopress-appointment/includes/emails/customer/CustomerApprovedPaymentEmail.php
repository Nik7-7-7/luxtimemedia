<?php

namespace MotoPress\Appointment\Emails\Customer;

use MotoPress\Appointment\Emails\Tags\InterfaceTags;
use MotoPress\Appointment\Helpers\EmailTagsHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.5.0
 */
class CustomerApprovedPaymentEmail extends AbstractCustomerEmail {

	/**
	 * @return string
	 *
	 * @since 1.5.0
	 */
	public function getName() {
		return 'customer_approved_payment_email';
	}

	/**
	 * @return string
	 *
	 * @since 1.5.0
	 */
	public function getLabel() {
		return esc_html__( 'New Booking (Confirmation upon Payment)', 'motopress-appointment' );
	}

	/**
	 * @return string
	 *
	 * @since 1.5.0
	 */
	public function getDescription() {
		return esc_html__( 'Notification to customer about a new booking after payment. This email is sent when "Confirmation Mode" is set to Confirmation upon payment.', 'motopress-appointment' );
	}

	/**
	 * @return string
	 *
	 * @since 1.5.0
	 */
	protected function getDefaultSubject() {
		return esc_html__( '{site_title} - Booking #{booking_id}', 'motopress-appointment' );
	}

	/**
	 * @return string
	 *
	 * @since 1.5.0
	 */
	protected function getDefaultHeader() {
		return esc_html__( 'Booking #{booking_id}', 'motopress-appointment' );
	}

	protected function initTags(): InterfaceTags {
		return EmailTagsHelper::CustomerEmailConfirmationUponPaymentTags();
	}
}
