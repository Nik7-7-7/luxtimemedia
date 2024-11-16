<?php
/**
 * Simply Schedule Appointments Google Calendar.
 *
 * @since   0.7.0
 * @package Simply_Schedule_Appointments
 */
use League\Period\Period;

/**
 * Simply Schedule Appointments Google Calendar.
 *
 * @since 0.6.0
 */
class SSA_Google_Calendar {
	/**
	 * Parent plugin class.
	 *
	 * @since 0.6.0
	 *
	 * @var   Simply_Schedule_Appointments
	 */
	

	protected $plugin = null;

	public $admin;

	public $api_mode = 'none';

	public $quotaUser = '';

	private $access_code;

	private $description = '';

	private $summary = '';

	public $worker_id = false;

	public $ssa_status_to_gcal_status_map = array(
		'booked' => 'confirmed',
		'canceled' => 'cancelled',
	);

	/**
	 * Constructor.
	 *
	 * @since  0.6.0
	 *
	 * @param  Simply_Schedule_Appointments $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();
		
		define( 'SSA_QUICK_CONNECT_GCAL_AUTH_ENDPOINT', 'https://ssa-quick-connect.com/gcal/store-auth-record' );
		define( 'SSA_QUICK_CONNECT_GCAL_SERVE_ENDPOINT', 'https://ssa-quick-connect.com/gcal/serve-access-tokens' );
		define( 'SSA_QUICK_CONNECT_GCAL_CLIENT_ID', '675097257088-om1lp3dpet01c0202sup7lce89ob9bnc.apps.googleusercontent.com' );
		// $settings = $this->plugin->google_calendar_settings->get();

		// if ( isset( $settings['gcal_api_mode'] ) ) {
		// 	$this->api_mode = $settings['gcal_api_mode'];
		// }

		// $this->setup_cron();

		// if ( isset( $_GET['gcal-sync-now'] ) && current_user_can( 'ssa_manage_site_settings'  ) ) {
		// 	$this->maybe_sync();
		// }
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  0.6.0
	 */
	public function hooks() {
		add_action( 'wp', array( $this, 'catch_oauth_callback' ), 1 );
		add_action( 'wp', array( $this, 'catch_quick_connect_auth_callback' ), 1 );
		add_action( 'ssa/appointment/after_update', array( $this, 'sync_appointment_to_calendar' ), 100, 3 );

		add_action( 'ssa/appointment/after_insert', array( $this, 'sync_appointment_to_calendar' ), 100, 1 );
		add_action( 'ssa/async/google_calendar_sync', array( $this, 'sync_appointment_to_calendar' ), 100, 1 );
		add_action( 'ssa/appointment/after_update', array( $this, 'sync_calendar_conference_to_appointment' ), 100, 1 );
		add_filter( 'ssa/get_blocked_periods/blocked_periods', array( $this, 'filter_blocked_periods' ), 10, 3 );

		add_action('ssa_refresh_google_calendar', array( $this, 'async_refresh_external_events' ), 10, 2 );

		add_action( 'admin_init', array( $this, 'sync_with_google_calendar' ) );

		// Action Scheduler Scheduling
		add_action( 'init', array( $this, 'schedule_async_actions_for_missing_events_ids' ) );

		add_action( 'ssa/google_calendar/resync_appointments_with_missing_event_id', array( $this, 'resync_appointments_with_missing_event_id' ) );
		
		add_action( 'ssa/settings/developer/updated', array( $this, 'maybe_invalidate_tokens' ), 10, 2 );
		add_action( 'ssa/settings/google_calendar/updated', array( $this, 'maybe_invalidate_tokens' ), 10, 2 );

		add_action( 'ssa/settings/google_calendar/updated', array( $this, 'resync_appointments_after_reconnect' ), 100, 2 );
	}


	/**
	 * Checks if Google Calendar has been just reconnected and resync all appointments
	 */
	public function resync_appointments_after_reconnect( $new_settings = array(), $old_settings = array()  ) {		
		if( ! empty( $old_settings["access_token"] ) ) return; // Access token exists already; GCal is connected
		
		if( empty( $new_settings["access_token"] ) ) return; // No access token GCal not yet connected

		if( $new_settings["access_token"] === $old_settings["access_token"] )  return; // Just in case

		$this->sync_upcoming_appointments_missing_gcal_events();

	}
	
	/**
	 * checks if admin switched Google Calendar Authorization mode, and removes all tokens
	 */
	public function maybe_invalidate_tokens( $new_settings = array(), $old_settings = array()  ) {
		
		if(empty($new_settings) || empty($old_settings)){
			return;
		}
		
		// will remove tokens if any tokens exist
		if( ( $old_settings["quick_connect_gcal_mode"] !== $new_settings["quick_connect_gcal_mode"] ) || ( isset( $old_settings["quick_connect_gcal_mode"] ) && $old_settings["quick_connect_gcal_mode"] !== $new_settings["quick_connect_gcal_mode"] ) ){
			
			$google_calendar_settings = $this->plugin->google_calendar_settings->get();
			
			// only inform team members if google calendar is enabled and is connected - meaning they have authorized and will need to re-authorize
			if( ! empty( $google_calendar_settings['access_token'] ) ) {
				// inform admins and team members by adding error notice
				$this->plugin->error_notices->add_error_notice( 'quick_connect_gcal_auth_mode_changed' );
			}
			
			$this->invalidate_all_access_tokens();
		}
		
	}
	
	/**
	 * invalidate all google calendar access_tokens
	 */
	public function invalidate_all_access_tokens() {
		// causing an infinite loop
		
		// invalidate main calendar availability cache and access_token
		$this->plugin->availability_external_model->bulk_delete( array(
			'service' => 'google',
		) );
		$google_calendar_settings = $this->plugin->google_calendar_settings->get();
		$google_calendar_settings['access_token'] = '';
		$this->plugin->google_calendar_settings->update( $google_calendar_settings );
		
		// get all staff
		$staff = $this->plugin->staff_model->query(array());
		// loop through staff
		foreach( $staff as $staff_member ) {
			
			$this->plugin->availability_external_model->bulk_delete( array(
				'service' => 'google',
				'staff_id' => $staff_member['id'],
			) );
			$this->plugin->staff_model->update( $staff_member['id'], array(
				'google' =>  '',
				'google_access_token' =>  ''
				) );
		}
	}
	 
	/**
	 * Scheduling resync_appointments_with_missing_event_id
	 *
	 * @return void
	 */
	public function schedule_async_actions_for_missing_events_ids () {

		// Check if class and fucntions exist to avoid fatal error
		if ( ! class_exists( 'ActionScheduler' ) ) {
			return;
		}

		if ( ! function_exists( 'as_has_scheduled_action' ) ) {
			return;
		}

		if ( ! function_exists( 'as_schedule_recurring_action' ) ) {
			return;
		}

		if ( ! function_exists( 'as_unschedule_action' ) ) {
			return;
		}

		// If Google Calendar is deactivated; check and unschedule any ongoing action then return
		if ( ! $this->plugin->settings_installed->is_activated( 'google_calendar' ) ){

			try {
				if ( true === as_has_scheduled_action( 'ssa/google_calendar/resync_appointments_with_missing_event_id' ) ) {
					as_unschedule_action( 'ssa/google_calendar/resync_appointments_with_missing_event_id', array(), '' );
				}

			} catch ( Exception $e ) {
				// Do nothing
			}
			return;
		}

		// If we reached here; check & schedule action
		try {

			if ( false === as_has_scheduled_action( 'ssa/google_calendar/resync_appointments_with_missing_event_id' ) ) {
				as_schedule_recurring_action( strtotime( 'now' ), HOUR_IN_SECONDS * 12, 'ssa/google_calendar/resync_appointments_with_missing_event_id' );
			}
		} catch ( Exception $e ) {
			// Do nothing
		}
	}


	public function resync_appointments_with_missing_event_id(){
		// if google calendar not connected just return
		if ( !$this->plugin->settings_installed->is_activated( 'google_calendar' ) ){
			return;
		}
		// Get list of booked + upcoming appointments.
		$appointments = $this->plugin->appointment_model->query(
			array(
				'status'         => array( 'booked' ),
				'start_date_min' => gmdate( 'Y-m-d H:i:s' ),
				'number'         => -1,
				'google_calendar_event_id' =>''
				)
			);
			
			if ( empty( $appointments ) ) {
				return;
			}
			
			// only sync appointments with empty group_id or matching group_id and id
			$appointments_to_sync = array_filter($appointments, function ( $appointment ){
				if ( ! empty( $appointment['group_id'] ) && $appointment['group_id'] !== $appointment['id']){
					return false;
				}
				
				return true;
			});

		$this->bulk_schedule_google_calendar_sync( $appointments_to_sync );
	}

	/**
	 * Checks which appointments don't have a Google Calendar Event and schedules an async action to create it for the respective appointment.
	 *
	 * @since 4.9.1
	 *
	 * @param array $appointments and array of appointment objects.
	 * @return void
	 */
	public function bulk_schedule_google_calendar_sync( $appointments = array() ) {
		if ( empty( $appointments ) ) {
			return;
		}
		$time_interval_in_seconds = 15;

		foreach ( $appointments as $index => $appointment ) {
			// adds 15 seconds to the current time interval.
			$interval  = $index * $time_interval_in_seconds;
			$timestamp = time() + $interval;

			$this->schedule_google_calendar_sync( $timestamp, $appointment['id'] );
		}
	}

	/**
	 * Schedules an async action to create a Google Calendar Event for the given appointment.
	 *
	 * @since 4.9.1
	 *
	 * @param int $timestamp timestamp to schedule the action.
	 * @param int $appointment_id appointment id.
	 * @return void
	 */
	public function schedule_google_calendar_sync( $timestamp, $appointment_id ) {
		if ( ! class_exists( 'ActionScheduler' ) ) {
			return;
		}

		if ( ! function_exists( 'as_schedule_single_action' ) ) {
			return;
		}

		try {
			as_schedule_single_action( $timestamp, 'ssa/async/google_calendar_sync', array( $appointment_id ), 'ssa_google_calendar_sync' );
		} catch ( Exception $e ) {
			return;
		}
	}

	public function async_refresh_external_events( $payload, $async_action ) {
		if ( empty( $payload['appointment_type_id'] ) ) {
			ssa_complete_action( $async_action['id'], 'appointment_type_id missing' );
			return;
		}
		$start = microtime(true);
		$did_refresh = $this->maybe_refresh_external_events_for_appointment_type( $payload['appointment_type_id'] );

		$appointment_type = new SSA_Appointment_Type_Object( $payload['appointment_type_id'] );
		if ( $this->plugin->settings_installed->is_enabled( 'staff' ) ) {
			$staff_refreshed_count = 0;
			$staff_members = $this->plugin->staff_appointment_type_model->get_staff_for_appointment_type( $appointment_type );
			if ( ! empty( $staff_members ) ) {		
				foreach ($staff_members as $staff) {
					$did_refresh_staff = $staff->pull_external_calendar_availability();
					if ( ! empty( $did_refresh_staff ) ) {
						$staff_refreshed_count++;
					}
				}
			}
		}
		$end = microtime(true);
		$note = ( ! empty( $did_refresh ) ) ? 'Refreshed appt type. ' : 'Skipped appt type '.$appointment_type->id.'. ';
		if ( ! empty( $staff_refreshed_count ) ) {
			$note .= 'Refreshed '.$staff_refreshed_count.' staff member(s). ';
		}
		$note .= 'Execution time: '.( $end - $start ) . 's';
		ssa_complete_action( $async_action['id'], $note );
	}

	public function is_activated( $force_check = false ) {
		$settings = $this->plugin->google_calendar_settings->get();

		if ( empty( $settings['access_token'] ) ) {
			return false;
		}

		if ( empty( $force_check ) ) {
			return true;
		}

		try {
			$this->client_init();
			$this->service_init();
			$calendar_list = $this->get_calendar_list();
		} catch ( Exception $e ) {
			$settings['access_token'] = '';
			$this->plugin->google_calendar_settings->update( $settings );
			return false;
		}

		return true;
	}

	public function get_schedule( SSA_Appointment_Type_Object $appointment_type, Period $query_period, $args ) {
		if ( !$this->plugin->settings_installed->is_activated( 'google_calendar' ) ) {
			return new SSA_Availability_Schedule();
		}

		$excluded_calendar_ids = $appointment_type->google_calendars_availability;
		if ( empty( $excluded_calendar_ids ) ) {
			return new SSA_Availability_Schedule();
		}

		$blocked_rows = $this->plugin->availability_external_model->query( array(
			'calendar_id_hash_IN' => array_map( 'ssa_int_hash', $excluded_calendar_ids ),
			'is_available' => 0,
			'type' => 'appointment_type',
			'service' => 'google',
			'number' => -1,
		) );

		$schedule = new SSA_Availability_Schedule();
		foreach ($blocked_rows as $blocked_row) {
			if ( ! empty( $blocked_row['is_all_day'] ) ) {
				$blocked_start_local = SSA_Utils::get_datetime_in_utc(
					$blocked_row['start_date'],
					$appointment_type->get_timezone()
				);

				$blocked_end_local = SSA_Utils::get_datetime_in_utc(
					$blocked_row['end_date'],
					$appointment_type->get_timezone()
				);

				$blocked_period = new Period(
					$blocked_start_local,
					$blocked_end_local
				);
			} else {
				$blocked_period = new Period( $blocked_row['start_date'], $blocked_row['end_date'] );
			}

			if ( ! $blocked_period->overlaps( $query_period ) ) {
				continue;
			}

			if ( ! empty( $blocked_row['is_all_day'] ) ) {
				$blocked_block = SSA_Availability_Block_Factory::available_for_period(
					$blocked_period, array(
					'capacity_available' => 0,
				) );
			} else {
				// make sure to apply buffers to timed gcal events
				$blocked_block = SSA_Availability_Block_Factory::available_for_period(
					$blocked_period, array(
					'capacity_available' => 0,
					'buffer_available' => 0,
				) );
			}
			$schedule = $schedule->pushmerge( $blocked_block );
		}

		return $schedule;
	}

	public function filter_blocked_periods( $blocked_periods, $appointment_type, $args ) {
		if ( !$this->plugin->settings_installed->is_activated( 'google_calendar' ) ) {
			return $blocked_periods;
		}

		$excluded_calendar_ids = $appointment_type->google_calendars_availability;
		if ( empty( $excluded_calendar_ids ) ) {
			return $blocked_periods;
		}

		$blocked_blocks = $this->plugin->availability_external_model->query( array(
			'calendar_id_hash_IN' => array_map( 'ssa_int_hash', $excluded_calendar_ids ),
			'is_available' => 0,
			'type' => 'appointment_type',
			'service' => 'google',
			'number' => -1,
		) );

		foreach ($blocked_blocks as $key => $blocked_block) {
			if ( $blocked_block['is_all_day'] == 1 ) {
				$blocked_start_local = $this->plugin->utils->get_datetime_in_utc( $blocked_block['start_date'], $this->plugin->utils->get_datetimezone( $appointment_type['id'] ) );
				$blocked_end_local = $this->plugin->utils->get_datetime_in_utc( $blocked_block['end_date'], $this->plugin->utils->get_datetimezone( $appointment_type['id'] ) );
				$blocked_period = new Period( $blocked_start_local, $blocked_end_local );
			} else {
				$blocked_period = new Period( $blocked_block['start_date'], $blocked_block['end_date'] );
			}
			if ( !empty( $args['buffered'] ) ) {
				$blocked_period = apply_filters( 'ssa/buffer_period/start_date', $blocked_period, $blocked_period, $appointment_type );
				$blocked_period = apply_filters( 'ssa/buffer_period/end_date', $blocked_period, $blocked_period, $appointment_type );
				if ( empty( $blocked_period ) ) {
					continue;
				}
			}
			$blocked_periods[] = $blocked_period;
		}
		return $blocked_periods;
	}


	public function prepare_appointment_type_availability_calendars_for_response( $data, $appointment_type_id, $recursive ) {
		echo '<pre>'.print_r($data, true).'</pre>'; // phpcs:ignore
		exit();
	}

	/** @var object */
	private $calendar;

	/** @var array */
	private $event;

	/** @var \SSA\Lib\Entities\Staff */
	private $staff;

	private $errors = array();

	public function client_init() {
		return $this->plugin->google_calendar_client->client_init();
	}

	public function get_filtered_client_id( $client_id ) {
		if ( defined( 'SSA_GOOGLE_CALENDAR_CLIENT_ID' ) ) {
			return SSA_GOOGLE_CALENDAR_CLIENT_ID;
		}

		$client_id = apply_filters( 'ssa/google_calendar/client_id', $client_id );
		return $client_id;
	}

	public function get_client_id() {
		$settings = $this->plugin->google_calendar_settings->get();
		$client_id = $settings['client_id_filtered'];

		return $client_id;
	}

	
	public function get_filtered_client_secret( $client_secret ) {
		if ( defined( 'SSA_GOOGLE_CALENDAR_CLIENT_SECRET' ) ) {
			return SSA_GOOGLE_CALENDAR_CLIENT_SECRET;
		}

		$client_secret = apply_filters( 'ssa/google_calendar/client_secret', $client_secret );
		return $client_secret;
	}

	public function get_client_secret() {
		$settings = $this->plugin->google_calendar_settings->get();
		$client_secret = $settings['client_secret_filtered'];

		return $client_secret;
	}
	
	/**
	 * Determines which access token to use for the current request
	 */
	public function service_init( $staff_id = 0 ) {
		return $this->plugin->google_calendar_client->service_init( $staff_id );
	}
	
	public function get_calendars_by_staff( $staff_id ) {
		try {
			$this->client_init();
			$this->service_init();
			$this->plugin->error_notices->delete_error_notice( 'google_calendar_get_calendars_by_staff' );

		} catch ( Exception $e ) {
			$this->plugin->error_notices->add_error_notice( 'google_calendar_get_calendars_by_staff' );
			ssa_debug_log( 'google_calendar_get_calendars_by_staff', 100 );
			ssa_debug_log( __( 'Google Calendar connection error, please disconnect and reconnect your Google Calendar account', 'simply-schedule-appointments' ), 100 );
			ssa_debug_log( $e->getCode() );
			ssa_debug_log( $e->getMessage() );
			return array();
		}

		$calendar_list = $this->get_calendar_list();
		$calendars = array();
		foreach ($calendar_list as $calendar_id => $calendar_details) {
			$calendars[$calendar_id] = array_merge( $calendar_details, array(
				'gcal_id' => $calendar_id,
				'title' => $calendar_details['summary'],
				'staff_id' => $staff_id,
			) );
		}
		return $calendars;
	}

	/**
	 * Whenever we sync an appointment with Google Calendar, this function checks if we have a Google Meet url available 
	 * and stores the url on the appointment model database.
	 *
	 * @param int $appointment_id
	 * @return void
	 */
	public function sync_calendar_conference_to_appointment( $appointment_id ) {
		if ( !$this->plugin->settings_installed->is_activated( 'google_calendar' ) ) {
			return;
		}

		$appointment_obj = new SSA_Appointment_Object( $appointment_id );

		$status = $appointment_obj->status;
		if ( empty( $status ) || ( ! $appointment_obj->is_canceled() && ! $appointment_obj->is_booked() ) ) {
			return;
		}

		$appointment = $appointment_obj->data;
		$appointment_type = $this->plugin->appointment_type_model->get( $appointment['appointment_type_id'] );

		if ( empty( $appointment_type['google_calendar_booking'] ) ) {
			return;
		}

		$web_meeting_url = $appointment_obj->__get( 'web_meeting_url' );
		
		// if Google Meet Url is already stored, bail 
		if( ! empty( $web_meeting_url ) ) {
			return;
		}
		
		$conference_url = $this->get_gcal_conference_url_from_event( $appointment_id );

		// if Google Calandar event has a Google Meet Url, store it
		if( ! empty( $appointment_type['web_meetings']['provider'] ) && 'google' == $appointment_type['web_meetings']['provider'] && $conference_url ) {
			$appointment_update_data = array(
				'web_meeting_url' => $conference_url,
			);

			remove_action( 'ssa/appointment/after_update', array( $this, 'sync_calendar_conference_to_appointment' ), 10, 1 ); // shouldn't be necessary because of ```if Google Meet Url is already stored, bail``` above


			add_filter( 'ssa/google_calendar/send_attendee_updates', array( $this, 'filter_to_prevent_attendee_updates' ) );
			$response = $this->plugin->appointment_model->update( $appointment_id, $appointment_update_data );
			if ( $appointment_obj->is_group_event() ) {
				$response = $this->plugin->appointment_model->update( $appointment_obj->group_id, $appointment_update_data );
			}
			remove_filter( 'ssa/google_calendar/send_attendee_updates', array( $this, 'filter_to_prevent_attendee_updates' ) );


			add_action( 'ssa/appointment/after_update', array( $this, 'sync_calendar_conference_to_appointment' ), 10, 1 );
		}

	}

	public function filter_to_prevent_attendee_updates( $value ) {
		return 'none';
	}

	public function sync_appointment_to_calendar( $appointment_id, $data = array(), $data_before = array() ) {
		if ( !$this->plugin->settings_installed->is_activated( 'google_calendar' ) ) {
			return;
		}

		if ( false === $this->should_sync_appointment_after_update( $data, $data_before ) ) {
			return;
		}

		$appointment_obj = new SSA_Appointment_Object( $appointment_id );
		$status = $appointment_obj->status;
		if ( empty( $status ) || !in_array( $status, array( 'booked', 'canceled' ) ) ) {
			return; // don't add pending appointments to the calendar
		}
		$appointment = $appointment_obj->data;
		$appointment_type = $this->plugin->appointment_type_model->get( $appointment['appointment_type_id'] );
		
		if ( empty( $appointment_type['google_calendar_booking'] ) ) {
			return;
		}

		if ( ! empty( $data_before ) && $data_before['status'] === 'canceled' && $status === 'canceled' && $appointment_obj->is_group_event() ) {
			return; // This is a group event canceled appointment that got reassigned
		}
		
		$calendar_id = $appointment_obj->get_calendar_id();
		$calendar_event_id = $appointment_obj->get_calendar_event_id();

		try {
			$this->client_init();
			$this->service_init();
			$this->plugin->error_notices->delete_error_notice( 'google_calendar_sync_appointment_to_calendar' );
			if ( ! empty( $calendar_id ) && ! empty( $calendar_event_id ) ) {
				$existing_event = $this->get_event( $calendar_event_id, $calendar_id );
				if ( $existing_event instanceof WP_Error ) {
					// insert revision of this failure
					$this->plugin->revision_model->insert_revision_gcal_after_sync('failure', $appointment_id, 'sync_appointment_to_calendar', 'Could not find existing event details', $calendar_id, $calendar_event_id );
					$calendar_id = null;
					$calendar_event_id = null;
				}
			}

			if ( empty( $calendar_id ) || empty( $calendar_event_id ) ) {
				$calendar_id = $appointment_type['google_calendar_booking'];
				$event = $this->get_gcal_event_from_appointment( $appointment );

				$calendar_event_id = $this->insert_event( $event, $calendar_id );
				if ( $calendar_event_id instanceof WP_Error ) {
					ssa_debug_log( $calendar_event_id, 10, 'Error creating Google Calendar Event for Appointment ID ' . $appointment_obj->id );
					// insert revision of this failure
					$this->plugin->revision_model->insert_revision_gcal_after_sync('failure', $appointment_id, 'sync_appointment_to_calendar', 'Appointment sync failed while creating event', $calendar_id, $calendar_event_id, $event );
					return;
				} else {
					$this->plugin->revision_model->insert_revision_gcal_after_sync('success', $appointment_id, 'sync_appointment_to_calendar', 'Appointment successfully synced to Google Calendar', $calendar_id, $calendar_event_id );
				}

				$appointment_update_data = array(
					'google_calendar_id' => $calendar_id,
					'google_calendar_event_id' => $calendar_event_id,
				);

				// we want to sync it again to properly handle events that happen on creation (like Google Meet web conference details)
				// so we no longer remove and add the actions
				// remove_action( 'ssa/appointment/after_update', array( $this, 'sync_appointment_to_calendar' ), 10, 1 );

				if ( $appointment_obj->is_group_event() ) {
					$response = $this->plugin->appointment_model->update( $appointment_obj->group_id, $appointment_update_data );
				} else {
					$response = $this->plugin->appointment_model->update( $appointment_id, $appointment_update_data );
				}


				// add_action( 'ssa/appointment/after_update', array( $this, 'sync_appointment_to_calendar' ), 10, 1 );
			} else {

				// if settings to delete google calendar event is enabled, and event is canceled, delete from Google Calendar
				if ( $this->plugin->google_calendar_settings->should_delete_events() ) {
					if ( $appointment_obj->is_group_event() && $appointment_obj->is_group_canceled() ) {
						$this->delete_event( $calendar_event_id, $calendar_id );
					// insert revision of this deletion
					$this->plugin->revision_model->insert_revision_gcal_after_sync('success', $appointment_id, 'sync_appointment_to_calendar', 'Deleted group event', $calendar_id, $calendar_event_id );
					return;
				} elseif ( $appointment_obj->is_individual_appointment() && $appointment_obj->is_canceled() ) {
					$this->delete_event( $calendar_event_id, $calendar_id );
					// insert revision of this deletion
					$this->plugin->revision_model->insert_revision_gcal_after_sync('success', $appointment_id, 'sync_appointment_to_calendar', 'Deleted individual event', $calendar_id, $calendar_event_id );
					return;
					}
				}

				$event = $this->get_gcal_event_from_appointment( $appointment );

				if ( ! empty( $existing_event->conferenceData ) ) {
					$event['conferenceData'] = $existing_event->conferenceData;
				}

				// TODO: handle attendees similarly

				$calendar_event_id = $this->update_event( $calendar_event_id, $event, $calendar_id );
				$this->plugin->revision_model->insert_revision_gcal_after_sync('success', $appointment_id, 'sync_appointment_to_calendar', 'Updated Google Calendar event', $calendar_id, $calendar_event_id );
				return;
			}
		} catch ( \Exception $e ) {
			$this->plugin->error_notices->add_error_notice( 'google_calendar_sync_appointment_to_calendar' );
			ssa_debug_log( 'google_calendar_sync_appointment_failed', 100 );
			ssa_debug_log( 'Exception: ' . print_r( $e, true ), 100 ); // phpcs:ignore
			ssa_debug_log( __( 'Google Calendar connection error, please disconnect and reconnect your Google Calendar account', 'simply-schedule-appointments' ), 100 );
			// insert revision of this exception
			$this->plugin->revision_model->insert_revision_gcal_after_sync('failure', $appointment_id, 'sync_appointment_to_calendar', 'Exception occured while doing sync', $calendar_id, $calendar_event_id );
			return false;
		}

		return true;
	}

	/**
	 * Gets the Google Meet Url from an appointment if the appointment has a Google Calendar event.
	 *
	 * @param int $appointment_id
	 * @return null|string
	 */
	public function get_gcal_conference_url_from_event( $appointment_id ) {
		if ( !$this->plugin->settings_installed->is_activated( 'google_calendar' ) ) {
			return null;
		}

		$appointment_obj = new SSA_Appointment_Object( $appointment_id );

		$status = $appointment_obj->status;
		if ( empty( $status ) || !in_array( $status, array( 'booked', 'canceled' ) ) ) {
			return null;
		}

		$appointment = $appointment_obj->data;
		$appointment_type = $this->plugin->appointment_type_model->get( $appointment['appointment_type_id'] );

		if ( empty( $appointment_type['google_calendar_booking'] ) ) {
			return null;
		}

		$calendar_id = $appointment_obj->get_calendar_id();
		$calendar_event_id = $appointment_obj->get_calendar_event_id();

		$event = $this->get_event( $calendar_event_id, $calendar_id );

		if( ! $event || is_wp_error( $event ) ) {
			return null;
		}

		$conference_data = isset($event->conferenceData)? $event->conferenceData : null;
		return $this->get_gcal_conference_url_from_conference_data( $conference_data );
	}

	public function get_gcal_conference_url_from_conference_data( $conference_data ) {
		if( ! $conference_data ) {
			return null;
		}

		$entry_points = isset($conference_data->entryPoints)? $conference_data->entryPoints : null;

		if( ! $entry_points ) {
			return null;
		}

		$video_entry = array_values( array_filter( $entry_points, function( $entry ) {
			return $entry->entryPointType === 'video';
		} ) );

		if( empty( $video_entry ) ) {
			return null;
		}
		
		return $video_entry[0]->uri;
	}

	/**
	 * Constructs an associative array representing a Google Calendar Event from an SSA appointment.
	 *
	 * @param array $appointment
	 * @param boolean $is_update
	 * @return array $event
	 */
	public function get_gcal_event_from_appointment( $appointment, $is_update = false ) {
		
		$appointment_obj = new SSA_Appointment_Object( $appointment['id'] );

		$appointment_type = $this->plugin->appointment_type_model->get( $appointment['appointment_type_id'] );

		$event = [
			"start"=> [
				"dateTime"=> (new DateTime( $appointment['start_date'] ))->format( \DateTime::RFC3339 )
			],
			"end"=> [
				"dateTime"=> (new DateTime( $appointment['end_date'] ))->format( \DateTime::RFC3339 )
			],
			"extendedProperties"=> []
		];

		if ( ! empty( $appointment_type['shared_calendar_event'] ) ) {
			$title       = $appointment_obj->get_calendar_event_title( SSA_Recipient_Shared::create() );
			$description = $appointment_obj->get_calendar_event_description( SSA_Recipient_Shared::create() );
			$location = $appointment_obj->get_calendar_event_location( SSA_Recipient_Shared::create() );
		} else {
			$title       = $appointment_obj->get_calendar_event_title( SSA_Recipient_Admin::create() );
			$description = $appointment_obj->get_calendar_event_description( SSA_Recipient_Admin::create() );
			$location = $appointment_obj->get_calendar_event_location( SSA_Recipient_Admin::create() );
		}

		$status = $appointment_obj->status;
		$event['status'] = 'confirmed';

		if ( $appointment_obj->is_group_event() ) {
			$appointment['id'] = $appointment_obj->group_id;
			if ( $appointment_obj->is_group_canceled() ) {
				$event['transparency'] = 'transparent';
			}
		} elseif ( $appointment_obj->is_individual_appointment() ) {
			if ( $appointment_obj->is_canceled() ) {
				$event['transparency'] = 'transparent';
			}
		}


		$event['summary'] = $title;
		$event['description'] = $description;
		$event['extendedProperties']['shared'] = array(
			'appointment_type_id' => $appointment['appointment_type_id'],
			'appointment_id' => $appointment['id'],
			'ssa_home_id' => SSA_Utils::get_home_id(),
		);

		$event['guestsCanInviteOthers'] = false;
		if ( $appointment_obj->is_group_event() ) {
			$event['guestsCanSeeOtherGuests'] = false;
		} elseif ( $appointment_obj->is_individual_appointment() ) {
			$event['guestsCanSeeOtherGuests'] = true;
		}

		// Only set attendees if appointment type allows it.
		if ( ! $is_update ) {
			$attendees = $appointment_obj->get_attendees();
			$gcal_attendees = array();

			foreach($attendees as $attendee){
				$gcal_attendee = array();
				$gcal_attendee['email'] = $attendee['email'];
				$gcal_attendee['displayName'] = $attendee['name'];
				$gcal_attendees[] = $gcal_attendee;
			}

			// Deprecated filter: $attendees = apply_filters('ssa/appointment/calendar_attendees', $gcal_attendees, $appointment);
			// Please use 'ssa/appointment/attendees' filter (defined in class-appointment-object.php) instead
			if ( ! empty( $gcal_attendees ) ) {
				$event['attendees'] = $gcal_attendees;
			}
		}

		// If custom location is set, add it to the event.
		if ( ! empty( $location ) ) {
			$event['location'] = $location;
			// Only set conference data if the appointment type allows it, AND we don't have a url yet.
		} elseif ( ! empty( $appointment_type['web_meetings']['provider'] ) ) {
			$web_meeting_url = $appointment_obj->__get( 'web_meeting_url' );
			if ( ! empty( $web_meeting_url ) ) {
				$event['location'] = $web_meeting_url;
			} else {
				if ( 'google' === $appointment_type['web_meetings']['provider'] ) {
					// Set Google Meet.
					$event['conferenceData'] = array(
						'conferenceId' => 'aaa-bbbb-ccc',
						'createRequest' => array(
							'requestId' => $appointment['id'],
							'conferenceSolutionKey' => array(
								'type' => 'hangoutsMeet',
							)
						)
					);
				}
			}
		}

		return $event;
	}

	public function maybe_queue_refresh_check( $appointment_type_id ) {
		if ( $this->plugin->settings_installed->is_activated( 'google_calendar' ) ) {
			$should_refresh_appointment_type = $this->should_refresh_appointment_type( $appointment_type_id );
			if ( $should_refresh_appointment_type ) {
				$payload = array(
					'appointment_type_id' => $appointment_type_id,
				);
				ssa_queue_action(
					'refresh_google_calendar',
					'ssa_refresh_google_calendar',
					10,
					$payload,
					'appointment_type',
					$appointment_type_id,
					'google_calendar',
					array(
						'date_queued' => gmdate( 'Y-m-d H:i:s' )
					)
				);
			}
		}
	}

	public function get_google_cache_version() {
		$cache_version = get_transient( 'ssa/google/cache_version' );
		if ( empty( $cache_version ) ) {
			$cache_version = 0;
		}

		return (int)$cache_version;
	}

	public function increment_google_cache_version() {
		$cache_version = $this->get_google_cache_version();
		$cache_version++;
		set_transient('ssa/google/cache_version', $cache_version, WEEK_IN_SECONDS );
		return $cache_version;
	}

	public function log_last_queued_refresh_time( $calendar_id ) {
		$google_calendar_settings = $this->plugin->google_calendar_settings->get();
		$refresh_interval = $google_calendar_settings['refresh_interval'];
		if ( $refresh_interval == 0 ) {
			return;
		}

		$transient_key = 'ssa/google/v'.$this->get_google_cache_version().'_calendar_'.ssa_int_hash( $calendar_id ).'/last_refreshed_time';
		$cache_lifetime = $refresh_interval*60;
		set_transient( $transient_key, time(), $cache_lifetime );
	}

	public function should_refresh_appointment_type( $appointment_type_id ) {
		$appointment_type = $this->plugin->appointment_type_model->get( $appointment_type_id );
		if ( empty( $appointment_type['google_calendars_availability'] ) ) {
			return false;
		}

		$should_refresh_appointment_type = false;
		foreach ($appointment_type['google_calendars_availability'] as $key => $calendar_id ) {
			if ( $this->should_refresh_calendar_id( $calendar_id ) ) {
				$should_refresh_appointment_type = true;
			}
		}

		return $should_refresh_appointment_type;
	}

	public function should_refresh_calendar_id( $calendar_id ) {
		$google_calendar_settings = $this->plugin->google_calendar_settings->get();
		$refresh_interval = $google_calendar_settings['refresh_interval'];
		if ( $refresh_interval == 0 ) {
			return true;
		}

		$cache_lifetime = $refresh_interval*60;

		$transient_key = 'ssa/google/v'.$this->get_google_cache_version().'_calendar_'.ssa_int_hash( $calendar_id ).'/last_refreshed_time';
		$last_queued_refresh_time = get_transient( $transient_key );
		if ( empty( $last_queued_refresh_time ) ) {
			$this->log_last_queued_refresh_time( $calendar_id );
			return true;
		}

		$time_to_queue_next_refresh = $last_queued_refresh_time + $refresh_interval*60;
		if ( time() < $time_to_queue_next_refresh ) {
			return false;
		}
		
		$this->log_last_queued_refresh_time( $calendar_id );
		return true;
	}

	public function maybe_refresh_external_events_for_appointment_type( $appointment_type_id, $args=array() ) {
		$should_refresh_appointment_type = $this->should_refresh_appointment_type( $appointment_type_id );

		if ( ! empty( $should_refresh_appointment_type ) ) {
			$this->refresh_external_events_for_appointment_type( $appointment_type_id, $args );
			return true;
		}

		return false;
	}

	public function maybe_refresh_calendars_for_appointment_type_id( $appointment_type_id ) {
		if ( $this->plugin->settings_installed->is_activated( 'google_calendar' ) ) {
			$last_refreshed = get_transient( 'ssa_gcal_last_refresh_queued_'.$appointment_type_id );
			$google_calendar_settings = $this->plugin->google_calendar_settings->get();
			$refresh_interval = $google_calendar_settings['refresh_interval'];
			if ( empty( $last_refreshed ) || $last_refreshed < gmdate( 'Y-m-d H:i:s', time()-$refresh_interval*MINUTE_IN_SECONDS ) ) {
				if ( $refresh_interval >= 1 ) {
					set_transient( 'ssa_gcal_last_refresh_queued_'.$appointment_type_id, gmdate( 'Y-m-d H:i:s' ), $refresh_interval*10 );
				}
				$payload = array(
					'appointment_type_id' => $appointment_type_id,
				);
				ssa_queue_action(
					'refresh_google_calendar',
					'ssa_refresh_google_calendar',
					10,
					$payload,
					'appointment_type',
					$appointment_type_id,
					'google_calendar',
					array(
						'date_queued' => gmdate( 'Y-m-d H:i:s' )
					)
				);
			}
		}
	}

	public function refresh_external_events_for_appointment_type( $appointment_type_id, $args=array() ) {
		$appointment_type = SSA_Appointment_Type_Object::instance( $appointment_type_id );
		$excluded_calendar_ids = $appointment_type->google_calendars_availability;
		if ( empty( $excluded_calendar_ids ) ) {
			return false;
		}

		$args = shortcode_atts( array(
			'staff_id' => 0,
		), $args );

		$this->client_init();
		$this->service_init();

		foreach ( $excluded_calendar_ids as $calendar_id) {
			$events = $this->get_events_from_calendar( $calendar_id );
			$events_hash = ssa_int_hash( json_encode( $events ) );
			$transient_key = 'ssa/google/v'.$this->get_google_cache_version().'_event_hash_for_calendar_'.ssa_int_hash( $calendar_id );
			$last_hash = get_transient( $transient_key );
			if ( ! empty( $last_hash ) && $last_hash == $events_hash) {
				continue; // this calendar hasn't changed since last time
			}

			set_transient( $transient_key, $events_hash, WEEK_IN_SECONDS );

			$deleted_count = $this->plugin->availability_external_model->bulk_delete( array(
				'calendar_id_hash' => ssa_int_hash( $calendar_id ),
				'type' => 'appointment_type',
				'service' => 'google',
			) );
			foreach ($events as $key => $event) {				
				if ( ! empty( $event['appointment_id'] ) ) {
					unset( $event['appointment_id'] );	
				}

				$response = $this->plugin->availability_external_model->raw_insert( $event );
			}

			$this->log_last_queued_refresh_time( $calendar_id );
		}

		do_action( 'ssa/settings/google_calendar/updated' ); // will trigger cache invalidation
		// TODO remove ^ in favor of calendar_id invalidation (don't nuke everything everytime any calendar is updated)
		return $events;
	}

	public function get_events_for_staff( ) {

	}

	public function get_events_from_calendar( $calendar_id, $args=array() ) {
		$args = shortcode_atts( array(
			'start_date' => new DateTime(),
			'end_date' => '',
		), $args );
		$google_calendar_settings = $this->plugin->google_calendar_settings->get();

		try {
			$calendar = $this->plugin->google_calendar_client->get_calendar_from_calendar_list( $calendar_id, array(
				'quotaUser' => $this->getQuotaUser(),
			) );
			
			if( empty( $calendar ) ) {
				return array();
			}
			
		} catch( Exception $e ) {
			$this->errors[] = $e->getMessage();
			return array();
		}

		// get all events from calendar, without timeMin filter (the end of the event can be later then the start of searched time period)
		$result = array();

		try {
			$calendar_access = $calendar->accessRole;
			$limit_events    = 500;
			if ( ! empty( $google_calendar_settings['query_limit'] ) ) {
				$limit_events = (int)$google_calendar_settings['query_limit'];
			}

			$timeMin = $args['start_date']->format( \DateTime::RFC3339 );

			$events = $this->plugin->google_calendar_client->get_events_from_calendar( $calendar_id, array(
				'quotaUser' => $this->getQuotaUser(),
				'singleEvents' => true,
				'orderBy'      => 'startTime',
				'timeMin'      => $timeMin,
				'maxResults'   => $limit_events,
			) );
			
			if ( empty( $events ) ) {
				return array();
			}

			foreach ( $events as $event ) {
				/** @var object $event */
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

				$event_transparency = ( empty ( $event->transparency ) || $event->transparency === 'opaque' ) ? 'opaque' : 'transparent';
				
				if ( strpos( $calendar_id, "holiday" ) !== false && $event->description === "Public holiday") {
					$event_transparency = 'opaque';
				}
				
				// if event was declined by the current staff/admin ($calendar->id), consider it transparent
				if ( ! empty( $event->attendees ) ) {
					foreach ( $event->attendees as $attendee ) {
						if ( $attendee->email == $calendar->id && $attendee->responseStatus == 'declined' ) {
							$event_transparency = 'transparent';
						}
					}
				}
				
				
				// Get start/end dates of event and transform them into WP timezone (Google doesn't transform whole day events into our timezone).
				$event_start = $event->start;
				$event_end   = $event->end;

				if ( $event_start->dateTime == null ) {
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
					'type' => 'appointment_type',
					'service' => 'google',
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
			$this->errors[] = $e->getMessage();
		}

		return array();

	}

	/**
	 * Create Event and return id
	 *
	 * @param Entities\Appointment $appointment
	 * @return mixed
	 */
	public function createEvent( Entities\Appointment $appointment )
	{
		try {
			if ( in_array( $this->getCalendarAccess(), array( 'writer', 'owner' ) ) ) {
				$this->event = array();

				$this->handleEventData( $appointment );

				$createdEvent = $this->plugin->google_calendar_client->insert_event_into_calendar( $this->getCalendarID(), $this->event, array(
					'quotaUser' => $this->getQuotaUser(),
					'conferenceDataVersion' => 1
				) );

				return $createdEvent->id;
			}
		} catch ( \Exception $e ) {
			$this->errors[] = $e->getMessage();
		}

		return false;
	}

	/**
	 * Update event
	 *
	 * @param Entities\Appointment $appointment
	 * @return bool
	 */
	public function updateEvent( Entities\Appointment $appointment )
	{
		try {
			if ( in_array( $this->getCalendarAccess(), array( 'writer', 'owner' ) ) ) {
				$this->event = (array) $this->plugin->google_calendar_client->get_event_from_calendar( $this->getCalendarID(), $appointment->getGoogleEventId(), array(
					'quotaUser' => $this->getQuotaUser(),
				) );

				$this->handleEventData( $appointment );

				$this->plugin->google_calendar_client->update_event_in_calendar( $this->getCalendarID(), $this->event->id, $this->event, array(
					'quotaUser' => $this->getQuotaUser(),
				) );

				return true;
			}
		} catch ( \Exception $e ) {
			$this->errors[] = $e->getMessage();
		}

		return false;
	}

	/**
	 * Get list of Google Calendars.
	 *
	 * @return array
	 */
	public function get_calendar_list() {
		$result = array();
		try {
			$this->client_init();
			$this->service_init();
			
			$calendarList = $this->plugin->google_calendar_client->get_calendar_list( array(
				'quotaUser' => $this->getQuotaUser(),
			));
			foreach ( $calendarList as $calendarListEntry ) {
				$result[ $calendarListEntry->id ] = array(
					'primary' => isset( $calendarListEntry->primary ) ? $calendarListEntry->primary : '',
					'summary' => isset( $calendarListEntry->summary ) ? $calendarListEntry->summary : '',
					'description' => isset( $calendarListEntry->description ) ? $calendarListEntry->description : '',
					'kind' => isset( $calendarListEntry->kind ) ? $calendarListEntry->kind : '',
					'location' => isset( $calendarListEntry->location ) ? $calendarListEntry->location : '',
					'role' => isset( $calendarListEntry->accessRole ) ? $calendarListEntry->accessRole : '',
					'background_color' => isset( $calendarListEntry->backgroundColor ) ? $calendarListEntry->backgroundColor : '',
					'foreground_color' => isset( $calendarListEntry->foregroundColor ) ? $calendarListEntry->foregroundColor : '',
					'color_id' => isset( $calendarListEntry->colorId ) ? $calendarListEntry->colorId : '',
					'default_reminders' => isset( $calendarListEntry->defaultReminders ) ? $calendarListEntry->defaultReminders : '',
					'time_zone' => isset( $calendarListEntry->timeZone ) ? $calendarListEntry->timeZone : '',
				);
			}
		} catch ( \Exception $e ) {
			if ( class_exists( 'Session' ) ) {
				Session::set( 'staff_google_auth_error', json_encode( $e->getMessage() ) );
			} else {
				throw new Exception( json_encode( $e->getMessage() ), '500' );
			}
		}

		return $result;
	}

	/**
	 * description: this is the same logic used by the PHP OAuth client
	 * with a minor difference, this takes the $token as an argument
	 *
	 * @return bool Returns True if the access_token is expired.
	 */
	public function is_access_token_expired( $token ) {
		if ( !$token ) {
			return true;
		}

		$created = 0;
		if ( isset( $token['created'] ) ) {
			$created = $token['created'];
		} elseif ( isset( $token['id_token'] ) ) {
			// check the ID token for "iat"
			// signature verification is not required here, as we are just
			// using this for convenience to save a round trip request
			// to the Google API server
			$idToken = $token['id_token'];
			if ( substr_count( $idToken, '.' ) == 2 ) {
				$parts   = explode( '.', $idToken );
				$payload = json_decode( base64_decode( $parts[1] ), true );
				if ( $payload && isset( $payload['iat'] ) ) {
					$created = $payload['iat'];
				}
			}
		}

		// If the token is set to expire in the next 30 seconds.
		return ( $created + ( $token['expires_in'] - 30 ) ) < time();
	}

	public function getQuotaUser(){
		$user = $this->quotaUser;
		if( !empty( $this->quotaUser ) ){
			return $this->quotaUser;
		}

		$staff_id = ssa_get_current_staff_id();

		// set the quotaUser to the domain-staff_id
		$domain = parse_url( get_home_url(), PHP_URL_HOST );

		$quotaUser = $domain;

		if( !empty( $staff_id ) ){
			$quotaUser .= "-$staff_id";
		}

		// cache
		$this->quotaUser = $quotaUser;

		return $quotaUser;

	}


	// keeping default argument to null
	// so that we can use this function to get all ssa_quick_connect tokens if needed
	/**
	 * description: gets the ssa_quick_connect access token from the database
	 * if the token is expired, it will get a newbatch of tokens from the ssa_quick_connect service
	 * whenever the function receives a success response from ssa_quick_connect service, it updates the database
	 * with all the tokens it received
	 * 
	 * The ssa_quick_connect service is assumed to always return valid tokens
	 *
	 * 
	 * @param int $staff_id
	 * @return string
	 */
	public function get_quick_connect_access_token($staff_id = null) {
		// we get settings even if staff_id is set to other than 0
		// because a ssa_quick_connect response will update all access_tokens
		$google_calendar_settings = $this->plugin->google_calendar_settings->get();
		$license_settings =	$this->plugin->license_settings->get();

		if( isset( $staff_id ) ){
			// attempt to get cached from the database
			if( $staff_id == 0 ){
				// if we're looking for the site-wide access token and its still valid
				if ( !$this->is_access_token_expired($google_calendar_settings['access_token']) ) {
					return $google_calendar_settings['access_token'];
				}

			}else{
				// if we're looking for a staff member's access token and its still valid
				$staff = $this->plugin->staff_model->get( $staff_id );
				if ( !$this->is_access_token_expired( $staff['google_access_token'] ) ) {
					return $staff['google_access_token'];
				}
			}
		}

		// reaching here means the cached/stored token was invalid
		// so we need to get a new one from the ssa_quick_connect service

		// if license is not active just return
		if( $license_settings['license_status'] !== 'valid'){
			return;
		}
		

		// if backoff has value, exponentially backoff
		if( $google_calendar_settings['quick_connect_backoff_timestamp'] + $google_calendar_settings['quick_connect_backoff'] > time() ){
			// just return, the queued action should repeat later
			return;
		}
		
		if ( ! defined( 'SSA_QUICK_CONNECT_GCAL_SERVE_ENDPOINT' ) ) {
			ssa_debug_log( 'SSA_QUICK_CONNECT_GCAL_SERVE_ENDPOINT is not defined!' );
			return false;
		}

		$response = wp_remote_post(SSA_QUICK_CONNECT_GCAL_SERVE_ENDPOINT, array(
			'body' => array(
				'domain'=> $google_calendar_settings['quick_connect_home_url'],
				'license_key'=>$license_settings['license']
			),
			
		));
		
		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );
		
		// if error
		if( empty( $response_body ) || $response_body['status'] !=='success' ){

			// check action and act accordingly
			// if service is down, set a timestamp to not retry before
			if( ! empty( $response_body['error']['action'] ) ) {
				switch ( $response_body['error']['action'] ) {
					// license key valid but not active on the domain
					case 'ACTIVATE_LICENSE':
						$this->plugin->error_notices->add_error_notice( 'quick_connect_gcal_activate_license' );
						ssa_debug_log( 'ACTIVATE_LICENSE ssa_quick_connect action received');
						break;
					// license key is not valid / expired
					case 'RENEW_LICENSE':
						$this->plugin->error_notices->add_error_notice( 'quick_connect_gcal_renew_license' );
						ssa_debug_log( 'RENEW_LICENSE ssa_quick_connect action received');
						break;
					// ssa_quick_connect is down, backoff
					case 'BACKOFF':
						// TODO later
						// The error notice below may not be needed later when ssa-quick-conect is more mature
						// for now this will help us keep an eye on any issues with the implementation
						$this->plugin->error_notices->add_error_notice( 'quick_connect_gcal_backoff' );
						ssa_debug_log( 'BACKOFF ssa_quick_connect action received');
						break;
						// if the action is empty or unknown
						// made default behaviour as backoff, if ssa_quick_connect service is not working we want the requests to slow down
					default:
						// TODO later
						// The error notice below may not be needed later when ssa-quick-conect is more mature
						// for now this will help us keep an eye on any issues with the implementation
						$this->plugin->error_notices->add_error_notice( 'quick_connect_gcal_backoff' );
						ssa_debug_log( 'UNKNOWN ssa_quick_connect action received');
						ssa_debug_log(print_r($response_body,true)); // phpcs:ignore
						return;
				}
			}

			// regardless of the error, backoff
			if( $google_calendar_settings['quick_connect_backoff'] ) {
				// if there is already a backoff interval, and we got another error response, double the backoff interval, otherwise start at a constant
				$google_calendar_settings['quick_connect_backoff'] = $google_calendar_settings['quick_connect_backoff'] ? $google_calendar_settings['quick_connect_backoff'] * 2 : 180;

				// cap the backoff interval at a maximum of 1 hour, we don't want this to increase forever
				$google_calendar_settings['quick_connect_backoff'] = min( $google_calendar_settings['quick_connect_backoff'], 3600 );

				$google_calendar_settings['quick_connect_backoff_timestamp'] = time();
				$this->plugin->google_calendar_settings->update( $google_calendar_settings );
			}
			return false;
		}
		
		// response ha a status of success, remove error notices
		$this->plugin->error_notices->delete_error_notice( 'quick_connect_gcal_auth_mode_changed' );
		$this->plugin->error_notices->delete_error_notice( 'quick_connect_gcal_activate_license' );
		$this->plugin->error_notices->delete_error_notice( 'quick_connect_gcal_renew_license' );
		$this->plugin->error_notices->delete_error_notice( 'quick_connect_gcal_re_authorize' );
		$this->plugin->error_notices->delete_error_notice( 'quick_connect_gcal_backoff' );
		
		// reset backoff values to zero
		$google_calendar_settings['quick_connect_backoff'] = 0;
		$google_calendar_settings['quick_connect_backoff_timestamp'] = 0;
		$this->plugin->google_calendar_settings->update( $google_calendar_settings );


		$records = $response_body['data'];
		$staff_tokens=[];
		// cache all tokens from ssa_quick_connect in the local database
		// we always update all staff tokens alongside the main token when someone authorizes.
		foreach ($records as $record) {
			if(!$record['access_token']){
				// TODO - add per staff error notice
				// ssa_quick_connect does not have the records / refresh token expired need a new one
				$this->plugin->error_notices->add_error_notice( 'quick_connect_gcal_re_authorize' );
				ssa_debug_log( 'RE_AUTHORIZE ssa_quick_connect action received for staff ID: ' . $record['staff_id']);
				continue;
			}
			$staff_tokens[$record['staff_id']] = $record['access_token'];
			if( $record['staff_id'] === 0 ){
				$google_calendar_settings['access_token'] = $record['access_token'];
				$this->plugin->google_calendar_settings->update( $google_calendar_settings );
			} else {
				// leave google connection as set by user - may be disabled on purpose but still has a corresponding ssa_quick_connect record
				$this->plugin->staff_model->update( $record['staff_id'], array(
					'google_access_token' =>  $record['access_token']
				) );
			}
		}

		if( isset( $staff_id ) ){
			// if we're looking for a specific staff member's access token
			return $staff_tokens[$staff_id];
		}

		// if we're looking for all staff member's access tokens
		return $staff_tokens;
	}

	public function auth_redirect_exit( $state, $admin_uri ){
		if ( isset( $state['wp_next_base_uri'] ) && filter_var( $state['wp_next_base_uri'], FILTER_VALIDATE_URL) !== false ) {
			wp_redirect( 
				add_query_arg(
					array(
						'ssa_state' => $state['wp_next_ssa_uri'],
					),
					$state['wp_next_base_uri']
				)
			);
		} else {
			// else, go to admin
			wp_redirect( $this->plugin->wp_admin->url( $admin_uri ) );
		}
		exit();
	}

	public function catch_quick_connect_auth_callback() {
		// after authorizing ssa_quick_connect, the redirect arrives at this callback
		// all we need to do at this point is get tokens from ssa_quick_connect
		// update the database tokens
		// and redirect to the next page, which should show the signed in user their connected calendar
		if( empty( $_GET['ssa_quick_connect_status'] ) || empty( $_GET['state'] ) ){
			return;
		}

		$state = json_decode( base64_decode( strtr( sanitize_text_field( $_GET['state'] ), '-_,', '+/=' ) ), true );

		if( $_GET['ssa_quick_connect_status'] === 'success' ){
			// in all cases, get tokens - this will update the database tokens as well for any records existing in ssa_quick_connect
			$ssa_quick_connect_response = $this->get_quick_connect_access_token();

				if ( empty( $state['staff_id'] ) ) {
					// enable google calendar automatically and ssa_quick_connect mode
					$this->plugin->google_calendar_settings->update( array(
						'enabled' => true,
						// this can only be uncommented when admin can turn it off - when gcal ssa_quick_connect feature is out of beta testing
						// 'quick_connect_gcal_mode' => true,
					) );
					// redirect and exit
					$this->auth_redirect_exit( $state, $state['wp_next_ssa_uri'] );
				}else{
					$this->auth_redirect_exit( $state, '/ssa/settings/staff/profile/' . $state['staff_id'] );
					exit();
				}
		}else{
			// log the failure so we can look at it later in debug logs when needed
			ssa_debug_log('SSA ssa_quick_connect google authorization process failed with error: ' . $_GET['error'] . ' and state:' . $_GET['state'] );
			// if ssa_quick_connect auth failed, default redirect as follows, just with additional error params to show in snackbar
			if( empty( $state['staff_id'] ) ){
				// go back to admin
				wp_redirect( $this->plugin->wp_admin->url( '/ssa/settings/google-calendar' . '?error='. $_GET['error'] ) );
			}else{
				// go back to staff profile
				wp_redirect( $this->plugin->wp_admin->url( '/ssa/settings/staff/profile/' . $state['staff_id'] . '?error='. $_GET['error'] ) );
			}
			exit();
		}
	}


	public function catch_oauth_callback() {
		global $wp;

		if ( empty( $_GET['code'] ) || empty( $_GET['state'] ) ) {
			return;
		}

		$state = json_decode( base64_decode( strtr( sanitize_text_field( $_GET['state'] ), '-_,', '+/=' ) ), true );
		if ( empty( $state['authorize'] ) || $state['authorize'] != 'google' ) {
			return;
		}

		// if ( empty( $wp->request ) ) {
		// 	if ( strpos( $_SERVER['REQUEST_URI'], self::get_redirect_slug() ) === false ) {
		// 		return;
		// 	}
		// } else {
		// 	if ( $wp->request != self::get_redirect_slug() ) {
		// 		return;
		// 	}
		// }
		
		$code = sanitize_text_field( $_GET['code'] );

		if ( $this->plugin->google_calendar_client->exchange_auth_code( $code ) ) {
			if ( isset( $state['staff_id'] ) ) {
				if ( empty( $state['staff_token'] ) || $state['staff_token'] != SSA_Utils::hash( $state['staff_id'] ) ) {
					wp_die( __( 'Permission denied. Invalid Staff Token', 'simply-schedule-appointments' ) );
				}
			} else {
				wp_die( 'staff_id required' );
			}

			if ( empty( $state['staff_id'] ) && ! current_user_can( 'ssa_manage_site_settings' ) ) {
					wp_die( __( 'Permission denied. Only administrators can authorize the primary Google account.', 'simply-schedule-appointments' ) );
			}

			if ( ! current_user_can( 'ssa_manage_others_appointments' ) && !empty( $state['staff_id'] ) && $state['staff_id'] != ssa_get_current_staff_id() ) {
					wp_die( __( 'Permission denied. You can only link to your own Google account.', 'simply-schedule-appointments' ) );
			}
			
			// upon reconnection, we need to delete the error notice
			$this->plugin->error_notices->delete_error_notice( 'quick_connect_gcal_auth_mode_changed' );

			if ( empty( $state['staff_id'] ) ) {
				$this->plugin->google_calendar_settings->update( array(
					'enabled' => true,
					'access_token' => $this->plugin->google_calendar_client->get_exchange_response(),
				) );

				$this->auth_redirect_exit( $state, $state['wp_next_ssa_uri'] );
			} else {
				$staff = SSA_Staff_Object::instance( $state['staff_id'] );
				$staff_google_access_token = $staff->google_access_token;
				if ( empty( $staff_google_access_token ) ) {
					$staff_google_access_token = array();
				}

				$staff_google_access_token = $this->plugin->google_calendar_client->get_exchange_response();
				// the update below assumes that when the team member connects their calendar, we always start with zero excluded calendars
				$this->plugin->staff_model->update( $staff->id, array(
					'google_access_token' => $staff_google_access_token,
					'google' => [
						'connected' => true,
						'excluded_calendars' => []	
					]
				) );

				$this->auth_redirect_exit( $state, '/ssa/settings/staff/profile/' . $state['staff_id'] );
			}
			
		} else {
			return;
		}
		
	}

	/**
	 * Delete event by id
	 *
	 * @param $event_id
	 * @return bool
	 */
	public function delete( $event_id )
	{
		try {
			if ( in_array( $this->getCalendarAccess(), array( 'writer', 'owner' ) ) ) {
				$this->plugin->google_calendar_client->delete_event_from_calendar( $this->getCalendarID(), $event_id, array(
					'quotaUser' => $this->getQuotaUser(),
				) );
				return true;
			}
		} catch ( \Exception $e ) {
			$this->errors[] = $e->getMessage();
		}

		return false;
	}

	/**
	 * @return array
	 */
	public function getErrors()
	{
		return $this->errors;
	}

	/**
	 * @param Entities\Appointment $appointment
	 */
	private function handleEventData( Entities\Appointment $appointment )
	{

		$service = Entities\Service::find( $appointment->getServiceId() );
		$description  = __( 'Service', 'simply-schedule-appointments' ) . ': ' . $service->getTitle() . PHP_EOL;
		$client_names = array();
		foreach ( $appointment->getCustomerAppointments() as $ca ) {
			$description .= sprintf(
				"%s: %s\n%s: %s\n%s: %s\n",
				__( 'Name',  'simply-schedule-appointments' ), $ca->customer->getFullName(),
				__( 'Email', 'simply-schedule-appointments' ), $ca->customer->getEmail(),
				__( 'Phone', 'simply-schedule-appointments' ), $ca->customer->getPhone()
			);
			$description .= $ca->getFormattedCustomFields( 'text' ) . PHP_EOL;
			if ( $ca->getExtras() != '[]' ) {
				$appointment_extras = json_decode( $ca->getExtras(), true );
				$extras = implode( ', ', array_map( function ( $extra ) use ( $appointment_extras ) {
					/** @var \SSAServiceExtras\Lib\Entities\ServiceExtra $extra */
					$count = $appointment_extras[ $extra->getId() ];

					return ( $count > 1 ? $count . ' × ' : '' ) . $extra->getTitle();
				}, (array) Proxy\ServiceExtras::findByIds( array_keys( $appointment_extras ) ) ) );
				if ( ! empty( $extras ) ) {
					$description .= __( 'Extras', 'simply-schedule-appointments' ) . ': ' . $extras . PHP_EOL;
				}
			}
			$client_names[] = $ca->customer->getFullName();
		}

		$staff = Entities\Staff::find( $appointment->getStaffId() );

		$title = strtr( get_option( 'ssa_gc_event_title', '{service_name}' ), array(
			'{service_name}' => $service->getTitle(),
			'{client_names}' => implode( ', ', $client_names ),
			'{staff_name}'   => $staff->getFullName(),
			/** @deprecate [[CODE]] */
			'[[SERVICE_NAME]]' => $service->getTitle(),
			'[[CLIENT_NAMES]]' => implode( ', ', $client_names ),
			'[[STAFF_NAME]]'   => $staff->getFullName(),
		) );

		$this->event['start'] = ["dateTime"=> Slots\DatePoint::fromStr( $appointment->getStartDate() )->format( \DateTime::RFC3339 )];
		$this->event['end'] = ["dateTime"=> Slots\DatePoint::fromStr( $appointment->getEndDate() )->modify( (int) $appointment->getExtrasDuration() )->format( \DateTime::RFC3339 )];
		$this->event['summary'] = $title;
		$this->event['description'] = $description;

		$this->event['extendedProperties']['private'] = array(
			'customers'      => json_encode( array_map( function( $ca ) { return $ca->customer->getId(); }, $appointment->getCustomerAppointments() ) ),
			'appointment_type_id'     => $service->getAppointmentTypeId(),
			'appointment_id' => $appointment->getId(),
			'ssa_home_id' => SSA_Utils::get_home_id(),
		);

		// Set Google Meet
		$conference_data = array();
		$conference_data['conferenceId'] = 'test-id';
		$this->event['conferenceData'] = $conference_data;
	}

	/**
	 * @return string
	 */
	private function getCalendarID()
	{
		return $this->staff->getGoogleCalendarId() ?: 'primary';
	}

	/**
	 * @return string [freeBusyReader, reader, writer, owner]
	 */
	private function getCalendarAccess( $calendar_id )
	{
		if ( $this->calendar === null ) {
			$this->calendar = $this->plugin->google_calendar_client->get_calendar_from_calendar_list( $calendar_id, array(
				'quotaUser' => $this->getQuotaUser(),
			) );
		}
		return $this->calendar->accessRole;
	}

	/**
	 * Return an array of calendars in user's Google account
	 *
	 * @return array|WP_Error
	 */
	public function get_calendars_list() {
		try {
			$calendars = $this->plugin->google_calendar_client->get_calendar_list( array(
				'quotaUser' => $this->getQuotaUser(),
			));
		}
		catch ( Exception $e ) {
			return new WP_Error( $e->getCode(), $e->getMessage() );
		}

		return $calendars;
	}

	/**
	 * Gets list of attendees on Gcal event, and merge with the list of attendees generated by SSA.
	 *
	 * @param int $event_id
	 * @param array $event
	 * @param int $calendar_id
	 * @return array $event
	 */
	public function merge_event_attendees( $event_id, $event, $calendar_id ) {
		$current_event = $this->get_event( $event_id, $calendar_id );

		if( is_wp_error( $current_event ) ) {
			return $event;
		}
		
		$current_attendees = empty( $current_event->attendees ) ? [] : $current_event->attendees; // get the list of attendees already on the calendar event
		$new_event_attendees = empty( $event['attendees'] ) ? [] : $event['attendees']; // get the list of attendees from the ssa event

		// let's prepare a list of emails already added to the gcal event
		$current_attendees_emails = array_map( function( object $attendee ) {
			return $attendee->email;
		}, $current_attendees );

		foreach( $new_event_attendees as $new_attendee ) {
			// let's check if the attendee already exists
			$current_attendee_index = array_search( $new_attendee['email'], $current_attendees_emails );

			if( $current_attendee_index > -1 ) {
				// get current attendee and modify the name if necessary
				$current_attendee = $current_attendees[ $current_attendee_index ];
				$current_attendee->displayName = $new_attendee['displayName'];
				continue;
			}

			// if the attendee doesn't exists, then add it
			$current_attendees[] = $new_attendee;
		}

		/* filter assigned staff to event; To handle reassigned appointmnents */
		if ( class_exists( 'SSA_Staff' ) ) {
				
			// Prepare a list of staff emails assigned to this event
			$assigned_staff_emails = array();

			foreach ( $new_event_attendees as $key => $attendee ) {
				$staff = $this->plugin->staff_model->find_staff_by_email( $attendee['email'] );
				if ( ! empty( $staff ) ) {
					array_push( $assigned_staff_emails, $attendee['email'] );
				}
			}

			$filtered_from_unassigned_staff = array_filter( $current_attendees, function( $attendee ) use ( $assigned_staff_emails ) {

				$staff = $this->plugin->staff_model->find_staff_by_email( $attendee->email );
				
				if ( empty( $staff ) ) {
					return true;
				}

				return in_array( $attendee->email, $assigned_staff_emails ); 

			});

			$current_attendees = array_values( $filtered_from_unassigned_staff );

		}

		// ok, now we can merge back the list of attendees into the event
		$event['attendees'] = $current_attendees;

		return $event;
	}

	/**
	 * Get a Google Event
	 *
	 * @param string $event_id
	 *
	 * @return WP_Error|object $event
	 */
	public function get_event( $event_id, $calendar_id ) {
		try {
			$event = $this->plugin->google_calendar_client->get_event_from_calendar( $calendar_id, $event_id, array(
				'quotaUser' => $this->getQuotaUser(),
			) );
		}
		catch ( Exception $e ) {
			return new WP_Error( $e->getCode(), $e->getMessage() );
		}

		return $event;
	}

	/**
	 * Insert a new event
	 *
	 * @param $event
	 *
	 * @return WP_Error|array $event
	 */
	public function insert_event( $event, $calendar_id ) {
		try {
			$created_event = $this->plugin->google_calendar_client->insert_event_into_calendar( $calendar_id, $event, array(
				'quotaUser' => $this->getQuotaUser(),
				'conferenceDataVersion' => 1,
				'sendUpdates' => apply_filters( 'ssa/google_calendar/send_attendee_updates', 'all', 'insert' ),
			) );

			return $created_event->id;
		}
		catch ( Exception $e ) {
			return new WP_Error( $e->getCode(), $e->getMessage() );
		}
	}

	/**
	 * Update an event
	 *
	 * @param string $event_id
	 * @param array $event
	 *
	 * @return WP_Error|string
	 */
	public function update_event( $event_id, $event, $calendar_id ) {
		// merge existing and new list of attendees
		$event_updated = $this->merge_event_attendees( $event_id, $event, $calendar_id );
		try {
			$updated_event = $this->plugin->google_calendar_client->update_event_in_calendar( $calendar_id, $event_id, $event_updated, array(
				'quotaUser' => $this->getQuotaUser(),
				'conferenceDataVersion' => 1,
				'sendUpdates' => apply_filters( 'ssa/google_calendar/send_attendee_updates', 'all', 'update' ),
			) );
			return $updated_event->id;
		}
		catch ( Exception $e ) {
			return new WP_Error( $e->getCode(), $e->getMessage() );
		}
	}
	

	/**
	 * Update an event
	 *
	 * @param $event
	 *
	 * @return WP_Error|string
	 */
	public function delete_event( $event_id, $calendar_id ) {
		try {
			$this->plugin->google_calendar_client->delete_event_from_calendar( $calendar_id, $event_id, array(
				'quotaUser' => $this->getQuotaUser(),
			) );
			return true;
		}
		catch ( Exception $e ) {
			return new WP_Error( $e->getCode(), $e->getMessage() );
		}
	}
	
	public static function get_redirect_slug() {
		return 'ssa-auth-redirect';
	}

	public static function deprecated_get_redirect_uri() {
		return home_url( self::get_redirect_slug() );
	}

	public static function get_redirect_uri() {
		$google_calendar_settings = ssa()->google_calendar_settings->get();

		// temporary - beta - remove as gcal ssa_quick_connect feature goes out of beta testing
		$developer_settings = ssa()->developer_settings->get();

		if( $google_calendar_settings['quick_connect_gcal_mode'] || $developer_settings['quick_connect_gcal_mode'] ){
			if ( ! defined( 'SSA_QUICK_CONNECT_GCAL_AUTH_ENDPOINT' ) ) {
				ssa_debug_log( 'SSA_QUICK_CONNECT_GCAL_AUTH_ENDPOINT is not defined!' );
			}
			return SSA_QUICK_CONNECT_GCAL_AUTH_ENDPOINT;
		}

		if ( defined( 'SSA_GOOGLE_REDIRECT_URI' ) ) {
			return SSA_GOOGLE_REDIRECT_URI;
		}
		$uri = SSA_Bootstrap::maybe_fix_protocol( home_url(), 'https' );
		$uri = apply_filters( 'ssa/google_calendar/redirect_uri', $uri );

		return $uri;
	}
	public static function get_wp_callback_uri() {
		if ( defined( 'SSA_GOOGLE_WP_CALLBACK_URI' ) ) {
			return SSA_GOOGLE_WP_CALLBACK_URI;
		}

		$uri = SSA_Bootstrap::maybe_fix_protocol(home_url(), 'https');
		$uri = apply_filters( 'ssa/google_calendar/wp_callback_uri', $uri );

		return $uri;
	}

	/**
	 * Secret URL to sync appointments with no Google Calendar Event.
	 *
	 * @since 5.0.1-beta1
	 *
	 * @return void
	 */
	public function sync_with_google_calendar() {
		if ( empty( $_GET['ssa-sync-google-calendar'] ) ) {
			return;
		}

		$sync = sanitize_text_field( $_GET['ssa-sync-google-calendar'] );

		// If 'ssa-sync-google-calendar=all', then sync all appointments. 
		// Otherwise, sync only the appointment that don't have a GCal event yet.
		if ( $sync === 'all' ) {
			$this->sync_all_upcoming_appointments();
		} else {
			$this->sync_upcoming_appointments_missing_gcal_events();
		}

		wp_safe_redirect( $this->plugin->wp_admin->url(), $status = 302 );
		exit;
	}

	/**
	 * Only syncs future booked appointments that have never been synced before 
	 *
	 * @return void
	 */
	public function sync_upcoming_appointments_missing_gcal_events() {
		$appointments = $this->plugin->appointment_model->query(
			array(
				'status'                   => array( 'booked' ),
				'start_date_min'           => gmdate( 'Y-m-d H:i:s' ),
				'number'                   => -1,
				'google_calendar_event_id' => ''
			)
		);

		if ( empty( $appointments ) ) {
			return;
		}

		$this->bulk_schedule_google_calendar_sync( $appointments );
	}

	/**
	 *  Syncs ALL future booked appointments even if they were previously synced
	 *
	 * @return void
	 */
	public function sync_all_upcoming_appointments() {
		$appointments = $this->plugin->appointment_model->query(
			array(
				'status'                   => array( 'booked' ),
				'start_date_min'           => gmdate( 'Y-m-d H:i:s' ),
				'number'                   => -1,
			)
		);

		if ( empty( $appointments ) ) {
			return;
		}

		$this->bulk_schedule_google_calendar_sync( $appointments );
	}

	/**
	 * Checks whether certain fields in an appointment have been changed.
	 * Especially needed to avoid duplicate api calls when the appoitment is first booked
	 *
	 * This function compares the values of certain fields in two versions of an appointment.
	 * If any of these fields have changed, the function returns true; otherwise, it returns false.
	 * If the function cannot determine whether a sync is needed or not, it returns null.
	 * 
	 * @param array $data The updated version of the appointment. Default is an empty array.
	 * @param array $data_before The original version of the appointment. Default is an empty array.
	 * 
	 * @return bool True if undeterminable or any of the fields have changed, false if none of the fields have changed.
	 */
	private function should_sync_appointment_after_update( $data = array(), $data_before = array() ) {
		if ( empty ( $data ) || empty( $data_before ) ) {
			return true; // Not an update; nothing we can check
		}
		// Fields that when changed we must resync to calendar
		$fields_we_care_about = array(
			'rescheduled_to_appointment_id', // We need this to make sure that after rescheduling the canceled one is properly handled
			'customer_information',
			'start_date',
			'end_date',
			'title',
			'description',
			'payment_method',
			'payment_received',
			'web_meeting_password',
			'web_meeting_id',
			'web_meeting_url',
			'status',
			'public_edit_url',
			'public_token',
			'staff_ids',
		);

		// Now lets check if any of these fields have changed
		foreach( $fields_we_care_about as $field ) {
			if ( array_key_exists( $field, $data ) && array_key_exists( $field, $data_before ) ) {
				if ( $data[$field] !== $data_before[$field] ) {
					// A field we care about has changed
					return true;
				}
			}
		}

		// If we reached here, it means no fields we care about has changed
		return false;
	}

}
