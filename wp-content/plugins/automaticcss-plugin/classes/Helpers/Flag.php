<?php
/**
 * Automatic.css Flag file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Helpers;

/**
 * Automatic.css Flag class.
 */
class Flag {

	/**
	 * Checks if a development flag is on.
	 * A flag is on when the constant is defined and its value is true.
	 *
	 * @param string $flag_name The name of the flag to check.
	 * @return boolean
	 */
	public static function is_on( $flag_name ) {
		return defined( $flag_name ) ? (bool) constant( $flag_name ) : false;
	}

}
