<?php

declare(strict_types=1);

namespace MotoPress\Appointment\AdminPages\Manage;

use MotoPress\Appointment\Entities\Notification;
use MotoPress\Appointment\Fields\Complex\TriggerPeriodField;
use MotoPress\Appointment\PostTypes\NotificationPostType;
use MotoPress\Appointment\Crons\SendNotificationsCron;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.13.0
 */
class ManageNotificationsPage extends ManagePostsPage {

	protected function addActions() {

		parent::addActions();

		// customize notifications query to sort by type
		add_action(
			'pre_get_posts',
			function( \WP_Query $query ) {

				if ( ! $query->is_main_query() ) {
					return;
				}

				if ( 'type' === $query->get( 'orderby' ) ) {

					$query->set( 'meta_key', mpa_prefix( 'type', 'metabox' ) );
					$query->set( 'orderby', 'meta_value' );
				}
			},
			10,
			1
		);

		// remove Draft postfix in title column
		add_filter(
			'display_post_states',
			function( $post_states, $post ) {

				if ( is_admin() &&
					'draft' === $post->post_status &&
					NotificationPostType::POST_TYPE === $post->post_type ) {

					return array();
				}
				return $post_states;
			},
			99999,
			2
		);
	}

	public function getDescription(): string {

		ob_start();

		$gmtOffset  = (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
		$dateFormat = mpapp()->settings()->getTimeFormat() . ', ' . mpa_date_format(); // 3:08 pm, July 21, 2022

		$scheduledAt       = SendNotificationsCron::getNextScheduledTime();
		$nextExecutionTime = $scheduledAt ? date_i18n( $dateFormat, $scheduledAt + $gmtOffset ) : '';

		$lastExecution = get_option( SendNotificationsCron::OPTION_NAME_CRON_LAST_EXECUTION, false );

		// 1. Display last execution info
		if ( SendNotificationsCron::isStarted() ) {

			echo '<p class="mpa-posts-additional-info">';
				esc_html_e( 'Last execution — just now. Sending notifications...', 'motopress-appointment' );
			echo '</p>';

		} elseif ( ! empty( $lastExecution ) ) {

			$lastExecutionTime = date_i18n( $dateFormat, $lastExecution[ SendNotificationsCron::CRON_LAST_EXECUTION_STARTED_AT_TIME ] + $gmtOffset );
			$notificationsSent = $lastExecution[ SendNotificationsCron::CRON_LAST_EXECUTION_SENT_NOTIFICATIONS_COUNT ];

			echo '<p class="mpa-posts-additional-info">';

			if ( ! $notificationsSent ) {

				printf(
					// translators: %s: Last execution time, like "3:08 pm, July 21, 2022".
					esc_html__( 'Last execution was at %s. No new notifications were sent.', 'motopress-appointment' ),
					esc_html( $lastExecutionTime )
				);

			} else {

				// Translators: %d: Number of notifications.
				$sentNotificationsAmountText = sprintf( _n( '%d notification', '%d notifications', $notificationsSent, 'motopress-appointment' ), $notificationsSent );

				printf(
					// Translators: 1: Last execution time, like "3:08 pm, July 21, 2022", 2: Number of notifications
					esc_html__( 'Last execution was at %1$s. %2$s were sent.', 'motopress-appointment' ),
					esc_html( $lastExecutionTime ),
					esc_html( $sentNotificationsAmountText )
				);
			}

			echo '</p>';
		}

		// 2. Display next execution time
		echo '<p class="mpa-posts-additional-info">';

		if ( ! empty( $nextExecutionTime ) ) {

			// Translators: %s: Next execution time, like "3:08 pm, July 21, 2022".
			printf( esc_html__( 'Next execution is scheduled for — %s.', 'motopress-appointment' ), esc_html( $nextExecutionTime ) );

		} else {

			esc_html_e( 'Failed to schedule next execution in your WordPress installation.', 'motopress-appointment' );
		}

		echo '</p>';

		// 3. Add cront task notice. We don't really need the "action" argument
		// in the URL, but just mark our "requests".
		// https://en.wikipedia.org/wiki/Cron
		$startSendNotificationCronUrl = add_query_arg(
			'action',
			SendNotificationsCron::getCronActionHookName(),
			home_url( 'wp-cron.php' )
		);

		echo '<p class="mpa-posts-additional-info">';

			printf(
				// Translators: %s: Cron task, like "*/15 * * * * ...".
				esc_html__( 'You can set up a real cron task in your server admin panel: %s', 'motopress-appointment' ),
				'<code>*/15 * * * * ' . esc_url( $startSendNotificationCronUrl ) . '</code>'
			);

		echo '</p>';

		return ob_get_clean();
	}

	/**
	 * @return array
	 */
	protected function customColumns() {

		return array(
			'type'       => __( 'Type', 'motopress-appointment' ),
			'status'     => __( 'Status', 'motopress-appointment' ),
			'recipients' => __( 'Recipients', 'motopress-appointment' ),
			'trigger'    => __( 'Condition', 'motopress-appointment' ),
		);
	}

	protected function customSortableColumns() {
		return array(
			'type'   => 'type',
			'status' => 'status',
		);
	}

	/**
	 * @param string $columnName
	 * @param Notification $entity
	 */
	protected function displayValue( $columnName, $entity ) {

		switch ( $columnName ) {

			case 'type':
				$notificationTypes = mpapp()->settings()->getAllNotificationTypes();

				if ( ! empty( $notificationTypes[ $entity->getType() ] ) ) {

					echo esc_html( $notificationTypes[ $entity->getType() ] );

				} else {
					echo esc_html( $entity->getType() );
				}
				break;

			case 'status':
				echo esc_html( $entity->getManualStatusLabel() );

				if ( $entity->isActive() && Notification::TYPE_ID_SMS === $entity->getType() &&
					! mpapp()->getNotificationHandler()->isSMSNotificationsEnabled() ) {

					echo '<p><strong>' . esc_html(
						__( 'The message will not be sent! Please set the SMS provider in settings.', 'motopress-appointment' )
					) . '</strong></p>';
				}
				break;

			case 'recipients':
				$recipients = $entity->getRecipients();
				$receivers  = array();

				if ( in_array( 'admin', $recipients, true ) ) {
					$receivers[] = esc_html__( 'Admin', 'motopress-appointment' );
				}

				if ( in_array( 'employee', $recipients, true ) ) {
					$receivers[] = esc_html__( 'Employee', 'motopress-appointment' );
				}

				if ( in_array( 'customer', $recipients, true ) ) {
					$receivers[] = esc_html__( 'Customer', 'motopress-appointment' );
				}

				if ( in_array( 'custom', $recipients, true ) ) {

					if ( Notification::TYPE_ID_EMAIL === $entity->getType() ) {

						$receivers = array_merge( $receivers, $entity->getCustomEmails() );

					} elseif ( Notification::TYPE_ID_SMS === $entity->getType() ) {

						$receivers = array_merge( $receivers, $entity->getCustomPhones() );
					}
				}

				if ( ! empty( $receivers ) ) {

					echo esc_html( implode( ', ', $receivers ) );

				} else {

					echo esc_html( mpa_tmpl_placeholder() );
				}

				break;

			case 'trigger':
				if ( Notification::TRIGGER_EVENT_ID_BOOKING_CANCELED === $entity->getTriggerEventId() ) {

					esc_html_e( 'Booking canceled', 'motopress-appointment' );

				} elseif ( Notification::TRIGGER_EVENT_ID_BOOKING_PLACED === $entity->getTriggerEventId() ) {

					esc_html_e( 'Booking placed', 'motopress-appointment' );

				} elseif ( Notification::TRIGGER_EVENT_ID_PAYMENT_COMPLETED === $entity->getTriggerEventId() ) {

					esc_html_e( 'Payment completed', 'motopress-appointment' );

				} elseif ( Notification::TRIGGER_EVENT_ID_BEFORE_AFTER_APPOINTMENT === $entity->getTriggerEventId() ) {

					echo esc_html(
						TriggerPeriodField::convertTriggerToString(
							$entity->getTrigger(),
							$entity->getTriggerTime()
						)
					);
				}
				break;
		}
	}
}
