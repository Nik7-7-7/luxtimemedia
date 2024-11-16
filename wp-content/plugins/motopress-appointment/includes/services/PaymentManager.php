<?php

namespace MotoPress\Appointment\Services;

use MotoPress\Appointment\Entities\Payment;
use MotoPress\Appointment\PostTypes\Statuses\PaymentStatuses;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.5.0
 */
class PaymentManager {

	/**
	 * @since 1.5.0
	 *
	 * @param Payment $payment
	 * @param string $log Optional. Custom reason. Not to be confused with the
	 *      status transition log.
	 * @param bool $skipCheck Optional. Skip the check that the payment can be
	 *      on hold. False by default.
	 * @return bool
	 */
	public function holdPayment( $payment, $log = '', $skipCheck = false ) {
		if ( ! $skipCheck && ! $this->canBeOnHold( $payment ) ) {
			return false;
		}

		/**
		 * @since 1.5.0
		 *
		 * @param Payment $payment
		 */
		do_action( 'mpa_before_hold_payment', $payment );

		if ( ! empty( $log ) ) {
			$payment->addLog( $log );
		}

		$onHold = (bool) mpa_update_status( $payment, PaymentStatuses::STATUS_ON_HOLD );

		if ( $onHold ) {
			/**
			 * @since 1.5.0
			 *
			 * @param Payment $payment
			 */
			do_action( 'mpa_payment_on_hold', $payment );
		}

		/**
		 * @since 1.5.0
		 *
		 * @param Payment $payment
		 * @param bool $isOnHold
		 */
		do_action( 'mpa_after_hold_payment', $payment, $onHold );

		return $onHold;
	}

	/**
	 * @since 1.5.0
	 *
	 * @param Payment $payment
	 * @param string $log Optional. Custom reason. Not to be confused with the
	 *      status transition log.
	 * @param bool $skipCheck Optional. Skip the check that the payment can be
	 *      completed. False by default.
	 * @return bool
	 */
	public function completePayment( $payment, $log = '', $skipCheck = false ) {
		if ( ! $skipCheck && ! $this->canBeCompleted( $payment ) ) {
			return false;
		}

		/**
		 * @since 1.5.0
		 *
		 * @param Payment $payment
		 */
		do_action( 'mpa_before_complete_payment', $payment );

		if ( ! empty( $log ) ) {
			$payment->addLog( $log );
		}

		$completed = (bool) mpa_update_status( $payment, PaymentStatuses::STATUS_COMPLETED );

		if ( $completed ) {
			/**
			 * @since 1.5.0
			 *
			 * @param Payment $payment
			 */
			do_action( 'mpa_payment_completed', $payment );
		}

		/**
		 * @since 1.5.0
		 *
		 * @param Payment $payment
		 * @param bool $isCompleted
		 */
		do_action( 'mpa_after_complete_payment', $payment, $completed );

		return $completed;
	}

	/**
	 * @since 1.5.0
	 *
	 * @param Payment $payment
	 * @param string $log Optional. Custom reason. Not to be confused with the
	 *      status transition log.
	 * @param bool $skipCheck Optional. Skip the check that the payment can be
	 *      cancelled. False by default.
	 * @return bool
	 */
	public function cancelPayment( $payment, $log = '', $skipCheck = false ) {
		if ( ! $skipCheck && ! $this->canBeCancelled( $payment ) ) {
			return false;
		}

		/**
		 * @since 1.5.0
		 *
		 * @param Payment $payment
		 */
		do_action( 'mpa_before_cancel_payment', $payment );

		if ( ! empty( $log ) ) {
			$payment->addLog( $log );
		}

		$cancelled = (bool) mpa_update_status( $payment, PaymentStatuses::STATUS_CANCELLED );

		if ( $cancelled ) {
			/**
			 * @since 1.5.0
			 *
			 * @param Payment $payment
			 */
			do_action( 'mpa_payment_cancelled', $payment );
		}

		/**
		 * @since 1.5.0
		 *
		 * @param Payment $payment
		 * @param bool $isCancelled
		 */
		do_action( 'mpa_after_cancel_payment', $payment, $cancelled );

		return $cancelled;
	}

	/**
	 * @since 1.5.0
	 *
	 * @param Payment $payment
	 * @param string $log Optional. Custom reason. Not to be confused with the
	 *      status transition log.
	 * @param bool $skipCheck Optional. Skip the check that the payment can be
	 *      abandoned. False by default.
	 * @return bool
	 */
	public function abandonPayment( $payment, $log = '', $skipCheck = false ) {
		if ( ! $skipCheck && ! $this->canBeAbandoned( $payment ) ) {
			return false;
		}

		/**
		 * @since 1.5.0
		 *
		 * @param Payment $payment
		 */
		do_action( 'mpa_before_abandon_payment', $payment );

		if ( ! empty( $log ) ) {
			$payment->addLog( $log );
		}

		$abandoned = (bool) mpa_update_status( $payment, PaymentStatuses::STATUS_ABANDONED );

		if ( $abandoned ) {
			/**
			 * @since 1.5.0
			 *
			 * @param Payment $payment
			 */
			do_action( 'mpa_payment_abandoned', $payment );
		}

		/**
		 * @since 1.5.0
		 *
		 * @param Payment $payment
		 * @param bool $isAbandoned
		 */
		do_action( 'mpa_after_abandon_payment', $payment, $abandoned );

		return $abandoned;
	}

	/**
	 * @since 1.5.0
	 *
	 * @param Payment $payment
	 * @param string $log Optional. Custom reason. Not to be confused with the
	 *      status transition log.
	 * @param bool $skipCheck Optional. Skip the check that the payment can be
	 *      failed. False by default.
	 * @return bool
	 */
	public function failPayment( $payment, $log = '', $skipCheck = false ) {
		if ( ! $skipCheck && ! $this->canBeFailed( $payment ) ) {
			return false;
		}

		/**
		 * @since 1.5.0
		 *
		 * @param Payment $payment
		 */
		do_action( 'mpa_before_fail_payment', $payment );

		if ( ! empty( $log ) ) {
			$payment->addLog( $log );
		}

		$failed = (bool) mpa_update_status( $payment, PaymentStatuses::STATUS_FAILED );

		if ( $failed ) {
			/**
			 * @since 1.5.0
			 *
			 * @param Payment $payment
			 */
			do_action( 'mpa_payment_failed', $payment );
		}

		/**
		 * @since 1.5.0
		 *
		 * @param Payment $payment
		 * @param bool $isFailed
		 */
		do_action( 'mpa_after_fail_payment', $payment, $failed );

		return $failed;
	}

	/**
	 * @since 1.5.0
	 *
	 * @param Payment $payment
	 * @param string $log Optional. Custom reason. Not to be confused with the
	 *      status transition log.
	 * @param bool $skipCheck Optional. Skip the check that the payment can be
	 *      refunded. False by default.
	 * @return bool
	 */
	public function refundPayment( $payment, $log = '', $skipCheck = false ) {
		if ( ! $skipCheck && ! $this->canBeRefunded( $payment ) ) {
			return false;
		}

		/**
		 * @since 1.5.0
		 *
		 * @param Payment $payment
		 */
		do_action( 'mpa_before_refund_payment', $payment );

		if ( ! empty( $log ) ) {
			$payment->addLog( $log );
		}

		$refunded = (bool) mpa_update_status( $payment, PaymentStatuses::STATUS_REFUNDED );

		if ( $refunded ) {

			/**
			 * @since 1.5.0
			 *
			 * @param Payment $payment
			 */
			do_action( 'mpa_payment_refunded', $payment );
		}

		/**
		 * @since 1.5.0
		 *
		 * @param Payment $payment
		 * @param bool $isRefunded
		 */
		do_action( 'mpa_after_refund_payment', $payment, $refunded );

		return $refunded;
	}

	/**
	 * @since 1.5.0
	 *
	 * @param Payment $payment
	 * @return bool
	 */
	public function canBeOnHold( $payment ) {
		$compatibleStatuses = array(
			PaymentStatuses::STATUS_PENDING,
		);

		$canBeOnHold = in_array( $payment->getStatus(), $compatibleStatuses );

		/**
		 * @since 1.5.0
		 *
		 * @param bool $canBeOnHold
		 * @param Payment $payment
		 */
		$canBeOnHold = (bool) apply_filters( 'mpa_payment_can_be_on_hold', $canBeOnHold, $payment );

		return $canBeOnHold;
	}

	/**
	 * @since 1.5.0
	 *
	 * @param Payment $payment
	 * @return bool
	 */
	public function canBeCompleted( $payment ) {
		$compatibleStatuses = array(
			PaymentStatuses::STATUS_PENDING,
			PaymentStatuses::STATUS_ON_HOLD,
		);

		$canBeCompleted = in_array( $payment->getStatus(), $compatibleStatuses );

		/**
		 * @since 1.5.0
		 *
		 * @param bool $canBeCompleted
		 * @param Payment $payment
		 */
		$canBeCompleted = (bool) apply_filters( 'mpa_payment_can_be_completed', $canBeCompleted, $payment );

		return $canBeCompleted;
	}

	/**
	 * @since 1.5.0
	 *
	 * @param Payment $payment
	 * @return bool
	 */
	public function canBeCancelled( $payment ) {
		$compatibleStatuses = array(
			PaymentStatuses::STATUS_PENDING,
			PaymentStatuses::STATUS_ON_HOLD,
			PaymentStatuses::STATUS_COMPLETED,
		);

		$canBeCancelled = in_array( $payment->getStatus(), $compatibleStatuses );

		/**
		 * @since 1.5.0
		 *
		 * @param bool $canBeCancelled
		 * @param Payment $payment
		 */
		$canBeCancelled = (bool) apply_filters( 'mpa_payment_can_be_cancelled', $canBeCancelled, $payment );

		return $canBeCancelled;
	}

	/**
	 * @since 1.5.0
	 *
	 * @param Payment $payment
	 * @return bool
	 */
	public function canBeAbandoned( $payment ) {
		$compatibleStatuses = array(
			PaymentStatuses::STATUS_PENDING,
		);

		$canBeAbandoned = in_array( $payment->getStatus(), $compatibleStatuses );

		/**
		 * @since 1.5.0
		 *
		 * @param bool $canBeAbandoned
		 * @param Payment $payment
		 */
		$canBeAbandoned = (bool) apply_filters( 'mpa_payment_can_be_abandoned', $canBeAbandoned, $payment );

		return $canBeAbandoned;
	}

	/**
	 * @since 1.5.0
	 *
	 * @param Payment $payment
	 * @return bool
	 */
	public function canBeFailed( $payment ) {
		$compatibleStatuses = array(
			PaymentStatuses::STATUS_PENDING,
			PaymentStatuses::STATUS_ON_HOLD,
		);

		$canBeFailed = in_array( $payment->getStatus(), $compatibleStatuses );

		/**
		 * @since 1.5.0
		 *
		 * @param bool $canBeFailed
		 * @param Payment $payment
		 */
		$canBeFailed = (bool) apply_filters( 'mpa_payment_can_be_failed', $canBeFailed, $payment );

		return $canBeFailed;
	}

	/**
	 * @since 1.5.0
	 *
	 * @param Payment $payment
	 * @return bool
	 */
	public function canBeRefunded( $payment ) {
		$compatibleStatuses = array(
			PaymentStatuses::STATUS_PENDING,
			PaymentStatuses::STATUS_ON_HOLD,
			PaymentStatuses::STATUS_COMPLETED,
		);

		$canBeRefunded = in_array( $payment->getStatus(), $compatibleStatuses );

		/**
		 * @since 1.5.0
		 *
		 * @param bool $canBeRefunded
		 * @param Payment $payment
		 */
		$canBeRefunded = (bool) apply_filters( 'mpa_payment_can_be_refunded', $canBeRefunded, $payment );

		return $canBeRefunded;
	}
}
