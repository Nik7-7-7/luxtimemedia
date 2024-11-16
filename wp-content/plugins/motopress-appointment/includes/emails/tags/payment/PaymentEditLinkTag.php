<?php

namespace MotoPress\Appointment\Emails\Tags\Payment;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
class PaymentEditLinkTag extends AbstractPaymentEntityTag {

	public function getName(): string {
		return 'payment_edit_link';
	}

	protected function description(): string {
		return esc_html__( 'Payment edit link', 'motopress-appointment' );
	}

	public function getTagContent(): string {
		$id = $this->entity->getId();

		return mpa_tmpl_edit_post_link_no_role_checks( $id );
	}
}
