<?php

/**
 * @since 1.18.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<p><?php esc_html_e( 'Dear {customer_name},', 'motopress-appointment' ); ?></p>

<p><?php esc_html_e( 'Thanks for creating an account on {appointment}.', 'motopress-appointment' ); ?></p>

<h4><?php esc_html_e( 'You Account Details', 'motopress-appointment' ) ?></h4>

<p><?php esc_html_e( 'Login: {customer_account_login}', 'motopress-appointment' ); ?></p>

<p><?php esc_html_e( 'Password: {customer_account_password}', 'motopress-appointment' ); ?></p>

<p><a href="{customer_account_link}"><?php esc_html_e( 'Log in here', 'motopress-appointment' ); ?></a></p>

<p><?php esc_html_e( 'Thank you!', 'motopress-appointment' ); ?></p>