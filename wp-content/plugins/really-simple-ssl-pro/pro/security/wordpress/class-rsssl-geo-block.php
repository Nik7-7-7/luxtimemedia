<?php
/**
 * This function is used to remove blocked countries from the blocked list.
 *
 * @category Security
 * @author   Marcel Santing <marcel@really-simple-plugins.com>
 * @package Really-Simple-SSL
 */

namespace RSSSL\Pro\Security\WordPress;

require_once __DIR__ . '/traits/trait-rsssl-api-toolbox.php';
require_once __DIR__ . '/traits/trait-rsssl-country.php';
require_once __DIR__ . '/contracts/interface-rsssl-country-contract.php';

use DateTime;
use DateTimeZone;
use Exception;
use RSSSL\Pro\Security\DynamicTables\Rsssl_Array_Query_Builder;
use RSSSL\Pro\Security\DynamicTables\Rsssl_Data_Table;
use RSSSL\Pro\Security\DynamicTables\Rsssl_Query_Builder;
use RSSSL\Pro\Security\WordPress\Contracts\Rsssl_Country_Contract;
use RSSSL\Pro\Security\WordPress\EventLog\Events\Rsssl_Country_Blocked;
use RSSSL\Pro\Security\WordPress\EventLog\Events\Rsssl_Ip_Blocked_Permanent;
use RSSSL\Pro\Security\WordPress\EventLog\Events\Rsssl_Ip_Removed_From_Blocklist;
use RSSSL\Pro\Security\WordPress\EventLog\Events\Rsssl_Ip_Removed_From_Whitelist;
use RSSSL\Pro\Security\WordPress\EventLog\Events\Rsssl_Ip_Trusted_Event;
use RSSSL\Pro\Security\WordPress\EventLog\Events\Rsssl_Region_Blocked;
use RSSSL\Pro\Security\WordPress\Firewall\Models\Rsssl_404_Block;
use RSSSL\Pro\Security\WordPress\Firewall\Rsssl_404_Interceptor;
use RSSSL\Pro\Security\WordPress\Firewall\Models\Rsssl_User_Agent_Block;
use RSSSL\Pro\Security\WordPress\Limitlogin\Rsssl_Country_Detection;
use RSSSL\Pro\Security\WordPress\Limitlogin\Rsssl_Geo_Location;
use RSSSL\Pro\Security\WordPress\Limitlogin\Rsssl_IP_Fetcher;
use RSSSL\Pro\Security\WordPress\Traits\Rsssl_Api_Toolbox;
use RSSSL\Pro\Security\WordPress\Traits\Rsssl_Country;
use RSSSL\Pro\Security\WordPress\EventLog\Events\Rsssl_Country_Allowed;
use RSSSL\Pro\Security\WordPress\EventLog\Events\Rsssl_Region_Allowed;
use RuntimeException;

if ( rsssl_is_in_deactivation_list('class-rsssl-geo-block') ){
	rsssl_remove_from_deactivation_list('class-rsssl-geo-block');
}
/**
 * Rsssl_Geo_Block class.
 *
 * This class provides functionality for blocking countries based on their geolocation.
 *
 * @since 1.0.0
 */
class Rsssl_Geo_Block implements Rsssl_Country_Contract {


	use Rsssl_Api_Toolbox;
	use Rsssl_Country;

	public const LIST_TYPES = array(
		'country',
		'block',
		'regions',
		'white_list',
		'block_list',
        'user_agent_list'
	);

    private $model;
    private $model_user_agent;

    /**
	 *  Rsssl_Geo_Block constructor.
	 *
	 * @throws Exception For database errors.
	 */
	public function __construct() {

        $this->model = new Rsssl_404_Block();
        $this->model_user_agent = new Rsssl_User_Agent_Block();
        add_action( 'rsssl_install_tables', array( $this, 'create_table' ) );
        add_action( 'rsssl_upgrade', array( $this, 'upgrade_geo_block' ) );
        add_action( 'rsssl_install_tables', array( Rsssl_Event_Log::class, 'create_event_log_table' ) );
		// we execute the hooks.
		$this->execute_hooks();
	}

	/**
	 * Handle upgrades
	 *
	 * @param $prev_version
	 *
	 * @return void
	 */
	public function upgrade_geo_block( $prev_version ): void {
		if ( $prev_version && version_compare($prev_version, '9.1.0', '<=') ) {
			$this->add_default_user_agents();
		}
	}

    /**
     * Deactivates the application.
     *
     *  - Disables the firewall.
     *  - Disables event log if limit login attempts is disabled.
     *  - Sets the event log threshold to disabled.
     *  - TODO: Determines if tables need to be deleted.
     */
    private static function deactivate(): void
    {
        // We disable all options within the configuration.
        rsssl_update_option('enable_firewall', false);
        // if the limit login attempts is disabled we disable the event log.
        if (!rsssl_get_option('enable_limited_login_attempts')) {
            rsssl_update_option('event_log_enabled', false);
        }
        // We set the threshold to disabled.
        rsssl_update_option('404_blocking_threshold', 'disabled');
        rsssl_update_option('404_blocking_captcha_trigger', false );
        // TODO: Determine if tables need to be deleted.
    }

    /**
	 * Creates the table for storing geo block data if it does not already exist.
	 *
	 * @return void
	 */
	public function create_table(): void {
		global $wpdb;
		$table_name      = $wpdb->base_prefix . 'rsssl_geo_block';
		$charset_collate = $wpdb->get_charset_collate();
		include_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$sql = "CREATE TABLE $table_name (
       id mediumint(9) NOT NULL AUTO_INCREMENT,
       iso2_code TEXT NOT NULL,
       country_name TEXT NULL,
       create_date TEXT NOT NULL,
       ip_address TEXT NULL,
       note TEXT NULL,
       data_type VARCHAR(20) DEFAULT 'country',
       attempt_count INT DEFAULT 1,
       last_attempt TEXT NULL,
       blocked BOOLEAN DEFAULT 0,
       permanent BOOLEAN DEFAULT 0,
       captcha INT DEFAULT 0,
       user_agent TEXT NULL,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       deleted_at TIMESTAMP NULL,
       PRIMARY KEY  (id),
       KEY idx_iso2_code (iso2_code(3)),
       KEY idx_data_type (data_type),
       KEY idx_ip_address (ip_address(15))
    ) $charset_collate;";
	dbDelta( $sql );

	}

	/**
	 * Removes the table containing all the countries that are blocked.
	 *
	 * @returns void
	 */
	public static function remove_table(): void {
		global $wpdb;
		$table_name = $wpdb->base_prefix . 'rsssl_geo_block';
		// phpcs:ignore
		$wpdb->query( "DROP TABLE IF EXISTS $table_name" );

		delete_option( 'rsssl_geo_block_db_version' );
	}

	/**
	 * Removed dependencies on disabled feature.
	 *
	 * @return void
	 */
	public static function down(): void {
		self::remove_table();
		Rsssl_Geo_Location::down();
		rsssl_remove_from_deactivation_list( 'class-rsssl-geo-block' );
	}

	/**
	 * Executes hooks for the geo-block functionality.
	 *
	 * This method adds and registers hooks for the geo-block functionality to be executed during various events.
	 *
	 * @throws Exception If the execution has an issue.
	 */
	public function execute_hooks(): void {
		add_action(
			'admin_init',
			static function () {
				if ( rsssl_is_in_deactivation_list( 'class-rsssl-geo-block' ) ) {
					do_action( 'rsssl_update_rules' );
                    self::down();
				}
			}
		);

		// First hook after the field firewall_enabled has changed an is enabled.
		add_action( 'rsssl_after_save_field', array( $this, 'save_field_handler' ), 10, 4 );
		add_filter( 'rsssl_do_action', array( $this, 'rsssl_geo_block_api' ), 10, 3 );
		add_filter( 'rsssl_firewall_rules', array( $this, 'generate_rules_for_headers' ), 40, 1 );
//		add_filter( 'rsssl_geo_block_initial_whitelist', array( $this, 'generate_initial_whitelist' ), 40, 1 );
        add_action('rsssl_install_dependencies_cron', array($this, 'install_dependencies'), 10, 1);
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
		if ( 'enable_firewall' === $field_id &&
		     true === (bool) $field_value ) {
            do_action('rsssl_install_tables');
            do_action('rsssl_update_rules');
			rsssl_update_option('event_log_enabled', true);
            $this->add_default_user_agents();
            // Schedule the cron event to run the install_dependencies function
            if (!wp_next_scheduled('rsssl_install_dependencies_cron')) {
	            $client_ip = (new Rsssl_IP_Fetcher())->get_ip_address()[0];
                wp_schedule_single_event(time() + 3, 'rsssl_install_dependencies_cron', array($client_ip));
            }
		}

		if ( 'enable_firewall' === $field_id &&
		     false === (bool) $field_value ) {
			do_action( 'rsssl_update_rules' );
            self::deactivate();
		}
	}

	/**
	 * Generate the initial whitelist based on the user's IP address.
	 *
	 * @return void
	 * @throws Exception If the whitelist generation fails.
	 */
	public function generate_initial_whitelist($ip_address): void {
		$geo_location =  new Rsssl_Geo_Location();
		//if maxmind is not installed, we schedule a retry.
		if ( $geo_location->country_detector === null ) {
			wp_schedule_single_event(120, 'rsssl_geo_block_initial_whitelist');
			return;
		}
		$country    = $geo_location->country_detector->get_country_by_ip( $ip_address );
		// We validate the ip address for ipv4 and ipv6.
		if ( false === filter_var( $ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 ) ) {
			$ip_address = null;
		}

		if ( ! empty( $ip_address ) ) {
			$this->add_trusted_ip( $ip_address, $country, __( 'Administrator IP', 'really-simple-ssl' ) );
		}
	}

	/**
	 * Installs the necessary dependencies for the application.
	 * - Creates the required database table.
	 * - Validates the GeoIP database.
	 *
	 * @param $client_ip
	 *
	 * @return void
	 * @throws Exception If the installation of the dependencies fails.
	 */
	public function install_dependencies($client_ip): void {
		$geo_location = new Rsssl_Geo_Location();
		if ( $geo_location->validate_geo_ip_database() ) {
			// we update the ruleset.
			do_action( 'rsssl_update_rules' );
		} else {
			$geo_location->get_geo_ip_database_file();
		}
        $this->generate_initial_whitelist($client_ip);
	}

	/**
	 * This function is used to add countries by region.
	 *
	 * @param  array  $data  Data that needs to be added.
	 *
	 * @return array
	 */
	public function add_blocked_region( array $data ): array {
		// Validate the provided data.
		if ( ! $this->validate_keys( $data, 'region_code' ) ) {
			return $this->json_response( false, __( 'Missing or invalid region data.', 'really-simple-ssl' ) );
		}

		// Validate the region code and get the ISO2 codes of the countries in the region.
		if ( ! array_key_exists( $data['region_code'], $this->get_continent_list() ) ) {
			return $this->json_response( false, __( 'Invalid region code.', 'really-simple-ssl' ) );
		}
		$country_iso2_codes = $this->continent_to_iso2country( $data['region_code'] );

		// Filter out countries that are already in the list.
		$country_iso2_codes = array_diff( $country_iso2_codes, $this->get_blocked_countries_list_to_array() );

		// Prepare the data for insertion into the database.
		$countries   = $this->get_country_list();
		$insert_data = array();
		foreach ( $country_iso2_codes as $iso2_code ) {
			$insert_data[] = array(
				'iso2_code'    => $iso2_code,
				'country_name' => $countries[ $iso2_code ],
                'create_date' => time(),
			);
		}

		// Insert the data into the database.
		$insertion_result = $this->insert_countries_into_database( $insert_data );
		if ( ! $insertion_result['success'] ) {
			return $this->json_response( false, $insertion_result['message'], null, $insertion_result['errors'] );
		}

		do_action( 'rsssl_update_rules' );
		$data['region_name'] = $this->get_region_name( $data['region_code'] );
		Rsssl_Region_Blocked::handle_event( $data );

		return $this->json_response(
			true,
			sprintf(
			// translators: %s: Name of the country that was removed from the blocked list.
				__( 'Access from all countries in %s is now blocked.', 'really-simple-ssl' ),
				$data['region_name']
			)
		);
	}

	/**
	 * This function is used to add countries by region.
	 *
	 * @param  array  $data  Data that needs to be added.
	 *
	 * @return array
	 */
	public function add_blocked_regions( array $data ): array {
		$errors = array();
		foreach ( $data['region_codes'] as $region ) {
			// Validate the provided data.
			if ( ! $this->validate_keys( $region, 'iso2_code' ) ) {
				$errors[ $region['iso2_code'] ] = __( 'Missing or invalid region data.', 'really-simple-ssl' );
			}

			// Validate the region code and get the ISO2 codes of the countries in the region.
			if ( ! array_key_exists( $region['iso2_code'], $this->get_continent_list() ) ) {
				$errors[ $region['iso2_code'] ] = __( 'Invalid region code.', 'really-simple-ssl' );
			}

			$country_iso2_codes = $this->continent_to_iso2country( $region['iso2_code'] );

			// Filter out countries that are already in the list.
			$country_iso2_codes = array_diff( $country_iso2_codes, $this->get_blocked_countries_list_to_array() );

			// Prepare the data for insertion into the database.
			$countries   = $this->get_country_list();
			$insert_data = array();
			foreach ( $country_iso2_codes as $iso2_code ) {
				$insert_data[] = array(
					'iso2_code'    => $iso2_code,
					'country_name' => $countries[ $iso2_code ],
				);
			}

			// Insert the data into the database.
			$insertion_result = $this->insert_countries_into_database( $insert_data );
			if ( ! $insertion_result['success'] ) {
				$errors[ $region['region_code'] ] = $insertion_result['message'];
			}
		}
		do_action( 'rsssl_update_rules' );

		foreach ( $data['region_codes'] as $region ) {
			$region['region_name'] = $this->get_region_name( $region['iso2_code'] );
			$region['region_code'] = $region['iso2_code'];
			Rsssl_Region_Blocked::handle_event( $region );
		}

		if ( empty( $errors ) ) {
			do_action( 'rsssl_update_rules' );

			return $this->json_response(
				true,
				__( 'Access from the selected regions is now blocked.', 'really-simple-ssl' )
			);
		}

		return $this->json_response(
			false,
			__( 'An error occurred while adding regions to the list.', 'really-simple-ssl' ),
			null,
			$errors
		);
	}

	/**
	 * This function is used to remove continents from the blocked list.
	 *
	 * @param  array  $data  data that needs to be removed.
	 *
	 * @return array
	 */
	public function remove_blocked_region( array $data ): array {
		// Validate the provided data.
		if ( ! $this->validate_keys( $data, 'region_code' ) ) {
			// Return an error with the message.
			return $this->json_response( false, __( 'Missing or invalid region data.', 'really-simple-ssl' ) );
		}

		// Check if the region code exists in the list of continents.
		if ( ! array_key_exists( $data['region_code'], $this->get_continent_list() ) ) {
			return $this->json_response( false, __( 'Invalid region code.', 'really-simple-ssl' ) );
		}
		// Get the ISO2 codes of the countries in the region.
		$country_iso2_codes = $this->continent_to_iso2country( $data['region_code'] );

		// Get only countries that are already in the list.
		$country_iso2_codes = array_intersect( $country_iso2_codes, $this->get_blocked_countries_list_to_array() );

		// Prepare the data for deletion from the database.
		$countries   = $this->get_country_list();
		$delete_data = array();
		foreach ( $country_iso2_codes as $iso2_code ) {
			$delete_data[] = array(
				'iso2_code'    => $iso2_code,
				'country_name' => $countries[ $iso2_code ],
			);
		}

		// Delete the data from the database.
		$deletion_result = $this->delete_countries_from_database( $delete_data );
		if ( ! $deletion_result['success'] ) {
			// Return an error with the message.
			return $this->json_response( false, $deletion_result['message'] );
		}

		do_action( 'rsssl_update_rules' );
		$data['region_name'] = $this->get_region_name( $data['region_code'] );
		Rsssl_Region_Allowed::handle_event( $data );
		// Return a success with the message.
		return $this->json_response(
			true,
			sprintf(
			// translators: %s: Name of the country that was removed from the blocked list.
				__( 'Access from all countries in %s is now allowed.', 'really-simple-ssl' ),
				$data['region_name']
			)
		);
	}

	/**
	 * This function is used to remove continents from the blocked list.
	 *
	 * @param  array  $data  data that needs to be removed.
	 *
	 * @return array
	 */
	public function remove_blocked_regions( array $data ): array {
		$errors = array();
		foreach ( $data['region_codes'] as $region ) {
			// Validate the provided data.
			if ( ! $this->validate_keys( $region, 'iso2_code' ) ) {
				$errors[ $region['iso2_code'] ] = __( 'Missing or invalid region data.', 'really-simple-ssl' );
			}

			// Check if the region code exists in the list of continents.
			if ( ! array_key_exists( $region['iso2_code'], $this->get_continent_list() ) ) {
				$errors[ $region['iso2_code'] ] = __( 'Invalid region code.', 'really-simple-ssl' );
			}
			// Get the ISO2 codes of the countries in the region.
			$country_iso2_codes = $this->continent_to_iso2country( $region['iso2_code'] );

			// Get only countries that are already in the list.
			$country_iso2_codes = array_intersect( $country_iso2_codes, $this->get_blocked_countries_list_to_array() );

			// Prepare the data for deletion from the database.
			$countries   = $this->get_country_list();
			$delete_data = array();
			foreach ( $country_iso2_codes as $iso2_code ) {
				$delete_data[] = array(
					'iso2_code'    => $iso2_code,
					'country_name' => $countries[ $iso2_code ],
				);
			}

			// Delete the data from the database.
			$deletion_result = $this->delete_countries_from_database( $delete_data );
			if ( ! $deletion_result['success'] ) {
				$errors[ $region['iso2_code'] ] = $deletion_result['message'];
			}
		}

		if ( empty( $errors ) ) {
			do_action( 'rsssl_update_rules' );

			foreach ( $data['region_codes'] as $region ) {
				$region['region_name'] = $this->get_region_name( $region['iso2_code'] );
				$region['region_code'] = $region['iso2_code'];
				Rsssl_Region_Allowed::handle_event( $region );
			}

			return $this->json_response(
				true,
				__( 'Access from the selected regions is now allowed.', 'really-simple-ssl' )
			);
		}

		return $this->json_response(
			false,
			__( 'An error occurred while removing regions from the list.', 'really-simple-ssl' ),
			null,
			$errors
		);

	}

	/**
	 * This removes all blocked countries from the database.
	 *
	 * @param  array  $delete_data  The array for data to delete.
	 *
	 * @return array
	 */
	private function delete_countries_from_database( array $delete_data ): array {
		global $wpdb;
		$table_name = $wpdb->base_prefix . 'rsssl_geo_block';
		$errors     = array();

		foreach ( $delete_data as $data ) {
			/* phpcs:disable WordPress.DB.DirectDatabaseQuery */
			$sql = $wpdb->prepare(
				"DELETE FROM $table_name WHERE iso2_code = %s AND `data_type` != 'trusted'",
				$data['iso2_code']
			);
			$deleted = $wpdb->query($sql);

			if ( false === $deleted ) {
				$errors[] = $wpdb->last_error;
			}
		}

		if ( ! empty( $errors ) ) {
			return array(
				'success' => false,
				'message' => __( 'Failed to delete some countries.', 'really-simple-ssl' ),
				'errors'  => $errors,
			);
		}

		return array( 'success' => true );
	}


	/**
	 * This function inserts the countries into the database.
	 *
	 * @param  array  $insert_data  Data that needs to be inserted.
	 *
	 * @return array
	 */
	private function insert_countries_into_database( array $insert_data ): array {
		global $wpdb;
		$table_name = $wpdb->base_prefix . 'rsssl_geo_block';
		$errors     = array();

		foreach ( $insert_data as $data ) {
			$inserted = $wpdb->insert(
				$table_name,
				array(
					'iso2_code'    => $data['iso2_code'],
					'country_name' => $data['country_name'],
                    'create_date'  => time(),
				)
			);

			if ( false === $inserted ) {
				$errors[] = $wpdb->last_error;
			}
		}

		if ( ! empty( $errors ) ) {
			return array(
				'success' => false,
				'message' => 'Failed to insert some countries.',
				'errors'  => $errors,
			);
		}

		return array( 'success' => true );
	}


	/**
	 * Adds a blocked country to the database.
	 *
	 * @param  array  $data  The data to use.
	 *
	 * @return array
	 */
	public function add_blocked_country( array $data ): array {
		// Check if necessary data is provided and valid.
		if ( ! $this->validate_keys( $data, 'country_code', 'country_name' ) ) {
			return $this->json_response( false, __( 'Missing or invalid country data.', 'really-simple-ssl' ) );
		}
		global $wpdb;
		$table_name = sanitize_text_field( $wpdb->base_prefix . 'rsssl_geo_block' );
		try {
			// first we check if the country is already in the list.
			if ( $this->country_exists_in_table( $table_name, $data['country_code'] ) ) {
				return $this->json_response( true, __( 'Country already in the list.', 'really-simple-ssl' ) );
			}

			// if the country is not in the list we add it.
			if ( ! $this->insert_country_to_table( $table_name, $data ) ) {
				return $this->json_response(
					false,
					__( 'Failed to add country to the list.', 'really-simple-ssl' )
				);
			}

		} catch ( Exception $e ) {
			// We return an error with the message.
			return $this->json_response(
				false,
				__( 'An error occurred: ', 'really-simple-ssl' ) . $e->getMessage()
			);
		}

		do_action( 'rsssl_update_rules' );

		// We return a success with the message.
		return $this->json_response(
			true,
			sprintf(
			// translators: %s: Name of the country that was removed from the blocked list.
				__( 'Access from %s is now blocked.', 'really-simple-ssl' ),
				$this->get_country_name( $data['country_code'] )
			),
			$wpdb->last_query
		);
	}

	public function add_blocked_countries( array $data ): array {
		$errors = array();
		foreach ( $data['country_codes'] as $country ) {
			// Check if necessary data is provided and valid.
			if ( ! $this->validate_keys( $country, 'country_code', 'country_name' ) ) {
				$errors[ $country['country_code'] ] = __( 'Missing or invalid country data.', 'really-simple-ssl' );
			}
			global $wpdb;
			$table_name = sanitize_text_field( $wpdb->base_prefix . 'rsssl_geo_block' );
			try {
				// first we check if the country is already in the list.
				if ( $this->country_exists_in_table( $table_name, $country['country_code'] ) ) {
					$errors[ $country['country_code'] ] = __( 'Country already in the list.', 'really-simple-ssl' );
					continue;
				}

				// if the country is not in the list we add it.
				if ( ! $this->insert_country_to_table( $table_name, $country ) ) {
					$errors[ $country['country_code'] ] = __( 'Failed to add country to the list.',
						'really-simple-ssl' );
				}

			} catch ( Exception $e ) {
				// We return an error with the message.
				$errors[ $country['country_code'] ] = __( 'An error occurred: ',
						'really-simple-ssl' ) . $e->getMessage();
			}
		}

		if ( empty( $errors ) ) {
			do_action( 'rsssl_update_rules' );

			return $this->json_response(
				true,
				__( 'Access from the selected countries is now blocked.', 'really-simple-ssl' )
			);
		}

		return $this->json_response(
			false,
			__( 'An error occurred while adding countries to the list.', 'really-simple-ssl' ),
			null,
			$errors
		);
	}

	/**
	 * Inserts a country into the specified database table.
	 *
	 * @param  string  $table_name  The name of the database table to insert into.
	 * @param  array  $data  The data to insert.
	 *                            - 'country_code'  (string) The ISO2 code of the country.
	 *                            - 'country_name'  (string) The name of the country.
	 *
	 * @return bool               True if the country is successfully inserted,
	 *                            false if an exception occurs during the insertion process.
	 */
	private function insert_country_to_table( string $table_name, array $data ): bool {
		global $wpdb;
		try {
			$wpdb->insert( $table_name, [
				'iso2_code'    => $data['country_code'],
				'country_name' => $data['country_name'],
                'create_date'  => time(),
			] );
		} catch ( Exception $e ) {
			return false;
		}
		Rsssl_Country_Blocked::handle_event( $data );

		return true;
	}

	/**
	 * Checks if a country exists in the specified table.
	 *
	 * @param  string  $table_name  The name of the table to check.
	 * @param  string  $country_code  The country code to look for.
	 *
	 * @return bool  Returns true if the country exists in the table, false otherwise.
	 */
	private function country_exists_in_table( string $table_name, string $country_code ): bool {
		global $wpdb;

		$statement = $wpdb->prepare( "SELECT iso2_code FROM $table_name WHERE iso2_code = %s and data_type = %s", $country_code, 'country' );
		$wpdb->get_results( $statement );

		return $wpdb->num_rows > 0;
	}

	/**
	 * Removes a blocked country from the database.
	 *
	 * @param  array  $data  The data to use.
	 *
	 * @return array
	 */
	public function remove_blocked_country( array $data ): array {
		// Check if necessary data is provided and valid.
		if ( ! $this->validate_keys( $data, 'country_code' ) ) {
			return $this->json_response(
				false,
				__( 'Invalid data provided.', 'really-simple-ssl' )
			);
		}

		global $wpdb;
		$table_name = $wpdb->base_prefix . 'rsssl_geo_block';

		$country_codes = is_array( $data['country_code'] ) ? $data['country_code'] : array( $data['country_code'] );

		try {
			$placeholders = implode( ', ', array_fill( 0, count( $country_codes ), '%s' ) );
			// first we check if the country is already in the list.
			/* phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared */
			$existing_countries = $this->get_existing_countries( $table_name, $country_codes );

			// if the country is not in the list we return.
			if ( empty( $existing_countries ) ) {
				return $this->json_response(
					true,
					sprintf(
					// translators: %s: Name of the country that was removed from the blocked list.
						__( '%s not in the list.', 'really-simple-ssl' ),
						$this->get_country_name( $data['country_code'] )
					)
				);
			}

			// Delete the countries from the list.
			$this->delete_countries( $country_codes, $table_name );
		} catch ( Exception $e ) {
			// We return an error with the message.
			return $this->json_response(
				false,
				__( 'An error occurred: ', 'really-simple-ssl' ) . $e->getMessage()
			);
		}

		// we rebuild the advanced_header file.
		do_action( 'rsssl_update_rules' );
		$data['country_name'] = $this->get_country_name( $data['country_code'] );
		Rsssl_Country_Allowed::handle_event( $data );
		// We return a success with the message.
		return $this->json_response(
			true,
			sprintf(
			// translators: %s: Name of the country that was removed from the blocked list.
				__( 'Access from %s is now allowed.', 'really-simple-ssl' ),
				$this->get_country_name( $data['country_code'] )
			)
		);
	}

	public function remove_blocked_countries( $data ): array {
		$errors = array();
		foreach ( $data['country_codes'] as $country ) {
			// First we validate;
			if ( ! $this->validate_keys( $country, 'country_code', 'country_name' ) ) {
				$errors[ $country['country_code'] ] = __( 'Missing or invalid country data.', 'really-simple-ssl' );
			}
			global $wpdb;
			$table_name = sanitize_text_field( $wpdb->base_prefix . 'rsssl_geo_block' );
			try {
				// first we check if the country is already in the list.
				/* phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared */
				$existing_countries = $this->get_existing_countries( $table_name, array( $country['country_code'] ) );

				// if the country is not in the list we return.
				if ( empty( $existing_countries ) ) {
					$errors[ $country['country_code'] ] = sprintf(
					// translators: %s: Name of the country that was removed from the blocked list.
						__( '%s not in the list.', 'really-simple-ssl' ),
						$this->get_country_name( $country['country_code'] )
					);
					continue;
				}

				// Delete the countries from the list.
				$this->delete_countries( array( $country['country_code'] ), $table_name );
			} catch ( Exception $e ) {
				// We return an error with the message.
				$errors[ $country['country_code'] ] = __( 'An error occurred: ',
						'really-simple-ssl' ) . $e->getMessage();
			}
		}

		if ( empty( $errors ) ) {
			do_action( 'rsssl_update_rules' );

			return $this->json_response(
				true,
				__( 'Access from the selected countries is now allowed.', 'really-simple-ssl' )
			);
		}

		return $this->json_response(
			false,
			__( 'An error occurred while removing countries from the list.', 'really-simple-ssl' ),
			null,
			$errors
		);

	}

	private function get_existing_countries( $table_name, $country_codes ) {
		global $wpdb;
		$placeholders = implode( ', ', array_fill( 0, count( $country_codes ), '%s' ) );

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT iso2_code FROM $table_name WHERE iso2_code IN ($placeholders) and data_type = 'country'",
				...$country_codes
			)
		);
	}


	private function delete_countries( $country_codes, $table_name ) {
		global $wpdb;
		foreach ( $country_codes as $country_code ) {
			$deleted = $wpdb->delete(
				$table_name,
				array( 'iso2_code' => $country_code, 'data_type' => 'country')
			);
			if ( ! $deleted ) {
				$this->json_response(
					false,
					sprintf(
						__( 'Failed to delete %s from the list.', 'really-simple-ssl' ),
						$this->get_country_name( $country_code )
					)
				);
			}
		}
	}


	/**
	 * Fetches a list of data based on the provided list type.
	 *
	 * @param string $list_type The type of list to fetch. Valid values are 'country', 'regions', and 'white_list'.
	 * @param array|null $data Optional. Additional data to filter the list. Default value is null.
	 * @param string $type Optional. The type of data to filter by. Default value is 'country'.
	 *
	 * @return array The fetched list data.
	 * @throws Exception If the list type is invalid.
	 */
	public function fetch_list( string $list_type, array $data = null, string $type = 'country'): array {
		// validate the list type.
		if ( ! $this->is_valid_list_type( $list_type ) ) {
			return $this->invalid_list_type_response();
		}

		global $wpdb;

		// manual ad a filter value to the $data.
		$table_name = 'rsssl_geo_block';
		if ( 'country' === $list_type ) { // This part you can extend with array based data.
			$return_data = $this->set_values_array_keys(
				$this->transpose_array(
					$this->get_country_list()
				)
			);

			return array( 'data' => $return_data );
		}

		if ( 'regions' === $list_type ) { // This part you can extend with array based data.
			$return_data = $this->set_values_array_keys(
				$this->transpose_array(
					$this->get_continent_list()
				), true
			);

			return array( 'data' => $return_data );
		}

		if ( 'white_list' === $list_type ) {
			// For database driven data.
			$data_table = $this->setup_data_table( $data, $wpdb->base_prefix . $table_name );
			$data_table->set_where( array( 'data_type', '=', $type ) );
			// we get the results.
			$result = $this->get_results_from_data_table_no_pagination( $data_table, $data ); // That's all we need to do.
			if ( isset( $result['data'] ) ) {
				foreach ( $result['data'] as $key => $value ) {
					$region_iso2_code                    = $this->build_country_continent_lookup_array( $value->iso2_code );
					$result['data'][ $key ]->region_name = $this->get_region_name( $region_iso2_code );
                    //convert unixtime to date formaat.
                    $result['data'][ $key ]->create_date = date('h:i, M j', (int)$value->create_date);
				}
			}

			return $result;
		}

		if ('block_list' === $list_type) {
			// For database driven data.
			$data_table = $this->setup_data_table( $data, $wpdb->base_prefix . $table_name );
			$data_table->set_where( array( 'data_type', '=' , $type ) );

			// We set the correct values bases on the filterValue
			switch ($data['filterValue']) {
				case 'temp':
					$data_table->set_where( array( 'blocked', '=', 1 ) );
					$data_table->set_where( array( 'permanent', '=', 0 ) );
					break;
				case 'permanent':
					$data_table->set_where( array( 'blocked', '=', 1 ) );
					$data_table->set_where( array( 'permanent', '=', 1 ) );
					break;
				default:
					$data_table->set_where( array( 'blocked', '=', 1 ) );
					break;
			}
			// we get the results.
			$result = $this->get_results_from_data_table_no_pagination( $data_table, $data ); // That's all we need to do.

			if ( isset( $result['data'] ) ) {
				foreach ( $result['data'] as $key => $value ) {
					if( true === (bool) $value->permanent ) {
						$result['data'][ $key ]->time_left = __( 'Permanent', 'really-simple-ssl' );
					} else {
						$result['data'][ $key ]->time_left = $this->get_time_left( $value->last_attempt );
					}
				}
			}
			return $result;
		}

        if ('user_agent_list' === $list_type) {
            if (isset($data['filter'])) {
                if ('blocked' === $data['filter']) {
                    $return_data['data'] = Rsssl_User_Agent_Block::fetch($data);
                } elseif('deleted' === $data['filter']) {
                    $return_data['data'] = Rsssl_User_Agent_Block::fetch($data, true);
                }
            } else {
                $return_data['data'] = Rsssl_User_Agent_Block::fetch($data);
            }

            return $return_data;
        }

		// For database driven data.
		$data_table = $this->setup_data_table( $data, $wpdb->base_prefix . $table_name );
		$data_table->set_where( array( 'data_type', '=', $type ) );

		$return_data = $this->get_results_from_data_table_no_pagination( $data_table,
			$data ); // That's all we need to do.

		foreach ( $return_data['data'] as $key => $value ) {
			if ( is_object($value) && property_exists($value, 'iso2_code') ) {
				$region_iso2_code   = $this->build_country_continent_lookup_array( $value->iso2_code );
				$value->region_name = $this->get_region_name( $region_iso2_code );
			}
		}
		return $return_data;
	}

	/**
	 * Retrieves a list of blocked countries from the database.
	 *
	 * @return string The list of blocked countries, separated by commas.
	 */
	public static function get_blocked_countries_list(): string {
		global $wpdb;
		$table_name   = $wpdb->base_prefix . 'rsssl_geo_block';
		$query_string = $wpdb->prepare(
			"SELECT iso2_code FROM {$table_name} WHERE data_type = %s AND ip_address is NULL",
			'country'
		);
		// phpcs:ignore
		$result         = $wpdb->get_results( $query_string );
		$column_results = array_column( $result, 'iso2_code' );

		return implode( ',', $column_results );
	}

	/**
	 * Retrieves the white list of trusted IP addresses.
	 *
	 * @return string The comma-separated list of IP addresses in the white list.
	 */
	public static function get_white_list(): string {
		global $wpdb;
		$table_name   = $wpdb->base_prefix . 'rsssl_geo_block';
		$query_string = $wpdb->prepare(
			"SELECT ip_address FROM {$table_name} WHERE data_type = %s",
			'trusted'
		);
		// phpcs:ignore
		$result         = $wpdb->get_results( $query_string );
		$column_results = array_column( $result, 'ip_address' );

		return implode( ',', $column_results );
	}

	/**
	 * Retrieves the list of blocked countries and returns it as an array.
	 *
	 * @return array The list of blocked countries as an array.
	 */
	public function get_blocked_countries_list_to_array(): array {
		$blocked_countries = self::get_blocked_countries_list();
		$blocked_countries = explode( ',', $blocked_countries );

		return array_map( 'trim', $blocked_countries );
	}

	/**
	 * Retrieves the list of blocked countries and returns it as an array.
	 *
	 * @return array The list of blocked countries as an array.
	 */
	public function get_white_list_to_array(): array {
		$white_list = self::get_white_list();
		$white_list = explode( ',', $white_list );

		return array_map( 'trim', $white_list );
	}

    /**
     * Generate the geo block lines for the advanced header file.
     *
     * @param $rules string The rules that are already in the advanced header file.
     * @return string
     */
	public function generate_rules_for_headers(string $rules): string {

		if ( ! $this->table_exists() ) {
			return $rules;
		}

		$blocked_countries = self::get_blocked_countries_list();
		if ( false === (bool) rsssl_get_option('enable_firewall') ) {
			return '';
		}

		$white_list       = self::get_white_list();
        $blocked_ips =      $this->clean_up_list($this->model->get_blocked_ips());
        $blocked_ips = implode(',', $blocked_ips);
        $blocked_agent_list = $this->model_user_agent->get_agent_list();
        $blocked_agent_list = $this->clean_up_list($blocked_agent_list, 'user_agent');
        $blocked_agent_list = implode(',', $blocked_agent_list);

        if (false === (bool)rsssl_get_option('enable_firewall')) {
            return '';
        }
        // Check if the site is part of a multisite network.
        if (is_multisite()) {
            // Retrieve the option from the network settings.
            $db_file = get_site_option('rsssl_geo_ip_database_file');
        } else {
            // Retrieve the option from the single site settings.
            $db_file = get_option('rsssl_geo_ip_database_file');
        }
		$block_page       = rsssl_get_template( '403-page.php', rsssl_path . 'pro/assets/templates' );
		$handler_file      = rsssl_path . 'pro/security/wordpress/firewall/block-region.php';
        $handler_file_404 = rsssl_path . 'pro/security/wordpress/firewall/404-detection.php';
        $handler_file_user_agent = rsssl_path . 'pro/security/wordpress/firewall/user-agent.php';
		$plugin_dir       = dirname( rsssl_plugin );
		$apology          = __('We\'re sorry.', 'really-simple-ssl');
		$message          = __('This website is unavailable in your region.', 'really-simple-ssl');
        $message_404      = __('Your access to this site has been temporarily denied', 'really-simple-ssl');
        $message_user_agent      = __('Your access to this site has been denied', 'really-simple-ssl');
		$error_code	   = __('Error code: 403', 'really-simple-ssl');

		$break    = "\n";
		$contents = $break;
		$contents .= '// Access Restrictions' . $break;
		$contents .= $break;

		if ( defined('WP_DEBUG') && WP_DEBUG ) {
			$contents .= '// Debug information because WP_DEBUG is true.' . $break;
			$contents .= 'ini_set("display_errors", 1);' . $break;
			$contents .= 'ini_set("display_startup_errors", 1);' . $break;
			$contents .= 'error_reporting(E_ALL);' . $break;
			$contents .= '// End Debug information' . $break;
			$contents .= $break;
		}

		$contents .= '// Plugin dir validation.' . $break;
		$contents .= '$plugin_dir = __DIR__ . "/plugins/' . $plugin_dir . '";' . $break;
		$contents .= 'if ( !file_exists($plugin_dir) ) {' . $break;
		$contents .= '  return;' . $break;
		$contents .= '}' . $break;
		$contents .= $break;

		$contents .= '// Variables needed for Access restrictions' . $break;
        $contents .= ' $ip_fetcher_file = "' . rsssl_path . 'pro/security/wordpress/limitlogin/class-rsssl-ip-fetcher.php";' . $break;
        $contents .= ' if ( !file_exists($ip_fetcher_file) ) {' . $break;
        $contents .= '   return;' . $break;
        $contents .= ' }' . $break;

        $contents .= '$country_detection_file = "' . rsssl_path . 'pro/security/wordpress/limitlogin/class-rsssl-country-detection.php";' . $break;
        $contents .= ' if ( !file_exists($country_detection_file) ) {' . $break;
        $contents .= '   return;' . $break;
        $contents .= ' }' . $break;

        $contents .= '$user_agent_detection_file = "' . rsssl_path . 'pro/security/wordpress/firewall/class-rsssl-user-agent-handler.php";' . $break;
        $contents .= ' if ( !file_exists($user_agent_detection_file) ) {' . $break;
        $contents .= '   return;' . $break;
        $contents .= ' }' . $break;

		$contents .= '  $apology = "' . $apology . '";' . $break;
		$contents .= '  $message = "' . $message . '";' . $break;
		$contents .= '  $error_code = "' . $error_code . '";' . $break;
		$contents .= '// The Geo Database file.' . $break;
		$contents .= '  $db_file = "' . $db_file . '";' . $break;
		$contents .= '  if (!file_exists($db_file)) {' . $break;
		$contents .= '    return;' . $break;
		$contents .= '  }' . $break;

        $contents .= '// Variables needed for 404' . $break;
        $contents .= '  $apology_404 = "' . $apology . '";' . $break;
        $contents .= '  $message_404 = "' . $message_404 . '";' . $break;
		$contents .= $break;

		$contents .= '// The block page.' . $break;
		$contents .= '  $block_page = "' . $block_page . '";' . $break;
		$contents .= '  if (!file_exists($block_page)) {' . $break;
		$contents .= '    return;' . $break;
		$contents .= '  }' . $break;
		$contents .= $break;

		$contents .= '// The blocked countries and the white list.' . $break;
		$contents .= '  $countries_blocked = explode(",", "' . $blocked_countries . '");' . $break;
		$contents .= '  $white_list = explode(",", "' . $white_list . '");' . $break;
        $contents .= '// The blocked ips for 404.' . $break;
        $contents .= '  $blocked_ips = explode(",", "' . $blocked_ips . '");' . $break;
		$contents .= $break;
		// now we add the file and include if it exists.
		$contents .= '// Loading the block-region.php' . $break;
		$contents .= 'if ( file_exists( "' . $handler_file . '" ) ) {' . $break;
		$contents .= '  require_once "' . $handler_file . '";' . $break;
		$contents .= '}' . $break;

        // now we add the file and include if it exists.
        $contents .= '// Loading the 404-detection.php' . $break;
        $contents .= 'if ( file_exists( "' . $handler_file_404 . '" ) ) {' . $break;
        $contents .= '  require_once "' . $handler_file_404 . '";' . $break;
        $contents .= '}' . $break;
        $contents .= '// End Loading the block-region.php' . $break;

        $contents .= '// Variables needed for useragent' . $break;
        $contents .= '  $apology_404 = "' . $apology . '";' . $break;
        $contents .= '  $message_user_agent = "' . $message_user_agent . '";' . $break;
        $contents .= $break;

        // adding the list of blocked user agents.
        $contents .= '// The blocked user agents.' . $break;
        $contents .= '  $blocked_user_agents = explode(",", "' . $blocked_agent_list . '");' . $break;
        $contents .= $break;


        // now we add the file and include if it exists.
        $contents .= '// Loading the user-agents.php' . $break;
        $contents .= 'if ( file_exists( "' . $handler_file_user_agent . '" ) ) {' . $break;
        $contents .= '  require_once "' . $handler_file_user_agent . '";' . $break;
        $contents .= '}' . $break;
        $contents .= '// End Loading the block-region.php' . $break;

		return $rules.$contents;
	}

    /**
     * Clean up the list by returning only the non-null ip addresses.
     *
     * @param array $data The list of objects.
     *
     * @return array The list of non-null ip addresses
     */
    private function clean_up_list(array $data, $type = 'ip_address'): array
    {
        return array_values(
            array_filter(
                array_map(
                    static function ($item) use ($type) {
                        // If the object is not null we return the ip address.
                        if ('ip_address' === $type) {
                            return $item->ip_address ?? null;
                        }
                        if ('user_agent' === $type) {
                            return $item->user_agent ?? null;
                        }
                    },
                    $data
                )
            )
        );
    }


	/**
	 * This function is used to handle the api calls for the ip list.
	 *
	 * @param  array  $response  The response array.
	 * @param  string  $action  The action to perform.
	 * @param  array  $data  The data to use.
	 *
	 * @return array|null
	 * @throws Exception When an error occurs while updating the data.
	 */
	public function rsssl_geo_block_api( array $response, string $action, array $data ): ?array {
		// if the option is not enabled, we return the response.
		if ( ! rsssl_admin_logged_in() ) {
			return $response;
		}

		switch ( $action ) {
			case 'geo_block_add_blocked_country':
				if ( isset( $data['country_codes'] ) ) {
					$response = $this->add_blocked_countries( $data );
				} else {
					$response = $this->add_blocked_country( $data );
				}
				break;
			case 'geo_block_remove_blocked_country':
				if ( isset( $data['country_codes'] ) ) {
					$response = $this->remove_blocked_countries( $data );
				} else {
					$response = $this->remove_blocked_country( $data );
				}
				break;
			case 'geo_block_add_blocked_region':
				if ( isset( $data['region_codes'] ) ) {
					$response = $this->add_blocked_regions( $data );
				} else {
					$response = $this->add_blocked_region( $data );
				}
				break;
			case 'geo_block_remove_blocked_region':
				if ( isset( $data['region_codes'] ) ) {
					$response = $this->remove_blocked_regions( $data );
				} else {
					$response = $this->remove_blocked_region( $data );
				}
				break;
			case 'geo_block_add_white_list_ip':
				$response = $this->add_white_list_ip( $data );

				break;
			case 'geo_block_reset_ip':
				$response = $this->remove_white_list_ip( $data );

				break;
			case 'rsssl_geo_list':
				// Based on the filter we get the correct list.
				$response = $this->handle_geo_list( $data );
				break;
			case 'rsssl_geo_white_list':
				// Based on the filter we get the correct list.
				$response = $this->handle_white_list( $data );
				break;
			case 'rsssl_firewall_block_list':
				// Based on the filter we get the correct list.
				$response = $this->handle_blocked_list( $data );
				break;
            case 'rsssl_user_agent_list':
                // Based on the filter we get the correct list.
                $response = $this->handle_user_agent_list( $data );
                break;
            case 'rsssl_user_agent_add':
                // Based on the filter we get the correct list.
                $response = $this->handle_user_agent_add( $data );
                do_action( 'rsssl_update_rules' );
                break;
            case 'rsssl_user_agent_delete':
                // Based on the filter we get the correct list.
                $response = $this->handle_user_agent_delete( $data );
                do_action( 'rsssl_update_rules' );
                break;
		}

		return $response;
	}

	/**
	 * Handles the geo list based on the specified Rsssl_Geo_Block object and data array.
	 *
	 * @param  array  $data  The data array containing additional parameters for handling the list.
	 *
	 * @return array The fetched or generated geo list.
	 * @throws Exception Throws an exception when an invalid filter value is specified.
	 */
	public function handle_geo_list( array $data ): array {
		if ( ! isset( $data['filterValue'] ) ) {
			return $this->json_response( false, __( 'Missing filter value.', 'really-simple-ssl' ) );
		}

		switch ( $data['filterValue'] ) {
			case 'countries':
				return $this->fetch_country_list( $data );
			case 'regions':
				return $this->fetch_continent_list( $data );
			case 'blocked':
				// When the table not yet exists, we return an empty array.
				if ( ! $this->table_exists() ) {
					return array(
						'success' => true,
						'data'    => array(),
						'post'    => $data,
					);
				}

				return $this->fetch_blocked_list( $data, 'country' );

			default:
				return $this->invalid_list_type_response();
		}
	}


	/**
	 * Handles the geo list based on the specified Rsssl_Geo_Block object and data array.
	 *
	 * @param  array  $data  The data array containing additional parameters for handling the list.
	 *
	 * @return array The fetched or generated geo list.
	 * @throws Exception Throws an exception when an invalid filter value is specified.
	 */
	public function handle_blocked_list( array $data ): array {
		if ( ! isset( $data['filterValue'] ) ) {
			return $this->json_response( false, __( 'Missing filter value.', 'really-simple-ssl' ) );
		}

		switch ( $data['filterValue'] ) {
			case 'all':
			case 'temp':
			case 'permanent':
				// When the table not yet exists, we return an empty array.
				if ( ! $this->table_exists() ) {
					return array(
						'success' => true,
						'data'    => array(),
						'post'    => $data,
					);
				}

				return $this->fetch_block_list( $data );

			default:
				return $this->fetch_block_list( $data );
		}
	}

	/**
	 * This function is used to create an array with country names.
	 *
	 * @param  int  $id  The ID of the country.
	 * @param  string  $iso2_code  The ISO 2 code of the country.
	 * @param  string  $country_name  The name of the country.
	 *
	 * @return array The array with the country details.
	 */
	public function create_array_with_country_name( int $id, string $iso2_code, string $country_name ): array {
		$continent_iso2_code = $this->build_country_continent_lookup_array( $iso2_code );

		return array(
			'id'           => $id, // This is just a placeholder for the data table.
			'iso2_code'    => $iso2_code,
			'country_name' => $country_name,
			'region'       => $continent_iso2_code,
			'region_name'  => $this->get_region_name( $continent_iso2_code ),
			'status'       => 'allowed',
		);
	}

	/**
	 * Fetches the blocked IP list based on the provided data and type.
	 *
	 * @param  array  $data  The data used to fetch the blocked list.
	 * @param  string  $type  The type of the data (default: 'country').
	 *
	 * @return array  The fetched blocked IP list.
	 */
	private function fetch_blocked_list( array $data, string $type = 'country' ): array {
		// Logic specific to fetching blocked list.
		try {
			return $this->fetch_list( 'block', $data, $type );
		} catch ( Exception $e ) {
			return $this->json_response(
				false,
				__( 'An error occurred: ', 'really-simple-ssl' ) . $e->getMessage()
			);
		}
	}

	/**
	 * This sets us up for the data table.
	 *
	 * @param  array  $data  The data to use.
	 * @param  string  $table_name  The table name to use.
	 *
	 * @throws Exception We throw an exception if it failed.
	 */
	private function setup_data_table( array $data, string $table_name ): Rsssl_Data_Table {
		return ( new Rsssl_Data_Table( $data, new Rsssl_Query_Builder( $table_name ) ) )->set_select_columns(
			array(
				'id',
				'iso2_code',
				'country_name',
				'ip_address',
				'create_date',
				'last_attempt',
				'note',
				'blocked',
				'permanent',
				"raw: '" . date('Y-m-d H:i:s', time()) . "' as now",
				"raw: '' as time_left",
				"raw: '" . __( 'Blocked', 'really-simple-ssl' ) . "' as status",
			)
		);
	}


	/**
	 * Renames the attempt_value to iso2_code.
	 *
	 * @param  array|null  $data  Renames the attempt value to iso2_code if needed.
	 *
	 * @return array|null
	 */
	private function rename_attempt_value_to_iso2_code( ?array $data ): ?array {
		if ( isset( $data['searchColumns'] ) ) {
			foreach ( $data['searchColumns'] as $key => $value ) {
				if ( 'attempt_value' === $value ) {
					$data['searchColumns'][ $key ] = 'iso2_code';
				}
			}
		}

		return $data;
	}

	/**
	 * This function is used to set up the data table array for the Really Simple Security Pro plugin.
	 *
	 * @param  array  $data  The original data array.
	 * @param  array  $countries  The countries array.
	 *
	 * @return Rsssl_Data_Table The set-up data table array.
	 * @throws Exception Throws an exception if filters or data does not match.
	 */
	public function setup_data_table_array( array $data, array $countries ): Rsssl_Data_Table {
		return ( new Rsssl_Data_Table( $data, new Rsssl_Array_Query_Builder( $countries ) ) )->set_select_columns(
			array(
				'id',
				'iso2_code',
				'country_name',
				"raw: '" . __( 'Open', 'really-simple-ssl' ) . "' as status",
			)
		);
	}

	public function set_values_array_keys( $countries, $region = false ): array {
		if ( $region ) {
			// We count the number of countries in the region.
			// we get all the blocked countries.
			$blocked_countries = $this->get_blocked_countries_list_to_array();
			// We remove empty values.
			$blocked_countries = array_filter( $blocked_countries );

			foreach ( $countries as $key => $value ) {
				$region_countries = $this->get_countries_by_region_code( $value['iso2_code'] );
				// We remove empty values.
				$region_countries = array_filter( $region_countries );
				// we count the number of countries in the region.
				$region_count = count( $region_countries );
				// we count the number of blocked countries in the region.
				$blocked_count = count( array_intersect( $region_countries, $blocked_countries ) );

				// we add the count to the array.
				$countries[ $key ]['region_count']  = $region_count;
				$countries[ $key ]['blocked_count'] = $blocked_count;
			}
		}

		// We change the named keys to other values.
		// We loop through the countries and add a status.
		foreach ( $countries as $key => $value ) {
			$countries[ $key ]['status'] = 'allowed';
			if ( $region ) {
				$countries[ $key ]['country_name'] = 'All';
				// if the number of blocked countries is the same as the number of countries in the region, we block the region.
				if ( $value['blocked_count'] > 0 ) {
					$countries[ $key ]['status'] = 'blocked';
				}
			}
		}

		// we make sure there is no empty value.
		return array_filter( $countries );
	}

	/**
	 * Filters an array based on a list of blocked countries.
	 *
	 * @param  array  $original_array  The original array to filter.
	 * @param  bool  $ignore_filter  Optional. Flag to indicate whether to ignore the filter. Default is false.
	 *
	 * @return array The filtered array without the countries in the blocked countries list.
	 */
	public function filter_array( array $original_array, bool $ignore_filter = false ): array {
		$countries = array();
		if ( ! $ignore_filter ) {
			$countries = $this->get_blocked_countries_list_to_array();
		}

		// now we filter out the countries from the original array that are in the $countries.
		return array_filter(
			$original_array,
			static function ( $key ) use ( $countries ) {
				return ! in_array( $key, $countries, true );
			},
			ARRAY_FILTER_USE_KEY
		);
	}

	/**
	 * This function is used to create an array with continent name.
	 *
	 * @param  int  $id  The ID of the country.
	 * @param  string  $iso2_code  The ISO 2 code of the country.
	 * @param  string  $continent_name  The name of the continent.
	 *
	 * @return array Returns an array with the country details including the continent name.
	 */
	public function create_array_with_continent_name( int $id, string $iso2_code, string $continent_name ): array {
		return array(
			'id'           => $id, // This is just a placeholder for the data table.
			'iso2_code'    => $iso2_code,
			'country_name' => $continent_name,
			'region'       => $iso2_code,
			'region_name'  => $continent_name,
			'status'       => 'allowed',
		);
	}

	/**
	 * Adds a trusted IP address to the database.
	 *
	 * @param  string  $ip_address  The IP address to add.
	 * @param  string  $country  The country of the IP address.
	 * @param  string  $note  An optional note for the IP address.
	 *
	 * @return void
	 *
	 * @throws RuntimeException If an error occurs while adding the trusted IP address.
	 */
	private function add_trusted_ip( string $ip_address, string $country, string $note = '', string $status = 'trusted'  ): void {
		global $wpdb;
		$table_name = $wpdb->base_prefix . 'rsssl_geo_block';

		if ( ! $this->exists_trusted_ip( $ip_address ) ) {
			try {
				$result = $wpdb->insert(
					$table_name,
					array(
						'ip_address'   => $ip_address,
						'iso2_code'    => mb_strlen( $country ) > 2 ? 'XX' : $country,
						'country_name' => $this->get_country_name( $country ),
						'data_type'    => 'blocked' === $status ? '404' : 'trusted',
						'permanent'    => 'blocked' === $status ? 1 : 0,
						'blocked'      => 'blocked' === $status ? 1 : 0,
                        'create_date'  => time(),
						'note'         => $note,
					)
				);
				if ( false === $result ) {
					$result = $wpdb->last_error;
					throw new RuntimeException( sprintf(esc_html( 'Error while adding %s IP: ' . $result ) , $status) );
				}
				if ('blocked' === $status ) {
					Rsssl_Ip_Blocked_Permanent::handle_event( array( 'ip_address' => $ip_address ) );
				} else {
					Rsssl_Ip_Trusted_Event::handle_event( array( 'ip_address' => $ip_address ) );
				}
			} catch ( Exception $e ) {
				// We log the error.
				throw new RuntimeException( sprintf(esc_html( 'Error while adding %s IP: ' . $e->getMessage() ), $status) );
			}
		}
	}

	/**
	 * Deletes a record from the database table by ID.
	 *
	 * @param  int  $id  The ID of the record to be deleted.
	 *
	 * @return void
	 */
	private function delete_by_id( int $id ): void {
		global $wpdb;
		$table_name = $wpdb->base_prefix . 'rsssl_geo_block';

		$entry = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $table_name WHERE id = %d",
				$id
			)
		);

		$this->handle_delete_event($entry->ip_address, $entry->data_type);

		$wpdb->delete(
			$table_name,
			array( 'id' => $id )
		);
	}

	/**
	 * Checks if a given table exists in the database.
	 *
	 * @return bool True if the table exists, otherwise false.
	 */
	private function table_exists(): bool {
		global $wpdb;
		$table_name = $wpdb->base_prefix . 'rsssl_geo_block';
		//phpcs:ignore
		$result = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) );

		return $result === $table_name;
	}

	/**
	 * Checks if a given IP address exists in the trusted IP list.
	 *
	 * @param  string  $ip_address  The IP address to check.
	 *
	 * @return bool True if the IP address exists in the trusted IP list, otherwise false.
	 */
	private function exists_trusted_ip( string $ip_address ): bool {
		if ( ! $this->table_exists() ) {
			return false;
		}
		global $wpdb;
		$table_name = $wpdb->base_prefix . 'rsssl_geo_block';
		//phpcs:ignore
		$statement = $wpdb->prepare(
		//phpcs:ignore
			"SELECT ip_address FROM $table_name WHERE ip_address = %s",
			$ip_address
		);
		//phpcs:ignore
		$wpdb->get_results( $statement );

		return $wpdb->num_rows > 0;
	}

	/**
	 * Handles the white list by fetching the trusted IP addresses.
	 *
	 * @param  array  $data  The data array to filter the white list.
	 *
	 * @return array The fetched white list.
	 * @throws Exception Throws an exception if an error occurs while fetching the white list.
	 */
	private function handle_white_list( array $data ): array {
		return $this->fetch_list( 'white_list', $data, 'trusted' );
	}

	/**
	 * Adds a white list IP address to the trusted IP list.
	 *
	 * @param  array  $data  The data containing the IP address and optional note.
	 *                   Example: ['ip_address' => '192.168.0.1', 'note' => 'Example note']. The note is optional.
	 *
	 * @return array       An array containing the response status and message.
	 *                    Example: ['status' => true, 'message' => 'IP address 192.168.0.1 is now trusted.']
	 *
	 * @throws Exception If validation fails.
	 */
	private function add_white_list_ip( array $data ): array {
		// We check if note is set and not empty.
		if ( empty( $data['note'] ) ) {
			unset( $data['note'] );
		}

		$country_detection = new Rsssl_Country_Detection( get_site_option( 'rsssl_geo_ip_database_file' ) );
		// Check if necessary data is provided and valid.
		if ( ! $this->validate_keys( $data, 'ip_address', isset( $data['note'] ) ? 'note' : null ) ) {
			return $this->json_response( false, __( 'Invalid data provided.', 'really-simple-ssl' ) );
		}

		// Check if an entry is already set.
		if ( $this->exists_trusted_ip( $data['ip_address'] ) ) {
			// updates the ip to trusted.
			$this->update_ip( $data['ip_address'], $data['note'] ?? '', $data['status'] );
		}

		try {
			// Add the trusted IP address to the database.
			$this->add_trusted_ip(
				$data['ip_address'],
				$country_detection->get_country_by_ip( $data['ip_address'] ),
				$data['note'] ?? '',
				$data['status'] ?? 'trusted'
			);
		} catch ( RuntimeException $e ) {
			// We return an error with the message.
			return $this->json_response( false, $e->getMessage() );
		}

		do_action( 'rsssl_update_rules' );
		// Return a success with the message.
		return $this->json_response(
			true,
			sprintf(
			// translators: %s: Name of the country that was removed from the blocked list.
				__( 'IP address %s is now %s.', 'really-simple-ssl' ),
				$data['ip_address'],
				$data['status'] ?? 'trusted'
			)
		);
	}

	/**
	 * Removes an IP address from the white list.
	 *
	 * @param  array  $data  The data containing the ID of the IP address to remove.
	 *
	 * @return array JSON response with a success or error message.
	 *
	 * @throws RuntimeException If an error occurs while deleting the IP address.
	 */
	private function remove_white_list_ip( array $data ): array {
		if ( ! $this->validate_keys( $data, 'id' ) ) {
			return $this->json_response( false, __( 'Invalid data provided.', 'really-simple-ssl' ) );
		}

		try {
			$this->delete_by_id( $data['id'] );
		} catch ( RuntimeException $e ) {
			return $this->json_response( false, $e->getMessage() );
		}
		do_action( 'rsssl_update_rules' );
		return $this->json_response(
			true,
			__( 'IP removed from list.', 'really-simple-ssl' )
		);
	}

	/**
	 * Fetches the block list using the given data.
	 *
	 * @param array $data The data used for fetching the block list.
	 *
	 * @return array
	 * @throws Exception If an error occurs while fetching the block list.
	 */
	private function fetch_block_list( array $data ): array {
		return $this->fetch_list( 'block_list', $data, '404' );
	}

	/**
	 * Handles the delete event based on the data type.
	 *
	 * @param string $ip_address The IP address.
	 * @param string $data_type The type of data (either '404' or any other value).
	 *
	 * @return void
	 */
	private function handle_delete_event( $ip_address, $data_type ): void {
		if ('404' === $data_type) {
			Rsssl_Ip_Removed_From_Blocklist::handle_event( array( 'ip_address' => $ip_address ) );
		} else {
			Rsssl_Ip_Removed_From_Whitelist::handle_event( array( 'ip_address' => $ip_address ) );
		}
	}

    /**
     * Handles the handling of the user agent list.
     *
     * Retrieves the list of user agents from the 'user_agent_list' database table
     * based on the provided data and returns the result.
     *
     * @param array $data The data to retrieve the user agent list.
     *                    - 'user_agent' (string) The user agent value to search for.
     *
     * @return array The retrieved user agent list from the 'user_agent_list' database table.
     */
    private function handle_user_agent_list(array $data)
    {
        return $this->fetch_list('user_agent_list', $data, 'user_agent');
    }

    /**
     * Handle the addition of a user agent.
     *
     * @param array $data The data containing the user agent and note.
     *
     * @return array The JSON response indicating success or failure.
     */
    private function handle_user_agent_add(array $data): array
    {
        try {
            $this->model_user_agent->add($data['user_agent'], $data['note']);
        } catch (Exception $e) {
            return $this->json_response(false, $e->getMessage());
        }

        return $this->json_response(true, __('User agent added.', 'really-simple-ssl'));
    }

    private function handle_user_agent_delete(array $data)
    {
        if (!isset($data['id'])) {
            return $this->json_response(false, __('Invalid data provided.', 'really-simple-ssl'));
        }

        $delete_values = $data["id"];

        if(is_array($delete_values)) {
            $delete_values =  // Only extracting the id values from the array
                array_map(static function($item) {
                    return $item['id'];
                }, $delete_values);
        } else {
            $delete_values = [$delete_values];
        }

        try {
            foreach ($delete_values as $id) {
                //get full object
                $user_agent = $this->model_user_agent->get_by_id($id);

                // if the useragent is in the default list, perform a soft delete otherwise perform a hard delete
                if (array_key_exists($user_agent->user_agent, $this->user_agents_list())) {
                    if($user_agent->deleted_at !== null) {
                        $this->model_user_agent->unSoftDelete($id);
                        continue;
                    }
                    $this->model_user_agent->softDelete($id);
                } else {
                    $this->model_user_agent->delete($id);
                }
            }
        } catch (Exception $e) {
            return $this->json_response(false, $e->getMessage());
        }

        return $this->json_response(true, __('User agent removed from current list.', 'really-simple-ssl'));
    }

    /**
     * Generate default user agents.
     *
     * This method is responsible for generating default user agents and adding them to the user agent model.
     * The generated user agents include "Lemon-Duck-*" with the note "trojan cryptominer".
     *
     * @return void
     */
    private function add_default_user_agents(): void
    {
        foreach ($this->user_agents_list() as $user_agent => $note) {
            $this->model_user_agent->add($user_agent, $note);
        }
    }

    /**
     * Returns an array containing the list of user agents and their descriptions.
     *
     * @return array The list of user agents and their descriptions.
     */
    private function user_agents_list (): array
    {
        return [
            'Lemon-Duck-*' => 'Trojan cryptominer',
            'Barkrowler' => 'Aggressive web crawler',
            'BDCbot' => 'Web scraping, possible data theft',
            'BLEXBot' => 'Web scraping, possible data theft',
            'Buck' => 'Web Scraping, possible data theft.',
            'Firefox/3.0' => 'Outdated browser, often misused',
            'MegaIndex.ru' => 'Web scraping, possible data theft',
            'python-requests' => 'Web scraping, possible data theft',
            'site.ru' => 'Web scraping, possible data theft',
        ];
    }

	/**
	 * Updates the ip to trusted
	 *
	 * @param $ip_address
	 * @param $note
	 * @param $status
	 *
	 * @return void
	 */
	private function update_ip( $ip_address, $note, $status ): void {
		global $wpdb;
		$table_name = $wpdb->base_prefix . 'rsssl_geo_block';
		$permanent = 0;
		$blocked = 0;
		if ( 'blocked' === $status) {
			$permanent = 1;
			$blocked = 1;
			$status = '404';
		}
		$wpdb->update(
			$table_name,
			array(
				'data_type' => $status ?? 'trusted',
				'permanent' => $permanent,
				'blocked'   => $blocked,
				'note'    =>  $note,
			),
			array( 'ip_address' => $ip_address )
		);
	}
}

new Rsssl_Geo_Block();
