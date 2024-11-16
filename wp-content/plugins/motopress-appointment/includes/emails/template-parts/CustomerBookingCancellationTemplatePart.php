<?php

namespace MotoPress\Appointment\Emails\TemplateParts;

use MotoPress\Appointment\Emails\Tags\InterfaceTags;
use MotoPress\Appointment\Helpers\EmailTagsHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.0
 */
class CustomerBookingCancellationTemplatePart extends AbstractTemplatePart {

	/**
	 * @return string
	 */
	public function getName() {
		return 'customer_booking_cancellation';
	}

	/**
	 * @return string
	 */
	public function getLabel() {
		return esc_html__( 'Customer Booking Cancelation', 'motopress-appointment' );
	}

	/**
	 * @return string
	 */
	public function getDescription() {
		return esc_html__( 'Used for the {cancelation_details} tag in customer emails.', 'motopress-appointment' );
	}

	/**
	 * @return string.
	 */
	public function renderDefaultTemplate() {
		return mpa_render_template(
			'emails/template-parts/cancellation-details.php'
		);
	}

	/**
	 * @since 1.15.2
	 */
	protected function initTags(): InterfaceTags {
		return EmailTagsHelper::CustomerBookingCancellationTemplatePartTags();
	}
}
