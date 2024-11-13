<?php
/**
 * Simply Schedule Appointments Appointment Type Model.
 *
 * @since   0.0.3
 * @package Simply_Schedule_Appointments
 */

use League\Period\Period;

/**
 * Simply Schedule Appointments Appointment Type Model.
 *
 * @since 0.0.3
 */
class SSA_Appointment_Type_Model extends SSA_Db_Model {
	protected $slug = 'appointment_type';
	protected $version = '2.9.2';

	/**
	 * Parent plugin class.
	 *
	 * @since 0.0.3
	 *
	 * @var   Simply_Schedule_Appointments
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * @since  0.0.3
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
	 * @since  0.0.3
	 */
	public function hooks() {
		add_action( 'ssa/appointment_type/after_insert', array( $this, 'invalidate_appointment_type_cache'), 1000 );
		add_action( 'ssa/appointment_type/after_update', array( $this, 'invalidate_appointment_type_cache'), 1000 );
		add_action( 'ssa/appointment_type/after_delete', array( $this, 'invalidate_appointment_type_cache'), 1000 );
		add_filter( 'ssa/appointment_type/prepare_item_for_response', array( $this, 'set_default_attributes' ), 100);
		
		add_filter( 'ssa/appointment_type/before_update', array( $this, 'recheck_capacity_settings'), 10, 3 );
		add_action( 'ssa/settings/staff/updated', array( $this, 'recheck_appointment_types_capacity_staff_toggled' ), 10, 2 );
		add_action( 'ssa/settings/resources/updated', array( $this, 'recheck_appointment_types_capacity_resources_toggled' ), 10, 2 );
		add_action( 'ssa/staff/after_update', array( $this, 'invalidate_appointment_type_cache'), 1000 );
		add_action( 'ssa/staff/after_delete', array( $this, 'invalidate_appointment_type_cache'), 1000 );

		add_action( 'ssa/resource_group/after_insert', array( $this, 'invalidate_appointment_type_cache'), 1000 );
		add_action( 'ssa/resource_group/after_update', array( $this, 'invalidate_appointment_type_cache'), 1000 );
		add_action( 'ssa/resource_group/after_delete', array( $this, 'invalidate_appointment_type_cache'), 1000 );

		add_action( 'ssa/resource/after_insert', array( $this, 'invalidate_appointment_type_cache'), 1000 );
		add_action( 'ssa/resource/after_update', array( $this, 'invalidate_appointment_type_cache'), 1000 );
		add_action( 'ssa/resource/after_delete', array( $this, 'invalidate_appointment_type_cache'), 1000 );
		
		add_action( 'ssa/async/gcal_cleanup', array( $this, 'cleanup_main_excluded_calendars'), 1000 );
	}
	
	/**
	 * resets capacity limits after resource feature is disabled or enabled
	 *
	 */
	public function recheck_appointment_types_capacity_resources_toggled( $settings, $old_settings){
		$all_settings = $this->plugin->settings->get();

		// Resources got disabled
		if ( !empty( $old_settings['enabled'] ) && empty( $settings['enabled'] )) {
			$appointment_types = $this->get_all_appointment_types();
			foreach ( $appointment_types as $appointment_type ) {
				// Check Staff is enabled and not empty and required for this appt type 
				$appointment_type_staff_settings = $appointment_type['staff'];
				if ( 
					! empty( $all_settings['staff']['enabled'] ) && 
					! empty( $appointment_type_staff_settings ) && 
					! empty( $appointment_type_staff_settings['required'] ) 
					) {
					continue;
				}

				// Check is the capacity max for this appt type
				if ( $appointment_type['capacity'] == SSA_Constants::CAPACITY_MAX) {
					$appointment_type_update_data = array(
						'capacity' => 1,
					);
					$this->update( $appointment_type['id'], $appointment_type_update_data );
				}
			}
			return;
		}

		// Resources got enabled 
		if ( empty( $old_settings['enabled'] ) && !empty( $settings['enabled'] ) ) {
			$appointment_types = $this->get_all_appointment_types();
			foreach ( $appointment_types as $appointment_type ) {
				// Check Resources not empty for this appt type
				$appointment_type_resource_settings = $appointment_type['resources_settings'];
				if ( empty( $appointment_type_resource_settings ) ) {
					continue;
				}

				// Check Staff is enabled and not empty and required for this appt type 
				$appointment_type_staff_settings = $appointment_type['staff'];
				if ( 
					! empty( $all_settings['staff']['enabled'] ) && 
					! empty( $appointment_type_staff_settings ) && 
					! empty( $appointment_type_staff_settings['required'] ) 
					) {
						$appointment_type_update_data = array(
							'resource_capacity' => $appointment_type['staff_capacity'],
						);
				}

				// Check capacity for appointment type
				if ( $appointment_type['capacity'] == 1 ) {
					$appointment_type_update_data = array(
						'capacity' => SSA_Constants::CAPACITY_MAX,
					);
				}
				$this->update( $appointment_type['id'], $appointment_type_update_data );
			}
		}
	}

	/**
	 * resets capacity limits after staff feature is disabled or enabled
	 *
	 */
	public function recheck_appointment_types_capacity_staff_toggled( $settings, $old_settings){
		$all_settings = $this->plugin->settings->get();

		// Staff got disabled
		if ( !empty( $old_settings['enabled'] ) && empty( $settings['enabled'] ) ) {
			$appointment_types = $this->get_all_appointment_types();
			foreach ( $appointment_types as $appointment_type ) {
				$appointment_type_resource_settings = $appointment_type['resources_settings'];
				// Check Resources is enabled and not empty for this appt type
				if ( ! empty( $all_settings['resources']['enabled'] ) && ! empty( $appointment_type_resource_settings ) ) {
					continue;
				}

				// Check is the capacity max for this appt type
				if ( $appointment_type['capacity'] == SSA_Constants::CAPACITY_MAX) {
					$appointment_type_update_data = array(
						'capacity' => 1,
					);
					$this->update( $appointment_type['id'], $appointment_type_update_data );
				}
			}
			return;
		}

		// Staff got enabled 
		if ( empty( $old_settings['enabled'] ) && !empty( $settings['enabled'] ) ) {
			$appointment_types = $this->get_all_appointment_types();
			foreach ( $appointment_types as $appointment_type ) {
				// Check Staff not empty for this appt type and required
				$appointment_type_staff_settings = $appointment_type['staff'];
				
				if ( empty( $appointment_type_staff_settings ) ) {
					continue;
				}

				if( empty( $appointment_type_staff_settings['required'] ) ) {
					continue;
				}

				// Check Resources is enabled and not empty for this appt type
				if ( ! empty( $all_settings['resources']['enabled'] ) && ! empty( $appointment_type_resource_settings ) ) {
					$appointment_type_update_data = array(
						'staff_capacity' => $appointment_type['resource_capacity'],
					);
				}

				// Check capacity for appointment type
				if ( $appointment_type['capacity'] == 1 ) {
					$appointment_type_update_data = array(
						'capacity' => SSA_Constants::CAPACITY_MAX,
					);
				}
				
				if ( !empty( $appointment_type_update_data ) ) {
					$this->update( $appointment_type['id'], $appointment_type_update_data );
				}
			}
		}
	}
	
	public function set_default_attributes( $item )
	{
		$default_booking_flow_settings = array(
			'booking_flow' => 'expanded',
			'fallback_booking_flow' => 'expanded',
			'time_view' => 'time_of_day_columns',
			'suggest_first_available' => 0,
			'suggest_first_available_within_minutes' => 0,
		);

		// If First Time Creating Item
		if ( empty( $item["booking_flow_settings"] ) ) {
			$item["booking_flow_settings"] = $default_booking_flow_settings;
		}

		// If Edition is Downgraded to Basic or Plus (or using old booking app)
		$developer_settings = $this->plugin->developer_settings->get();
		if ( !empty( $developer_settings['old_booking_app'] ) ||  !$this->plugin->settings_installed->is_installed( 'booking_flows' ) ) {
			$item["booking_flow_settings"] = $default_booking_flow_settings;
			// If Booking Layout is Not Week or Month
			if ($item['booking_layout'] !== 'week' && $item['booking_layout'] !== 'month' ) {
				$item['booking_layout'] = 'week';
			}
		}
		
		if( ! empty( $item[ 'label_id' ] ) ) {
			$label = $this->plugin->appointment_type_label_model->get( $item[ 'label_id' ] );
			if ( empty( $label ) ) {
				// should never happen
				$label = $this->plugin->appointment_type_label_model->query()[0];
			}
			$item[ 'label_name' ] = $label[ 'name' ];
			$item[ 'label_color' ] = $label[ 'color' ];
		}

		if( $this->plugin->settings_installed->is_enabled( 'staff' ) ) {
			$item = $this->append_staff_objects( $item );
		}

		if( $this->plugin->settings_installed->is_enabled( 'resources' ) ) {
			$item = $this->append_resources_object( $item );
		}

		return $item;
	}

	protected $indexes = array(
		'author_id' => [ 'author_id' ],
		'status' => [ 'status' ],
		'date_created' => [ 'date_created' ],
		'date_modified' => [ 'date_modified' ],
	);

	public function belongs_to() {
		return array(
			'Author' => array(
				'model' => 'WP_User_Model',
				'foreign_key' => 'author_id',
			),
			'AppointmentTypeLabel' => array(
				'model' => $this->plugin->appointment_type_label_model,
				'foreign_key' => 'label_id',
			),
		);
	}

	public function has_many() {
		return array(
			'Appointment' => array(
				'model' => $this->plugin->appointment_model,
				'foreign_key' => 'appointment_type_id',
			),
		);
	}

	protected $schema = array(
		'author_id' => array(
			'field' => 'author_id',
			'label' => 'Author',
			'default_value' => 0,
			'format' => '%d',
			'mysql_type' => 'BIGINT',
			'mysql_length' => 20,
			'mysql_unsigned' => true,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
		),
		'title' => array(
			'field' => 'title',
			'label' => 'Title',
			'default_value' => false,
			'format' => '%s',
			'mysql_type' => 'TINYTEXT',
			'mysql_length' => false,
			'mysql_unsigned' => false,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
		),
		'slug' => array(
			'field' => 'slug',
			'label' => 'Slug',
			'default_value' => false,
			'format' => '%s',
			'mysql_type' => 'TINYTEXT',
			'mysql_length' => false,
			'mysql_unsigned' => false,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
		),
		'description' => array(
			'field' => 'description',
			'label' => 'Description',
			'default_value' => false,
			'format' => '%s',
			'mysql_type' => 'TINYTEXT',
			'mysql_length' => false,
			'mysql_unsigned' => false,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
		),
		'instructions' => array(
			'field' => 'instructions',
			'label' => 'Instructions',
			'default_value' => false,
			'format' => '%s',
			'mysql_type' => 'TEXT',
			'mysql_length' => '',
			'mysql_unsigned' => false,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
		),
		'label' => array(
			'field' => 'label',
			'label' => 'Label',
			'default_value' => false,
			'format' => '%s',
			'mysql_type' => 'TINYTEXT',
			'mysql_length' => false,
			'mysql_unsigned' => false,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
		),
		'label_id' => array(
			'field' => 'label_id',
			'label' => 'Label ID',
			'default_value' => 0,
			'format' => '%d',
			'mysql_type' => 'BIGINT',
			'mysql_length' => 20,
			'mysql_unsigned' => true,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
		),
		'capacity' => array(
			'field' => 'capacity',
			'label' => 'Capacity',
			'default_value' => 1,
			'format' => '%d',
			'mysql_type' => 'MEDIUMINT',
			'mysql_length' => '6',
			'mysql_unsigned' => false,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
		),
		'staff_capacity' => array(
			'field' => 'staff_capacity',
			'label' => 'Staff Capacity',
			'default_value' => 1,
			'format' => '%d',
			'mysql_type' => 'MEDIUMINT',
			'mysql_length' => '6',
			'mysql_unsigned' => false,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
		),
		'resource_capacity' => array(
			'field' => 'resource_capacity',
			'label' => 'Resource Capacity',
			'default_value' => 1,
			'format' => '%d',
			'mysql_type' => 'MEDIUMINT',
			'mysql_length' => '6',
			'mysql_unsigned' => false,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
		),
		'capacity_type' => array(
			'field' => 'capacity_type',
			'label' => 'Capacity Type',
			'default_value' => 'individual',
			'format' => '%s',
			'mysql_type' => 'VARCHAR',
			'mysql_length' => '10',
			'mysql_unsigned' => false,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
		),
		'has_max_capacity' => array(
			'field' => 'has_max_capacity',
			'label' => 'Has Max Capacity',
			'default_value' => 1,
			'format' => '%d',
			'mysql_type' => 'TINYINT',
			'mysql_length' => '1',
			'mysql_unsigned' => false,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
		),
		'buffer_before' => array(
			'field' => 'buffer_before',
			'label' => 'Buffer Before',
			'description' => 'Buffer before event',
			'example' => 'Don\'t let customers book an appointment if I have something else on my calendar ending 15 minutes before the appointment would start',
			'default_value' => false,
			'format' => '%s',
			'mysql_type' => 'MEDIUMINT',
			'mysql_length' => '8',
			'mysql_unsigned' => true,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
		),
		'duration' => array(
			'field' => 'duration',
			'label' => 'Duration',
			'default_value' => false,
			'format' => '%s',
			'mysql_type' => 'MEDIUMINT',
			'mysql_length' => '8',
			'mysql_unsigned' => true,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
		),
		'buffer_after' => array(
			'field' => 'buffer_after',
			'label' => 'Buffer After',
			'description' => 'Buffer after event',
			'example' => 'Don\'t let customers book an appointment if I have something else on my calendar starting 15 minutes after the appointment would start',
			'default_value' => false,
			'format' => '%s',
			'mysql_type' => 'MEDIUMINT',
			'mysql_length' => '8',
			'mysql_unsigned' => true,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
		),
		'min_booking_notice' => array(
			'field' => 'min_booking_notice',
			'label' => 'Minimum Booking Notice',
			'description' => 'Prevent events less than X minutes away',
			'default_value' => false,
			'format' => '%s',
			'mysql_type' => 'MEDIUMINT',
			'mysql_length' => '8',
			'mysql_unsigned' => true,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
		),
		'max_booking_notice' => array(
			'field' => 'max_booking_notice',
			'label' => 'Maximum Booking Notice',
			'description' => 'Prevent events more than X minutes away',
			'default_value' => false,
			'format' => '%s',
			'mysql_type' => 'MEDIUMINT',
			'mysql_length' => '8',
			'mysql_unsigned' => true,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
		),
		'max_event_count' => array(
			'field' => 'max_event_count',
			'label' => 'Max number of events',
			'description' => 'Prevent more than X events per day',
			'default_value' => false,
			'format' => '%s',
			'mysql_type' => 'SMALLINT',
			'mysql_length' => '5',
			'mysql_unsigned' => true,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
		),
		'booking_start_date' => array(
			'field' => 'booking_start_date',
			'label' => 'Booking Start Date',
			'description' => 'When can a user start buying/booking?',
			'example' => 'Buy between 1/1 and 1/15',
			'default_value' => false,
			'format' => '%s',
			'mysql_type' => 'DATETIME',
			'mysql_length' => '',
			'mysql_unsigned' => false,
			'mysql_allow_null' => true,
			'mysql_extra' => '',
			'cache_key' => false,
		),
		'booking_end_date' => array(
			'field' => 'booking_end_date',
			'label' => 'Booking End Date',
			'default_value' => false,
			'format' => '%s',
			'mysql_type' => 'DATETIME',
			'mysql_length' => '',
			'mysql_unsigned' => false,
			'mysql_allow_null' => true,
			'mysql_extra' => '',
			'cache_key' => false,
		),
		'availability_type' => array(
			'field' => 'availability_type',
			'label' => 'Availability Type',
			'default_value' => 'available_blocks',
			'format' => '%s',
			'mysql_type' => 'VARCHAR',
			'mysql_length' => '16',
			'mysql_unsigned' => false,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
		),
		'availability' => array(
			'field' => 'availability',
			'type' => 'array',
			'label' => 'Default Availability',
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
		'availability_start_date' => array(
			'field' => 'availability_start_date',
			'label' => 'Availability Start Date',
			'description' => 'When a customer books, when will the appointment times be scheduled for?',
			'example' => 'Schedule appointments for 1/15-1/31',
			'default_value' => false,
			'format' => '%s',
			'mysql_type' => 'DATETIME',
			'mysql_length' => '',
			'mysql_unsigned' => false,
			'mysql_allow_null' => true,
			'mysql_extra' => '',
			'cache_key' => false,
		),
		'availability_end_date' => array(
			'field' => 'availability_end_date',
			'label' => 'Availability End Date',
			'default_value' => false,
			'format' => '%s',
			'mysql_type' => 'DATETIME',
			'mysql_length' => '',
			'mysql_unsigned' => false,
			'mysql_allow_null' => true,
			'mysql_extra' => '',
			'cache_key' => false,
		),
		'availability_increment' => array(
			'field' => 'availability_increment',
			'label' => 'Availability Increment',
			'default_value' => false,
			'format' => '%s',
			'mysql_type' => 'MEDIUMINT',
			'mysql_length' => '8',
			'mysql_unsigned' => true,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
		),
		'timezone_style' => array(
			'field' => 'timezone_style',
			'label' => 'Timezone Style',
			'description' => 'Localized: Invitees will see your availability in their time zone. Recommended for virtual meetings. Locked: Invitees will see your availability in a specific time zone. Recommended for in-person meetings.',
			'default_value' => false,
			'format' => '%s',
			'mysql_type' => 'TINYTEXT',
			'mysql_length' => false,
			'mysql_unsigned' => false,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
		),
		'booking_layout' => array(
			'field' => 'booking_layout',
			'label' => 'Booking Layout',
			'description' => 'Default layout to use for this appointment type',
			'default_value' => 'week',
			'format' => '%s',
			'mysql_type' => 'VARCHAR',
			'mysql_length' => '16',
			'mysql_unsigned' => false,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
		),
		'customer_information' => array(
			'field' => 'customer_information',
			'type' => 'array',
			'label' => 'Customer Information',
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
		'custom_customer_information' => array(
			'field' => 'custom_customer_information',
			'type' => 'array',
			'label' => 'Customer Information',
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
		'notifications' => array(
			'field' => 'notifications',
			'type' => 'array',
			'label' => 'Notifications',
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
		'payments' => array(
			'field' => 'payments',
			'type' => 'array',
			'label' => 'Payments',
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
		'staff' => array(
			'field' => 'staff',
			'type' => 'array',
			'label' => 'Staff',
			'default_value' => '',
			'format' => '%s',
			'mysql_type' => 'TEXT',
			'mysql_length' => false,
			'mysql_unsigned' => false,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
			'encoder' => 'json',
		),
		'google_calendars_availability' => array(
			'field' => 'google_calendars_availability',
			'type' => 'array',
			'label' => 'Availability Calendars (Google)',
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
		'google_calendar_booking' => array(
			'field' => 'google_calendar_booking',
			'label' => 'Booking Calendar (Google)',
			'default_value' => false,
			'format' => '%s',
			'mysql_type' => 'TINYTEXT',
			'mysql_length' => false,
			'mysql_unsigned' => false,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
		),
		'shared_calendar_event' => array(
			'field' => 'shared_calendar_event',
			'label' => 'Invite Customer to Calendar Event',
			'default_value' => 0,
			'format' => '%d',
			'mysql_type' => 'TINYINT',
			'mysql_length' => '1',
			'mysql_unsigned' => false,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
		),
		'web_meetings' => array(
			'field' => 'web_meetings',
			'type' => 'array',
			'label' => 'Web Meetings',
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
		'mailchimp' => array(
			'field' => 'mailchimp',
			'type' => 'array',
			'label' => 'Mailchimp',
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
			'default_value' => 'publish', // publish, draft, trash, delete
			'format' => '%s',
			'mysql_type' => 'VARCHAR',
			'mysql_length' => '16',
			'mysql_unsigned' => false,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
			'supports' => array(
				'soft_delete' => true,
			),
		),
		'display_order' => array(
			'field' => 'display_order',
			'label' => 'Order',
			'description' => 'Store the display order',
			'default_value' => 0,
			'format' => '%d',
			'mysql_type' => 'INT',
			'mysql_length' => '5',
			'mysql_unsigned' => true,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
		),
		'date_created' => array(
			'field' => 'date_created',
			'label' => 'Date Created',
			'default_value' => false,
			'format' => '%s',
			'mysql_type' => 'DATETIME',
			'mysql_length' => '',
			'mysql_unsigned' => false,
			'mysql_allow_null' => true,
			'mysql_extra' => '',
			'cache_key' => false,
		),
		'date_modified' => array(
			'field' => 'date_modified',
			'label' => 'Date Modified',
			'default_value' => false,
			'format' => '%s',
			'mysql_type' => 'DATETIME',
			'mysql_length' => '',
			'mysql_unsigned' => false,
			'mysql_allow_null' => true,
			'mysql_extra' => '',
			'cache_key' => false,
		),
		'resources_settings' => array(
			'field' => 'resources_settings',
			'type' => 'array',
			'label' => 'Resources Settings',
			'description' => 'Resources Settings to use for this appointment type',
			'default_value' => array(),
      		'format' => '%s',
			'mysql_type' => 'TEXT',
			'mysql_length' => false,
			'mysql_unsigned' => false,
			'mysql_allow_null' => false,
			'mysql_extra' => '',
			'cache_key' => false,
			'encoder' => 'json',
		),
		'booking_flow_settings' => array(
			'field' => 'booking_flow_settings',
			'type' => 'array',
			'label' => 'Booking Flow Settings',
			'description' => 'Booking Flow Settings to use for this appointment type',
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
	);


	public function get_computed_schema() {
		if ( !empty( $this->computed_schema ) || false === $this->computed_schema ) {
			return $this->computed_schema;
		}

		$this->computed_schema = array(
			'version' => '2019-07-02',
			'fields' => array(
				'has_sms' => array(
					'name' => 'has_sms',

					'get_function' => array( $this->plugin->sms, 'has_sms_for_appointment_type_id' ),
					'get_input_path' => $this->primary_key,

					// Deprecated, expecting php values only
					// 'set_function' => array( 'SSA_Utils', 'moment_to_php_format' ),
					// 'set_result_path' => 'date_format',
				),

			),
		);

		return $this->computed_schema;
	}

			
	public function register_custom_routes() {
		$namespace = $this->api_namespace.'/v' . $this->api_version;
		$base = $this->get_api_base();
		
		// this endpoint is only meant for testing availability data - invalidates cache on each request
		register_rest_route( $namespace, '/' . $base . '/(?P<id>[\d]+)/availability-refreshed', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_availability_refreshed' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'            => array(
					'context'          => array(
						'default'      => 'view',
					),
				),
			),
		) );

		// endpoint below returns cached availability - recommended - more performant
		register_rest_route( $namespace, '/' . $base . '/(?P<id>[\d]+)/availability', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_availability' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'            => array(
					'context'          => array(
						'default'      => 'view',
					),
				),
			),
		) );

		register_rest_route( $namespace, '/' . $base . '/(?P<id>[\d]+)/recover', array(
			array(
				'methods'         => WP_REST_Server::CREATABLE,
				'callback'        => array( $this, 'recover_appointment_type' ),
				'permission_callback' => array( $this, 'create_item_permissions_check' ),
				'args'            => array(
					'context'          => array(
						'default'      => 'view',
					),
				),
			),
		) );
	}

	public function get_items_permissions_check( $request ) {
		if ( current_user_can( 'ssa_manage_others_appointments' ) ) {
			return true;
		}
		
		$granted = $this->nonce_permissions_check( $request );
		if ( $granted ) {
			return $granted;
		}

		// TODO: implement ssa_nonce check as a fallback

		return false;
	}

	/**
	 * Check if a given request has access to create items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function create_item_permissions_check( $request ) {
		if ( current_user_can( 'ssa_manage_appointment_types' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if a given request has access to create items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function update_item_permissions_check( $request ) {
		if ( current_user_can( 'ssa_manage_appointment_types' ) ) {
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
		if ( current_user_can( 'ssa_manage_appointment_types' ) ) {
			return true;
		}

		return false;
	}

	public function filter_where_conditions( $where, $args ) {

		global $wpdb;

		if ( ! empty( $args['label_id'] ) ) {
			if ( is_array( $args['label_id'] ) ) {
				$where .= ' AND (';
				foreach ( $args['label_id'] as $key => $label_id ) {
					$where .= $wpdb->prepare( "`label_id` = " . '%d' , $label_id );
					if ( $key + 1 < count( $args['label_id'] ) ) {
						$where .= ' OR ';
					}
				}
				$where .= ') ';
			} else {
				$where .= $wpdb->prepare( " AND `label_id` = " . '%d' , sanitize_text_field( $args['label_id'] ) );
			}
		}
		
		return $where;
	}

	/**
	 * Get a collection of items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		$params = $request->get_params();
		$data = $this->query( $params );

		// Set staff_ids and resource_group_ids
		if ( $this->plugin->settings_installed->is_enabled( 'staff' ) ) {
			foreach ( $data as $key => $appointment_type ) {
				if ( empty( $appointment_type['staff_ids'] ) ) {
					$data[ $key ]['staff_ids'] = $this->plugin->staff_appointment_type_model->get_staff_ids_for_appointment_type_id( $appointment_type['id'] );
				}
			}
		}

		if ( $this->plugin->settings_installed->is_enabled( 'resources' ) ) {
			foreach ( $data as $key => $appointment_type ) {
				if ( empty( $appointment_type['resource_group_ids'] ) ) {
					$data[ $key ]['resource_group_ids'] = $this->plugin->resource_group_appointment_type_model->get_resource_group_ids( $appointment_type['id'] );
				}
			}
		}
	
		$response = array(
			'response_code' => 200,
			'error' => '',
			'data' => $data,
		);

		return new WP_REST_Response( $response, 200 );
	}

	public function append_staff_objects( $appointment_type ) {

		if ( empty( $appointment_type['staff']['required'] ) ) {
			return $appointment_type;
		}
	
		if ( ! empty( $appointment_type['staff_objects'] ) ) {
			return $appointment_type;
		}
	
		$associated_staff_ids = $this->plugin->staff_appointment_type_model->get_staff_ids_for_appointment_type_id( $appointment_type['id'] );
		$associated_staff = $this->plugin->staff_model->query( array(
			'id' => $associated_staff_ids,
			'fields' => array(
				'id',
				'display_name',
				'name'
			)
		));

		$appointment_type['staff_objects'] = $associated_staff;

		return $appointment_type;
	}

	public function append_resources_object( $appointment_type ) {

		if ( empty( $appointment_type['resources_settings'] ) ) {
			return $appointment_type;
		}
	
		if ( ! empty( $appointment_type['resources_objects'] ) ) {
			return $appointment_type;
		}
	
		$associated_resource_group_ids = $this->plugin->resource_group_appointment_type_model->get_resource_group_ids( $appointment_type['id'] );
	
		$associated_resource_groups = $this->plugin->resource_group_model->query( array(
			'id' => $associated_resource_group_ids,
			'fields' => array(
				'id',
				'title',
				'resource_type'
			)
		));
	
		foreach( $associated_resource_groups as $resource_group ) {
			if( $resource_group['resource_type'] === 'identifiable' ) {
				$associated_resource_ids = $this->plugin->resource_group_resource_model->get_resource_ids( $resource_group['id'] );
				$associated_resources = array();
				if ( !empty( $associated_resource_ids ) ) {
					$associated_resources = $this->plugin->resource_model->query( array(
						'id' => $associated_resource_ids,
						'status' => 'publish',
						'fields' => array(
							'id',
							'title',
						)
					));
				}
				$resource_group['resources'] = $associated_resources;
			}

			$appointment_type['resources_objects'][] = $resource_group;
		}

		return $appointment_type;
	}


	public function get( $appointment_type_id, $recursive=0 ) {
		$appointment_type = parent::get( $appointment_type_id, $recursive );

		if ( !is_array( $appointment_type ) ) {
			return $appointment_type;
		}

		if ( empty( $appointment_type['availability_increment'] ) ) {
			$appointment_type['availability_increment'] = 15;
		}

		if ( ! empty( $appointment_type['has_max_capacity'] )
			&& ! empty( $appointment_type['has_max_capacity'] )
			&& SSA_Constants::CAPACITY_MAX == $appointment_type['capacity']
		) {
			$appointment_type['has_max_capacity'] = 0;
		}

		if ( $this->plugin->settings_installed->is_enabled( 'staff' ) ) {
			if ( empty( $appointment_type['staff_ids'] ) ) {
				$appointment_type['staff_ids'] = $this->plugin->staff_appointment_type_model->get_staff_ids_for_appointment_type_id( $appointment_type_id );
			}
		}

		if ( $this->plugin->settings_installed->is_enabled( 'resources' ) ) {
			if ( empty( $appointment_type['resource_group_ids'] ) ) {
				$appointment_type['resource_group_ids'] = $this->plugin->resource_group_appointment_type_model->get_resource_group_ids( $appointment_type_id );
			}
		}

		if ( isset( $appointment_type['web_meetings'] ) && empty( $appointment_type['web_meetings'] ) ) {
			$appointment_type['web_meetings'] = array(
				'provider' => '',
				'url'      => '',
			);
		}

		return $appointment_type;
	}

	public function DEPRECATED_get_availability( $request ) {
		$params = $request->get_params();
		if ( empty( $params['id'] ) ) {
			return false;
		}
		$appointment_type_id = (int)sanitize_text_field( $params['id'] );

		$params = shortcode_atts( array(
			'start_date_min' => '',
			'start_date_max' => '',
			'end_date_min' => '',
			'end_date_max' => '',
			'refresh' => '',

			'excluded_appointment_ids' => array(),
		), $params );

		$transient_key = 'ssa_api_availability_'.$appointment_type_id.'_'.md5( json_encode( $params ) );
		// $response = get_transient( $transient_key ); // TODO: Implement cache
		if ( empty( $params['refresh'] ) && !empty( $response ) ) {
			$response['cached'] = true;
			return $response;
		}

		$this->plugin->google_calendar->maybe_queue_refresh_check( $appointment_type_id );

		if ( empty( $params['start_date_min'] ) ) {
			$params['start_date_min'] = $params['start_date_max'];
		}
		if ( empty( $params['end_date_max'] ) ) {
			$params['end_date_max'] = $params['end_date_min'];
		}
		if ( empty( $params['start_date_min'] ) ) {
			$params['start_date_min'] = gmdate('Y-m-d H:i:s');
		}
		if ( empty( $params['start_date_max'] ) && empty( $params['end_date_max'] ) ) {
			$params['start_date_max'] = gmdate('Y-m-d', strtotime( $params['start_date_min'] ) + 3600*24*29 );
		}

		$params = array_filter( $params );

		$bookable_appointments = $this->plugin->availability_functions->get_bookable_appointments( $appointment_type_id, $params );
		$data = array();
		foreach ($bookable_appointments as $key => $bookable_appointment) {
			$data[] = array(
				'start_date' => $bookable_appointment['period']->getStartDate()->format('Y-m-d H:i:s'),
			);
		}

		$response = array(
			'response_code' => 200,
			'error' => '',
			'data' => $data,
		);

		return $response;
	}

	// only used for testing availability data in CI - invalidates cache
	public function get_availability_refreshed( $request ) {
		// invalidate caches
		$this->plugin->availability_cache_invalidation->invalidate_everything();
		return $this->get_availability($request);
	}

	public function get_availability( $request ) {
		$params = $request->get_params();
		$args = array();
		
		if( isset( $params['query_args'] ) ) {
			$args = shortcode_atts( array(
				'staff_ids_all_required' => array(),
				'staff_ids_any_required' => array(),
				'resources_required' => array(),
			), $params['query_args'] );
		}

		if( isset( $args['staff_ids_all_required'] ) ) {
			$args['staff_ids_all_required'] = is_array( $args['staff_ids_all_required'] ) ? $args['staff_ids_all_required'] : array( $args['staff_ids_all_required'] );
		}

		if( isset( $args['staff_ids_any_required'] ) ) {
			$args['staff_ids_any_required'] = is_array( $args['staff_ids_any_required'] ) ? $args['staff_ids_any_required'] : array( $args['staff_ids_any_required'] );
		}

		if( isset( $args['resources_required'] ) ) {
			$args['resources_required'] = is_array( $args['resources_required'] ) ? $args['resources_required'] : array( $args['resources_required'] );
		}

		if ( empty( $params['id'] ) ) {
			return false;
		}
		$appointment_type_id = (int)sanitize_text_field( $params['id'] );
		$appointment_type = SSA_Appointment_Type_Object::instance( $appointment_type_id );

		$params = shortcode_atts( array(
			'start_date_min' => '',
			'start_date_max' => '',
			'end_date_min' => '',
			'end_date_max' => '',
			'refresh' => '',

			'excluded_appointment_ids' => array(),
		), $params );

		// $is_global_cache_locked = get_transient( 'ssa/cache/lock_global' );
		// if ( $is_global_cache_locked ) {
		// 	sleep( 2 ); // just to alleviate a cache stampede, hopefully the cache is populated by then
		// }

		$transient_key = 'ssa_api_availability_'.$appointment_type_id.'_'.md5( json_encode( $params ) );
		// $response = get_transient( $transient_key ); // TODO: Implement cache
		if ( empty( $params['refresh'] ) && !empty( $response ) ) {
			$response['cached'] = true;
			return $response;
		}

		$this->plugin->google_calendar->maybe_refresh_calendars_for_appointment_type_id( $appointment_type_id );

		if ( empty( $params['start_date_min'] ) ) {
			$params['start_date_min'] = $params['start_date_max'];
		}
		if ( empty( $params['end_date_max'] ) ) {
			$params['end_date_max'] = $params['end_date_min'];
		}
		if ( empty( $params['start_date_min'] ) ) {
			$params['start_date_min'] = gmdate('Y-m-d H:i:s');
		}
		if ( empty( $params['start_date_max'] ) && empty( $params['end_date_max'] ) ) {
			$params['start_date_max'] = gmdate('Y-m-d', strtotime( $params['start_date_min'] ) + 3600*24*29 );
		}

		$params = array_filter( $params );

		$start_date = ssa_datetime( $params['start_date_min'] );
		$end_date = ssa_datetime( $params['start_date_max'] );

		$one_day_interval = new DateInterval( 'P1D'); 
		$yesterday = ssa_datetime( gmdate( 'Y-m-d' ) )->sub( $one_day_interval );
		if ( $start_date < $yesterday ) {
			$start_date = $yesterday;
		}

		if ( $end_date < $start_date ) {
			$end_date = $start_date;
		}

		$period = new Period(
			$start_date,
			$end_date
		);

		// $schedule = $appointment_type->get_schedule( $period );
		$time_start = microtime( true );
		$availability_query = new SSA_Availability_Query(
			$appointment_type,
			$period,
			$args
		);
		$is_query_cache_locked = get_transient( 'ssa/cache/lock_'.$availability_query->get_query_hash() );
		if ( $is_query_cache_locked ) {
			sleep( 4 ); // avoid the stampede on this specific query
		}
		$bookable_start_datetime_strings = $availability_query->get_bookable_appointment_start_datetime_strings();
		$booking_app_availability_cache = ssa_cache_get( 'booking_app_availability_edge_cache' );
		if ( empty( $booking_app_availability_cache ) ) {
			$booking_app_availability_cache = array();
		}
		if ( count( $booking_app_availability_cache ) > 100 ) {
			array_shift( $booking_app_availability_cache );
		}
		$booking_app_availability_cache[$availability_query->get_query_hash()] = $bookable_start_datetime_strings;
		ssa_cache_set( 'booking_app_availability_edge_cache', $booking_app_availability_cache  );
		// ssa_debug_log( microtime( true ) - $time_start, 10, 'get_availability_query', 'query_time' );
		// $min_booking_notice = $appointment_type->__get( 'min_booking_notice' );
		// $max_booking_notice = $appointment_type->__get( 'max_booking_notice' );

		// $now = new DateTimeImmutable();
		// if ( ! empty( $min_booking_notice ) ) {
		// 	$min_start_date = $now->add( new DateInterval('PT'. $min_booking_notice .'M' ) );
		// 	$min_start_date_string = $min_start_date->format( 'Y-m-d H:i:s' );
		// }

		// if ( ! empty( $max_booking_notice ) ) {
		// 	$max_start_date = $now->add( new DateInterval( 'PT'. $max_booking_notice .'M' ) );
		// 	$max_start_date_string = $max_start_date->format( 'Y-m-d H:i:s' );
		// }

		// $filtered = array_values( array_filter($bookable_start_datetime_strings, function($date) use ($min_start_date_string, $max_start_date_string) {
		// 	if ( ! empty( $min_start_date_string ) && $date['start_date'] < $min_start_date_string ) {
		// 		return false;
		// 	}
		// 	if ( ! empty( $max_start_date_string ) && $date['start_date'] > $max_start_date_string ) {
		// 		return false;
		// 	}

		// 	return true;
		// }) );

		$response = array(
			'response_code' => 200,
			'error' => '',
			'data' => $bookable_start_datetime_strings,
		);

		return $response;
	}

	public function get_all_appointment_types() {
		$transient = ssa_cache_get( 'appointment_types' );

		if( $transient ) {
			return $transient;
		}

		$items = $this->query();
		if ( $this->plugin->settings_installed->is_enabled( 'staff' ) ) {
			foreach ( $items as $key => $appointment_type ) {
				if ( empty( $appointment_type['staff_ids'] ) ) {
					$items[ $key ]['staff_ids'] = $this->plugin->staff_appointment_type_model->get_staff_ids_for_appointment_type_id( $appointment_type['id'] );
				}
			}
		}

		if ( $this->plugin->settings_installed->is_enabled( 'resources' ) ) {
			foreach ( $items as $key => $appointment_type ) {
				if ( empty( $appointment_type['resource_group_ids'] ) ) {
					$items[ $key ]['resource_group_ids'] = $this->plugin->resource_group_appointment_type_model->get_resource_group_ids( $appointment_type['id'] );
				}
			}
		}

		$transient = ssa_cache_set( 'appointment_types', $items, '', MONTH_IN_SECONDS );

		return $items;
	}

	public function invalidate_appointment_type_cache() {
		ssa_cache_delete('appointment_types');
		ssa_cache_delete('appointment_types_booking_app_new');
	}

	/**
	 * Given an Appointment Type ID, changes the status from "delete" to "publish", 
	 * if the appointment type was flagged as deleted.
	 * 
	 * @since 4.4.9
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function recover_appointment_type( WP_REST_Request $request ) {
		$params = $request->get_params();
		if ( empty( $params['id'] ) ) {
			return false;
		}
		$appointment_type_id = (int)sanitize_text_field( $params['id'] );
		$appointment_type = new SSA_Appointment_Type_Object( $appointment_type_id );

		// if appointment type is not actually deleted, there's nothing else to be done here
		if( $appointment_type->get_status() !== 'delete' ) {
			return new WP_REST_Response(array(
				'data' => $this->get($appointment_type_id),
				'error' => "",
				'response_code' => 200
			), 200);
		}

		$updated = $appointment_type->update_status( 'publish' );

		if( ! $updated ) {
			return new WP_REST_Response(array(
				'data' => $this->get($appointment_type_id),
				'error' => __( 'Appointment type not updated. Please try again', 'simply-schedule-appointments' ),
				'response_code' => 500
			), 500);
		}

		return new WP_REST_Response( array(
			'data' => $this->get($appointment_type_id),
			'error' => "",
			'response_code' => 200
		), 200 );
	}

	public function get_label_id_for_appointment_type_id( $appointment_type_id ) {
		static $map_appointment_type_ids_to_label_ids = array();
		if ( isset( $map_appointment_type_ids_to_label_ids[$appointment_type_id] ) ) {
			return $map_appointment_type_ids_to_label_ids[$appointment_type_id];
		}

		$appointment_type = new SSA_Appointment_Type_Object( $appointment_type_id );
		$label_id = $appointment_type->label_id;
		$map_appointment_type_ids_to_label_ids[$appointment_type_id] = $label_id;

		return $label_id;
	}
	
	/**
	 * Cleans up excluded calendars for staff_id 0 / main calendar
	 */
	public function cleanup_main_excluded_calendars() {
		
		if( ! $this->plugin->settings_installed->is_enabled( 'google_calendar' ) ) {
			return;
		}
		
		$google_calendar_settings = $this->plugin->google_calendar_settings->get();
		if( empty( $google_calendar_settings['access_token'] ) ) {
			return;
		}
		
		try {
			$client = $this->plugin->google_calendar_client->service_init( 0 );
			$calendar_list = $client->get_calendar_list();
			
			// should never be empty unless something failed
			if( empty( $calendar_list ) ) {
				return;
			}
			
			$calendar_list_ids = array_column( $calendar_list, 'id' );
			$invalid_calendar_ids = array();
			
			$all_appointment_types = $this->plugin->appointment_type_model->get_all_appointment_types();
			foreach( $all_appointment_types as $appointment_type ) {
				if( empty( $appointment_type['google_calendars_availability'] ) ) {
					continue;
				}
				
				$excluded_calendars = $appointment_type['google_calendars_availability'];
				// filter, if not in array $calendar_list remove it
				$valid_excluded_calendars = array_filter( $excluded_calendars, function (string $calendar_id) use ( $calendar_list_ids, &$invalid_calendar_ids ) { 
					if( in_array( $calendar_id, $calendar_list_ids ) ) {
						return true;
					}
					$invalid_calendar_ids[] = $calendar_id;
					return false;
				} );
				
				if( count( $valid_excluded_calendars ) === count( $excluded_calendars ) ) {
					continue;
				}
				
				$appointment_type['google_calendars_availability'] = $valid_excluded_calendars;
				
				$this->plugin->appointment_type_model->update( $appointment_type['id'], $appointment_type );
			}
			
			$invalid_calendar_ids = array_unique( $invalid_calendar_ids );
			foreach( $invalid_calendar_ids as $invalid_calendar_id ) {
				$deleted_count = $this->plugin->availability_external_model->bulk_delete( array(
					'calendar_id_hash' => ssa_int_hash( $invalid_calendar_id ),
					'service' => 'google',
				) );
			}
			
		} catch (\Throwable $th) {
			// do nothing
			// this cleanup is not critical
		}
	}
	
	/**
	 * Prevents and edge case where availability would be incorrectly limited by the capacity setting when teams or resources are enabled
	 * 
	 */
	public function recheck_capacity_settings ( $data, $data_before, $appointment_type_id ) {
		// if has_max_capacity is truthy → capacity stays at the set value
		if( ! isset( $data['has_max_capacity'] ) || ! empty( $data['has_max_capacity'] ) ) {
			return $data;
		}
		
		$teams_feature_enabled = $this->plugin->settings_installed->is_enabled( 'staff' );
		$resources_feature_enabled = $this->plugin->settings_installed->is_enabled( 'resources' );
		
		// if no team & no resources → capacity stays at the set value
		if( ! $teams_feature_enabled && ! $resources_feature_enabled ){
			return $data;
		}
		
		if( ( ! empty( $data['staff']['required'] ) && ! empty( $data['staff_ids'] ) ) || ! empty( $data['resource_group_ids'] ) ){
			// teams or resources enabled on type & has_max_capacity is 0 → set capacity to CAPACITY_MAX
			$data['capacity'] = SSA_Constants::CAPACITY_MAX;
		}
		// else - do nothing
		// if no team & no resources (on the type itself) → capacity stays at the set value
		
		return $data;
	}
}
