<?php
/**
 * Simply Schedule Appointments Log Model.
 *
 * @since   2.1.8
 * @package Simply_Schedule_Appointments
 */
use League\Period\Period;

/**
 * Simply Schedule Appointments Log Model.
 *
 * @since 2.1.8
 */
class SSA_Payment_Model extends SSA_Db_Model {
	protected $slug = 'payment';
	protected $version = '1.7.4';

	/**
	 * Parent plugin class.
	 *
	 * @since 0.0.2
	 *
	 * @var   Simply_Schedule_Appointments
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * @since  0.0.2
	 *
	 * @param  Simply_Schedule_Appointments $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		// $this->version = $this->version.'.'.time(); // dev mode
		parent::__construct( $plugin );

		$this->hooks();
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  0.0.2
	 */
	public function hooks() {
		// add_action( 'init', array($this,'debug') );
	}

	public function debug() {
		$appointment = $this->plugin->appointment_model->get( 20, 1 );
		echo '<pre>'.print_r($appointment, true).'</pre>'; // phpcs:ignore
		exit;
	}

	// public function belongs_to() {
	// 	return array(
	// 		'Author' => array(
	// 			'model' => 'WP_User_Model',
	// 			'foreign_key' => 'author_id',
	// 		),
	// 		'Customer' => array(
	// 			'model' => 'WP_User_Model',
	// 			'foreign_key' => 'customer_id',
	// 		),
	// 		'Appointment' => array(
	// 			'model' => $this->plugin->appointment_model,
	// 			'foreign_key' => 'appointment_id',
	// 		),
	// 	);
	// }

	protected $schema = array(
		'appointment_id' => array(
			'field' => 'appointment_id',
			'label' => 'Appointment ID',
			'default_value' => 0,
			'format' => '%d',
			'mysql_type' => 'BIGINT',
			'mysql_length' => 20,
			'mysql_unsigned' => true,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
		),
		'author_id' => array(
			'field' => 'author_id',
			'label' => 'Author ID',
			'default_value' => 0,
			'format' => '%d',
			'mysql_type' => 'BIGINT',
			'mysql_length' => 20,
			'mysql_unsigned' => true,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
		),
		'gateway' => array(
			'field' => 'gateway',
			'label' => 'Gateway',
			'default_value' => '',
			'format' => '%s',
			'mysql_type' => 'VARCHAR',
			'mysql_length' => '16',
			'mysql_unsigned' => false,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
		),
		'gateway_transaction_id' => array(
			'field' => 'gateway_transaction_id',
			'label' => 'Gateway Transaction ID',
			'default_value' => '',
			'format' => '%s',
			'mysql_type' => 'VARCHAR',
			'mysql_length' => '255',
			'mysql_unsigned' => false,
			'mysql_allow_null' => true,
			'mysql_extra' => '',
			'cache_key' => false,
		),
		'gateway_payload' => array(
			'field' => 'gateway_payload',
			'label' => 'Gateway Payload',
			'default_value' => false,
			'format' => '%s',
			'mysql_type' => 'TEXT',
			'mysql_length' => false,
			'mysql_unsigned' => false,
			'mysql_allow_null' => true,
			'mysql_extra' => '',
			'cache_key' => false,
			'encoder' => 'json',
		),
		'gateway_response' => array(
			'field' => 'gateway_response',
			'label' => 'Gateway Response',
			'default_value' => false,
			'format' => '%s',
			'mysql_type' => 'TEXT',
			'mysql_length' => false,
			'mysql_unsigned' => false,
			'mysql_allow_null' => true,
			'mysql_extra' => '',
			'cache_key' => false,
			'encoder' => 'json',
		),
		'user_email' => array(
			'field' => 'user_email',
			'label' => 'User Email',
			'default_value' => '',
			'format' => '%s',
			'mysql_type' => 'VARCHAR',
			'mysql_length' => '128',
			'mysql_unsigned' => false,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
		),
		'user_ip' => array(
			'field' => 'user_ip',
			'label' => 'User IP',
			'default_value' => '',
			'format' => '%s',
			'mysql_type' => 'VARCHAR',
			'mysql_length' => '128',
			'mysql_unsigned' => false,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
		),
		'purchase_key' => array(
			'field' => 'purchase_key',
			'label' => 'Purchase Key',
			'default_value' => '',
			'format' => '%s',
			'mysql_type' => 'VARCHAR',
			'mysql_length' => '255',
			'mysql_unsigned' => false,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
		),
		'payment_mode' => array(
			'field' => 'payment_mode',
			'label' => 'Payment Mode',
			'default_value' => '',
			'format' => '%s',
			'mysql_type' => 'VARCHAR',
			'mysql_length' => '250',
			'mysql_unsigned' => false,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
		),

		'currency' => array(
			'field' => 'currency',
			'label' => 'Currency',
			'default_value' => '',
			'format' => '%s',
			'mysql_type' => 'VARCHAR',
			'mysql_length' => '8',
			'mysql_unsigned' => false,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
		),

		'amount_attempted' => array(
			'field' => 'amount_attempted',
			'label' => 'Amount Due',
			'default_value' => false,
			'format' => '%s',
			'mysql_type' => 'DECIMAL(9,2)',
			'mysql_length' => '',
			'mysql_unsigned' => false,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
		),

		'amount_paid' => array(
			'field' => 'amount_paid',
			'label' => 'Amound Paid',
			'default_value' => false,
			'format' => '%s',
			'mysql_type' => 'DECIMAL(9,2)',
			'mysql_length' => '',
			'mysql_unsigned' => false,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
		),

		'amount_refunded' => array(
			'field' => 'amount_refunded',
			'label' => 'Amound Refunded',
			'default_value' => false,
			'format' => '%s',
			'mysql_type' => 'DECIMAL(9,2)',
			'mysql_length' => '',
			'mysql_unsigned' => false,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
		),

		'payment_meta' => array(
			'field' => 'payment_meta',
			'label' => 'Payment Meta',
			'default_value' => false,
			'format' => '%s',
			'mysql_type' => 'TEXT',
			'mysql_length' => false,
			'mysql_unsigned' => false,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
			'encoder' => 'json',
		),

		'refund_meta' => array(
			'field' => 'refund_meta',
			'label' => 'Refund Meta',
			'default_value' => false,
			'format' => '%s',
			'mysql_type' => 'TEXT',
			'mysql_length' => false,
			'mysql_unsigned' => false,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
			'encoder' => 'json',
		),

		'notes' => array(
			'field' => 'notes',
			'label' => 'Notes',
			'default_value' => false,
			'format' => '%s',
			'mysql_type' => 'TEXT',
			'mysql_length' => false,
			'mysql_unsigned' => false,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
			'encoder' => 'json',
		),

		'status' => array(
			'field' => 'status',
			'label' => 'Status',
			'default_value' => '',
			'format' => '%s',
			'mysql_type' => 'VARCHAR',
			'mysql_length' => '16',
			'mysql_unsigned' => false,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
		),
		'date_created' => array(
			'field' => 'date_created',
			'label' => 'Date Created',
			'default_value' => false,
			'format' => '%s',
			'mysql_type' => 'datetime',
			'mysql_length' => '',
			'mysql_unsigned' => false,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
		),
		'date_modified' => array(
			'field' => 'date_modified',
			'label' => 'Date Modified',
			'default_value' => false,
			'format' => '%s',
			'mysql_type' => 'datetime',
			'mysql_length' => '',
			'mysql_unsigned' => false,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
		),
	);

	public $indexes = array(
		'appointment_id' => [ 'appointment_id' ],
		'gateway' => [ 'gateway' ],
		'status' => [ 'status' ],
		'date_created' => [ 'date_created' ],
	);

	public function filter_where_conditions( $where, $args ) {
		if ( !empty( $args['gateway'] ) ) {
			$where .= ' AND gateway="'.sanitize_text_field( $args['gateway'] ).'"';
		}

		if ( !empty( $args['status'] ) ) {
			$where .= ' AND status="'.sanitize_text_field( $args['status'] ).'"';
		}
		
		if ( !empty( $args['appointment_id'] ) ) {
			$where .= ' AND appointment_id="'.sanitize_text_field( $args['appointment_id'] ).'"';
		}

		if ( !empty( $args['gateway_transaction_id'] ) ) {
			$where .= ' AND gateway_transaction_id="'.sanitize_text_field( $args['gateway_transaction_id'] ).'"';
		}
				
		return $where;
	}

	public function get_item_permissions_check( $request ) {
		return false; // We're not currently using this
	}

	public function get_items_permissions_check( $request ) {
		if ( current_user_can( 'ssa_manage_others_appointments' ) ) {
			return true;
		}

		if ( current_user_can( 'ssa_manage_appointments' ) ) {
			// TODO: if ( current_user_can manage THIS payment for THIS appointment ID )
			return true;
		}

		$params = $request->get_params();
		if ( true === parent::get_item_permissions_check( $request ) ) {
			return true;
		}

		if ( true === $this->id_token_permissions_check( $request ) ) {
			return true;
		}

		return false;
	}

	public function create_item_permissions_check( $request ) {
		if ( ! $this->nonce_permissions_check( $request ) ) {
		 	return false;
		}

		/* ID / Token Verification */
		$params = $request->get_params();
		if ( empty( $params['appointment_id'] ) || empty( $params['appointment_token'] ) ) {
			return false;
		}
		if ( $this->plugin->appointment_model->verify_id_token( 
				sanitize_text_field( $params['appointment_id'] ),
				sanitize_text_field( $params['appointment_token'] )
			) ) {

			return true;
		}

		return false;
	}

	/**
	 * Check if a given request has access to update a specific item
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function update_item_permissions_check( $request ) {
		if ( true === $this->get_item_permissions_check( $request ) ) {
			return true;
		}
		
		return false;
	}


	public function register_custom_routes() {
		$namespace = $this->api_namespace.'/v' . $this->api_version;
		$base = $this->get_api_base();
	}

	public function insert( $data, $type = '' ) {
		$response = array();

		$wp_error = new WP_Error();

		if ( empty( $data['gateway'] ) ) {
			$wp_error->add( 'gateway_required', __( 'Internal Error: a payment gateway was not specified', 'simply-schedule-appointments' ) );
			return $wp_error;
		}

		$appointment_object = new SSA_Appointment_Object( $data['appointment_id'] );

		if ( $data['gateway'] === 'stripe' ) {
			$appointment_type = $appointment_object->get_appointment_type();

			$start_date = $appointment_object->start_date_datetime;
			$period = new Period(
				$start_date->sub( $appointment_type->get_buffered_duration_interval() ),
				$start_date->add( $appointment_type->get_buffered_duration_interval() )
			);

			$availability_query = new SSA_Availability_Query( $appointment_type, $period );

			$is_period_available = $availability_query->is_prospective_appointment_bookable( $appointment_object
			);

			if ( ! $is_period_available ) {
				return array(
					'error' => array(
						'code' => 'appointment_unavailable_no_payment',
						'message' => __( 'Sorry, that time was booked and is no longer available. No payment was processed.', 'simply-schedule-appointments' ),
						'data' => array(),
					),
				);
			}
		}

		if ( empty( $data['gateway_payload'] ) ) {
			$data['gateway_payload'] = '';
		}
		
		if ( empty( $data['purchase_key'] ) ) {
			$data['purchase_key'] = $this->plugin->payments->generate_unique_purchase_key( $data );
		}

		$payment_data = $this->plugin->payments->process_payment( $data['appointment_id'], $data['gateway'], $data['gateway_payload'] );
		
		if ( is_wp_error( $payment_data ) ) {
			return $payment_data;
		}
		
		$data = array_merge( $data, $payment_data );

		// check if transaction already exists, and update it if so
		if( ! empty ( $payment_data["gateway_transaction_id"] ) ) {
			$existing_payment = $this->query( array( 'gateway_transaction_id'=>$payment_data["gateway_transaction_id"]) );
			if ( ! empty( $existing_payment ) ) {
				if(count($existing_payment) > 1) {
					// should never happen in theory, but just in case
					ssa_debug_log("ERROR: multiple payments with same transaction id: ".$payment_data["gateway_transaction_id"]);
				}
				parent::update( $existing_payment[0]["id"], $data);
				return $existing_payment[0]["id"];
			}
		}

		$payment_id = parent::insert( $data, $type );
		return $payment_id;
	}

}
