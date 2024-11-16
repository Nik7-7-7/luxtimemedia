<?php

namespace MotoPress\Appointment\Emails\Tags\Customer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.18.0
 */
class CustomerNameTag extends AbstractCustomerEntityTag {

	public function getName(): string {
		return 'customer_name';
	}

	protected function description(): string {
		return esc_html__( 'Customer name', 'motopress-appointment' );
	}

	public function getTagContent(): string {
		return $this->getEntity()->getName();
	}
}