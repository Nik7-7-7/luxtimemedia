<?php
/**
 * Plugin Name: Simply Schedule Appointments
 * Plugin URI:  https://simplyscheduleappointments.com
 * Description: Easy appointment scheduling
 * Version:     1.6.7.65
 * Requires PHP: 7.4
 * Author:      NSquared
 * Author URI:  https://nsquared.io/
 * Donate link: https://simplyscheduleappointments.com
 * License:     GPLv2
 * Text Domain: simply-schedule-appointments
 * Domain Path: /languages
 *
 * @link    https://simplyscheduleappointments.com
 *
 * @package Simply_Schedule_Appointments
 * @version 1.6.7.65
 *
 * Built using generator-plugin-wp (https://github.com/WebDevStudios/generator-plugin-wp)
 */

 
/**
 * Copyright (c) 2017 N Squared (email : support@simplyscheduleappointments.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */


/**
 * Autoloads files with classes when needed.
 *
 * @since  0.0.0
 * @param  string $class_name Name of the class being requested.
 */
if ( ! function_exists( 'ssa_autoload_classes' ) ) {
	function ssa_autoload_classes( $class_name ) {

		// If our class doesn't have our prefix, don't load it.
		if ( 0 !== strpos( $class_name, 'SSA_' ) ) {
			return;
		}

		// Set up our filename.
		$filename = strtolower( str_replace( '_', '-', substr( $class_name, strlen( 'SSA_' ) ) ) );

		// Include our file.
		Simply_Schedule_Appointments::include_file( 'includes/class-' . $filename );
	}
}

spl_autoload_register( 'ssa_autoload_classes' );
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	if ( version_compare( phpversion(), '7.0', '>=' ) ) {
		include_once __DIR__ . '/vendor/autoload.php';
	 }
 };

define( 'SSA_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Main initiation class.
 *
 * @since  0.0.0
 */
final class Simply_Schedule_Appointments {
	
	// declare class properties, avoid dynamic properties and their warnings
	public $action_scheduler;
	public $advanced_scheduling_availability;
	public $advanced_scheduling_settings;
	public $appointment_meta_model;
	public $appointment_model;
	public $appointment_type_label_model;
	public $appointment_type_model;
	public $async_action_model;
	public $availability_cache_invalidation;
	public $availability_cache;
	public $availability_default;
	public $availability_external_model;
	public $availability_functions;
	public $availability_model;
	public $beaver_builder;
	public $blackout_dates_settings;
	public $blackout_dates;
	public $block_booking;
	public $block_upcoming_appointments;
	public $bootstrap;
	public $cache;
	public $calendar_events_settings;
	public $capabilities;
	public $capacity_settings;
	public $capacity;
	public $csv_exporter;
	public $customer_information;
	public $customers;
	public $dashboard_upcoming_appointments_widget;
	public $developer_settings;
	public $divi;
	public $elementor;
	public $error_notices;
	public $filesystem;
	public $formidable;
	public $forms;
	public $gcal_exporter;
	public $google_calendar_admin;
	public $google_calendar_api;
	public $embed_booking_app_api;
	public $google_calendar_client;
	public $google_calendar_settings;
	public $google_calendar;
	public $gravityforms;
	public $health_check;
	public $hooks;
	public $license_api;
	public $license_settings;
	public $license;
	public $mailchimp_api;
	public $mailchimp_settings;
	public $mailchimp;
	public $missing;
	public $notices_api;
	public $notices;
	public $notification_model;
	public $notifications_api;
	public $notifications_settings;
	public $notifications;
	public $offline_payments_settings;
	public $offline_payments;
	public $payment_model;
	public $payments_settings;
	public $payments;
	public $paypal_settings;
	public $paypal;
	public $reminders;
	public $resource_appointment_model;
	public $resource_group_appointment_type_model;
	public $resource_group_model;
	public $resource_group_resource_model;
	public $resource_model;
	public $resources_settings;
	public $resources;
	public $revision_meta_model;
	public $revision_model;
	public $scheduling_max_per_day;
	public $settings_api;
	public $settings_global;
	public $settings_installed;
	public $settings;
	public $shortcodes;
	public $sms_api;
	public $sms_settings;
	public $sms;
	public $staff_appointment_model;
	public $staff_appointment_type_model;
	public $staff_availability;
	public $staff_model;
	public $staff_settings;
	public $staff;
	public $stripe_settings;
	public $stripe;
	public $styles_settings;
	public $styles;
	public $support_status_api;
	public $support_status;
	public $support;
	public $templates_api;
	public $templates;
	public $tracking_settings;
	public $tracking;
	public $translation_settings;
	public $translation;
	public $upgrade;
	public $users;
	public $utils;
	public $validation;
	public $web_meetings;
	public $webex_settings;
	public $webex;
	public $webhooks_settings;
	public $webhooks;
	public $woocommerce_settings;
	public $woocommerce;
	public $wp_admin;
	public $zoom_settings;
	public $zoom;
	
	/**
	 * Current version.
	 *
	 * @var    string
	 * @since  0.0.0
	 */
	const VERSION = '1.6.7.65';

	/**
	 * URL of plugin directory.
	 *
	 * @var    string
	 * @since  0.0.0
	 */
	protected $url = '';

	/**
	 * Path of plugin directory.
	 *
	 * @var    string
	 * @since  0.0.0
	 */
	protected $path = '';

	/**
	 * Plugin basename.
	 *
	 * @var    string
	 * @since  0.0.0
	 */
	protected $basename = '';

	/**
	 * Detailed activation error messages.
	 *
	 * @var    array
	 * @since  0.0.0
	 */
	protected $activation_errors = array();

	/**
	 * Singleton instance of plugin.
	 *
	 * @var    Simply_Schedule_Appointments
	 * @since  0.0.0
	 */
	protected static $single_instance = null;

	/**
	 * Instance of SSA_Debug
	 *
	 * @since4.0.1
	 * @var SSA_Debug
	 */
	protected $debug;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @since   0.0.0
	 * @return  Simply_Schedule_Appointments A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$single_instance ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}

	/**
	 * Sets up our plugin.
	 *
	 * @since  0.0.0
	 */
	protected function __construct() {
		$this->basename = plugin_basename( __FILE__ );
		$this->url      = plugin_dir_url( __FILE__ );
		$this->path     = plugin_dir_path( __FILE__ );

		require $this->dir( 'includes/lib/td-health-check/health-check.php' );
		$this->health_check = new TD_Health_Check();
		require_once __DIR__ . '/vendor/woocommerce/action-scheduler/action-scheduler.php';

		if ( ! defined( 'WP_SENTRY_VERSION' ) ) {
			define( 'WP_SENTRY_VERSION', 'v' . self::VERSION );
		}
	}

	/**
	 * Attach other plugin classes to the base plugin class.
	 *
	 * @since  0.0.0
	 */
	public function plugin_classes() {
		$classes = array(
			'settings'                               => 'SSA_Settings',
			'bootstrap'                              => 'SSA_Bootstrap',
			'missing'                                => 'SSA_Missing',
			'upgrade'                                => 'SSA_Upgrade',
			'utils'                                  => 'SSA_Utils',
			'validation'                             => 'SSA_Validation',
			'capabilities'                           => 'SSA_Capabilities',
			'hooks'                                  => 'SSA_Hooks',
			
			'resources'                     		 => 'SSA_Resources',
			'resources_settings'                     => 'SSA_Resources_Settings',
			'resource_model'                         => 'SSA_Resource_Model',
			'resource_group_model'             		 => 'SSA_Resource_Group_Model',
			'resource_appointment_model'             => 'SSA_Resource_Appointment_Model',
			'resource_group_appointment_type_model'  => 'SSA_Resource_Group_Appointment_Type_Model',
			'resource_group_resource_model'  		 => 'SSA_Resource_Group_Resource_Model',
			'revision_model'                         => 'SSA_Revision_Model',
			'revision_meta_model'                    => 'SSA_Revision_Meta_Model',
			'appointment_model'                      => 'SSA_Appointment_Model',
			'appointment_meta_model'                 => 'SSA_Appointment_Meta_Model',
			'appointment_type_model'                 => 'SSA_Appointment_Type_Model',
			'availability_model'                     => 'SSA_Availability_Model',
			'appointment_type_label_model'           => 'SSA_Appointment_Type_Label_Model',
			'availability_external_model'            => 'SSA_Availability_External_Model',
			'availability_functions'                 => 'SSA_Availability_Functions',
			'availability_default'                   => 'SSA_Availability_Default',
			'availability_cache'                     => 'SSA_Availability_Cache',
			'availability_cache_invalidation'        => 'SSA_Availability_Cache_Invalidation',

			'capacity_settings'                      => 'SSA_Capacity_Settings',
			'capacity'                               => 'SSA_Capacity',
			'cache'                                  => 'SSA_Cache',

			'debug'                                  => 'SSA_Debug',

			'customers'                              => 'SSA_Customers',
			'customer_information'                   => 'SSA_Customer_Information',

			'scheduling_max_per_day'                 => 'SSA_Scheduling_Max_Per_Day',

			'settings_installed'                     => 'SSA_Settings_Installed',
			'settings_global'                        => 'SSA_Settings_Global',

			'gcal_exporter'                          => 'SSA_Gcal_Exporter',
			'notifications'                          => 'SSA_Notifications',
			'notifications_settings'                 => 'SSA_Notifications_Settings',
			'notification_model'                     => 'SSA_Notification_Model',
			'notices'                                => 'SSA_Notices',
			'error_notices'                          => 'SSA_Error_Notices',
			'csv_exporter'                           => 'SSA_CSV_Exporter',

			'calendar_events_settings'               => 'SSA_Calendar_Events_Settings',

			'async_action_model'                     => 'SSA_Async_Action_Model',

			/* Features */
			'developer_settings'                     => 'SSA_Developer_Settings',

			'license_settings'                       => 'SSA_License_Settings',
			'license'                                => 'SSA_License',

			'advanced_scheduling_settings'           => 'SSA_Advanced_Scheduling_Settings',
			'advanced_scheduling_availability'       => 'SSA_Advanced_Scheduling_Availability',

			'blackout_dates_settings'                => 'SSA_Blackout_Dates_Settings',
			'blackout_dates'                         => 'SSA_Blackout_Dates',

			'elementor'                              => 'SSA_Elementor',
			'beaver_builder'                         => 'SSA_Beaver_Builder',
			'divi'                                   => 'SSA_Divi',
			'forms'                                  => 'SSA_Forms',
			'formidable'                             => 'SSA_Formidable',

			'staff_settings'                         => 'SSA_Staff_Settings',
			'staff'                                  => 'SSA_Staff',

			'staff_model'                            => 'SSA_Staff_Model',
			'staff_appointment_model'                => 'SSA_Staff_Appointment_Model',
			'staff_appointment_type_model'           => 'SSA_Staff_Appointment_Type_Model',
			'staff_availability'                     => 'SSA_Staff_Availability',

			'google_calendar_settings'               => 'SSA_Google_Calendar_Settings',
			'google_calendar'                        => 'SSA_Google_Calendar',
			'google_calendar_client'                        => 'SSA_Google_Calendar_Client',
			'google_calendar_admin'                  => 'SSA_Google_Calendar_Admin',

			'gravityforms'                           => 'SSA_Gravityforms',

			'payment_model'                          => 'SSA_Payment_Model',

			'mailchimp_settings'                     => 'SSA_Mailchimp_Settings',
			'mailchimp'                              => 'SSA_Mailchimp',

			'offline_payments_settings'              => 'SSA_Offline_Payments_Settings',
			'offline_payments'                       => 'SSA_Offline_Payments',

			'payments_settings'                      => 'SSA_Payments_Settings',
			'payments'                               => 'SSA_Payments',

			'paypal_settings'                        => 'SSA_Paypal_Settings',
			'paypal'                                 => 'SSA_Paypal',

			'reminders'                              => 'SSA_Reminders',

			'sms'                                    => 'SSA_Sms',
			'sms_settings'                           => 'SSA_Sms_Settings',

			'stripe_settings'                        => 'SSA_Stripe_Settings',
			'stripe'                                 => 'SSA_Stripe',

			'styles_settings'                        => 'SSA_Styles_Settings',
			'styles'                                 => 'SSA_Styles',

			'support'                                => 'SSA_Support',
			'support_status'                         => 'SSA_Support_Status',

			'templates'                              => 'SSA_Templates',

			'tracking_settings'                      => 'SSA_Tracking_Settings',
			'tracking'                               => 'SSA_Tracking',

			'translation'                            => 'SSA_Translation',
			'translation_settings'                   => 'SSA_Translation_Settings',

			'users'                                  => 'SSA_Users',

			'webhooks_settings'                      => 'SSA_Webhooks_Settings',
			'webhooks'                               => 'SSA_Webhooks',

			'web_meetings'                           => 'SSA_Web_Meetings',

			'woocommerce_settings'                   => 'SSA_Woocommerce_Settings',
			'woocommerce'                            => 'SSA_Woocommerce',

			'webex'                                  => 'SSA_Webex',
			'webex_settings'                         => 'SSA_Webex_Settings',

			'zoom_settings'                          => 'SSA_Zoom_Settings',
			'zoom'                                   => 'SSA_Zoom',

			'shortcodes'                             => 'SSA_Shortcodes',
			'block_booking'                          => 'SSA_Block_Booking',
			'block_upcoming_appointments'            => 'SSA_Block_Upcoming_Appointments',
			'filesystem'                             => 'SSA_Filesystem',
			'wp_admin'                               => 'SSA_Wp_Admin',
			'action_scheduler'                       => 'SSA_Action_Scheduler',
			'dashboard_upcoming_appointments_widget' => 'SSA_Dashboard_Upcoming_Appointments_Widget',

			// NO API CLASSES SHOULD BE HERE (should be defined in rest_api_init hook)
		);

		include __DIR__ . '/includes/class-exception.php';

		foreach ( $classes as $variable_name => $class_name ) {
			if ( class_exists( $class_name ) ) {
				$this->$variable_name = new $class_name( $this );
			} else {
				$this->$variable_name = $this->missing;
			}
		}
	} // END OF PLUGIN CLASSES FUNCTION

	/**
	 * Attach other plugin classes to the base plugin class.
	 *
	 * @since  0.0.2
	 */
	public function rest_api_init() {
		$classes = array(
			'settings_api'        => 'SSA_Settings_Api',
			'notices_api'         => 'SSA_Notices_Api',
			'license_api'         => 'SSA_License_Api',
			'templates_api'       => 'SSA_Templates_Api',

			'google_calendar_api' => 'SSA_Google_Calendar_Api',
			'notifications_api'   => 'SSA_Notifications_Api',
			'mailchimp_api'       => 'SSA_Mailchimp_Api',
			'sms_api'             => 'SSA_Sms_Api',
			'support_status_api'  => 'SSA_Support_Status_Api',
			'embed_booking_app_api'=> 'SSA_Embed_Booking_App_Api',
		);

		foreach ( $classes as $variable_name => $class_name ) {
			if ( class_exists( $class_name ) ) {
				$this->$variable_name = new $class_name( $this );
			} else {
				$this->$variable_name = $this->missing;
			}
		}
	}

	/**
	 * data-cfasync is an HTML attribute used to indicate to Cloudflare's Rocket Loader
	 * whether or not to load a particular script asynchronously
	 */
	public function rocket_loader_filter( $tag, $handle, $src ) {
		if( 'ssa-iframe-outer' === $handle || 'ssa-form-embed' === $handle ){
			return str_replace( '<script', '<script data-cfasync="false"', $tag );
		}
		// keep other scripts untouched
		return $tag;
	}

	/**
	 * Add hooks and filters.
	 * Priority needs to be
	 * < 10 for CPT_Core,
	 * < 5 for Taxonomy_Core,
	 * and 0 for Widgets because widgets_init runs at init priority 1.
	 *
	 * @since  0.0.0
	 */
	public function hooks() {
		if ( ! function_exists( 'ray' ) ) {
			function ray() {
				// we need this empty function in case a ray() call mistakenly makes it into a production release
			}
		}
		$this->plugins_loaded();
		add_action( 'init', array( $this, 'init' ), 0 );
		// Avoid Cloudflare's Rocket Loader lazy load the editor iframe
		add_filter( 'script_loader_tag', array( $this, 'rocket_loader_filter' ), 10, 3 );
		add_action( 'rest_api_init', array( $this, 'rest_api_init' ), 0 );
	}
	/**
	 * Activate the plugin.
	 *
	 * @since  0.0.0
	 */
	public function _activate() {
		// Bail early if requirements aren't met.
		if ( ! $this->check_requirements() ) {
			return;
		}

		// Make sure any rewrite functionality has been loaded.
		flush_rewrite_rules();
	}

	/**
	 * Deactivate the plugin.
	 * Uninstall routines should be in uninstall.php.
	 *
	 * @since  0.0.0
	 */
	public function _deactivate() {
		// Add deactivation cleanup functionality here.
	}

	public static function _uninstall(){
		global $wpdb;
		$table_prefix = $wpdb->prefix;
		// Get Developer Settings
		$wpdb->query("SELECT option_value FROM $wpdb->options WHERE option_name = 'ssa_settings_json'");
		$settings = json_decode($wpdb->last_result[0]->option_value);
	
		if ( ! empty( $settings->developer->remove_data_on_uninstall) ) {
			// Finds and delete all rows from the wp_options table that contains "ssa_" in the option_name column.
			$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'ssa\_%' OR option_name LIKE 'wp_ssa\_%' OR option_name LIKE '{$table_prefix}ssa\_%'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		
			// Truncate all ssa database tables.
			$wpdb->query("DROP TABLE {$table_prefix}ssa_appointments");
			$wpdb->query("DROP TABLE {$table_prefix}ssa_appointment_meta");
			$wpdb->query("DROP TABLE {$table_prefix}ssa_appointment_types");
			$wpdb->query("DROP TABLE {$table_prefix}ssa_availability");
			$wpdb->query("DROP TABLE {$table_prefix}ssa_availability_external");
			$wpdb->query("DROP TABLE {$table_prefix}ssa_async_actions");
			$wpdb->query("DROP TABLE {$table_prefix}ssa_payments");
			$wpdb->query("DROP TABLE {$table_prefix}ssa_staff");
			$wpdb->query("DROP TABLE {$table_prefix}ssa_staff_appointments");
			$wpdb->query("DROP TABLE {$table_prefix}ssa_staff_appointment_types");
			$wpdb->query("DROP TABLE {$table_prefix}ssa_appointment_type_labels");
			$wpdb->query("DROP TABLE {$table_prefix}ssa_revisions");
			$wpdb->query("DROP TABLE {$table_prefix}ssa_revision_meta");
		}
	}

	public function plugins_loaded() {
		// Bail early if requirements aren't met.
		if ( ! $this->check_requirements() ) {
			return;
		}

		require $this->dir( 'includes/lib/td-util/td-util-init.php' );

		// Initialize plugin classes.
		$this->plugin_classes();

		// Load translated strings for plugin.
		load_plugin_textdomain( 'simply-schedule-appointments', false, dirname( $this->basename ) . '/languages/' );

		do_action( 'ssa_loaded' );
	}

	/**
	 * Init hooks
	 *
	 * @since  0.0.0
	 */
	public function init() {

	}

	/**
	 * Check if the plugin meets requirements and
	 * disable it if they are not present.
	 *
	 * @since  0.0.0
	 *
	 * @return boolean True if requirements met, false if not.
	 */
	public function check_requirements() {

		// Bail early if plugin meets requirements.
		if ( $this->meets_requirements() ) {
			return true;
		}

		// Add a dashboard notice.
		add_action( 'all_admin_notices', array( $this, 'requirements_not_met_notice' ) );

		// Deactivate our plugin.
		add_action( 'admin_init', array( $this, 'deactivate_me' ) );

		// Didn't meet the requirements.
		return false;
	}

	/**
	 * Deactivates this plugin, hook this function on admin_init.
	 *
	 * @since  0.0.0
	 */
	public function deactivate_me() {

		// We do a check for deactivate_plugins before calling it, to protect
		// any developers from accidentally calling it too early and breaking things.
		if ( function_exists( 'deactivate_plugins' ) ) {
			deactivate_plugins( $this->basename );
		}
	}

	/**
	 * Check that all plugin requirements are met.
	 *
	 * @since  0.0.0
	 *
	 * @return boolean True if requirements are met.
	 */
	public function meets_requirements() {
		if ( version_compare( phpversion(), '7.4', '<' ) ) {
			$this->activation_errors[] = 'Simply Schedule Appointments requires <strong>PHP version 7.4 or higher</strong>. Most WordPress hosts are supporting PHP 7, so your web host should easily be able to update you to PHP 7.4 or higher if you contact them. <br />A higher PHP version is safer, faster, and best of all lets you use Simply Schedule Appointments :) <br /><a href="https://simplyscheduleappointments.com/">Learn More</a>';
			return false;
		}

		if ( ! class_exists( '\League\Period\Period' ) ) {
			$this->activation_errors[] = 'Core library <code>Period</code> missing, please <a href="mailto:support@simplyscheduleappointments.com">contact support</a>';
		}

		if ( ! empty( $this->activation_errors ) ) {
			return false;
		}

		// Handle edge case - with no permalinks set, all WP REST API calls will fail
		global $wp_rewrite;
		if ( ! empty( $wp_rewrite ) && empty( $wp_rewrite->permalink_structure ) ) {
			$wp_rewrite->set_permalink_structure( '/%postname%/' );
			update_option( 'rewrite_rules', false );
			$wp_rewrite->flush_rules( true );
		}

		// Do checks for required classes / functions or similar.
		// Add detailed messages to $this->activation_errors array.
		return true;
	}

	/**
	 * Adds a notice to the dashboard if the plugin requirements are not met.
	 *
	 * @since  0.0.0
	 */
	public function requirements_not_met_notice() {

		// Compile default message.
		$default_message = sprintf( __( 'Simply Schedule Appointments detected that your system does not meet the minimum requirements. We\'ve <a href="%s">deactivated</a> Simply Schedule Appointments to make sure nothing breaks.', 'simply-schedule-appointments' ), admin_url( 'plugins.php' ) );

		// Default details to null.
		$details = null;

		// Add details if any exist.
		if ( $this->activation_errors && is_array( $this->activation_errors ) ) {
			$details = '<h4>' . implode( '</h4><br /><h4>', $this->activation_errors ) . '</h4>';
		}

		// Output errors.
		?>
		<div id="message" class="error">
			<h3><?php echo wp_kses_post( $default_message ); ?></h3>
			<?php echo wp_kses_post( $details ); ?>
		</div>
		<?php
	}

	/**
	 * Magic getter for our object.
	 *
	 * @since  0.0.0
	 *
	 * @param  string $field Field to get.
	 * @throws Exception     Throws an exception if the field is invalid.
	 * @return mixed         Value of the field.
	 */
	public function __get( $field ) {
		switch ( $field ) {
			case 'version':
				return self::VERSION;
			case 'basename':
			case 'debug':
			case 'missing':
				if ( property_exists( $this, $field ) && ! is_null( $this->$field ) ) {
					return $this->$field;
				} else {
					return $this->missing;
				}
			default:
				return $this->missing;
		}
	}

	/**
	 * Include a file from the includes directory.
	 *
	 * @since  0.0.0
	 *
	 * @param  string $filename Name of the file to be included.
	 * @return boolean          Result of include call.
	 */
	public static function include_file( $filename ) {
		$file = self::dir( $filename . '.php' );
		if ( file_exists( $file ) ) {
			return include_once $file;
		}
		return false;
	}

	/**
	 * This plugin's directory.
	 *
	 * @since  0.0.0
	 *
	 * @param  string $path (optional) appended path.
	 * @return string       Directory and path.
	 */
	public static function dir( $path = '' ) {
		static $dir;
		$dir = $dir ? $dir : trailingslashit( dirname( __FILE__ ) );
		return $dir . $path;
	}

	/**
	 * This plugin's template subdirectory.
	 *
	 * @since  0.0.0
	 *
	 * @param  string $path (optional) appended path.
	 * @return string       Directory and path.
	 */
	public static function template_subdirectory() {
		return apply_filters( 'ssa_template_subdirectory', 'ssa/' );
	}

	/**
	 * This plugin's url.
	 *
	 * @since  0.0.0
	 *
	 * @param  string $path (optional) appended path.
	 * @return string       URL and path.
	 */
	public static function url( $path = '' ) {
		static $url;
		$url = $url ? $url : trailingslashit( plugin_dir_url( __FILE__ ) );
		return $url . $path;
	}

	/**
	 * This plugin's version.
	 *
	 * @since 4.4.9
	 *
	 * @return string
	 */
	public function get_current_version() {
		return $this->__get( 'version' );
	}

	/**
	 * The current user's edition.
	 *
	 * @since 6.0.4
	 *
	 * @return integer
	 */
	public function get_current_edition() {
		$current_edition = substr( $this->get_current_version(), 0, 1 );

		// Handle case when there is no prefix added yet, assume it's development environment
		$editions = array_keys( $this->ssa_editions() );
		if ( ! in_array( $current_edition, $editions ) ) {
			$current_edition = max( $editions );
		}
		return (int) $current_edition;
	}

	private function ssa_editions () {
		return array(
			'1' => 'Basic',
			'2' => 'Plus',
			'3' => 'Pro',
			'4' => 'Business',
		);
	}

	/**
	 * The current ssa edition; Business, Pro, Plus, Basic
	 *
	 * @return string
	 */
	public function get_current_edition_str() {
		$editions     = $this->ssa_editions();
		$curr_edition = $this->get_current_edition();
		return $editions[ strval( $curr_edition ) ];
	}

	/**
	 * Update current plugin's version on the database.
	 *
	 * @since 4.4.9
	 *
	 * @return void
	 */
	public function store_current_version() {
		$version = $this->get_current_version();
		update_option( 'ssa_plugin_version', $version );
	}
}

/**
 * Grab the Simply_Schedule_Appointments object and return it.
 * Wrapper for Simply_Schedule_Appointments::get_instance().
 *
 * @since  0.0.0
 * @return Simply_Schedule_Appointments  Singleton instance of plugin class.
 */
function ssa() {
	return Simply_Schedule_Appointments::get_instance();
}

// Kick it off.
add_action( 'plugins_loaded', array( ssa(), 'hooks' ) );

// Activation and deactivation.
register_activation_hook( __FILE__, array( ssa(), '_activate' ) );
register_deactivation_hook( __FILE__, array( ssa(), '_deactivate' ) );

// Uninstall
register_uninstall_hook( __FILE__, array( 'Simply_Schedule_Appointments', '_uninstall' ) );

function ssa_is_debug() {
	if ( defined( 'SSA_DEBUG' ) && SSA_DEBUG ) {
		return true;
	}

	return false;
}

if ( defined( 'WP_CLI' ) && WP_CLI && method_exists( 'WP_CLI', 'add_command' ) ) {
	require_once ssa()->dir( 'includes/class-cli.php' );
}
