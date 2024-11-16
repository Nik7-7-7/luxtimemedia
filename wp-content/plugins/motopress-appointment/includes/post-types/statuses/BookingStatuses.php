<?php

namespace MotoPress\Appointment\PostTypes\Statuses;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class BookingStatuses extends AbstractPostStatuses {

	/** @since 1.0 */
	const STATUS_PENDING = 'pending';

	/** @since 1.0 */
	const STATUS_CANCELLED = 'cancelled';

	/** @since 1.0 */
	const STATUS_ABANDONED = 'abandoned';

	/** @since 1.0 */
	const STATUS_CONFIRMED = 'confirmed';

	/**
	 * @since 1.0
	 */
	protected function initStatuses() {
		$this->statuses[ self::STATUS_PENDING ] = array(
			'label'       => esc_html_x( 'Pending', 'Booking status', 'motopress-appointment' ),
			// Translators: %s: The posts count.
			'label_count' => _n_noop( 'Pending <span class="count">(%s)</span>', 'Pending <span class="count">(%s)</span>', 'motopress-appointment' ),
			'is_public'   => true,
			'is_internal' => false,
			'is_manual'   => true,
		);

		$this->statuses[ self::STATUS_CANCELLED ] = array(
			'label'       => esc_html_x( 'Cancelled', 'Booking status', 'motopress-appointment' ),
			// Translators: %s: The posts count.
			'label_count' => _n_noop( 'Cancelled <span class="count">(%s)</span>', 'Cancelled <span class="count">(%s)</span>', 'motopress-appointment' ),
			'is_public'   => true,
			'is_internal' => false,
			'is_manual'   => true,
		);

		$this->statuses[ self::STATUS_ABANDONED ] = array(
			'label'       => esc_html_x( 'Abandoned', 'Booking status', 'motopress-appointment' ),
			// Translators: %s: The posts count.
			'label_count' => _n_noop( 'Abandoned <span class="count">(%s)</span>', 'Abandoned <span class="count">(%s)</span>', 'motopress-appointment' ),
			'is_public'   => true,
			'is_internal' => false,
			'is_manual'   => false,
		);

		$this->statuses[ self::STATUS_CONFIRMED ] = array(
			'label'       => esc_html_x( 'Confirmed', 'Booking status', 'motopress-appointment' ),
			// Translators: %s: The posts count.
			'label_count' => _n_noop( 'Confirmed <span class="count">(%s)</span>', 'Confirmed <span class="count">(%s)</span>', 'motopress-appointment' ),
			'is_public'   => true,
			'is_internal' => false,
			'is_manual'   => true,
		);
	}

	/**
	 * @param string $newStatus
	 * @param string $oldStatus
	 * @param \MotoPress\Appointment\Entities\Booking $booking
	 *
	 * @since 1.0
	 */
	protected function finishTransition( $newStatus, $oldStatus, $booking ) {
		// Add booking log
		$newLabel = $this->getLabel( $newStatus );
		$oldLabel = $this->getLabel( $oldStatus );

		// Translators: 1: Old status name (like "Pending"), 2: New status name.
		$booking->addLog( sprintf( esc_html__( 'Status changed from %1$s to %2$s.', 'motopress-appointment' ), $oldLabel, $newLabel ) );
	}

	/**
	 * @return array [Status => Label]
	 *
	 * @since 1.0
	 */
	public function getPendingStatuses() {
		return $this->getLabels(
			array(
				self::STATUS_PENDING,
			)
		);
	}

	/**
	 * @return array [Status => Label]
	 *
	 * @since 1.0
	 */
	public function getFailedStatuses() {
		return $this->getLabels(
			array(
				self::STATUS_CANCELLED,
				self::STATUS_ABANDONED,
			)
		);
	}

	/**
	 * @since 1.15.2
	 *
	 * @return string[]
	 */
	public function getUnblockedTimeSlotsStatuses() {
		return array(
			self::STATUS_DRAFT,
			self::STATUS_TRASH,
			self::STATUS_CANCELLED,
			self::STATUS_ABANDONED,
		);
	}

	/**
	 * @since 2.0.0
	 *
	 * @return string[] [ Status code => Label ]
	 */
	public function getBlockedTimeSlotsStatuses(): array {
		return $this->getLabels(
			[
				self::STATUS_PENDING,
				self::STATUS_CONFIRMED,
			]
		);
	}

	/**
	 * @return array [Status => Label]
	 *
	 * @since 1.0
	 */
	public function getBookedStatuses() {
		return $this->getLabels(
			array(
				self::STATUS_CONFIRMED,
			)
		);
	}

	/**
	 * @since 1.5.0
	 *
	 * @return string
	 */
	public function getDefaultManualStatus() {
		return self::STATUS_CONFIRMED;
	}
}
