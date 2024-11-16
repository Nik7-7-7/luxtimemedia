<?php
/**
 * Interface Rsssl_Country_Contract
 *
 * This interface defines the contract for fetching country data.
 *
 * @package Really-Simple-SSL
 * @author Marcel Santing <marcel@really-simple-plugins.com>
 * @company Really Simple Plugins
 */

namespace RSSSL\Pro\Security\WordPress\Contracts;

use Exception;
use RSSSL\Pro\Security\DynamicTables\Rsssl_Data_Table;

/**
 * Interface Rsssl_Country_Contract
 *
 * This interface includes a single method, fetch_list(), that returns an array of country data.
 * It is recommended that classes implementing this interface should also use the Rsssl_Country_Trait
 * which provides the common implementation for the get_countries() method.
 *
 *    Example usage:
 *
 *    class CountryClass implements Rsssl_Country_Contract {
 *        use Rsssl_Country_Trait;
 *    }
 *
 * @package Really-Simple-SSL
 * @author Marcel Santing <marcel@really-simple-plugins.com>
 * @company Really Simple Plugins
 */
interface Rsssl_Country_Contract {

	/**
	 * Fetches a list of items based on the specified list type and optional data.
	 *
	 * @param  string     $list_type  The type of list to fetch.
	 * @param  array|null $data  Optional data to filter or modify the fetched list.
	 *
	 * @return array The fetched list of items.
	 */
	public function fetch_list( string $list_type, array $data = null ): array;

	/**
	 * This function is used to set up the data table array for the Really Simple Security Pro plugin.
	 *
	 * @param array $data The original data array.
	 * @param array $countries The countries array.
	 *
	 * @return Rsssl_Data_Table The set-up data table array.
	 * @throws Exception Throws an exception if filters or data does not match.
	 */
	public function setup_data_table_array( array $data, array $countries ): Rsssl_Data_Table;

	/**
	 * This method is used to filter an original array based on a filter condition.
	 *
	 * @param  array $original_array  The original array that needs to be filtered.
	 * @param  bool  $ignore_filter  A boolean value indicating whether to ignore the filter condition and return the original array as it is.
	 *
	 * @return array Returns the filtered array if the $ignore_filter parameter is false, otherwise returns the original array.
	 */
	public function filter_array( array $original_array, bool $ignore_filter ): array;

	/**
	 * Handles the geo list based on the specified Rsssl_Geo_Block object and data array.
	 *
	 * @param array $data The data array containing additional parameters for handling the list.
	 *
	 * @return array The fetched or generated geo list.
	 * @throws Exception Throws an exception when an invalid filter value is specified.
	 */
	public function handle_geo_list( array $data ): array;

	/**
	 * This function is used to create an array with country names.
	 *
	 * @param int    $id The ID of the country.
	 * @param string $iso2_code The ISO 2 code of the country.
	 * @param string $country_name The name of the country.
	 *
	 * @return array The array with the country details.
	 */
	public function create_array_with_country_name( int $id, string $iso2_code, string $country_name ): array;

	/**
	 * This function is used to create an array with continent name.
	 *
	 * @param int    $id The ID of the country.
	 * @param string $iso2_code The ISO 2 code of the country.
	 * @param string $continent_name The name of the continent.
	 *
	 * @return array Returns an array with the country details including the continent name.
	 */
	public function create_array_with_continent_name( int $id, string $iso2_code, string $continent_name ): array;
}
