<?php
defined( 'ABSPATH' ) or die();

/**
 * Test if wp-login.php is available. If not, we can assume the login URL has been changed already
 * @return bool
 */

function test_rsssl_wp_login_available(): bool {

	if ( ! get_option('rsssl_test_wp_login_available') ) {
		$wp_login_response = wp_remote_get( trailingslashit( site_url() ) . 'wp-login.php' );
		if ( is_array( $wp_login_response ) && ! is_wp_error( $wp_login_response ) ) {
			$response_code = wp_remote_retrieve_response_code( $wp_login_response );
			if ( $response_code !== 404 ) {
				update_option('rsssl_test_wp_login_available', true, false );
				return true;
			}
		}

		update_option('rsssl_test_wp_login_available', false, false );
		return false;
	}
	return get_option('rsssl_test_wp_login_available');
}