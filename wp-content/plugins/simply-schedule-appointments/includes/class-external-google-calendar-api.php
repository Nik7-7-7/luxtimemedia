<?php
/**
 * Simply Schedule Appointments External Google Calendar Api.
 *
 * @since   4.1.2
 * @package Simply_Schedule_Appointments
 */

/**
 * Simply Schedule Appointments External Google Calendar Api.
 *
 * @since 4.1.2
 */
class SSA_External_Google_Calendar_Api extends SSA_External_Calendar_Api {
	const SERVICE = 'google';
	protected $api;
	protected $api_service;
	protected $staff_id;
	
	public static function create_for_staff_id( $staff_id ) {
		$instance = new self;
		$instance->staff_id = $staff_id;

		return $instance;
	}
	
	public function get_api() {
		// not used
		// just here to satisfy the abstract class
	}

	public function get_api_service() {
		if ( null !== $this->api_service ) {
			return $this->api_service;
		}
		$this->api_service = ssa()->google_calendar_client->service_init($this->staff_id);
		return $this->api_service;
	}
	
	public function api_get_identity() {
		// not used
		// just here to satisfy the abstract class
	}

	/**
	 * Get list of Google Calendars.
	 *
	 * @return array
	 */
	public function get_calendar_list() {
		$result = array();
		try {
			$calendarList = $this->get_api_service()->get_calendar_list();
			foreach ( $calendarList as $calendarListEntry ) {
				$result[ $calendarListEntry->id ] = array(
					'primary' => isset( $calendarListEntry->primary ) ? $calendarListEntry->primary : false,
					'summary' => isset($calendarListEntry->summary) ? $calendarListEntry->summary : '',
					'description' => isset($calendarListEntry->description) ? $calendarListEntry->description : '',
					'kind' => isset($calendarListEntry->kind) ? $calendarListEntry->kind : '',
					'location' => isset($calendarListEntry->location) ? $calendarListEntry->location : '',
					'role' => isset($calendarListEntry->accessRole) ? $calendarListEntry->accessRole : '',
					'background_color' => isset($calendarListEntry->backgroundColor) ? $calendarListEntry->backgroundColor : '',
					'foreground_color' => isset($calendarListEntry->foregroundColor) ? $calendarListEntry->foregroundColor : '',
					'color_id' => isset($calendarListEntry->colorId) ? $calendarListEntry->colorId : '',
					'default_reminders' => isset($calendarListEntry->defaultReminders) ? $calendarListEntry->defaultReminders : '',
					'time_zone' => isset($calendarListEntry->timeZone) ? $calendarListEntry->timeZone : '',
				);
			}
		} catch ( \Exception $e ) {
			if ( class_exists( 'Session' ) ) {
				Session::set( 'staff_google_auth_error', json_encode( $e->getMessage() ) );
			} else {
				throw new Exception( 'staff_google_auth_error' . $e->getMessage(), '500' );
			}
		}
		return $result;
	}


	public function pull_availability_calendar( $calendar_id, $args=array() ) {
		$args = shortcode_atts( array(
			'start_date' => new DateTime(),
			'end_date' => '',
			'type' => '',
			'subtype' => '',
			'staff_id' => 0,
		), $args );

		try {
			$calendar = $this->get_api_service()->get_calendar_from_calendar_list( $calendar_id );
		} catch( Exception $e ) {
			ssa_debug_log( $e->getMessage() );
		}

		// get all events from calendar, without timeMin filter (the end of the event can be later then the start of searched time period)
		$result = array();

		try {
			$calendar_access = isset($calendar->accessRole) ? $calendar->accessRole : '';
			$limit_events    = 500;

			$timeMin = $args['start_date']->format( \DateTime::RFC3339 );

			$events = $this->get_api_service()->get_events_from_calendar( $calendar_id, array(
				'singleEvents' => true,
				'orderBy'      => 'startTime',
				'timeMin'      => $timeMin,
				'maxResults'   => $limit_events,
			) );

			foreach ( $events as $event ) {
				// Skip events created by SSA in non freeBusyReader calendar.
				if ( $calendar_access != 'freeBusyReader' ) {
					$ext_properties = ! empty( $event->extendedProperties ) ? $event->extendedProperties : null;
					if ( $ext_properties !== null ) {
						if ( ! empty( $ext_properties->private->ssa_home_id ) && $ext_properties->private->ssa_home_id == SSA_Utils::get_home_id() ) {
							continue; // If this event comes from this site, we don't need to load it from gcal, we can use the local db copy in wp_appointments table instead
						}

						if ( ! empty( $ext_properties->shared->ssa_home_id ) && $ext_properties->shared->ssa_home_id == SSA_Utils::get_home_id() ) {
							continue; // If this event comes from this site, we don't need to load it from gcal, we can use the local db copy in wp_appointments table instead
						}
					}
				}
				$event_transparency = ( empty( $event->transparency ) || $event->transparency === 'opaque' ) ? 'opaque' : 'transparent';

				// if event was declined by the current staff/admin ($calendar->id), consider it transparent
				if ( !empty( $event->attendees ) ) {
					foreach ( $event->attendees as $attendee ) {
						if ( $attendee->email == $calendar->id && $attendee->responseStatus == 'declined' ) {
							$event_transparency = 'transparent';
						}
					}
				}
				
				// Get start/end dates of event and transform them into WP timezone (Google doesn't transform whole day events into our timezone).
				$event_start = $event->start;
				$event_end   = $event->end;

				if ( empty( $event_start->dateTime ) ) {
					// All day event.
					$event_start_date = new \DateTime( $event_start->date, new \DateTimeZone( 'UTC' ) );
					$event_end_date = new \DateTime( $event_end->date, new \DateTimeZone( 'UTC' ) );
					$is_all_day = 1;
				} else {
					// Regular event.
					$event_start_date = new \DateTime( $event_start->dateTime );
					$event_end_date = new \DateTime( $event_end->dateTime );
					$is_all_day = 0;
				}

				// Convert to WP time zone.
				$event_start_date = date_timestamp_set( date_create( 'UTC' ), $event_start_date->getTimestamp() );
				$event_end_date   = date_timestamp_set( date_create( 'UTC' ), $event_end_date->getTimestamp() );

				$result[] = array(
					'type' => $args['type'],
					'subtype' => $args['subtype'],
					'staff_id' => $args['staff_id'],
					'service' => self::SERVICE,
					'calendar_id' => $calendar_id,
					'calendar_id_hash' => ssa_int_hash( $calendar_id ),
					'ical_uid' => isset( $event->iCalUID ) ? $event->iCalUID : '',
					'event_id' => isset( $event->id ) ? $event->id : '',
					'status' => isset( $event->status ) ? $event->status : '',
					'start_date' => $event_start_date->format( 'Y-m-d H:i:s' ),
					'end_date' => $event_end_date->format( 'Y-m-d H:i:s' ),
					'is_all_day' => $is_all_day,
					'transparency' => $event_transparency,
					'is_available' => ( $event_transparency === 'transparent' ) ? 1 : 0,
				);
			}
			return $result;
		} catch ( \Exception $e ) {
			ssa_debug_log( $e->getMessage() );
		}

		return array();

	}

	// TODO:
	public function push_appointment( SSA_Appointment_Object $appointment ) {
		die( 'TODO' ); // phpcs:ignore
	}
	public function pull_appointment( SSA_Appointment_Object $appointment ) {
		die( 'TODO' ); // phpcs:ignore
	}

}
