<?php
/**
 * @var string $page_slug
 * @var string $page_title
 * @var array $settings_title
 * @var array $settings_description
 * @var array $tables_data
 * @var array $charts_data
 * @var boolean $is_user_account
 */

$page_index = array(
	'revenue'             => 0,
	'engagement'          => 1,
	'instructor-students' => 2,
	'reviews'             => 3,
);
?>

<div class="masterstudy-analytics-<?php echo esc_html( $page_slug ); ?>-page__header">
	<h1 class="masterstudy-analytics-<?php echo esc_html( $page_slug ); ?>-page__title">
		<?php echo $is_user_account ? esc_html__( 'Analytics', 'masterstudy-lms-learning-management-system-pro' ) : esc_html( $page_title ); ?>
	</h1>
	<?php if ( $is_user_account ) { ?>
		<div class="masterstudy-analytics-<?php echo esc_html( $page_slug ); ?>-page__tabs">
			<?php
			STM_LMS_Templates::show_lms_template(
				'components/tabs',
				array(
					'items'            => array(
						array(
							'id'    => 'revenue',
							'title' => __( 'Revenue', 'masterstudy-lms-learning-management-system-pro' ),
						),
						array(
							'id'    => 'engagement',
							'title' => __( 'Engagement', 'masterstudy-lms-learning-management-system-pro' ),
						),
						array(
							'id'    => 'instructor-students',
							'title' => __( 'Students', 'masterstudy-lms-learning-management-system-pro' ),
						),
						array(
							'id'    => 'reviews',
							'title' => __( 'Reviews', 'masterstudy-lms-learning-management-system-pro' ),
						),
					),
					'style'            => 'nav-sm',
					'active_tab_index' => $page_index[ $page_slug ],
					'dark_mode'        => false,
				)
			);
			?>
		</div>
	<?php } ?>
	<div class="masterstudy-analytics__date">
		<div class="masterstudy-analytics__date-label"></div>
		<div class="masterstudy-analytics__date-value"></div>
	</div>
	<?php
	STM_LMS_Templates::show_lms_template(
		'components/analytics/settings-button',
		array(
			'id' => $page_slug,
		)
	);
	?>
</div>
<?php if ( $is_user_account ) { ?>
	<div class="masterstudy-analytics-<?php echo esc_html( $page_slug ); ?>-page__separator">
		<span class="masterstudy-analytics-<?php echo esc_html( $page_slug ); ?>-page__separator-short"></span>
		<span class="masterstudy-analytics-<?php echo esc_html( $page_slug ); ?>-page__separator-long"></span>
	</div>
	<?php
}
STM_LMS_Templates::show_lms_template(
	'components/analytics/datepicker-modal',
	array(
		'id' => $page_slug,
	)
);
STM_LMS_Templates::show_lms_template(
	'components/analytics/settings-modal',
	array(
		'id'                  => $page_slug,
		'title'               => $settings_title,
		'text'                => $settings_description,
		'settings'            => array_merge( $tables_data, $charts_data ),
		'submit_button_text'  => esc_html__( 'Save', 'masterstudy-lms-learning-management-system-pro' ),
		'submit_button_style' => 'primary',
	)
);
