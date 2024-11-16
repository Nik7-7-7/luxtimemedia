<?php

declare(strict_types=1);

namespace MotoPress\Appointment\Metaboxes\Coupon;

use MotoPress\Appointment\Metaboxes\FieldsMetabox;
use MotoPress\Appointment\Entities\Coupon;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.11.0
 */
class CouponSettingsMetabox extends FieldsMetabox {

	protected function theName(): string {
		return 'coupon_settings_metabox';
	}

	public function getLabel(): string {
		return esc_html__( 'Coupon Settings', 'motopress-appointment' );
	}

	/**
	 * @return array
	 */
	protected function theFields() {
		return array(
			'description'     => array(
				'type'  => 'textarea',
				'label' => esc_html__( 'Description', 'motopress-appointment' ),
				'size'  => 'wide',
				'rows'  => 3,
			),
			'type'            => array(
				'type'    => 'select',
				'label'   => esc_html__( 'Type', 'motopress-appointment' ),
				'options' => array(
					Coupon::COUPON_DISCOUNT_TYPE_FIXED => esc_html_x( 'Fixed', 'Fixed amount', 'motopress-appointment' ),
					Coupon::COUPON_DISCOUNT_TYPE_PERCENTAGE => esc_html_x( 'Percentage', 'Percentage amount', 'motopress-appointment' ),
				),
				'default' => Coupon::COUPON_DISCOUNT_TYPE_FIXED,
			),
			'amount'          => array(
				'type'        => 'number',
				'label'       => esc_html__( 'Amount', 'motopress-appointment' ),
				'description' => esc_html__( 'Enter percent or fixed amount according to the selected type.', 'motopress-appointment' ),
				'min'         => 0,
				'step'        => 0.1,
				'default'     => 0,
				'required'    => true,
			),
			'expiration_date' => array(
				'type'        => 'date',
				'label'       => esc_html__( 'Expiration Date', 'motopress-appointment' ),
				'description' => esc_html__( 'The date when this coupon expires.', 'motopress-appointment' ),
			),
			'services'        => array(
				'type'    => 'multiselect',
				'label'   => esc_html__( 'Services', 'motopress-appointment' ),
				'options' => mpa_get_services(),
			),
			'min_date'        => array(
				'type'  => 'date',
				'label' => esc_html__( 'Min Date', 'motopress-appointment' ),
			),
			'max_date'        => array(
				'type'  => 'date',
				'label' => esc_html__( 'Max Date', 'motopress-appointment' ),
			),
			'usage_limit'     => array(
				'type'    => 'number',
				'label'   => esc_html__( 'Usage Limit', 'motopress-appointment' ),
				'min'     => 0,
				'step'    => 1,
				'default' => '',
				'size'    => 'small',
			),
			'usage_count'     => array(
				'type'     => 'number',
				'label'    => esc_html__( 'Usage Count', 'motopress-appointment' ),
				'default'  => 0,
				'size'     => 'small',
				'readonly' => true,
			),
		);
	}
}
