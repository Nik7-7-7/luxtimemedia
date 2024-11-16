<?php

namespace MotoPress\Appointment\Emails\Tags\Customer;

use MotoPress\Appointment\Emails\Tags\AbstractTag;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.18.0
 */
class CustomerAccountLinkTag extends AbstractTag {

	public function getName(): string {
		return 'customer_account_link';
	}

	protected function description(): string {
		return esc_html__( 'Link to My Account page', 'motopress-appointment' );
	}

	public function getTagContent(): string {
		$customerAccountPageId = mpapp()->settings()->getCustomerAccountPage();

		if ( ! $customerAccountPageId ) {
			// todo:
			// It is necessary to provide for a possible change in the id of the account page.
			// It possible make a universal link to a page via direct-link-action.
			return get_home_url();
		}

		return get_permalink( $customerAccountPageId );
	}
}
