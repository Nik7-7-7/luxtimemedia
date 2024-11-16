<?php
/**
 * Simply Schedule Appointments Google Calendar Api.
 *
 * @since   0.8.0
 * @package Simply_Schedule_Appointments
 */

/**
 * Simply Schedule Appointments Google Calendar Api.
 *
 * @since 0.8.0
 */
class SSA_Google_Calendar_Api extends WP_REST_Controller {
	/**
	 * Parent plugin class
	 *
	 * @var   class
	 * @since 1.0.0
	 */
	protected $plugin = null;

	/**
	 * Constructor
	 *
	 * @since  1.0.0
	 * @param  object $plugin Main plugin object.
	 * @return void
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();
	}

	/**
	 * Initiate our hooks
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function hooks() {
		$this->register_routes();
	}


	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
		$version = '1';
		$namespace = 'ssa/v' . $version;
		$base = 'google_calendars';
		register_rest_route( $namespace, '/' . $base, array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'            => array(

				),
			),
		) );

		register_rest_route( $namespace, '/' . $base . '/disconnect', array(
			array(
				'methods'         => WP_REST_Server::CREATABLE,
				'callback'        => array( $this, 'disconnect' ),
				'permission_callback' => array( $this, 'disconnect_permissions_check' ),
			),
		) );

		register_rest_route( $namespace, '/' . $base . '/staff_disconnect', array(
			array(
				'methods'         => WP_REST_Server::CREATABLE,
				'callback'        => array( $this, 'staff_disconnect' ),
				'args' => array(
					'staff_id' => array(
						'required' => true,
					),
				),
				'permission_callback' => array( $this, 'staff_disconnect_permissions_check' ),
			),
		) );

		register_rest_route( $namespace, '/' . $base . '/authorize_url', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'authorize_url' ),
				'permission_callback' => array( 'TD_API_Model', 'logged_in_permissions_check' ),
			),
		) );

		// register_rest_route( $namespace, '/' . $base . '/schema', array(
		// 	'methods'         => WP_REST_Server::READABLE,
		// 	'callback'        => array( $this, 'get_public_item_schema' ),
		// ) );
	}

	public function authorize_url( $request ) {
		$wp_next_ssa_uri  = null;
		$wp_next_base_uri = null;
		$params = $request->get_params();
		if ( !empty( $params['ssa_redirect'] ) ) {
			$wp_next_ssa_uri = $params['ssa_redirect'];
		}
		if ( !empty( $params['ssa_base_url'] ) ) {
			$wp_next_base_uri = $params['ssa_base_url'];
		}

		if ( empty( $params['staff_id'] ) ) {
			$params['staff_id'] = 0;
		}
		$this->plugin->google_calendar_client->client_init();
		$auth_url = $this->plugin->google_calendar_client->get_auth_url( $params['staff_id'], $wp_next_ssa_uri, $wp_next_base_uri );

		return array(
			'response_code' => 200,
			'error' => '',
			'data' => array(
				'authorize_url' => $auth_url,
			),
		);
	}

	/**
	 * Get a collection of items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		if ( !$this->plugin->settings_installed->is_activated( 'google_calendar' ) ) {
			return false;
		}

		$params = $request->get_params();

		$google_calendars = array();
		if ( $this->plugin->settings_installed->is_enabled( 'staff' ) && ! empty( $params['staff_id'] ) ) {
			if ( $params['staff_id'] == ssa_get_current_staff_id() ) {
				$staff = new SSA_Staff_Object( $params['staff_id'] );
				$external_calendar_api = $staff->get_external_calendar_api();
				if ( $external_calendar_api instanceof SSA_External_Google_Calendar_Api ) {
					$calendar_list = $external_calendar_api->get_calendar_list();
					foreach ($calendar_list as $calendar_id => $calendar_details) {
						$google_calendars[$calendar_id] = array_merge( $calendar_details, array(
							'gcal_id' => $calendar_id,
							'title' => $calendar_details['summary'],
						) );
					}
				}
			}

			return array(
				'response_code' => 200,
				'error' => '',
				'data' => array(
					'google_calendars' => array_values( $google_calendars ),
					// 'user_info' => $user_info,
				),
			);
		}

		$calendars = $this->plugin->google_calendar->get_calendars_by_staff( 0 );
		$google_calendars = array_merge( $google_calendars, $calendars );

		if ( !current_user_can( 'ssa_manage_staff' ) ) {
			$google_calendars = array_filter( $google_calendars, function( $google_calendar ) {
				if ( empty( $google_calendar['staff_id'] ) ) {
					return true; // staff_id 0 is global
				}

				if ( $google_calendar['staff_id'] == ssa_get_current_staff_id() ) {
					return true;
				}

				return false;
			} );
		}

		return array(
			'response_code' => 200,
			'error' => '',
			'data' => array(
				'google_calendars' => array_values( $google_calendars ),
				// 'user_info' => $user_info,
			),
		);
	}

	public function disconnect( $request ) {
		$settings = $this->plugin->google_calendar_settings->get();
		$deleted_count = $this->plugin->availability_external_model->bulk_delete( array(
			'service' => 'google',
		) );

		$settings['access_token'] = '';
		$this->plugin->google_calendar_settings->update( $settings );
		return array(
			'response_code' => 200,
			'error' => '',
			'data' => $this->plugin->google_calendar_settings->get(),
		);
	}

	public function staff_disconnect( $request ) {
		$params = $request->get_params();
		$settings = $this->plugin->google_calendar_settings->get();
		$deleted_count = $this->plugin->availability_external_model->bulk_delete( array(
			'service' => 'google',
			'staff_id' => $params['staff_id'],
		) );

		$this->plugin->staff_model->update( $params['staff_id'], array(
			'google_access_token' => '',
			'google' => '', 
		) );

		return array(
			'response_code' => 200,
			'error' => '',
			'data' => $this->plugin->staff_model->get( $params['staff_id'] ),
		);
	}

	/**
	 * Update one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Request
	 */
	public function update_item( $request ) {
		$params = $request->get_params();
		$notice_name = sanitize_text_field( $params['id'] );
		$is_visible = false;

		if ( !empty( $params['global'] ) ) {
			$global_google_calendars = get_option( 'ssa_google_calendars', array() );
			if ( !in_array( $notice_name, $global_google_calendars ) ) {
				$global_google_calendars[] = $notice_name;
				update_option( 'ssa_google_calendars', $global_google_calendars );
			}
		} elseif ( is_user_logged_in() ) {
			$user_google_calendars = get_user_meta( get_current_user_id(), 'ssa_google_calendars', true );
			if ( empty( $user_google_calendars ) ) {
				$user_google_calendars = array();
			}

			if ( !in_array( $notice_name, $user_google_calendars ) ) {
				$user_google_calendars[] = $notice_name;
				update_user_meta( get_current_user_id(), 'ssa_google_calendars', $user_google_calendars );
			}
		}
		
		return array(
			'response_code' => 200,
			'error' => '',
			'data' => array(
				'noticeName' => $notice_name,
				'isVisible' => $is_visible,
			),
		);
	}

	/**
	 * Delete one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Request
	 */
	public function delete_item( $request ) {
		$params = $request->get_params();
		$notice_name = sanitize_text_field( $params['id'] );
		$is_visible = true;

		if ( !empty( $params['global'] ) ) {
			$global_google_calendars = get_option( 'ssa_google_calendars', array() );
			if ( in_array( $notice_name, $global_google_calendars ) ) {
				$pos = array_search( $notice_name, $global_google_calendars );
				unset( $global_google_calendars[$pos] );
				update_option( 'ssa_google_calendars', $global_google_calendars );
			}
		} elseif ( is_user_logged_in() ) {
			$user_google_calendars = get_user_meta( get_current_user_id(), 'ssa_google_calendars', true );
			if ( empty( $user_google_calendars ) ) {
				$user_google_calendars = array();
			}

			if ( in_array( $notice_name, $user_google_calendars ) ) {
				$pos = array_search( $notice_name, $user_google_calendars );
				unset( $user_google_calendars[$pos] );
				update_user_meta( get_current_user_id(), 'ssa_google_calendars', $user_google_calendars );
			}
		}

		return array(
			'response_code' => 200,
			'error' => '',
			'data' => array(
				'noticeName' => $notice_name,
				'isVisible' => $is_visible,
			),
		);
	}

	/**
	 * Check if a given request has access to get items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function get_items_permissions_check( $request ) {
		if ( current_user_can( 'ssa_manage_site_settings' ) ) {
		 	return true;
		}

		$params = $request->get_params();

		if ( empty( $params['staff_id'] ) ) {
			if ( current_user_can( 'ssa_manage_appointment_types' ) ) {
				return true;
			}
		}
		
		if ( ! empty( $params['staff_id'] ) ) {
			if ( $params['staff_id'] == ssa_get_current_staff_id() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if a given request has access to get items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function disconnect_permissions_check( $request ) {
		return current_user_can( 'ssa_manage_site_settings' );
	}

	/**
	 * Check if a given request has access to get items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function staff_disconnect_permissions_check( $request ) {
		$params = $request->get_params();
		if ( empty( $params['staff_id'] ) ) {
			return false;
		}

		if ( current_user_can( 'ssa_manage_staff' ) ) {
		 	return true;
		}

		if ( ! current_user_can( 'ssa_manage_appointments' ) ) {
			return false;
		}

		if ( $params['staff_id'] == ssa_get_current_staff_id() ) {
			return true;
		}

		return false;
	}


	/**
	 * Check if a given request has access to get a specific item
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function get_item_permissions_check( $request ) {
		return TD_API_Model::nonce_permissions_check( $request );
	}

	/**
	 * Check if a given request has access to update a specific item
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function update_item_permissions_check( $request ) {
		if ( is_user_logged_in() ) {
			return true;
		}
	}

	/**
	 * Check if a given request has access to delete a specific item
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function delete_item_permissions_check( $request ) {
		if ( is_user_logged_in() ) {
			return true;
		}
	}

	/**
	 * Prepare the item for create or update operation
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_Error|object $prepared_item
	 */
	protected function prepare_item_for_database( $request ) {
		return array();
	}

	/**
	 * Prepare the item for the REST response
	 *
	 * @param mixed $item WordPress representation of the item.
	 * @param WP_REST_Request $request Request object.
	 * @return mixed
	 */
	public function prepare_item_for_response( $item, $request ) {
		return array();
	}

	/**
	 * Get the query params for collections
	 *
	 * @return array
	 */
	public function get_collection_params() {
		return array(
			'page'                   => array(
				'description'        => 'Current page of the collection.',
				'type'               => 'integer',
				'default'            => 1,
				'sanitize_callback'  => 'absint',
			),
			'per_page'               => array(
				'description'        => 'Maximum number of items to be returned in result set.',
				'type'               => 'integer',
				'default'            => 10,
				'sanitize_callback'  => 'absint',
			),
			'search'                 => array(
				'description'        => 'Limit results to those matching a string.',
				'type'               => 'string',
				'sanitize_callback'  => 'sanitize_text_field',
			),
		);
	}
}
