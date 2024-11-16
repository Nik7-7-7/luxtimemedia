<?php

namespace MotoPress\Appointment\Emails\Customer;

use MotoPress\Appointment\Emails\AbstractEmail;
use MotoPress\Appointment\Emails\Tags\InterfaceTags;
use MotoPress\Appointment\Entities\Customer;
use MotoPress\Appointment\Helpers\EmailTagsHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.18.0
 */
class CustomerAccountCreationEmail extends AbstractEmail {

	/**
	 * @var Customer|null
	 */
	protected $customer = null;

	/**
	 * @return string
	 */
	public function getDefaultRecipients(): string {
		if ( is_null( $this->customer ) ) {
			return '';
		}

		$customerId = $this->customer->getUserId();
		if ( ! $customerId ) {
			return '';
		}

		$userdata = get_userdata( $customerId );

		if ( ! $userdata ) {
			return '';
		}

		if ( ! is_email( $userdata->user_email ) ) {
			return '';
		}

		return $userdata->user_email;
	}

	/**
	 * @param Customer $customer
	 */
	public function setCustomer( Customer $customer ) {
		$this->customer = $customer;
		$this->tags->setEntity( $customer );
	}

	/**
	 * @return string
	 */
	public function getName(): string {
		return 'customer_account_creation_email';
	}

	/**
	 * @return string
	 */
	public function getLabel(): string {
		return esc_html__( 'Customer Account Creation', 'motopress-appointment' );
	}

	/**
	 * @return string
	 */
	public function getDescription(): string {
		return esc_html__( 'Notification to the customer that their account was created.', 'motopress-appointment' );
	}

	/**
	 * @return string
	 */
	protected function getDefaultSubject(): string {
		return sprintf( '{site_title} - %s', $this->getDefaultHeader() );
	}

	/**
	 * @return string
	 */
	protected function getDefaultHeader(): string {
		return esc_html__( 'Your account details', 'motopress-appointment' );
	}

	protected function initTags(): InterfaceTags {
		return EmailTagsHelper::CustomerEmailAccountCreationTags();
	}

	protected function getMessageTemplate() {
		return 'emails/customer/' . $this->getFilename();
	}
}