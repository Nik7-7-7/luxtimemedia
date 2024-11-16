<?php

namespace MotoPress\Appointment\Handlers;

use MotoPress\Appointment\Entities\Booking;
use MotoPress\Appointment\PostTypes\Statuses\BookingStatuses;
use MotoPress\Appointment\Entities\Reservation;
use MotoPress\Appointment\PostTypes\ReservationPostType;
use MotoPress\Appointment\PostTypes\BookingPostType;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This handler uses https://github.com/googleapis/google-api-php-client/tree/v2.1.3
 * @since 1.10.0
 */
class GoogleCalendarSyncHandler {

	const MPA_EMPLOYEE_META_KEY_GOOGLE_CALENDAR_TOKEN       = 'mpa_google_calendar_token';
	const MPA_RESERVATION_META_KEY_GOOGLE_CALENDAR_EVENT_ID = 'mpa_google_calendar_event_id';

	/**
	 * @since 1.10.0
	 */
	public function __construct() {
		if ( empty( mpapp()->settings()->getGoogleCalendarClientId() ) ||
			empty( mpapp()->settings()->getGoogleCalendarClientSecret() ) ) {
			return;
		}

		add_action(
			'init',
			function() {

				if ( false !== strpos( $_SERVER['REQUEST_URI'], '/mpa-google-calendar-sync/' ) ) {
					$this->saveGoogleCalendarAccessToken();
				}
			}
		);

		add_action(
			'mpa_booking_confirmed',
			function( $mpaBooking ) {

				foreach ( $mpaBooking->getReservations() as $reservation ) {

					$this->addNewReservationToGoogleCalendar( $mpaBooking, $reservation );
				}
			},
			10,
			1
		);

		add_action(
			'mpa_booking_placed_by_user',
			function( $mpaBooking ) {

				$this->syncBookingToGoogleCalendar( $mpaBooking );
			},
			10,
			1
		);

		add_action(
			'mpa_booking_placed_by_admin',
			function( $mpaBooking ) {

				$this->syncBookingToGoogleCalendar( $mpaBooking );
			},
			10,
			1
		);

		add_action(
			'wp_trash_post',
			function( int $postId ) {

				$post = get_post( $postId );

				if ( BookingPostType::POST_TYPE == $post->post_type ) {

					$mpaBooking = mpapp()->repositories()->booking()->mapPostToEntity( $post );

					foreach ( $mpaBooking->getReservations() as $reservation ) {

						$this->deleteReservationFromGoogleCalendar( $reservation );
					}
				}
			},
			10,
			2
		);

		add_action(
			'mpa_booking_pending',
			function( $mpaBooking, $oldStatus, $isNewPost ) {

				if ( $isNewPost ) {
					return;
				}

				foreach ( $mpaBooking->getReservations() as $reservation ) {

					$this->deleteReservationFromGoogleCalendar( $reservation );
				}
			},
			10,
			3
		);

		add_action(
			'mpa_booking_cancelled',
			function( $mpaBooking ) {

				foreach ( $mpaBooking->getReservations() as $reservation ) {

					$this->deleteReservationFromGoogleCalendar( $reservation );
				}
			},
			10,
			1
		);

		add_action(
			'before_delete_post',
			function( $postId, $post ) {

				if ( ReservationPostType::POST_TYPE == $post->post_type ) {
					$reservation = mpapp()->repositories()->reservation()->mapPostToEntity( $post );
					$this->deleteReservationFromGoogleCalendar( $reservation );
				}
			},
			10,
			2
		);

		add_action(
			'mpa_reservations_updated',
			function( $mpaBooking ) {

				$this->syncBookingToGoogleCalendar( $mpaBooking );
			},
			10,
			1
		);
	}

	/**
	 * @since 1.10.0
	 */
	public static function isGoogleCalendarConnectedToEmployee( int $mpaEmployeeId ): bool {
		return ! empty( static::getStoredGoogleAPIToken( $mpaEmployeeId ) ) &&
			! static::getGoogleAPIClient( $mpaEmployeeId )->isAccessTokenExpired();
	}

	/**
	 * @since 1.10.0
	 */
	public static function disconnectGoogleCalendarFromEmployee( int $mpaEmployeeId ) {
		delete_post_meta(
			$mpaEmployeeId,
			static::MPA_EMPLOYEE_META_KEY_GOOGLE_CALENDAR_TOKEN
		);
	}

	/**
	 * @since 1.10.0
	 */
	private static function getStoredGoogleAPIToken( int $mpaEmployeeId ): array {
		$googleAPIToken = get_post_meta( $mpaEmployeeId, static::MPA_EMPLOYEE_META_KEY_GOOGLE_CALENDAR_TOKEN, true );
		return ! empty( $googleAPIToken ) ? $googleAPIToken : array();
	}

	/**
	 * @since 1.10.0
	 */
	public static function getGoogleCalendarConnectionURL( int $mpaEmployeeId ): string {
		$googleAPI = static::getGoogleAPIClient( $mpaEmployeeId );
		$authUrl   = $googleAPI->createAuthUrl();
		return $authUrl;
	}

	/**
	 * Doc about authorization https://developers.google.com/identity/protocols/oauth2/web-server
	 * @since 1.10.0
	 */
	private static function getGoogleAPIClient( int $mpaEmployeeId ) {
		$googleAPI = new \Google_Client();
		$googleAPI->setScopes( \Google_Service_Calendar::CALENDAR );
		$googleAPI->setAuthConfig(
			array(
				'client_id'     => mpapp()->settings()->getGoogleCalendarClientId(),
				'client_secret' => mpapp()->settings()->getGoogleCalendarClientSecret(),
				'redirect_uris' => array( get_site_url() . '/mpa-google-calendar-sync/' ),
			)
		);
		$googleAPI->setAccessType( 'offline' );
		$googleAPI->setPrompt( 'select_account consent' );
		// if we will need more parameters then create array, json_encode() it and base64UrlEncode() for safe url
		$googleAPI->setState( $mpaEmployeeId );

		$accessToken = static::getStoredGoogleAPIToken( $mpaEmployeeId );
		if ( ! empty( $accessToken ) ) {

			$googleAPI->setAccessToken( $accessToken );
		}

		$googleAPI->setTokenCallback(
			function( $cacheKey, $accessToken ) use ( $mpaEmployeeId ) {

				update_post_meta(
					$mpaEmployeeId,
					static::MPA_EMPLOYEE_META_KEY_GOOGLE_CALENDAR_TOKEN,
					$accessToken
				);
			}
		);

		// If there is no previous token or it's expired.
		if ( $googleAPI->isAccessTokenExpired() ) {
			//Refresh the token if possible, else fetch a new one.
			if ( $googleAPI->getRefreshToken() ) {

				$googleAPI->fetchAccessTokenWithRefreshToken( $googleAPI->getRefreshToken() );
			}
		}

		return $googleAPI;
	}

	/**
	 * @since 1.10.0
	 */
	private function saveGoogleCalendarAccessToken() {
		$mpaEmployeeId = empty( $_GET['state'] ) ? 0 : absint( $_GET['state'] );
		$mpaEmployee   = mpapp()->repositories()->employee()->findById( $mpaEmployeeId );

		$googleAPIAuthCode = sanitize_text_field( $_GET['code'] );

		if ( empty( $mpaEmployee ) || empty( $googleAPIAuthCode ) ) {

			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log(
				'Appointment Booking error: Could not get Google Calendar Access Token [ Employee id = ' . $mpaEmployeeId .
				', code = ' . $googleAPIAuthCode
			);
			return;
		}

		$googleAPI = static::getGoogleAPIClient( $mpaEmployeeId );

		// Exchange authorization code for an access token.
		$accessToken = $googleAPI->fetchAccessTokenWithAuthCode( $googleAPIAuthCode );
		$googleAPI->setAccessToken( $accessToken );

		// Check to see if there was an error.
		if ( array_key_exists( 'error', $accessToken ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'Appointment Booking error: Could not get Google Calendar Access Token - ' . $accessToken );
		}

		update_post_meta(
			$mpaEmployeeId,
			static::MPA_EMPLOYEE_META_KEY_GOOGLE_CALENDAR_TOKEN,
			$googleAPI->getAccessToken()
		);

		wp_safe_redirect( get_edit_post_link( $mpaEmployeeId, '' ) );
		exit;
	}

	/**
	 * Doc about Google calendar https://developers.google.com/calendar/api/guides/create-events#php
	 * @since 1.10.0
	 */
	private function addNewReservationToGoogleCalendar( Booking $mpaBooking, Reservation $mpaReservation ) {
		if ( ! static::isGoogleCalendarConnectedToEmployee( $mpaReservation->getEmployeeId() ) ) {
			return;
		}

		$googleAPI = static::getGoogleAPIClient( $mpaReservation->getEmployeeId() );
		$calendar  = new \Google_Service_Calendar( $googleAPI );

		try {

			$event = $calendar->events->insert(
				'primary',
				new \Google_Service_Calendar_Event( $this->collectGoogleCalendarEventData( $mpaBooking, $mpaReservation ) )
			);

			update_post_meta(
				$mpaReservation->getId(),
				static::MPA_RESERVATION_META_KEY_GOOGLE_CALENDAR_EVENT_ID,
				$event->getId()
			);
		} catch ( \Throwable $e ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log(
				'### Appointment Booking Error: ' . $e->getMessage() . PHP_EOL .
				$e->getTraceAsString()
			);
		}
	}

	/**
	 * @since 1.10.0
	 */
	private function collectGoogleCalendarEventData( Booking $mpaBooking, Reservation $mpaReservation ) {

		$timeZoneFromSettings = wp_timezone();
		$eventStart           = $mpaReservation->getServiceTime()->getStartTime();
		$eventStart           = new \DateTime( $eventStart->format( 'Y-m-d H:i:s' ), $timeZoneFromSettings );

		$eventEnd = $mpaReservation->getServiceTime()->getEndTime();
		$eventEnd = new \DateTime( $eventEnd->format( 'Y-m-d H:i:s' ), $timeZoneFromSettings );

		$serviceName = get_the_title( $mpaReservation->getServiceId() );

		$eventData = array(
			'summary'     => $serviceName . ' / ' .
				$mpaReservation->getService()->getQuantityLabel() . ': ' . $mpaReservation->getCapacity() . ' / ' .
				str_replace( 'https://', '', get_site_url( null, '', 'https' ) ),
			'location'    => get_the_title( $mpaReservation->getLocationId() ),
			'description' => esc_html__( 'Booking', 'motopress-appointment' ) . ' #' . $mpaBooking->getId() . ': ' . $serviceName . ', ' .
				$mpaBooking->getCustomerName() . ' ' . $mpaBooking->getCustomerPhone() . ' ' . $mpaBooking->getCustomerEmail(),
			'start'       => array(
				// format like 2016-07-04T17:53:30+02:00
				'dateTime' => $eventStart->format( \DateTime::RFC3339 ),
			),
			'end'         => array(
				// format like 2016-07-04T17:53:30+02:00
				'dateTime' => $eventEnd->format( \DateTime::RFC3339 ),
			),
		);

		return $eventData;
	}

	/**
	 * @since 1.10.0
	 */
	private function updateReservationInGoogleCalendar( Booking $mpaBooking, Reservation $mpaReservation ) {
		if ( ! static::isGoogleCalendarConnectedToEmployee( $mpaReservation->getEmployeeId() ) ) {
			return;
		}

		$googleAPI = static::getGoogleAPIClient( $mpaReservation->getEmployeeId() );
		$calendar  = new \Google_Service_Calendar( $googleAPI );

		$eventId = get_post_meta(
			$mpaReservation->getId(),
			static::MPA_RESERVATION_META_KEY_GOOGLE_CALENDAR_EVENT_ID,
			true
		);

		try {

			$event = $calendar->events->update(
				'primary',
				$eventId,
				new \Google_Service_Calendar_Event( $this->collectGoogleCalendarEventData( $mpaBooking, $mpaReservation ) )
			);

		} catch ( \Throwable $e ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log(
				'### Appointment Booking Error: ' . $e->getMessage() . PHP_EOL .
				$e->getTraceAsString()
			);
		}
	}

	/**
	 * @since 1.10.0
	 */
	private function deleteReservationFromGoogleCalendar( Reservation $mpaReservation ) {
		if ( ! static::isGoogleCalendarConnectedToEmployee( $mpaReservation->getEmployeeId() ) ) {
			return;
		}

		$googleAPI = static::getGoogleAPIClient( $mpaReservation->getEmployeeId() );
		$calendar  = new \Google_Service_Calendar( $googleAPI );

		$eventId = get_post_meta(
			$mpaReservation->getId(),
			static::MPA_RESERVATION_META_KEY_GOOGLE_CALENDAR_EVENT_ID,
			true
		);
		try {

			$result = $calendar->events->delete( 'primary', $eventId );

			if ( 204 == $result->getStatusCode() ) {

				update_post_meta(
					$mpaReservation->getId(),
					static::MPA_RESERVATION_META_KEY_GOOGLE_CALENDAR_EVENT_ID,
					null
				);
			}
		} catch ( \Throwable $e ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log(
				'### Appointment Booking Error: ' . $e->getMessage() . PHP_EOL .
				$e->getTraceAsString()
			);
		}
	}

	/**
	 * @since 1.10.0
	 */
	private function syncBookingToGoogleCalendar( Booking $mpaBooking ) {
		if ( BookingStatuses::STATUS_CANCELLED == $mpaBooking->getStatus() ||
			BookingStatuses::STATUS_PENDING == $mpaBooking->getStatus() ) {
			return;
		}

		foreach ( $mpaBooking->getReservations() as $reservation ) {

			$eventId = get_post_meta(
				$reservation->getId(),
				static::MPA_RESERVATION_META_KEY_GOOGLE_CALENDAR_EVENT_ID,
				true
			);

			if ( ! empty( $eventId ) ) {

				$this->updateReservationInGoogleCalendar( $mpaBooking, $reservation );

			} else {

				$this->addNewReservationToGoogleCalendar( $mpaBooking, $reservation );
			}
		}
	}
}
