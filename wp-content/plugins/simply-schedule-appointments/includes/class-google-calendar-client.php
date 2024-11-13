<?php
/**
 *
 * @since   6.6.5
 * @package Simply_Schedule_Appointments
 * 
 */

 class SSA_Google_Calendar_Client {
	
	private $access_token = false;
	
	private $client_id = false;
	
	private $client_secret = false;
	
	private $redirect_uri = false;
	
	private $quotaUser = null;
	
	
	/**
	 * Parent plugin class.
	 *
	 * @since 0.6.0
	 *
	 * @var   Simply_Schedule_Appointments
	 */
	protected $plugin = null;
	
	protected $staff_id = 0;
	
	/**
	 * @since 6.6.5
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();
	}
	
	/**
	 * Initiate our hooks.
	 *
	 * @since  0.6.0
	 */
	public function hooks() {
		//
	}
	
	public function client_init() {
		if ( !empty($this->client_id) || !empty($this->client_secret) ) {
			return $this;
		}
		
		$google_calendar_settings = $this->plugin->google_calendar_settings->get();

		// temporary - beta - remove as gcal ssa_quick_connect feature goes out of beta testing
		$developer_settings = $this->plugin->developer_settings->get();

		// Only initialize if we're not using the new ssa_quick_connect auth flow
		// any method besides this one should access the client_id and client_secret directly on this class
		if( !$google_calendar_settings['quick_connect_gcal_mode'] && !$developer_settings['quick_connect_gcal_mode'] ){
			$this->client_id = $this->plugin->google_calendar->get_client_id();
			$this->client_secret = $this->plugin->google_calendar->get_client_secret();
		} else {
			// if ssa_quick_connect enabled get our own client_id
			if( !defined( 'SSA_QUICK_CONNECT_GCAL_CLIENT_ID' ) ){
				ssa_debug_log( 'SSA_QUICK_CONNECT_GCAL_CLIENT_ID not defined!', 10 );
				return false;
			}
			$this->client_id = SSA_QUICK_CONNECT_GCAL_CLIENT_ID;
		}
		
		return $this;
	}

	public function service_init( $staff_id = 0 ) {
		$client = (new self( $this->plugin ))->client_init();
		$client->staff_id = $staff_id;
		$client->authorize();
		return $client;
	}
	
	/**
	 * Call this to authorize the client
	 * updates the access token in the settings as well
	 * 
	 * @since 6.6.5
	 * 
	 * @return void
	 */
	private function authorize() {
		$staff_access_token = $this->get_access_token_for_staff_id();
		if( $staff_access_token != $this->access_token ) {
			$this->access_token = $staff_access_token;
		}
		
		// check also if the access token is the correct one
		if( !$this->is_access_token_expired( $this->access_token ) ) {
			// no need to refresh access token
			return;
		}
		
		// if quick connect enabled, get quick connect access token
		
		$google_calendar_settings = $this->plugin->google_calendar_settings->get();
		// temporary - beta - remove as gcal ssa_quick_connect feature goes out of beta testing
		$developer_settings = $this->plugin->developer_settings->get();
		
		$google_quick_connect_gcal_mode = ( $google_calendar_settings['quick_connect_gcal_mode'] == true ) || ( $developer_settings['quick_connect_gcal_mode'] == true );
		
		if(  true == $google_quick_connect_gcal_mode ){
			$this->authorize_with_quick_connect( $this->staff_id );
		} else {
			$this->authorize_with_client_id_and_secret();
		}

		if ( empty( $this->access_token ) ) {
			ssa_debug_log( 'missing_access_token for staff id '.$this->staff_id, 10 );
			return;
		}
		
		// if still expired
		if( $this->is_access_token_expired( $this->access_token ) ) {
			ssa_debug_log( 'expired_access_token for staff id '.$this->staff_id, 10 );
			ssa_debug_log( ssa_get_stack_trace(), 10 );
			throw new Exception( 'Failed to authorize with Google Calendar' );
		}
	}
	
	private function authorize_with_client_id_and_secret() {
		$access_token = $this->get_access_token_for_staff_id();
		if( $this->is_access_token_expired( $access_token ) ) {
			$this->access_token = $this->refresh_access_token( $access_token );
			$this->update_token_in_database();
		} else {
			$this->access_token = $access_token;
		}
	}
	
	private function get_access_token_for_staff_id() {
		// get access token from settings
		if( empty( $this->staff_id ) ) {
			$google_calendar_settings = $this->plugin->google_calendar_settings->get();
			return $google_calendar_settings['access_token'];
		} else {
			$staff = SSA_Staff_Object::instance( $this->staff_id );
			return $staff->google_access_token;
		}
	}
	/**
	 * Quick Connect is assumed to always return a valid access token
	 * This should shortcut the method that sets the access token and just set the access token directly
	 *
	 * @param [type] $staff_id
	 * @return void
	 */
	private function authorize_with_quick_connect() {
		$this->access_token = $this->plugin->google_calendar->get_quick_connect_access_token( $this->staff_id );
		// no need to update the token in database, because get_quick_connect_access_token handles that
	}
	
	private function get_request_headers( ){
		if( empty( $this->access_token ) ) {
			$this->authorize();
		}
		$headers =  array(
				'Content-Type' => 'application/json',
				'Authorization' => 'Bearer ' . $this->access_token['access_token'],
		);
		return $headers;
	}
	
	/**
	 * Test and confirm that the access token
	 * Makes an API call and confirms that the access token is valid
	 *
	 * @param array $options
	 * @return void
	 */
	public function validate_access_token( array $access_token ) {
		$gcal_api_endpoint = 'https://www.googleapis.com/calendar/v3/users/me/calendarList';
		
		$response = wp_remote_get(
			$gcal_api_endpoint,
			array(
				'headers' => array(
					'Content-Type' => 'application/json',
					'Authorization' => 'Bearer ' . $access_token['access_token'],
				),
				'timeout' => 60
			)
		);
		
		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) > 299 ) {
			ssa_debug_log( print_r( $response, true ), 10); // phpcs:ignore
			throw new Exception( 'Failed to validate Google Calendar access token' );
		}
		
		return true;
	}
	/**
	 * use in place of ->calendarList->listCalendarList( $options = array() ) {}
	 * this method will return all calendars, not just the first page
	 * 
	 * @since 6.6.5
	 * 
	 * @return array
	 */
	public function get_calendar_list( $options = array() ) {
		$calendar_list = array();
		$gcal_api_endpoint = 'https://www.googleapis.com/calendar/v3/users/me/calendarList' . '?' . $this->get_params_from_options( $options );
		$current_endpoint = $gcal_api_endpoint;
		
		// get all pages of calendar list
		while(true){
			try {
				$response = wp_remote_get(
					$current_endpoint,
					array(
						'headers' => $this->get_request_headers(),
						'timeout' => 60
					)
				);
				
				if ( is_wp_error($response) || wp_remote_retrieve_response_code($response) > 299 ) {
					ssa_debug_log( print_r( $response, true ), 10); // phpcs:ignore
					return false;
				}
				
				$data = json_decode( wp_remote_retrieve_body( $response ) );
				
				// add calendar list to array
				$calendar_list = array_merge( $calendar_list, $data->items );
				
				if(empty($data->items)){
					ssa_debug_log( 'No calendars found in calendar list', 10 );
					ssa_debug_log( print_r( $response, true ), 10); // phpcs:ignore
				}
				
				if ( empty( $data->nextPageToken ) ) {
					break;
				} else {
					$current_endpoint = $gcal_api_endpoint . '&pageToken=' . $data->nextPageToken;
				}
			} catch ( \Throwable $th ) {
				ssa_debug_log( print_r( $th, true ), 10 ); // phpcs:ignore
				break;
			}
		}
		
		// Success
		// return calendar list
		return $calendar_list;
	}
	
	/**
	 * 
	 * use in place of ->calendarList->get( $calendar_id, $options = array() ) {}
	 */
	public function get_calendar_from_calendar_list ( $calendar_id, $options = array() ) {
		$gcal_api_endpoint = "https://www.googleapis.com/calendar/v3/users/me/calendarList/" . urlencode( $calendar_id ) . "?" . $this->get_params_from_options( $options );
		try {
			$response = wp_remote_get(
				$gcal_api_endpoint,
				array(
					'headers' => $this->get_request_headers(),
					'timeout' => 60
				)
			);
			
			// we don't want to log 404 errors, because we expect them if the calendar is not found
			if ( is_wp_error( $response ) || ( wp_remote_retrieve_response_code( $response ) > 299 && wp_remote_retrieve_response_code( $response ) != 404 ) ) {
				ssa_debug_log( print_r( $response, true ), 10 ); // phpcs:ignore
				return false;
			}
			
			$data = json_decode( wp_remote_retrieve_body( $response ) );
			// Success
			return $data;
		} catch ( \Throwable $th ) {
			ssa_debug_log( print_r( $th, true ), 10 ); // phpcs:ignore
			return false;
		}
	}
	
	/**
	 * use in place of ->events->listEvents( $calendar_id, $options = array() ) {}
	 
	 */
	public function get_events_from_calendar( $calendar_id, $options = array() ) {
		// if is a holiday caledar, pull events in english locale so that we have a way to identiy public holidays
		if( false !== strpos( $calendar_id, 'holiday' ) ){
			$calendar_id_parts = explode( '.', $calendar_id );
			array_shift( $calendar_id_parts );
			$calendar_id = 'en.' . implode( '.', $calendar_id_parts );
		}
		
		// exclude workingLocation events - these are not useful for availability
		$event_types_query = 'eventTypes=default&eventTypes=outOfOffice&eventTypes=focusTime&eventTypes=fromGmail';
		
		$gcal_api_endpoint = "https://www.googleapis.com/calendar/v3/calendars/" . urlencode( $calendar_id ) . "/events?" . $event_types_query . '&' . $this->get_params_from_options( $options );

		try {
			$response = wp_remote_get(
				$gcal_api_endpoint,
				array(
					'headers' => $this->get_request_headers(),
					'timeout' => 60
				)
			);

			if ( is_wp_error($response) || wp_remote_retrieve_response_code($response) > 299 ) {
				if( wp_remote_retrieve_response_code($response) == 404 ){
					ssa_debug_log( 'Received 404, getting events for ' . $calendar_id . " from " . $gcal_api_endpoint . " working with staff id " . $this->staff_id ); // phpcs:ignore
					ssa_debug_log( ssa_get_stack_trace(), 10 );
				} else {
					ssa_debug_log( print_r( $response, true ), 10 ); // phpcs:ignore
				}
				return [];
			}
			
			$data = json_decode( wp_remote_retrieve_body( $response ) );
			
			// Success
			return $data->items;
		} catch ( \Throwable $th ) {
			ssa_debug_log( print_r( $th, true ), 10 ); // phpcs:ignore
			return [];
		}
	}
	
	/**
	 * 
	 * use in place of ->events->insert( $calendar_id, $event, $options = array() ) {}
	 * 
	 */
	public function insert_event_into_calendar( $calendar_id, $event, $options = array() ) {
		$gcal_api_endpoint = "https://www.googleapis.com/calendar/v3/calendars/" . urlencode( $calendar_id ) . "/events?" . $this->get_params_from_options( $options );
		
		try {
			$response = wp_remote_post(
				$gcal_api_endpoint,
				array(
					'headers' => $this->get_request_headers(),
					'timeout' => 60,
					'body' => json_encode($event),
				)
			);
			
			if ( is_wp_error($response) || wp_remote_retrieve_response_code($response) > 299 ) {
				ssa_debug_log( print_r( $response, true ), 10 ); // phpcs:ignore
				return false;
			}
			
			$event = json_decode(wp_remote_retrieve_body($response) );
			
			// Success
			// return event ID
			return $event;
		} catch ( \Throwable $th ) {
			ssa_debug_log( print_r( $th, true ), 10 ); // phpcs:ignore
			return false;
		}
	}
	
	
	/**
	 * 
	 * use in place of ->events->get( $calendar_id, $event_id, $options = array() ) {}
	 * 
	 */
	public function get_event_from_calendar( $calendar_id, $event_id, $options = array() ) {
		if(empty($calendar_id) || empty($event_id)){
			ssa_debug_log( 'Warning: called get_event_from_calendar with calendar_id:' . $calendar_id . ' & event_id:' . $event_id , 10 );
			return false;
		}
		$gcal_api_endpoint = "https://www.googleapis.com/calendar/v3/calendars/" . urlencode( $calendar_id ) . "/events/" . $event_id . "?" . $this->get_params_from_options( $options );
		
		try {
			$response = wp_remote_get(
				$gcal_api_endpoint,
				array(
					'headers' => $this->get_request_headers(),
					'timeout' => 60
				)
			);
			
			if ( is_wp_error($response) || wp_remote_retrieve_response_code($response) > 299 ) {
				ssa_debug_log( print_r( $response, true ), 10 ); // phpcs:ignore
				return false;
			}
			
			$data = json_decode(wp_remote_retrieve_body($response) );
			
			// Success
			return $data;
		} catch ( \Throwable $th ) {
			ssa_debug_log( print_r( $th, true ), 10 ); // phpcs:ignore
			return false;
		}
	}
	
	
	/**
	 * 
	 * use in place of ->events->update( $calendar_id, $event_id, $event_updated, $options = array() ) { }
	 */
	public function update_event_in_calendar( $calendar_id, $event_id, $event_updated, $options = array() ) {
		$gcal_api_endpoint = "https://www.googleapis.com/calendar/v3/calendars/" . urlencode( $calendar_id ) . "/events/" . $event_id . "?" . $this->get_params_from_options( $options );
		
		try {
			$response = wp_remote_request(
				$gcal_api_endpoint,
				array(
					'headers' => $this->get_request_headers(),
					'timeout' => 60,
					'body' => json_encode( $event_updated ),
					'method'    => 'PUT'
				)
			);
			
			if ( is_wp_error($response) || wp_remote_retrieve_response_code($response) > 299 ) {
				ssa_debug_log( print_r( $response, true ), 10 ); // phpcs:ignore
				return false;
			}
			
			$data = json_decode(wp_remote_retrieve_body($response) );
		
			// Success
			return $data;
		} catch ( \Throwable $th ) {
			ssa_debug_log( print_r( $th, true ), 10 ); // phpcs:ignore
			return false;
		}
	}
	
	
	/**
	 * use in place of ->events->delete( $calendar_id, $event_id, $options = array() ) {}
	 */
	public function delete_event_from_calendar( $calendar_id, $event_id, $options = array() ) {
		$gcal_api_endpoint = "https://www.googleapis.com/calendar/v3/calendars/" . urlencode( $calendar_id ) . "/events/" . $event_id . "?" . $this->get_params_from_options( $options );
		
		try {
			$response = wp_remote_request(
				$gcal_api_endpoint,
				array(
					'headers' => $this->get_request_headers(),
					'timeout' => 60,
					'method'    => 'DELETE'
				)
			);
			
			if ( is_wp_error($response) || wp_remote_retrieve_response_code($response) > 299 ) {
				ssa_debug_log( print_r( $response, true ), 10 ); // phpcs:ignore
				return false;
			}
			
			// Success
			// the delete method returns an empty body
			return true;
		} catch ( \Throwable $th ) {
			ssa_debug_log( print_r( $th, true ), 10 ); // phpcs:ignore
			return false;
		}
	}
	
	/**
	 * description: this is the same logic used by the PHP OAuth client
	 * with a minor difference, this takes the $token as an argument
	 * 
	 * @param array $token
	 * @return bool Returns True if the access_token is expired.
	 */
	public function is_access_token_expired( $token ) {
		if ( !$token ) {
			return true;
		}
		
		if ( is_object( $token ) ) {
			$token = (array) $token;
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
		} else {
			// id_token is not available, so we can't check the "iat"
			// check using api response
			try {
				$valid = $this->validate_access_token( $token );
				if( $valid ){
					return false;
				}
			} catch (\Throwable $th) {
				// we're inside of a method that only checks if the token is expired
				return true;
			}
		}

		// If the token is set to expire in the next 30 seconds.
		return ( $created + ( $token['expires_in'] - 30 ) ) < time();
	}
	
	/**
	 * Exchange the refresh token for an access token
	 *
	 * @param string $client_id
	 * @param string $client_secret
	 * @param string $refresh_token
	 * @return bool|array
	 */
	private function exchange_refresh_token( $client_id, $client_secret, $refresh_token ){
		$gcal_api_endpoint = 'https://www.googleapis.com/oauth2/v4/token';
		
		try {
			$response = wp_remote_post(
				$gcal_api_endpoint,
				array(
					'body' => array(
						'refresh_token' => $refresh_token,
						'client_id' => $client_id,
						'client_secret' => $client_secret,
						'grant_type' => 'refresh_token',
						// return also the refresh token
						'access_type' => 'offline',
					),
				)
			);

			if ( is_wp_error($response) || wp_remote_retrieve_response_code($response) > 299 ) {
				ssa_debug_log( print_r( $response, true ), 10 ); // phpcs:ignore
				return false;
			}
			
			$data = json_decode(wp_remote_retrieve_body($response), true);
			
			if( empty( $data['refresh_token'] ) ) {
				// attach the refresh token to the access token
				$data['refresh_token'] = $refresh_token;
			}
			
			// Success
			return $data;
		} catch ( \Throwable $th ) {
			ssa_debug_log( print_r( $th, true ), 10 ); // phpcs:ignore
			return false;
		}
	}
	
	/**
	 * We never call this with the quick connect flow, because we don't have a refresh token
	 *
	 * @return void
	 */
	private function refresh_access_token($access_token) {
		$client_id = $this->client_id;
		$client_secret = $this->client_secret;
		$refresh_token = $access_token['refresh_token'];
		$response = $this->exchange_refresh_token( $client_id, $client_secret, $refresh_token );
		if( empty( $response ) || ! is_array( $response ) || empty( $response['access_token'] ) ) {
			ssa_debug_log( 'Failed to refresh access token for staff id ' . (string) $this->staff_id . print_r($response, true), 10); // phpcs:ignore
			throw new Exception( 'Failed to refresh access token' );
		}
		return $response;
	}
	
	private function update_token_in_database(){
		$staff_id = $this->staff_id;
		$access_token = $this->access_token;
		if(empty($staff_id)){
			if(empty($access_token['refresh_token'])){
				// log that we received an access token without a refresh token
				ssa_debug_log('Received an access token without a refresh token ' . ' for staff id ' . (string) $staff_id . print_r($access_token, true), 10 ); // phpcs:ignore
				$google_calendar_settings = $this->plugin->google_calendar_settings->get();
				$access_token['refresh_token'] = !empty($google_calendar_settings['access_token']['refresh_token']) ? $google_calendar_settings['access_token']['refresh_token'] : '';
			}
			$this->plugin->google_calendar_settings->update( array( 'access_token' => $access_token ) );
		} else {
			if(empty($access_token['refresh_token'])){
				// log that we received an access token without a refresh token
				ssa_debug_log('Received an access token without a refresh token ' . ' for staff id ' . (string) $staff_id . print_r($access_token, true), 10 ); // phpcs:ignore
				$staff = $this->plugin->staff_model->get( $staff_id );
				$access_token['refresh_token'] = !empty($staff['google_access_token']['refresh_token']) ? $staff['google_access_token']['refresh_token'] : '';
			}
			$this->plugin->staff_model->update( $staff_id, array(
				'google_access_token' => $access_token,
			) );
		}
	}
	
	public function get_auth_url( $staff_id, $wp_next_ssa_uri = null, $wp_next_base_uri = null ) {
		$this->client_init();
		$gcal_api_endpoint = 'https://accounts.google.com/o/oauth2/auth?';
		// need to store the exact home url returned at this point
		// because some plugins can affect the home url, causing the quick-connect domain to be invalid
		$site_home_url = get_home_url();
		$this->plugin->google_calendar_settings->update( array(
			'quick_connect_home_url' => $site_home_url,
		) );
		
		$license_settings =	$this->plugin->license_settings->get();
		$license = '';
		
		// https://accounts.google.com/o/oauth2/auth?
		$params = array(
			'response_type'=>'code',
			'client_id'=> $this->client_id,
			'redirect_uri'=> $this->plugin->google_calendar->get_redirect_uri(),
			'scope'=> 'https://www.googleapis.com/auth/calendar openid',
			'approval_prompt'=>'force',
			'access_type'=>'offline',
		);
		
		if ( empty( $wp_next_ssa_uri ) ) {
			$wp_next_ssa_uri = 'ssa/settings/google-calendar';
		}
		if ( empty( $wp_next_base_uri ) ) {
			$wp_next_base_uri = $this->plugin->wp_admin->url();
		}
		
		$params['state'] = strtr( base64_encode( json_encode( array(
			'authorize' => 'google',
			'staff_id' => $staff_id,
			'staff_token' => SSA_Utils::hash( $staff_id ),
			'token' => $license,
			'redirect_uri' => $this->plugin->google_calendar->get_redirect_uri(),
			'wp_callback_uri' => $this->plugin->google_calendar::get_wp_callback_uri(),
			'wp_next_ssa_uri' => $wp_next_ssa_uri,
			'wp_next_base_uri' => $wp_next_base_uri, // grab from the parent page (example: /my-account/), like we do for booking_url in booking-app
			// used for ssa_quick_connect - staff_id as well
			'domain' => $site_home_url,
			'license_key'=> $license_settings['license_filtered'],
		) ) ), '+/=', '-_,' );
		
		return $gcal_api_endpoint . $this->get_params_from_options( $params );
	}
	
	/**
	 * 
	 * @since 6.6.5
	 * 
	 * @return bool
	 
	 */
	public function exchange_auth_code( $code ) {
		$this->client_init();
		$gcal_api_endpoint = 'https://www.googleapis.com/oauth2/v4/token?';
		$params = array(
			'code' => $code,
			'grant_type' => 'authorization_code',
			'client_id' => $this->client_id,
			'client_secret' => $this->client_secret,
			'redirect_uri' => $this->plugin->google_calendar->get_redirect_uri(),
		);
		
		
		try {
			$response = wp_remote_post( $gcal_api_endpoint . $this->get_params_from_options( $params ));
			
			if ( is_wp_error($response) || wp_remote_retrieve_response_code($response) > 299 ) {
				throw new \Throwable( $response );
			}
			
			$data = json_decode(wp_remote_retrieve_body($response), true);
			
			$this->access_token = $data;

			return true;
		} catch ( \Throwable $th ) {
			ssa_debug_log( print_r( $th, true ), 10 ); // phpcs:ignore
			return false;
		}

	}
	
	public function get_exchange_response() {
		return $this->access_token;
	}
	
	public function get_access_token()
	{
	  return $this->access_token;
	}
	
	public function get_params_from_options ( $options ) {
		if( empty( $options ) ) {
			return '';
		}
		$params_string = '';
		// avoid $this->get_params_from_options( $options ); it will convert true to 1, and google api does not like that
		foreach( $options as $key => $value ) {
			// if boolean replace with string equivalent
			if( is_bool( $value ) ) {
				$value = $value ? 'true' : 'false';
			}
			$params_string .= http_build_query([$key => $value]) . '&';
		}
		return $params_string;
	}
}
