<?php

namespace MotoPress\Appointment\PostTypes\Statuses;

use MotoPress\Appointment\Entities\Booking;
use MotoPress\Appointment\Entities\Payment;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.5.0
 */
class PaymentStatuses extends AbstractPostStatuses {

	/** @since 1.5.0 */
	const STATUS_PENDING = 'pending';
	/** @since 1.5.0 */
	const STATUS_ON_HOLD = 'on-hold';
	/** @since 1.5.0 */
	const STATUS_COMPLETED = 'completed';
	/** @since 1.5.0 */
	const STATUS_CANCELLED = 'cancelled';
	/** @since 1.5.0 */
	const STATUS_ABANDONED = 'abandoned';
	/** @since 1.5.0 */
	const STATUS_FAILED = 'failed';
	/** @since 1.5.0 */
	const STATUS_REFUNDED = 'refunded';

	/**
	 * @since 1.5.0
	 */
	protected function initStatuses() {

		$this->statuses[ self::STATUS_PENDING ] = array(
			'label'       => esc_html_x( 'Pending', 'Payment status', 'motopress-appointment' ),
			// Translators: %s: The posts count.
			'label_count' => _n_noop( 'Pending <span class="count">(%s)</span>', 'Pending <span class="count">(%s)</span>', 'motopress-appointment' ),
			'is_public'   => true,
			'is_internal' => false,
			'is_manual'   => true,
		);

		$this->statuses[ self::STATUS_ON_HOLD ] = array(
			'label'       => esc_html_x( 'On Hold', 'Payment status', 'motopress-appointment' ),
			// Translators: %s: The posts count.
			'label_count' => _n_noop( 'On Hold <span class="count">(%s)</span>', 'On Hold <span class="count">(%s)</span>', 'motopress-appointment' ),
			'is_public'   => true,
			'is_internal' => false,
			'is_manual'   => true,
		);

		$this->statuses[ self::STATUS_COMPLETED ] = array(
			'label'       => esc_html_x( 'Completed', 'Payment status', 'motopress-appointment' ),
			// Translators: %s: The posts count.
			'label_count' => _n_noop( 'Completed <span class="count">(%s)</span>', 'Completed <span class="count">(%s)</span>', 'motopress-appointment' ),
			'is_public'   => true,
			'is_internal' => false,
			'is_manual'   => true,
		);

		$this->statuses[ self::STATUS_CANCELLED ] = array(
			'label'       => esc_html_x( 'Canceled', 'Payment status', 'motopress-appointment' ),
			// Translators: %s: The posts count.
			'label_count' => _n_noop( 'Canceled <span class="count">(%s)</span>', 'Canceled <span class="count">(%s)</span>', 'motopress-appointment' ),
			'is_public'   => true,
			'is_internal' => false,
			'is_manual'   => true,
		);

		$this->statuses[ self::STATUS_ABANDONED ] = array(
			'label'       => esc_html_x( 'Abandoned', 'Payment status', 'motopress-appointment' ),
			// Translators: %s: The posts count.
			'label_count' => _n_noop( 'Abandoned <span class="count">(%s)</span>', 'Abandoned <span class="count">(%s)</span>', 'motopress-appointment' ),
			'is_public'   => true,
			'is_internal' => false,
			'is_manual'   => true,
		);

		$this->statuses[ self::STATUS_FAILED ] = array(
			'label'       => esc_html_x( 'Failed', 'Payment status', 'motopress-appointment' ),
			// Translators: %s: The posts count.
			'label_count' => _n_noop( 'Failed <span class="count">(%s)</span>', 'Failed <span class="count">(%s)</span>', 'motopress-appointment' ),
			'is_public'   => true,
			'is_internal' => false,
			'is_manual'   => true,
		);

		$this->statuses[ self::STATUS_REFUNDED ] = array(
			'label'       => esc_html_x( 'Refunded', 'Payment status', 'motopress-appointment' ),
			// Translators: %s: The posts count.
			'label_count' => _n_noop( 'Refunded <span class="count">(%s)</span>', 'Refunded <span class="count">(%s)</span>', 'motopress-appointment' ),
			'is_public'   => true,
			'is_internal' => false,
			'is_manual'   => true,
		);
	}

	/**
	 * @since 1.5.0
	 *
	 * @param string $newStatus
	 * @param string $oldStatus
	 * @param Payment $payment
	 */
	protected function finishTransition( $newStatus, $oldStatus, $payment ) {
		$this->logTransition( $newStatus, $oldStatus, $payment );

		$this->updatePendingTime( $payment, $newStatus, $oldStatus );

		// Update expecting booking
		$booking = $payment->getExpectingBooking();

		if ( ! is_null( $booking ) ) {
			$this->updateBooking( $booking, $payment );
		}
	}

	/**
	 * @since 1.5.0
	 *
	 * @param string $newStatus
	 * @param string $oldStatus
	 * @param Payment $payment
	 */
	protected function logTransition( $newStatus, $oldStatus, $payment ) {
		$newLabel = $this->getLabel( $newStatus );
		$oldLabel = $this->getLabel( $oldStatus );

		// Translators: 1: Old status name (like "Pending"), 2: New status name.
		$payment->addLog( sprintf( esc_html__( 'Status changed from %1$s to %2$s.', 'motopress-appointment' ), $oldLabel, $newLabel ) );
	}

	/**
	 * @since 1.5.0
	 *
	 * @param Payment $payment
	 * @param string $newStatus
	 * @param string $oldStatus
	 */
	protected function updatePendingTime( $payment, $newStatus, $oldStatus ) {

		if ( self::STATUS_PENDING === $newStatus ) {
			$payment->resetExpirationTime();

			\MotoPress\Appointment\Crons\AbandonPendingPaymentCron::schedule();

		} elseif ( self::STATUS_PENDING === $oldStatus ) {
			$payment->removeExpirationTime();
		}
	}

	/**
	 * @since 1.5.0
	 * @since 1.6.0 Added processing of refunded payment status
	 *
	 * @param Booking $booking
	 * @param Payment $payment
	 */
	protected function updateBooking( $booking, $payment ) {

		switch ( $payment->getStatus() ) {
			case self::STATUS_ON_HOLD:
				if ( mpapp()->payments()->isInstantGateway( $payment->getGatewayId() ) ) {
					mpa_update_status( $booking, BookingStatuses::STATUS_CONFIRMED );

					/**
					 * @since 1.5.0
					 *
					 * @param Booking $booking
					 * @param Payment $payment
					 */
					do_action( 'mpa_booking_confirmed_with_payment', $booking, $payment );

				} else {
					mpa_update_status( $booking, BookingStatuses::STATUS_PENDING );

					$bookingMessage = sprintf(
						// Translators: %s: Payment gateway name, like "Pay Afterwards".
						esc_html__( 'Payment (%s) for this booking is on hold.', 'motopress-appointment' ),
						mpapp()->payments()->getGatewayName( $payment->getGatewayId() )
					);

					$booking->addLog( $bookingMessage );
				}
				break;

			case self::STATUS_COMPLETED:
				if ( mpapp()->payments()->isInstantGateway( $payment->getGatewayId() ) ) {
					// Do nothing. Instant gateways already triggered an email
					// and "booking_confirmed" hook on status STATUS_ON_HOLD.
				} else {
					mpa_update_status( $booking, BookingStatuses::STATUS_CONFIRMED );

					/**
					 * @since 1.5.0
					 *
					 * @param Booking $booking
					 * @param Payment $payment
					 */
					do_action( 'mpa_booking_confirmed_with_payment', $booking, $payment );
				}
				break;

			case self::STATUS_ABANDONED:
				mpa_update_status( $booking, BookingStatuses::STATUS_ABANDONED );
				break;

			case self::STATUS_CANCELLED:
			case self::STATUS_FAILED:
				mpa_update_status( $booking, BookingStatuses::STATUS_CANCELLED );
				break;

			case self::STATUS_REFUNDED:
				mpa_update_status( $booking, BookingStatuses::STATUS_CANCELLED );

				$booking->addLog( esc_html__( 'Booking is canceled because its payment was refunded.', 'motopress-appointment' ) );
				break;
		}
	}

	/**
	 * @since 1.5.0
	 *
	 * @return array [Status => Label]
	 */
	public function getPendingStatuses() {
		return $this->getLabels(
			array(
				self::STATUS_PENDING,
				self::STATUS_ON_HOLD,
			)
		);
	}

	/**
	 * @since 1.5.0
	 *
	 * @return array [Status => Label]
	 */
	public function getFailedStatuses() {
		return $this->getLabels(
			array(
				self::STATUS_CANCELLED,
				self::STATUS_ABANDONED,
				self::STATUS_FAILED,
			)
		);
	}

	/**
	 * @since 1.5.0
	 *
	 * @return array [Status => Label]
	 */
	public function getCompletedStatuses() {
		return $this->getLabels(
			array(
				self::STATUS_COMPLETED,
			)
		);
	}

	/**
	 * @since 1.5.0
	 *
	 * @return string
	 */
	public function getDefaultManualStatus() {
		return self::STATUS_COMPLETED;
	}
}
