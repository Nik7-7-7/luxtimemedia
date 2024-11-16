<?php

/**
 * @param bool $show_add_to_calendar
 *
 * @since 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Initialize args
extract(
	array(
		'show_add_to_calendar' => true,
	),
	EXTR_SKIP
);

?>
<div class="mpa-booking-step mpa-booking-step-booking mpa-hide">
    <p class="mpa-message">
		<?php esc_html_e( 'Making a reservation...', 'motopress-appointment' ); ?>
        <span class="mpa-preloader"></span>
    </p>

	<?php if ( $show_add_to_calendar ) { ?>
		<?php mpa_display_template( 'shortcodes/booking/sections/booking-details-section.php' ); ?>
	<?php } ?>

    <p class="mpa-actions mpa-hide">
		<?php echo mpa_tmpl_button( esc_html__( 'Back', 'motopress-appointment' ), array( 'class' => 'button button-secondary mpa-button-back' ) ); ?>
		<?php echo mpa_tmpl_button( esc_html__( 'Add New Reservation', 'motopress-appointment' ), array( 'class' => 'button button-primary mpa-button-reset' ) ); ?>
    </p>
</div>
