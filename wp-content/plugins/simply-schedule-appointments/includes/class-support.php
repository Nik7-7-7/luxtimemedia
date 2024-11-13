<?php
/**
 * Simply Schedule Appointments Support.
 *
 * @since   2.1.6
 * @package Simply_Schedule_Appointments
 */

/**
 * Simply Schedule Appointments Support.
 *
 * @since 2.1.6
 */
class SSA_Support {
	/**
	 * Parent plugin class.
	 *
	 * @since 2.1.6
	 *
	 * @var   Simply_Schedule_Appointments
	 */
	protected $plugin = null;
	protected $secret_urls = array(
		array(
			'callback' => 'fix_appointment_durations',
			'title' => 'Fix appointment durations',
			'details' => 'Pass 1 as a parameter to update the end date of all appointments to match the duration of the appointment type.',
			'param' => 'ssa-fix-appointment-durations',
			'param_default_value' => '1'
		),
		array(
			'callback' => 'fix_appointment_group_ids',
			'title' => 'Fix appointment group ids',
			'details' => 'Pass 1 as a parameter to update the group id of all appointments to match the group id of the first appointment in the group.',
			'param' => 'ssa-fix-appointment-group-ids',
			'param_default_value' => '1'
		),
		array(
			'callback' => 'fix_db_datetime_schema',
			'title' => 'Fix database datetime schema',
			'details' => 'Pass 1 as a parameter to update the datetime schema of all appointments and appointment types.',
			'param' => 'ssa-fix-db-datetime-schema',
			'param_default_value' => '1'
		),
		array(
			'callback' => 'fix_db_availability_schema',
			'title' => 'Fix database availability schema',
			'details' => 'Pass 1 as a parameter to drop and recreate the availability table.',
			'param' => 'ssa-fix-db-availability-schema',
			'param_default_value' => '1'
		),
		array(
			'callback' => 'fix_appointment_types',
			'title' => 'Fix appointment types',
			'details' => 'Pass 1 as a parameter to update the custom customer information and customer information fields of all appointment types',
			'param' => 'ssa-fix-appointment-types',
			'param_default_value' => '1'
		),
		array(
			'callback' => 'fix_missing_appointment_types',
			'title' => 'Fix missing appointment types',
			'details' => 'Pass 1 as a parameter to update appointments with appointment types replacing the deleted ones.',
			'param' => 'ssa-fix-missing-appointment-types',
			'param_default_value' => '1'
		),
		array(
			'callback' => 'purge_abandoned_appointments',
			'title' => 'Purge abandoned appointments',
			'details' => 'Pass 1 as a parameter to delete all appointments with a status of "abandoned" from the database.',
			'param' => 'ssa-purge-abandoned-appointments',
			'param_default_value' => '1'
		),
		array(
			'callback' => 'ssa_factory_reset',
			'title' => 'Factory reset SSA',
			'details' => 'Pass 1 as a parameter to delete all SSA settings from the database and truncate all SSA database tables.',
			'param' => 'ssa-factory-reset',
			'param_default_value' => '1'
		),
		array(
			'callback' => 'rebuild_db',
			'title' => 'Rebuild SSA database',
			'details' => 'Pass 1 as a parameter to recreate all SSA database tables.',
			'param' => 'ssa-rebuild-db',
			'param_default_value' => '1'
		),
		array(
			'callback' => 'clear_google_cache',
			'title' => 'Clear Google cache',
			'details' => 'Pass 1 as a parameter to clear the Google Calendar cache.',
			'param' => 'ssa-clear-google-cache',
			'param_default_value' => '1'
		),
		array(
			'callback' => 'set_google_query_limit',
			'title' => 'Set Google Calendar query limit',
			'details' => 'Pass a new limit to use for Google Calendar events. SSA defaults to 500.',
			'param' => 'ssa-set-google-query-limit',
			'param_default_value' => '500'
		),
		array(
			'callback' => 'clear_all_cache',
			'title' => 'Clear all cache',
			'details' => 'Pass 1 as a parameter to clear all cache.',
			'param' => 'ssa-clear-all-cache',
			'param_default_value' => '1'
		),
		array(
			'callback' => 'populate_cache',
			'title' => 'Populate cache',
			'details' => 'Pass 1 as a parameter to populate the cache.',
			'param' => 'ssa-populate-cache',
			'param_default_value' => '1'
		),
		array(
			'callback' => 'restore_plugin_backup',
			'title' => 'Restore plugin backup',
			'details' => 'Pass 1 as a parameter to restore the last backup of the plugin settings.',
			'param' => 'ssa-restore-backup',
			'param_default_value' => '1'
		),
		array(
			'callback' => 'bulk_send_notifications',
			'title' => 'Bulk send notifications',
			'details' => 'Pass 1 as a parameter to send notifications for all future booked appointments.',
			'param' => 'ssa-resend-booked-notifications',
			'param_default_value' => '1'
		),
		array(
			'callback' => 'set_display_capacity_available',
			'title' => 'Set display capacity available',
			'details' => 'Pass 1 as a parameter to set the display capacity available setting to true. Pass 0 as a parameter to set the display capacity available setting to false.',
			'param' => 'ssa-set-display-capacity-available',
			'param_default_value' => '1'
		),
		array(
			'callback' => 'ssa_set_license',
			'title' => 'Set license',
			'details' => 'Pass the license key as a parameter to activate it.',
			'param' => 'ssa-set-license',
			'param_default_value' => '1'
		),
		array(
			'callback' => 'ssa_set_debug_level',
			'title' => 'Set debug level',
			'details' => 'Pass the debug level as a parameter to set the ssa_debug_level option.',
			'param' => 'ssa-set-debug-level',
			'param_default_value' => '10'
		),
		array(
			'callback' => 'ssa_cleanup_excluded_calendars',
			'title' => 'Clean up excluded calendars for main calendar and staff profiles',
			'details' => 'Will remove any excluded calendars that are not accessible by SSA. Both from the main calendar and staff profiles.',
			'param' => 'ssa-cleanup-excluded-calendars',
			'param_default_value' => '1'
		),
		array(
			'callback' => 'ssa_remove_all_appointments',
			'title' => 'Removes all appointments and their associated data',
			'details' => 'Will totally remove all appointments and their associated data from the database. This cannot be undone!',
			'param' => 'ssa-remove-all-appointments',
			'param_default_value' => '1'
		),
	);
	/**
	 * Constructor.
	 *
	 * @since  2.1.6
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
	 * @since  2.1.6
	 */
	public function hooks() {
		foreach( $this->secret_urls as $secret_url ) {
			if ( ! method_exists( $this, $secret_url['callback'] ) ) {
				// if method does not exist on this class
				// throw an exception here to catch the problems in the CI/CD pipeline
				throw new Exception( 'Method does not exist on this class: ' . $secret_url['callback'] );
			}
			add_action( 'admin_init', array( $this, $secret_url['callback'] ) );
		}
		// maybe render support page
		add_action( 'admin_init', array( $this, 'render_secret_support_page' ), 0 );
	}
	
	/**
	 * Render support page.
	 * Called from wp-admin/admin.php
	 */
	public function render_secret_support_page() {
		
		if ( empty( $_GET['ssa-support-admin'] ) || $_GET['ssa-support-admin'] !== '1') {
			return;
		}
		
		if( !current_user_can( 'ssa_manage_site_settings' ) ) {
			return;
		}
		
		header( 'Content-Type: text/html' );
		?>
		<main>
			<style>
				* {
					font-family: Arial, sans-serif;
					margin: 0;
					padding: 0;
				}
				main {
					padding: 40px;
				}
				main > div > * {
					margin-bottom: 20px;
				}
				h1 {
					font-size: 2em;
				}
				h2 {
					font-size: 1.5em;
				}
				p {
					font-size: 1em;
				}
				.danger {
					color: red;
				}
				.actions-container {
					display: flex;
					flex-direction: column;
					gap: 50px;
				}
				.actions-container section {
					border-top: 1px solid #ccc;
					padding-top: 20px;
					display: flex;
					flex-direction: column;
					gap: 20px;
				}
				
				section span {
					display: flex;
					gap: 80px;
				}
				.link {
					color: blue;
					margin-right: auto;
				}
				input {
					height: 30px;
					padding: 10px;
				}
			</style>
			<div>
				<h1 class="danger"><?php echo home_url()?></h1>
				<h2>You are on the SSA secret support page</h2>
				<p>This is available to site administrators only, and should only be used for debugging and support purposes.</p>
			</div>
			<br>
			<h2>Actions</h2>
			<div class="actions-container">
				<?php
					foreach( $this->secret_urls as $secret_url ) {
						$nonce = wp_create_nonce( $secret_url['param'] );
						$link = admin_url() . '?ssa_nonce=' . $nonce . '&' . $secret_url['param'] . '=' . $secret_url['param_default_value'];
						echo $this->generateSupportUrl($secret_url, $link);
					}
				?>
			</div>
		</main>
		<?php
		// exit to avoid rendering any other content
		exit;
	}
	
	
	/**
	 * Generate a support url
	 */
	public function generateSupportUrl( $secret_url, $link ) {
		return '
		<section>
			<h3>' . $secret_url['title'] . '</h3>' . 
			( empty($secret_url['details']) ? '' : '<p>' . $secret_url['details'] .'</p>' ) . 
			'<a class="link" href="' . $link . '" id="' . $secret_url['param'] . '-link">' . $link . '</a>
		</section>
		';
	}
	
	/**
	 * Remove all appointments and their associated data. Keeping SSA settings and appointment types intact.
	 * 
	 * @return void
	 */
	public function ssa_remove_all_appointments () {
		if ( empty( $_GET['ssa-remove-all-appointments'] ) ) {
			return;
		}
		
		if (!current_user_can('ssa_manage_site_settings')) {
			return;
		}
		
		if ( wp_verify_nonce( $_GET['ssa_nonce'], 'ssa-remove-all-appointments' ) === false ) {
			return;
		}
		
		$appointments_to_delete = $this->plugin->appointment_model->query( array(
			'number' => -1,
		) );
		
		$appointments_to_delete_ids = wp_list_pluck( $appointments_to_delete, 'id' );
		
		$revisions_to_delete = $this->plugin->revision_model->query( array(
			'number' => -1,
			'appointment_id' => $appointments_to_delete_ids,
		) );
		
		$revisions_to_delete_ids = wp_list_pluck( $revisions_to_delete, 'id' );
		
		$revision_meta_to_delete = $this->plugin->revision_meta_model->query( array(
			'number' => -1,
			'revision_id' => $revisions_to_delete_ids,
		) );
		
		// remove all appointment async actions
		$this->plugin->async_action_model->bulk_delete( array(
			'number' => -1,
			'object_type' => 'appointment',
		) );
		
		$this->plugin->revision_meta_model->bulk_delete( wp_list_pluck( $revision_meta_to_delete, 'id' ) );
		$this->plugin->revision_model->bulk_delete( wp_list_pluck( $revisions_to_delete, 'id' ) );
		$this->plugin->resource_appointment_model->truncate();
		$this->plugin->payment_model->truncate();
		$this->plugin->appointment_meta_model->truncate();
		$this->plugin->appointment_model->truncate();
	}
	
	/**
	 * Removes GCAL excluded calendars that the user removed their subscription to. Both from the main calendar and staff profiles.
	 * 
	 */
	public function ssa_cleanup_excluded_calendars () {
		if ( empty( $_GET['ssa-cleanup-excluded-calendars'] ) ) {
			return;
		}
		
		if (!current_user_can('ssa_manage_site_settings')) {
			return;
		}
		
		if ( wp_verify_nonce( $_GET['ssa_nonce'], 'ssa-cleanup-excluded-calendars' ) === false ) {
			return;
		}
		
		// cleanup main excluded calendars
		$this->plugin->appointment_type_model->cleanup_main_excluded_calendars();

		$this->plugin->staff_model->cleanup_staff_excluded_calendars();

		
		wp_redirect( remove_query_arg( 'ssa-cleanup-excluded-calendars' ) );
		exit;
	}
	 
	/**
	 * Set the debug level.
	 * Used to write logs of levels lower than 10.
	 * 
	 */
	public function ssa_set_debug_level() {
		if ( empty( $_GET['ssa-set-debug-level'] ) ) {
			return;
		}
		
		if (!current_user_can('ssa_manage_site_settings')) {
			return;
		}
		
		if ( wp_verify_nonce( $_GET['ssa_nonce'], 'ssa-set-debug-level' ) === false ) {
			return;
		}
		
		$debug_level = (int) sanitize_text_field( $_GET['ssa-set-debug-level'] );
		
		update_option( 'ssa_debug_level', $debug_level );
		
		wp_redirect( remove_query_arg( 'ssa-set-debug-level' ) );
		exit;
	}
	
	public function ssa_set_license() {
		if ( empty( $_GET['ssa-set-license'] )) {
			return;
		}
		
		if (!current_user_can('ssa_manage_site_settings')) {
			return;
		}
		
		if ( wp_verify_nonce( $_GET['ssa_nonce'], 'ssa-set-license' ) === false ) {
			return;
		}

		$license_key = sanitize_text_field( $_GET['ssa-set-license'] );
		$this->plugin->license->activate( $license_key );

		wp_redirect( remove_query_arg( 'ssa-set-license' ) );
		exit;
	}

	public function populate_cache() {
		if ( empty( $_GET['ssa-populate-cache'] ) ) {
			return;
		}

		if ( ! current_user_can( 'ssa_manage_site_settings' ) ) {
			return;
		}
		
		if ( wp_verify_nonce( $_GET['ssa_nonce'], 'ssa-populate-cache' ) === false ) {
			return;
		}

		$this->plugin->availability_cache->populate_cache();

		wp_redirect( remove_query_arg( 'ssa-populate-cache' ) );
		exit;
	}

	public function clear_google_cache() {
		if ( empty( $_GET['ssa-clear-google-cache'] ) ) {
			return;
		}

		if ( ! current_user_can( 'ssa_manage_site_settings' ) ) {
			return;
		}
		
		if ( wp_verify_nonce( $_GET['ssa_nonce'], 'ssa-clear-google-cache' ) === false ) {
			return;
		}

		$this->plugin->availability_external_model->bulk_delete( array(
			'service' => 'google',
		) );
		$this->plugin->google_calendar->increment_google_cache_version();

		wp_redirect( remove_query_arg( 'ssa-clear-google-cache' ) );
		exit;
	}

	public function set_google_query_limit() {
		if ( empty( $_GET['ssa-set-google-query-limit'] ) ) {
			return;
		}

		if ( ! current_user_can( 'ssa_manage_site_settings' ) ) {
			return;
		}
		
		if ( wp_verify_nonce( $_GET['ssa_nonce'], 'ssa-set-google-query-limit' ) === false ) {
			return;
		}

		$set_google_query_limit = (int)esc_attr( $_GET['ssa-set-google-query-limit'] );
		if ( empty( $set_google_query_limit ) ) {
			return;
		}

		$this->plugin->google_calendar_settings->update( array(
			'query_limit' => $set_google_query_limit,
		) );

		$this->plugin->google_calendar->increment_google_cache_version();

		wp_redirect( remove_query_arg( 'ssa-set-google-query-limit' ) );
		exit;
	}

	public function set_display_capacity_available() {
		if ( ! isset ( $_GET['ssa-set-display-capacity-available'] ) ) {
			return;
		}
		
		if (!current_user_can('ssa_manage_site_settings')) {
			return;
		}

		if ( wp_verify_nonce( $_GET['ssa_nonce'], 'ssa-set-display-capacity-available' ) === false ) {
			return;
		}
		// if ( ! is_user_logged_in() ) {
		// 	return;
		// }
		$new_value = (bool) $_GET['ssa-set-display-capacity-available'];
		$developer_settings = $this->plugin->developer_settings->get();
		$developer_settings['display_capacity_available'] = $new_value;
		$this->plugin->developer_settings->update( $developer_settings );
		$this->plugin->availability_cache_invalidation->increment_cache_version();

		wp_redirect( remove_query_arg( 'ssa-set-display-capacity-available' ) );
		exit;
	}

	public function clear_all_cache() {
		if ( empty( $_GET['ssa-clear-all-cache'] ) ) {
			return;
		}

		if ( ! current_user_can( 'ssa_manage_site_settings' ) ) {
			return;
		}
		
		if ( wp_verify_nonce( $_GET['ssa_nonce'], 'ssa-clear-all-cache' ) === false ) {
			return;
		}

		$this->plugin->availability_external_model->bulk_delete( array(
			'service' => 'google',
		) );
		$this->plugin->google_calendar->increment_google_cache_version();
		$this->plugin->availability_cache_invalidation->increment_cache_version();

		wp_redirect( remove_query_arg( 'ssa-clear-all-cache' ) );
		exit;
	}

	public function fix_appointment_durations() {
		if ( empty( $_GET['ssa-fix-appointment-durations'] ) ) {
			return;
		}

		if ( ! current_user_can( 'ssa_manage_site_settings' ) ) {
			return;
		}
		
		if ( wp_verify_nonce( $_GET['ssa_nonce'], 'ssa-fix-appointment-durations' ) === false ) {
			return;
		}

		$appointments = $this->plugin->appointment_model->query( array(
			'number' => -1,
		) );
		$now = new DateTimeImmutable();

		foreach ($appointments as $key => $appointment) {
			if ( empty( $appointment['appointment_type_id'] ) ) {
				continue; // likely an abandoned appointment from a form integration (where an appointment type was never selected)
			}
			$appointment_type = new SSA_Appointment_Type_Object( $appointment['appointment_type_id'] );
			$duration = $appointment_type->duration;
			$start_date = new DateTimeImmutable( $appointment['start_date'] );

			$end_date = $start_date->add( new DateInterval( 'PT' .$duration. 'M' ) );
			if ( $end_date->format( 'Y-m-d H:i:s' ) != $appointment['end_date'] ) {
				echo '<pre>'.print_r($appointment, true).'</pre>'; // phpcs:ignore
				$appointment['end_date'] = $end_date->format( 'Y-m-d H:i:s' );

				$this->plugin->appointment_model->update( $appointment['id'], $appointment );
			}
		}

		wp_redirect( $this->plugin->wp_admin->url(), $status = 302);
		exit;
	}

	public function purge_abandoned_appointments() {
		if ( empty( $_GET['ssa-purge-abandoned-appointments'] ) ) {
			return;
		}

		if ( ! current_user_can( 'ssa_manage_site_settings' ) ) {
			return;
		}

		if ( wp_verify_nonce( $_GET['ssa_nonce'], 'ssa-purge-abandoned-appointments' ) === false ) {
			return;
		}
		
		$this->plugin->appointment_model->delete_abandoned();

		wp_redirect( $this->plugin->wp_admin->url(), $status = 302);
		exit;
	}

	public function fix_db_availability_schema() {
		if ( empty( $_GET['ssa-fix-db-availability-schema'] ) ) {
			return;
		}

		if ( ! current_user_can( 'ssa_manage_site_settings' ) ) {
			return;
		}

		if ( wp_verify_nonce( $_GET['ssa_nonce'], 'ssa-fix-db-availability-schema' ) === false ) {
			return;
		}

		$this->plugin->availability_model->drop();
		$this->plugin->availability_model->create_table();

		wp_redirect( $this->plugin->wp_admin->url(), $status = 302);
		exit;
	}

	public function fix_appointment_types() {
		if ( empty( $_GET['ssa-fix-appointment-types'] ) ) {
			return;
		}

		if ( ! current_user_can( 'ssa_manage_site_settings' ) ) {
			return;
		}

		if ( wp_verify_nonce( $_GET['ssa_nonce'], 'ssa-fix-appointment-types' ) === false ) {
			return;
		}
		
		$appointment_types = $this->plugin->appointment_type_model->query( array(
			'number' => -1,
		) );
		foreach ($appointment_types as $appointment_type) {
			if ( empty( $appointment_type['custom_customer_information'] ) ) {
				$appointment_type['custom_customer_information'] = array(
					array(
						'field' => 'Name',
						'display' => true,
						'required' => true,
						'type' => 'single-text',
						'icon' => 'face',
						'values' => '',
					),
					array(
						'field' => 'Email',
						'display' => true,
						'required' => true,
						'type' => 'single-text',
						'icon' => 'email',
						'values' => '',
					),
				);
			}

			if ( empty( $appointment_type['customer_information'] ) ) {
				$appointment_type['customer_information'] = array(
					array(
						'field' => 'Name',
						'display' => true,
						'required' => true,
						'type' => 'single-text',
						'icon' => 'face',
						'values' => '',
					),
					array(
						'field' => 'Email',
						'display' => true,
						'required' => true,
						'type' => 'single-text',
						'icon' => 'email',
						'values' => '',
					),
				);
			}

			$appointment_type['custom_customer_information'] = array_values( (array) $appointment_type['custom_customer_information'] );
			$appointment_type['customer_information'] = array_values( (array) $appointment_type['customer_information'] );

			$appointment_types = $this->plugin->appointment_type_model->update(
				$appointment_type['id'],
				$appointment_type
			);
		}

		wp_redirect( $this->plugin->wp_admin->url(), $status = 302);
		exit;
	}

	public function fix_missing_appointment_types() {
		if ( empty( $_GET['ssa-fix-missing-appointment-types'] ) ) {
			return;
		}

		if ( ! current_user_can( 'ssa_manage_site_settings' ) ) {
			return;
		}
		
		if ( wp_verify_nonce( $_GET['ssa_nonce'], 'ssa-fix-missing-appointment-types' ) === false ) {
			return;
		}

		$this->plugin->upgrade->maybe_fix_deleted_appointment_types();

		wp_redirect( $this->plugin->wp_admin->url(), $status = 302);
		exit;
	}
	public function fix_db_datetime_schema() {
		if ( empty( $_GET['ssa-fix-db-datetime-schema'] ) ) {
			return;
		}

		if ( ! current_user_can( 'ssa_manage_site_settings' ) ) {
			return;
		}
		
		if ( wp_verify_nonce( $_GET['ssa_nonce'], 'ssa-fix-db-datetime-schema' ) === false ) {
			return;
		}

		global $wpdb;

		$now = gmdate( 'Y-m-d H:i:s' );

		$before_queries = array(
			/* Appointment Types */
			"UPDATE {$this->plugin->appointment_type_model->get_table_name()} SET `booking_start_date`='".SSA_Constants::EPOCH_START_DATE."' WHERE `booking_start_date`=0",

			"UPDATE {$this->plugin->appointment_type_model->get_table_name()} SET `booking_end_date`='".SSA_Constants::EPOCH_END_DATE."' WHERE `booking_end_date`=0",

			"UPDATE {$this->plugin->appointment_type_model->get_table_name()} SET `availability_start_date`='".SSA_Constants::EPOCH_START_DATE."' WHERE `availability_start_date`=0",

			"UPDATE {$this->plugin->appointment_type_model->get_table_name()} SET `availability_end_date`='".SSA_Constants::EPOCH_END_DATE."' WHERE `availability_end_date`=0",

			"UPDATE {$this->plugin->appointment_type_model->get_table_name()} SET `date_created`='1970-01-01' where `date_created`=0",

			"UPDATE {$this->plugin->appointment_type_model->get_table_name()} SET `date_modified`='1970-01-01' where `date_modified`=0",

			/* Appointments */
			"UPDATE {$this->plugin->appointment_model->get_table_name()} SET `start_date`='1970-01-01' where `start_date`=0",

			"UPDATE {$this->plugin->appointment_model->get_table_name()} SET `end_date`='1970-01-01' where `end_date`=0",

			"UPDATE {$this->plugin->appointment_model->get_table_name()} SET `date_created`='1970-01-01' where `date_created`=0",

			"UPDATE {$this->plugin->appointment_model->get_table_name()} SET `date_modified`='1970-01-01' where `date_modified`=0",

		);

		$after_queries = array(
			/* Appointment Types */
			"UPDATE {$this->plugin->appointment_type_model->get_table_name()} SET `booking_start_date`=NULL where `booking_start_date`='".SSA_Constants::EPOCH_START_DATE."'",

			"UPDATE {$this->plugin->appointment_type_model->get_table_name()} SET `booking_end_date`=NULL where `booking_end_date`='".SSA_Constants::EPOCH_END_DATE."'",

			"UPDATE {$this->plugin->appointment_type_model->get_table_name()} SET `availability_start_date`=NULL where `availability_start_date`='".SSA_Constants::EPOCH_START_DATE."'",

			"UPDATE {$this->plugin->appointment_type_model->get_table_name()} SET `availability_end_date`=NULL where `availability_end_date`='".SSA_Constants::EPOCH_END_DATE."'",
		);

		$has_failed = false;
		foreach ($before_queries as $query) {
			$result = $wpdb->query( $query );
			if ( false === $result ) {
				$has_failed = true;
			}
		}

		$this->plugin->appointment_type_model->create_table();
		$this->plugin->appointment_model->create_table();

		foreach ($after_queries as $query) {
			$result = $wpdb->query( $query );
			if ( false === $result ) {
				$has_failed = true;
			}
		}

		$this->fix_appointment_group_ids( true );

		wp_redirect( $this->plugin->wp_admin->url(), $status = 302);
		exit;
	}

	public function fix_appointment_group_ids( $force = false ) {
		if ( empty( $force ) && empty( $_GET['ssa-fix-appointment-group-ids'] ) ) {
			return;
		}

		if ( ! current_user_can( 'ssa_manage_site_settings' ) ) {
			return;
		}
		
		if ( wp_verify_nonce( $_GET['ssa_nonce'], 'ssa-fix-appointment-group-ids' ) === false ) {
			return;
		}

		$appointments = $this->plugin->appointment_model->query( array(
			'number' => -1,
		) );
		$now = new DateTimeImmutable();

		foreach ($appointments as $key => $appointment) {
			if ( ! empty( $appointment['group_id'] ) ) {
				continue;
			}

			$appointment_type = new SSA_Appointment_Type_Object( $appointment['appointment_type_id'] );
			$capacity_type = $appointment_type->capacity_type;
			if ( empty( $capacity_type ) || $capacity_type !== 'group' ) {
				continue;
			}

			$start_date = new DateTimeImmutable( $appointment['start_date'] );

			$args = array(
				'number' => -1,
				'orderby' => 'id',
				'order' => 'ASC',
				'appointment_type_id' => $appointment['appointment_type_id'],
				'start_date' => $appointment['start_date'],
				'exclude_ids' => $appointment['id'],
			);

			$new_group_id = 0;
			$appointment_arrays = $this->plugin->appointment_model->query( $args );
			foreach ($appointment_arrays as $appointment_array) {
				if ( ! empty( $appointment_array['group_id'] ) ) {
					$new_group_id = $appointment_array['group_id'];
				}
			}

			if ( empty( $new_group_id ) && empty( $appointment_arrays[0]['id'] ) ) {
				continue;
			}

			$new_group_id = $appointment_arrays[0]['id'];

			$this->plugin->appointment_model->update( $appointment['id'], array(
				'group_id' => $new_group_id
			) );

			foreach ($appointment_arrays as $appointment_array) {
				$this->plugin->appointment_model->update( $appointment_array['id'], array(
					'group_id' => $new_group_id
				) );
			}
		}

		wp_redirect( $this->plugin->wp_admin->url(), $status = 302);
		exit;
	}

	/**
	 * Deletes all ssa related options from wp_options table and truncate all ssa database tables.
	 *
	 * @since 5.4.3
	 *
	 * @return void
	 */
	public function ssa_factory_reset() {
		if ( empty( $_GET['ssa-factory-reset'] ) ) { // phpcs:ignore
			return;
		}

		if ( ! current_user_can( 'ssa_manage_site_settings' ) ) {
			return;
		}

		if ( wp_verify_nonce( $_GET['ssa_nonce'], 'ssa-factory-reset' ) === false ) {
			return;
		}

		// Finds and delete all rows from the wp_options table that contains "ssa_" in the option_name column.
		global $wpdb;

		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'ssa\_%'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		// Truncate all ssa database tables.
		$this->plugin->appointment_model->truncate();
		$this->plugin->appointment_meta_model->truncate();
		$this->plugin->appointment_type_model->truncate();
		$this->plugin->availability_model->truncate();
		$this->plugin->availability_external_model->truncate();
		$this->plugin->async_action_model->truncate();
		$this->plugin->payment_model->truncate();
		$this->plugin->staff_model->truncate();
		$this->plugin->staff_appointment_model->truncate();
		$this->plugin->staff_appointment_type_model->truncate();
		$this->plugin->resource_model->truncate();
		$this->plugin->resource_group_model->truncate();
		$this->plugin->resource_group_resource_model->truncate();
		$this->plugin->resource_group_appointment_type_model->truncate();
		$this->plugin->resource_appointment_model->truncate();
		$this->plugin->revision_model->truncate();
		$this->plugin->revision_meta_model->truncate();

		wp_safe_redirect( $this->plugin->wp_admin->url(), $status = 302 );
		exit;
	}

	public function rebuild_db() {
		if ( empty( $_GET['ssa-rebuild-db'] ) ) {
			return;
		}

		if ( ! current_user_can( 'ssa_manage_site_settings' ) ) {
			return;
		}
		
		if ( wp_verify_nonce( $_GET['ssa_nonce'], 'ssa-rebuild-db' ) === false ) {
			return;
		}

		$this->plugin->appointment_model->create_table();
		$this->plugin->appointment_meta_model->create_table();
		$this->plugin->appointment_type_model->create_table();
		$this->plugin->appointment_type_label_model->create_table();
		$this->plugin->availability_model->create_table();
		$this->plugin->availability_external_model->create_table();
		$this->plugin->async_action_model->create_table();
		$this->plugin->payment_model->create_table();
		$this->plugin->revision_model->create_table();
		$this->plugin->revision_meta_model->create_table();
		$this->plugin->resource_appointment_model->create_table();
		$this->plugin->resource_group_appointment_type_model->create_table();
		$this->plugin->resource_model->create_table();
		$this->plugin->resource_group_model->create_table();
		$this->plugin->resource_group_resource_model->create_table();
		$this->plugin->staff_model->create_table();
		$this->plugin->staff_appointment_model->create_table();
		$this->plugin->staff_appointment_type_model->create_table();

		wp_redirect( $this->plugin->wp_admin->url(), $status = 302);
		exit;
	}

	public function restore_plugin_backup() {
		if ( empty( $_GET['ssa-restore-backup'] ) ) {
			return;
		}

		if ( ! current_user_can( 'ssa_manage_site_settings' ) ) {
			return;
		}

		if ( wp_verify_nonce( $_GET['ssa_nonce'], 'ssa-restore-backup' ) === false ) {
			return;
		}
		
		// restore previous backup file
		$restore = $this->plugin->support_status->restore_settings_backup();

		// if something happens, print the errors
		if( is_wp_error( $restore ) ) {
			$string = implode("\n", $restore->get_error_messages());
			wp_die($string);
		}

		wp_redirect( $this->plugin->wp_admin->url(), $status = 302);
		exit;
	}

	/**
	 * Given a GET parameter on the url, get a list of future booked appointments and schedule notifications for the ones that are valid.
	 *
	 * @since 4.8.8
	 *
	 * @return void
	 */
	public function bulk_send_notifications() {
		if ( empty( $_GET['ssa-resend-booked-notifications'] ) ) {
			return;
		}

		if ( ! current_user_can( 'ssa_manage_site_settings' ) ) {
			return;
		}
		
		if ( wp_verify_nonce( $_GET['ssa_nonce'], 'ssa-resend-booked-notifications' ) === false ) {
			return;
		}

		// Get list of booked appointments.
		$appointments = $this->plugin->appointment_model->query(
			array(
				'status'         => SSA_Appointment_Model::get_booked_statuses(),
				'start_date_min' => gmdate( 'Y-m-d H:i:s' ),
				'number'         => -1,
			)
		);

		$notifications = $this->plugin->notifications_settings->get_notifications();

		if ( empty( $notifications ) ) {
			wp_safe_redirect( $this->plugin->wp_admin->url(), $status = 302 );
			exit;
		}

		// filter list of notifications to return only the one to be sent to the customer.
		$customer_booked_notifications = array_values(
			array_filter(
				$notifications,
				function( $notification ) {
					return (
						( !isset( $notification['active'] ) || true == $notification['active'] ) &&
						'appointment_booked' === $notification['trigger'] &&
						strpos( implode( ';', $notification['sent_to'] ), 'customer_email' ) !== false
					);
				}
			)
		);

		// If notification is found, then send list of appointments to validate the notification and possibly send them.
		if ( ! empty( $customer_booked_notifications ) ) {
			foreach ( $customer_booked_notifications as $customer_booked_notification ) {
				$this->plugin->action_scheduler->bulk_schedule_notifications( $customer_booked_notification, $appointments );
			}
		}

		wp_safe_redirect( $this->plugin->wp_admin->url(), $status = 302 );
		exit();
	}

	public static function should_display_support_tab() {
		// hide support tab via wp-config
		if( defined( 'SSA_DISPLAY_SUPPORT_TAB' ) && false === SSA_DISPLAY_SUPPORT_TAB ) {
				return false;
		}
		if( is_multisite() && ! current_user_can('manage_network') ) {
			return false;
		}
		return true;
	}

}
