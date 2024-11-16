<?php

namespace MotoPress\Appointment\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class ValidateUtils {

	/**
	 * @param mixed $value
	 * @return bool
	 *
	 * @since 1.2
	 */
	public static function validateBool( $value ) {
		return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * @param mixed $value
	 * @param mixed $min Optional. Number or false.
	 * @param mixed $max Optional. Number or false.
	 * @return int|false
	 *
	 * @since 1.2
	 */
	public static function validateInt( $value, $min = false, $max = false ) {
		return static::validateNumber( $value, $min, $max, FILTER_VALIDATE_INT );
	}

	/**
	 * @param mixed $value
	 * @param mixed $min Optional. Number or false.
	 * @param mixed $max Optional. Number or false.
	 * @return float|false
	 *
	 * @since 1.2
	 */
	public static function validateFloat( $value, $min = false, $max = false ) {
		return static::validateNumber( $value, $min, $max, FILTER_VALIDATE_FLOAT );
	}

	/**
	 * @param mixed $value
	 * @param mixed $min Optional. Number or false.
	 * @param mixed $max Optional. Number or false.
	 * @param int $filter Optional. FILTER_VALIDATE_FLOAT by default.
	 * @return mixed
	 *
	 * @since 1.0
	 */
	public static function validateNumber( $value, $min = false, $max = false, $filter = FILTER_VALIDATE_FLOAT ) {
		$filterOptions = array();

		if ( false !== $min ) {
			$filterOptions['min_range'] = $min;
		}

		if ( false !== $max ) {
			$filterOptions['max_range'] = $max;
		}

		if ( ! empty( $filterOptions ) ) {
			return filter_var( $value, $filter, array( 'options' => $filterOptions ) );
		} else {
			return filter_var( $value, $filter );
		}
	}

	/**
	 * @param mixed $value
	 * @param mixed $min Optional. Number or false. 0 by default.
	 * @param mixed $max Optional. Number or false.
	 * @return float|false
	 *
	 * @since 1.2
	 */
	public static function validatePrice( $value, $min = 0, $max = false ) {
		return static::validateNumber( $value, $min, $max, FILTER_VALIDATE_FLOAT );
	}
}
