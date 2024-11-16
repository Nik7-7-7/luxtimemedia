<?php

namespace MotoPress\Appointment\ListTables\Emails;

use MotoPress\Appointment\Emails\AbstractEmail;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.1.0
 */
class AdminEmailsListTable extends EmailsListTable {

	/**
	 * @since 1.1.0
	 */
	protected function loadItems() {
		$this->items = mpapp()->emails()->getAdminEmails();
	}

	/**
	 * @param string $columnName
	 * @param AbstractEmail $email
	 *
	 * @since 1.1.0
	 */
	protected function displayColumn( $columnName, $email ) {
		switch ( $columnName ) {
			case 'recipients':
				$admin            = '<code>' . esc_html__( 'Administrator', 'motopress-appointment' ) . '</code>';
				$customRecipients = $email->getCustomRecipients();

				if ( ! empty( $customRecipients ) ) {
					$recipients = $admin . ', ' . $customRecipients;
				} else {
					$recipients = $admin;
				}

				echo $recipients;

				break;

			default:
				parent::displayColumn( $columnName, $email );
				break;
		}
	}
}
