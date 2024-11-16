<?php
/**
 * Adjust cookie expiration to the setting in Really Simple Security
 * @param $expire
 *
 * @return float|int
 */
function rsssl_pro_login_session( $expire ) {
	$expiration_hours = rsssl_get_option('login_cookie_expiration', 48);
	return $expiration_hours * HOUR_IN_SECONDS;
}
add_filter ( 'auth_cookie_expiration', 'rsssl_pro_login_session', 999 ); //ensure priority
