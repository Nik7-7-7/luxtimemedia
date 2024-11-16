<?php
/**
 * @var MotoPress\Appointment\Entities\Booking $booking
 */

use MotoPress\Appointment\PostTypes\Statuses\BookingStatuses;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( BookingStatuses::STATUS_CONFIRMED === $booking->getStatus() ) {

	$bookingStatusMessage = __( 'Thank you for your payment. Your transaction has been completed.', 'motopress-appointment' );

} else {

	$bookingStatusMessage = __( 'We are pleased to inform you that your reservation request has been received.', 'motopress-appointment' );
}
?>

<p class="mpa-booking-status-message booking-<?php echo esc_attr( $booking->getStatus() ); ?>"><?php echo esc_html( $bookingStatusMessage ); ?></p>
<?php

mpa_display_template(
	'shortcodes/template-parts/booking-details.php',
	array(
		'booking'                  => $booking,
		'isShowAddToCalendarLinks' => true,
	)
);

?>
