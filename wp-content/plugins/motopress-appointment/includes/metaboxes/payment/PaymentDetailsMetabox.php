<?php

namespace MotoPress\Appointment\Metaboxes\Payment;

use MotoPress\Appointment\Metaboxes\AbstractChildFieldsMetabox;
use MotoPress\Appointment\Payments\Gateways\AbstractPaymentGateway;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.5.0
 */
class PaymentDetailsMetabox extends AbstractChildFieldsMetabox {

	/**
	 * @since 1.5.0
	 * @var string
	 */
	public $parentPostmeta = '_mpa_booking_id';

	/**
	 * @since 1.5.0
	 *
	 * @return string
	 */
	protected function theName(): string {
		return 'payment_details_metabox';
	}

	/**
	 * @since 1.5.0
	 *
	 * @return string
	 */
	public function getLabel(): string {
		return esc_html__( 'Payment Details', 'motopress-appointment' );
	}

	/**
	 * @since 1.5.0
	 *
	 * @return array
	 */
	protected function theFields() {

		$gateways = array_map(
			function ( $gateway ) {
				return $gateway->getName(); },
			mpapp()->payments()->getAll()
		);

		// Get preset values, if the "booking_id" is set
		$defaultBookingId = '';
		$defaultAmount    = 0.0;
		$defaultGatewayId = '';

		if ( isset( $_GET['booking_id'] ) ) {
			$booking = mpa_get_booking( $_GET['booking_id'] );

			if ( ! is_null( $booking ) ) {
				$defaultBookingId = $booking->getId();
				$defaultAmount    = $booking->getToPayPrice();
				$defaultGatewayId = 'manual';
			}
		}

		return array(
			'booking_id'     => array(
				'type'  => 'text',
				'label' => esc_html__( 'Booking ID', 'motopress-appointment' ),
				'value' => $defaultBookingId,
			),
			'amount'         => array(
				'type'  => 'price',
				'label' => esc_html__( 'Amount', 'motopress-appointment' ),
				'value' => $defaultAmount,
			),
			'currency'       => array(
				'type'    => 'select',
				'label'   => esc_html__( 'Currency', 'motopress-appointment' ),
				'options' => mpapp()->bundles()->currencies()->getCurrencies(),
				'default' => mpapp()->settings()->getCurrency(),
				'size'    => 'regular',
			),
			'gateway_id'     => array(
				'type'    => 'select',
				'label'   => esc_html__( 'Payment Gateway', 'motopress-appointment' ),
				'options' => array( '' => esc_html__( '— Select —', 'motopress-appointment' ) ) + $gateways,
				'default' => '',
				'value'   => $defaultGatewayId,
				'size'    => 'regular',
			),
			'gateway_mode'   => array(
				'type'    => 'select',
				'label'   => esc_html__( 'Gateway Mode', 'motopress-appointment' ),
				'options' => array(
					AbstractPaymentGateway::GATEWAY_MODE_LIVE => esc_html__( 'Live', 'motopress-appointment' ),
					AbstractPaymentGateway::GATEWAY_MODE_SANDBOX => esc_html__( 'Sandbox', 'motopress-appointment' ),
				),
				'default' => AbstractPaymentGateway::GATEWAY_MODE_LIVE,
				'size'    => 'regular',
			),
			'payment_method' => array(
				'type'  => 'text',
				'label' => esc_html__( 'Payment Method', 'motopress-appointment' ),
				'size'  => 'regular',
			),
			'transaction_id' => array(
				'type'  => 'text',
				'label' => esc_html__( 'Transaction ID', 'motopress-appointment' ),
				'size'  => 'regular',
			),
		);
	}
}
