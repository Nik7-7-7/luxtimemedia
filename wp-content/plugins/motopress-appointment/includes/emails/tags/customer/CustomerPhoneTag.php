<?php

namespace MotoPress\Appointment\Emails\Tags\Customer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.18.0
 */
class CustomerPhoneTag extends AbstractCustomerEntityTag {

	public function getName(): string {
		return 'customer_phone';
	}

	protected function description(): string {
		return esc_html__( 'Customer phone', 'motopress-appointment' );
	}

	public function getTagContent(): string {
		return $this->getEntity()->getPhone();
	}
}