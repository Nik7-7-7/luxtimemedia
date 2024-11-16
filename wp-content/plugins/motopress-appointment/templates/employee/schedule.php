<?php

/**
 * @since 1.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$schedule = mpapp()->repositories()->schedule()->findByEmployee( get_the_ID() );

if ( is_null( $schedule ) ) {
	return;
}

$workingHours = mpa_tmpl_schedule( $schedule );

if ( empty( $workingHours ) ) {
	return;
}

?>
<ul class="mpa-employee-working-hours">
	<?php foreach ( $workingHours as $period => $time ) { ?>
		<li class="mpa-working-day">
			<span class="mpa-schedule-days"><?php echo esc_html( $period ); ?>:</span>
			<span class="mpa-schedule-time"><?php echo esc_html( $time ); ?></span>
		</li>
	<?php } ?>
</ul>
