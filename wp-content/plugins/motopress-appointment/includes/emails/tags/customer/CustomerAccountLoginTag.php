<?php

namespace MotoPress\Appointment\Emails\Tags\Customer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.18.0
 */
class CustomerAccountLoginTag extends AbstractCustomerEntityTag {

	public function getName(): string {
		return 'customer_account_login';
	}

	protected function description(): string {
		return esc_html__( 'Customer account login', 'motopress-appointment' );
	}

	public function getTagContent(): string {
		$customer = $this->getEntity();

		$userId = $customer->getUserId();
		if ( ! $userId ) {
			return $this->getTag();
		}

		$userdata = get_userdata( $userId );

		if ( ! $userdata ) {
			return $this->getTag();
		}

		return $userdata->user_login;
	}
}