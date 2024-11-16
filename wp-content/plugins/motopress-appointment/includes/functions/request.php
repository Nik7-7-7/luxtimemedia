<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param string $action Action name. May be unprefixed.
 * @param string $nonceField Optional. 'mpa_nonce' by default.
 * @return bool
 *
 * @since 1.0
 */
function mpa_verify_nonce( $action, $nonceField = 'mpa_nonce' ) {

	if ( ! isset( $_REQUEST[ $nonceField ] ) ) {
		return false;
	}

	$action = mpa_prefix( $action );
	$nonce  = $_REQUEST[ $nonceField ];

	return wp_verify_nonce( $nonce, $action );
}
