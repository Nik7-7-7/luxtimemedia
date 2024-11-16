<?php

/**
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<p><?php esc_html_e( 'Dear Administrator,', 'motopress-appointment' ); ?></p>

<p><?php esc_html_e( 'You got a new confirmed appointment booking.', 'motopress-appointment' ); ?></p>

<p>{booking_edit_link}</p>

<p><?php esc_html_e( 'Scheduled services:', 'motopress-appointment' ); ?></p>
<ul>
{reservations_details}
</ul>

<p><?php esc_html_e( 'Client:', 'motopress-appointment' ); ?><br>{customer_name}<br>{customer_email}<br>{customer_phone}</p>
<p><?php esc_html_e( 'Booking notes', 'motopress-appointment' ); ?>:<br>{customer_notes}</p>
<p><?php esc_html_e( 'Total price', 'motopress-appointment' ); ?>: {booking_total_price}</p>
