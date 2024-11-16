<?php

namespace MotoPress\Appointment\Emails\Tags\Payment;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
class PaymentAmountTag extends AbstractPaymentEntityTag {

	public function getName(): string {
		return 'payment_amount';
	}

	protected function description(): string {
		return esc_html__( 'Payment amount', 'motopress-appointment' );
	}

	public function getTagContent(): string {
		return mpa_tmpl_price( $this->entity->getAmount(), array( 'literal_free' => false ) );
	}
}
