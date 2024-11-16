<?php
/**
 * Simply Schedule Appointments Webex.
 *
 * @since   3.7.1
 * @package Simply_Schedule_Appointments
 */

/**
 * Simply Schedule Appointments Webex.
 *
 * @since 3.7.1
 */
class SSA_Webex {
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
	private $api_url = 'https://webexapis.com/v1/';

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
		add_action( 'ssa/async/webex_create_meeting', array( $this, 'maybe_create_meeting' ), 10, 1 );

		add_action( 'ssa/appointment/canceled', array( $this, 'maybe_cancel_meeting' ), 10, 1 );

		add_action( 'ssa/appointment/edited', array( $this, 'maybe_edit_meeting' ), 10, 1 );

	}

	/**
	 * Init API endpoints.
	 *
	 * @since  5.6.0
	 *
	 * @return void
	 */
	public function init() {
		$this->register_routes();
	}

	/**
	 * Register the routes for the objects of the controller.
	 *
	 * @since  5.6.0
	 *
	 * @return void
	 */
	public function register_routes() {
		$version   = '1';
		$namespace = 'ssa/v' . $version;
		$base      = 'webex';

		register_rest_route(
			$namespace,
			'/' . $base . '/redirect_url',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'redirect_url_request' ),
					'permission_callback' => array( 'TD_API_Model', 'logged_in_permissions_check' ),
				),
			)
		);

		register_rest_route(
			$namespace,
			'/' . $base . '/authorize_url',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'authorize_url_request' ),
					'permission_callback' => array( 'TD_API_Model', 'logged_in_permissions_check' ),
				),
			)
		);

		register_rest_route(
			$namespace,
			'/' . $base . '/disconnect',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'disconnect' ),
					'permission_callback' => array( 'TD_API_Model', 'logged_in_permissions_check' ),
				),
			)
		);

		register_rest_route(
			$namespace,
			'/' . $base . '/me',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_user_data' ),
					'permission_callback' => array( 'TD_API_Model', 'logged_in_permissions_check' ),
				),
			)
		);
	}

	/**
	 * Returns Webex Redirect url.
	 *
	 * @since 5.6.0
	 *
	 * @return WP_REST_Response The redirect url.
	 */
	public function redirect_url_request() {
		$redirect_url = $this->get_redirect_url();

		return new WP_REST_Response( $redirect_url, 200 );
	}

	/**
	 * Returns Webex Redirection url.
	 *
	 * @since 5.6.0
	 *
	 * @param string $base_url Base url.
	 * @param string $state    State.
	 *
	 * @return string
	 */
	public function get_redirect_url( $base_url = null, $state = null ) {
		if ( ! $base_url ) {
			$redirect_url = get_home_url( null, '?ssa-auth=webex', 'https' );
		} else {
			$redirect_url = add_query_arg(
				array(
					'ssa-auth'     => 'webex',
					'ssa_state'    => $state,
					'ssa_redirect' => $base_url,
				),
				get_home_url()
			);
		}
		return $redirect_url;
	}

	/**
	 * Returns Webex Authorize url.
	 *
	 * @return string The authorize url.
	 */
	public function get_marketplace_redirect_uri() {
		return 'https://simplyscheduleappointments.com/authorize/webex/';
	}

	/**
	 * Returns Webex Oauth url.
	 *
	 * @since 5.6.0
	 *
	 * @param WP_REST_Request $request The request.
	 * @return WP_REST_Response The response.
	 */
	public function authorize_url_request( WP_REST_Request $request ) {
		$params   = $request->get_params();
		$auth_url = 'https://webexapis.com/v1/authorize';
		$base_url = $params['ssa_base_url'] ? $params['ssa_base_url'] : null;
		$state    = $params['ssa_redirect'] ? $params['ssa_redirect'] : null;

		$site_redirect_url = $this->get_redirect_url( $base_url, $state );

		$parsed_url = add_query_arg(
			array(
				'response_type' => 'code',
				'client_id'     => $this->get_client_id(),
				'scope'         => 'meeting:schedules_read meeting:schedules_write spark:people_read spark-admin:people_read',
				'state'         => base64_encode( // phpcs:ignore
					wp_json_encode(
						array(
							'authorize'         => 'webex',
							'site_url'          => get_home_url(),
							'site_redirect_url' => $site_redirect_url,
						)
					)
				),
				'redirect_uri'  => $this->get_marketplace_redirect_uri(),
			),
			$auth_url
		);

		return new WP_REST_Response(
			array(
				'response_code' => 200,
				'error'         => '',
				'data'          => array(
					'authorize_url' => $parsed_url,
				),
			),
			200
		);
	}

	/**
	 * Verifies if the url is an oauth redirection and runs the appropriate code.
	 *
	 * @since 5.6.0
	 *
	 * @return void
	 */
	public function catch_oauth_callback() {
		if ( isset( $_GET['ssa-auth'] ) && 'webex' === $_GET['ssa-auth'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( $this->plugin->settings_installed->is_enabled( 'webex' ) ) {
				$code     = isset( $_GET['code'] ) ? $_GET['code'] : null; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$state    = isset( $_GET['ssa_state'] ) ? $_GET['ssa_state'] : null; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$redirect = isset( $_GET['ssa_redirect'] ) ? $_GET['ssa_redirect'] : null; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$this->connect( $code, $state, $redirect );
			}
		}
	}

	/**
	 * Grabs the Webex Oauth authorization code and stores on the plugin settings.
	 *
	 * @since 5.6.0
	 *
	 * @param string $code Oauth code.
	 * @param string $state State.
	 * @param string $redirect Redirect url.
	 *
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
			$redirect_url = $this->plugin->wp_admin->url( 'ssa/settings/webex' );
		}

		$settings = $this->plugin->webex_settings->get();

		$settings['auth_code'] = $code;
		$this->plugin->webex_settings->update( $settings );

		// performs a redirect to the plugin url.
		wp_safe_redirect( $redirect_url );
		exit();
	}

	/**
	 * Disconnects Webex account.
	 *
	 * @since 5.6.0
	 *
	 * @param WP_REST_Request $request The request.
	 * @return WP_REST_Response The response.
	 */
	public function disconnect( WP_REST_Request $request ) {
		$settings = $this->plugin->webex_settings->get();

		$settings['auth_code']            = null;
		$settings['access_token']         = null;
		$settings['access_token_expires'] = null;
		$this->plugin->webex_settings->update( $settings );

		return new WP_REST_Response(
			array(
				'response_code' => 200,
				'error'         => '',
				'data'          => $this->plugin->webex_settings->get(),
			),
			200
		);
	}

	/**
	 * Pulls the Webex Access Token from the database. Requests one if we don't have it, or refreshes it if it's expired.
	 *
	 * @return string
	 */
	public function get_access_token() {
		$settings     = $this->plugin->webex_settings->get();
		$access_token = $settings['access_token'];
		// if we don't have any access token, request one and refresh the settings.
		if ( empty( $access_token ) || strpos( $access_token, 'errors' ) !== false ) {
			$this->request_access_token();
			$settings = $this->plugin->webex_settings->get();
		}

		// decode the token stored on the database.
		$access_token = json_decode( $settings['access_token'], true );

		$access_token_expires = $settings['access_token_expires'];
		$access_token_expires = strtotime( $access_token_expires );

		// if the token is expired, then we refresh and store the new token.
		if ( $access_token_expires <= time() ) {
			$access_token = $this->refresh_access_token();

			if ( $access_token ) {
				$access_token = json_decode( $access_token, true );

				return $access_token['access_token'];
			} else {
				return null;
			}
		} else {
			return $access_token['access_token'];
		}
	}

	/**
	 * Returns Webex SSA App client id.
	 *
	 * @since 5.6.0
	 *
	 * @return string The client id.
	 */
	public function get_client_id() {
		return 'C34843b206daf581522204d6f9dd998fc90bf35a71b4753dbe1df9360b3c56125';
	}

	/**
	 * Returns Webex SSA App client secret.
	 *
	 * @since 5.6.0
	 *
	 * @return string The client secret.
	 */
	public function get_client_secret() {
		return '3ecfb60efe0fe2bab8f7276e3eae8df3a04788bbefd7b90e6817e1dd96b58715';
	}

	/**
	 * If we don't have any Webex Access Token, this function retrieves the first one.
	 *
	 * @since 5.6.0
	 *
	 * @return string The access token.
	 */
	public function request_access_token() {
		$settings  = $this->plugin->webex_settings->get();
		$code      = $settings['auth_code'];
		$oauth_url = 'https://webexapis.com/v1/access_token';

		$request = wp_remote_post(
			$oauth_url,
			array(
				'headers' => array(
					'Content-Type' => 'application/x-www-form-urlencoded',
				),
				'body'    => array(
					'grant_type'    => 'authorization_code',
					'code'          => $code,
					'client_id'     => $this->get_client_id(),
					'client_secret' => $this->get_client_secret(),
					'redirect_uri'  => $this->get_marketplace_redirect_uri(),
				),
			)
		);

		if ( ! is_wp_error( $request ) ) {
			$token             = wp_remote_retrieve_body( $request );
			$decoded           = json_decode( $token, true );
			$expires           = $decoded['expires_in'];
			$expires_formatted = gmdate( 'Y-m-d H:i:s', strtotime( "+$expires seconds" ) );

			$settings['access_token']         = $token;
			$settings['access_token_expires'] = $expires_formatted;
			$this->plugin->webex_settings->update( $settings );

			return $token;
		} else {
			ssa_debug_log( 'request_access_token() error' );
		}

		return null;
	}

	/**
	 * If we a Webex Access Token expires, this function refreshes the token.
	 *
	 * @since 5.6.0
	 *
	 * @return string The access token.
	 */
	public function refresh_access_token() {
		if ( get_transient( 'ssa/webex/lock/refresh_access_token' ) ) {
			sleep( 10 );
			$settings = $this->plugin->webex_settings->get();
			return $settings['access_token'];
		}

		set_transient( 'ssa/webex/lock/refresh_access_token', true, 10 );
		$settings     = $this->plugin->webex_settings->get();
		$access_token = json_decode( $settings['access_token'], true );
		$oauth_url    = 'https://webexapis.com/v1/access_token';

		if ( isset( $access_token['errors'] ) ) {
			return null;
		}

		$request = wp_remote_post(
			$oauth_url,
			array(
				'headers' => array(
					'Content-Type' => 'application/x-www-form-urlencoded',
				),
				'body'    => array(
					'grant_type'    => 'refresh_token',
					'client_id'     => $this->get_client_id(),
					'client_secret' => $this->get_client_secret(),
					'refresh_token' => $access_token['refresh_token'],
				),
			)
		);

		if ( ! is_wp_error( $request ) ) {
			$token             = wp_remote_retrieve_body( $request );
			$decoded           = json_decode( $token, true );
			$expires           = $decoded['expires_in'];
			$expires_formatted = gmdate( 'Y-m-d H:i:s', strtotime( "+$expires seconds" ) );

			$settings['access_token']         = $token;
			$settings['access_token_expires'] = $expires_formatted;

			$this->plugin->webex_settings->update( $settings );
			set_transient( 'ssa/webex/lock/refresh_access_token', false, 1 );

			return $token;
		} else {
			ssa_debug_log( 'rrefresh_access_token() error' );
		}

		return null;
	}

	/**
	 * Pulls basic authorized user information.
	 *
	 * @since 5.6.0
	 *
	 * @return WP_REST_Response The response object.
	 */
	public function get_user_data() {
		$user = $this->send_request( 'people/me', array() );

		if ( is_wp_error( $user ) ) {
			return new WP_REST_Response( $user->get_error_message(), 500 );
		}

		if ( $user ) {
			return new WP_REST_Response(
				array(
					'first_name' => $user->firstName, // @codingStandardsIgnoreLine - API returns camelCase
					'last_name'  => $user->lastName, // @codingStandardsIgnoreLine - API returns camelCase
					'email'      => $user->emails[0],
					'avatar'     => isset( $user->avatar ) ? $user->avatar : null,
				),
				200
			);
		}

		return new WP_REST_Response( __( 'Webex user not found.' ), 404 );
	}

	/**
	 * Checks if Webex is enabled, and the appointment is booked. If so, schedules an action to generate the Webex meeting url.
	 *
	 * @since 5.6.0
	 *
	 * @param  int $appointment_id The appointment ID.
	 * @return void
	 */
	public function queue_maybe_create_meeting( $appointment_id ) {
		if ( ! $this->plugin->settings_installed->is_activated( 'webex' ) ) {
			return;
		}

		$appointment_obj = new SSA_Appointment_Object( $appointment_id );

		$status = $appointment_obj->status;
		if ( empty( $status ) || ! $appointment_obj->is_booked() ) {
			return; // only proceed if the appointment is booked.
		}

		$appointment_type = $appointment_obj->get_appointment_type();

		// If web meetings for the appointment type is not set, or the provider is not set, OR the provider is NOT webex, then bail.
		$web_meetings = $appointment_type->web_meetings;
		if ( empty( $web_meetings['provider'] ) || 'webex' !== $web_meetings['provider'] ) {
			return;
		}

		$this->plugin->action_scheduler->add_action(
			'webex_create_meeting',
			time(),
			array(
				'appointment_id' => $appointment_id,
			),
			'webex_create_meeting'
		);
	}

	/**
	 * After an appointment is created, this function gets the appointment data, generates a Webex Meeting url,
	 * and stores the meeting url on the appointment metadata.
	 *
	 * @since 5.6.0
	 *
	 * @param int $appointment_id The appointment ID.
	 *
	 * @throws Exception If the appointment is not found.
	 * @return void
	 */
	public function maybe_create_meeting( $appointment_id ) {
		if ( ! $this->plugin->settings_installed->is_activated( 'webex' ) ) {
			return;
		}

		$appointment_obj = new SSA_Appointment_Object( $appointment_id );

		$appt_model = new SSA_Appointment_Model( $this->plugin );
		$status     = $appointment_obj->status;

		if ( empty( $status ) || ! in_array( $status, array_merge( $appt_model->get_booked_statuses(), $appt_model->get_canceled_statuses() ), true ) ) {
			return; // don't add pending appointments to Webex Meetings.
		}

		$appointment      = $appointment_obj->data;
		$appointment_type = $this->plugin->appointment_type_model->get( $appointment['appointment_type_id'] );

		// If web meetings for the appointment type is not set, or the provider is not set, OR the provider is NOT Webex, then bail.
		if ( ! $appointment_type['web_meetings'] || ! isset( $appointment_type['web_meetings']['provider'] ) || 'webex' !== $appointment_type['web_meetings']['provider'] ) {
			return;
		}

		// if it's an appointment that belongs to a group, and it's not the parent appointment, we don't want to create a new Webex meeting url.
		// Instead, we want to reuse the one that was created for the parent appointment.
		if ( $appointment_obj->is_group_event() && $appointment_id !== (int) $appointment_obj->group_id ) {
			$parent_appointment_obj = new SSA_Appointment_Object( $appointment_obj->group_id );
			$meeting_url            = $parent_appointment_obj->__get( 'web_meeting_url' );
			$web_meeting_id       = $parent_appointment_obj->__get( 'web_meeting_id' );
			$web_meeting_password = $parent_appointment_obj->__get( 'web_meeting_password' );


			if ( ! empty( $meeting_url ) && ! empty( $web_meeting_id ) && ! empty( $web_meeting_password ) ) {
				$appointment_update_data = array(
					'web_meeting_url' => $meeting_url,
					'web_meeting_id' => $web_meeting_id,
					'web_meeting_password' => $web_meeting_password,
			);

				$response = $this->plugin->appointment_model->update( $appointment_id, $appointment_update_data );

				// let's update the Webex meeting description to include all attendees.
				if ( $response ) {
					$this->maybe_edit_meeting( $appointment_id );
				}

				return $meeting_url;
			}
		}

		$title       = $appointment_obj->get_calendar_event_title( SSA_Recipient_Admin::create() );
		$description = $appointment_obj->get_calendar_event_description( SSA_Recipient_Admin::create() );
		$start_date  = new DateTimeImmutable( $appointment['start_date'] );
		$end_date    = new DateTimeImmutable( $appointment['end_date'] );

		$start_date_localized = $this->plugin->utils->get_datetime_as_local_datetime( $start_date );
		$end_date_localized   = $this->plugin->utils->get_datetime_as_local_datetime( $end_date );

		$meeting_config = array(
			'title'                 => $title,
			'agenda'                => $description,
			'start'                 => $start_date_localized->format( \DateTime::RFC3339 ),
			'end'                   => $end_date_localized->format( \DateTime::RFC3339 ),
			'timezone'              => $start_date_localized->format( 'e' ),
			'enabledJoinBeforeHost' => false,
			'joinBeforeHostMinutes' => null,
			'invitees'              => $this->get_appointment_invitees( $appointment_obj ),
			'sendEmail'             => false, // Default on Webex is true, which means that every invitee will receive an email from Webex. We have our own notifications.
		);

		if ( ! empty( $appointment_type['web_meetings']['webex_privacy'] ) && 'open' === $appointment_type['web_meetings']['webex_privacy'] ) {
			$meeting_config['enabledJoinBeforeHost'] = true;
			$meeting_config['joinBeforeHostMinutes'] = 15; // Need this otherwise it defaults to zero, which is the same as a private meeting.
		}

		$meeting = $this->create_meeting( $meeting_config );

		if ( is_wp_error( $meeting ) ) {
			throw new Exception( $meeting->get_error_message() );
		}

		if ( $meeting ) {
			$appointment_update_data = array(
				'web_meeting_url' => $meeting->webLink, // @codingStandardsIgnoreLine (WordPress doesn't like pascalCase).
				'web_meeting_id'       => $meeting->id,
				'web_meeting_password' => $meeting->password,
			);

			$response = $this->plugin->appointment_model->update( $appointment_id, $appointment_update_data );

			if ( $appointment_obj->is_group_event() ) {
				$response = $this->plugin->appointment_model->update( $appointment_obj->group_id, $appointment_update_data );
			}
		}

		return $meeting;
	}

	/**
	 * Given an appointment object, returns a list of invitees for the Webex meeting.
	 *
	 * @since 5.6.0
	 *
	 * @param SSA_Appointment_Object $appointment_obj The appointment object.
	 * @return array The list of invitees.
	 */
	public function get_appointment_invitees( SSA_Appointment_Object $appointment_obj ) {
		$attendees = $appointment_obj->get_attendees();

		$invitees = array();

		foreach ( $attendees as $attendee ) {
			$invitees[] = array(
				'email'       => $attendee['email'],
				'displayName' => $attendee['name'],
				'coHost'      => false,
			);
		}

		return $invitees;
	}

	/**
	 * Given a Webex meeting url, returns the meeting ID on the url.
	 *
	 * @since 5.6.0
	 *
	 * @param string $url The Webex meeting url.
	 * @return null|string the Webex meeting ID. Null if the url format was invalid.
	 */
	public function get_meeting_id_by_url( $url = null ) {
		$parts = explode( '?MTID=', $url ); // remove query string.

		if ( isset( $parts[1] ) ) {
			return $parts[1];
		}

		return null;
	}

	/**
	 * Generates a Webex Meeting based on Appointment Data.
	 *
	 * @since 5.6.0
	 *
	 * @param array $args The arguments to create the meeting.
	 * @return StdClass|boolean|WP_Error The Webex Meeting object. False or WP_Error if the request failed.
	 */
	public function create_meeting( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'title'                 => null,
				'agenda'                => null,
				'start'                 => null,
				'end'                   => null,
				'timezone'              => null,
				'enabledJoinBeforeHost' => false,
				'joinBeforeHostMinutes' => null,
				'invitees'              => array(),
				'sendEmail'             => null,
			)
		);

		$meeting = $this->send_request( 'meetings/', $args, 'POST' );

		return $meeting;
	}


	/**
	 * Send request to API.
	 *
	 * @since 5.6.0
	 *
	 * @param string $endpoint API endpoint.
	 * @param array  $data Data to send.
	 * @param string $request Request type.
	 *
	 * @return array|bool|string|WP_Error
	 */
	protected function send_request( $endpoint, $data = array(), $request = 'GET' ) {
		$request_url  = $this->api_url . $endpoint;
		$access_token = $this->get_access_token();

		$args = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token,
				'Content-Type'  => 'application/json',
			),
			'timeout' => 10,
		);

		if ( 'GET' === $request ) {
			$args['body'] = ! empty( $data ) ? $data : array();
			$response     = wp_remote_get( $request_url, $args );
		} elseif ( 'DELETE' === $request ) {
			$args['body']   = ! empty( $data ) ? wp_json_encode( $data ) : array();
			$args['method'] = 'DELETE';
			$response       = wp_remote_request( $request_url, $args );
		} elseif ( 'PATCH' === $request ) {
			$args['body']   = ! empty( $data ) ? wp_json_encode( $data ) : array();
			$args['method'] = 'PATCH';
			$response       = wp_remote_request( $request_url, $args );
		} elseif ( 'PUT' === $request ) {
			$args['body']   = ! empty( $data ) ? wp_json_encode( $data ) : array();
			$args['method'] = 'PUT';
			$response       = wp_remote_request( $request_url, $args );
		} else {
			$args['body']   = ! empty( $data ) ? wp_json_encode( $data ) : array();
			$args['method'] = 'POST';
			$response       = wp_remote_post( $request_url, $args );
		}

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response = wp_remote_retrieve_body( $response );

		if ( ! $response ) {
			return false;
		}

		$response = json_decode( $response );

		// If the response is an error, return it.
		if ( isset( $response->errors ) ) {
			return new WP_Error( 'webex_error', $response->message );
		}

		return $response;
	}

	/**
	 * Send Request to APi to cancel appt on webex
	 *
	 * @param  int $appointment_id The appointment ID.
	 */
	public function maybe_cancel_meeting( $appointment_id ) {

		if ( ! $this->plugin->settings_installed->is_activated( 'webex' ) ) {
			return;
		}

		$appointment_obj = new SSA_Appointment_Object( $appointment_id );

		$status = $appointment_obj->status;

		$appt_model = new SSA_Appointment_Model( $this->plugin );

		if ( empty( $status ) || ! in_array( $status, array_merge( $appt_model->get_booked_statuses(), $appt_model->get_canceled_statuses() ), true ) ) {
			return;
		}

		$appointment      = $appointment_obj->data;
		$appointment_type = $this->plugin->appointment_type_model->get( $appointment['appointment_type_id'] );

		// If web meetings for the appointment type is not set, or the provider is not set, OR the provider is NOT Webex, then bail.
		if ( ! $appointment_type['web_meetings'] || ! isset( $appointment_type['web_meetings']['provider'] ) || 'webex' !== $appointment_type['web_meetings']['provider'] ) {
			return;
		}

		// If it is a group event don't send cancel request just edit the one in Webex
		if ( $appointment_obj->is_group_event()  ) {
			$this->maybe_edit_meeting( $appointment_id );
			return;
		}

		$webex_meeting_id = $appointment_obj->__get( 'web_meeting_id' );

		if ( ! empty( $webex_meeting_id ) ) {

			$args = array();

			$req = $this->send_request(
				'meetings/' . $webex_meeting_id . '?sendEmail=false',
				$args,
				'DELETE'
			);

			return $req;

		}

	}

	/**
	 * Send Request to APi to edit appt on webex
	 *
	 * @param  int $appointment_id The appointment ID.
	 */
	public function maybe_edit_meeting( $appointment_id ) {

		if ( ! $this->plugin->settings_installed->is_activated( 'webex' ) ) {
			return;
		}

		$appointment_obj = new SSA_Appointment_Object( $appointment_id );

		$appointment      = $appointment_obj->data;
		$appointment_type = $this->plugin->appointment_type_model->get( $appointment['appointment_type_id'] );

		// If web meetings for the appointment type is not set, or the provider is not set, OR the provider is NOT Webex, then bail.
		if ( ! $appointment_type['web_meetings'] || ! isset( $appointment_type['web_meetings']['provider'] ) || 'webex' !== $appointment_type['web_meetings']['provider'] ) {
			return;
		}

		$webex_meeting_id       = $appointment_obj->__get( 'web_meeting_id' );
		$webex_meeting_password = $appointment_obj->__get( 'web_meeting_password' );

		if ( empty( $webex_meeting_id ) || empty( $webex_meeting_password ) ) {
			return;
		}

		$title       = $appointment_obj->get_calendar_event_title( SSA_Recipient_Admin::create() );
		$description = $appointment_obj->get_calendar_event_description( SSA_Recipient_Admin::create() );
		$start_date  = new DateTimeImmutable( $appointment['start_date'] );
		$end_date    = new DateTimeImmutable( $appointment['end_date'] );

		$start_date_localized = $this->plugin->utils->get_datetime_as_local_datetime( $start_date );
		$end_date_localized   = $this->plugin->utils->get_datetime_as_local_datetime( $end_date );

		$meeting_config = array(
			'title'                 => $title,
			'agenda'                => $description,
			'password'              => $webex_meeting_password,
			'start'                 => $start_date_localized->format( \DateTime::RFC3339 ),
			'end'                   => $end_date_localized->format( \DateTime::RFC3339 ),
			'timezone'              => $start_date_localized->format( 'e' ),
			'enabledJoinBeforeHost' => false,
			'joinBeforeHostMinutes' => null,
			'invitees'              => $this->get_appointment_invitees( $appointment_obj ),
			'sendEmail'             => false, // Default on Webex is true, which means that every invitee will receive an email from Webex. We have our own notifications.
		);

		if ( ! empty( $appointment_type['web_meetings']['webex_privacy'] ) && 'open' === $appointment_type['web_meetings']['webex_privacy'] ) {
			$meeting_config['enabledJoinBeforeHost'] = true;
			$meeting_config['joinBeforeHostMinutes'] = 15; // Need this otherwise it defaults to zero, which is the same as a private meeting.
		}

		$meeting = $this->edit_meeting( $webex_meeting_id, $meeting_config );

		return $meeting;

	}

	/**
	 * Edits a Webex Meeting based on new Appointment Data.
	 *
	 * @param string $webex_meeting_id
	 * @param array  $args The arguments to create the meeting.
	 * @return StdClass|boolean|WP_Error The Webex Meeting object. False or WP_Error if the request failed.
	 */
	public function edit_meeting( $webex_meeting_id, $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'title'                 => null,
				'agenda'                => null,
				'password'              => null,
				'start'                 => null,
				'end'                   => null,
				'timezone'              => null,
				'enabledJoinBeforeHost' => false,
				'joinBeforeHostMinutes' => null,
				'invitees'              => array(),
				'sendEmail'             => null,
			)
		);

		$meeting = $this->send_request(
			'meetings/' . $webex_meeting_id,
			$args,
			'PUT'
		);

		return $meeting;
	}

}
