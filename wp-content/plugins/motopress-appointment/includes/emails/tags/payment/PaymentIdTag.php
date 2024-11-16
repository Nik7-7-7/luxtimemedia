<?php

namespace MotoPress\Appointment\Emails\Tags\Payment;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
class PaymentIdTag extends AbstractPaymentEntityTag {

	public function getName(): string {
		return 'payment_id';
	}

	protected function description(): string {
		return esc_html__( 'Payment ID', 'motopress-appointment' );
	}

	public function getTagContent(): string {
		$id = $this->entity->getId();

		return strval( $id );
	}
}
