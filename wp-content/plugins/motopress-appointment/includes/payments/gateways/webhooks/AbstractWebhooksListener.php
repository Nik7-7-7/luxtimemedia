<?php

namespace MotoPress\Appointment\Payments\Gateways\Webhooks;

use MotoPress\Appointment\Entities\Payment;
use MotoPress\Appointment\Payments\Gateways\AbstractPaymentGateway;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.5.0
 */
abstract class AbstractWebhooksListener {

	/** @since 1.5.0 */
	const URL_KEY = 'mpa-listener';

	/** @since 1.5.0 */
	const STATUS_SUCCESS = 0;
	/** @since 1.5.0 */
	const STATUS_PARSE_ERROR = 1;
	/** @since 1.5.0 */
	const STATUS_VALIDATION_ERROR = 2;
	/** @since 1.5.0 */
	const STATUS_REPOSITORY_ERROR = 4;
	/** @since 1.5.0 */
	const STATUS_PROCESSING_ERROR = 128; // Exit status code must be in range [0; 254]

	/**
	 * @since 1.5.0
	 * @var AbstractPaymentGateway
	 */
	public $gateway = null;

	/**
	 * @since 1.5.0
	 *
	 * @param AbstractPaymentGateway $gateway
	 */
	public function __construct( $gateway ) {
		$this->gateway = $gateway;
	}

	/**
	 * @since 1.5.0
	 *
	 * @see StripePaymentGateway::addListeners()
	 */
	public function addListeners() {
		add_action( 'init', array( $this, 'handleRequest' ) );
	}

	/**
	 * @since 1.5.0
	 *
	 * @access protected
	 */
	public function handleRequest() {

		if ( ! $this->isCurrentListenerRequest() ) {
			return;
		}

		$rawInput = $this->parseInput();

		if ( empty( $rawInput ) ) {
			$this->fireExit( self::STATUS_PARSE_ERROR );
		}

		$validInput = $this->validateInput( $rawInput );

		if ( ! $validInput ) {
			$this->fireExit( self::STATUS_VALIDATION_ERROR );
		}

		$payment = $this->findPayment( $validInput );

		if ( ! $payment || $payment->getGatewayId() !== $this->gateway->getId() ) {
			$this->fireExit( self::STATUS_REPOSITORY_ERROR );
		}

		$this->processEvent( $payment, $validInput );

		$this->fireExit();
	}

	/**
	 * @since 1.5.0
	 *
	 * @return mixed
	 */
	abstract public function parseInput();

	/**
	 * @since 1.5.0
	 *
	 * @param mixed $rawInput
	 * @return mixed|false
	 */
	abstract public function validateInput( $rawInput);

	/**
	 * @since 1.5.0
	 *
	 * @param mixed $validInput
	 * @return Payment|null
	 */
	abstract public function findPayment( $validInput);

	/**
	 * @since 1.5.0
	 *
	 * @param Payment $payment
	 * @param mixed $validInput
	 */
	abstract public function processEvent( $payment, $validInput);

	/**
	 * @since 1.5.0
	 *
	 * @return string
	 */
	public function getWebhookUrl() {
		$url = add_query_arg( self::URL_KEY, $this->gateway->getId(), home_url( 'index.php' ) );

		if ( is_ssl() ) {
			$url = preg_replace( '|^http://|', 'https://', $url );
		}

		return $url;
	}

	/**
	 * @since 1.5.0
	 *
	 * @return boolean
	 */
	public function isCurrentListenerRequest() {

		return ! empty( $_GET[ self::URL_KEY ] )
			&& $_GET[ self::URL_KEY ] === $this->gateway->getId();
	}

	/**
	 * @since 1.5.0
	 *
	 * @param int $statusCode Optional. Status code. Success by default (0).
	 */
	protected function fireExit( $statusCode = self::STATUS_SUCCESS ) {
		exit( absint( $statusCode ) );
	}
}
