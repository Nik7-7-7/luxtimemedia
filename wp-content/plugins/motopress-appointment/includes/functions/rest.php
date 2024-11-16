<?php

use MotoPress\Appointment\Structures\TimePeriod;
use MotoPress\Appointment\Utils\ParseUtils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param string $message
 * @return WP_Error
 *
 * @since 1.0
 */
function mpa_rest_request_error( $message ) {
	return new WP_Error( 'invalid_request', $message );
}

/**
 * @since 1.5.0
 *
 * @param string $message
 * @return WP_Error
 */
function mpa_rest_failure_error( $message ) {
	return new WP_Error( 'failed_request', $message );
}

/**
 * @param string|int $id
 * @return int
 *
 * @since 1.0
 */
function mpa_rest_sanitize_id( $id ) {
	return mpa_posint( $id );
}

/**
 * @param string|array $ids
 * @return int[]
 *
 * @since 1.0
 */
function mpa_rest_sanitize_ids( $ids ) {
	if ( is_string( $ids ) ) {
		$ids = explode( ',', $ids );
	}

	$ids = array_map( 'mpa_rest_sanitize_id', $ids );
	$ids = mpa_array_filter_reset( $ids );

	return $ids;
}

/**
 * @param string|DateTime $date
 * @return DateTime|null
 *
 * @since 1.0
 */
function mpa_rest_sanitize_date( $date ) {
	if ( is_string( $date ) ) {
		return mpa_parse_date( $date, null );
	} else {
		return $date;
	}
}

/**
 * @param string $dateString
 * @return string
 *
 * @since 1.0
 */
function mpa_rest_sanitize_date_string( $dateString ) {
	return mpa_validate_date( $dateString, '' );
}

/**
 * @param mixed $value
 * @return bool
 *
 * @since 1.2.1
 */
function mpa_rest_sanitize_bool( $value ) {
	return ParseUtils::parseBool( $value );
}

/**
 * @since 1.4.0
 *
 * @param mixed $input
 * @return array Valid cart items.
 */
function mpa_rest_sanitize_cart_items( $input ) {
	if ( empty( $input ) || ! is_array( $input ) ) {
		return array();
	}

	$cartItems = array();

	foreach ( $input as $item ) {
		if ( isset( $item['service_id'], $item['employee_id'], $item['location_id'], $item['date'], $item['time'] )
			&& mpa_validate_date( $item['date'] )
			&& TimePeriod::validate( $item['time'] )
		) {
			$date = mpa_parse_date( $item['date'] );

			$time = new TimePeriod( $item['time'] );
			$time->setDate( $date ); // "Reset" the date

			$capacity = isset( $item['capacity'] )
				? ParseUtils::parseInt( $item['capacity'], 1 )
				: 1;

			$cartItems[] = array(
				'service_id'  => mpa_posint( $item['service_id'] ),
				'employee_id' => mpa_posint( $item['employee_id'] ),
				'location_id' => mpa_posint( $item['location_id'] ),
				'date'        => $date,
				'time'        => $time,
				'capacity'    => $capacity,
			);
		}
	}

	return $cartItems;
}
