<?php

namespace MotoPress\Appointment\Payments\Gateways;

use WP_Error;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use MotoPress\Appointment\Payments\Gateways\Webhooks\PayPalWebhookListener;
use MotoPress\Appointment\Entities\Payment;
use \MotoPress\Appointment\Entities\Booking;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.6.0
 */
class PayPalPaymentGateway extends AbstractPaymentGateway {

	/**
	 * @since 1.6.0
	 */
	const OPTION_NAME_PAYPAL_WEBHOOK_ID = 'mpa_paypal_webhook_id';

	/**
	 * @since 1.6.0
	 */
	private $supportedCurrenciesAndTheirDecimals = array(
		'AUD' => 2,
		'BRL' => 2,
		'CAD' => 2,
		'CNY' => 2,
		'CZK' => 2,
		'DKK' => 2,
		'EUR' => 2,
		'HKD' => 2,
		'HUF' => 0,
		'ILS' => 2,
		'JPY' => 0,
		'MYR' => 2,
		'MXN' => 2,
		'TWD' => 0,
		'NZD' => 2,
		'NOK' => 2,
		'PHP' => 2,
		'PLN' => 2,
		'GBP' => 2,
		'RUB' => 2,
		'SGD' => 2,
		'SEK' => 2,
		'CHF' => 2,
		'THB' => 2,
		'USD' => 2,
	);

	/**
	 * @since 1.6.0
	 */
	private $paypalClientId = '';

	/**
	 * @since 1.6.0
	 */
	private $paypalSecret = '';

	/**
	 * @since 1.6.0
	 */
	private $webhookListener = null;

	/**
	 * @since 1.6.0
	 */
	public function __construct() {

		parent::__construct();

		$this->paypalClientId = $this->getOption( 'client_id', $this->paypalClientId );
		$this->paypalSecret   = $this->getOption( 'secret', $this->paypalSecret );

		if ( $this->isActive() ) {
			$this->webhookListener = new PayPalWebhookListener( $this );
			$this->webhookListener->addListeners();
		}

		if ( $this->isEnabled() && ! $this->isCurrencyFromSettingsSupported() && is_admin() ) {

			mpa_tmpl_notice(
				'warning',
				wp_kses_post(
					'<div class="notice notice-warning"><p>' .
					sprintf(
						// Translators: %s: currency code, like EUR
						__( 'PayPal does not support %s currency. If you want to use PayPal payment, set one of the allowed currencies from <a href="https://developer.paypal.com/docs/api/reference/currency-codes/#paypal-account-payments" target="_blank">this list</a>.', 'motopress-appointment' ),
						mpapp()->settings()->getCurrency()
					)
				),
				false
			);
		}

		add_action(
			'mpa_settings_saved',
			function( $currentTab, $currentSection, $settings ) {

				if ( isset( $settings['mpa_paypal_payment_gateway_enable'] ) ) {

					// load PayPal API client from vendor folder
					require_once \MotoPress\Appointment\PLUGIN_DIR . 'vendor/autoload.php';

					if ( $settings['mpa_paypal_payment_gateway_enable'] ) {

						$this->paypalClientId = $this->getOption( 'client_id', $this->paypalClientId );
						$this->paypalSecret   = $this->getOption( 'secret', $this->paypalSecret );

						$this->maybeRegisterPayPalWebhook();

					} else {

						$this->maybeDeletePayPalWebhook();
					}
				}
			},
			10,
			3
		);
	}

	public function getId(): string {
		return 'paypal';
	}

	public function getName(): string {
		return __( 'PayPal', 'motopress-appointment' );
	}

	public function getDefaultPublicName(): string {
		return __( 'Pay by PayPal', 'motopress-appointment' );
	}

	public function enqueueScripts() {

		// Main doc: https://developer.paypal.com/docs/business/javascript-sdk/javascript-sdk-reference/
		// How to configure: https://developer.paypal.com/docs/business/javascript-sdk/javascript-sdk-configuration/
		// Some options: https://developer.paypal.com/docs/checkout/integration-features/
		// How to test: https://developer.paypal.com/docs/business/test-and-go-live/
		// About errors https://developer.paypal.com/docs/api/reference/orders-v2-errors/#create-order
		// phpcs:ignore
		wp_enqueue_script(
			'paypal_sdk',
			'https://www.paypal.com/sdk/js?client-id=' . $this->paypalClientId .
			'&disable-funding=credit,bancontact,blik,eps,giropay,ideal,mercadopago,mybank,p24,sepa,sofort,venmo&currency=' .
			mpapp()->settings()->getCurrency(),
			array(),
			// do not set version because paypal server returns error
			null,
			true
		);
	}

	/**
	 * @since 1.6.0
	 */
	private function isCurrencyFromSettingsSupported() {
		return in_array( mpapp()->settings()->getCurrency(), array_keys( $this->supportedCurrenciesAndTheirDecimals ) );
	}

	/**
	 * @since 1.6.0
	 */
	public function getFrontendData() {

		$paypalPriceDecimals = 2;

		if ( $this->isCurrencyFromSettingsSupported() ) {

			$paypalPriceDecimals = $this->supportedCurrenciesAndTheirDecimals[ mpapp()->settings()->getCurrency() ];
		}

		return parent::getFrontendData() + array(
			'paypal_price_decimals' => $paypalPriceDecimals,
			'paypal_error_message'  => esc_html__( 'Something went wrong, please try again.', 'motopress-appointment' ),
		);
	}

	/**
	 * @since 1.6.0
	 * Fields of payment gateway settings
	 */
	public function getFields() {

		$fields = parent::getFields();

		$description = '<a href="https://developer.paypal.com/docs/business/get-started/#get-api-credentials" target="_blank">' .
			__( 'Get PayPal API credentials for live and sandbox modes', 'motopress-appointment' ) . '</a>';

		$fields[ $this->getOptionNameRaw( 'client_id' ) ] = array(
			'type'        => 'text',
			'label'       => esc_html__( 'Client ID', 'motopress-appointment' ),
			'description' => mpa_kses_link( $description ),
			'size'        => 'regular',
			'value'       => $this->paypalClientId,
		);

		$fields[ $this->getOptionNameRaw( 'secret' ) ] = array(
			'type'        => 'text',
			'label'       => esc_html__( 'Secret', 'motopress-appointment' ),
			'description' => mpa_kses_link( $description ),
			'size'        => 'regular',
			'value'       => $this->paypalSecret,
		);

		return $fields;
	}

	/**
	 * @since 1.6.0
	 */
	public function isActive() {

		return $this->isEnabled() &&
			! empty( $this->paypalClientId ) &&
			! empty( $this->paypalSecret ) &&
			$this->isCurrencyFromSettingsSupported();
	}

	/**
	 * @since 1.6.0
	 */
	public function printBillingFields() {

		echo '<div class="mpa-paypal-error mpa-hide"></div>
            <div class="mpa-paypal-container"></div>';
	}

	/**
	 * @since 1.6.0
	 * This SDK is used for getting Order payment_source details
	 */
	private function getPayPalClient():PayPalHttpClient {

		$environment = null;

		if ( $this->isSandbox() ) {

			$environment = new SandboxEnvironment( $this->paypalClientId, $this->paypalSecret );

		} else {

			$environment = new ProductionEnvironment( $this->paypalClientId, $this->paypalSecret );
		}

		$paypalClient = new PayPalHttpClient( $environment );

		return $paypalClient;
	}

	/**
	 * @since 1.6.0
	 * use this SDK for webhooks
	 */
	public function getPayPalAPIContext():\PayPal\Rest\ApiContext {
		return new \PayPal\Rest\ApiContext(
			new \PayPal\Auth\OAuthTokenCredential(
				$this->paypalClientId,
				$this->paypalSecret
			)
		);
	}

	/**
	 * @since 1.6.0
	 */
	private function maybeRegisterPayPalWebhook() {

		$paypalWebhookInfo = get_option( self::OPTION_NAME_PAYPAL_WEBHOOK_ID );

		if ( ! empty( $paypalWebhookInfo['webhook_id'] ) &&
			$this->paypalClientId == $paypalWebhookInfo['client_id'] &&
			$this->paypalSecret == $paypalWebhookInfo['secret'] ) {
			return;
		}

		if ( ! empty( $paypalWebhookInfo['webhook_id'] ) ) {
			// delete old webhook from old paypal credentials
			$this->maybeDeletePayPalWebhook();
		}

		// doc https://developer.paypal.com/docs/api/webhooks/v1/
		// https://github.com/paypal/PayPal-PHP-SDK/tree/master/sample/notifications
		$webhook            = new \PayPal\Api\Webhook();
		$webhookListenerURL = add_query_arg(
			\MotoPress\Appointment\Payments\Gateways\Webhooks\AbstractWebhooksListener::URL_KEY,
			$this->getId(),
			get_site_url() . '/'
		);
		$webhook->setUrl( $webhookListenerURL );

		// event names https://developer.paypal.com/docs/api-basics/notifications/webhooks/event-names/
		$webhookEventTypes = array(
			new \PayPal\Api\WebhookEventType(
				'{
                    "name":"PAYMENT.CAPTURE.REFUNDED"
                }'
			),
		);
		$webhook->setEventTypes( $webhookEventTypes );

		try {

			$apiContext = $this->getPayPalAPIContext();

			$webhookList = \PayPal\Api\Webhook::getAllWithParams( array(), $apiContext );

			$isWebhookAlreadyExist = false;

			foreach ( $webhookList->getWebhooks() as $existedWebhook ) {

				if ( $webhook->getUrl() == $existedWebhook->getUrl() ) {

					$isWebhookAlreadyExist = true;
					$webhook               = $existedWebhook;
					break;
				}
			}

			if ( ! $isWebhookAlreadyExist ) {

				$webhook = $webhook->create( $apiContext );
			}

			update_option(
				self::OPTION_NAME_PAYPAL_WEBHOOK_ID,
				array(
					'webhook_id' => $webhook->getId(),
					'client_id'  => $this->paypalClientId,
					'secret'     => $this->paypalSecret,
				)
			);

		} catch ( \Exception $e ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( $e );

			if ( $e instanceof \PayPal\Exception\PayPalConnectionException ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( $e->getData() );
			}
		}
	}

	/**
	 * @since 1.6.0
	 */
	private function maybeDeletePayPalWebhook() {

		$paypalWebhookInfo = get_option( self::OPTION_NAME_PAYPAL_WEBHOOK_ID );

		if ( empty( $paypalWebhookInfo['webhook_id'] ) ) {
			return;
		}

		$webhook = new \PayPal\Api\Webhook();
		$webhook->setId( $paypalWebhookInfo['webhook_id'] );

		update_option( self::OPTION_NAME_PAYPAL_WEBHOOK_ID, null );

		try {

			$webhook->delete( $this->getPayPalAPIContext() );

		} catch ( \Exception $e ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( $e );

			if ( $e instanceof \PayPal\Exception\PayPalConnectionException ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( $e->getData() );
			}
		}
	}

	/**
	 * Creates pending payment transaction which will be processed later.
	 * @param array $paymentData - can contains gateway specific data from frontend
	 * (for example, payment transaction id, token, payment intent id and so on)
	 * @return mixed any gateway specific data needed on frontend
	 */
	public function startPayment( Booking $booking, string $currencyCode, float $payingAmount, array $paymentData ) {

		$payment = parent::startPayment( $booking, $currencyCode, $payingAmount, $paymentData );

		return $payment->getTransactionId();
	}

	/**
	 * Each gateway can add here additional payment data for a new starting payment.
	 * @throws \Exception if payment had not been stored
	 */
	protected function prepareAndStoreNewPayment( Payment $payment, Booking $booking, array $paymentData ): Payment {

		$orderId = $this->preparePaymentIntent( $payment );

		if ( is_wp_error( $orderId ) ) {
			throw new \Exception( $orderId->get_error_message() );
		}

		$payment->setTransactionId( $orderId );

		$payment = parent::prepareAndStoreNewPayment( $payment, $booking, $paymentData );

		return $payment;
	}

	/**
	 * Creates PayPal order via REST API request
	 *
	 * @param Payment $payment
	 *
	 * @return string|WP_Error PayPal order id
	 *
	 * @since 1.14.0
	 */
	public function preparePaymentIntent( Payment $payment ) {

		$orderId      = '';
		$paypalClient = $this->getPayPalClient();

		// Construct a request object and set desired parameters
		// Here, OrdersCreateRequest() creates a POST request to /v2/checkout/orders
		$request = new OrdersCreateRequest();
		$request->prefer( 'return=representation' );
		$request->body = array(
			'intent'         => 'CAPTURE',
			'purchase_units' => array(
				array(
					'custom_id'   => $payment->getBookingId(),
					'description' => 'Booking #' . $payment->getBookingId(),
					'amount'      => array(
						'value'         => $payment->getAmount(),
						'currency_code' => $payment->getCurrency(),
					),
				),
			),
		);

		try {
			$response = $paypalClient->execute( $request );

			if ( isset( $response->result->id ) ) {
				$orderId = $response->result->id;
			}
		} catch ( \PayPalHttp\HttpException $e ) {

			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( $e->getMessage() );

			return new WP_Error( 'process_payment', $e->getMessage() );
		}

		return $orderId;
	}

	/**
	 * @param Payment $payment
	 * @param array $paymentData[ 'booking' => Booking, ... any gateway specific data from frontend ]
	 * @return Payment
	 * @throws \Exception if something goes wrong
	 */
	public function processPayment( $payment, $paymentData ) {

		if ( ! isset( $paymentData['paypalDetails']['id'] ) ) {

			$errorMessage = esc_html__( 'Failed to process PayPal payment: order id is not set.', 'motopress-appointment' );
			$payment->addLog( $errorMessage );

			throw new \Exception( 'process_payment', $errorMessage );
		}

		if ( ! isset( $paymentData['paypalDetails']['purchase_units'][0]['payments']['captures'][0]['id'] ) ) {

			$errorMessage = esc_html__( 'Failed to process PayPal payment: transaction id is not set.', 'motopress-appointment' );
			$payment->addLog( $errorMessage );

			throw new \Exception( 'process_payment', $errorMessage );
		}

		if ( ! isset( $paymentData['paypalDetails']['status'] ) ) {

			$errorMessage = esc_html__( 'Failed to process PayPal payment: transaction status is not set.', 'motopress-appointment' );
			$payment->addLog( $errorMessage );

			throw new \Exception( 'process_payment', $errorMessage );
		}

		$payment->setTransactionId( $paymentData['paypalDetails']['purchase_units'][0]['payments']['captures'][0]['id'], 'save' );

		// documetation about this request% https://developer.paypal.com/docs/api/orders/v2/#orders_get
		$orderId      = $paymentData['paypalDetails']['id'];
		$paypalClient = $this->getPayPalClient();
		$orderRequest = new OrdersGetRequest( $orderId );
		// get full order information to be able to get payment_source info
		$orderRequest->headers['prefer'] = 'return=representation';
		// $orderRequest->path .= 'fields=payment_source';

		$response = $paypalClient->execute( $orderRequest );

		if ( isset( $response->result->payment_source->card ) ) {

			$payment->setPaymentMethod( 'card', 'save' );

		} else {

			$payment->setPaymentMethod( 'paypal', 'save' );
		}

		if ( $this->isSandbox() ) {

			$payment->setGatewayMode( static::GATEWAY_MODE_SANDBOX, 'save' );
		}

		if ( 'COMPLETED' == $paymentData['paypalDetails']['status'] ) {

			mpa_payment_manager()->completePayment(
				$payment,
				sprintf(
					// Translators: %s: Payment intent type or ID.
					esc_html__( 'Payment for payment intent %s succeeded.', 'motopress-appointment' ),
					isset( $paymentData['paypalDetails']['intent'] ) ? esc_html( $paymentData['paypalDetails']['intent'] ) : 'unknown'
				)
			);

		} else {
			// Translators: %s: payment status for example 'Completed'
			$errorMessage = sprintf( esc_html__( 'Failed to process PayPal payment. It has %s status.', 'motopress-appointment' ), $paymentData['paypalDetails']['status'] );
			$payment->addLog( $errorMessage );

			throw new \Exception( $errorMessage );
		}

		return $payment;
	}
}
