<?php
/**
 * Class Rsssl_Login_Data_Manager
 *
 * Get a list of items based on the given data.
 *
 * @package RSSSL\Pro\Security\WordPress\Limitlogin
 * @since 7.3.0
 * @author Really Simple Plugins
 * @company Really Simple Plugins
 */

namespace RSSSL\Pro\Security\WordPress\Limitlogin;

use Exception;
use RSSSL\Pro\Security\DynamicTables\Rsssl_Data_Table;
use RSSSL\Pro\Security\DynamicTables\Rsssl_Query_Builder;
use RSSSL\Pro\Security\WordPress\Limitlogin\Rsssl_Data_Helper;

/**
 * Get a list of items based on the given data.
 *
 * @param  array  $data  The data to filter the list.
 *
 * @return array The filtered list of items.
 * @throws Exception If an error occurs during the retrieval process.
 */
class Rsssl_Login_Data_Manager {
	/**
	 * Get a list of items based on the given data.
	 *
	 * @param  array $data  The data to filter the list.
	 *
	 * @return array The filtered list of items.
	 * @throws Exception If an error occurs during the retrieval process.
	 */
	public function get_list( array $data ): array {
		return $this->get_list_base( $data, array( 'attempt_type', '=', 'source_ip' ) );
	}

	/**
	 * Get a list of users matching the provided data.
	 *
	 * Retrieves a list of users based on the provided data array and a specific condition
	 * where the attempt type is equal to 'username'.
	 *
	 * @param  array $data  The array containing the data used to filter the users.
	 *                   It should include an entry for the attempt type.
	 *
	 * @return array An array containing the list of users who match the provided data.
	 * @throws Exception If an error occurs during the retrieval process.
	 */
	public function get_user_list( array $data ): array {
		return $this->get_list_base( $data, array( 'attempt_type', '=', 'username' ) );
	}

	/**
	 * Get a list of blocked countries based on the given data.
	 *
	 * @param  array $data  The data to filter the list.
	 *
	 * @return array The filtered list of blocked countries.
	 * @throws Exception If an error occurs during the retrieval process.
	 */
	public function get_blocked_country_list( array $data ): array {
		return $this->get_list_base( $data, array( 'attempt_type', '=', 'country' ) );
	}

	/**
	 * Retrieves a list of data based on the given parameters and conditions.
	 *
	 * @param  array|null $data  An optional array of data to be used for filtering, sorting, and pagination.
	 * @param  array      $where  An array of conditions to filter the data.
	 *
	 * @return array The retrieved data.
	 *
	 * @throws Exception If an error occurs during the retrieval process.
	 */
	private function get_list_base( array $data, array $where ): array {
		global $wpdb;
		try {
			$timezone = Rsssl_Data_Helper::get_wordpress_timezone()->getName();

			if ( isset( $data['sortColumn']['column'] ) && 'datetime' === $data['sortColumn']['column'] ) {
				$data['sortColumn']['column'] = 'last_failed';
			}

			$data_table = new Rsssl_Data_Table(
				$data,
				new Rsssl_Query_Builder( $wpdb->base_prefix . 'rsssl_login_attempts' )
			);

			$data_table->set_select_columns(
				array(
					'id',
					'attempt_value',
					'attempt_type',
					'status',
					'last_failed',
					"raw:DATE_FORMAT(FROM_UNIXTIME(last_failed), '%%H:%%i, %%M %%e') as datetime",
				)
			);

			$result = Rsssl_Data_Helper::validate_data_table_and_get_results( $data, $data_table, $where );

			if ( isset( $data['sortColumn']['column'] ) && 'timestamp' === $data['sortColumn']['column'] ) {
				$data['sortColumn']['column'] = 'datetime';
			}

			$result['post'] = $data;

			foreach ( $result['data'] as $key => $value ) {
				if ( is_string( $value->datetime ) ) {
					$result['data'][ $key ]->datetime = Rsssl_Data_Helper::convert_timezone( $value->datetime, $timezone );
				}
			}

			return $result;
		} catch ( Exception $e ) {
			return array(
				'error' => $e->getMessage(),
			);
		}
	}
}
