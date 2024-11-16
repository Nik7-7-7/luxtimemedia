<?php

/**
 * @since 1.0
 * @since 1.5.0 added the <code>$timepicker_columns</code> argument.
 * @since 1.5.0 added the <code>$show_timepicker_end_time</code> argument.
 *
 * @param int  $timepicker_columns       Optional. 3 by default.
 * @param bool $show_timepicker_end_time Optional. False by default.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Initialize args
extract(
	array(
		'timepicker_columns'       => 3,
		'show_timepicker_end_time' => false,
	),
	EXTR_SKIP
);

$timepickerClasses = '';

switch ( $timepicker_columns ) {
	case 2:
		$timepickerClasses .= ' mpa-two-columns';
		break;
	case 3:
		$timepickerClasses .= ' mpa-three-columns';
		break;
	case 4:
		$timepickerClasses .= ' mpa-four-columns';
		break;
	case 5:
		$timepickerClasses .= ' mpa-five-columns';
		break;
}

if ( $show_timepicker_end_time ) {
	$timepickerClasses .= ' mpa-show-end-time';
}

?>
<div class="mpa-booking-step mpa-booking-step-period mpa-hide">
	<p class="mpa-shortcode-title">
		<?php esc_html_e( 'Select Date & Time', 'motopress-appointment' ); ?>
	</p>

	<div class="mpa-input-container">
		<div class="mpa-input-wrapper mpa-date-wrapper">
			<input class="mpa-date mpa-hide" type="hidden" value="">

			<div class="mpa-loading"></div>
		</div>

		<div class="mpa-input-wrapper mpa-time-wrapper mpa-hide">
			<div class="mpa-times-container">
				<div class="mpa-times <?php echo esc_attr( $timepickerClasses ); ?>"></div>
			</div>
		</div>
	</div>

	<p class="mpa-actions mpa-hide">
		<?php echo mpa_tmpl_button( esc_html__( 'Back', 'motopress-appointment' ), array( 'class' => 'button button-secondary mpa-button-back' ) ); ?>
		<?php echo mpa_tmpl_button( esc_html__( 'Next', 'motopress-appointment' ), array( 'class' => 'button button-primary mpa-button-next' ) ); ?>
	</p>

	<div class="mpa-loading"></div>
</div>
