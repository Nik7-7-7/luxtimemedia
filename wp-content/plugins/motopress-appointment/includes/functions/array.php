<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param array $array
 * @param callable $callback Optional. Null by default.
 * @return array
 *
 * @since 1.0
 */
function mpa_array_filter_reset( $array, $callback = null ) {

	if ( is_callable( $callback ) ) {
		$values = array_filter( $array, $callback );
	} else {
		// Don't pass null to callback or will get error
		$values = array_filter( $array );
	}

	return array_values( $values );
}

/**
 * Applies the callback to the items and resets the indexes of the result array.
 *
 * @param callable $callback
 * @param array $items
 * @return array
 *
 * @since 1.0
 */
function mpa_array_map_reset( $callback, $items ) {
	$map = array_map( $callback, $items );
	$map = array_filter( $map );

	return array_values( $map ); // Get rid of gaps in keys after array_filter()
}

/**
 * @param array $array
 * @return array
 *
 * @since 1.0
 */
function mpa_array_unique_reset( $array ) {
	return array_values( array_unique( $array ) );
}

/**
 * @param array $array
 * @param int|string $key
 * @return array
 *
 * @since 1.0
 */
function mpa_array_group_by( $array, $key ) {
	$array2 = array();

	foreach ( $array as $nestedArray ) {
		$nestedValue = $nestedArray[ $key ];

		$array2[ $nestedValue ][] = $nestedArray;
	}

	return $array2;
}

/**
 * @param array $array
 * @return mixed|false The value of the first key or False if the array is empty.
 *
 * @since 1.0
 */
function mpa_first_key( $array ) {
	// array_keys() + reset() is faster way than using foreach cycle,
	// especially on big arrays
	$keys = array_keys( $array );
	return reset( $keys );
}

/**
 * @param array $array
 * @return array [First key, First value]
 *
 * @since 1.0
 */
function mpa_first_pair( $array ) {
	$firstValue = reset( $array );
	$firstKey   = key( $array );

	return array( $firstKey, $firstValue );
}

/**
 * @param array $array1
 * @param array $array2
 * @return array Removed values.
 *
 * @since 1.2
 */
function mpa_array_diff_all( &$array1, &$array2 ) {
	$intersection = array_intersect( $array1, $array2 );

	$array1 = array_diff( $array1, $intersection );
	$array2 = array_diff( $array2, $intersection );

	return $intersection;
}

/**
 * @param string $string
 * @param string $delimiter Optional. ',' by default.
 * @return array
 *
 * @since 1.2
 */
function mpa_explode( $string, $delimiter = ',' ) {
	$values = explode( $delimiter, $string );
	$values = array_map( 'trim', $values );

	// Filter empty strings after the trim() function
	$values = array_filter( $values, 'mpa_filter_empty_string' );

	// Reset keys
	return array_values( $values );
}

/**
 * @param array $array
 * @param mixed $value
 *
 * @since 1.2.1
 */
function mpa_array_remove( &$array, $value ) {

	$key = array_search( $value, $array );

	if ( false !== $key ) {
		unset( $array[ $key ] );
	}
}

/**
 * @param array $vars
 * @return array New array with cloned values.
 *
 * @since 1.2.1
 */
function mpa_array_clone( $vars ) {
	return array_map(
		function ( $var ) {
			return clone $var;
		},
		$vars
	);
}

/**
 * Merges nested arrays into one finel array. Preserves numeric indexes.
 *
 * @param array $array
 * @return array
 *
 * @since 1.2.1
 */
function mpa_combine_subarrays( $array ) {
	$newArray = array();

	foreach ( $array as $subarray ) {
		// array_merge() resets numeric indexes
		$newArray = array_replace( $newArray, $subarray );
	}

	return $newArray;
}
