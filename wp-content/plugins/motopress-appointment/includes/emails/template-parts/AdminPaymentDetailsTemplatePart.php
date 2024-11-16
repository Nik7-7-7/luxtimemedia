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
class AdminPaymentDetailsTemplatePart extends AbstractTemplatePart {

	/**
	 * @return string
	 */
	public function getName() {
		return 'admin_payment_details';
	}

	/**
	 * @return string
	 */
	public function getLabel() {
		return esc_html__( 'Admin Payment Details', 'motopress-appointment' );
	}

	/**
	 * @return string
	 */
	public function getDescription() {
		return esc_html__( 'Used for the {booking_payments} tag in admin emails.', 'motopress-appointment' );
	}

	/**
	 * @return string.
	 */
	public function renderDefaultTemplate() {
		return mpa_render_template(
			'emails/template-parts/admin-payment-details.php'
		);
	}

	/**
	 * @since 1.15.2
	 */
	protected function initTags(): InterfaceTags {
		return EmailTagsHelper::AdminPaymentDetailsTemplatePartTags();
	}
}
