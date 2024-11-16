<?php


/**
 * Class Mailster_REST_Automations_Controller
 */
class Mailster_REST_Automations_Controller extends WP_REST_Controller {
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
		$this->rest_base = 'automations';
	}

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/triggers',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_triggers' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
				'schema' => null,

			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/numbers/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_numbers' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
				'schema' => null,

			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/actions',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_actions' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
				'schema' => null,

			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/lists',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_lists' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
				'schema' => null,

			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/forms',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_forms' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
				'schema' => null,

			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/tags',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_tags' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
				'schema' => null,

			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/fields',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_fields' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
				'schema' => null,

			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/campaigns',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_campaigns' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
				'schema' => null,

			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/stats/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_campaign_stats' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
				'schema' => null,

			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/queue/(?P<post_id>\d+)(?:/(?P<step>[a-zA-Z0-9-]+))?',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_queue_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
				'schema' => null,

			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/queue/(?P<post_id>\d+)/(?P<step>[a-zA-Z0-9-]+)/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'edit_queue_item' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
				'schema' => null,

			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/queue/(?P<post_id>\d+)/(?P<step>[a-zA-Z0-9-]+)/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_queue_item' ),
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


	public function get_triggers( $request ) {

		$triggers = mailster( 'automations' )->get_triggers();

		return rest_ensure_response( $triggers );
	}

	public function get_numbers( $request ) {

		$post_id = $request->get_param( 'id' );

		$numbers = mailster( 'automations' )->get_numbers( $post_id );

		return rest_ensure_response( $numbers );
	}

	public function get_actions( $request ) {

		$actions = mailster( 'automations' )->get_actions();

		return rest_ensure_response( $actions );
	}

	public function get_lists( $request ) {

		$lists = mailster( 'lists' )->get();

		foreach ( $lists as $i => $list ) {
			$lists[ $i ]->ID        = (int) $list->ID;
			$lists[ $i ]->added     = (int) $list->added;
			$lists[ $i ]->updated   = (int) $list->updated;
			$lists[ $i ]->parent_id = (int) $list->parent_id;
		}

		return rest_ensure_response( $lists );
	}

	public function get_forms( $request ) {

		$forms = mailster( 'block-forms' )->get_all();

		foreach ( $forms as $i => $form ) {
			$forms[ $i ]->ID   = (int) $form->ID;
			$forms[ $i ]->name = $form->post_title;
		}

		return rest_ensure_response( $forms );
	}

	public function get_tags( $request ) {

		$tags = mailster( 'tags' )->get();

		foreach ( $tags as $i => $list ) {
			$tags[ $i ]->ID      = (int) $list->ID;
			$tags[ $i ]->added   = (int) $list->added;
			$tags[ $i ]->updated = (int) $list->updated;
		}

		return rest_ensure_response( $tags );
	}


	public function get_fields( $request ) {

		$fields = array(
			'firstname' => array(
				'name' => mailster_text( 'firstname' ),
				'type' => 'text',
				'id'   => 'firstname',
			),
			'lastname'  => array(
				'name' => mailster_text( 'lastname' ),
				'type' => 'text',
				'id'   => 'lastname',
			),
		);

		$custom_fields = (array) mailster()->get_custom_fields();

		return rest_ensure_response( array_values( $fields + $custom_fields ) );
	}


	public function get_campaigns( $request ) {

		$campaigns = mailster( 'campaigns' )->get_workflow();

		$return = array();

		foreach ( $campaigns as $i => $campaign ) {
			$meta     = mailster( 'campaigns' )->meta( $campaign->ID );
			$return[] = array(
				'ID'        => (int) $campaign->ID,
				'title'     => $campaign->post_title ? $campaign->post_title : __( 'no title', 'mailster' ),
				'edit_url'  => admin_url( 'post.php?action=edit' ),
				'subject'   => $meta['subject'],
				'preheader' => $meta['preheader'],
				'from_name' => $meta['from_name'],
				'from'      => $meta['from_email'],
				'new_url'   => admin_url( 'post-new.php?post_type=newsletter' ),
				'nonce'     => wp_create_nonce( 'edit_workflow_campaign_' . $campaign->ID ),
			);
		}

		return rest_ensure_response( $return );
	}


	public function get_campaign_stats( $request ) {

		$post_id = $request->get_param( 'id' );

		$actions = mailster( 'actions' )->get_by_campaign( $post_id );

		return rest_ensure_response( $actions );
	}

	public function get_queue_items( $request ) {

		$post_id = $request->get_param( 'post_id' );
		$step_id = $request->get_param( 'step' );
		$id      = $request->get_param( 'id' );

		if ( ! empty( $step_id ) ) {
			$items = mailster( 'automations' )->get_queue( $post_id, $step_id );
		} else {
			$items = mailster( 'automations' )->get_queue_count( $post_id );
		}

		return rest_ensure_response( $items );
	}

	public function edit_queue_item( $request ) {

		$post_id = $request->get_param( 'post_id' );
		$step_id = $request->get_param( 'step' );
		$id      = $request->get_param( 'id' );
		$json    = $request->get_json_params();

		if ( isset( $json['finish'] ) ) {
			$items = mailster( 'automations' )->finish_queue_item( $id );
		}
		if ( isset( $json['forward'] ) ) {
			$items = mailster( 'automations' )->forward_queue_item( $id );
		}

		// $items = mailster( 'automations' )->remove_queue_item( $id );

		return rest_ensure_response( $items );
	}

	public function delete_queue_item( $request ) {

		$post_id = $request->get_param( 'post_id' );
		$step_id = $request->get_param( 'step' );
		$id      = $request->get_param( 'id' );

		$items = mailster( 'automations' )->remove_queue_item( $id );

		return rest_ensure_response( $items );
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
