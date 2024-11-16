<?php

declare(strict_types=1);

namespace MotoPress\Appointment\Crons;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.13.0
 */
class SendNotificationsCron extends AbstractTaskChainCron {

	const TASK_DATA_KEY_UNPROCESSED_NOTIFICATION_IDS = 'unprocessed_notification_ids';
	const TASK_DATA_KEY_CURRENT_NOTIFICATION_ID      = 'current_notification_id';
	const TASK_DATA_KEY_UNPROCESSED_RESERVATION_IDS  = 'unprocessed_reservation_ids';

	const OPTION_NAME_CRON_LAST_EXECUTION = 'mpa_send_notifications_cron_last_execution';

	const CRON_LAST_EXECUTION_STARTED_AT_TIME          = 'started_at_time';
	const CRON_LAST_EXECUTION_SENT_NOTIFICATIONS_COUNT = 'sent_notifications_count';


	public static function getCronActionHookName(): string {
		return 'mpa_send_notifications_cron';
	}


	protected static function getStartTaskData(): array {

		update_option(
			static::OPTION_NAME_CRON_LAST_EXECUTION,
			array(
				static::CRON_LAST_EXECUTION_STARTED_AT_TIME => time(),
				static::CRON_LAST_EXECUTION_SENT_NOTIFICATIONS_COUNT => 0,
			),
			false
		);

		// load ids to save memory and reduce task data for task chain
		$activeNotificationIds = mpapp()->repositories()->notification()->findAllActiveNotificationIds();

		if ( empty( $activeNotificationIds ) ) {
			return array();
		}

		return static::fillTaskDataWithReservations( array( static::TASK_DATA_KEY_UNPROCESSED_NOTIFICATION_IDS => $activeNotificationIds ) );

	}

	private static function fillTaskDataWithReservations( array $taskData ): array {

		if ( ! empty( $taskData[ static::TASK_DATA_KEY_CURRENT_NOTIFICATION_ID ] ) &&
			! empty( $taskData[ static::TASK_DATA_KEY_UNPROCESSED_RESERVATION_IDS ] ) ) {
			// task data has unprocessed reservation ids already
			return $taskData;
		}

		if ( empty( $taskData[ static::TASK_DATA_KEY_UNPROCESSED_NOTIFICATION_IDS ] ) ) {
			// nothing to process
			return array();
		}

		$notificationId = array_shift( $taskData[ static::TASK_DATA_KEY_UNPROCESSED_NOTIFICATION_IDS ] );

		$notification = mpapp()->repositories()->notification()->findById( $notificationId );

		if ( null === $notification ) {

			return static::fillTaskDataWithReservations( $taskData );
		}

		$reservations = mpapp()->getNotificationHandler()->findNotNotifiedReservations( $notification );

		if ( empty( $reservations ) ) {

			return static::fillTaskDataWithReservations( $taskData );
		}

		$taskData[ static::TASK_DATA_KEY_CURRENT_NOTIFICATION_ID ] = $notificationId;

		foreach ( $reservations as $reservation ) {

			$taskData[ static::TASK_DATA_KEY_UNPROCESSED_RESERVATION_IDS ][] = $reservation->getId();
		}

		return $taskData;
	}

	protected function processTask( array $taskData ): array {

		$cronLastExecutionData = get_option( static::OPTION_NAME_CRON_LAST_EXECUTION );

		$notification = mpapp()->repositories()->notification()->findById( $taskData[ static::TASK_DATA_KEY_CURRENT_NOTIFICATION_ID ] );

		if ( null === $notification ) {

			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log(
				'### WARNING: Cron ' . get_class( $this ) . ' could not find Notification by id = ' .
				$taskData[ static::TASK_DATA_KEY_CURRENT_NOTIFICATION_ID ]
			);

			unset( $taskData[ static::TASK_DATA_KEY_UNPROCESSED_RESERVATION_IDS ] );

			return static::fillTaskDataWithReservations( $taskData );
		}

		reset( $taskData[ static::TASK_DATA_KEY_UNPROCESSED_RESERVATION_IDS ] );
		$reservationId = current( $taskData[ static::TASK_DATA_KEY_UNPROCESSED_RESERVATION_IDS ] );

		try {

			$reservation = mpapp()->repositories()->reservation()->findById( $reservationId );

			if ( null !== $reservation ) {

				mpapp()->getNotificationHandler()->sendNotification( $notification, $reservation );

				$messagesCountPerReservation = count( $notification->getRecipientsContacts( $reservation ) );
				$cronLastExecutionData[ static::CRON_LAST_EXECUTION_SENT_NOTIFICATIONS_COUNT ] += $messagesCountPerReservation;

			} else {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log(
					'### Cron ERROR [ ' . get_class( $this ) . ' ] could not send notification [id = ' . $notification->getId() .
					' for reservation [id = ' . $reservationId . '] because reservation was not found.'
				);
			}
		} catch ( \Throwable $e ) {

			// uncomment for testing
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {

				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log(
					'### Cron ERROR: [' . get_class( $this ) . '] could not send notification [id = ' . $notification->getId() .
					'] for reservation [id = ' . $reservationId . '] because of: ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString()
				);
			}
		}

		// remove processed reservtion id from task data
		reset( $taskData[ static::TASK_DATA_KEY_UNPROCESSED_RESERVATION_IDS ] );
		$firstElementIndex = key( $taskData[ static::TASK_DATA_KEY_UNPROCESSED_RESERVATION_IDS ] );
		unset( $taskData[ static::TASK_DATA_KEY_UNPROCESSED_RESERVATION_IDS ][ $firstElementIndex ] );

		update_option( static::OPTION_NAME_CRON_LAST_EXECUTION, $cronLastExecutionData, false );

		return static::fillTaskDataWithReservations( $taskData );
	}
}
