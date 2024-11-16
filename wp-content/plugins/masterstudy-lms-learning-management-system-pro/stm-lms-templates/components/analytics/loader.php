<?php
/**
 * @var string $loader_type
 *
 * available loader types: doughnut-x-loader, doughnut-y-loader, data-loader, line-loader, table-loader
 */

$loader_type = isset( $loader_type ) ? $loader_type : 'doughnut-x-loader';
?>

<div class="masterstudy-analytics-loader <?php echo esc_attr( 'table-loader' === $loader_type ) ? 'masterstudy-analytics-loader_table' : ''; ?>">
	<img src="<?php echo esc_attr( STM_LMS_PRO_URL . '/assets/img/analytics/' . $loader_type . '.svg' ); ?>" class="masterstudy-analytics-loader__image">
</div>
