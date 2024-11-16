<?php
/**
 * @var MotoPress\Appointment\Entities\Booking $booking
 *
 * @since 1.18.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="mpa-account-booking">
	<?php
	mpa_display_template(
		'shortcodes/template-parts/booking-details.php',
		array(
			'booking'                  => $booking,
			'isShowAddToCalendarLinks' => true,
		)
	);
	?>
</div>
