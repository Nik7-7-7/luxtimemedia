<?php

/**
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<p><?php esc_html_e( 'Dear {customer_name},', 'motopress-appointment' ); ?></p>

<p><?php esc_html_e( 'We have confirmed your appointment booking request #{booking_id}.', 'motopress-appointment' ); ?></p>

<p><?php esc_html_e( 'Scheduled services:', 'motopress-appointment' ); ?></p>
<ul>
{reservations_details}
</ul>

<p><?php esc_html_e( 'Total price', 'motopress-appointment' ); ?>: {booking_total_price}</p>

<p>{cancelation_details}</p>

<p><?php esc_html_e( 'Thank you and see you soon.', 'motopress-appointment' ); ?></p>
