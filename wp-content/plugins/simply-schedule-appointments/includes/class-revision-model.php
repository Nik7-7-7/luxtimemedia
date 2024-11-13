<?php
/**
 * Simply Schedule Appointment Revision Model.
 *
 * @since   6.1.0
 * @package Simply_Schedule_Appointments
 */

/**
 * Simply Schedule Appointment Revision Model.
 *
 * @since 6.1.0
 */
class SSA_Revision_Model extends SSA_Db_Model {
	protected $slug    = 'revision';
	protected $version = '3.7.6';
	/**
	 * Parent plugin class.
	 *
	 * @since 6.1.0
	 *
	 * @var   Simply_Schedule_Appointments
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * @since  6.1.0
	 *
	 * @param  Simply_Schedule_Appointments $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {

		parent::__construct( $plugin );

		$this->hooks();
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  6.1.0
	 */
	public function hooks() {
		add_action( 'ssa/appointment/booked', array( $this, 'insert_revision_booked_appointment' ), 10, 3 );
		add_action( 'ssa/appointment/edited', array( $this, 'insert_revision_edited_appointment' ), 10, 3 );
		add_action( 'ssa/appointment/rescheduled', array( $this, 'insert_revision_rescheduled_appointment' ), 10, 3 );
		add_action( 'ssa/appointment/abandoned', array( $this, 'insert_revision_abandoned_appointment' ), 10, 3 );
		add_action( 'ssa/appointment/canceled', array( $this, 'insert_revision_canceled_appointment' ), 10, 3 );
		add_action( 'ssa/appointment/pending', array( $this, 'insert_revision_pending_appointment' ), 10, 3 );

		// Add revision first assigned to team member 
		add_action( 'ssa/appointment/after_insert', array( $this, 'maybe_insert_revision_assigned_appointment' ), 1000, 2 );

		// Add revision record whenever an appointment is reassigned
		add_action( 'ssa/appointment/after_update', array( $this, 'maybe_insert_revision_reassigned_appointment' ), 1000, 3 );


		// scheduled cleanup
		add_action( 'init', array( $this, 'schedule_async_actions' ) );
		add_action( 'ssa/revisions/cleanup', array( $this, 'cleanup_revisions' ), 10, 0 );

		// Notifications
		add_action( 'ssa/notification/scheduled', array( $this, 'insert_revision_on_notification_scheduled' ), 10, 9 );
		add_action( 'ssa/notification/sent', array( $this, 'insert_revision_on_notification_sent' ), 10, 8 );

		//Appointment Types revisions
		add_action( 'ssa/appointment_type/after_insert', array( $this, 'insert_revision_created_appointment_type' ), 1000, 3 );
		add_action( 'ssa/appointment_type/after_delete', array( $this, 'insert_revision_deleted_appointment_type' ), 1000, 3 );
		add_action( 'ssa/appointment_type/after_update', array( $this, 'insert_revision_updated_appointment_type' ), 1000, 3 );
	}

	/**
	 * Scheduling the revisions cleanup async action
	 *
	 * @return void
	 */
	public function schedule_async_actions() {
		// below functions wrap the action scheduler methods, make all the needed checks and log any failures
		if ( false === ssa_has_scheduled_action( 'ssa/revisions/cleanup' ) ) {
			ssa_schedule_recurring_action( strtotime( 'now' ), DAY_IN_SECONDS, 'ssa/revisions/cleanup' );
		}
	}

	/**
	 * revisions scheduled cleanup
	 *
	 * @return void
	 */
	public function cleanup_revisions() {
		$revisions = $this->query(
			array(
				'date_created_max' => date( 'Y-m-d H:i:s', strtotime( '-3 months' ) ),
			)
		);

		// get ids of revisions as an array
		$revisions_ids = wp_list_pluck( $revisions, 'id' );
		if ( ! empty( $revisions_ids ) ) {
			// delete all corresponding revision_meta rows
			$this->plugin->revision_meta_model->bulk_delete(
				array(
					'revision_id' => $revisions_ids,
				)
			);
			// delete revisions rows
			$this->bulk_delete( $revisions_ids );
		}
	}

	public function has_many() {
		return array(
			// TODO check correct name
			'Revision_Meta_Values' => array(
				'model'       => $this->plugin->revision_meta_model,
				'foreign_key' => 'revision_id',
			),
		);
	}

	protected $schema = array(
		'result'              => array(
			'field'            => 'result',
			'label'            => 'Result',
			'default_value'    => '',
			'format'           => '%s',
			'mysql_type'       => 'VARCHAR', // 'success', 'failure', or 'warning'
			'mysql_length'     => '8',
			'mysql_unsigned'   => false,
			'mysql_allow_null' => false,
			'mysql_extra'      => '',
			'cache_key'        => false,
		),
		// foreign key
		'appointment_id'      => array(
			'field'            => 'appointment_id',
			'label'            => 'Appointment ID',
			'default_value'    => 0,
			'format'           => '%d',
			'mysql_type'       => 'BIGINT',
			'mysql_length'     => 20,
			'mysql_unsigned'   => true,
			'mysql_allow_null' => false,
			'mysql_extra'      => '',
			'cache_key'        => false,
		),

		// foreign key
		'appointment_type_id' => array(
			'field'            => 'appointment_type_id',
			'label'            => 'Appointment Type ID',
			'default_value'    => 0,
			'format'           => '%d',
			'mysql_type'       => 'BIGINT',
			'mysql_length'     => 20,
			'mysql_unsigned'   => true,
			'mysql_allow_null' => false,
			'mysql_extra'      => '',
			'cache_key'        => false,
		),

		// foreign key
		'user_id'             => array(
			'field'            => 'user_id',
			'label'            => 'Customer ID',
			'default_value'    => 0,
			'format'           => '%d',
			'mysql_type'       => 'BIGINT',
			'mysql_length'     => 20,
			'mysql_unsigned'   => true,
			'mysql_allow_null' => false,
			'mysql_extra'      => '',
			'cache_key'        => false,
		),

		// foreign key
		'staff_id'            => array(
			'field'            => 'staff_id',
			'label'            => 'Staff ID',
			'default_value'    => 0,
			'format'           => '%d',
			'mysql_type'       => 'BIGINT',
			'mysql_length'     => 20,
			'mysql_unsigned'   => true,
			'mysql_allow_null' => false,
			'mysql_extra'      => '',
			'cache_key'        => false,
		),

		// foreign key
		'payment_id'          => array(
			'field'            => 'payment_id',
			'label'            => 'Payment ID',
			'default_value'    => 0,
			'format'           => '%d',
			'mysql_type'       => 'BIGINT',
			'mysql_length'     => 20,
			'mysql_unsigned'   => true,
			'mysql_allow_null' => false,
			'mysql_extra'      => '',
			'cache_key'        => false,
		),

		// foreign key
		'async_action_id'     => array(
			'field'            => 'async_action_id',
			'label'            => 'Async Action ID',
			'default_value'    => 0,
			'format'           => '%d',
			'mysql_type'       => 'BIGINT',
			'mysql_length'     => 20,
			'mysql_unsigned'   => true,
			'mysql_allow_null' => false,
			'mysql_extra'      => '',
			'cache_key'        => false,
		),

		// allows filtering/looking at what happened in a certain timeframe
		'date_created'        => array(
			'field'            => 'date_created',
			'label'            => 'Date Created',
			'default_value'    => false,
			'format'           => '%s',
			'mysql_type'       => 'DATETIME',
			'mysql_length'     => '',
			'mysql_unsigned'   => false,
			'mysql_allow_null' => true,
			'mysql_extra'      => '',
			'cache_key'        => false,
		),

		// action ( edit, cancel, etc...)
		'action'              => array(
			'field'            => 'action',
			'label'            => 'Action',
			'default_value'    => '',
			'format'           => '%s',
			'mysql_type'       => 'VARCHAR',
			'mysql_length'     => '32',
			'mysql_unsigned'   => false,
			'mysql_allow_null' => false,
			'mysql_extra'      => '',
			'cache_key'        => false,
		),

		// action_title ( Appointment Canceled, Appointment Booked, etc...)
		'action_title'				=> array(
			'field'            => 'action_title',
			'label'            => 'Action Title',
			'default_value'    => '',
			'format'           => '%s',
			'mysql_type'       => 'TINYTEXT',
			'mysql_length'     => false,
			'mysql_unsigned'   => false,
			'mysql_allow_null' => false,
			'mysql_extra'      => '',
			'cache_key'        => false,
		),

		// event summary
		'action_summary'      => array(
			'field'            => 'action_summary',
			'label'            => 'Action Summary',
			'default_value'    => '',
			'format'           => '%s',
			'mysql_type'       => 'TEXT',
			'mysql_length'     => false,
			'mysql_unsigned'   => false,
			'mysql_allow_null' => false,
			'mysql_extra'      => '',
			'cache_key'        => false,
		),

		'summary_vars'        => array(
			'field'            => 'summary_vars',
			'label'            => 'Summary Variables',
			'default_value'    => '',
			'format'           => '%s',
			'mysql_type'       => 'TEXT',
			'mysql_length'     => false,
			'mysql_unsigned'   => false,
			'mysql_allow_null' => false,
			'mysql_extra'      => '',
			'cache_key'        => false,
			'encoder'          => 'json_serialize',
		),

		// context ( booking, settings, syncing etc...)
		'context'             => array(
			'field'            => 'context',
			'label'            => 'Context',
			'default_value'    => '',
			'format'           => '%s',
			'mysql_type'       => 'VARCHAR',
			'mysql_length'     => '32',
			'mysql_unsigned'   => false,
			'mysql_allow_null' => false,
			'mysql_extra'      => '',
			'cache_key'        => false,
		),

		// better filtering, also cross context filtering, like web meetings across several contexts (google, zoom,, etc)
		'sub_context'         => array(
			'field'            => 'sub_context',
			'label'            => 'Sub Context',
			'default_value'    => '',
			'format'           => '%s',
			'mysql_type'       => 'VARCHAR',
			'mysql_length'     => '32',
			'mysql_unsigned'   => false,
			'mysql_allow_null' => false,
			'mysql_extra'      => '',
			'cache_key'        => false,
		),

		'stack_trace' => array(
			'field'            => 'stack_trace',
			'label'            => 'Stack Trace',
			'default_value'    => '',
			'format'           => '%s',
			'mysql_type'       => 'TEXT', 
			'mysql_length'     => false,
			'mysql_unsigned'   => false,
			'mysql_allow_null' => true,
			'mysql_extra'      => '', 
			'cache_key'        => false, 
		),
	);


	// below fields are indexed to use in filtering, like getting all events of a certain appointment_id
	public $indexes = array(
		'appointment_id'      => array( 'appointment_id' ),
		'appointment_type_id' => array( 'appointment_type_id' ),
		'user_id'             => array( 'user_id' ),
		'staff_id'            => array( 'staff_id' ),
		'async_action_id'     => array( 'async_action_id' ),
		'date_created'        => array( 'date_created' ),
		'action'              => array( 'action' ),
		'context'             => array( 'context' ),
		'sub_context'         => array( 'sub_context' ),
	);


	public function get_items_permissions_check( $request ) {
		
		if ( current_user_can( 'ssa_manage_others_appointments' ) ) {
			return true;
		}

		if ( current_user_can( 'ssa_manage_appointments' ) ) {
			return true;
		}

		if (current_user_can( 'ssa_manage_appointment_types' ) ) {
			return true;
		}

		if ( true === parent::get_item_permissions_check( $request ) ) {
			return true;
		}
		
		return false;
	}

	// TODO IMPORTANT: if each action only has one foreign id populated, 2 or more of the below where conditions will eliminate every result
	public function filter_where_conditions( $where, $args ) {
		global $wpdb;
		if ( ! empty( $args['appointment_id'] ) ) {
			if( is_array( $args['appointment_id'] ) ) {
				$appointment_ids = implode( ',', array_map('intval', $args['appointment_id'] ) );
			} else {
				$appointment_ids = intval( $args['appointment_id'] );
			}
			$where .= " AND `appointment_id` IN( $appointment_ids ) ";
		}

		if ( ! empty( $args['appointment_type_id'] ) ) {
			$where .=  $wpdb->prepare( ' AND appointment_type_id=%d', sanitize_text_field( $args['appointment_type_id'] ) );
		}

		if ( ! empty( $args['user_id'] ) ) {
			$where .=  $wpdb->prepare( ' AND user_id=%d', sanitize_text_field( $args['user_id'] ) );
		}

		if ( ! empty( $args['staff_id'] ) ) {
			$where .=  $wpdb->prepare( ' AND staff_id=%d', sanitize_text_field( $args['staff_id'] ) );
		}

		if ( ! empty( $args['async_action_id'] ) ) {
			$where .=  $wpdb->prepare( ' AND async_action_id=%d', sanitize_text_field( $args['async_action_id'] ) );
		}

		if ( ! empty( $args['date_created'] ) ) {
			$where .=  $wpdb->prepare( ' AND date_created=%s', sanitize_text_field( $args['date_created'] ) );
		}

		if ( ! empty( $args['action'] ) ) {
			$where .=  $wpdb->prepare( ' AND action=%s', sanitize_text_field( $args['action'] ) );
		}

		if ( ! empty( $args['context'] ) ) {
			$where .=  $wpdb->prepare( ' AND context=%s', sanitize_text_field( $args['context'] ) );
		}

		if ( ! empty( $args['sub_context'] ) ) {
			$where .=  $wpdb->prepare( ' AND sub_context=%s', sanitize_text_field( $args['sub_context'] ) );
		}

		// Only query where the action_title is set, this will help querying after the revisions table had been updated
		$where .= " AND `action_title` IS NOT NULL AND `action_title` != ''";

		return $where;

	}

	// =================================================================================
	//
	// Section: Revision insertion function definitions
	//
	// Pattern: functions restructure the data available then pass it to insert_revision
	// ==================================================================================

	/**
	 * When it's first assigned to a team member
	 *
	 * @param integer $appointment_id
	 * @param array $data
	 * @return void
	 */
	public function maybe_insert_revision_assigned_appointment( $appointment_id, $data_after, $data_before = null ) {

		if ( empty( $data_after['staff_ids'] ) || ! empty( $data_before ) ) return;

		$params = array(
			'result'				 => 'success',
			'action'				 => 'assigned',
			'appointment_id' => $appointment_id,
			'data_after' 		 => $data_after,
			'data_before'		 => $data_before,
			'staff_ids'			 => $data_after['staff_ids']
		);
		$this->insert_revision_appointment( $params );
	}

	public function maybe_insert_revision_reassigned_appointment( $appointment_id, $data_after, $data_before ) {

		if ( SSA_Appointment_Model::is_appointment_reassigned( $data_after, $data_before ) ) {
			$params = array(
				'result'				 => 'success',
				'action'				 => 'reassigned',
				'appointment_id' => $appointment_id,
				'data_after' 		 => $data_after,
				'data_before'		 => $data_before,
				'staff_ids'			 => $data_after['staff_ids']
			);
			$this->insert_revision_appointment( $params );
		}

	}

	public function insert_revision_gcal_after_sync( $result, $appointment_id, $action, $action_summary, $calendar_id, $calendar_event_id, $event = null ) {
		$revision_meta = array(
				'calendar_id' => $calendar_id,
				'calendar_event_id' => $calendar_event_id,
			);

		if ( ! empty( $event ) ) {
			$revision_meta['event'] = (array) $event;
		}

		// below: hints for WP.org to pick up phrases for translation
		// __( 'Could not find existing event details for appointment ID {{ appointment_id }}', 'simply-schedule-appointments' );
		// __( 'Error while creating event for appointment ID {{ appointment_id }}', 'simply-schedule-appointments' );
		// __( 'Deleted group event for appointment ID {{ appointment_id }}', 'simply-schedule-appointments' );
		// __( 'Deleted individual event for appointment ID {{ appointment_id }}', 'simply-schedule-appointments' );
		// __( 'Exception occured while doing sync for appointment ID {{ appointment_id }}', 'simply-schedule-appointments' );
		// __( 'Inserted GCAL event for appointment ID {{ appointment_id }}', 'simply-schedule-appointments' );
		// __( 'Updated GCAL event for appointment ID {{ appointment_id }}', 'simply-schedule-appointments' );

		$this->insert_revision(
			array(
				'result'         => $result,
				'appointment_id' => $appointment_id,
				'action'         => $action,
				'action_title'	 => $this->get_action_title( $action, $result ),
				'action_summary' => $action_summary,
				'summary_vars'   => array(),
				'context'        => 'gcal',
				'sub_context'    => 'sync',
			),
			$revision_meta
		);
	}

	public function insert_revision_abandoned_appointment( $appointment_id, $data_after, $data_before = null ) {
		$params = array(
			'result'				 => 'success',
			'action'				 => 'abandoned',
			'appointment_id' => $appointment_id,
			'data_after' 		 => $data_after,
			'data_before'		 => $data_before
		);
		$this->insert_revision_appointment( $params );
	}

	public function insert_revision_edited_appointment( $appointment_id, $data_after, $data_before = null ) {
		$params = array(
			'result'				 => 'success',
			'action'				 => 'edited',
			'appointment_id' => $appointment_id,
			'data_after' 		 => $data_after,
			'data_before'		 => $data_before
		);
		$this->insert_revision_appointment( $params );
	}

	public function insert_revision_rescheduled_appointment( $appointment_id, $data_after, $data_before = null ) {
		// Formatting the appointment date and time
		$date_format = SSA_Utils::localize_default_date_strings( 'F j, Y' );
		$time_format = SSA_Utils::localize_default_date_strings( 'g:i a' );
		
		$business_previous_start_date =  $this->plugin->utils->get_datetime_as_local_datetime( $data_before['start_date'] )->format( $date_format );
		$business_previous_start_time =  $this->plugin->utils->get_datetime_as_local_datetime( $data_before['start_date'] )->format( $time_format );
		
		$business_start_date = $this->plugin->utils->get_datetime_as_local_datetime( $data_after['start_date'] )->format( $date_format );
		$business_start_time = $this->plugin->utils->get_datetime_as_local_datetime( $data_after['start_date'] )->format( $time_format );
		
		$params = array(
			'result'				 => 'success',
			'action'				 => 'rescheduled',
			'appointment_id' => $appointment_id,
			'data_after' 		 => $data_after,
			'data_before'		 => $data_before,
			'business_previous_start_date' => $business_previous_start_date,
			'business_previous_start_time' => $business_previous_start_time,
			'business_start_date' => $business_start_date,
			'business_start_time' => $business_start_time,
		);
		$this->insert_revision_appointment( $params );
	}
	
	public function insert_revision_booked_appointment( $appointment_id, $data_after, $data_before = null ) {
		$action = empty( $data_before ) ? 'first_booked' : 'booked';
		
		$params = array(
			'result'				 => 'success',
			'action'				 => $action,
			'appointment_id' => $appointment_id,
			'data_after' 		 => $data_after,
			'data_before'		 => $data_before
		);
		$this->insert_revision_appointment( $params );
	}

	public function insert_revision_canceled_appointment( $appointment_id, $data_after, $data_before = null ) {
		$params = array(
			'result'				 => 'success',
			'action'				 => 'canceled',
			'appointment_id' => $appointment_id,
			'data_after' 		 => $data_after,
			'data_before'		 => $data_before
		);
		$this->insert_revision_appointment( $params );
	}

	public function insert_revision_pending_appointment( $appointment_id, $data_after, $data_before = null ) {
		$params = array(
			'result'				 => 'success',
			'action'				 => $data_after['status'], // pending_form or pending_payment
			'appointment_id' => $appointment_id,
			'data_after' 		 => $data_after,
			'data_before'		 => null
		);
		$this->insert_revision_appointment( $params );
	}

	public function insert_revision_appointment( $params ) {

		// below: hints for WP.org to pick up phrases for translation
		// __( '{{ user }} changed the appointment status to {{ action }}', 'simply-schedule-appointments' );
		$revision = array(
			'result'         => $params['result'],
			'appointment_id' => $params['appointment_id'],
			'action'         => $params['action'],
			'action_title'	 => $this->get_action_title( $params['action'] ),
			'action_summary' => $this->get_action_summary( $params['action'] ),
			'summary_vars'   => array(
				'user'   		=> $this->get_user_name(),
				'staff' 		=> empty( $params['staff_ids'] ) ? array() : $params['staff_ids'],
				'action' 		=> $params['action'],
				'action_noun'   => empty( $params['action_noun'] ) ? null : $params['action_noun'],
				'action_verb'   => empty( $params['action_verb'] ) ? null : $params['action_verb'],
				'recipient_type'=> isset($params['recipient_type']) ? $params['recipient_type'] : null,
				'notification_type'=> isset($params['notification_type']) ? $params['notification_type'] : null,
				'notification_date'=> isset($params['notification_date']) ? $params['notification_date'] : null,
				'notification_time'=> isset($params['notification_time']) ? $params['notification_time'] : null,
				'business_previous_start_date' => isset( $params['business_previous_start_date'] ) ? $params['business_previous_start_date'] : null,
				'business_previous_start_time' => isset( $params['business_previous_start_time'] ) ? $params['business_previous_start_time'] : null,
				'business_start_date' => isset( $params['business_start_date'] ) ? $params['business_start_date'] : null,
				'business_start_time' => isset( $params['business_start_time'] ) ? $params['business_start_time'] : null,
			),
			'context'        => 'booking',
		);

		// in the revision_meta
		// only set meta_value_before if it has a value.
		// because if the field is set to null, the db will not insert the record.
		$revision_meta = array();

		// append appointment status changes
		if ( ! isset( $params['data_after']['status'] ) && isset( $params['data_before']['status'] ) ) {
			// If status is not set we assume it didn't change
			$params['data_after']['status'] = $params['data_before']['status'];
		}

		$revision_meta['status']['meta_value'] = $params['data_after']['status'];
		
		if ( isset( $params['data_before']['status'] ) ) {
			$revision_meta['status']['meta_value_before'] = $params['data_before']['status'];
		}

		// append appointment raw data changes
		$revision_meta['raw_data']['meta_value'] = $params['data_after'];
		if ( isset( $params['data_before'] ) ) {
			$revision_meta['raw_data']['meta_value_before'] = $params['data_before'];
		}

		// insert revision
		$this->insert_revision( $revision, $revision_meta );
	}

	// signarture of function that renders the action summary
	// $this->plugin->templates->render_template_string('{{ user }} changed the appointment status to {{ action }}',['user'=>'name','action'=>'action'])

	// =========================================================================
	//
	// main re-usable function - use to insert revisions and revisions meta data
	//
	// =========================================================================
	public function insert_revision( $revision = array(), $revision_meta = array() ) {
		if ( ! isset( $revision['result'] ) ) {
			ssa_debug_log( 'must specify result to create revision', 10 );
			ssa_debug_log( print_r( $revision, true ), 10 ); //phpcs:ignore
			return;
		}
		if (
			! isset( $revision['appointment_id'] ) &&
			! isset( $revision['appointment_type_id'] ) &&
			! isset( $revision['staff_id'] ) &&
			! isset( $revision['payment_id'] ) &&
			! isset( $revision['async_action_id'] ) ) {
				ssa_debug_log( 'must reference at least one foreign key to create revision', 10 );
				ssa_debug_log( print_r( $revision, true ), 10 ); //phpcs:ignore
				return;
		}
		if ( ! isset( $revision['action'] ) ) {
			ssa_debug_log( "action field must be set to create revision\n", 10 );
			ssa_debug_log( print_r( $revision, true ), 10 ); //phpcs:ignore
			return;
		}
		if ( ! isset( $revision['action_summary'] ) ) {
			ssa_debug_log( "action_summary field must be set to create revision\n", 10 );
			ssa_debug_log( print_r( $revision, true ), 10 ); //phpcs:ignore
			return;
		}
		if ( ! isset( $revision['context'] ) ) {
			ssa_debug_log( "context field must be set to create revision\n", 10 );
			ssa_debug_log( print_r( $revision, true ), 10 ); //phpcs:ignore
			return;
		}

		// Call & get the stack trace for each revision before insert
		$stack_trace = ssa_get_stack_trace();

		// merge with default values
		$revision = wp_parse_args(
			$revision,
			array(
				'user_id'      => get_current_user_id(),
				'summary_vars' => array(),
				'stack_trace'  => $this->parse_stack_trace_before_insert( $stack_trace )
			)
		);
		
		// pass on an array of meta data to be batch inserted under this revision's id in the meta table
		if ( ! empty( $revision_meta ) ) {
			$revision['meta_data'] = $revision_meta;
		}
		
		// insert revision and get its id
		$revision_id = $this->insert( $revision );

		return $revision_id;
	}

	public function prepare_item_for_response( $item, $recursive = 0 ) {
		$item = parent::prepare_item_for_response( $item, $recursive );

		if ( $recursive >= 0 ) {
			$item['action_title'] = __( $item['action_title'], 'simply-schedule-appointments' );
			$item['action_summary_populated'] = $this->popuplate_action_summary_for_response( $item );
		}

		return $item;
	}

	public function get_action_title( $action, $result = 'success' ) {
		// Only get/edit action titles here
		$action_titles = array(
			'synced_successfully' => 'Appointment Synced',
			'failed_to_sync' => 'Appointment Failed to Sync',
			'first_booked' => 'Appointment Booked',
			'booked' => 'Appointment Booked',
			'canceled' => 'Appointment Canceled',
			'rescheduled' => 'Appointment Rescheduled',
			'edited'	=> 'Appointment Edited',
			'abandoned' => 'Appointment Abandoned',
			'pending_payment' => 'Appointment\'s Payment Pending',
			'pending_form' => 'Appointment\'s Form Pending',
			'assigned' => 'Appointment Assigned',
			'reassigned' => 'Appointment Reassigned',
			'notification_scheduled' => 'Notification Scheduled',
			'notification_with_duration' => 'Notification Scheduled',
			'reminder' => 'Notification Scheduled',
			'notification_sent' => 'Notification Sent',
			'notification_not_sent' => 'Notification Not Sent',
			'publish' => 'Appointment Type Created',
			'delete' => 'Appointment Type Deleted',
			'recover' => 'Appointment Type Recovered',
			'title_changed' => 'Title Changed',
			'min_booking_notice_changed' => 'Notice Required Changed',
			'buffer_before_changed' => 'Buffer Before Changed',
			'buffer_after_changed' => 'Buffer After Changed',
			'max_event_count_changed' => 'Per Day Limit Changed',
			'status_changed' => 'Appointment Type Recovered',
			'capacity_changed' => 'Capacity Changed',
			'capacity_type_changed' => 'Capacity Type Changed',
			'timezone_style_changed' => 'Timezone Changed',
			'duration_changed' => 'Duration Changed',
			'label_id_changed' => 'Label Color changed',
			'slug_changed' => 'Slug changed',
			'instructions_changed' => 'Instructions changed',
			'availability_increment_changed' => 'Availability Start Times Changed',
			'availability_type_changed' => 'Availability Type Changed',
			'availability_changed'=> 'Availability Schedule Changed',
			'web_meetings_changed'=> 'Web Meetings Changed',
			'booking_flow_settings_changed'=> 'Booking Flow Changed',
			'customer_information_changed'=> 'Customer Information Changed',
			'staff_changed' => 'Team Members Changed',
			'staff_ids_changed' => 'Team Members Staff Changed',
			'staff_capacity_changed' => 'Staff Capacity Changed',
			'has_max_capacity_changed' => 'Max Capacity Changed',
			'reminder_sent' => 'Notification Sent',
			'reminder_not_sent'=>'Notification Not Sent',
			'max_booking_notice_changed' => 'Max Booking Notice Changed',
			'shared_calendar_event_changed' => 'Shared Calendar Event Changed',
		);

		// Update the array below whenever needed
		if ( in_array( $action, array( 'sync_appointment_to_calendar' ) ) ) {
			return $result === 'success' ? $action_titles['synced_successfully'] : $action_titles['failed_to_sync'];
		}

		if ( isset( $action_titles[ $action ] ) ) {
			return $action_titles[$action];
		}

		// We shouldn't really end up here
		ssa_debug_log( "Action `$action` does not exist in get_action_title\n", 10 );
		return 'Unknown Action';
	}

	public function create_item_permissions_check( $request ) {
			// only ssa code should interact with this class
			return false;
	}

	/**
	 * Check if a given request has access to update a specific item
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function update_item_permissions_check( $request ) {
			// only ssa code should interact with this class
			return false;
	}

	/**
	 * Check and return the logged in user name
	 * Otherwise return 'A logged out user'
	 *
	 * @param array $data
	 * @return string
	 */
	public function get_user_name() {

		$current_user = wp_get_current_user();

		// We don't have a logged in user
		if ( empty( $current_user->ID ) ) {
			return __( 'A logged out user', 'simply-schedule-appointments');
		}

		// First check if who's booking/editing is a staff member
		if( class_exists( 'SSA_Staff_Model' ) ) {
			$staff_id = $this->plugin->staff_model->get_staff_id_for_user_id( $current_user->ID );

			if( ! empty( $staff_id ) ) {
				$staff = new SSA_Staff_Object( $staff_id );
				return $staff->display_name;
			}
		}

		if( ! empty( $current_user->display_name ) ) {
			return $current_user->display_name;
		}

		if( ! empty( $current_user->user_login ) ) {
			return $current_user->user_login;
		}

		// Just in case if all have failed
		return __( 'A logged out user', 'simply-schedule-appointments');

	}

	public function popuplate_action_summary_for_response( $item ) {
		/* translators: If found, between double curly braces {{ Should not be translated }}. Actions: booked, canceled, pending_payment.. */
		$action_summary = esc_html__( $item['action_summary'], 'simply-schedule-appointments' );
		$summary_vars = $item['summary_vars'];

		// Regular expression to match placeholders wrapped inside double curly braces
		$pattern = '/{{\s*(.*?)\s*}}/';

		// Check if the action_summary contains placeholders
		if ( preg_match( $pattern, $action_summary ) ) {
				// Replace the placeholders with actual values
				$action_summary = preg_replace_callback($pattern, function($matches) use ($summary_vars) {
						$placeholder = $matches[1];
						// Check if the placeholder corresponds to a valid variable name
						if ( isset( $summary_vars[ $placeholder ] ) ) {
								if ( $placeholder === 'action' ) {
									// Only allow translations for actions
									return __( $summary_vars[ $placeholder ], 'simply-schedule-appointments' );
								}
								if ( $placeholder === 'staff' ) {
									$staff_ids = $summary_vars[ $placeholder ];
									$staff_names = array();
									
									foreach ( $staff_ids as $staff_id ) {
										$name = SSA_Staff_Model::get_staff_name_by_id( $staff_id );
										array_push($staff_names, $name);
									}
									if ( empty( $staff_names ) ) {
										return __( 'no one', 'simply-schedule-appointments' );
									}
									return implode(", ", $staff_names);
								}
								return $summary_vars[ $placeholder ];
						} else {
								// Placeholder does not correspond to a valid variable name
								return $matches[0]; // return the original placeholder
						}
				}, $action_summary );
		}
		return $action_summary;

	}

	public function get_action_summary( $action = '' ) {
		switch ( $action ) {
			case 'first_booked':
				return 'Appointment booked by {{ user }}';
			case 'assigned':
				return 'Appointment assigned to {{ staff }}';
			case 'reassigned':
				return 'Appointment reassigned to {{ staff }}';
			case 'edited':
				return 'Appointment edited by {{ user }}';
			case 'rescheduled':
				return 'Appointment rescheduled by {{ user }} from {{ business_previous_start_date }} at {{ business_previous_start_time }} to {{ business_start_date }} at {{ business_start_time }}';
			case 'booked':
			case 'canceled':
			case 'abandoned':
			case 'pending_payment':
			case 'pending_form':
				return '{{ user }} changed the appointment status to {{ action }}';
			case 'notification_scheduled':
				return 'The notification was scheduled to inform the {{ recipient_type }} that an {{ action_noun }} has been {{ action_verb }}.';
			case 'notification_with_duration':
				return 'The notification was scheduled to be sent on {{ notification_date }} at {{ notification_time }} to inform the {{ recipient_type }} that an {{ action_noun }} has been {{ action_verb }}.';
			case 'reminder':
				return 'The notification was scheduled to be sent on {{ notification_date }} at {{ notification_time }} to remind the {{ recipient_type }} about the appointment.';
			case 'notification_sent':
				return 'The notification was sent by {{ notification_type }} to inform the {{ recipient_type }} that an {{ action_noun }} has been {{ action_verb }}.';
			case 'notification_not_sent':
				return "The notification by {{ notification_type }},  to inform the {{ recipient_type }} that an {{ action_noun }} has been {{ action_verb }}, could not be sent";
			case 'publish':
				return 'Appointment Type created by {{ user }}';
			case 'delete':
				return 'Appointment Type deleted by {{ user }}';
			case 'recover':
				return 'Appointment Type recovered by {{ user }}';
			case 'title_changed':
				return '{{ user }} changed the appointment type title from {{ old_field }} to {{ new_field }}';
			case 'min_booking_notice_changed':
				return '{{ user }} changed the notice required from {{ old_field }} to {{ new_field }}';
			case 'max_booking_notice_changed':
				return '{{ user }} changed how far in advance customers can book an appointment from {{ old_field }} to {{ new_field }}';
			case 'shared_calendar_event_changed':
				return '{{ user }} changed the shared event settings for google calendar';
			case 'buffer_before_changed':
				return '{{ user }} changed the buffer before from {{ old_field }} to {{ new_field }}';
			case 'buffer_after_changed':
				return '{{ user }} changed the buffer after from {{ old_field }} to {{ new_field }}';
			case 'max_event_count_changed':
				return '{{ user }} changed the maximum number of appointments from {{ old_field }} to {{ new_field }}';
			case 'status_changed':
				return '{{ user }} recovered this appointment type';
			case 'capacity_changed':
				return '{{ user }} changed the capacity from {{ old_field }} to {{ new_field }}';
			case 'capacity_type_changed':
				return '{{ user }} changed the capacity type from {{ old_field }} to {{ new_field }}';
			case 'timezone_style_changed':
				return '{{ user }} changed the timezone from {{ old_field }} to {{ new_field }}';
			case 'duration_changed':
				return '{{ user }} changed the duration form {{ old_field }} to {{ new_field }}';
			case 'label_id_changed':
				return '{{ user }} changed the label color';
			case 'slug_changed':
				return '{{ user }} changed the slug from {{ old_field }} to {{ new_field }}';
			case 'instructions_changed':
				return '{{ user }} changed the instructions to {{ new_field }}';
			case 'availability_type_changed':
				return '{{ user }} changed the availability type from {{ old_field }} to {{ new_field }}';
			case 'availability_changed':
					return '{{ user }} changed the availability schedule';
			case 'availability_increment_changed':
				return '{{ user }} changed the appointment start times from {{ old_field }} to {{ new_field }}';
			case 'web_meetings_changed':
				return '{{ user }} changed the web meetings settings';
			case 'booking_flow_settings_changed':
				return '{{ user }} changed the booking flow settings';
			case 'customer_information_changed':
				return '{{ user }} changed the customer information fields';	
			case 'staff_changed':
				return '{{ user }} changed the team member booking rules';
			case 'staff_ids_changed':
				return '{{ user }} changed the team members staff';
			case 'staff_capacity_changed':
				return '{{ user }} changed the team members staff capacity';
			case 'has_max_capacity_changed':
				return '{{ user }} switched the maximum capacity option ';
			case 'reminder_sent':
				return 'The notification was sent by {{ notification_type }} to remind the {{ recipient_type }} about the appointment';
			case 'reminder_not_sent':
				return 'The notification by {{ notification_type }} to remind the {{ recipient_type }} about the appointment could not be sent';
			default:
				return '{{ user }} changed the appointment status to {{ action }}';
		}
	}
  
	public function parse_stack_trace_before_insert( $string = '' ) {
		$pattern = '/#(\d+)\s+(.*)/';
		preg_match_all( $pattern, $string, $matches, PREG_SET_ORDER );

		$output = '';

		foreach ( $matches as $match ) {
			$order = $match[1];
			
			// Skip first two traces since these would always be for [0] -> ssa_get_stack_trace() and [1]-> SSA_Revision_Model->insert_revision
			if ( $order <= 1 ) {
				continue;
			}
			
			// Skip traces over 18 level deep
			if ( $order > 18 ) {
				break;
			}

			$output .= $match[0]."\n";
		}

		return $output;
	}

	public function insert_revision_on_notification_sent( $appointment_id, $response, $action_noun, $action_verb, $recipient_type,$notification_type,$data_after, $data_before) {
		if ($response === true || (is_array($response) && !in_array(false, $response))){
			$res = 'success';
			if($action_noun == 'appointment_start'){
				$action = 'reminder_sent';
			}
			else{
			$action = 'notification_sent';}
		}
		else{
			$res = 'failure';
			if($action_noun == 'appointment_start'){
				$action = 'reminder_not_sent';
			}
			else{
			$action = 'notification_not_sent';
		}
	}
		// Insert revision for notification sent
		$params = array(
			'result'            => $res,
			'action'            => $action,
			'appointment_id'    => $appointment_id,
			'data_after'        => $data_after,
			'data_before'       => $data_before,
			'action_noun'       => $action_noun,
			'action_verb'       => $action_verb,
			'recipient_type'    => $recipient_type,
			'notification_type' => $notification_type
		);

		$this->insert_revision_appointment( $params );
		}

	public function insert_revision_on_notification_scheduled( $appointment_id, $action_noun, $action_verb, $notification_date, $notification_time, $duration, $recipient_type, $data_after, $data_before) {
		if($duration > 0){
			if($action_noun == 'appointment_start'){
				$action = 'reminder';
			}else{
				$action = 'notification_with_duration';
			}
		}else{
			if($action_noun == 'appointment_start'){
				return ;
			}else{
			$action = 'notification_scheduled';
			}
		}
		// Insert revision for notification scheduled
		$params = array(
			'result'            => 'success',
			'action'            => $action,
			'appointment_id'    => $appointment_id,
			'data_after'        => $data_after,
			'data_before'       => $data_before,
			'action_noun'       => $action_noun,
			'action_verb'       => $action_verb,
			'recipient_type'    => $recipient_type,
			'notification_date'       => $notification_date,
			'notification_time'       => $notification_time,
		);

		$this->insert_revision_appointment( $params );
		$username = $this->get_user_name();
		}

		public function insert_revision_appointment_type( $params ) {
						$revision = array(
				'result'         => $params['result'],
				'appointment_type_id' => $params['appointment_type_id'],
				'action'         => $params['action'],
				'action_title'	 => $this->get_action_title( $params['action'] ),
				'action_summary' => $this->get_action_summary( $params['action'] ),
				'summary_vars'   => array(
					'user'   		=> $this->get_user_name(),
					'old_field'     => $params['old_field'],
					'new_field'     => $params['new_field']
				),
				'context'        => 'appointment type',
			);
	
			// in the revision_meta
			// only set meta_value_before if it has a value.
			// because if the field is set to null, the db will not insert the record.
			$revision_meta = array();
	
			// append appointment status changes
			if ( ! isset( $params['data_after']['status'] ) && isset( $params['data_before']['status'] ) ) {
				// If status is not set we assume it didn't change
				$params['data_after']['status'] = $params['data_before']['status'];
			}
	
			$revision_meta['status']['meta_value'] = $params['data_after']['status'];
			
			if ( isset( $params['data_before']['status'] ) ) {
				$revision_meta['status']['meta_value_before'] = $params['data_before']['status'];
			}
	
			// append appointment raw data changes
			$revision_meta['raw_data']['meta_value'] = $params['data_after'];
			if ( isset( $params['data_before'] ) ) {
				$revision_meta['raw_data']['meta_value_before'] = $params['data_before'];
			}
	
			// insert revision
			$this->insert_revision( $revision, $revision_meta );
		}

	public function insert_revision_created_appointment_type( $appointment_type_id, $data_after, $data_before = null) {
		$data_after['status'] = 'publish';
		$params = array(
			'result'			  => 'success',
			'action'			  => 'publish',
			'appointment_type_id' => $appointment_type_id,
			'old_field'           => '',
			'new_field'           => 'published',
			'data_after'		  => $data_after,
			'data_before'		  => $data_before
		);
		$this->insert_revision_appointment_type( $params );
	}
	
	public function insert_revision_deleted_appointment_type( $appointment_type_id, $data_after, $data_before) {
		$data_after['status'] = 'delete';
		$params = array(
			'result'		      => 'success',
			'action'			  => 'delete',
			'appointment_type_id' => $appointment_type_id,
			'old_field'           => 'published',
			'new_field'           => 'deleted',
			'data_after'		  => $data_after,
			'data_before'		  => $data_before
		);
		
		$this->insert_revision_appointment_type( $params );
	}

	public function insert_revision_updated_appointment_type( $appointment_type_id, $data_after, $data_before ) {
		if(empty($data_after) || empty($data_before)){
			return;
		}
		$changed_fields = $this->get_appt_type_changed_fields($data_after,$data_before);
		foreach($changed_fields as $changed_field){
			$old_field = $data_before[$changed_field];
			$new_field = $data_after[$changed_field];
			if($changed_field !== 'capacity' && $changed_field !== 'max_event_count'){
				if (is_numeric($old_field) && is_int($old_field + 0)) {
					$old_field = $this->display_duration($old_field);
				}
				if (is_numeric($new_field) && is_int($new_field + 0)) {
					$new_field = $this->display_duration($new_field);
				}
			}
			
			$params = array(
				'result'		      => 'success',
				'action'			  => $changed_field.'_changed',
				'appointment_type_id' => $appointment_type_id,
				'old_field' 		  => $old_field ,
				'new_field'		 	  => $new_field,
				'data_after'		  => $data_after,
				'data_before'		  => $data_before
			);
			$this->insert_revision_appointment_type( $params );
		}
	}

	public function get_appt_type_changed_fields($data_after,$data_before){		
		$changed_fields =[];

		foreach( $data_after as $key => $field ) {
			if ( empty( $data_before[ $key ] ) ) {
				continue;
			}

			// No Need to record the re-ordering of appointment types
			if ( $key === 'display_order'){
				continue;
			}

			if( $data_before [ $key ] !== $field ) {
				if ( $key === 'custom_customer_information') {
					$key = 'customer_information';
				}
				$changed_fields[] = $key;
			}
		}
		
		return $changed_fields;
	}

	//convert minutes to hours / days / weeks
	public function display_duration($duration) {
		$minutes = intval($duration);

		if ($minutes === 0) {
			return '0';
		}

		$type = 'minute';
		$num = $minutes;

		if ($minutes % (7 * 24 * 60) === 0) {
			$num = $minutes / (7 * 24 * 60);
			$type = 'week';
		} elseif ($minutes % (24 * 60) === 0) {
			$num = $minutes / (24 * 60);
			$type = 'day';
		} elseif ($minutes % 60 === 0) {
			$num = $minutes / 60;
			$type = 'hour';
		}
	
		$displayType = $num === 1 ? $type : $type . 's';
		return $num . ' ' . $displayType;
	}
	
}


