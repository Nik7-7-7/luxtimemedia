<?php

/**
 * @since 1.15.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php esc_html_e( 'Click the link below to cancel your booking.', 'motopress-appointment' ); ?>
<br/>
<a href="{booking_cancel_link}"><?php esc_html_e( 'Cancel your booking', 'motopress-appointment' ); ?></a>
<br/>
