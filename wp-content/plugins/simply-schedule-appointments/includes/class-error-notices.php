<?php
/**
 * Simply Schedule Appointments Error Notices.
 *
 * @since   6.3.1
 * @package Simply_Schedule_Appointments
 */

/**
 * Simply Schedule Appointments Error Notices.
 *
 * @since 6.3.1
 */
class SSA_Error_Notices {
	/**
	 * Parent plugin class.
	 *
	 * @since 6.3.1
	 *
	 * @var   Simply_Schedule_Appointments
	 */
	protected $plugin = null;
	protected $scan_interval_transient_key = 'ssa/notices/error/scan_interval';


	/**
	 * Constructor.
	 *
	 * @since  6.3.1
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
	 * @since  6.3.1
	 */
	public function hooks() {
	}

	/**
	 * General function to include custom checks for errors
	 *
	 * @return void
	 */
	public function scan_for_errors(){
		$this->check_for_aiowps_6g_block_query();
		$this->check_perfmatters_lazy_loading_settings();
		$this->check_gcal_missing_refresh_token();

	}
	
	/**
	 * Only call scan_for_errors every 12 hours
	 *
	 * @return void
	 */
	public function maybe_scan_for_errors() {
		$scan_interval = get_transient( $this->scan_interval_transient_key );

		if( empty( $scan_interval ) ) {
			$this->scan_for_errors();
			$expiration = 12 * HOUR_IN_SECONDS;
			set_transient( $this->scan_interval_transient_key, true, $expiration );
		}
	}

	/**
	 * Accepts the stored errors in db, loop over them and foreach run its callback function
	 * The callback must be in this class
	 * Check the callback field in the schema
	 *
	 * @param array $errors
	 * @return array
	 */
	public function run_callbacks( $errors = array() ) {

		$schema = $this->get_schema();
		
		foreach ( $errors as $key => $value ) {

			if( ! empty( $schema[ $key ]['callback'] ) && ! empty( $schema[ $key ]['id'] ) ) {

				$callback = $schema[ $key ]['callback'];
				$id = $schema[ $key ]['id'];
				call_user_func( array( $this, $callback), $id );
			}
		}
		// Return a fresh array of error notices since some may have been deleted after running the callbacks
		return $this->fetch_error_notices_ids();

	}

	/**
	 * Check for Perfmatters plugin if installed
	 * Check for lazyload settings if enabled and promt the users to exclude 'ssa_booking_iframe'
	 *
	 * @return void
	 */
	public function check_perfmatters_lazy_loading_settings(){
		// check if perfmatters deactivated
		if ( ! is_plugin_active( 'perfmatters/perfmatters.php' ) ) {
			$this->delete_error_notice('perfmatters_plugin_lazy_loading_enabled');
			return;
		}
		
		global $perfmatters_settings_page;
		if ( empty( $perfmatters_settings_page ) ) {
			$this->delete_error_notice('perfmatters_plugin_lazy_loading_enabled');
			return;
		}

		$perfmatters_options = get_option( 'perfmatters_options', array() );

		if ( empty( $perfmatters_options ) ) {
			return;
		}

		// Means the lazy loading option is disabled
		if ( empty( $perfmatters_options['lazyload']['lazy_loading'] ) ) {
			$this->delete_error_notice('perfmatters_plugin_lazy_loading_enabled');
			return;
		}

		// ssa_booking_iframe has been added successfully to lazy_loading_exclusions
		if( is_array( $perfmatters_options['lazyload']['lazy_loading_exclusions'] ) && in_array( 'ssa_booking_iframe', $perfmatters_options['lazyload']['lazy_loading_exclusions'] ) ) {
			$this->delete_error_notice('perfmatters_plugin_lazy_loading_enabled');
			return;
		}

		$this->add_error_notice('perfmatters_plugin_lazy_loading_enabled');

	}

	/**
	 * Check if all-in-one-wp-security-and-firewall plugin is installed
	 * If so, check in its settings -> 6G blacklist firewall rules -> Block query strings if enabled
	 * Since this would prevent the reschedule action from working as expected
	 * 
	 * @since  6.4.4
	 *
	 * @return void
	 */
	public function check_for_aiowps_6g_block_query() {
		global $aiowps_firewall_config;
		if ( empty( $aiowps_firewall_config ) || ! is_object( $aiowps_firewall_config ) ) {
			$this->delete_error_notice('all_in_one_security_firewall_rules_block_query_strings');
			return;
		}

		if ( ! method_exists( $aiowps_firewall_config, 'get_value' ) ) {
			return;
		}
		$blocked_query = (bool) $aiowps_firewall_config->get_value('aiowps_6g_block_query');

		if( empty ( $blocked_query ) ) {
			$this->delete_error_notice('all_in_one_security_firewall_rules_block_query_strings');
			return;
		}

		$this->add_error_notice('all_in_one_security_firewall_rules_block_query_strings');

	}
    
	/**
	 * Prepare and return the error notices based on what we have stored in db
	 *
	 * @since  6.3.1
	 * 
	 * @return array
	 */
	public function get_error_notices(){

		$this->maybe_scan_for_errors();

		$stored_error_ids = $this->fetch_error_notices_ids();

		if( empty( $stored_error_ids ) ) {
			return array();
		}

		$stored_error_ids = $this->run_callbacks( $stored_error_ids );

		$schema = $this->get_schema();
		$output = array();

		foreach ( $stored_error_ids as $key => $value ) {
			// Assert that the stored id has a match in the schema
			if( ! isset( $schema[ $key ] ) ) {
				$this->delete_error_notice( $key );
				continue;
			}
			array_push( $output, $schema[ $key ] );
		}
		return $output;
	}

	/**
	 * Get error notices from db
	 * 
	 * @since  6.3.1
	 *
	 * @return array
	 */
	public function fetch_error_notices_ids(){
		return get_option( 'ssa_error_notices', array() );
	}

	/**
	 * Add a single error to ssa_error_notices array
	 *
	 * @since  6.3.1
	 * 
	 * @param string $error_id
	 * @return int|void
	 */
	public function add_error_notice( $error_id = '' ) {

		if( empty( $error_id ) ) {
				return;
		}

		// Assert that we have the error defined in the schema
		$schema = $this->get_schema();

		if( ! isset( $schema[ $error_id ] ) ) {
			return;
		}

		$error_notices_ids = $this->fetch_error_notices_ids();

		if ( ! isset( $error_notices_ids[ $error_id ] ) ) {

			$error_notices_ids[ $error_id ] = true;
			return update_option( 'ssa_error_notices', $error_notices_ids );
		}

	}

	/**
	 * delete a single error from ssa_error_notices array
	 *
	 * @since  6.3.1
	 * 
	 * @param string $error_id
	 * @return int|void
	 */
	public function delete_error_notice( $error_id = '' ){

		if( empty( $error_id ) ) {
				return;
		}

		$error_notices_ids = $this->fetch_error_notices_ids();

		if ( isset( $error_notices_ids[ $error_id ] ) ) {
			unset( $error_notices_ids[ $error_id ]);
			return update_option( 'ssa_error_notices', $error_notices_ids );
		}

	}

	/**
	 * Mega array of ssa warnings and errors
	 * 
	 * id: name must be the same as the key name, used for adding and deleting
	 * type: error | warning
	 * priority: 1 high priority
	 * message: the message to display in the banner
	 * link: external link with https:// | local link /ssa/settings/google-calendar
	 * link_message: the button/link text
	 *
	 * @var array
	 */
	public function get_schema(){

		return array(
			'google_calendar_sync_appointment_to_calendar' => array(
				'id'			=> 'google_calendar_sync_appointment_to_calendar',
				'type'			=> 'error',
				'priority' 		=> 1,
				'message'		=> __( 'SSA failed to sync an appointment to Google Calendar. Please disconnect and reconnect Google Calendar in the settings and contact support if this error message persists.', 'simply-schedule-appointments' ),
				'link'			=> '/ssa/settings/google-calendar',
				'link_message' 	=> __( 'Go to settings', 'simply-schedule-appointments' ),
				'callback'	=> 'check_if_gcal_enabled'
			),
			'missing_gcal_refresh_token_globally'	=> array(
				'id'			=> 'missing_gcal_refresh_token_globally',
				'type'			=> 'error',
				'priority' 		=> 1,
				'message'		=> __( 'Lost connection to Google Calendar. Please disconnect and reconnect Google Calendar in the settings and contact support if this error message persists.', 'simply-schedule-appointments' ),
				'link'			=>  '/ssa/settings/google-calendar',
				'link_message' 	=> __( 'Go to settings', 'simply-schedule-appointments' ),
				'callback'	=> 'confirm_gcal_refresh_token_not_missing_globally'
			),
			'missing_gcal_refresh_token_for_staff'	=> array(
				'id'			=> 'missing_gcal_refresh_token_for_staff',
				'type'			=> 'error',
				'priority' 		=> 1,
				'message'		=> __( 'One or more of your team members needs to reauthorize their Google Calendar. Please disconnect and reconnect their calendar in the team settings and contact support if this error message persists.', 'simply-schedule-appointments' ),
				'link'			=>  '/ssa/settings/staff/all',
				'link_message' 	=> __( 'Go to Team settings', 'simply-schedule-appointments' ),
				'callback'	=> 'confirm_gcal_refresh_token_not_missing_for_team'
			),
			'google_calendar_get_calendars_by_staff' => array(
				'id'			=> 'google_calendar_get_calendars_by_staff',
				'type'			=> 'error',
				'priority' 		=> 1,
				'message'		=> __( 'SSA failed to sync Calendars by staff. Please disconnect and reconnect Google Calendar in the settings and contact support if this error message persists.', 'simply-schedule-appointments' ),
				'link'			=> '/ssa/settings/google-calendar',
				'link_message' 	=> __( 'Go to settings', 'simply-schedule-appointments' ),
				'callback'	=> 'check_if_gcal_enabled',
			),
			'google_calendar_authentication' => array(
				'id'			=> 'google_calendar_authentication',
				'type'			=> 'error',
				'priority' 		=> 1,
				'message'		=> __( 'SSA was unable to connect to Google Calendar. Please disconnect and reconnect Google Calendar in the settings and contact support if this error message persists.', 'simply-schedule-appointments' ),
				'link'			=> '/ssa/settings/google-calendar',
				'link_message' 	=> __( 'Go to settings', 'simply-schedule-appointments' ),
				'callback'	=> 'check_if_gcal_enabled'
			),
			'all_in_one_security_firewall_rules_block_query_strings' => array(
				'id'			=> 'all_in_one_security_firewall_rules_block_query_strings',
				'type'			=> 'warning',
				'priority' 		=> 1,
				'message'		=> __( 'Conflict with "All-In-One Security (AIOS)" plugin: AIOS "Block Query Strings" setting is currently enabled. This will prevent Simply Schedule Appointments from working as expected, including users being unable to reschedule their appointments. Please disable this feature in your settings.', 'simply-schedule-appointments' ),
				'link'			=> esc_url( admin_url() ) . 'admin.php?page=aiowpsec_firewall&tab=tab3',
				'link_message' 	=> __( 'Go to AIOS settings', 'simply-schedule-appointments' ),
				'callback'	=> 'check_for_aiowps_6g_block_query'

			),
			'perfmatters_plugin_lazy_loading_enabled' => array(
				'id'			=> 'perfmatters_plugin_lazy_loading_enabled',
				'type'			=> 'warning',
				'priority' 		=> 1,
				'message'		=> __( 'Conflict with "Perfmatters" plugin: "Lazy Loading" setting is currently enabled. This will prevent Simply Schedule Appointments from working as expected. Please add "ssa_booking_iframe" into the "Exclude from Lazy Loading" field under the Lazy Loading tab.', 'simply-schedule-appointments' ),
				'link'			=> esc_url( admin_url() ) . 'options-general.php?page=perfmatters#lazyload',
				'link_message' 	=> __( 'Go to Perfmatters Lazyload settings', 'simply-schedule-appointments' ),
				'callback'	=> 'check_perfmatters_lazy_loading_settings'
			),
			// commented out because it is not being used
			// 'twilio_low_balance'	=> array(
			// 	'id'			=> 'twilio_low_balance',
			// 	'type'			=> 'warning',
			// 	'priority' 		=> 10,
			// 	'message'		=> __( 'This is a warning message for low Twilio balance This is a warning message for low Twilio balance This is a warning message for low Twilio balance', 'simply-schedule-appointments' ),
			// 	'link'			=> 'https://simplyscheduleappointments.com/guides/',
			// 	'link_message' 	=> 'Go to simplyscheduleappointments.com',
			// 	'callback'	=> 'check_for_twilio_if_enabled'
			// ),
			'quick_connect_gcal_auth_mode_changed'	=> array(
				'id'			=> 'quick_connect_gcal_auth_mode_changed',
				'type'			=> 'warning',
				'priority' 		=> 10,
				'message'		=> __( 'An admin changed the Google calendar authorization process. Kindly authorize SSA again', 'simply-schedule-appointments' ),
				'link'			=> $this->plugin->wp_admin->url( '/ssa/settings/google-calendar' ),
				'link_message' 	=> 'Go to your calendar settings'

			),
			'quick_connect_gcal_activate_license'	=> array(
				'id'			=> 'quick_connect_gcal_activate_license',
				'type'			=> 'warning',
				'priority' 		=> 10,
				'message'		=> __( 'SSA Quick Connect service was not able to match your license key to your current domain. Kindly activate the key on this domain', 'simply-schedule-appointments' ),
				'link'			=> $this->plugin->wp_admin->url( '/ssa/settings/all' ),
				'link_message' 	=> 'Go to SSA settings',
				'callback'	=> 'check_if_license_is_valid'

			),
			'quick_connect_gcal_renew_license'	=> array(
				'id'			=> 'quick_connect_gcal_renew_license',
				'type'			=> 'warning',
				'priority' 		=> 10,
				'message'		=> __( 'SSA Quick Connect service was not able to validate your license key. Kindly renew your license or contact support', 'simply-schedule-appointments' ),
				'link'			=> $this->plugin->wp_admin->url( '/ssa/settings/all' ),
				'link_message' 	=> 'Go to SSA settings',
				'callback'	=> 'check_if_license_is_valid'

			),
			'quick_connect_gcal_re_authorize'	=> array(
				'id'			=> 'quick_connect_gcal_re_authorize',
				'type'			=> 'warning',
				'priority' 		=> 10,
				'message'		=> __( 'Looks like SSA Quick Connect\'s access to your Google Calendar was revoked. Please connect your calendar again', 'simply-schedule-appointments' ),
				'link'			=> $this->plugin->wp_admin->url( '/ssa/settings/google-calendar' ),
				'link_message' 	=> 'Go to your calendar settings'
			),
			'quick_connect_gcal_backoff'	=> array(
				'id'			=> 'quick_connect_gcal_backoff',
				'type'			=> 'warning',
				'priority' 		=> 10,
				'message'		=> __( 'SSA Quick Connect service is not responding, your Google Calendar may be out of sync', 'simply-schedule-appointments' ),
				'link'			=> $this->plugin->wp_admin->url( '/ssa/support/help' ),
				'link_message' 	=> 'Contact our support',
				'callback'	=> 'check_if_quick_connect_gcal_backoff_is_reset'
			),
			'stripe_invalid_webhook_secret'	=> array(
				'id'			=> 'stripe_invalid_webhook_secret',
				'type'			=> 'warning',
				'priority' 		=> 10,
				'message'		=> __( 'Looks like your webhook secret value is setup incorrectly. Please make sure you copy the correct secret from your Stripe dashboard', 'simply-schedule-appointments' ),
				'link'			=> 'https://dashboard.stripe.com/account/webhooks/',
				'link_message' 	=> 'Go to Stripe Webhook Settings',
				'callback'	=> 'check_for_stripe_if_enabled'
			),
			'stripe_test_mode_active'	=> array(
				'id'			=> 'stripe_test_mode_active',
				'type'			=> 'warning',
				'priority' 		=> 10,
				'message'		=> __( 'Stripe is in test mode! Remember to switch to live mode when ready to receive payments.' ),
				'link'			=>  '/ssa/settings/payments/stripe',
				'link_message' 	=> 'Go to Stripe Settings',
				'callback'	=> 'confirm_stripe_live_mode_active'
			)
		);
	}

	public function confirm_stripe_live_mode_active( $id = '' ) {
		if ( empty( $id ) ) {
			return;
		}

		if( ! class_exists( 'SSA_Stripe' ) || ! $this->plugin->settings_installed->is_enabled( 'stripe' ) ) {
			$this->delete_error_notice( $id );
		}
		
		$stripe_settings = $this->plugin->stripe_settings->get();
		
	}
	
	public function check_for_stripe_if_enabled( $id = '' ) {

		if ( empty( $id ) ) {
			return;
		}

		if( ! class_exists( 'SSA_Stripe' ) || ! $this->plugin->settings_installed->is_enabled( 'stripe' ) ) {
			$this->delete_error_notice( $id );
		}
	}
	
	// public function check_for_twilio_if_enabled( $id = '' ) {
	// 	if ( empty( $id ) ) {
	// 		return;
	// 	}
	// 	// TODO
	// }
	
	public function check_if_license_is_valid( $id = '' ) {
		if ( empty( $id ) ) {
			return;
		}
		$license_settings = $this->plugin->license->check();
		if ( ! empty( $license_settings['license_status'] ) && 'valid' === $license_settings['license_status'] ) {
			$this->delete_error_notice( $id );
		}
	}
	
	public function check_if_quick_connect_gcal_backoff_is_reset( $id = '' ) {
		if ( empty( $id ) ) {
			return;
		}
		$google_calendar_settings = ssa()->google_calendar_settings->get();
		if( $google_calendar_settings["quick_connect_backoff"] === 0 ){
			$this->delete_error_notice( $id );
		}
	}


	/**
	 * A callback to assert that Google Calendar is enabled
	 * If it's not we delete the error passed as parameter since the error is no longer valid
	 *
	 * @param string $id
	 * @return void
	 */
	public function check_if_gcal_enabled( $id = '' ) {
		if ( empty( $id ) ) {
			return;
		}

		if( ! class_exists( 'SSA_Google_Calendar' ) || ! $this->plugin->settings_installed->is_enabled( 'google_calendar' ) ) {
			$this->delete_error_notice( $id );
		}
	}

	public function confirm_gcal_refresh_token_not_missing_globally( $id = '' ) {
		if ( empty( $id ) ) return;

		if( ! class_exists( 'SSA_Google_Calendar' ) || ! $this->plugin->settings_installed->is_enabled( 'google_calendar' ) ) {
			$this->delete_error_notice( $id );
		}
		
		if( $this->plugin->developer_settings->is_in_dev_quick_connect_gcal_mode() || $this->plugin->google_calendar_settings->is_in_quick_connect_gcal_mode() ){
			return $this->delete_error_notice( $id );
		}
		
		$should_reconnect_main_calendar = $this->plugin->google_calendar_settings->is_main_calendar_refresh_token_lost();

		if( empty( $should_reconnect_main_calendar ) ) {
			$this->delete_error_notice( $id );
		}
		
	}

	public function confirm_gcal_refresh_token_not_missing_for_team( $id = '' ) {
		if ( empty( $id ) ) return;

		if( ! class_exists( 'SSA_Google_Calendar' ) || ! $this->plugin->settings_installed->is_enabled( 'google_calendar' ) ) {
			$this->delete_error_notice( $id );
		}

		if( $this->plugin->developer_settings->is_in_dev_quick_connect_gcal_mode() || $this->plugin->google_calendar_settings->is_in_quick_connect_gcal_mode() ){
			return $this->delete_error_notice( $id );
		}
		
		if( ! class_exists( 'SSA_Staff' ) || ! ssa()->settings_installed->is_enabled( 'staff' ) ) {
			$this->delete_error_notice( $id );
		}

		$lost_staff_calendar_refresh_tokens = $this->plugin->staff_model->count_lost_staff_gcal_refresh_tokens();

		if( empty( $lost_staff_calendar_refresh_tokens ) ) {
			$this->delete_error_notice( $id );
		}

	}

	public function check_gcal_missing_refresh_token() {

		if( ! class_exists( 'SSA_Google_Calendar' ) || ! $this->plugin->settings_installed->is_enabled( 'google_calendar' ) ) {
			return;
		}
		
		if( $this->plugin->developer_settings->is_in_dev_quick_connect_gcal_mode() || $this->plugin->google_calendar_settings->is_in_quick_connect_gcal_mode() ){
			return;
		}

		$should_reconnect_main_calendar = $this->plugin->google_calendar_settings->is_main_calendar_refresh_token_lost();

		if( ! empty( $should_reconnect_main_calendar ) ) {
			$this->add_error_notice('missing_gcal_refresh_token_globally');
		}

		// Check staff
		if( ! class_exists( 'SSA_Staff' ) || ! ssa()->settings_installed->is_enabled( 'staff' ) ) {
			return;
		}

		$lost_staff_calendar_refresh_tokens = $this->plugin->staff_model->count_lost_staff_gcal_refresh_tokens();

		if( ! empty( $lost_staff_calendar_refresh_tokens ) ) {
			$this->add_error_notice('missing_gcal_refresh_token_for_staff');
		}

	}

}
