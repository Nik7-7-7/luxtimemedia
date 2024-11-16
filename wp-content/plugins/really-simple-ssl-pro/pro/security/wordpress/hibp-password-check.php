<?php
defined( 'ABSPATH' ) or die();

/**
 * Check password on user registration and password change
 */
function rsssl_check_password_on_set( $user_id, $password = '' ) {
	if ( empty( $password ) ) {
		$user = get_user_by( 'ID', $user_id );
		if ( $user && $user->user_pass ) {
			$password = $user->user_pass;
		} else {
			return;
		}
	}

	rsssl_check_password_pwned( $password );
}

add_action( 'user_register', 'rsssl_check_password_on_set', 10, 2 );
add_action( 'password_reset', 'rsssl_check_password_on_set', 10, 2 );
add_action( 'profile_update', 'rsssl_check_password_on_set', 10, 2 );


/**
 * Check if a password has been pwned using the Have I Been Pwned API
 *
 * @param string | WP_User $password The password or its hash to check
 *
 * @return int The number of times the password has been pwned (0 if not pwned)
 */
function rsssl_check_password_pwned( $password ) {

	$hash = '';

	// Password can be a regular password, an array with a user_pass key or a WP_User object depending on where the password has been changed
	if ( $password instanceof WP_User ) {
		// wp user object
		if ( isset( $password->data->user_pass ) ) {
			$hash = strtoupper( sha1( $password->data->user_pass ) );
		}
	} elseif ( is_array( $password ) && isset( $password['user_pass'] ) ) {
		$hash = strtoupper( sha1( $password['user_pass'] ) );
	} else {
		// The input is a regular password
		$hash = strtoupper( sha1( $password ) );
	}

	// Get the first 5 characters of the hash
	$hash_prefix = substr( $hash, 0, 5 );

	// Get the rest of the hash
	$hash_suffix = substr( $hash, 5 );

	// Prepare the API request
	$url = 'https://api.pwnedpasswords.com/range/' . $hash_prefix;

	// Send the request
	$response = wp_remote_get( $url );

	// Check for errors
	if ( is_wp_error( $response ) ) {
		// Continue as if the password hasn't been pwned
		return 0;
	}

	// Get the response body
	$body = wp_remote_retrieve_body( $response );

	// Split the response into lines
	$lines = explode( "\n", $body );

	// Check each line for a match
	foreach ( $lines as $line ) {
		list( $suffix, $count ) = explode( ':', $line );
		if ( strcasecmp( $suffix, $hash_suffix ) === 0 ) {
			return intval( trim( $count ) );
		}
	}

	// If no match found, the password hasn't been pwned
	return 0;
}

/**
 * Check password on user registration form submission
 */
function rsssl_check_password_on_registration( $errors, $sanitized_user_login, $user_email ) {
	if ( ! empty( $_POST['user_pass'] ) ) {
		$pwned_count = rsssl_check_password_pwned( $_POST['user_pass'] );
		if ( $pwned_count > 0 ) {
			$errors->add( 'rsssl_password_pwned',
				sprintf( __( "Warning: This password has been found in %d data breaches. Please choose a different password.", "really-simple-ssl" ), $pwned_count )
			);
		}
	}

	return $errors;
}

add_filter( 'registration_errors', 'rsssl_check_password_on_registration', 10, 3 );

/**
 * Check password on password reset form submission
 */
function rsssl_check_password_on_reset( $errors, $user ) {
	if ( ! empty( $_POST['pass1'] ) ) {
		$pwned_count = rsssl_check_password_pwned( $_POST['pass1'] );
		if ( $pwned_count > 0 ) {
			// Add our custom error
			$custom_error = sprintf( __( "Warning: This password has been found in %d data breaches. Please choose a different password.", "really-simple-ssl" ), $pwned_count );
			$errors->add( 'rsssl_password_pwned', $custom_error );
			// Add filter to modify the error message
			add_filter( 'login_errors', function ( $error ) use ( $custom_error ) {
				return $custom_error;
			} );
		}
	}

	return $errors;
}

add_action( 'validate_password_reset', 'rsssl_check_password_on_reset', 10, 2 );

/**
 * Check password on profile update form submission
 */
function rsssl_check_password_on_profile_update( $errors, $update, $user ) {
	if ( ! empty( $_POST['pass1'] ) ) {
		$pwned_count = rsssl_check_password_pwned( $_POST['pass1'] );
		if ( $pwned_count > 0 ) {
			$errors->add( 'rsssl_password_pwned',
				sprintf( __( "Warning: This password has been found in %d data breaches. Please choose a different password.", "really-simple-ssl" ), $pwned_count )
			);
		}
	}

	return $errors;
}

add_filter( 'user_profile_update_errors', 'rsssl_check_password_on_profile_update', 10, 3 );