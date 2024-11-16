<?php
/**
 * @var MotoPress\Appointment\Entities\Booking $booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


mpa_display_template(
	'shortcodes/template-parts/booking-details.php',
	array(
		'booking' => $booking,
	)
);
