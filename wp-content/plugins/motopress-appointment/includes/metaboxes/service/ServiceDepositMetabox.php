<?php

namespace MotoPress\Appointment\Metaboxes\Service;

use MotoPress\Appointment\Metaboxes\FieldsMetabox;
use MotoPress\Appointment\Entities\Service;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.14.0
 */
class ServiceDepositMetabox extends FieldsMetabox {


	/**
	 * @return string
	 */
	protected function theName(): string {
		return 'service_deposit_metabox';
	}


	/**
	 * @return array
	 */
	protected function theFields() {

		return array(
			'deposit_type'   => array(
				'type'    => 'select',
				'label'   => esc_html__( 'Type', 'motopress-appointment' ),
				'options' => array(
					Service::DEPOSIT_TYPE_DISABLED   => esc_html__( 'Disabled', 'motopress-appointment' ),
					Service::DEPOSIT_TYPE_FIXED      => esc_html_x( 'Fixed', 'Fixed amount', 'motopress-appointment' ),
					Service::DEPOSIT_TYPE_PERCENTAGE => esc_html_x( 'Percentage', 'Percentage amount', 'motopress-appointment' ),
				),
				'default' => Service::DEPOSIT_TYPE_DISABLED,
			),
			'deposit_amount' => array(
				'type'        => 'number',
				'label'       => esc_html__( 'Amount', 'motopress-appointment' ),
				'description' => esc_html__( 'Enter percent or fixed amount according to the selected type.', 'motopress-appointment' ),
				'min'         => 0,
				'step'        => 0.1,
				'default'     => 0,
				'required'    => true,
			),
		);
	}

	/**
	 * @return string
	 */
	public function getLabel(): string {
		return esc_html__( 'Deposit Settings', 'motopress-appointment' );
	}
}
