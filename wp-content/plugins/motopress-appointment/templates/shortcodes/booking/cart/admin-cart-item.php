<?php

/**
 * @since 1.9.0
 *
 * @param Reservation $reservation         Optional. Template tags by default.
 * @param bool        $enable_multibooking Optional. Value from settings by default.
 */

use MotoPress\Appointment\Helpers\ReservationHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$itemId       = '{item_id}';
$serviceId    = '{service_id}';
$serviceName  = '{service_name}';
$employeeId   = '{employee_id}';
$employeeName = '{employee_name}';
$locationId   = '{location_id}';
$locationName = '{location_name}';
$price        = '{reservation_price}';
$date         = '{reservation_date}';
$saveDate     = '{reservation_save_date}';
$period       = '{reservation_period}';
$savePeriod   = '{reservation_save_period}';
$clientsCount = '{reservation_clients_count}';
$clients      = '{reservation_clients}';
$uid          = '';

if ( ! empty( $reservation ) ) {
	$service = mpa_get_service( $reservation->getServiceId() );

	if ( ! is_null( $service ) ) {
		$capacityOptions = ReservationHelper::getCapacityOptions( $service, $reservation->getEmployeeId(), $reservation );
	} else {
		$capacityOptions = [ 1 ];
	}

	$capacityVariants = array_combine( $capacityOptions, $capacityOptions );

	$itemId       = uniqid();
	$serviceId    = $reservation->getServiceId();
	$serviceName  = get_the_title( $serviceId );
	$employeeId   = $reservation->getEmployeeId();
	$employeeName = get_the_title( $employeeId );
	$locationId   = $reservation->getLocationId();
	$locationName = get_the_title( $locationId );
	$price        = $reservation->getPrice();
	$date         = mpa_format_date( $reservation->getDate() );
	$saveDate     = mpa_format_date( $reservation->getDate(), 'internal' );
	$period       = $reservation->getServiceTime()->toString();
	$savePeriod   = $reservation->getServiceTime()->toString( 'internal' );
	$clientsCount = $reservation->getCapacity();
	$clients      = mpa_tmpl_select_options( $capacityVariants, $reservation->getCapacity() );
	$uid          = $reservation->getUid();
}

if ( ! isset( $enable_multibooking ) ) {
	$enable_multibooking = mpapp()->settings()->isMultibookingEnabled();
}

// Display template
$atts = array(
	'class' => 'mpa-reservation mpa-cart-item',
);

if ( empty( $reservation ) ) {
	$atts['class'] .= ' mpa-cart-item-template';
} else {
	$atts['data-id'] = $itemId;
}

?>
<tr <?php echo mpa_tmpl_atts( $atts ); ?>>
	<td class="column-service mpa-service-id mpa-service-name">
		<input type="hidden" name="reservations[<?php echo esc_attr( $itemId ); ?>][service_id]" value="<?php echo esc_attr( $serviceId ); ?>">
		<?php echo mpa_tmpl_edit_post_link( $serviceId, $serviceName ); ?>
	</td>
	<td class="column-employee mpa-employee-id mpa-employee-name">
		<input type="hidden" name="reservations[<?php echo esc_attr( $itemId ); ?>][employee_id]" value="<?php echo esc_attr( $employeeId ); ?>">
		<?php echo mpa_tmpl_edit_post_link( $employeeId, $employeeName ); ?>
	</td>
	<td class="column-location mpa-location-id mpa-location-name">
		<input type="hidden" name="reservations[<?php echo esc_attr( $itemId ); ?>][location_id]" value="<?php echo esc_attr( $locationId ); ?>">
		<?php echo mpa_tmpl_edit_post_link( $locationId, $locationName ); ?>
	</td>
	<td class="column-price mpa-reservation-price">
		<?php echo is_numeric( $price ) ? mpa_tmpl_price( $price ) : esc_html( $price ); ?>
	</td>
	<td class="column-date mpa-reservation-date mpa-reservation-save-date">
		<input type="hidden" name="reservations[<?php echo esc_attr( $itemId ); ?>][date]" value="<?php echo esc_attr( $saveDate ); ?>">
		<?php echo esc_html( $date ); ?>
	</td>
	<td class="column-time mpa-reservation-period mpa-reservation-save-period">
		<input type="hidden" name="reservations[<?php echo esc_attr( $itemId ); ?>][time]" value="<?php echo esc_attr( $savePeriod ); ?>">
		<?php echo esc_html( $period ); ?>
	</td>
	<td class="column-clients mpa-reservation-clients">
		<span class="mpa-reservation-clients-count">
			<?php echo esc_html( $clientsCount ); ?>
		</span>
		<select name="reservations[<?php echo esc_attr( $itemId ); ?>][capacity]">
			<?php echo $clients; // {template_tag} or HTML <options>'s ?>
		</select>
	</td>
	<td class="column-actions">
		<input type="hidden" name="reservations[<?php echo esc_attr( $itemId ); ?>][uid]" value="<?php echo esc_attr( $uid ); ?>">

		<?php
		$buttonText = $enable_multibooking
			? esc_html__( 'Remove', 'motopress-appointment' )
			: esc_html__( 'Edit', 'motopress-appointment' );

		$buttonAtts = array(
			'class' => 'button button-secondary ' . ( $enable_multibooking ? 'mpa-button-remove' : 'mpa-button-edit' ),
		);

		echo mpa_tmpl_button( $buttonText, $buttonAtts );
		?>
	</td>
</tr>
