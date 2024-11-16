<?php

namespace MotoPress\Appointment\Emails\Tags\Customer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.18.0
 */
class CustomerEmailTag extends AbstractCustomerEntityTag {

	public function getName(): string {
		return 'customer_email';
	}

	protected function description(): string {
		return esc_html__( 'Customer email', 'motopress-appointment' );
	}

	public function getTagContent(): string {
		return $this->getEntity()->getEmail();
	}
}