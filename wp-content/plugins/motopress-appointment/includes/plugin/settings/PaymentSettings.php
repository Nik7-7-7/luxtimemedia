<?php

namespace MotoPress\Appointment\Plugin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.5.0
 */
trait PaymentSettings {

	/**
	 * An alias of <code>isPaymentConfirmationMode()</code>.
	 *
	 * @since 1.5.0
	 *
	 * @return bool
	 */
	public function isPaymentsEnabled() {
		return $this->isPaymentConfirmationMode();
	}

	/**
	 * @since 1.5.0
	 *
	 * @return int
	 */
	public function getReservationReceivedPageId() {
		$pageId = (int) get_option( 'mpa_payment_success_page', 0 );
		$pageId = mpa_translate_page_id( $pageId );

		return $pageId;
	}

	/**
	 * @since 1.5.0
	 *
	 * @return string
	 */
	public function getReservationReceivedPageUrl() {

		$pageId  = $this->getReservationReceivedPageId();
		$pageUrl = get_permalink( $pageId );

		if ( false === $pageUrl ) {
			$pageUrl = home_url();
		}

		return $pageUrl;
	}

	/**
	 * @since 1.5.0
	 *
	 * @return int
	 */
	public function getFailedTransactionPageId() {
		$pageId = (int) get_option( 'mpa_payment_failed_page', 0 );
		$pageId = mpa_translate_page_id( $pageId );

		return $pageId;
	}

	/**
	 * @since 1.5.0
	 *
	 * @return string
	 */
	public function getFailedTransactionPageUrl() {

		$pageId = $this->getFailedTransactionPageId();
		$pageUrl = get_permalink( $pageId );

		if ( false === $pageUrl ) {
			$pageUrl = home_url();
		}

		return $pageUrl;
	}

	/**
	 * @since 1.5.0
	 *
	 * @return string
	 */
	public function getDefaultPaymentGateway() {
		return get_option( 'mpa_default_payment_gateway', '' );
	}

	/**
	 * @since 1.5.0
	 *
	 * @return int Pending payment time (minutes).
	 */
	public function getPendingPaymentTime() {
		return (int) get_option( 'mpa_pending_payment_time', $this->getDefaultPendingPaymentTime() );
	}

	/**
	 * @since 1.5.0
	 *
	 * @return int Default pending time (minutes).
	 */
	public function getDefaultPendingPaymentTime() {
		return 60; // One hour
	}

	/**
	 * @since 1.5.0
	 *
	 * @return array
	 */
	public function getPaymentSettings() {
		return $this->getPublicPaymentSettings() + array(
			'reservation_received_page_id'   => $this->getReservationReceivedPageId(),
			'failed_transaction_page_id' => $this->getFailedTransactionPageId(),
			'pending_payment_time'       => $this->getPendingPaymentTime(),
		);
	}

	/**
	 * @since 1.5.0
	 *
	 * @return array
	 */
	public function getPublicPaymentSettings() {
		return array(
			'enable_payments'             => $this->isPaymentsEnabled(),
			'active_gateways'             => array_keys( mpapp()->payments()->getActive() ),
			'reservation_received_page_url'   => $this->getReservationReceivedPageUrl(),
			'failed_transaction_page_url' => $this->getFailedTransactionPageUrl(),
			'default_payment_gateway'     => $this->getDefaultPaymentGateway(),
		);
	}
}
