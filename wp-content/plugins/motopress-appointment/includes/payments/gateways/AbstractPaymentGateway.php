<?php

namespace MotoPress\Appointment\Payments\Gateways;

use MotoPress\Appointment\Entities\Payment;
use \MotoPress\Appointment\Entities\Booking;
use \MotoPress\Appointment\PostTypes\Statuses\PaymentStatuses;
use \MotoPress\Appointment\PostTypes\PaymentPostType;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.5.0
 */
abstract class AbstractPaymentGateway {

	const GATEWAY_MODE_LIVE    = 'live';
	const GATEWAY_MODE_SANDBOX = 'sandbox';

	/**
	 * Unprefixed payment gateway ID.
	 *
	 * @deprecated overwrite getName method in your gateway
	 * @var string
	 */
	protected $id = 'abstract';

	/**
	 * Gateway name (label) on the settings page.
	 *
	 * @deprecated overwrite getName method in your gateway
	 * @var string
	 */
	protected $name = 'Abstract Payment';

	/**
	 * Public name (label) shown on the frontend.
	 *
	 * @deprecated overwrite getName method in your gateway
	 * @var string
	 */
	protected $publicName = '';

	/**
	 * Public description.
	 *
	 * @deprecated overwrite getName method in your gateway
	 * @var string
	 */
	protected $description = '';

	/**
	 * @since 1.5.0
	 * @var bool
	 */
	protected $isEnabled = false;

	/**
	 * @deprecated overwrite getName method in your gateway
	 * @var bool
	 */
	protected $hasSandboxSupport = true;

	/**
	 * @since 1.5.0
	 * @var bool
	 */
	protected $isSandbox = false;

	/**
	 * @since 1.5.0
	 */
	public function __construct() {
		$this->setupProperties();
	}

	/**
	 * @return string Unprefixed payment gateway ID.
	 */
	public function getId(): string {
		return $this->id;
	}

	/**
	 * @return string Gateway name / label.
	 */
	public function getName(): string {
		return $this->name;
	}

	final public function getPublicName(): string {
		return $this->getOption( 'title', $this->getDefaultPublicName() );
	}

	protected function getDefaultPublicName(): string {
		return ! empty( $this->publicName ) ? $this->publicName : $this->getName();
	}

	final public function getDescription(): string {
		return $this->getOption( 'description', $this->getDefaultDescription() );
	}

	protected function getDefaultDescription(): string {
		return $this->description;
	}

	/**
	 * @return bool If true, then don't show this payment gateway on the settings page.
	 */
	public function isInternal(): bool {
		return false;
	}

	public function isEnabled(): bool {
		return $this->isEnabled;
	}

	/**
	 * @return string 'live'|'sandbox'
	 */
	public function getMode(): string {
		return $this->isSandbox ? static::GATEWAY_MODE_SANDBOX : static::GATEWAY_MODE_LIVE;
	}

	public function isSandboxModeEnabled(): bool {
		return $this->isSandbox;
	}

	/**
	 * @return bool Whether gateway is enabled and supports current plugin settings (for example, currency).
	 */
	public function isActive() {
		return $this->isEnabled();
	}

	/**
	 * @return bool Is sandbox enabled.
	 */
	public function isSandbox(): bool {
		return $this->isSandbox;
	}

	public function isSupportsSandbox(): bool {
		return $this->hasSandboxSupport;
	}

	public function isOnlinePayment() {
		return true;
	}

	/**
	 * @since 1.5.0
	 * @deprecated use own method in constructor
	 */
	protected function setupProperties() {

		$this->publicName  = $this->getOption( 'title', $this->publicName );
		$this->description = $this->getOption( 'description', $this->description );
		$this->isEnabled   = (bool) $this->getOption( 'enable', $this->isEnabled );
		$this->isSandbox   = (bool) $this->getOption( 'sandbox', $this->isSandbox );
	}

	/**
	 * @return array All additional data, required for a gateway to function properly on the frontend.
	 */
	public function getFrontendData() {

		return array(
			'online'       => $this->isOnlinePayment(),
			'redirect_url' => array(
				'payment_received'   => mpapp()->settings()->getReservationReceivedPageUrl(),
				'failed_transaction' => mpapp()->settings()->getFailedTransactionPageUrl(),
			),
		);
	}

	/**
	 * An empty method is provided to add wp_enqueue_script() handlers of each of the payment methods, if necessary.
	 * @see \MotoPress\Appointment\Shortcodes\AppointmentFormShortcode::enqueueScripts
	 *
	 * @return void
	 */
	public function enqueueScripts() {}

	/**
	 * @return array of payment gateway settings arguments.
	 */
	public function getFields() {

		$backUrl = mpapp()->pages()->settings()->getUrl(
			array(
				'tab' => 'payment',
			)
		);

		$fields = array(
			$this->getOptionNameRaw( 'group' )       => array(
				'type'          => 'group',
				'label'         => $this->getName(),
				'description'   => $this->getAdminDescription(),
				'title_actions' => array(
					$backUrl => esc_html__( 'Back', 'motopress-appointment' ),
				),
			),

			$this->getOptionNameRaw( 'enable' )      => array(
				'type'    => 'checkbox',
				'label'   => esc_html__( 'Enable/Disable', 'motopress-appointment' ),
				'label2'  => esc_html__( 'Enable this payment method', 'motopress-appointment' ),
				'default' => false,
				'value'   => $this->isEnabled(),
			),

			$this->getOptionNameRaw( 'sandbox' )     => array(
				'type'        => 'checkbox',
				'label'       => esc_html__( 'Test Mode', 'motopress-appointment' ),
				'label2'      => esc_html__( 'Enable Sandbox Mode', 'motopress-appointment' ),
				'description' => esc_html__( 'Sandbox can be used to test payments.', 'motopress-appointment' ),
				'default'     => false,
				'value'       => $this->isSandbox(),
			),

			$this->getOptionNameRaw( 'title' )       => array(
				'type'         => 'text',
				'label'        => esc_html__( 'Title', 'motopress-appointment' ),
				'description'  => esc_html__( 'Payment method title that the customer will see on your website.', 'motopress-appointment' ),
				'size'         => 'regular',
				'translatable' => true,
				'value'        => $this->getPublicName(),
			),

			$this->getOptionNameRaw( 'description' ) => array(
				'type'         => 'textarea',
				'label'        => esc_html__( 'Description', 'motopress-appointment' ),
				'description'  => esc_html__( 'Payment method description that the customer will see on your website.', 'motopress-appointment' ),
				'rows'         => 3,
				'size'         => 'large',
				'translatable' => true,
				'value'        => $this->getDescription(),
			),
		);

		// Maybe remove sandbox option ("Test Mode")
		if ( ! $this->isSupportsSandbox() ) {
			$sandboxOption = $this->getOptionNameRaw( 'sandbox' );
			unset( $fields[ $sandboxOption ] );
		}

		return $fields;
	}

	/**
	 * @return string Payment gateway description on the top of the page, before the first field.
	 */
	protected function getAdminDescription() {

		$settingsNotice = '';

		if ( ! mpapp()->settings()->isPaymentsEnabled() ) {

			$noticeMessage = wp_kses( __( '<strong>Note:</strong> Customers will see payment methods only if online payments are enabled in Appointments > Settings > Confirmation Mode.', 'motopress-appointment' ), array( 'strong' => array() ) );

			$settingsNotice = mpa_tmpl_notice( 'warning', $noticeMessage, false );
		}

		return $settingsNotice;
	}

	/**
	 * @param mixed $default Optional. '' by default.
	 * @return mixed
	 */
	protected function getOption( string $unprefixedOptionName, $defaultValue = '' ) {

		$optionName  = mpa_prefix( $this->getOptionNameRaw( $unprefixedOptionName ) );
		$optionValue = get_option( $optionName, $defaultValue );

		if ( $this->isTranslatableOption( $unprefixedOptionName ) ) {
			$optionValue = mpa_translate_string( $optionValue, $optionName );
		}

		return $optionValue;
	}

	/**
	 * @param string $option
	 * @return string Unprefixed option name.
	 */
	protected function getOptionNameRaw( $option ): string {
		return "{$this->getId()}_payment_gateway_{$option}";
	}

	protected function isTranslatableOption( string $unprefixedOptionName ): bool {
		return in_array( $unprefixedOptionName, array( 'title', 'description' ), true );
	}

	/**
	 * Echos payment gateway fields on frontend.
	 */
	public function printBillingFields() {}

	/**
	 * Creates pending payment transaction which will be processed later.
	 * @param array $paymentData - can contains gateway specific data from frontend
	 * (for example, payment transaction id, token, payment intent id and so on)
	 * @return mixed any gateway specific data needed on frontend
	 */
	public function startPayment( Booking $booking, string $currencyCode, float $payingAmount, array $paymentData ) {

		$payment = new Payment(
			$booking->getExpectingPaymentId(),
			array(
				'status'      => PaymentStatuses::STATUS_PENDING,
				'bookingId'   => $booking->getId(),
				'amount'      => $payingAmount,
				'currency'    => $currencyCode,
				'gatewayId'   => $this->getId(),
				'gatewayMode' => $this->getMode(),
			)
		);

		$payment = $this->prepareAndStoreNewPayment( $payment, $booking, $paymentData );

		// update booking expecting payment in case if payment was new
		$booking->expectPayment( $payment );
		mpapp()->repositories()->booking()->saveBooking( $booking );

		return $payment;
	}

	/**
	 * Each gateway can add here additional payment data for a new starting payment.
	 * @throws \Exception if payment had not been stored
	 */
	protected function prepareAndStoreNewPayment( Payment $payment, Booking $booking, array $paymentData ): Payment {
		return $this->storePayment( $payment );
	}

	/**
	 * @throws \Exception if payment had not been stored
	 */
	final protected function storePayment( Payment $payment ): Payment {

		$paymentId = $payment->getId();

		if ( ! $payment->getId() ) {

			$paymentId = wp_insert_post(
				array(
					'post_type'   => PaymentPostType::POST_TYPE,
					'post_status' => $payment->getStatus(),
					'post_parent' => $payment->getBookingId(),
				),
				true
			);

			if ( is_wp_error( $paymentId ) ) {

				throw new \Exception( $paymentId->get_error_message() );
			}

			$payment->setId( $paymentId );
		} else {

			mpa_update_post_status( $paymentId, $payment->getStatus() );
		}

		mpa_add_post_uid( $paymentId, $payment->getUid() );

		update_post_meta( $paymentId, '_mpa_amount', $payment->getAmount() );
		update_post_meta( $paymentId, '_mpa_currency', $payment->getCurrency() );
		update_post_meta( $paymentId, '_mpa_gateway_id', $payment->getGatewayId() );
		update_post_meta( $paymentId, '_mpa_gateway_mode', $payment->getGatewayMode() );
		update_post_meta( $paymentId, '_mpa_payment_method', $payment->getPaymentMethod() );
		update_post_meta( $paymentId, '_mpa_transaction_id', $payment->getTransactionId() );

		return $payment;
	}

	/**
	 * @param Payment $payment
	 * @param array $paymentData[ 'booking' => Booking, ... any gateway specific data from frontend ]
	 * @return Payment
	 * @throws \Exception if something goes wrong
	 */
	abstract public function processPayment( $payment, $paymentData );
}
