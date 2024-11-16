<?php

/**
 * @since 1.13.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<p>
<?php
	// Translators: %s: Customer name.
	printf( esc_html__( 'Dear %s,', 'motopress-appointment' ), '{customer_name}' );
?>
</p>

<p>
	<?php esc_html_e( 'For your information:', 'motopress-appointment' ); ?>
	<br>
	<?php
		// Translators: %d: "Notice 1", "Notice 2" etc.
		printf( esc_html__( 'Service Notice %d', 'motopress-appointment' ), 1 );
	?>
 - {notification_notice_1}
	<br>
	<?php
		// Translators: %d: "Notice 1", "Notice 2" etc.
		printf( esc_html__( 'Service Notice %d', 'motopress-appointment' ), 2 );
	?>
 - {notification_notice_2}
</p>

<h4><?php esc_html_e( 'Reservation details', 'motopress-appointment' ); ?></h4>

<p><?php esc_html_e( '{service_name} with {employee_name} on {reservation_date} from {start_buffer_time} till {end_buffer_time} at {location_name}.', 'motopress-appointment' ); ?></p>

<p><?php esc_html_e( 'Thank you!', 'motopress-appointment' ); ?></p>
