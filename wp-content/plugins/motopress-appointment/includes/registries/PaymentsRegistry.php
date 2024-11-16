<?php

namespace MotoPress\Appointment\Registries;

use MotoPress\Appointment\Payments\Gateways\BankPaymentGateway;
use MotoPress\Appointment\Payments\Gateways\CashPaymentGateway;
use MotoPress\Appointment\Payments\Gateways\FreePaymentGateway;
use MotoPress\Appointment\Payments\Gateways\ManualPaymentGateway;
use MotoPress\Appointment\Payments\Gateways\AbstractPaymentGateway;
use MotoPress\Appointment\Payments\Gateways\StripePaymentGateway;
use MotoPress\Appointment\Payments\Gateways\TestPaymentGateway;
use MotoPress\Appointment\Payments\Gateways\PayPalPaymentGateway;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.5.0
 */
class PaymentsRegistry {

	/**
	 * @since 1.5.0
	 * @var AbstractPaymentGateway[]
	 */
	protected $payments = array();

	/**
	 * @since 1.5.0
	 */
	public function __construct() {
		add_filter(
			'mpa_payment_section_settings',
			function( $fields, $sectionName ) {
				$gateway = $this->getGateway( $sectionName );

				if ( ! is_null( $gateway ) ) {
					$fields += $gateway->getFields();
				}

				return $fields;
			},
			10,
			2
		);

		add_action(
			'plugins_loaded',
			function() {
				$this->payments = apply_filters(
					'mpa_registered_payment_gateways',
					array(
						'manual' => new ManualPaymentGateway(),
						'free'   => new FreePaymentGateway(),
						'test'   => new TestPaymentGateway(),
						'cash'   => new CashPaymentGateway(),
						'bank'   => new BankPaymentGateway(),
						'stripe' => new StripePaymentGateway(),
						'paypal' => new PayPalPaymentGateway(),
					)
				);
			},
			900 // load payment gateways after Appointment Booking Plugin and its addons!
		);
	}

	/**
	 * @since 1.5.0
	 *
	 * @return ManualPaymentGateway
	 */
	public function manual() {
		return $this->getGateway( __FUNCTION__ );
	}

	/**
	 * @since 1.6.2
	 *
	 * @return FreePaymentGateway
	 */
	public function free(): FreePaymentGateway {
		return $this->getGateway( __FUNCTION__ );
	}

	/**
	 * @since 1.5.0
	 *
	 * @return TestPaymentGateway
	 */
	public function test() {
		return $this->getGateway( __FUNCTION__ );
	}

	/**
	 * @since 1.5.0
	 *
	 * @return CashPaymentGateway
	 */
	public function cash() {
		return $this->getGateway( __FUNCTION__ );
	}

	/**
	 * @since 1.5.0
	 *
	 * @return BankPaymentGateway
	 */
	public function bank() {
		return $this->getGateway( __FUNCTION__ );
	}

	/**
	 * @since 1.5.0
	 *
	 * @return StripePaymentGateway
	 */
	public function stripe() {
		return $this->getGateway( __FUNCTION__ );
	}

	/**
	 * @since 1.6.0
	 */
	public function paypal() {
		return $this->getGateway( __FUNCTION__ );
	}

	/**
	 * @since 1.5.0
	 *
	 * @param string $gatewayId
	 * @return AbstractPaymentGateway|null
	 */
	public function getGateway( $gatewayId ) {
		return isset( $this->payments[ $gatewayId ] ) ? $this->payments[ $gatewayId ] : null;
	}

	/**
	 * @since 1.5.0
	 *
	 * @param string $gatewayId
	 * @return string
	 */
	public function getGatewayName( $gatewayId ) {
		$gateway = $this->getGateway( $gatewayId );

		return is_null( $gateway ) ? $gatewayId : $gateway->getName();
	}

	/**
	 * @since 1.5.0
	 *
	 * @return AbstractPaymentGateway[] [Gateway ID => Gateway object]
	 */
	public function getAll() {
		return $this->payments;
	}

	/**
	 * All except of internal payment gateways.
	 *
	 * @since 1.5.0
	 *
	 * @return AbstractPaymentGateway[] [Gateway ID => Gateway object]
	 */
	public function getAllManagable() {

		$gateways = $this->getAll();

		// Skip internal
		$gateways = array_filter(
			$gateways,
			function ( $gateway ) {
				return ! $gateway->isInternal();
			}
		);

		return $gateways;
	}

	/**
	 * @since 1.5.0
	 *
	 * @param bool $skipInternal Optional. True by default.
	 * @return AbstractPaymentGateway[] [Gateway ID => Gateway object]
	 */
	public function getEnabled( $skipInternal = true ) {
		$gateways = $skipInternal ? $this->getAllManagable() : $this->getAll();

		// Skip disabled
		$gateways = array_filter(
			$gateways,
			function ( $gateway ) {
				return $gateway->isEnabled();
			}
		);

		return $gateways;
	}

	/**
	 * @since 1.5.0
	 *
	 * @param bool $skipInternal Optional. True by default.
	 * @return AbstractPaymentGateway[] [Gateway ID => Gateway object]
	 */
	public function getActive( $skipInternal = true ) {
		$gateways = $skipInternal ? $this->getAllManagable() : $this->getAll();

		// Skip inactive
		$gateways = array_filter(
			$gateways,
			function ( $gateway ) {
				return $gateway->isActive();
			}
		);

		return $gateways;
	}

	/**
	 * @since 1.5.0
	 *
	 * @param string $gatewayId
	 * @return bool
	 */
	public function isInstantGateway( $gatewayId ) {
		// Don't mention 'manual' and 'test'. They both are something different
		return in_array( $gatewayId, array( 'cash', 'bank' ) );
	}
}
