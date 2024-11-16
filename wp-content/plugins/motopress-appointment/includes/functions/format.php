<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ceil the value to the nearest full step.
 *
 * @param int $value
 * @param int $step
 * @return int
 *
 * @since 1.0
 */
function mpa_ceil_to_step( $value, $step ) {
	if ( 0 != $value % $step ) {
		$value = $value - ( $value % $step ) + $step;
	}

	return $value;
}

/**
 * @param string $string
 * @return string|int
 *
 * @since 1.0
 */
function mpa_maybe_intval( $string ) {
	return is_numeric( $string ) ? intval( $string ) : $string;
}

/**
 * @param int|string $value
 * @return int The number in range [0; oo)
 *
 * @since 1.0
 */
function mpa_posint( $value ) {
	return max( 0, intval( $value ) );
}

/**
 * @param bool $value
 *
 * @return string
 *
 * @since 1.19.0
 */
function mpa_bool_to_str( bool $value ): string {
	return $value ? 'true' : 'false';
}

/**
 * @param string $string
 * @param string $prefix Optional. 'public', 'private', 'metabox', 'widget',
 *     'none' or custom prefix string. 'public' by default.
 * @return string
 *
 * @since 1.0
 * @since 1.3 added the <code>'metabox'</code> prefix.
 * @since 1.3 added the <code>'widget'</code> prefix.
 * @since 1.3 added the <code>'none'</code> prefix.
 * @since 1.3 added the custom prefix support.
 */
function mpa_prefix( $string, $prefix = 'public' ) {
	if ( in_array( $prefix, array( 'public', 'private', 'metabox', 'widget', 'none' ) ) ) {
		switch ( $prefix ) {
			case 'public':
				$prefix = 'mpa_';
				break;

			case 'private':
			case 'metabox':
				$prefix = '_mpa_';
				break;

			case 'widget': // Leave it to AbstractWidget::get_field_name()
			case 'none':
				$prefix = '';
				break;
		}

		// Remove old prefix
		$string = mpa_unprefix( $string );
	} else {
		$string = mpa_unprefix( $string, $prefix );
	}

	// Add new prefix
	return $prefix . $string;
}

/**
 * @param string $string
 * @param string $prefix Optional. Custom prefix to remove.
 * @return string
 *
 * @since 1.0
 * @since 1.3 added the <code>prefix</code> argument.
 */
function mpa_unprefix( $string, $prefix = '' ) {

	if ( ! empty( $prefix ) ) {
		if ( strpos( $string, $prefix ) === 0 ) {
			return substr( $string, strlen( $prefix ) ); // Remove the custom prefix
		}
	} else {
		if ( strpos( $string, 'mpa_' ) === 0 ) {
			return substr( $string, 4 ); // Remove the publc prefix
		} elseif ( 0 === strpos( $string, '_mpa_' ) ) {
			return substr( $string, 5 ); // Remove the private prefix
		}
	}

	// already without the prefix
	return $string;
}

/**
 * @param array $array
 * @return array
 *
 * @since 1.2
 */
function mpa_evaluate_numbers( $array ) {
	return array_map( 'mpa_maybe_intval', $array );
}

/**
 * @param string $html
 * @return string
 *
 * @since 1.2
 */
function mpa_strip_html_whitespaces( $html ) {
	// Replace newlines, tabs and multiple spaces with one space (in text
	// and attributes)
	$output = preg_replace( '/\\s+/', ' ', $html );

	// Remove spaces before and after the tag
	$output = preg_replace( '/(?<=\\>)\\s+|\\s+(?=\\<)/', '', $output );

	if ( ! is_null( $output ) ) {
		return $output;
	} else {
		return $html;
	}
}

/**
 * @param string $string Slug, HTML ID or any other string.
 * @return string Class name, like: 'SampleClass'.
 *
 * @since 1.2.1
 */
function mpa_str_to_class_name( $string ) {
	// ['admin', 'pending', 'booking', 'email']
	$segments = preg_split( '/[\\W_]+/', $string );
	$segments = array_filter( $segments, 'mpa_filter_empty_string' );

	// ['Admin', 'Pending', 'Booking', 'Email']
	$segments = array_map( 'ucfirst', $segments );

	// 'AdminPendingBookingEmail'
	$className = implode( '', $segments );

	return $className;
}

/**
 * @param string $string Slug, HTML ID or any other string.
 * @return string Method name, like: 'sampleMethod'.
 *
 * @since 1.2.1
 */
function mpa_str_to_method_name( $string ) {
	// 'adminPendingBookingEmail'
	return lcfirst( mpa_str_to_class_name( $string ) );
}

/**
 * @param string $text
 * @return string
 *
 * @since 1.2.1
 */
function mpa_kses_link( $text ) {
	return wp_kses(
		$text,
		array(
			'a' => array(
				'href'   => array(),
				'target' => array(),
			),
		)
	);
}
