<?php

namespace MotoPress\Appointment\Emails\Tags\Customer;

use MotoPress\Appointment\Emails\Tags\AbstractTag;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.18.0
 */
class CustomerAccountPasswordTag extends AbstractTag {

	public function getName(): string {
		return 'customer_account_password';
	}

	protected function description(): string {
		return esc_html__( 'Customer account password', 'motopress-appointment' );
	}

	public function getTagContent(): string {
		/**
		 * To set the password value in the tag,
		 * you need to use add_filter: mpa_email_tag_customer_account_password
		 *
		 * @param string by default value will equal own tag: {customer_account_password}
		 */
		return apply_filters( 'mpa_email_tag_customer_account_password', $this->getTag() );
	}
}