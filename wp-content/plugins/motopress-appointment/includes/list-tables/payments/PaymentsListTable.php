<?php

namespace MotoPress\Appointment\ListTables\Payments;

use MotoPress\Appointment\ListTables\AbstractSettingsListTable;
use MotoPress\Appointment\Payments\Gateways\AbstractPaymentGateway;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.5.0
 */
class PaymentsListTable extends AbstractSettingsListTable {

	/**
	 * @since 1.5.0
	 */
	protected function loadItems() {
		$this->items = mpapp()->payments()->getAllManagable();
	}

	/**
	 * @since 1.5.0
	 *
	 * @return array
	 */
	public function getColumns() {
		return array(
			'label'   => esc_html__( 'Method', 'motopress-appointment' ),
			'switch'  => esc_html__( 'Enabled', 'motopress-appointment' ),
			'sandbox' => esc_html__( 'Sandbox', 'motopress-appointment' ),
			'actions' => '', // No title
		);
	}

	/**
	 * @since 1.5.0
	 *
	 * @param string $columnName
	 * @param AbstractPaymentGateway $gateway
	 */
	protected function displayColumn( $columnName, $gateway ) {

		switch ( $columnName ) {
			case 'label':
				echo mpa_tmpl_link( $this->getSectionUrl( $gateway ), $gateway->getName() );
				break;

			case 'sandbox':
				if ( $gateway->isSandbox() ) {
					esc_html_e( 'Yes', 'motopress-appointment' );
				} elseif ( $gateway->isSupportsSandbox() ) {
					esc_html_e( 'No', 'motopress-appointment' ); // Supports, but not enabled
				} else {
					echo mpa_tmpl_placeholder();
				}
				break;
			

			default:
				parent::displayColumn( $columnName, $gateway );
				break;
		}
	}

	/**
	 * @since 1.5.0
	 *
	 * @param AbstractPaymentGateway $gateway
	 * @return bool
	 */
	protected function isEnabled( $gateway ) {
		return $gateway->isEnabled();
	}

	/**
	 * @since 1.5.0
	 *
	 * @param AbstractPaymentGateway $gateway
	 * @return string
	 */
	protected function getSectionUrl( $gateway ) {
		return mpapp()->pages()->settings()->getUrl(
			array(
				'tab'     => 'payment',
				'section' => $gateway->getId(),
			)
		);
	}
}
