<?php

/**
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<p><?php esc_html_e( 'Dear {customer_name},', 'motopress-appointment' ); ?></p>

<p><?php esc_html_e( 'Your appointment booking request #{booking_id} is waiting for our confirmation.', 'motopress-appointment' ); ?></p>

<p><?php esc_html_e( 'Requested services:', 'motopress-appointment' ); ?></p>
<ul>
{reservations_details}
</ul>

<p><?php esc_html_e( 'Total price', 'motopress-appointment' ); ?>: {booking_total_price}</p>

<p>{cancelation_details}</p>

<p><?php esc_html_e( 'Stay in touch.', 'motopress-appointment' ); ?></p>
