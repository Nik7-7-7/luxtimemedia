<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return int
 *
 * @since 1.0
 */
function mpa_current_year() {
	return (int) date( 'Y' );
}

/**
 * Public date format, set in Settings > General.
 *
 * @return string
 *
 * @since 1.0
 */
function mpa_date_format() {
	return mpapp()->settings()->getDateFormat();
}

/**
 * @param DateTime $date
 * @param string $format: public (by default), internal ('Y-m-d') or custom date format.
 * @return string
 *
 * @since 1.0
 */
function mpa_format_date( $date, $format = 'public' ) {

	if ( 'internal' === $format ) {

		return $date->format( 'Y-m-d' );

	} elseif ( 'public' === $format ) {

		$dateUTC = new DateTime(
			$date->format( 'Y-m-d 00:00:00' ),
			new \DateTimeZone( 'UTC' )
		);

		return date_i18n(
			mpapp()->settings()->getDateFormat(),
			$dateUTC->getTimestamp()
		);

	} else {

		return $date->format( $format );
	}
}

/**
 * @param string|DateTime $date Only the internal format is acceptable as a
 *     string: 'Y-m-d'.
 * @param mixed $default Optional. False by default (same return value as in the
 *     DateTime::createFromFormat()).
 * @return DateTime|mixed
 *
 * @since 1.0
 * @since 1.2.1 the argument <code>$date</code> accepts DateTime object.
 */
function mpa_parse_date( $date, $default = false ) {

	if ( '' === $date ) {
		$date = false;
	} elseif ( is_string( $date ) ) {
		$date = DateTime::createFromFormat( 'Y-m-d', $date, wp_timezone() );
	}

	// Reset current time
	if ( false !== $date ) {
		$date->setTime( 0, 0 );
	} else {
		$date = $default;
	}

	return $date;
}

/**
 * @param string $dateString Only the internal format is acceptable - 'Y-m-d'.
 * @param mixed $default Optional. False by default.
 * @return string|false Valid string or false.
 *
 * @since 1.0
 */
function mpa_validate_date( $dateString, $default = false ) {

	$dateString = trim( $dateString );

	$datePattern = mpa_validate_date_pattern();
	$isValid     = (bool) preg_match( "/^{$datePattern}$/", $dateString );

	if ( $isValid ) {
		return $dateString;
	} else {
		return $default;
	}
}

/**
 * @return string Validation pattern for internal date format ('Y-m-d').
 *
 * @since 1.0
 */
function mpa_validate_date_pattern() {
	return '\\d{4}-\\d{2}-\\d{2}';
}

/**
 * @param string $modifier Optional. Modifier like '+1 day'. Empty by default.
 * @return DateTime
 *
 * @since 1.0
 */
function mpa_today( $modifier = '' ) {
	$date = new DateTime( 'today', wp_timezone() );

	if ( ! empty( $modifier ) ) {
		$date->modify( $modifier );
	}

	return $date;
}

/**
 * @param DateTime $origin
 * @param DateTime $target
 * @param string $units Optional. Only 'minutes' are available at the moment.
 * @return int|false Difference <code>$target - $origin</code>. False in case of
 *     an error.
 *
 * @since 1.2
 */
function mpa_date_diff( $origin, $target, $units = 'minutes' ) {
	if ( 'minutes' != $units ) {
		return false;
	}

	$originValue = (int) $origin->format( 'G' ) * 60 + (int) $origin->format( 'i' );
	$targetValue = (int) $target->format( 'G' ) * 60 + (int) $target->format( 'i' );

	return $targetValue - $originValue;
}
