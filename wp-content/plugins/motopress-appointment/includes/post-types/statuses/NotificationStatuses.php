<?php

declare(strict_types=1);

namespace MotoPress\Appointment\PostTypes\Statuses;

use MotoPress\Appointment\Entities\Notification;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.13.0
 */
class NotificationStatuses extends AbstractPostStatuses {

	protected function addActions() {
		// No custom statuses to register
	}

	protected function initStatuses() {
		// No custom statuses
	}

	/**
	 * @param string $newStatus
	 * @param string $oldStatus
	 * @param Notification $entity
	 */
	protected function finishTransition( $newStatus, $oldStatus, $entity ) {
		// No additional steps are required
	}

	/**
	 * Required for custom submit box.
	 *
	 * @return array [Status => Label]
	 */
	public function getManualStatuses() {
		return array(
			self::STATUS_DRAFT   => esc_html_x( 'Disabled', 'Notification status', 'motopress-appointment' ),
			self::STATUS_PUBLISH => esc_html_x( 'Active', 'Notification status', 'motopress-appointment' ),
		);
	}

	public function getManualStatusLabel( $status ) {

		return ! empty( $this->getManualStatuses()[ $status ] ) ? $this->getManualStatuses()[ $status ] : '';
	}

	/**
	 * @return string
	 */
	public function getDefaultManualStatus() {
		return self::STATUS_PUBLISH;
	}

	/**
	 * Required for custom submit box.
	 *
	 * @param string $status
	 * @return bool
	 */
	public function hasStatus( $status ) {
		return self::STATUS_DRAFT === $status || self::STATUS_PUBLISH === $status;
	}
}
