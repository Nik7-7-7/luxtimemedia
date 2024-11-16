<?php

namespace MotoPress\Appointment\ListTables\Emails;

use MotoPress\Appointment\Emails\AbstractEmail;
use MotoPress\Appointment\ListTables\AbstractSettingsListTable;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.1.0
 */
class EmailsListTable extends AbstractSettingsListTable {

	/**
	 * @since 1.1.0
	 */
	protected function loadItems() {
		$this->items = mpapp()->emails()->getEmails();
	}

	/**
	 * @return array
	 *
	 * @since 1.1.0
	 */
	public function getColumns() {
		return array(
			'label'      => esc_html_x( 'Email', 'Column "Email"', 'motopress-appointment' ),
			'switch'     => esc_html__( 'Enabled', 'motopress-appointment' ),
			'type'       => esc_html__( 'Content Type', 'motopress-appointment' ),
			'recipients' => esc_html__( 'Recipients', 'motopress-appointment' ),
			'actions'    => '', // No title
		);
	}

	/**
	 * @param string $columnName
	 * @param AbstractEmail $email
	 *
	 * @since 1.1.0
	 */
	protected function displayColumn( $columnName, $email ) {
		switch ( $columnName ) {
			case 'label':
				// Show label
				echo mpa_tmpl_link( $this->getSectionUrl( $email ), $email->getLabel() );

				// Show description
				$description = $email->getDescription();

				if ( ! empty( $description ) ) {
					echo '<p>', $description, '</p>';
				}

				break;

			case 'type':
				echo 'text/html';
				break;

			case 'recipients':
				$recipients = $email->getRecipients();

				if ( ! empty( $recipients ) ) {
					echo $recipients;
				} else {
					echo mpa_tmpl_placeholder();
				}

				break;

			default:
				parent::displayColumn( $columnName, $email );
				break;
		}
	}

	/**
	 * @since 1.5.0
	 *
	 * @param AbstractEmail $email
	 * @return bool
	 */
	protected function isEnabled( $email ) {
		return ! $email->isDisabled();
	}

	/**
	 * @param AbstractEmail $email
	 * @return string
	 *
	 * @since 1.1.0
	 */
	protected function getSectionUrl( $email ) {
		return mpapp()->pages()->settings()->getUrl(
			array(
				'tab'     => 'email',
				'section' => $email->getName(),
			)
		);
	}
}
