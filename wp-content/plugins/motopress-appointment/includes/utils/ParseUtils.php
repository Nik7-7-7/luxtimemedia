<?php

namespace MotoPress\Appointment\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.2
 */
class ParseUtils {

	/**
	 * @param mixed $value
	 * @return bool
	 *
	 * @since 1.2
	 */
	public static function parseBool( $value ) {
		return ValidateUtils::validateBool( $value );
	}

	/**
	 * @param mixed $value
	 * @param mixed $min Optional. Number of false.
	 * @param mixed $max Optional. Number of false.
	 * @return int
	 *
	 * @since 1.2
	 */
	public static function parseInt( $value, $min = false, $max = false ) {
		return static::parseNumber( $value, $min, $max, FILTER_VALIDATE_INT );
	}

	/**
	 * @param mixed $value
	 * @param mixed $min Optional. Number of false.
	 * @param mixed $max Optional. Number of false.
	 * @return float
	 *
	 * @since 1.2
	 */
	public static function parseFloat( $value, $min = false, $max = false ) {
		return static::parseNumber( $value, $min, $max, FILTER_VALIDATE_FLOAT );
	}

	/**
	 * @param mixed $value
	 * @param mixed $min Optional. Number or false.
	 * @param mixed $max Optional. Number or false.
	 * @param int $filter Optional. FILTER_VALIDATE_FLOAT by default.
	 * @return int|float
	 *
	 * @since 1.2
	 */
	public static function parseNumber( $value, $min = false, $max = false, $filter = FILTER_VALIDATE_FLOAT ) {
		$validValue = ValidateUtils::validateNumber( $value, $min, $max, $filter );

		if ( false === $validValue ) {
			$validValue = 0;

			if ( false !== $min ) {
				$validValue = max( $min, $validValue );
			}

			if ( false !== $max ) {
				$validValue = min( $validValue, $max );
			}
		}

		return $validValue;
	}

	/**
	 * @param mixed $value
	 * @param mixed $min Optional. Number of false. 0 by default.
	 * @param mixed $max Optional. Number of false.
	 * @return float
	 *
	 * @since 1.2
	 */
	public static function parsePrice( $value, $min = 0, $max = false ) {
		return static::parseNumber( $value, $min, $max, FILTER_VALIDATE_FLOAT );
	}

	/**
	 * @param int|string $value
	 * @return int
	 *
	 * @since 1.2
	 */
	public static function parseId( $value ) {
		return static::parseInt( $value, 0 );
	}

	/**
	 * @param array|string $value
	 * @return int[]
	 *
	 * @since 1.2
	 */
	public static function parseIds( $value ) {
		if ( is_string( $value ) ) {
			$value = mpa_explode( $value );
		}

		$ids = array_map( array( __CLASS__, 'parseId' ), $value );
		$ids = mpa_array_filter_reset( $ids );

		return $ids;
	}

	/**
	 * @param string $value
	 * @return string
	 *
	 * @since 1.2
	 */
	public static function parseSlug( $value ) {
		return sanitize_text_field( $value );
	}

	/**
	 * @param array|string $value
	 * @return string[]
	 *
	 * @since 1.2
	 */
	public static function parseSlugs( $value ) {
		if ( is_string( $value ) ) {
			$value = mpa_explode( $value );
		}

		$slugs = array_map( array( __CLASS__, 'parseSlug' ), $value );

		return $slugs;
	}

	/**
	 * @param int|string $value
	 * @return int|string
	 *
	 * @since 1.2
	 */
	public static function parseSlugOrId( $value ) {
		if ( is_numeric( $value ) ) {
			return static::parseId( $value );
		} elseif ( '' !== $value ) {
			return static::parseSlug( $value );
		} else {
			return 0; // Return as ID by default
		}
	}

	/**
	 * Combination of parseIds() + parseSlugs(), where every item can be either
	 * post ID or post slug (name).
	 *
	 * @param array|string $value
	 * @return array Array, where all numeric items parsed as IDs.
	 *
	 * @since 1.2
	 */
	public static function parseSlugsAndIds( $value ) {
		if ( is_string( $value ) ) {
			$value = mpa_explode( $value );
		}

		$posts = array_map( array( __CLASS__, 'parseSlugOrId' ), $value );

		// Filter zeros (0) and empty strings ("")
		$posts = mpa_array_filter_reset( $posts );

		return $posts;
	}

	/**
	 * @param string $value
	 * @param string $default Optional. 'ASC' by default.
	 * @return 'ASC'|'DESC'
	 *
	 * @since 1.2
	 */
	public static function parseOrder( $value, $default = 'ASC' ) {
		$order = strtoupper( trim( $value ) );

		if ( 'ASC' === $order || 'DESC' === $order ) {
			return $order;
		} else {
			return $default;
		}
	}
}
