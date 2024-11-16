<?php

/**
 * @param \MotoPress\Appointment\ListTables\AbstractListTable $listTable
 *
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$columns = $listTable->getColumns();

?>
<table id="<?php echo esc_attr( $listTable->getId() ); ?>" class="wp-list-table mpa-list-table widefat striped mpa-table">
	<thead>
		<tr>
			<?php foreach ( $columns as $columnName => $columnLabel ) { ?>
				<th class="<?php echo esc_attr( "{$columnName} column-{$columnName}" ); ?>">
					<?php echo $columnLabel; ?>
				</th>
			<?php } ?>
		</tr>
	</thead>

	<tbody>
		<?php if ( $listTable->hasItems() ) { ?>
			<?php $listTable->displayRows(); ?>
		<?php } else { ?>
			<tr class="no-items">
				<td colspan="<?php echo count( $columns ); ?>">
					<?php esc_html_e( 'No items found.', 'motopress-appointment' ); ?>
				</td>
			</tr>
		<?php } ?>
	</tbody>
</table>
