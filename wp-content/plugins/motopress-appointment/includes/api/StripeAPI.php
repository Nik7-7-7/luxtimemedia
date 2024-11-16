<?php

namespace MotoPress\Appointment\API;

use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\Account;
use Stripe\Stripe;
use WP_Error;
use Exception;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.5.0
 */
class StripeAPI {

	/** @since 1.16.0 */
	const API_VERSION = '2023-10-16';

	/** @since 1.16.0 */
	const PARTNER_ID = 'pp_partner_Fs0jSMbknaJwVC';

	/** @since 1.16.0 */
	const TRANSIENT_KEY_ACCOUNT_COUNTRY = 'mpa_stripe_account_country';

	/**
	 * @since 1.5.0
	 * @var string
	 */
	public $secretKey = '';

	/**
	 * @since 1.5.0
	 *
	 * @param string $secretKey
	 */
	public function __construct( $secretKey ) {
		$this->secretKey = $secretKey;
	}

	/**
	 * @since 1.5.0
	 */
	public function registerApp() {
		Stripe::setAppInfo( mpapp()->getName(), mpapp()->getVersion(), mpa_plugin_uri(), self::PARTNER_ID );
		Stripe::setApiVersion( self::API_VERSION );
		Stripe::setApiKey( $this->secretKey );
	}

	/**
	 *
	 * @since 1.5.0
	 *
	 * @param float $amount
	 * @param string $currency Optional.
	 * @return int
	 */
	public function convertToSmallestUnit( $amount, $currency = null ) {
		if ( ! $currency ) {
			$currency = mpapp()->settings()->getCurrency();
		}

		// See all currencies presented as links on page
		// https://stripe.com/docs/currencies#presentment-currencies
		switch ( strtoupper( $currency ) ) {
			// Zero decimal currencies
			case 'BIF':
			case 'CLP':
			case 'DJF':
			case 'GNF':
			case 'JPY':
			case 'KMF':
			case 'KRW':
			case 'MGA':
			case 'PYG':
			case 'RWF':
			case 'UGX':
			case 'VND':
			case 'VUV':
			case 'XAF':
			case 'XOF':
			case 'XPF':
				$amount = absint( $amount ); // Remove cents
				break;

			default:
				$amount = round( $amount * 100 ); // In cents
				break;
		}

		return (int) $amount;
	}

	/**
	 * @since 1.5.0
	 *
	 * @param string $currency
	 * @return float
	 */
	public function getMinimumAmount( $currency ) {
		// See https://stripe.com/docs/currencies#minimum-and-maximum-charge-amounts
		switch ( strtoupper( $currency ) ) {
			case 'GBP':
				return 0.30;

			default:
			case 'AUD':
			case 'BRL':
			case 'CAD':
			case 'CHF':
			case 'EUR':
			case 'INR':
			case 'NZD':
			case 'SGD':
			case 'USD':
				return 0.50;

			case 'BGN':
				return 1.00;

			case 'MYR':
				return 2;

			case 'AED':
			case 'PLN':
			case 'RON':
				return 2.00;

			case 'DKK':
				return 2.50;

			case 'NOK':
			case 'SEK':
				return 3.00;

			case 'HKD':
				return 4.00;

			case 'MXN':
				return 10;

			case 'CZK':
				return 15.00;

			case 'JPY':
				return 50;

			case 'HUF':
				return 175.00;
		}
	}

	/**
	 * Checks Stripe minimum amount value authorized per currency.
	 *
	 * @since 1.5.0
	 *
	 * @param float $amount
	 * @param string $currency
	 * @return bool
	 */
	public function checkMinimumAmount( $amount, $currency ) {
		$currentAmount = $this->convertToSmallestUnit( $amount, $currency );
		$minimumAmount = $this->convertToSmallestUnit( $this->getMinimumAmount( $currency ), $currency );

		return $currentAmount >= $minimumAmount;
	}

	/**
	 * @since 1.14.0
	 *
	 * @param array $requestArgs
	 * @return PaymentIntent|WP_Error Payment Intent or WP_Error.
	 */
	public function createPaymentIntent( $requestArgs ) {

		$this->registerApp();

		try {
			// See https://stripe.com/docs/api/payment_intents/create
			return PaymentIntent::create( $requestArgs );
		} catch ( Exception $error ) {
			return new WP_Error( 'stripe_api_error', $error->getMessage() );
		}
	}

	/**
	 * @since 1.5.0
	 *
	 * @param string $paymentIntentId
	 * @return PaymentIntent|WP_Error
	 */
	public function getPaymentIntent( $paymentIntentId ) {

		$this->registerApp();

		try {
			return PaymentIntent::retrieve( $paymentIntentId );
		} catch ( Exception $error ) {
			return new WP_Error( 'stripe_api_error', $error->getMessage() );
		}
	}

	/**
	 * @since 1.14.0
	 *
	 * @param string $paymentMethodId
	 *
	 * @return PaymentMethod|WP_Error
	 */
	public function getPaymentMethod( $paymentMethodId ) {

		$this->registerApp();

		try {
			return PaymentMethod::retrieve( $paymentMethodId );
		} catch ( Exception $error ) {
			return new WP_Error( 'stripe_api_error', $error->getMessage() );
		}
	}

	/**
	 * @since 1.5.0
	 *
	 * @param PaymentIntent $paymentIntent
	 * @param string $description
	 * @return true|WP_Error
	 */
	public function updatePaymentIntentDescription( $paymentIntent, $description ) {

		$this->registerApp();

		try {
			$paymentIntent->update( $paymentIntent->id, array( 'description' => $description ) );
		} catch ( Exception $error ) {
			return new WP_Error( 'stripe_api_error', $error->getMessage() );
		}

		return true;
	}

	/**
	 * @since 1.16.0
	 *
	 * @return string two-letter account country code
	 */
	public function getAccountCountry() {

		$cached = get_transient( self::TRANSIENT_KEY_ACCOUNT_COUNTRY );

		if ( false !== $cached ) {
			return $cached;
		}

		$this->registerApp();

		try {
			$accountCountry = Account::retrieve()->country;
		} catch ( Exception $error ) {
			return '';
		}

		set_transient( self::TRANSIENT_KEY_ACCOUNT_COUNTRY, $accountCountry, 1 * DAY_IN_SECONDS );

		return $accountCountry;
	}
}
