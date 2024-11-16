<?php
/**
 * @var string $page_slug
 * @var array $stats_types
 * @var string $extra_class
 */
?>

<div class="masterstudy-analytics-<?php echo esc_html( $page_slug ); ?>-page-stats <?php echo esc_html( $extra_class ?? '' ); ?>">
	<div class="masterstudy-analytics-<?php echo esc_html( $page_slug ); ?>-page-stats__wrapper">
		<?php foreach ( $stats_types as $type ) { ?>
			<div class="masterstudy-analytics-<?php echo esc_html( $page_slug ); ?>-page-stats__block">
				<?php
				STM_LMS_Templates::show_lms_template( 'components/analytics/loader', array( 'loader_type' => 'data-loader' ) );
				STM_LMS_Templates::show_lms_template(
					'components/analytics/stats-block',
					array(
						'type' => $type,
					)
				);
				?>
			</div>
		<?php } ?>
	</div>
</div>
