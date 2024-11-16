<?php
/**
 * File: rsssl-event-log.php
 *
 * This file contains the definition of the rsssl_event_log class, which is responsible for event logging.
 *
 * @package RSSSL\Pro\Security\WordPress
 * @author Marcel Santing
 */

namespace RSSSL\Pro\Security\WordPress;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

use DateTime;
use DateTimeZone;
use Exception;
use RSSSL\Pro\Security\WordPress\Limitlogin\Rsssl_Geo_Location;
use RSSSL\Pro\Security\DynamicTables\Rsssl_Data_Table;
use RSSSL\Pro\Security\DynamicTables\Rsssl_Query_Builder;
use RuntimeException;
use stdClass;
use wpdb;

if ( ! class_exists( 'Rsssl_Event_Log' ) ) {

	/**
	 * Class rsssl_event_log
	 * This class is used to log events to the database.
	 * and adds the appropriate actions to the bespoke events. Like limit login attempts.
	 *
	 * @package RSSSL\Pro\Security\WordPress
	 * @author Marcel Santing
	 */
	class Rsssl_Event_Log {
		public const TABLE = 'rsssl_event_logs';

		/**
		 * Rsssl_Event_Log constructor.
		 */
		public function __construct() {
			// we only run this once if the plugin was activated.
			add_action( 'rsssl_install_tables', array($this, 'create_event_log_table') );
			add_action( 'rsssl_after_save_field', array( $this, 'save_field_handler' ), 10, 4 );
		}

        /**
         * Checks if a table exists in the database.
         *
         * @return bool Returns true if the table exists, false otherwise.
         */
        public static function check_table_exists(): bool
        {
            global $wpdb;
            $table = $wpdb->base_prefix . self::TABLE;
            $result = $wpdb->get_results("SHOW TABLES LIKE '$table'");
            return count($result) > 0;
        }

        /**
		 * Handles the saving of a field.
		 *
		 * @param  string  $field_id  The ID of the field.
		 * @param  mixed  $field_value  The new value of the field.
		 * @param  mixed  $prev_value  The previous value of the field.
		 * @param  string  $field_type  The type of the field.
		 *
		 * @return void
		 * @throws Exception If the installation of the dependencies fails.
		 */
		public function save_field_handler( string $field_id, $field_value, $prev_value, string $field_type ): void {
			// Add your condition based on field_id, field_value, etc.
			if ( ('event_log_enabled') &&
			     true === (bool) $field_value ) {
				self::create_event_log_table();
			}
		}

		/**
		 * Create the event log table.
		 *
		 * @return void
		 */
		public static function create_event_log_table(): void {
			// Create the table.
			global $wpdb;
			$charset_collate = $wpdb->get_charset_collate();
			$table           = $wpdb->base_prefix . self::TABLE;
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			$sql = "CREATE TABLE $table (
	            id mediumint(9) NOT NULL AUTO_INCREMENT,
	            timestamp bigint NOT NULL,
	            event_id int(5) NOT NULL,
	            event_type TEXT NOT NULL,
	            event_name TEXT NOT NULL,
	            event_data TEXT NULL,
	            severity TEXT NOT NULL,
	            username TEXT NULL,
	            source_ip TEXT NULL,
	            description TEXT NULL,
	            PRIMARY KEY  (id)
	        ) $charset_collate;";

			dbDelta( $sql );
		}

		/**
		 * Log an event into the database.
		 *
		 * @param  array $data  An associative array containing the details of the event. Expected keys:
		 *                           - username: The username associated with the event.
		 *                           - timestamp: The event timestamp.
		 *                           - event_id: The ID of the event.
		 *                           - event_type: The type of event.
		 *                           - event_data: Additional data related to the event.
		 *                           - severity: The severity level of the event.
		 *                           - source_ip: The IP address from which the event originated.
		 *                           - description: A brief description of the event.
		 *
		 * @return void
		 * @throws Exception If the insertion fails.
		 */
		public static function log_event( array $data ): void {
			global $wpdb;
			// if the event id = 1010 or 1020 we add a small-time increase to the timestamp.
			if ( '1010' === $data['event_id'] || '1020' === $data['event_id'] ) {
				++ $data['timestamp'];
			}
			// Sanitize and validate data.
			$data['username']    = sanitize_text_field( $data['username'] );
			$data['timestamp']   = (int) $data['timestamp'];
			$data['event_id']    = (int) $data['event_id'];
			$data['event_type']  = sanitize_text_field( $data['event_type'] );
			$data['severity']    = sanitize_text_field( $data['severity'] );
			$data['source_ip']   = isset( $data['source_ip'] ) ? sanitize_text_field( $data['source_ip'] ) : null;
			$data['description'] = isset( $data['description'] ) ? sanitize_text_field( $data['description'] ) : null;

			// If no event exists or no timeframe provided, create a new event.
			// phpcs:ignore WordPress.DB
			$result = $wpdb->insert(
				$wpdb->base_prefix . self::TABLE,
				$data
			);

		}

		/**
		 * Cleans up all events older than thirty days.
		 *
		 * @global wpdb $wpdb
		 * @return void
		 */
		public static function clean_up_event_log(): void {
			global $wpdb;

			$query = $wpdb->prepare(
				"DELETE FROM {$wpdb->base_prefix}rsssl_event_logs WHERE timestamp < (UNIX_TIMESTAMP() - %d)",
				30 * 24 * 60 * 60
			);

			// Execute the query.
			// phpcs:ignore WordPress.DB
			$wpdb->query( $query );
		}

		/**
		 * Retrieves event logs from the database.
		 *
		 * This function handles the retrieval of event logs based on the provided data.
		 * It creates a data table instance with the specified query builder and returns the results
		 * after validating and applying search, sorting, filtering, and pagination.
		 *
		 * @param  array|null $data  Optional. An associative array containing the parameters for filtering, sorting, etc.
		 *                        - search: The search query.
		 *                        - sort: The sorting criteria.
		 *                        - filter: The filtering conditions.
		 *                        - pagination: The pagination parameters.
		 *
		 * @return array           The resulting event logs and additional information such as post data.
		 * @throws Exception      If any error occurs during the operation.
		 */
		public function get_events( array $data = null ): array {
            // validating if the table exists.
            if (!self::check_table_exists()) {
                self::create_event_log_table();
            }
			global $wpdb;
			try {
				$timezone = $this->get_wordpress_timezone()->getName();

				// Manipulate the ['sortColumn']['column'] to sort by timestamp instead of datetime.
				if ( isset( $data['sortColumn']['column'] ) && 'datetime' === $data['sortColumn']['column'] ) {
					$data['sortColumn']['column'] = 'timestamp';
				}

				$data_table = new Rsssl_Data_Table( $data, new Rsssl_Query_Builder( $wpdb->base_prefix . self::TABLE ) );
				$data_table->set_select_columns(
					array(
						'id',
						'username',
						'description',
						'event_type',
						'source_ip',
						'timestamp',
						'event_name',
						'severity',
						"raw:JSON_UNQUOTE(JSON_EXTRACT(event_data, '$.iso2_code')) as iso2_code",
						"raw:JSON_UNQUOTE(JSON_EXTRACT(event_data, '$.country_name')) as country_name",
						"raw:DATE_FORMAT(FROM_UNIXTIME(timestamp), '%%H:%%i, %%M %%e') as datetime",
					)
				);

				// extract the event type from the data.
                if (! isset($data['event_type'])) {
                    $data['event_type'] = 'login-protection';
                }
				$event_type = $data['event_type'];
				unset( $data['event_type'] );
				$data_table->set_where(
					array(
						'event_type', '=', $event_type,
					)
				);

				$result = $data_table
					->validate_search()
					->validate_sorting(
						array(
							'column'    => 'timestamp',
							'direction' => 'desc',
						)
					)
					->validate_filter()
					->validate_pagination()
					->get_results();

				// Now we remove all sorting.
				if ( isset( $data['sortColumn']['column'] ) && 'timestamp' === $data['sortColumn']['column'] ) {
					$data['sortColumn']['column'] = 'datetime';
				}
				$result['post'] = $data;
				// We loop through the results and convert the datetime to the timezone of the user.
				foreach ( $result['data'] as $key => $value ) {
					// Ensure $timezone is valid before attempting conversion.
					if ( is_string( $value->datetime ) ) {
						$result['data'][ $key ]->datetime = $this->convert_time_format( $value->datetime );
					}
				}

				return $result;
			} catch ( Exception $e ) {
				error_log( esc_html( $e->getMessage() ) );
			}
		}

		/**
		 * Retrieves event logs from the database.
		 *
		 * @throws Exception If an error occurs during the operation.
		 */
		public function get_wordpress_timezone(): DateTimeZone {
			$timezone_string = get_option( 'timezone_string' );

			if ( ! empty( $timezone_string ) ) {
				return new DateTimeZone( $timezone_string );
			}

			$offset  = get_option( 'gmt_offset' );
			$hours   = (int) $offset;
			$minutes = ( $offset - $hours ) * 60;

			$offset_string = sprintf( '%+03d:%02d', $hours, $minutes );
			return new DateTimeZone( $offset_string );
		}

		/**
		 * Converts the datetime to the timezone of the user.
		 *
		 * @param string $datetime The datetime to convert.
		 *
		 * @return string The converted datetime.
		 * @throws Exception If an error occurs during the operation.
		 */
		private function convert_time_format( string $datetime ): string {
			$time_zone = wp_timezone_string();
			$datetime = new DateTime( $datetime );
			$datetime->setTimezone( new DateTimeZone( $time_zone ) );
			return $datetime->format('H:i, M j');
		}

		/**
		 * Retrieves event logs from the database.
		 *
		 * @param string $get_ip_address The IP address to search for.
		 * @param string $code           The event code to search for.
		 *
		 * @return array|object|stdClass[]|null
		 */
		public function get_event_log( string $get_ip_address, string $code ) {
			global $wpdb;
			$ip_address = $get_ip_address;
			$ip_address = filter_var( $ip_address, FILTER_VALIDATE_IP );
			$ip_address = sanitize_text_field( $ip_address );
			$ip_address = $wpdb->esc_like( $ip_address );

			// phpcs:ignore WordPress.DB
			return $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->base_prefix}rsssl_event_logs WHERE source_ip = %s AND event_id = %s",
					$ip_address,
					$code
				)
			);
		}
	}

	new Rsssl_Event_Log();
}

if ( rsssl_get_option( 'event_log_enabled' ) ) {
	if ( ! function_exists( 'RSSSL\Pro\Security\WordPress\rsssl_event_log_api' ) ) {
		/**
		 * This function is used to log events to the database.
		 *
		 * @param array  $response The response to be returned.
		 * @param string $action   The action to be performed.
		 * @param array  $data     The data to be used for the event log.
		 *
		 * @return array
		 * @throws Exception If an error occurs during the operation.
		 */
		function rsssl_event_log_api( array $response, string $action, array $data ): array {
			if ( ! rsssl_admin_logged_in() ) {
				return $response;
			}
			if ( 'event_log' === $action ) {
				// creating a random string based on time.
				$response = ( new rsssl_event_log() )->get_events( $data );
			}
			return $response;
		}

		// Add the rsssl_event_log_api function as a filter callback.
		add_filter( 'rsssl_do_action', 'RSSSL\Pro\Security\wordpress\rsssl_event_log_api', 10, 3 );
	}
}

// Add the cleanup function to the hook.
add_action(
	'REALLY_SIMPLE_SSL_every_day_hook',
	array( 'RSSSL\Pro\Security\wordpress\rsssl_event_log', 'clean_up_event_log' )
);
