<?php

/**
 * @since 1.3.1
 *
 * @param string $input_name Required.
 * @param array  $variations Required. Array of [employee, price, duration,
 *                           min_capacity, max_capacity].
 *
 * @todo Move upgrade code to plugin patcher.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$employees = mpa_get_employees(); // [ID => Name]
$durations = mpa_time_durations( mpapp()->settings()->getTimeStep(), 1440 ); // [15 => '15m', 30 => '30m', ...]

$hasValues = ! empty( $variations );

$hideTableClass = ( $hasValues ? '' : 'mpa-hide' );

// Display template
?>
<input type="hidden" name="<?php echo esc_attr( $input_name ); ?>" value="">

<div class="mpa-data-lists mpa-hide">
	<?php echo mpa_tmpl_select( $employees, 0, array( 'class' => 'mpa-employees-list' ) ); ?>
	<?php echo mpa_tmpl_select( $durations, 0, array( 'class' => 'mpa-durations-list' ) ); ?>
</div>

<table class="widefat striped mpa-table-centered <?php echo $hideTableClass; ?>">
	<thead>
		<th class="row-title column-employee"    ><?php esc_html_e( 'Employee', 'motopress-appointment' ); ?></th>
		<th class="row-title column-price"       ><?php esc_html_e( 'Price', 'motopress-appointment' ); ?></th>
		<th class="row-title column-duration"    ><?php esc_html_e( 'Duration', 'motopress-appointment' ); ?></th>
		<th class="row-title column-min-capacity"><?php esc_html_e( 'Minimum Capacity', 'motopress-appointment' ); ?></th>
		<th class="row-title column-max-capacity"><?php esc_html_e( 'Maximum Capacity', 'motopress-appointment' ); ?></th>
		<th class="column-actions"></th>
	</thead>

	<tbody>
		<?php
		foreach ( $variations as $variation ) {
			// Add optional fields (upgrade 1.3 to 1.4)
			$variation += array(
				'min_capacity' => '',
				'max_capacity' => '',
			);

			$rowId       = uniqid();
			$inputPrefix = "{$input_name}[{$rowId}]";
			?>

			<tr class="mpa-variation" data-id="<?php echo esc_attr( $rowId ); ?>">
				<td class="column-employee">
					<?php
					echo mpa_tmpl_select(
						$employees,
						$variation['employee'],
						array(
							'name'  => "{$inputPrefix}[employee]",
							'class' => 'mpa-employees',
						)
					);
					?>
				</td>

				<td class="column-price">
					<input class="mpa-price" type="number" name="<?php echo esc_attr( "{$inputPrefix}[price]" ); ?>" value="<?php echo esc_attr( $variation['price'] ); ?>" min="0" step="0.01">
				</td>

				<td class="column-duration">
					<?php
					echo mpa_tmpl_select(
						$durations,
						$variation['duration'],
						array(
							'name'  => "{$inputPrefix}[duration]",
							'class' => 'mpa-durations',
						)
					);
					?>
				</td>

				<td class="column-min-capacity">
					<input type="number" name="<?php echo esc_attr( "{$inputPrefix}[min_capacity]" ); ?>" value="<?php echo esc_attr( $variation['min_capacity'] ); ?>" min="1" step="1">
				</td>

				<td class="column-max-capacity">
					<input type="number" name="<?php echo esc_attr( "{$inputPrefix}[max_capacity]" ); ?>" value="<?php echo esc_attr( $variation['max_capacity'] ); ?>" min="1" step="1">
				</td>

				<td class="column-actions">
					<?php echo mpa_tmpl_dashicon( 'trash', 'mpa-remove-button' ); ?>
				</td>
			</tr>
		<?php } // For each variation ?>
	</tbody>
</table>

<p class="mpa-controls">
	<?php
	echo mpa_tmpl_button(
		esc_html__( 'Add Variation', 'motopress-appointment' ),
		array( 'class' => 'button button-primary mpa-add-button' )
	);
	?>
</p>
