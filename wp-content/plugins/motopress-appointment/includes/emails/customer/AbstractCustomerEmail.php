<?php

namespace MotoPress\Appointment\Emails\Customer;

use MotoPress\Appointment\Emails\AbstractBookingEmail;
use MotoPress\Appointment\Emails\Tags\InterfaceTags;
use MotoPress\Appointment\Helpers\EmailTagsHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.1.0
 */
abstract class AbstractCustomerEmail extends AbstractBookingEmail {

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function getDefaultRecipients() {
		if ( ! is_null( $this->booking ) ) {
			return $this->booking->getCustomerEmail();
		} else {
			return '';
		}
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	protected function getMessageTemplate() {
		return 'emails/customer/' . $this->getFilename();
	}

	protected function initTags(): InterfaceTags {
		return EmailTagsHelper::CustomerEmailTags();
	}
}
