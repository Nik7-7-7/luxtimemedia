<?php

/**
 * @since 1.9.0
 *
 * @param Reservation[] $reservations Optional.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $reservations ) ) {
	$booking = mpa_get_booking();

	if ( ! is_null( $booking ) ) {
		$reservations = $booking->getReservations();
	} else {
		$reservations = array();
	}
}

$enableMultibooking = mpapp()->settings()->isMultibookingEnabled() || count( $reservations ) > 1;

// Mark step as "mpa-loaded", because it will load only after the hit of the
// "Edit Reservations" button

?>
<div class="mpa-booking-step mpa-booking-step-cart mpa-booking-step-admin-cart mpa-loaded">
	<table class="widefat striped mpa-table-centered mpa-cart">
		<thead>
			<tr>
				<th class="row-title column-service" ><?php esc_html_e( 'Service', 'motopress-appointment' ); ?></th>
				<th class="row-title column-employee"><?php esc_html_e( 'Employee', 'motopress-appointment' ); ?></th>
				<th class="row-title column-location"><?php esc_html_e( 'Location', 'motopress-appointment' ); ?></th>
				<th class="row-title column-price"   ><?php esc_html_e( 'Price', 'motopress-appointment' ); ?></th>
				<th class="row-title column-date"    ><?php esc_html_e( 'Date', 'motopress-appointment' ); ?></th>
				<th class="row-title column-time"    ><?php esc_html_e( 'Time', 'motopress-appointment' ); ?></th>
				<th class="row-title column-clients" ><?php esc_html_e( 'Clients', 'motopress-appointment' ); ?></th>
				<th class="row-title column-actions" ><?php esc_html_e( 'Action', 'motopress-appointment' ); ?></th>
			</tr>
		</thead>
		<tbody class="mpa-cart-items">
			<?php

			// Display template
			mpa_display_template( 'shortcodes/booking/cart/admin-cart-item.php', array( 'enable_multibooking' => $enableMultibooking ) );

			// Display actual items
			foreach ( $reservations as $reservation ) {
				mpa_display_template(
					'shortcodes/booking/cart/admin-cart-item.php',
					array(
						'reservation'         => $reservation,
						'enable_multibooking' => $enableMultibooking,
					)
				);
			}

			?>

			<tr class="no-items <?php echo ! empty( $reservations ) ? 'mpa-hide' : ''; ?>">
				<td colspan="8"><?php esc_html_e( 'No reservations found.', 'motopress-appointment' ); ?></td>
			</tr>
		</tbody>
	</table>

	<p class="mpa-actions">
		<?php
		if ( $enableMultibooking || count( $reservations ) === 0 ) {
			$buttonAtts = array(
				'class' => 'button button-primary mpa-button-new',
			);

			echo mpa_tmpl_button( esc_html__( 'Add More', 'motopress-appointment' ), $buttonAtts );
		}

		echo mpa_tmpl_button( esc_html__( 'Edit Reservations', 'motopress-appointment' ), array( 'class' => 'button button-primary mpa-button-edit' ) );
		?>
	</p>

	<div class="mpa-loading"></div>
</div>
