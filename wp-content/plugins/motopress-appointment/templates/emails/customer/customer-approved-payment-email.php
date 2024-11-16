<?php

/**
 * @since 1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<p><?php esc_html_e( 'Dear {customer_name},', 'motopress-appointment' ); ?></p>

<p><?php esc_html_e( 'Your appointment has been booked.', 'motopress-appointment' ); ?></p>

<p><?php esc_html_e( 'Scheduled services:', 'motopress-appointment' ); ?></p>
<ul>
{reservations_details}
</ul>

<p><?php esc_html_e( 'Cost and payment information', 'motopress-appointment' ); ?>:</p>
<p><?php esc_html_e( 'Total price', 'motopress-appointment' ); ?>: {booking_total_price}</p>
<p><?php esc_html_e( 'Already paid', 'motopress-appointment' ); ?>: {booking_already_paid}</p>
<p><?php esc_html_e( 'Left to pay', 'motopress-appointment' ); ?>:  {booking_left_to_pay}</p>
<p>{booking_payments}</p>

<p>{cancelation_details}</p>

<p><?php esc_html_e( 'See you soon.', 'motopress-appointment' ); ?></p>
