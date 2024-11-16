<?php

/**
 * @since 1.15.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<p><?php esc_html_e( 'Dear Administrator,', 'motopress-appointment' ); ?></p>

<p><?php esc_html_e( 'Booking is canceled by the customer.', 'motopress-appointment' ); ?></p>

<p>{booking_edit_link}</p>

<p><?php esc_html_e( 'Canceled services:', 'motopress-appointment' ); ?></p>
<ul>
	{reservations_details}
</ul>
