<?php

namespace MotoPress\Appointment\Handlers;

use MotoPress\Appointment\Entities\Booking;
use MotoPress\Appointment\Entities\Notification;
use MotoPress\Appointment\Entities\Reservation;
use MotoPress\Appointment\Structures\TimePeriod;
use MotoPress\Appointment\Utils\DateTimeUtils;
use DateTime;
use \MotoPress\Appointment\Fields\Complex\TriggerPeriodField;
use MotoPress\Appointment\Entities\Payment;
use MotoPress\Appointment\PostTypes\Statuses\BookingStatuses;
use MotoPress\Appointment\PostTypes\Statuses\PaymentStatuses;
use MotoPress\Appointment\Crons\SendNotificationsCron;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class NotificationHandler {

	const META_KEY_NOTIFICATION_SENT = '_mpa_notification_sent';

	/**
	 * @var AbstractNotificationSender[] - [ sender_id => Sender instance ]
	 */
	private $allRegisteredNotificationSenders = array();

	/**
	 * @var array - [ notification_type => AbstractNotificationSender instance ]
	 */
	private $activeNotificationSenders = array();


	public function __construct() {

		add_action(
			'plugins_loaded',
			function() {

				$this->allRegisteredNotificationSenders = apply_filters(
					'mpa_registered_notification_senders',
					array(
						EmailNotificationSender::getSenderId() => new EmailNotificationSender(),
					)
				);

				$smsSenderId = mpapp()->settings()->getSMSNotificationSenderId();

				$this->activeNotificationSenders[ Notification::TYPE_ID_EMAIL ] = $this->allRegisteredNotificationSenders[ EmailNotificationSender::getSenderId() ];

				if ( ! empty( $smsSenderId ) && ! empty( $this->allRegisteredNotificationSenders[ $smsSenderId ] ) ) {

					$this->activeNotificationSenders[ Notification::TYPE_ID_SMS ] = $this->allRegisteredNotificationSenders[ $smsSenderId ];
				}
			},
			100
		);

		// Use a priority of 15 to send notifications after the default status mails
		add_action(
			'mpa_booking_placed_by_user',
			function( Booking $booking ) {
				SendNotificationsCron::schedule( true );
			},
			15,
			1
		);
		add_action(
			'mpa_booking_placed_by_admin',
			function( Booking $booking ) {
				SendNotificationsCron::schedule( true );
			},
			15,
			1
		);

		add_action(
			'mpa_booking_cancelled',
			function( Booking $booking ) {
				$this->sendNotificationsForCanceledBookingIfNeeded( $booking );
			},
			10,
			1
		);
		add_action(
			'mpa_payment_completed',
			function( Payment $payment ) {
				SendNotificationsCron::schedule( true );
			},
			10,
			1
		);

	}

	public function isSMSNotificationsEnabled(): bool {
		return ! empty( $this->activeNotificationSenders[ Notification::TYPE_ID_SMS ] );
	}

	public function getSMSNotificationSenders(): array {

		return array_filter(
			$this->allRegisteredNotificationSenders,
			function( $sender ) {
				return Notification::TYPE_ID_SMS === $sender->getSenderNotificationTypeId();
			}
		);
	}

	/**
	 * @throws \Exception when notification was not sent
	 */
	public function sendNotification( Notification $notification, Reservation $reservation, bool $isTestNotification = false ) {

		if ( empty( $this->activeNotificationSenders[ $notification->getType() ] ) ) {

			throw new \Exception( 'Could not find active notification sender for notification type: ' . $notification->getType() );
		}

		$notificationSender = $this->activeNotificationSenders[ $notification->getType() ];

		$notificationSender->sendNotification( $notification, $reservation, $isTestNotification );

		if ( ! $isTestNotification ) {
			// Mark reservation as "already sent"
			add_post_meta( $reservation->getId(), static::META_KEY_NOTIFICATION_SENT, $notification->getId() );
		}
	}


	private function sendNotificationsForCanceledBookingIfNeeded( Booking $booking ) {

		$activeNotifications = mpapp()->repositories()->notification()->findAllActive();

		// send notifications for canceled booking directly others
		foreach ( $activeNotifications as $notification ) {

			if ( Notification::TRIGGER_EVENT_ID_BOOKING_CANCELED !== $notification->getTriggerEventId() ) {

				continue;
			}

			$reservations = $this->findNotNotifiedCanceledBookingReservations( $notification, array( $booking->getId() ) );

			if ( ! empty( $reservations ) ) {

				foreach ( $reservations as $reservation ) {

					try {

						$this->sendNotification( $notification, $reservation );

					} catch ( \Throwable $e ) {

						// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
						error_log( $e->getMessage() . PHP_EOL . $e->getTraceAsString() );
					}
				}
			}
		}
	}

	/**
	 * @return Reservation[]
	 */
	public function findNotNotifiedReservations( Notification $notification, array $bookingIds = array() ): array {

		if ( ! $notification->isActive() ) {
			return array();
		}

		if ( Notification::TRIGGER_EVENT_ID_BOOKING_CANCELED === $notification->getTriggerEventId() ) {

			return $this->findNotNotifiedCanceledBookingReservations( $notification, $bookingIds );

		} elseif ( Notification::TRIGGER_EVENT_ID_BOOKING_PLACED === $notification->getTriggerEventId() ) {

			return $this->findNotNotifiedPlacedBookingReservations( $notification, $bookingIds );

		} elseif ( Notification::TRIGGER_EVENT_ID_PAYMENT_COMPLETED === $notification->getTriggerEventId() ) {

			return $this->findNotNotifiedPaidBookingReservations( $notification, $bookingIds );

		} elseif ( Notification::TRIGGER_EVENT_ID_BEFORE_AFTER_APPOINTMENT === $notification->getTriggerEventId() ) {

			return $this->findNotNotifiedBeforeAfterAppointmentReservations( $notification, $bookingIds );

		} else {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'ERROR: unknown notification trigger event id = ' . $notification->getTriggerEventId() );
			return array();
		}
	}

	/**
	 * @return Reservation[]
	 */
	private function findNotNotifiedCanceledBookingReservations( Notification $notification, array $bookingIds = array() ): array {

		// we do not search and send notifications for old canceled bookings for now
		// because we do not have cancellation date stored to be able to filter
		// too old canceled booking from recent
		if ( empty( $bookingIds ) ) {
			return array();
		}

		// find canceled booking ids among given
		$canceledBookingIds = mpapp()->repositories()->booking()->findAll(
			array(
				// for query optimization
				'no_found_rows' => true,
				'fields'        => 'ids',
				'post_status'   => BookingStatuses::STATUS_CANCELLED,
				'post__in'      => $bookingIds,
			)
		);

		if ( empty( $canceledBookingIds ) ) {
			return array();
		}

		$notSuitedReservationIds = mpapp()->repositories()->reservation()->findAll(
			array(
				// for query optimization
				'no_found_rows'   => true,
				'post_parent__in' => $canceledBookingIds,
				'fields'          => 'ids',
				'meta_query'      => array(
					array(
						'key'     => static::META_KEY_NOTIFICATION_SENT,
						'value'   => $notification->getId(),
						'compare' => '=',
					),
				),
			)
		);

		$notNotifiedReservations = mpapp()->repositories()->reservation()->findAll(
			array(
				// for query optimization
				'no_found_rows'   => true,
				'post_parent__in' => $canceledBookingIds,
				'post__not_in'    => $notSuitedReservationIds,
			)
		);

		return $notNotifiedReservations;
	}

	/**
	 * @return Reservation[]
	 */
	private function findNotNotifiedPlacedBookingReservations( Notification $notification, array $bookingIds = array() ): array {

		$fitsBookingIdsQuery = array(
			// for query optimization
			'no_found_rows' => true,
			'fields'        => 'ids',
			'post_status'   => array( BookingStatuses::STATUS_CONFIRMED, BookingStatuses::STATUS_PENDING ),
			'date_query'    => array(
				array(
					'after'     => ( new \DateTime( '-24 hours', wp_timezone() ) )->format( 'Y-m-d H:i:s' ),
					'before'    => ( new \DateTime( 'now', wp_timezone() ) )->format( 'Y-m-d H:i:s' ),
					'inclusive' => true,
				),
			),
		);

		if ( ! empty( $bookingIds ) ) {

			$fitsBookingIdsQuery['post__in'] = $bookingIds;
		}

		$fitsBookingIds = mpapp()->repositories()->booking()->findAll( $fitsBookingIdsQuery );

		if ( empty( $fitsBookingIds ) ) {
			return array();
		}

		$notSuitedReservationIds = mpapp()->repositories()->reservation()->findAll(
			array(
				// for query optimization
				'no_found_rows'   => true,
				'post_parent__in' => $fitsBookingIds,
				'fields'          => 'ids',
				'meta_query'      => array(
					array(
						'key'     => static::META_KEY_NOTIFICATION_SENT,
						'value'   => $notification->getId(),
						'compare' => '=',
					),
				),
			)
		);

		$notNotifiedReservations = mpapp()->repositories()->reservation()->findAll(
			array(
				// for query optimization
				'no_found_rows'   => true,
				'post_parent__in' => $fitsBookingIds,
				'post__not_in'    => $notSuitedReservationIds,
			)
		);

		return $notNotifiedReservations;
	}

	/**
	 * @return Reservation[]
	 */
	private function findNotNotifiedPaidBookingReservations( Notification $notification, array $bookingIds = array() ): array {

		$completedPaymentsQuery = array(
			// for query optimization
			'no_found_rows' => true,
			'fields'        => 'all',
			'post_status'   => PaymentStatuses::STATUS_COMPLETED,
			'date_query'    => array(
				array(
					'after'     => ( new \DateTime( '-24 hours', wp_timezone() ) )->format( 'Y-m-d H:i:s' ),
					'before'    => ( new \DateTime( 'now', wp_timezone() ) )->format( 'Y-m-d H:i:s' ),
					'inclusive' => true,
				),
			),
		);

		if ( ! empty( $bookingIds ) ) {

			$completedPaymentsQuery['post_parent__in'] = $bookingIds;
		}

		$completedPayments = mpapp()->repositories()->payment()->findAll( $completedPaymentsQuery );

		if ( empty( $completedPayments ) ) {
			return array();
		}

		$bookingIdsOfCompletedPayments = array();

		foreach ( $completedPayments as $completedPayment ) {

			$bookingIdsOfCompletedPayments[] = $completedPayment->getBookingId();
		}

		// find confirmed bookings among found with completed payments
		$confirmedBookingIds = mpapp()->repositories()->booking()->findAll(
			array(
				// for query optimization
				'no_found_rows' => true,
				'fields'        => 'ids',
				'post_status'   => BookingStatuses::STATUS_CONFIRMED,
				'post__in'      => $bookingIdsOfCompletedPayments,
			)
		);

		if ( empty( $confirmedBookingIds ) ) {
			return array();
		}

		$notSuitedReservationIds = mpapp()->repositories()->reservation()->findAll(
			array(
				// for query optimization
				'no_found_rows'   => true,
				'post_parent__in' => $confirmedBookingIds,
				'fields'          => 'ids',
				'meta_query'      => array(
					array(
						'key'     => static::META_KEY_NOTIFICATION_SENT,
						'value'   => $notification->getId(),
						'compare' => '=',
					),
				),
			)
		);

		$notNotifiedReservations = mpapp()->repositories()->reservation()->findAll(
			array(
				// for query optimization
				'no_found_rows'   => true,
				'post_parent__in' => $confirmedBookingIds,
				'post__not_in'    => $notSuitedReservationIds,
			)
		);

		return $notNotifiedReservations;
	}

	/**
	 * @return Reservation[]
	 */
	private function findNotNotifiedBeforeAfterAppointmentReservations( Notification $notification, array $bookingIds = array() ): array {

		if ( TriggerPeriodField::UNIT_DAY === $notification->getTriggerUnit() &&
			$notification->getTriggerTime() > DateTimeUtils::timeNow( 'internal' )
		) {
			// Skip notification because sending time has not yet come
			return array();
		}

		$timePeriodInTriggerUnits  = $notification->getTriggerPeriod();
		$reservationsFoundByPeriod = array();

		if ( TriggerPeriodField::UNIT_DAY === $notification->getTriggerUnit() ) {

			if ( 'before' === $notification->getTriggerOperator() ) {
				$reservationDate = new DateTime( "+{$timePeriodInTriggerUnits} days", wp_timezone() );
			} else {
				$reservationDate = new DateTime( "-{$timePeriodInTriggerUnits} days", wp_timezone() );
			}

			$notSuitedReservationsQuery = array(
				// for query optimization
				'no_found_rows' => true,
				'fields'        => 'ids',
				'meta_query'    => array(
					'relation' => 'AND',
					array(
						'key'     => '_mpa_date',
						'value'   => mpa_format_date( $reservationDate, 'internal' ),
						'compare' => '=',
					),
					array(
						'key'     => static::META_KEY_NOTIFICATION_SENT,
						'value'   => $notification->getId(),
						'compare' => '=',
					),
				),
			);

			if ( ! empty( $bookingIds ) ) {
				$notSuitedReservationsQuery['post_parent__in'] = $bookingIds;
			}

			$notSuitedReservationIds = mpapp()->repositories()->reservation()->findAll( $notSuitedReservationsQuery );

			$reservationsQuery = array(
				// for query optimization
				'no_found_rows' => true,
				'meta_query'    => array(
					array(
						'key'     => '_mpa_date',
						'value'   => mpa_format_date( $reservationDate, 'internal' ),
						'compare' => '=',
					),
				),
			);

			if ( ! empty( $bookingIds ) ) {
				$reservationsQuery['post_parent__in'] = $bookingIds;
			}

			if ( ! empty( $notSuitedReservationIds ) ) {
				$reservationsQuery['post__not_in'] = $notSuitedReservationIds;
			}

			$reservationsFoundByPeriod = mpapp()->repositories()->reservation()->findAll( $reservationsQuery );

		} else {

			if ( 'before' === $notification->getTriggerOperator() ) {

				$reservationDateFrom = new DateTime( '+' . ( $timePeriodInTriggerUnits - 1 ) . ' hours', wp_timezone() );
				$reservationDateTo   = new DateTime( "+{$timePeriodInTriggerUnits} hours", wp_timezone() );

			} else {

				$reservationDateFrom = new DateTime( '-' . ( $timePeriodInTriggerUnits + 1 ) . ' hours', wp_timezone() );
				$reservationDateTo   = new DateTime( "-{$timePeriodInTriggerUnits} hours", wp_timezone() );
			}

			$notSuitedReservationsQuery = array(
				// for query optimization
				'no_found_rows' => true,
				'fields'        => 'ids',
				'meta_query'    => array(
					'relation' => 'AND',
					array(
						'key'     => '_mpa_date',
						'value'   => mpa_format_date( $reservationDateFrom, 'internal' ),
						'compare' => '>=',
					),
					array(
						'key'     => '_mpa_date',
						'value'   => mpa_format_date( $reservationDateTo, 'internal' ),
						'compare' => '<=',
					),
					array(
						'key'     => static::META_KEY_NOTIFICATION_SENT,
						'value'   => $notification->getId(),
						'compare' => '=',
					),
				),
			);

			if ( ! empty( $bookingIds ) ) {
				$notSuitedReservationsQuery['post_parent__in'] = $bookingIds;
			}

			$notSuitedReservationIds = mpapp()->repositories()->reservation()->findAll( $notSuitedReservationsQuery );

			$reservationsQuery = array(
				// for query optimization
				'no_found_rows' => true,
				'meta_query'    => array(
					'relation' => 'AND',
					array(
						'key'     => '_mpa_date',
						'value'   => mpa_format_date( $reservationDateFrom, 'internal' ),
						'compare' => '>=',
					),
					array(
						'key'     => '_mpa_date',
						'value'   => mpa_format_date( $reservationDateTo, 'internal' ),
						'compare' => '<=',
					),
				),
			);

			if ( ! empty( $bookingIds ) ) {
				$reservationsQuery['post_parent__in'] = $bookingIds;
			}

			if ( ! empty( $notSuitedReservationIds ) ) {
				$reservationsQuery['post__not_in'] = $notSuitedReservationIds;
			}

			$reservationsFoundByPeriod = mpapp()->repositories()->reservation()->findAll( $reservationsQuery );

			if ( TriggerPeriodField::UNIT_DAY === $notification->getTriggerUnit() ) {

				// Filter by time
				$allowedTimeFrame = new TimePeriod( $reservationDateFrom, $reservationDateTo );

				$reservationsFoundByPeriod = array_filter(
					$reservationsFoundByPeriod,
					function ( Reservation $reservation ) use ( $allowedTimeFrame ) {
						return $reservation->getServiceTime()->intersectsWith( $allowedTimeFrame, 'time' );
					}
				);
			}
		}

		if ( empty( $reservationsFoundByPeriod ) ) {
			return array();
		}

		// find reservations from confirmed bookings only
		$bookingIdsFromFoundReservations = array_map(
			function ( Reservation $reservation ) {
				return $reservation->getBookingId();
			},
			$reservationsFoundByPeriod
		);

		$confirmedBookingIds = mpapp()->repositories()->booking()->findAll(
			array(
				// for query optimization
				'no_found_rows' => true,
				'fields'        => 'ids',
				'post__in'      => array_unique( $bookingIdsFromFoundReservations ),
				'post_status'   => BookingStatuses::STATUS_CONFIRMED,
			)
		);

		if ( empty( $confirmedBookingIds ) ) {
			return array();
		}

		$notNotifiedReservations = array_filter(
			$reservationsFoundByPeriod,
			function ( Reservation $reservation ) use ( $confirmedBookingIds ) {
                // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				return in_array( $reservation->getBookingId(), $confirmedBookingIds );
			}
		);

		return $notNotifiedReservations;
	}
}
