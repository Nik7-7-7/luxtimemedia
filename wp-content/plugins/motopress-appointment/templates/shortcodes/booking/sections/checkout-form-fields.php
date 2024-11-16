<?php

/**
 * @since 1.23.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

extract(
	array(
		'html_id'          => '',
		'placeholderName'  => '',
		'placeholderEmail' => '',
		'placeholderPhone' => '',
	),
	EXTR_SKIP
);

?>

<p class="mpa-input-wrapper mpa-customer-name-wrapper">
	<label for="mpa-customer-name-<?php echo esc_attr( $html_id ); ?>">
		<?php esc_html_e( 'Name', 'motopress-appointment' ); ?>
		<?php echo mpa_tmpl_required(); ?>
	</label>
	<input
		id="mpa-customer-name-<?php echo esc_attr( $html_id ); ?>"
		class="mpa-customer-name"
		type="text"
		name="name"
		placeholder="<?php echo $placeholderName; ?>"
		required="required">
</p>

<p class="mpa-input-wrapper mpa-customer-email-wrapper">
	<label for="mpa-customer-email-<?php echo esc_attr( $html_id ); ?>">
		<?php esc_html_e( 'Email', 'motopress-appointment' ); ?>
		<?php echo mpa_tmpl_required(); ?>
	</label>
	<input
		id="mpa-customer-email-<?php echo esc_attr( $html_id ); ?>"
		class="mpa-customer-email"
		type="email"
		name="email"
		placeholder="<?php echo $placeholderEmail; ?>"
		required="required">
</p>

<p class="mpa-input-wrapper mpa-customer-phone-wrapper">
	<label for="mpa-customer-phone-<?php echo esc_attr( $html_id ); ?>">
		<?php esc_html_e( 'Phone', 'motopress-appointment' ); ?>
		<?php echo mpa_tmpl_required(); ?>
	</label>
	<input
		id="mpa-customer-phone-<?php echo esc_attr( $html_id ); ?>"
		class="mpa-customer-phone"
		type="tel"
		name="tel"
		placeholder="<?php echo esc_attr( $placeholderPhone ); ?>"
		autocomplete="on"
		required="required"
	>
</p>

<p class="mpa-input-wrapper mpa-customer-notes-wrapper">
	<label for="mpa-customer-notes-<?php echo esc_attr( $html_id ); ?>">
		<?php esc_html_e( 'Booking notes', 'motopress-appointment' ); ?>
	</label>
	<textarea
		id="mpa-customer-notes-<?php echo esc_attr( $html_id ); ?>"
		class="mpa-customer-notes"
		name="notes"
		rows="4"></textarea>
</p>