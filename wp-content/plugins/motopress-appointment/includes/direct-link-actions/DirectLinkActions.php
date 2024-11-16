<?php

namespace MotoPress\Appointment\DirectLinkActions;

use MotoPress\Appointment\DirectLinkActions\Pages\BookingCancellationPage;
use MotoPress\Appointment\DirectLinkActions\Pages\BookingCancelledPage;
use MotoPress\Appointment\DirectLinkActions\Actions\BookingCancellationAction;
use MotoPress\Appointment\DirectLinkActions\Actions\StripeReturnURLAction;
use MotoPress\Appointment\DirectLinkActions\Pages\FailedTransactionPage;
use MotoPress\Appointment\DirectLinkActions\Pages\ReservationReceivedPage;

/**
 * @since 1.15.0
 */
class DirectLinkActions {

	/**
	 * @var BookingCancellationPage
	 */
	private $bookingCancellationPage;

	/**
	 * @var BookingCancelledPage
	 */
	private $bookingCancelledPage;

	/**
	 * @var ReservationReceivedPage
	 */
	private $paymentReceivedPage;

	/**
	 * @var FailedTransactionPage
	 */
	private $paymentFailedTransactionPage;

	/**
	 * @var BookingCancellationAction
	 */
	private $bookingCancellationAction;

	/**
	 * @var StripeReturnURLAction
	 */
	private $stripeReturnURLAction;

	public function __construct() {
		$this->initPages();
		$this->initActions();
	}

	private function initPages() {
		$this->bookingCancellationPage      = new BookingCancellationPage();
		$this->bookingCancelledPage         = new BookingCancelledPage();
		$this->paymentReceivedPage          = new ReservationReceivedPage();
		$this->paymentFailedTransactionPage = new FailedTransactionPage();
	}

	private function initActions() {
		$this->bookingCancellationAction = new BookingCancellationAction();
		$this->stripeReturnURLAction     = new StripeReturnURLAction();
	}

	/**
	 * @return BookingCancellationPage
	 */
	public function getBookingCancellationPage() {
		return $this->bookingCancellationPage;
	}

	/**
	 * @return BookingCancelledPage
	 */
	public function getBookingCancelledPage() {
		return $this->bookingCancelledPage;
	}

	/**
	 * @return ReservationReceivedPage
	 */
	public function getReservationReceivedPage() {
		return $this->paymentReceivedPage;
	}

	/**
	 * @return FailedTransactionPage
	 */
	public function getFailedTransactionPage() {
		return $this->paymentFailedTransactionPage;
	}

	/**
	 * @return BookingCancellationAction
	 */
	public function getBookingCancellationAction() {
		return $this->bookingCancellationAction;
	}

	/**
	 * @return StripeReturnURLAction
	 */
	public function getStripeReturnURLPage() {
		return $this->stripeReturnURLAction;
	}
}
