<?php


/**
 * Class Mailster_REST_Subscriber_Count_Controller
 */
class Mailster_REST_Subscriber_Count_Controller extends WP_REST_Controller {
	/**
	 * The namespace.
	 *
	 * @var string
	 */
	protected $namespace;

	/**
	 * Rest base for the current object.
	 *
	 * @var string
	 */
	protected $rest_base;


	public function __construct() {

		$this->namespace = 'mailster/v1';
		$this->rest_base = 'subscriber_count';
	}

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_count' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
				'schema' => null,

			)
		);
	}

	/**
	 * Check permissions for the read.
	 *
	 * @param WP_REST_Request $request get data from request.
	 *
	 * @return bool|WP_Error
	 */
	public function get_items_permissions_check( $request ) {

		if ( ! current_user_can( 'edit_posts' ) ) {
			return new WP_Error( 'rest_forbidden', esc_html__( 'You cannot view this resource.' ), array( 'status' => $this->authorization_status_code() ) );
		}
		return true;
	}


	public function get_count( $request ) {

		$args = $request->get_param( 'mailster' );

		$count = mailster( 'subscribers' )->get_count_by_status( 1 );

		$data = array(
			'count'  => $count,
			'output' => mailster( 'subscribers' )->get_formated_count( $args ),
		);

		return rest_ensure_response( $data );
	}



	/**
	 * Sets up the proper HTTP status code for authorization.
	 *
	 * @return int
	 */
	public function authorization_status_code() {

		$status = 401;

		if ( is_user_logged_in() ) {
			$status = 403;
		}

		return $status;
	}
}
