<?php
/**
 * The Rsssl_Admin_Config_Countries class is responsible for handling the configuration of countries and regions in the Really Simple Security Pro plugin.
 * It provides methods for fetching lists of countries and regions, and for handling the geo list based on the specified Rsssl_Geo_Block object and data array.
 * It also provides methods for creating arrays with country and continent names, and for registering API routes and handling API actions.
 *
 * @package Really_Simple_SSL_PRO
 * @subpackage Security\WordPress\Limitlogin
 * @since 7.3.0
 * @category Class
 * @author Really Simple Security
 * @company Really Simple Plugins
 */

namespace RSSSL_PRO\Security\WordPress\Limitlogin;

require_once rsssl_path . 'pro/security/wordpress/traits/trait-rsssl-api-toolbox.php';
require_once rsssl_path . 'pro/security/wordpress/traits/trait-rsssl-country.php';
require_once rsssl_path . 'pro/security/wordpress/contracts/interface-rsssl-country-contract.php';

use Exception;
use RSSSL\Pro\Security\DynamicTables\Rsssl_Array_Query_Builder;
use RSSSL\Pro\Security\DynamicTables\Rsssl_Data_Table;
use RSSSL\Pro\Security\DynamicTables\Rsssl_Query_Builder;
use RSSSL\Pro\Security\WordPress\Contracts\Rsssl_Country_Contract;
use RSSSL\Pro\Security\WordPress\Traits\Rsssl_Api_Toolbox;
use RSSSL\Pro\Security\WordPress\Traits\Rsssl_Country;
use wpdb;

/**
 * The Rsssl_Admin_Config_Countries class is responsible for handling the configuration of countries and regions in the Really Simple Security Pro plugin.
 * It provides methods for fetching lists of countries and regions, and for handling the geo list based on the specified Rsssl_Geo_Block object and data array.
 * It also provides methods for creating arrays with country and continent names, and for registering API routes and handling API actions.
 *
 * @package Really_Simple_SSL_PRO
 * @subpackage Security\WordPress\Limitlogin
 */
class Rsssl_Admin_Config_Countries implements Rsssl_Country_Contract {
	use Rsssl_Api_Toolbox;
	use Rsssl_Country;

	public const LIST_TYPES = array( 'country', 'regions' );

	/**
	 * Class constructor.
	 *
	 * Registers the API routes for the "rsssl_do_action" filter.
	 *
	 * @return void
	 */
	public function __construct() {
		add_filter( 'rsssl_do_action', array( $this, 'register_api_routes' ), 10, 3 );
	}

	/**
	 * Fetches a list of items based on the specified list type and optional data.
	 *
	 * @param  string     $list_type  The type of list to fetch.
	 * @param  array|null $data  Optional data to filter or modify the fetched list.
	 *
	 * @return array The fetched list of items.
	 * @throws Exception Exception when table failed.
	 */
	public function fetch_list( string $list_type, array $data = null ): array {
		if ( ! $this->is_valid_list_type( $list_type ) ) {
			return $this->invalid_list_type_response();
		}

		if ( $list_type === 'country' ) { // This part you can extend with array based data.
			$data_table = $this->setup_data_table_array(
				$data,
				$this->transpose_array(
					$this->get_country_list()
				)
			);

			return $this->get_results_from_data_table( $data_table, $data ); // That's all we need to do.
		}

		if ( 'regions' === $list_type ) { // This part you can extend with array based data.
			$data_table = $this->setup_data_table_array(
				$data,
				$this->transpose_array(
					$this->get_continent_list()
				)
			);

			return $this->get_results_from_data_table( $data_table, $data ); // That's all we need to do.
		}

		return array();
	}

	/**
	 * This function is used to set up the data table array for the Really Simple Security Pro plugin.
	 *
	 * @param  array $data  The original data array.
	 * @param  array $countries  The countries array.
	 *
	 * @return Rsssl_Data_Table The set-up data table array.
	 * @throws Exception Throws an exception if filters or data does not match.
	 */
	public function setup_data_table_array( array $data, array $countries ): Rsssl_Data_Table {
		// We filter out the array where attempt_type is country.
		return ( new Rsssl_Data_Table(
			$data,
			new Rsssl_Array_Query_Builder(
				$this->filter_array( $countries )
			)
		)
		)->set_select_columns(
			array(
				'id',
				'iso2_code',
				'country_name',
				'region',
				'raw: country as attempt_type',
				'raw: iso2_code as attempt_value',
				'raw: "" as region_name',
				"raw: '" . __( 'Trusted', 'really-simple-ssl' ) . "' as status",
			)
		);
	}

	/**
	 * Filter the original array by removing keys that exist in the blocked countries list.
	 *
	 * @param  array $original_array  The original array to filter.
	 * @param  bool  $ignore_filter  Set to true to ignore the filter and return the original array as-is.
	 *
	 * @return array The filtered array with keys that do not exist in the blocked countries list.
	 * @global wpdb $wpdb The WordPress database access object.
	 */
	public function filter_array( array $original_array, bool $ignore_filter = false ): array {
		if ( $ignore_filter ) {
			return $original_array;
		}
		$blocked_countries = $this->get_blocked_countries();
		$countries         = array();

		foreach ( $blocked_countries as $country ) {
			$countries[] = $country->attempt_value;
		}

		return array_filter(
			$original_array,
			static function ( $key ) use ( $countries ) {
				return ! in_array( $key, $countries, true );
			},
			ARRAY_FILTER_USE_KEY
		);
	}

	/**
	 * Handles the geo list based on the specified Rsssl_Geo_Block object and data array.
	 *
	 * @param  array $data  The data array containing additional parameters for handling the list.
	 * @param  string $optional_action  The optional action to perform on the list.
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
				return $this->fetch_list( 'country', $data );
			case 'regions':
				return $this->fetch_continent_list( $data );
			case 'blocked':
				return $this->fetch_blocked_list( $data );
			default:
				return $this->invalid_list_type_response();
		}
	}

	/**
	 * This function is used to create an array with country names.
	 *
	 * @param  int    $id  The ID of the country.
	 * @param  string $iso2_code  The ISO 2 code of the country.
	 * @param  string $country_name  The name of the country.
	 *
	 * @return array The array with the country details.
	 */
	public function create_array_with_country_name( int $id, string $iso2_code, string $country_name ): array {
		$continent_iso2_code = $this->build_country_continent_lookup_array( $iso2_code );
		$blocked_countries   = $this->get_blocked_countries_with_id();
		// We extract the iso2_code from the blocked countries.
		$blocked_countries_value = array_map(
			static function ( $country ) {
				return $country->attempt_value;
			},
			$blocked_countries
		);

		return array(
			'id'            => $id, // This is just a placeholder for the data table.
			'iso2_code'     => $iso2_code,
			'attempt_value' => $iso2_code,
			'attempt_type'  => 'country',
			'country_name'  => $country_name,
			'region'        => $continent_iso2_code,
			'region_name'   => $this->get_region_name( $continent_iso2_code ),
			'status'        => ( in_array( $iso2_code, $blocked_countries_value, true ) ? 'blocked' : 'trusted' ),
			// Here we fetch the id from the $blocked_countries that matches the iso 2.
			'db_id'         => ( in_array(
				$iso2_code,
				$blocked_countries_value,
				true
			) ? $blocked_countries[ array_search( $iso2_code, $blocked_countries_value, true ) ]->id : 0 ),
		);
	}


	/**
	 * This function is used to create an array with continent name.
	 *
	 * @param  int    $id  The ID of the country.
	 * @param  string $iso2_code  The ISO 2 code of the country.
	 * @param  string $continent_name  The name of the continent.
	 *
	 * @return array Returns an array with the country details including the continent name.
	 */
	public function create_array_with_continent_name( int $id, string $iso2_code, string $continent_name ): array {
		return array(
			'id'            => $id, // This is just a placeholder for the data table.
			'iso2_code'     => $iso2_code,
			'country_name'  => __( 'All', 'really-simple-ssl' ),
			'attempt_value' => $iso2_code,
			'attempt_type'  => 'country',
			'region'        => $continent_name,
			'region_name'   => $continent_name,
			'status'        => 'Trusted',
		);
	}


	/**
	 * Fetch the blocked list.
	 *
	 * @param  array $data  The data used for filtering, sorting, and pagination.
	 *
	 * @return array  The blocked list data or an error object on exception.
	 */
	private function fetch_blocked_list( array $data ): array {
		global $wpdb;

		if ( isset( $data['searchColumns'] ) ) {
			$search       = $data['searchColumns'];
			$search_value = $data['search'];
			unset( $data['searchColumns'], $data['search'] );
		}

		try {
			$filtered_list = array();
			if ( isset( $search, $search_value ) ) {
				$filtered_list = $this->get_iso2_codes_by_country_name( $search_value );
			}
			$timezone = get_option( 'timezone_string' );
			if ( ! $timezone ) {
				// if there is no timezone set we set it to UTC.
				$timezone = 'UTC';
			}

			$query_builder = ( new Rsssl_Query_Builder( $wpdb->base_prefix . 'rsssl_login_attempts' ) )->where(
				'attempt_type',
				'=',
				'country'
			);
			if ( ! empty( $filtered_list ) ) {
				$query_builder->where_in( 'attempt_value', $filtered_list );
			}

			// manual ad a filter value to the $data.
			$data_table = new Rsssl_Data_Table(
				$data,
				$query_builder
			);
			$data_table->set_select_columns(
				array(
					'attempt_value',
					'raw: ' . $wpdb->base_prefix . 'rsssl_login_attempts.id as id',
					'attempt_type',
					'raw: attempt_value as country_name',
					'raw: "" as region_name',
					'raw: ' . $wpdb->base_prefix . 'rsssl_login_attempts.status as status',
					'last_failed',
					"raw:DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(last_failed), 'UTC', '$timezone'), '%%H:%%i, %%M %%e') as datetime",
				)
			);

			// we already add a where selection in the query builder.

			$result         = $data_table
				->validate_sorting(
					array(
						'column'    => 'datetime',
						'direction' => 'desc',
					)
				)
				->validate_filter()
				->validate_pagination()
				->get_results();
			$result['post'] = $data;
			$result['test'] = $filtered_list;
			if ( isset( $search, $search_value ) ) {
				$result['search']       = $search;
				$result['search_value'] = $search_value;
			}

			// we now add the country name and region to the result.
			foreach ( $result['data'] as $key => $value ) {
				$result['data'][ $key ]->country_name = $this->get_country_name( $value->attempt_value );
				$result['data'][ $key ]->region_name  = $this->get_region_name( $this->build_country_continent_lookup_array( $value->attempt_value ) );
			}

			// if the sort value is region_name we need to sort the array by the region_name.
			if ( isset( $data['sortColumn'] ) && 'country_name' !== $data['sortColumn'] && 'region_name' === $data['sortColumn']['column'] ) {
				usort(
					$result['data'],
					static function ( $a, $b ) use ( $data ) {
						if ( 'asc' === $data['sortDirection'] ) {
							return $a->region_name <=> $b->region_name;
						}

						return $b->region_name <=> $a->region_name;
					}
				);
			}

			return $result;

		} catch ( Exception $e ) {
			return $this->json_response( false, $e->getMessage() );
		}
	}


	/**
	 * Get the list of blocked countries.
	 *
	 * @return array The list of blocked countries as WPDB results.
	 * @global wpdb $wpdb The WordPress database access object.
	 */
	private function get_blocked_countries(): array {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		return $wpdb->get_results(
			"SELECT 
							attempt_value
						FROM {$wpdb->base_prefix}rsssl_login_attempts 
							   WHERE attempt_type = 'country'"
		);
	}

	/**
	 * Get the list of blocked countries.
	 *
	 * @return array The list of blocked countries as WPDB results.
	 * @global wpdb $wpdb The WordPress database access object.
	 */
	private function get_blocked_countries_with_id(): array {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		return $wpdb->get_results(
			"SELECT 
    							id,
							attempt_value
						FROM {$wpdb->base_prefix}rsssl_login_attempts 
							   WHERE attempt_type = 'country'"
		);
	}

	/**
	 * This method is used to register API routes and handle API actions.
	 *
	 * @param  array  $response  The response data to be returned.
	 * @param  string $action  The action to be performed.
	 * @param  array  $data  Additional data for the action.
	 *
	 * @return array The modified or original response data.
	 * @throws Exception Throws an exception if data can not be handled properly.
	 */
	public function register_api_routes( array $response, string $action, array $data ): array {
		if ( ! rsssl_admin_logged_in() ) {
			return $response;
		}

		$action_handlers = array(
			'rsssl_limit_login_country' => array( $this, 'handle_geo_list' ),
			// If we need further actions we can add them here.
		);

		if ( isset( $action_handlers[ $action ] ) ) {
			return call_user_func( $action_handlers[ $action ], $data );
		}

		return $response;
	}

	/**
	 * Filter the columns of the given data array based on the search criteria.
	 *
	 * @param  array  $data  The original data array.
	 * @param  array  $search  The columns to search.
	 * @param  string $search_value  The value to search for.
	 *
	 * @return array The filtered data array.
	 */
	private function filter_search_columns( array $data, array $search, string $search_value ): array {
		return array_filter(
			$data,
			static function ( $value ) use ( $search, $search_value ) {
				foreach ( $search as $column ) {
					if ( isset( $value->$column ) && false !== stripos( $value->$column, $search_value ) ) {
						return true;
					}
				}

				return false;
			}
		);
	}
}

new Rsssl_Admin_Config_Countries();
