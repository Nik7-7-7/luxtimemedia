<?php
/**
 * @var object $current_user
 */

use MasterStudy\Lms\Plugin\Addons;

wp_enqueue_style( 'masterstudy-analytics-short-report' );
wp_enqueue_style( 'masterstudy-analytics-components' );
wp_enqueue_script( 'masterstudy-analytics-short-report' );

$stats_types = array(
	'revenue',
	'orders',
	'courses',
	'enrollments',
	'students',
	'reviews',
);

if ( is_ms_lms_addon_enabled( 'certificate_builder' ) ) {
	array_splice( $stats_types, 6, 0, 'certificates_created' );
}

if ( is_ms_lms_addon_enabled( Addons::COURSE_BUNDLE ) ) {
	array_splice( $stats_types, 7, 0, 'bundles' );
}
?>

<div class="masterstudy-analytics-short-report-page">
	<h3 class="masterstudy-analytics-short-report-page__title">
		<?php echo esc_html__( 'Analytics', 'masterstudy-lms-learning-management-system-pro' ); ?>
	</h3>
	<div class="masterstudy-analytics-short-report-page__tabs">
		<?php
		STM_LMS_Templates::show_lms_template(
			'components/tabs',
			array(
				'items'            => array(
					array(
						'id'    => 'all_time',
						'title' => __( 'All time', 'masterstudy-lms-learning-management-system-pro' ),
					),
					array(
						'id'    => 'this_year',
						'title' => __( 'Year', 'masterstudy-lms-learning-management-system-pro' ),
					),
					array(
						'id'    => 'this_month',
						'title' => __( 'Month', 'masterstudy-lms-learning-management-system-pro' ),
					),
					array(
						'id'    => 'this_week',
						'title' => __( 'Week', 'masterstudy-lms-learning-management-system-pro' ),
					),
					array(
						'id'    => 'today',
						'title' => __( 'Day', 'masterstudy-lms-learning-management-system-pro' ),
					),
				),
				'style'            => 'nav-md',
				'active_tab_index' => 0,
				'dark_mode'        => false,
			)
		);
		?>
	</div>
	<?php
	STM_LMS_Templates::show_lms_template(
		'components/button',
		array(
			'title' => esc_html__( 'Detailed reports', 'masterstudy-lms-learning-management-system' ),
			'link'  => STM_LMS_User::login_page_url() . 'analytics',
			'style' => 'primary',
			'size'  => 'sm',
			'id'    => 'user-detailed-report',
		)
	);
	?>
</div>
<?php
STM_LMS_Templates::show_lms_template(
	'analytics/partials/stats-section',
	array(
		'page_slug'   => 'short-report',
		'stats_types' => $stats_types,
	)
);
?>
