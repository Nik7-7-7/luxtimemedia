<?php

namespace MotoPress\Appointment\Emails\Admin;

use MotoPress\Appointment\Emails\AbstractBookingEmail;
use MotoPress\Appointment\Emails\Tags\InterfaceTags;
use MotoPress\Appointment\Helpers\EmailTagsHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.1.0
 */
abstract class AbstractAdminEmail extends AbstractBookingEmail {
	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function getDefaultRecipients() {
		return mpapp()->settings()->getAdminEmail();
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	protected function getMessageTemplate() {
		return 'emails/admin/' . $this->getFilename();
	}

	protected function initTags(): InterfaceTags {
		return EmailTagsHelper::AdminEmailTags();
	}
}
