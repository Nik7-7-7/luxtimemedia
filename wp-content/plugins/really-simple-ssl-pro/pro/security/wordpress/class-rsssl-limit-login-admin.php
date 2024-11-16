<?php
/**
 * Class Rsssl_Limit_Login_Admin
 *
 * The Rsssl_Limit_Login_Admin class is responsible for managing login attempts and providing functionality to limit login attempts.
 *
 * @package RSSSL_PRO\Security\WordPress
 * @since 7.3.0
 * @category Class
 * @company Really Simple Plugins
 * @author Really Simple Plugins
 */
namespace RSSSL\Pro\Security\WordPress;
require_once rsssl_path . '/lib/admin/class-helper.php';
use RSSSL\lib\admin\Helper;

require_once rsssl_path . 'pro/security/wordpress/traits/trait-rsssl-api-toolbox.php';
require_once rsssl_path . 'pro/security/wordpress/traits/trait-rsssl-country.php';


use Exception;
use RSSSL\Pro\Security\WordPress\Eventlog\Rsssl_Event_Type;
use RSSSL\Pro\Security\WordPress\Limitlogin\Rsssl_Geo_Location;
use RSSSL\Pro\Security\WordPress\Limitlogin\Rsssl_Login_Data_Manager;
use RSSSL\Pro\Security\WordPress\Traits\Rsssl_Api_Toolbox;
use RSSSL\Pro\Security\WordPress\Traits\Rsssl_Country;
use wpdb;

/**
 * Class Rsssl_Limit_Login_Admin
 *
 * The Rsssl_Limit_Login_Admin class is responsible for managing login attempts and providing functionality to limit login attempts.
 */
class Rsssl_Limit_Login_Admin {
	use Helper;
	use Rsssl_Api_Toolbox;
	use Rsssl_Country;

	public const EVENT_CODE_USER_BLOCKED = '1012';
	public const EVENT_CODE_USER_UNBLOCKED = '1013';
	public const EVENT_CODE_IP_BLOCKED = '1022';
	public const EVENT_CODE_IP_UNBLOCKED = '1023';

	public const EVENT_CODE_IP_ADDED_TO_ALLOWLIST = '1024';
	public const EVENT_CODE_IP_REMOVED_FROM_ALLOWLIST = '1025';
	public const EVENT_CODE_USER_ADDED_TO_ALLOWLIST = '1014';
	public const EVENT_CODE_USER_REMOVED_FROM_ALLOWLIST = '1015';
	public const EVENT_CODE_IP_UNLOCKED = '1021';
	public const EVENT_CODE_USER_LOCKED = '1010';
	public const EVENT_CODE_USER_UNLOCKED = '1011';
	public const EVENT_CODE_IP_LOCKED = '1020';
	public const EVENT_CODE_IP_UNLOCKED_BY_ADMIN = '1021';
	public const EVENT_CODE_COUNTRY_BLOCKED = '1026';
	public const EVENT_CODE_COUNTRY_UNBLOCKED = '1027';
	public const TABLE = 'rsssl_login_attempts';

	/**
	 * The login data manager object.
	 *
	 * @var Rsssl_Login_Data_Manager $login_data_manager
	 */
	private Rsssl_Login_Data_Manager $login_data_manager;


	/**
	 * Create a new instance of the class.
	 *
	 * Initializes the object by adding a filter to the 'rsssl_do_action' hook,
	 * which registers the 'register_limit_login_api' method as a callback function with a priority of 10 and 3 arguments.
	 */
	public function __construct() {

		add_action( 'rsssl_after_save_field', array( $this, 'save_field_handler' ), 10, 4 );
		add_action( 'rsssl_install_tables', array( $this, 'create_login_attempts_table' ) );
		add_filter( 'rsssl_do_action', array( $this, 'register_limit_login_api' ), 10, 3 );

		try {
			$this->login_data_manager = new Rsssl_Login_Data_Manager();
		} catch ( Exception $e ) {
			$this->json_response( false, $e->getMessage() );
		}
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
	 */
	public function save_field_handler( string $field_id, $field_value, $prev_value, string $field_type ): void {
		// Add your condition based on field_id, field_value, etc.
		if ( 'enable_limited_login_attempts' === $field_id &&
		     true === (bool) $field_value ) {
			rsssl_update_option( 'event_log_enabled', true);
			$this->install_dependencies();
		}
	}

	/**
	 * Checks if the specified table exists in the database.
	 *
	 * @return bool True if the table exists, false otherwise.
	 * @global object $wpdb The WordPress database object.
	 *
	 */
	public static function check_if_table_exists(): bool {
		global $wpdb;
		$table_name = $wpdb->base_prefix . self::TABLE;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$tables = $wpdb->get_col( 'SHOW TABLES', 0 );
		if ( in_array( $table_name, $tables, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * We create the login attempts table.
	 *
	 * @return void
	 */
	public static function create_login_attempts_table(): void {
			// Create the table.
			global $wpdb;
			$charset_collate = $wpdb->get_charset_collate();
			$table           = $wpdb->base_prefix . self::TABLE;

			$sql = "CREATE TABLE $table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            first_failed bigint NOT NULL,
            last_failed bigint NOT NULL,
            attempt_type TEXT NOT NULL,
            attempt_value TEXT NOT NULL,
            user_agent TEXT NULL,
            status TEXT NULL,
            attempts int NULL,
            endpoint TEXT NULL,
            blocked tinyint(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (id),
             UNIQUE KEY idx_attempt_type_value (attempt_type(25), attempt_value(150)),
		    INDEX idx_attempt_type (attempt_type(25)),
		    INDEX idx_attempt_value (attempt_value(150))
        ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Handles the entity data and performs necessary operations.
	 *
	 * This function handles the entity data by sanitizing the input fields, checking if the entry already exists,
	 * deleting the existing entry if it exists, inserting a new entry into the database, fetching the record, logging
	 * the event data, and invalidating the cache based on the entity type.
	 *
	 * @param  array  $data  The entity data.
	 * @param  string  $type  The entity type ('ip' or 'username'). Default is 'ip'.
	 *
	 * @return array  The JSON response with success status and message.
	 * @throws Exception When an error occurs while handling the entity data.
	 */
	public function handle_entity( array $data, string $type = 'source_ip' ): array {
		global $wpdb;
		$entry_value  = $this->sanitize_text_field( $data, 'value', $type );
		$entry_status = $this->sanitize_text_field( $data, 'status', $type );
		$entry_exists = $this->check_entry( $wpdb, $type, $entry_value );

		if ( $entry_exists > 0 ) {
			$this->delete_existing_entry( $wpdb, $type, $entry_value );
		}

		try {
			if ( 'region' === $type ) {
				// we get all countries and then we add them to the allowlist.
				$countries = $this->continent_to_iso2country( $entry_value );
				foreach ( $countries as $country ) {
					// we check if the country already exists. if not we add it.
					$country_exists = $this->check_entry( $wpdb, 'country', $country );
					if ( $country_exists > 0 ) {
						$this->delete_existing_entry( $wpdb, 'country', $country );
					}
					$this->insert_data_into_database( $wpdb, $country, 'country', $entry_status );
				}
			} else {
				$this->insert_data_into_database( $wpdb, $entry_value, $type, $entry_status );
			}
		} catch ( Exception $e ) {
			return $this->json_response(
				false,
				$e->getMessage()
			);
		}

		if ( 'source_ip' === $type ) {
			wp_cache_delete( 'rsssl_allowlist_ranges' );
			wp_cache_delete( 'rsssl_allowlist_ips' );
		}
		if ( 'username' === $type ) {
			wp_cache_delete( 'rsssl_allowlist_users' );
			wp_cache_delete( 'rsssl_blocklist_users' );
		}

		if ( 'country' === $type || 'region' === $type ) {
			wp_cache_delete( 'rsssl_allowlist_countries' );
		}

		if ( 'region' === $type ) {
			$entry_value = $this->get_region_name( $entry_value );
		}

		if ( 'country' === $type ) {
			$entry_value = $this->get_country_name( $entry_value );
		}

		return $this->json_response(
			true,
			sprintf(
			// :translation
				__(
					'%1s %2$s added to %3$s.',
					'really-simple-ssl'
				),
				ucfirst( $type ),
				$entry_value,
				$entry_status
			)
		);
	}

	public function handle_entities( array $data, string $type = 'source_ip' ): array {
		global $wpdb;
		$entry_status = $this->sanitize_text_field( $data, 'status', $type );
		// we loop through the datavalues and santize it.
		foreach ( $data['value'] as $key => $entry ) {
			$newData               = $data;
			$newData['value']      = $entry;
			$entry_value           = $this->sanitize_text_field( $data, 'value', $type );

			$data['value'][ $key ] = $newData;
			unset( $newData );
		}
		$errors = array();
		foreach ( $data['value'] as $value_loop ) {
			$entry_exists = $this->check_entry( $wpdb, $type, $value_loop['value'] );
			if ( $entry_exists > 0 ) {
				$this->delete_existing_entry( $wpdb, $type, $value_loop['value'] );
			}
			try {
				if ( 'region' === $type ) {
					// we get all countries and then we add them to the allowlist.
					$countries = $this->continent_to_iso2country( $value_loop["value"] );
					foreach ( $countries as $country ) {
						// we check if the country already exists. if not we add it.
						$country_exists = $this->check_entry( $wpdb, 'country', $country );
						if ( $country_exists > 0 ) {
							$this->delete_existing_entry( $wpdb, 'country', $country );
						}
						$this->insert_data_into_database( $wpdb, $country, 'country', $entry_status );
					}
				} else {
					$this->insert_data_into_database( $wpdb, $value_loop['value'], $type, $entry_status );
				}
			} catch ( Exception $e ) {
				$errors[ $value_loop ] = $e->getMessage();
			}
		}

		if ( 'source_ip' === $type ) {
			wp_cache_delete( 'rsssl_allowlist_ranges' );
			wp_cache_delete( 'rsssl_allowlist_ips' );
		}
		if ( 'username' === $type ) {
			wp_cache_delete( 'rsssl_allowlist_users' );
			wp_cache_delete( 'rsssl_blocklist_users' );
		}

		if ( 'country' === $type || 'region' === $type ) {
			wp_cache_delete( 'rsssl_allowlist_countries' );
		}

		//now we check the number of errors vs the number of value.
		if ( count( $errors ) > 0 ) {
			if ( count( $errors ) === count( $data['value'] ) ) {
				// all entries had issues we let the client know
				return $this->json_response( false,
					sprintf
					( __( 'No %1s were added to %2s', 'really-simple-ssl' ),
						$type,
						$entry_status
					)
				);
			}

			// We list the countries that were not imported and why.
			return $this->json_response( false,
				sprintf
				( __( 'Some %1s were added to %2s, missing %1s are %3s', 'really-simple-ssl' ),
					$type,
					$entry_status,
					implode( ', ', array_keys( $errors ) )
				)
			);
		}

        $plural_type = $type;
        if ($type === 'country') {
            $plural_type = 'countries';
        } else {
            $plural_type .= 's';
        }

		return $this->json_response( true,
			sprintf
			( __( 'All %1s were added to %2s', 'really-simple-ssl' ),
                $plural_type,
				$entry_status
			)
		);

	}

	/**
	 * Inserts data into the specified database table.
	 *
	 * This function inserts the provided entry value, type, entry status, and current timestamp into the specified
	 * table in the database using prepared statements. It then retrieves the inserted record and logs the event data.
	 *
	 * @param  \wpdb  $wpdb  The WordPress database class instance.
	 * @param  mixed  $entry_value  The value to insert into the 'attempt_value' column.
	 * @param  string  $type  The value to insert into the 'attempt_type' column.
	 * @param  string  $entry_status  The value to insert into the 'status' column.
	 *
	 * @return void
	 * @throws Exception If an error occurs while inserting the data into the database.
	 */
	public function insert_data_into_database( $wpdb, $entry_value, string $type, string $entry_status ) {
		// phpcs:ignore WordPress.DB -- We are inserting data into the database and we do prepare.
		$table_name     = $wpdb->base_prefix . self::TABLE;
		$prepared_query = $wpdb->prepare(
		// phpcs:ignore WordPress.DB -- We are inserting data into the database and we do prepare.
			"INSERT INTO $table_name (attempt_value, attempt_type, status, last_failed) VALUES (%s, %s, %s, %s)",
			$entry_value,
			$type,
			$entry_status,
			time()
		);
		// Execute the query.
		// phpcs:ignore WordPress.DB -- We are inserting data into the database and we do prepare.
		$wpdb->query( $prepared_query );

		// fetching the last id.
		$last_id = $wpdb->insert_id;

		// now we fetch the record.
		$query = $wpdb->prepare(
			"SELECT * FROM {$wpdb->base_prefix}rsssl_login_attempts WHERE id = %d",
			$last_id
		);
		// phpcs:ignore WordPress.DB
		$result = $wpdb->get_row( $query, ARRAY_A );
		$this->log_event_data( $result );
	}

	/**
	 * Sanitizes a text field value in the given data array based on the specified key and type.
	 *
	 * This function is used to sanitize a specific text field value in the provided data array based on the given key
	 * and type. It returns the sanitized value as a string.
	 *
	 * @param  array  $data  The data array containing the field value.
	 * @param  string  $key  The key of the field in the data array.
	 * @param  string  $type  The type of the field ('username' or 'ip').
	 *
	 * @return string  The sanitized text field value.
	 */
	private function sanitize_text_field( array $data, string $key, string $type ): string {
		// based on the type we either sanitize the user or the ip.
		if ( 'username' === $type ) {
			return array_key_exists( $key, $data ) ? sanitize_text_field( $data[ $key ] ) : '';
		}

		// ig the type is an ip we sanitize the ip for ipv4 as well as ipv6.
		if ( 'source_ip' === $type ) {
			$ip = array_key_exists( $key, $data ) ? sanitize_text_field( $data[ $key ] ) : '';
			if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) || filter_var( $ip, FILTER_VALIDATE_IP,
					FILTER_FLAG_IPV6 ) ) {
				return $ip;
			}
		}

		return array_key_exists( $key, $data ) ? sanitize_text_field( $data[ $key ] ) : '';
	}

	/**
	 * Checks if an entry exists in the specified table and column.
	 *
	 * This function checks if an entry exists in the specified WordPress database table and column.
	 * It sanitizes the entry value if the type is 'username'.
	 *
	 * @param  wpdb  $wpdb  The WordPress database object.
	 * @param  string  $type  The type of entry ('username' or 'ip').
	 * @param  string  $entry_value  The value of the entry.
	 *
	 * @return int|null            The number of matching entries or null if an error occurred.
	 */
	private function check_entry( wpdb $wpdb, string $type, string $entry_value ): ?int {
		$entry_value = $this->sanitize_text_field( array( 'value' => $entry_value ), 'value', $type );

		//phpcs:ignore WordPress.DB
		return $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->base_prefix}rsssl_login_attempts WHERE attempt_value = %s",
				$entry_value
			)
		);
	}

	/**
	 * Deletes the existing entry in the specified table for the given entry value.
	 *
	 * This method deletes the entry from the database table 'rsssl_login_attempts' that matches the provided entry value.
	 *
	 * @param  wpdb  $wpdb  The global WordPress database class instance.
	 * @param  string  $type  The type of entry ('value' or 'range').
	 * @param  string  $entry_value  The value of the entry to be deleted.
	 *
	 * @return void
	 */
	private function delete_existing_entry( wpdb $wpdb, string $type, string $entry_value ): void {
		$entry_value = $this->sanitize_text_field( array( 'value' => $entry_value ), 'value', $type );
		// phpcs:ignore WordPress.DB
		$wpdb->delete( $wpdb->base_prefix . 'rsssl_login_attempts', array( 'attempt_value' => $entry_value ) );
	}


	/**
	 * Deletes entries from the specified table based on the provided data and region.
	 *
	 * This function deletes entries from the login attempts table based on the provided data and region.
	 * If a region is provided, it first converts the region into corresponding country ISO codes, and then fetches the IDs
	 * for the countries.
	 * It then loops through the IDs and deletes the corresponding records from the login attempts table.
	 * Finally, it clears the cache for various keys related to the allowlist, blocklist, users, and countries.
	 *
	 * @param  array  $data  The data to be used for deleting entries.
	 * @param  bool  $region  The region value for filtering the entries (optional).
	 *
	 * @return array  The JSON response indicating the success or failure of the deletions.
	 *
	 * @throws Exception If an error occurs while deleting the entries.
	 */
	public function delete_entries( array $data, bool $region = false ): array {
		global $wpdb;
		if ( ! $region ) {
			// Ensure that $data['id'] is always an array.
			if ( isset( $data['id'] ) ) {
				$data['ids'] = array( $data['id'] );
			}
			$ids = $data['ids'];
		}

		if ( $region ) {
			$countries = $this->continent_to_iso2country( $data['value'] );
			// we get all the countries and then we look up the ids for the countries.
			$ids = array();
			foreach ( $countries as $country ) {
				//phpcs:ignore WordPress.DB
				$ids[] = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT id FROM {$wpdb->base_prefix}rsssl_login_attempts WHERE attempt_value = %s AND attempt_type = 'country'",
						$country
					)
				);
			}
			// we remove the empty values.
			$ids = array_filter( $ids );
		}

		if ( empty( $ids ) ) {
			return $this->json_response( true, __( 'Records deleted successfully.', 'really-simple-ssl' ) );
		}

		// Loop through the IDs.
		foreach ( $ids as $id ) {
			// Safely prepare the SQL statement for fetching the record.
			$table          = $wpdb->base_prefix . self::TABLE;
			$prepared_query = $wpdb->prepare(
			//phpcs:ignore WordPress.DB
				"SELECT id, status, attempt_type, attempt_value FROM {$table} WHERE id = %d",
				(int) $id
			);
			// phpcs:ignore WordPress.DB
			$result = $wpdb->get_row( $prepared_query, ARRAY_A );
			// Check if record exists.
			if ( ! $result ) {
				return $this->json_response( false, __( 'Record not found.', 'really-simple-ssl' ) );
			}
			// Safely delete the record.
			// phpcs:ignore WordPress.DB
			$wpdb->delete( $wpdb->base_prefix . 'rsssl_login_attempts', array( 'id' => $id ), array( '%d' ) );
			// Log the event based on record status.
			$this->log_event_data( $result, true );
		}

		// Clear cache once after all deletions.
		wp_cache_delete( 'rsssl_allowlist_ranges' );
		wp_cache_delete( 'rsssl_allowlist_ips' );
		wp_cache_delete( 'rsssl_blocklist_ranges' );
		wp_cache_delete( 'rsssl_blocklist_ips' );
		wp_cache_delete( 'rsssl_allowlist_users' );
		wp_cache_delete( 'rsssl_blocklist_users' );
		wp_cache_delete( 'rsssl_allowlist_countries' );

		return $this->json_response( true, __( 'Records deleted successfully.', 'really-simple-ssl' ) );
	}

	/**
	 * Checks if the action requires limiting login and returns the updated do_action value based on the action.
	 *
	 * This method checks if the given action is 'rsssl_limit_login'. If it is, it sets the do_action value to true and calls 'get_list' method to retrieve the list of data.
	 * If the given action is 'rsssl_limit_login_user', it also sets the do_action value to true and calls 'get_user_list' method to retrieve the list of user data.
	 * For any other action, the do_action value remains unchanged.
	 *
	 * @param  array  $response  The flag indicating if the action should be limited.
	 * @param  string  $action  The action being performed.
	 * @param  mixed  $data  The data associated with the action.
	 *
	 * @return array The updated do_action value.
	 * @throws Exception If an error occurs during the retrieval process.
	 */
	public function register_limit_login_api( array $response, string $action, $data ): array {
		// if the option is not enabled, we return the response.
		if ( ! rsssl_admin_logged_in() ) {
			return $response;
		}
		if ( 'rsssl_limit_login' === $action ) {
			$response = $this->login_data_manager->get_list( $data );
		}

		if ( 'rsssl_limit_login_user' === $action ) {
			$response = $this->login_data_manager->get_user_list( $data );
		}

		if ( 'user_update_row' === $action ) {
			$response = $this->handle_entity( $data, 'username' );
		}

		if ( 'ip_update_row' === $action ) {
			$response = $this->handle_entity( $data );
		}

		if ( 'country_update_row' === $action ) {
			if (is_array($data['value'])) {
				$response = $this->handle_entities( $data, 'country' );
			} else {
				$response = $this->handle_entity( $data, 'country' );
			}
		}

		if ( 'region_update_row' === $action ) {
			if (is_array($data['value'])) {
				$response = $this->handle_entities( $data, 'region' );
			} else {
				$response = $this->handle_entity( $data, 'region' );
			}
		}

		if ( 'delete_entries_regions' === $action ) {
			$response = $this->delete_entries( $data, true );
		}

		if ( 'delete_entries' === $action ) {
			$response = $this->delete_entries( $data );
		}

		return $response;
	}

	/**
	 * Logs events for the given records.
	 *
	 * @param  array  $data  The data to log.
	 * @param  bool  $deleted  Whether the data was deleted or not.
	 *
	 * @return void
	 * @throws Exception When an error occurs while logging the data.
	 */
	private function log_event_data( array $data, bool $deleted = false ): void {
		// If data is not an array, convert it to an array.
		$original = $data;
		if ( ! is_array( $data ) || ( is_object( $original ) && $data === (array) $original ) ) {
			$data = array( $data );
		}

		if ( ! isset( $data[0] ) && isset( $data['id'] ) ) {
			$data = array( $data );
		}

		foreach ( $data as $record ) {
			if ( is_array( $record ) ) {
				$status        = $record['status'] ?? null;
				$attempt_type  = $record['attempt_type'] ?? null;
				$attempt_value = $record['attempt_value'] ?? null;
			} elseif ( is_object( $record ) ) {
				$status        = $record->status ?? null;
				$attempt_type  = $record->attempt_type ?? null;
				$attempt_value = $record->attempt_value ?? null;
			} else {
				// If it's neither an array nor an object, skip this iteration.
				continue;
			}

			switch ( array( $status, $attempt_type ) ) {
				case array( 'blocked', 'source_ip' ):
					$event_type = self::EVENT_CODE_IP_BLOCKED;
					if ( $deleted ) {
						$event_type = self::EVENT_CODE_IP_UNBLOCKED;
					}
					$event_type = Rsssl_Event_Type::add_to_block( $event_type, $attempt_value );
					break;
				case array( 'allowed', 'source_ip' ):
					$event_type = self::EVENT_CODE_IP_ADDED_TO_ALLOWLIST;
					if ( $deleted ) {
						$event_type = self::EVENT_CODE_IP_REMOVED_FROM_ALLOWLIST;
					}
					$event_type = Rsssl_Event_Type::add_to_block( $event_type, $attempt_value );
					break;
				case array( 'blocked', 'username' ):
					$event_type = self::EVENT_CODE_USER_BLOCKED;
					if ( $deleted ) {
						$event_type = self::EVENT_CODE_USER_UNBLOCKED;
					}
					$event_type = Rsssl_Event_Type::add_to_block( $event_type, '', $attempt_value );
					break;
				case array( 'allowed', 'username' ):
					$event_type = self::EVENT_CODE_USER_ADDED_TO_ALLOWLIST;
					if ( $deleted ) {
						$event_type = self::EVENT_CODE_USER_REMOVED_FROM_ALLOWLIST;
					}
					$event_type = Rsssl_Event_Type::add_to_block( $event_type, '', $attempt_value );
					break;
				case array( 'locked', 'username' ):
					$event_type = self::EVENT_CODE_USER_LOCKED;
					if ( $deleted ) {
						$event_type = self::EVENT_CODE_USER_UNLOCKED;
					}
					$event_type = Rsssl_Event_Type::add_to_block( $event_type, '', $attempt_value );
					break;
				case array( 'locked', 'source_ip' ):
					$event_type = self::EVENT_CODE_IP_LOCKED;
					if ( $deleted ) {
						$event_type = self::EVENT_CODE_IP_UNLOCKED;
					}
					$event_type = Rsssl_Event_Type::add_to_block( $event_type, $attempt_value );
					break;
				case array( 'blocked', 'country' ):
					$event_type = self::EVENT_CODE_COUNTRY_BLOCKED;
					if ( $deleted ) {
						$event_type = self::EVENT_CODE_COUNTRY_UNBLOCKED;
					}
					$event_type = Rsssl_Event_Type::add_to_block( $event_type, '', '', $attempt_value );
					break;
				default:
					// No event to log for this record.
					continue 2;
			}

			Rsssl_Event_Log::log_event( $event_type );
		}
	}

	/**
	 * Invalidates the cache for the specified table and IP address.
	 *
	 * This function clears the cache for the allowlist or blocklist based on the provided table and IP address.
	 * If the IP address is a range, it clears the cache for the corresponding range cache key. Otherwise, it clears
	 * the cache for the corresponding IP cache key.
	 *
	 * @param  string  $table  The table name ('rsssl_allowlist' or 'rsssl_blocklist').
	 * @param  string  $ip  The IP address or range.
	 *
	 * @return void
	 */
	public function invalidate_cache( string $table, string $ip ): void {

		if ( 'rsssl_allowlist' === $table ) {
			// Check if range or IP.
			if ( strpos( $ip, '/' ) !== false ) {
				wp_cache_delete( 'rsssl_allowlist_ranges' );
			} else {
				wp_cache_delete( 'rsssl_allowlist_ips' );
			}
		}

		if ( 'rsssl_blocklist' === $table ) {
			if ( strpos( $ip, '/' ) !== false ) {
				wp_cache_delete( 'rsssl_blocklist_ranges' );
			} else {
				wp_cache_delete( 'rsssl_blocklist_ips' );
			}
		}
	}

	/**
	 * Installs the dependencies required for the plugin.
	 *
	 * This method installs the necessary dependencies for the plugin by creating the login attempts table
	 * and validating the geo IP database. If the geo IP database is not valid, it retrieves the necessary
	 * file.
	 *
	 * @return void
	 */
	private function install_dependencies(): void {
		self::create_login_attempts_table();
		$geo_location = new Rsssl_Geo_Location();
		if ( ! $geo_location->validate_geo_ip_database() ) {
			try {
				$geo_location->get_geo_ip_database_file();
			} catch ( Exception $e ) {
				$this->log( $e->getMessage() );
			}
		}
	}
}

new Rsssl_Limit_Login_Admin();
