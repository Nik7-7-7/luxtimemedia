<?php

namespace MotoPress\Appointment\Emails\Tags\Payment;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
class PaymentMethodTag extends AbstractPaymentEntityTag {

	public function getName(): string {
		return 'payment_method';
	}

	protected function description(): string {
		return esc_html__( 'Payment method', 'motopress-appointment' );
	}

	public function getTagContent(): string {
		return ! empty( $this->entity->getPaymentMethod() ) ? $this->entity->getPaymentMethod() : $this->entity->getGateway()->getPublicName();
	}
}
