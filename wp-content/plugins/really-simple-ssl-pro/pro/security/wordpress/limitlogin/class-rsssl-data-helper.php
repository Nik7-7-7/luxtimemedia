<?php

namespace RSSSL\Pro\Security\WordPress\Limitlogin;

use DateTimeZone;
use Exception;
use RSSSL\Pro\Security\DynamicTables\Rsssl_Data_Table;

/**
 * Validates the data table and returns the results.
 *
 * This method validates the provided data table by setting the where clause, validating the search input,
 * validating the sorting options, validating the filter options, validating the pagination options,
 * and finally returning the results.
 *
 * @param  array  $data  The data to be used in the query.
 * @param  Rsssl_Data_Table  $data_table  The data table object.
 * @param  array  $where  The where clause for the query.
 *
 * @return array  The results of the query.
 * @throws Exception If an error occurs during the validation process.
 */
class Rsssl_Data_Helper {
	/**
	 * Validates the data table and returns the results.
	 *
	 * This method validates the provided data table by setting the where clause, validating the search input,
	 * validating the sorting options, validating the filter options, validating the pagination options,
	 * and finally returning the results.
	 *
	 * @param  array            $data  The data to be used in the query.
	 * @param  Rsssl_Data_Table $data_table  The data table object.
	 * @param  array            $where  The where clause for the query.
	 *
	 * @return array  The results of the query.
	 * @throws Exception If an error occurs during the validation process.
	 */
	public static function validate_data_table_and_get_results( array $data, Rsssl_Data_Table $data_table, array $where ): array {
		return $data_table
			->set_where( $where )
			->validate_search()
			->validate_sorting(
				array(
					'column'    => 'last_failed',
					'direction' => 'desc',
				)
			)
			->validate_filter()
			->validate_pagination()
			->get_results();
	}

	/**
	 * Retrieves the timezone for a WordPress installation.
	 *
	 * This method looks for the timezone string option first. If the option exists and is not empty,
	 * it returns the corresponding DateTimeZone object. If the timezone string option does not exist or
	 * is empty, it calculates the timezone offset using the gmt_offset option and returns the corresponding
	 * DateTimeZone object.
	 *
	 * @return DateTimeZone The DateTimeZone object representing the timezone.
	 * @throws Exception If the timezone string option is invalid.
	 */
	public static function get_wordpress_timezone(): DateTimeZone {
		$timezone_string = get_option( 'timezone_string' );
		if ( ! empty( $timezone_string ) ) {
			return new DateTimeZone( $timezone_string );
		}

		$offset  = get_option( 'gmt_offset' );
		$offset  = (float) $offset;
		$hours   = (int) $offset;
		$minutes = ( $offset - $hours ) * 60;
		$offset  = sprintf( '%+03d:%02d', $hours, $minutes );

		return new DateTimeZone( $offset );
	}

	/**
	 * Converts a datetime string to the timezone of the WordPress installation.
	 *
	 * @param string $datetime The datetime string to convert.
	 * @param string $timezone The timezone to convert to.
	 *
	 * @return string The converted datetime string.
	 * @throws Exception If the timezone is invalid.
	 */
	public static function convert_timezone( string $datetime, string $timezone ): string {
		$date = new \DateTime( $datetime, new DateTimeZone( 'UTC' ) );
		$date->setTimezone( new DateTimeZone( $timezone ) );

		return $date->format( 'H:i, M j' );
	}
}
