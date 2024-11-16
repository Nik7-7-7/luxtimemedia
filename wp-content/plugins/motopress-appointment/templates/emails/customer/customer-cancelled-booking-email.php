<?php

/**
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<p><?php esc_html_e( 'Dear {customer_name},', 'motopress-appointment' ); ?></p>

<p><?php esc_html_e( 'Your appointment has been canceled.', 'motopress-appointment' ); ?></p>

<p><?php esc_html_e( 'Canceled services:', 'motopress-appointment' ); ?></p>
<ul>
{reservations_details}
</ul>

<p><?php esc_html_e( 'Let us know if we can do anything else for you.', 'motopress-appointment' ); ?></p>
