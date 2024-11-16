<?php
/**
 * Simply Schedule Appointments Zoom.
 *
 * @since   3.7.1
 * @package Simply_Schedule_Appointments
 */

use \Firebase\JWT\JWT;

/**
 * Simply Schedule Appointments Zoom.
 *
 * @since 3.7.1
 */
class SSA_Zoom {
	/**
	 * Parent plugin class.
	 *
	 * @since 3.7.1
	 *
	 * @var   Simply_Schedule_Appointments
	 */
	protected $plugin = null;

	/**
	 * API endpoint base
	 *
	 * @var string
	 */
	private $api_url = 'https://api.zoom.us/v2/';
	
	/**
	 * Constructor.
	 *
	 * @since  3.7.1
	 *
	 * @param  Simply_Schedule_Appointments $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  3.7.1
	 */
	public function hooks() {
		add_action( 'rest_api_init', array( $this, 'init' ) );
		add_action( 'init', array( $this, 'catch_oauth_callback' ), 1 );

		add_action( 'ssa/appointment/booked', array( $this, 'queue_maybe_create_meeting' ), 100, 1 );
		add_action( 'ssa_zoom_create_meeting', array( $this, 'maybe_create_meeting' ), 10, 2 );

	}

	public function init() {
		$this->register_routes();
	}
	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
		$version = '1';
		$namespace = 'ssa/v' . $version;
		$base = 'zoom';

		register_rest_route( $namespace, '/' . $base . '/redirect_url', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'redirect_url_request' ),
				'permission_callback' => array( 'TD_API_Model', 'logged_in_permissions_check' ),
			),
		) );

		register_rest_route( $namespace, '/' . $base . '/authorize_url', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'authorize_url_request' ),
				'permission_callback' => array( 'TD_API_Model', 'logged_in_permissions_check' ),
			),
		) );

		register_rest_route( $namespace, '/' . $base . '/disconnect', array(
			array(
				'methods'         => WP_REST_Server::CREATABLE,
				'callback'        => array( $this, 'disconnect' ),
				'permission_callback' => array( 'TD_API_Model', 'logged_in_permissions_check' ),
			),
		) );

		register_rest_route( $namespace, '/' . $base . '/me', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_user_data' ),
				'permission_callback' => array( 'TD_API_Model', 'logged_in_permissions_check' ),
			),
		) );
	}

	/**
	 * Returns Zoom Redirect url.
	 *
	 * @param WP_REST_Request $request
	 * @return void
	 */
	public function redirect_url_request( WP_REST_Request $request ) {
		$redirect_url = $this->get_redirect_url();

		return new WP_REST_Response( $redirect_url, 200 );
	}

	public function get_redirect_url( $base_url = null, $state = null ) {
		if ( ! $base_url ) {
			$redirect_url = get_home_url( null, '?ssa-auth=zoom', 'https' );
		} else {
			$redirect_url = add_query_arg(
				array(
					'ssa-auth'     => 'zoom',
					'ssa_state'    => $state,
					'ssa_redirect' => $base_url,
				),
				get_home_url()
			);
		}
		return $redirect_url;
	}

	public function get_marketplace_redirect_uri() {
		return 'https://simplyscheduleappointments.com/authorize/zoom/';
	}

	/**
	 * Returns Zoom Oauth url.
	 *
	 * @param WP_REST_Request $request
	 * @return void
	 */
	public function authorize_url_request( WP_REST_Request $request ) {
		$params = $request->get_params();
		$auth_url = 'https://zoom.us/oauth/authorize';

		$settings = $this->plugin->zoom_settings->get();

		$base_url = $params['ssa_base_url'] ? $params['ssa_base_url'] : null;
		$state    = $params['ssa_redirect'] ? $params['ssa_redirect'] : null;

		$site_redirect_url = $this->get_redirect_url( $base_url, $state );

		$parsed_url = add_query_arg( array(
			'response_type' => 'code',
			'client_id'     => $this->get_client_id(),
			'state'         => base64_encode( json_encode( array(
				'authorize' => 'zoom',
				'site_url' => get_home_url(),
				'site_redirect_url' => $site_redirect_url,
			) ) ),
			'redirect_uri'  => $this->get_marketplace_redirect_uri(),
		), $auth_url );

		return new WP_REST_Response( array(
			'response_code' => 200,
			'error' => '',
			'data' => array(
				'authorize_url' => $parsed_url,
			),
		), 200 );
	}	

	/**
	 * Verifies if the url is an oauth redirection and runs the appropriate code.
	 *
	 * @return void
	 */
	public function catch_oauth_callback() {
		if( isset( $_GET['ssa-auth'] ) && $_GET['ssa-auth'] == 'zoom' ) {
			if ( $this->plugin->settings_installed->is_enabled( 'zoom' ) ) {
				$code     = isset( $_GET['code'] ) ? $_GET['code'] : null;
				$state    = isset( $_GET['ssa_state'] ) ? $_GET['ssa_state'] : null;
				$redirect = isset( $_GET['ssa_redirect'] ) ? $_GET['ssa_redirect'] : null;
				$this->connect( $code, $state, $redirect );
			}
		}
	}

	/**
	 * Grabs the Zoom Oauth authorization code and stores on the plugin settings.
	 *
	 * @param string $code
	 * @param string $state
	 * @param string $redirect
	 * @return void
	 */
	public function connect( $code = null, $state = null, $redirect = null ) {
		if ( $redirect ) {
			$redirect_url = add_query_arg(
				array(
					'ssa_state' => $state,
				),
				$redirect
			);
		} else {
			$redirect_url = $this->plugin->wp_admin->url( 'ssa/settings/zoom' );
		}

		$settings = $this->plugin->zoom_settings->get();

		$settings['auth_code'] = $code;
		$this->plugin->zoom_settings->update( $settings );

		// performs a redirect to the plugin url.
		wp_redirect( $redirect_url );
		exit();
	}

	/**
	 * Disconnects Zoom account.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function disconnect( WP_REST_Request $request ){
		$settings = $this->plugin->zoom_settings->get();

		$settings['auth_code'] = null;
		$settings['access_token'] = null;
		$settings['access_token_expires'] = null;
		$this->plugin->zoom_settings->update( $settings );

		return new WP_REST_Response( array(
			'response_code' => 200,
			'error' => '',
			'data' => $this->plugin->zoom_settings->get(),
		), 200 );
	}

	/**
	 * Pulls the Zoom Access Token from the database. Requests one if we don't have it, or refreshes it if it's expired.
	 *
	 * @return string
	 */
	public function get_access_token() {
		$settings = $this->plugin->zoom_settings->get();
		// $settings['access_token'] = null;
		// $settings['access_token_expires'] = null;
		// $settings['auth_code'] = null;
		// $settings = $this->plugin->zoom_settings->update($settings);
		// wp_die();
		$access_token = $settings['access_token'];
		// if we don't have any access token, request one and refresh the settings
		if( empty( $access_token ) || strpos( $access_token, 'invalid_request' ) ) {
			$this->request_access_token();
			$settings = $this->plugin->zoom_settings->get();
		}

		// decode the token stored on the database
		$access_token = json_decode( $settings['access_token'], true );

		$access_token_expires = $settings['access_token_expires'];
		$access_token_expires = strtotime( $access_token_expires );
		$one_hour_ago = time() - HOUR_IN_SECONDS;

		// if the token is expired, then we refresh and store the new token
		if( $access_token_expires <= time() ) {
			$access_token = $this->refresh_access_token();

			if( $access_token ) {
				$access_token = json_decode( $access_token, true );

				return $access_token['access_token'];
			} else {
				return null;
			}
		} else {
			return $access_token['access_token'];
		}
	}

	public function get_client_id() {
		if ( defined( 'SSA_ZOOM_CLIENT_ID' ) && SSA_ZOOM_CLIENT_ID ) {
			return SSA_ZOOM_CLIENT_ID;
		}

		$client_id = 'HOl8RrYwQ4iDJwB516STg';
		$client_id = apply_filters( 'ssa/zoom/client_id', $client_id );
		
		return $client_id;
	}

	public function get_auth_code() {
		if ( defined( 'SSA_ZOOM_AUTH_CODE' ) && SSA_ZOOM_AUTH_CODE ) {
			return SSA_ZOOM_AUTH_CODE;
		}

		$zoom_auth_code = 'SE9sOFJyWXdRNGlESndCNTE2U1RnOmNyU1c2ZHc4VHBIYWZwSjdYMkRodHVUd0ZaRGp4TG1S'; // base64 encoded client_id:client_secret
		$zoom_auth_code = apply_filters( 'ssa/zoom/auth_code', $zoom_auth_code );
		return $zoom_auth_code;

		// If you are trying to override this with your own custom Zoom app, you need to generate the auth code like below.

		// $client_id = $this->get_client_id();
		// $client_secret = 'client_secret_goes_here';
		// $client_id_and_secret = $client_id.':'.$client_secret;
		// $auth_code = base64_encode( $client_id_and_secret );
	}

	/**
	 * If we don't have any Zoom Access Token, this function retrieves the first one.
	 *
	 * @return string
	 */
	public function request_access_token() {
		$settings = $this->plugin->zoom_settings->get();
		$code = $settings['auth_code'];
		$auth_code = $this->get_auth_code();
		$oauth_url = 'https://zoom.us/oauth/token';

		$request = wp_remote_post( $oauth_url, array(
			'headers' => array(
				'Authorization' => "Basic $auth_code",
				'Content-Type' => 'application/x-www-form-urlencoded',
			),
			'body' => array(
				'grant_type'   => 'authorization_code',
				'code'         => $code,
				'redirect_uri' => $this->get_marketplace_redirect_uri(),
			)
		) );

		if( !is_wp_error( $request ) ) {
			$token = wp_remote_retrieve_body( $request );
			$decoded = json_decode( $token, true );
			$expires = $decoded['expires_in'];
			$expires_formatted = date( "Y-m-d H:i:s", strtotime( "+$expires seconds" ) );
			$settings['access_token'] = $token;
			$settings['access_token_expires'] = $expires_formatted;
			$this->plugin->zoom_settings->update( $settings );

			return $token;
		} else {
			ssa_debug_log( 'request_access_token() error' );
		}

		return null;
	}

	/**
	 * If we a Zoom Access Token expires, this function refreshes the token.
	 *
	 * @return string
	 */
	public function refresh_access_token() {
		if ( get_transient( 'ssa/zoom/lock/refresh_access_token' ) ) {
			sleep( 10 );
			$settings = $this->plugin->zoom_settings->get();
			return $settings['access_token'];
		}

		set_transient( 'ssa/zoom/lock/refresh_access_token', true, 10 );
		$settings = $this->plugin->zoom_settings->get();
		$access_token = json_decode( $settings['access_token'], true );
		$auth_code = $this->get_auth_code();

		$oauth_url = 'https://zoom.us/oauth/token';

		$request = wp_remote_post( $oauth_url, array(
			'headers' => array(
				'Authorization' => "Basic $auth_code",
			),
			'body' => array(
				'grant_type'    => 'refresh_token',
				'refresh_token' => $access_token['refresh_token'],
			)
		) );

		if( !is_wp_error( $request ) ) {
			$token = wp_remote_retrieve_body( $request );
			$decoded = json_decode( $token, true );
			$expires = $decoded['expires_in'];
			$expires_formatted = date( "Y-m-d H:i:s", strtotime( "+$expires seconds" ) );

			$settings['access_token'] = $token;
			$settings['access_token_expires'] = $expires_formatted;
			$this->plugin->zoom_settings->update( $settings );
			set_transient( 'ssa/zoom/lock/refresh_access_token', false, 1 );

			return $token;
		}

		return null;
	}

	/**
	 * Pulls basic authorized user information.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get_user_data( WP_REST_Request $request ) {
		$user = $this->send_request('users/me', array());

		if( $user ) {
			return new WP_REST_Response( array(
				'first_name'  => $user->first_name,
				'last_name'   => $user->last_name,
				'email'       => $user->email,
				'meeting_url' => $user->personal_meeting_url,
				'pic_url'     => isset( $user->pic_url ) ? $user->pic_url : null,
			), 200 );
		}

		return new WP_REST_Response( __( 'Zoom user not found.' ), 404 );
	}

	public function get_users() {
		$users = $this->send_request( 'users', array() );
		return $users;
	}

	public function queue_maybe_create_meeting( $appointment_id ) {
		if ( !$this->plugin->settings_installed->is_activated( 'zoom' ) ) {
			return;
		}

		$appointment_obj = new SSA_Appointment_Object( $appointment_id );
		
		$status = $appointment_obj->status;
		if ( empty( $status ) || ! $appointment_obj->is_booked() ) {
			return; // only proceed if the appointment is booked
		}
		
		$appointment_type = $appointment_obj->get_appointment_type();

		// If web meetings for the appointment type is not set, or the provider is not set, OR the provider is NOT zoom, then bail
		$web_meetings = $appointment_type->web_meetings;
		if( empty( $web_meetings['provider'] ) || $web_meetings['provider'] !== 'zoom' ) {
			return;
		}

		ssa_queue_action(
			'appointment_booked',
			'ssa_zoom_create_meeting',
			10,
			array(
				'appointment_id' => $appointment_id,
			),
			'appointment',
			$appointment_id,
			'zoom',
			array()
		);
	}

	public function fail_async_action( $async_action, $error_code = 500, $error_message = '', $context = array() ) {
		$response = array(
			'status_code' => $error_code,
			'error_message' => $error_message,
			'context' => $context,
		);

		ssa_complete_action( $async_action['id'], $response );
	}

	public function maybe_create_meeting( $payload, $async_action ) {
		if ( empty( $payload['appointment_id'] ) ) {
			ssa_complete_action( $async_action['id'], array(
				'reason' => 'Appointment ID not found.',
			) );
		}

		$appointment_obj = new SSA_Appointment_Object( $payload['appointment_id'] );
		if ( ssa_datetime() > $appointment_obj->end_date ) {
			ssa_complete_action( $async_action['id'], array(
				'reason' => 'Appointment is already over, skip meeting creation.',
			) );
		}

		$meeting = $this->create_web_meeting_url( $payload['appointment_id'] );

		if ( empty( $meeting ) ) {
			$this->fail_async_action( $async_action, 500, 'Empty response', $meeting );
			return;
		}

		if ( ! empty( $meeting->code ) && 429 == $meeting->code ) {
			ssa_complete_action( $async_action['id'], array(
				'code' => 429,
				'meeting_response' => $meeting,
			) );

			ssa_queue_action(
				'appointment_booked_requeued',
				'ssa_zoom_create_meeting',
				10,
				$payload,
				'appointment',
				$payload['appointment_id'],
				'zoom',
				array(
					'date_queued' => ssa_datetime( 'tomorrow' )->format( 'Y-m-d 00:00:00' ),
				)
			);
			return;
		}

		ssa_complete_action( $async_action['id'], $meeting );

	}

	/**
	 * After an appointment is created, this function gets the appointment data, generates a Zoom Meeting url,
	 * and stores the meeting url on the appointment metadata.
	 *
	 * @param int $appointment_id The appointment ID.
	 * @return void
	 */
	public function create_web_meeting_url( $appointment_id ) {
		if ( ! $this->plugin->settings_installed->is_activated( 'zoom' ) ) {
			return;
		}

		$appointment_obj = new SSA_Appointment_Object( $appointment_id );

		$status = $appointment_obj->status;
		if ( empty( $status ) || ! in_array( $status, array( 'booked', 'canceled' ) ) ) {
			return; // don't add pending appointments to Zoom Meetings.
		}

		$appointment      = $appointment_obj->data;
		$appointment_type = $this->plugin->appointment_type_model->get( $appointment['appointment_type_id'] );

		// If web meetings for the appointment type is not set, or the provider is not set, OR the provider is NOT zoom, then bail.
		if ( ! $appointment_type['web_meetings'] || ! isset( $appointment_type['web_meetings']['provider'] ) || 'zoom' !== $appointment_type['web_meetings']['provider'] ) {
			return;
		}

		// if it's an appointment that belongs to a group, and it's not the parent appointment, we don't want to create a new Zoom meeting url.
		// Instead, we want to reuse the one that was created for the parent appointment.
		if ( $appointment_obj->is_group_event() && $appointment_id !== (int) $appointment_obj->group_id ) {
			$parent_appointment_obj = new SSA_Appointment_Object( $appointment_obj->group_id );
			$meeting_url            = $parent_appointment_obj->__get( 'web_meeting_url' );

			if ( ! empty( $meeting_url ) ) {
				$appointment_update_data = array(
					'web_meeting_url' => $meeting_url,
				);

				$response = $this->plugin->appointment_model->update( $appointment_id, $appointment_update_data );

				// if we're reusing the same meeting id, let's update the Zoom meeting description to include all attendees.
				$zoom_meeting_id = $this->get_meeting_id_by_url( $meeting_url );

				if ( $zoom_meeting_id ) {
					$description = $appointment_obj->get_calendar_event_description( SSA_Recipient_Admin::create() );
					$description = ( strlen( $description ) > 2000 ) ? substr( $description, 0, 1997 ).'...' : $description; // 2000 Character limit, https://marketplace.zoom.us/docs/api-reference/zoom-api/methods/#operation/meetingCreate

					$update = $this->send_request(
						'meetings/' . $zoom_meeting_id,
						array(
							'agenda' => $description,
						),
						'PATCH'
					);
				}

				return $meeting_url;
			}
		}

		$title       = $appointment_obj->get_calendar_event_title( SSA_Recipient_Admin::create() );
		$description = $appointment_obj->get_calendar_event_description( SSA_Recipient_Admin::create() );
		$description = ( strlen( $description ) > 2000 ) ? substr( $description, 0, 1997 ).'...' : $description; // 2000 Character limit, https://marketplace.zoom.us/docs/api-reference/zoom-api/methods/#operation/meetingCreate
		$start_date  = new DateTimeImmutable( $appointment['start_date'] );
		$end_date    = new DateTimeImmutable( $appointment['end_date'] );
		$duration    = ( strtotime( $appointment['end_date'] ) - strtotime( $appointment['start_date'] ) ) / 60;

		$start_date_localized = $this->plugin->utils->get_datetime_as_local_datetime( $start_date );

		$meeting_config = array(
			'topic'      => $title,
			'start_time' => $start_date_localized->format( \DateTime::RFC3339 ),
			'timezone'   => $start_date_localized->format( 'e' ),
			'duration'   => $duration,
			'agenda'     => $description,
			'settings'   => array(
				'waiting_room'     => true,
				'join_before_host' => false,
			),
		);

		if ( ! empty( $appointment_type['web_meetings']['zoom_privacy'] ) && 'open' == $appointment_type['web_meetings']['zoom_privacy'] ) {
			$meeting_config['settings']['waiting_room'] = false;
			$meeting_config['settings']['join_before_host'] = true;
		}

		$meeting = $this->create_meeting( $meeting_config );

		if ( $meeting ) {
			$appointment_update_data = array(
				'web_meeting_url' => $meeting->join_url,
			);

			$response = $this->plugin->appointment_model->update( $appointment_id, $appointment_update_data );

			if ( $appointment_obj->is_group_event() ) {
				$response = $this->plugin->appointment_model->update( $appointment_obj->group_id, $appointment_update_data );
			}
		}

		return $meeting;
	}

	/**
	 * Given a Zoom meeting url, returns the meeting ID on the url.
	 *
	 * @since 4.8.8
	 *
	 * @param string $url The Zoom meeting url.
	 * @return null|string the Zoom meeting ID. Null if the url format was invalid.
	 */
	public function get_meeting_id_by_url( $url = null ) {
		$base  = explode( '?', $url ); // remove query string.
		$parts = explode( '/j/', $base[0] ); // break base url from id.

		if ( isset( $parts[1] ) ) {
			return $parts[1];
		}

		return null;
	}

	/**
	 * Generates a Zoom Meeting based on Appointment Data.
	 * 
	 * @param array $args
	 * @return StdClass/boolean
	 */
	public function create_meeting( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'host_id'    => 'me',
			'topic'      => null,
			'type'       => 2,
			'start_time' => null,
			'timezone'   => 'UTC',
			'duration'   => 120,
			'agenda'     => null,
		) );
		if ( ! empty( $args['agenda'] ) && is_string( $args['agenda'] ) ) {
			$args['agenda'] = ( strlen( $args['agenda'] ) > 2000 ) ? substr( $args['agenda'], 0, 1997 ).'...' : $args['agenda']; // 2000 Character limit, https://marketplace.zoom.us/docs/api-reference/zoom-api/methods/#operation/meetingCreate
		}

		$meeting = $this->send_request( 'users/'. $args['host_id'] .'/meetings', $args, "POST" );

		return $meeting;
	}


	/**
	 * Send request to API
	 *
	 * @param $function
	 * @param $data
	 * @param string $request
	 *
	 * @return array|bool|string|WP_Error
	 */
	protected function send_request( $function, $data, $request = "GET" ) {
		$request_url = $this->api_url . $function;
		$access_token = $this->get_access_token();

		$args        = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token,
				'Content-Type'  => 'application/json'
			)
		);

		if ( $request == "GET" ) {
			$args['body'] = ! empty( $data ) ? $data : array();
			$response     = wp_remote_get( $request_url, $args );
		} else if ( $request == "DELETE" ) {
			$args['body']   = ! empty( $data ) ? json_encode( $data ) : array();
			$args['method'] = "DELETE";
			$response       = wp_remote_request( $request_url, $args );
		} else if ( $request == "PATCH" ) {
			$args['body']   = ! empty( $data ) ? json_encode( $data ) : array();
			$args['method'] = "PATCH";
			$response       = wp_remote_request( $request_url, $args );
		} else {
			$args['body']   = ! empty( $data ) ? json_encode( $data ) : array();
			$args['method'] = "POST";
			$response       = wp_remote_post( $request_url, $args );
		}
				
		$response = wp_remote_retrieve_body( $response );

		if ( ! $response ) {
			return false;
		}

		return json_decode( $response );
	}

// 	private function generate_jwt() {
// 		$zoom_settings = $this->plugin->zoom_settings->get();

// // HARDCODED
// 		$zoom_settings = SSA_Zoom_Settings_Fixtures_Test::connected_valid();
// // HARDCODED

// 		$token = array(
// 			"iss" => $zoom_settings['api_key'],
// 			"exp" => time() + 3600 //60 seconds as suggested
// 		);

// 		return JWT::encode( $token, $zoom_settings['api_secret'] );
// 	}

	public function is_activated() {
		$settings = $this->plugin->zoom_settings->get();

		if ( empty( $settings['access_token'] ) || empty( $settings['auth_code'] ) ) {
			return false;
		}

		return true;
	}
}
