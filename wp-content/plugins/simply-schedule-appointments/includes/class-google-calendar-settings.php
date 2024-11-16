<?php
/**
 * Simply Schedule Appointments Google Calendar Settings.
 *
 * @since   0.6.0
 * @package Simply_Schedule_Appointments
 */

/**
 * Simply Schedule Appointments Google Calendar Settings.
 *
 * @since 0.6.0
 */
class SSA_Google_Calendar_Settings extends SSA_Settings_Schema {
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
		parent::__construct();
		$this->plugin = $plugin;
		$this->hooks();
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  0.0.3
	 */
	public function hooks() {
		
	}

	protected $slug = 'google_calendar';

	public function get_schema() {
		if ( !empty( $this->schema ) ) {
			return $this->schema;
		}

		$this->schema = array(
			// YYYY-MM-DD
			'version' => '2023-12-07',
			'fields' => array(
				'enabled' => array(
					'name' => 'enabled',
					'default_value' => false,
				),

				// false as default - currently not used - controlled by beta feature switch
				// boolean, whether quick_connect_gcal_mode mode is currently active
				'quick_connect_gcal_mode' => array(
					'name' => 'quick_connect_gcal_mode',
					'default_value' => false,
				),

				// minimum number of seconds to wait after quick_connect_backoff_timestamp
				'quick_connect_backoff' => array(
					'name' => 'quick_connect_backoff',
					'default_value' => 0,
				),
				
				// timestamp where backoff started
				'quick_connect_backoff_timestamp' => array(
					'name' => 'quick_connect_backoff_timestamp',
					'default_value' => 0,
				),
				
				'quick_connect_home_url' => array(
					'name' => 'quick_connect_home_url',
					'default_value' => get_home_url(),
				),

				'client_id' => array(
					'name' => 'client_id',
					'default_value' => '',
					'required_capability' => 'ssa_manage_site_settings',
				),

				'client_secret' => array(
					'name' => 'client_secret',
					'default_value' => '',
					'required_capability' => 'ssa_manage_site_settings',
				),

				'access_token' => array(
					'name' => 'access_token',
					'default_value' => '',
					'required_capability' => 'ssa_manage_appointments',
					'writeonly_secret' => true
				),

				'web_meetings' => array(
					'name' => 'web_meetings',
					'default_value' => false,
					'required_capability' => 'ssa_manage_site_settings',
				),

				'delete_canceled_events' => array(
					'name' => 'delete_canceled_events',
					'default_value' => false,
					'required_capability' => 'ssa_manage_site_settings',
				),
				
				'refresh_interval' => array(
					'name' => 'refresh_interval',
					'default_value' => 5,
					'required_capability' => 'ssa_manage_site_settings',
				),

				'query_limit' => array(
					'name' => 'query_limit',
					'default_value' => 500,
					'required_capability' => 'ssa_manage_site_settings',
				),
			),
		);

		return $this->schema;
	}

	public function get_computed_schema() {
		if ( !empty( $this->computed_schema ) ) {
			return $this->computed_schema;
		}

		$this->computed_schema = array(
			'version' => '2022-02-24',
			'fields' => array(
				'client_id_filtered' => array(
					'name' => 'client_id_filtered',
					'get_function' => array( $this->plugin->google_calendar, 'get_filtered_client_id' ),
					'get_input_path' => 'client_id',
					'required_capability' => 'ssa_manage_site_settings',

				),
				'client_secret_filtered' => array(
					'name' => 'client_secret_filtered',
					'get_function' => array( $this->plugin->google_calendar, 'get_filtered_client_secret' ),
					'get_input_path' => 'client_secret',
					'required_capability' => 'ssa_manage_site_settings',

				),
			),
		);

		return $this->computed_schema;
	}

	/**
	 * Check the setting to automatically delete Google Calendar events when an appointment is rescheduled or canceled.
	 * 
	 * @since 4.7.4
	 *
	 * @return boolean
	 */
	public function should_delete_events() {
		$settings = $this->get();
		if ( empty( $settings['delete_canceled_events'] ) ) {
			return false;
		}

		return $settings['delete_canceled_events'];
	}
	
	public function is_main_calendar_refresh_token_lost(){
		$settings = $this->get();
		if ( empty( $settings['enabled'] ) ) {
			return false;
		}
		
		if ( ! empty($settings['access_token'] ) && empty($settings['access_token']['refresh_token'] ) ) {
			return true;
		}
		return false;
	}
}
