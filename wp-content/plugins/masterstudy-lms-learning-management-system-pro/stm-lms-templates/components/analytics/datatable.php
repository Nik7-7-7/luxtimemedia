<?php
/**
 * @var string $id
 * @var array $columns
 */

wp_enqueue_style( 'masterstudy-datatables-library' );

$columns = isset( $columns ) ? $columns : array();
?>

<div class="masterstudy-datatable">
	<table id="masterstudy-datatable-<?php echo esc_attr( $id ); ?>">
		<thead>
			<tr>
				<?php foreach ( $columns as $column ) { ?>
					<th><?php echo esc_html( $column['title'] ); ?></th>
				<?php } ?>
			</tr>
		</thead>
	</table>
</div>
