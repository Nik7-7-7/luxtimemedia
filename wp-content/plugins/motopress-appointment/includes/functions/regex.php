<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param string $attribute
 * @param string $html
 * @param mixed $default Optional. '' by default.
 * @return mixed Parsed or default value.
 *
 * @since 1.3
 */
function mpa_parse_html_attr( $attribute, $html, $default = '' ) {

	$pattern = '/' . $attribute . '="([^"]*)"/i';

	$matched = (bool) preg_match( $pattern, $html, $matches );

	if ( $matched ) {
		return $matches[1];
	} else {
		return $default;
	}
}
