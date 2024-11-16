<?php
/**
 * Simply Schedule Appointments Sms Api.
 *
 * @since   2.9.1
 * @package Simply_Schedule_Appointments
 */

/**
 * Simply Schedule Appointments Sms Api.
 *
 * @since 2.9.1
 */
class SSA_Sms_Api extends WP_REST_Controller {
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
		$base = 'sms';
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

		register_rest_route( $namespace, '/' . $base . '/authorize', array(
			array(
				'methods'         => WP_REST_Server::CREATABLE,
				'callback'        => array( $this, 'authorize' ),
				'permission_callback' => array( $this, 'disconnect_permissions_check' ),
				'args' => array(
					// 'api_key' => array(
					// 	'required' => true,
					// ),
				),
			),
		) );

		register_rest_route( $namespace, '/' . $base . '/send_test', array(
			array(
				'methods'         => WP_REST_Server::CREATABLE,
				'callback'        => array( $this, 'send_test' ),
				'permission_callback' => array( $this, 'disconnect_permissions_check' ),
				'args' => array(
					'phone' => array(
						'required' => true,
					),
				),
			),
		) );

		register_rest_route( $namespace, '/' . $base . '/deauthorize', array(
			array(
				'methods'         => WP_REST_Server::CREATABLE,
				'callback'        => array( $this, 'deauthorize' ),
				'permission_callback' => array( $this, 'disconnect_permissions_check' ),
				'args' => array(
					// 'api_key' => array(
					// 	'required' => true,
					// ),
				),
			),
		) );

		register_rest_route( $namespace, '/' . $base . '/lists', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'lists' ),
				'permission_callback' => array( $this, 'disconnect_permissions_check' ),
				'args' => array(
					// 'api_key' => array(
					// 	'required' => true,
					// ),
				),
			),
		) );


		// register_rest_route( $namespace, '/' . $base . '/schema', array(
		// 	'methods'         => WP_REST_Server::READABLE,
		// 	'callback'        => array( $this, 'get_public_item_schema' ),
		// ) );
	}

	public function authorize( $request ) {
		$params = $request->get_params();

		$response = $this->plugin->sms->authorize();
		if ( is_a( $response, 'WP_Error' ) ) {
			return array(
				'response_code' => $response->get_error_code(),
				'error' => $response,
				'data' => array(),
			);
		}
		
		$response = ssa()->settings->get()['sms'];

		return array(
			'response_code' => 200,
			'error' => '',
			'data' => $response,
		);
	}

	public function send_test( $request ) {
		$params = $request->get_params();
		$this->plugin->sms->send_test( $params['phone'] );

		if ( is_a( $response, 'WP_Error' ) ) {
			return array(
				'response_code' => $response->get_error_code(),
				'error' => $response,
				'data' => array(),
			);
		}

		return array(
			'response_code' => 200,
			'error' => '',
			'data' => $response,
		);
	}

	public function deauthorize( $request ) {
		$params = $request->get_params();

		$response = $this->plugin->sms->deauthorize();
		if ( is_a( $response, 'WP_Error' ) ) {
			return array(
				'response_code' => $response->get_error_code(),
				'error' => $response,
				'data' => array(),
			);
		}

		return array(
			'response_code' => 200,
			'error' => '',
			'data' => $response,
		);
	}

	public function lists( $request ) {
		$params = $request->get_params();

		$response = $this->plugin->sms->fetch_lists();
		if ( is_a( $response, 'WP_Error' ) ) {
			return array(
				'response_code' => $response->get_error_code(),
				'error' => $response,
				'data' => array(),
			);
		}

		return array(
			'response_code' => 200,
			'error' => '',
			'data' => $response,
		);
	}

	/**
	 * Get a collection of items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		if ( !$this->plugin->settings_installed->is_enabled( 'sms' ) ) {
			return false;
		}

		if ( $this->plugin->settings_installed->is_enabled( 'staff' ) ) {
			if( current_user_can( 'ssa_manage_staff' ) ) {
				die( 'TODO: staff' . __FILE__ . ':' . __LINE__ ); // phpcs:ignore
				// get all for all users
				// or just specific staff id if specified
			} else {
				$staff_ids = array( get_current_user_id() );
			}
		} else {
			$staff_ids = array( 0 );
		}

		
		$sms = array();
		
		foreach ($staff_ids as $key => $staff_id) {			
			$calendars = $this->plugin->sms->get_calendars_by_staff( $staff_id );
			$sms = array_merge( $sms, $calendars );
		}

		if ( !current_user_can( 'ssa_manage_staff' ) ) {
			$sms = array_filter( $sms, function( $sms ) {
				if ( empty( $sms['staff_id'] ) ) {
					return true; // staff_id 0 is global
				}

				if ( $sms['staff_id'] == get_current_user_id() ) {
					return true;
				}

				return false;
			} );
		}

		return array(
			'response_code' => 200,
			'error' => '',
			'data' => array(
				'sms' => array_values( $sms ),
			),
		);
	}

	public function disconnect( $request ) {
		$settings = $this->plugin->sms_settings->get();

		$settings['twilio_account_sid'] = '';
		$settings['twilio_auth_token'] = '';
		$settings['twilio_test_account_sid'] = '';
		$settings['twilio_test_auth_token'] = '';
		$settings['twilio_send_from_phone'] = '';

		$this->plugin->sms_settings->update( $settings );
		return array(
			'response_code' => 200,
			'error' => '',
			'data' => $this->plugin->sms_settings->get(),
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
			$global_sms = get_option( 'ssa_sms', array() );
			if ( !in_array( $notice_name, $global_sms ) ) {
				$global_sms[] = $notice_name;
				update_option( 'ssa_sms', $global_sms );
			}
		} elseif ( is_user_logged_in() ) {
			$user_sms = get_user_meta( get_current_user_id(), 'ssa_sms', true );
			if ( empty( $user_sms ) ) {
				$user_sms = array();
			}

			if ( !in_array( $notice_name, $user_sms ) ) {
				$user_sms[] = $notice_name;
				update_user_meta( get_current_user_id(), 'ssa_sms', $user_sms );
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
			$global_sms = get_option( 'ssa_sms', array() );
			if ( in_array( $notice_name, $global_sms ) ) {
				$pos = array_search( $notice_name, $global_sms );
				unset( $global_sms[$pos] );
				update_option( 'ssa_sms', $global_sms );
			}
		} elseif ( is_user_logged_in() ) {
			$user_sms = get_user_meta( get_current_user_id(), 'ssa_sms', true );
			if ( empty( $user_sms ) ) {
				$user_sms = array();
			}

			if ( in_array( $notice_name, $user_sms ) ) {
				$pos = array_search( $notice_name, $user_sms );
				unset( $user_sms[$pos] );
				update_user_meta( get_current_user_id(), 'ssa_sms', $user_sms );
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
		return current_user_can( 'ssa_manage_site_settings' );
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
