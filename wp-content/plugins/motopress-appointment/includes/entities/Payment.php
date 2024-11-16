<?php

namespace MotoPress\Appointment\Entities;

use MotoPress\Appointment\Payments\Gateways\AbstractPaymentGateway;
use WP_Comment;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.5.0
 * @see \MotoPress\Appointment\Repositories\PaymentRepository
 */
class Payment extends AbstractUniqueEntity {

	/**
	 * @since 1.5.0
	 * @since 1.19.0 protected
	 *
	 * @var string
	 */
	protected $status = 'new';

	/**
	 * @since 1.5.0
	 * @since 1.19.0 protected
	 * @var int
	 */
	protected $bookingId = 0;

	/**
	 * @since 1.5.0
	 * @var float
	 */
	protected $amount = 0.0;

	/**
	 * @since 1.5.0
	 * @since 1.19.0 protected
	 * @var string
	 */
	protected $currency = 'EUR';

	/**
	 * @since 1.5.0
	 * @var string
	 */
	protected $gatewayId = 'manual';

	/**
	 * @since 1.5.0
	 * @var string
	 */
	protected $gatewayMode = AbstractPaymentGateway::GATEWAY_MODE_LIVE;

	/**
	 * @since 1.5.0
	 * @since 1.19.0 protected
	 * @var string 'card', 'cash' etc.
	 */
	protected $paymentMethod = '';

	/**
	 * @since 1.5.0
	 * @since 1.19.0 protected
	 * @var string
	 */
	protected $transactionId = '';


	public function getStatus(): string {
		return $this->status;
	}

	public function setStatus( string $status ) {
		$this->status = $status;
	}

	public function getBookingId(): int {
		return $this->bookingId;
	}

	/**
	 * @since 1.5.0
	 *
	 * @return Booking|null
	 */
	public function getBooking() {
		if ( $this->bookingId > 0 ) {
			return mpa_get_booking( $this->bookingId );
		} else {
			return null;
		}
	}

	/**
	 * @since 1.5.0
	 *
	 * @return float
	 */
	public function getAmount() {
		return $this->amount;
	}


	public function getCurrency(): string {
		return $this->currency;
	}

	/**
	 * @since 1.5.0
	 *
	 * @return Booking|null
	 */
	public function getExpectingBooking() {
		$booking = $this->getBooking();

		if ( ! is_null( $booking ) && $booking->isExpectsPayment( $this ) ) {
			return $booking;
		} else {
			return null;
		}
	}

	/**
	 * Set the expiration time to current time + pending payment time.
	 *
	 * @since 1.5.0
	 */
	public function resetExpirationTime() {
		$expirationTime = time()
			+ mpapp()->settings()->getPendingPaymentTime() * MINUTE_IN_SECONDS;

		$this->setExpirationTime( $expirationTime );
	}

	/**
	 * @since 1.5.0
	 *
	 * @param int $timestamp
	 */
	public function setExpirationTime( $timestamp ) {
		update_post_meta( $this->id, '_mpa_pending_time', $timestamp );
	}

	/**
	 * @since 1.5.0
	 *
	 * @return int
	 */
	public function getExpirationTime() {
		$expirationTime = get_post_meta( $this->id, '_mpa_pending_time', true );

		return intval( $expirationTime );
	}

	/**
	 * @since 1.5.0
	 */
	public function removeExpirationTime() {
		delete_post_meta( $this->id, '_mpa_pending_time' );
	}

	public function getGatewayId(): string {
		return $this->gatewayId;
	}

	public function getGatewayMode(): string {
		return $this->gatewayMode;
	}

	/**
	 * @since 1.5.0
	 *
	 * @return AbstractPaymentGateway|null
	 */
	public function getGateway() {
		return mpapp()->payments()->getGateway( $this->gatewayId );
	}

	public function getPaymentMethod(): string {
		return $this->paymentMethod;
	}

	public function getTransactionId(): string {
		return $this->transactionId;
	}

	/**
	 * @since 1.5.0
	 *
	 * @return bool
	 */
	public function isPending() {
		return array_key_exists( $this->status, mpapp()->postTypes()->payment()->statuses()->getPendingStatuses() );
	}

	/**
	 * @since 1.5.0
	 *
	 * @return bool
	 */
	public function isCompleted() {
		return array_key_exists( $this->status, mpapp()->postTypes()->payment()->statuses()->getCompletedStatuses() );
	}

	/**
	 * @since 1.5.0
	 *
	 * @return bool
	 */
	public function isFailed() {
		return array_key_exists( $this->status, mpapp()->postTypes()->payment()->statuses()->getFailedStatuses() );
	}

	public function setGatewayMode( $gatewayMode, $save = false ) {
		$this->gatewayMode = $gatewayMode;

		if ( $save ) {
			update_post_meta( $this->id, '_mpa_gateway_mode', $this->gatewayMode );
		}
	}

	/**
	 * @since 1.5.0
	 *
	 * @param string $paymentMethod
	 * @param bool|mixed $save Optional. Whether to update the post meta. False by
	 *      default.
	 */
	public function setPaymentMethod( $paymentMethod, $save = false ) {
		$this->paymentMethod = $paymentMethod;

		if ( $save ) {
			update_post_meta( $this->id, '_mpa_payment_method', $paymentMethod );
		}
	}

	/**
	 * @since 1.5.0
	 *
	 * @param string $transactionId
	 * @param bool|mixed $save Optional. Whether to update the post meta. False by
	 *      default.
	 */
	public function setTransactionId( $transactionId, $save = false ) {
		$this->transactionId = $transactionId;

		if ( $save ) {
			update_post_meta( $this->id, '_mpa_transaction_id', $transactionId );
		}
	}

	/**
	 * @param int|null $authorId Optional. Current logged in user ID by default.
	 * @return int|false The new comment's ID on success, false on failure.
	 *
	 * @since 1.14.0
	 */
	public function addLog( string $message, $authorId = null ) {
		return mpapp()->postTypes()->payment()->logs()->addLog( $this->id, $message, $authorId );
	}

	/**
	 * @return WP_Comment[] Logs in descending order.
	 *
	 * @since 1.14.0
	 */
	public function getLogs(): array {
		return mpapp()->postTypes()->payment()->logs()->getLogs( $this->id );
	}

	/**
	 * @return mixed
	 */
	public function getGatewaySpecificData( string $gatewaySpecificDataKey ) {
		return get_post_meta( $this->getId(), $gatewaySpecificDataKey, true );
	}

	/**
	 * @param mixed $gatewaySpecificData
	 */
	public function setGatewaySpecificData( string $gatewaySpecificDataKey, $gatewaySpecificData ) {
		update_post_meta( $this->getId(), $gatewaySpecificDataKey, $gatewaySpecificData );
	}
}
