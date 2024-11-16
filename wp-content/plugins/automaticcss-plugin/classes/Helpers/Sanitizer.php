<?php
/**
 * Automatic.css Sanitizer file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Helpers;

/**
 * Automatic.css Sanitizer class.
 */
class Sanitizer {

	/**
	 * Sanitize a value for use in HTML output.
	 *
	 * @param string|int|float $value The value to sanitize.
	 * @param int|false        $decimals The number of decimals to use. If false, the number of decimals will be determined automatically.
	 * @return string
	 */
	public static function get_sanitized_value( $value, $decimals = false ) {
		Logger::log( sprintf( '%s: sanitizing %s with %d decimals', __METHOD__, $value, $decimals ) );
		$sanitized_value = (string) $value; // assume it's a string and needs no sanitization.
		$decimals = false === $decimals ? self::get_decimal_places( $value ) : (int) $decimals;
		Logger::log( sprintf( '%s: %d decimals', __METHOD__, $decimals ) );
		if ( 0 === $decimals ) {
			// it's an integer.
			$sanitized_value = (string) (int) $value;
			Logger::log( sprintf( '%s: sanitized as int: %s', __METHOD__, $sanitized_value ) );
		} else if ( $decimals > 0 ) {
			// it's a float.
			$sanitized_value = number_format( (float) $value, $decimals, '.', '' );
			Logger::log( sprintf( '%s: sanitized as float: %s', __METHOD__, $sanitized_value ) );
		}
		return $sanitized_value;
	}

	/**
	 * Get the number of decimals in a given number.
	 *
	 * @param float|int $value The value to get the number of decimals from.
	 * @return int|false
	 */
	public static function get_decimal_places( $value ) {
		$localconv = localeconv();
		$decimal_separator = $localconv['decimal_point'];
		if ( is_numeric( $value ) && false !== strpos( $value, $decimal_separator ) ) {
			return strlen( substr( $value, strpos( $value, $decimal_separator ) + 1 ) );
		}
		return false;
	}

}
