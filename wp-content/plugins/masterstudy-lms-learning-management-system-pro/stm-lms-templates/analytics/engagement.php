<?php
$is_user_account = ! is_admin();

if ( $is_user_account ) {
	get_header();
}

wp_enqueue_style( 'masterstudy-analytics-engagement-page' );
wp_enqueue_style( 'masterstudy-analytics-components' );
wp_enqueue_script( 'masterstudy-analytics-engagement-page' );

$courses_columns = array(
	array(
		'title' => esc_html__( '№', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'number',
	),
	array(
		'title' => esc_html__( 'Course name', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'course_name',
	),
	array(
		'title' => esc_html__( 'Not started', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'not_started',
	),
	array(
		'title' => esc_html__( 'In progress', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'in_progress',
	),
	array(
		'title' => esc_html__( 'Completed', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'completed',
	),
	array(
		'title' => esc_html__( 'Expired', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'expired',
	),
	array(
		'title' => esc_html__( 'Reviews', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'reviews',
	),
	array(
		'title' => esc_html__( 'Course creation date', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'date_created',
	),
	array(
		'title' => '',
		'data'  => 'course_id',
	),
);

$students_columns = array(
	array(
		'title' => esc_html__( '№', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'number',
	),
	array(
		'title' => esc_html__( 'Student name', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'student_name',
	),
	array(
		'title' => esc_html__( 'Total', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'total',
	),
	array(
		'title' => esc_html__( 'Not started', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'not_started',
	),
	array(
		'title' => esc_html__( 'In progress', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'in_progress',
	),
	array(
		'title' => esc_html__( 'Completed', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'completed',
	),
	array(
		'title' => esc_html__( 'Expired', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'expired',
	),
	array(
		'title' => esc_html__( 'Reviews', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'reviews',
	),
	array(
		'title' => esc_html__( 'Joined on', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'joined',
	),
	array(
		'title' => '',
		'data'  => 'student_id',
	),
);

$table_routes = array(
	'courses'  => 'engagement/courses',
	'students' => 'engagement/students',
);

$stats_types = array(
	'new_courses',
	'enrollments',
	'new_students',
	'new_lessons',
	'new_quizzes',
);

// Replace 'new_assignments' with 'new_students' for instructors
if ( ! is_admin() ) {
	$stats_types[2] = 'new_assignments';
}

if ( is_ms_lms_addon_enabled( 'certificate_builder' ) ) {
	array_splice( $stats_types, 2, 0, 'certificates' );
	array_splice(
		$students_columns,
		8,
		0,
		array(
			array(
				'title' => esc_html__( 'Certificates', 'masterstudy-lms-learning-management-system-pro' ),
				'data'  => 'certificates',
			),
		)
	);
}

if ( is_ms_lms_addon_enabled( 'enterprise_courses' ) ) {
	array_splice( $stats_types, 5, 0, 'new_groups_courses' );
}

if ( is_ms_lms_addon_enabled( 'shareware' ) ) {
	array_splice( $stats_types, 6, 0, 'new_trial_courses' );
}

wp_localize_script(
	'masterstudy-analytics-engagement-page',
	'engagement_page_data',
	array(
		$table_routes['courses']  => $courses_columns,
		$table_routes['students'] => $students_columns,
		'titles'                  => array(
			'enrollments_chart' => array(
				'total'  => esc_html__( 'Total', 'masterstudy-lms-learning-management-system-pro' ),
				'unique' => esc_html__( 'Unique', 'masterstudy-lms-learning-management-system-pro' ),
			),
			'by_status'         => array(
				esc_html__( 'Not started', 'masterstudy-lms-learning-management-system-pro' ),
				esc_html__( 'In progress', 'masterstudy-lms-learning-management-system-pro' ),
				esc_html__( 'Completed', 'masterstudy-lms-learning-management-system-pro' ),
			),
			'assignments'       => array(
				esc_html__( 'In progress', 'masterstudy-lms-learning-management-system-pro' ),
				esc_html__( 'Pending review', 'masterstudy-lms-learning-management-system-pro' ),
				esc_html__( 'Passed', 'masterstudy-lms-learning-management-system-pro' ),
				esc_html__( 'Failed', 'masterstudy-lms-learning-management-system-pro' ),
			),
		),
		'user_account_url'        => STM_LMS_User::login_page_url() . 'analytics/',
		'search_placeholders'     => array(
			$table_routes['courses']  => esc_html__( 'Search by course name', 'masterstudy-lms-learning-management-system-pro' ),
			$table_routes['students'] => esc_html__( 'Search by student name', 'masterstudy-lms-learning-management-system-pro' ),
		),
	)
);

$charts_data = array(
	array(
		'title' => esc_html__( 'Enrollments by status', 'masterstudy-lms-learning-management-system-pro' ),
		'id'    => 'by-status',
	),
);

if ( is_ms_lms_addon_enabled( 'assignments' ) ) {
	$charts_data[] = array(
		'title' => esc_html__( 'Assignment engagement', 'masterstudy-lms-learning-management-system-pro' ),
		'id'    => 'assignments',
	);
}

$tables_data = array(
	array(
		'title' => esc_html__( 'Enrollments chart', 'masterstudy-lms-learning-management-system-pro' ),
		'id'    => 'enrollments-chart',
	),
	array(
		'title' => esc_html__( 'Engagement table', 'masterstudy-lms-learning-management-system-pro' ),
		'id'    => 'engagement-table',
	),
);

if ( $is_user_account ) {
	do_action( 'stm_lms_template_main' );
	?>
	<div class="stm-lms-wrapper user-account-page">
		<div class="container">
			<?php
			do_action( 'stm_lms_admin_after_wrapper_start', STM_LMS_User::get_current_user() );
}
?>
			<div class="masterstudy-analytics-engagement-page">
				<?php
				STM_LMS_Templates::show_lms_template(
					'analytics/partials/header',
					array(
						'page_slug'            => 'engagement',
						'page_title'           => esc_html__( 'Engagement', 'masterstudy-lms-learning-management-system-pro' ),
						'settings_title'       => esc_html__( 'Course engagement reports page', 'masterstudy-lms-learning-management-system-pro' ),
						'settings_description' => esc_html__( 'Select which information to show in your enrollment reports.', 'masterstudy-lms-learning-management-system-pro' ),
						'tables_data'          => $tables_data,
						'charts_data'          => $charts_data,
						'is_user_account'      => $is_user_account,
					)
				);

				STM_LMS_Templates::show_lms_template(
					'analytics/partials/stats-section',
					array(
						'page_slug'   => 'engagement',
						'stats_types' => $stats_types,
					)
				);
				?>
				<div class="masterstudy-analytics-engagement-page-line" data-chart-id="enrollments-chart">
					<div class="masterstudy-analytics-engagement-page-line__wrapper">
						<div class="masterstudy-analytics-engagement-page-line__content">
							<?php STM_LMS_Templates::show_lms_template( 'components/analytics/loader', array( 'loader_type' => 'line-chart-loader' ) ); ?>
							<div class="masterstudy-analytics-engagement-page-line__header">
								<h2 class="masterstudy-analytics-engagement-page-line__title">
									<?php echo esc_html__( 'Enrollments', 'masterstudy-lms-learning-management-system-pro' ); ?>
								</h2>
								<div class="masterstudy-analytics-engagement-page-line__total-wrapper">
									<div class="masterstudy-analytics-engagement-page-line__total">
										<div class="masterstudy-analytics-engagement-page-line__total-title">
											<?php echo esc_html__( 'Total', 'masterstudy-lms-learning-management-system-pro' ); ?>:
										</div>
										<div id="enrollments-total" class="masterstudy-analytics-engagement-page-line__total-value"></div>
									</div>
									<div class="masterstudy-analytics-engagement-page-line__total">
										<div class="masterstudy-analytics-engagement-page-line__total-title">
											<?php echo esc_html__( 'Unique', 'masterstudy-lms-learning-management-system-pro' ); ?>:
										</div>
										<div id="unique-total" class="masterstudy-analytics-engagement-page-line__total-value"></div>
									</div>
								</div>
								<?php
								STM_LMS_Templates::show_lms_template(
									'components/analytics/settings-dropdown',
									array(
										'id'         => 'enrollments',
										'menu_items' => array(
											array(
												'id'    => 'enrollments-chart',
												'title' => esc_html__( 'Hide report', 'masterstudy-lms-learning-management-system-pro' ),
											),
										),
									)
								);
								?>
							</div>
							<div class="masterstudy-analytics-engagement-page-line__chart">
								<?php
								STM_LMS_Templates::show_lms_template(
									'components/analytics/line-chart',
									array(
										'id' => 'enrollments',
									)
								);
								?>
							</div>
						</div>
					</div>
				</div>
				<?php foreach ( $charts_data as $data ) { ?>
					<div class="masterstudy-analytics-engagement-page-doughnut" data-chart-id="<?php echo esc_attr( $data['id'] ); ?>">
						<div class="masterstudy-analytics-engagement-page-doughnut__wrapper">
							<div class="masterstudy-analytics-engagement-page-doughnut__content">
								<?php STM_LMS_Templates::show_lms_template( 'components/analytics/loader', array( 'loader_type' => 'doughnut-x-loader' ) ); ?>
								<div class="masterstudy-analytics-engagement-page-doughnut__header">
									<h2 class="masterstudy-analytics-engagement-page-doughnut__title">
										<?php echo esc_html( $data['title'] ); ?>
									</h2>
									<?php
									STM_LMS_Templates::show_lms_template(
										'components/analytics/settings-dropdown',
										array(
											'id'         => 'engagement',
											'menu_items' => array(
												array(
													'id' => $data['id'],
													'title' => esc_html__( 'Hide report', 'masterstudy-lms-learning-management-system-pro' ),
												),
											),
										)
									);
									?>
								</div>
								<div class="masterstudy-analytics-engagement-page-doughnut__chart">
									<img src="<?php echo esc_attr( STM_LMS_PRO_URL . '/assets/img/analytics/graphic.svg' ); ?>" class="masterstudy-analytics-empty-chart">
									<?php
									STM_LMS_Templates::show_lms_template(
										'components/analytics/doughnut-chart',
										array(
											'id' => $data['id'],
										)
									);
									STM_LMS_Templates::show_lms_template(
										'components/analytics/chart-total',
										array(
											'id' => $data['id'],
										)
									);
									?>
								</div>
								<?php
								STM_LMS_Templates::show_lms_template(
									'components/analytics/doughnut-chart-info',
									array(
										'quantity' => 'by-status' === $data['id'] ? 3 : 4,
									),
								);
								?>
							</div>
						</div>
					</div>
				<?php } ?>
				<div class="masterstudy-analytics-engagement-page-table" data-chart-id="engagement-table">
					<?php STM_LMS_Templates::show_lms_template( 'components/analytics/loader', array( 'loader_type' => 'table-loader' ) ); ?>
					<div class="masterstudy-analytics-engagement-page-table__wrapper">
						<div class="masterstudy-analytics-engagement-page-table__header">
							<div class="masterstudy-analytics-engagement-page-table__title">
								<?php echo esc_html__( 'Engagement', 'masterstudy-lms-learning-management-system-pro' ); ?>
							</div>
							<div class="masterstudy-analytics-table__tabs">
								<?php
								STM_LMS_Templates::show_lms_template(
									'components/tabs',
									array(
										'items'            => array(
											array(
												'id'    => $table_routes['courses'],
												'title' => esc_html__( 'Courses', 'masterstudy-lms-learning-management-system-pro' ),
											),
											array(
												'id'    => $table_routes['students'],
												'title' => esc_html__( 'Students', 'masterstudy-lms-learning-management-system-pro' ),
											),
										),
										'style'            => 'default',
										'active_tab_index' => 0,
										'dark_mode'        => false,
									)
								);
								?>
							</div>
							<div class="masterstudy-analytics-engagement-page-table__search-wrapper">
								<input type="text" id="table-engagement-search" class="masterstudy-analytics-engagement-page-table__search" placeholder="<?php echo esc_html__( 'Search by course name', 'masterstudy-lms-learning-management-system-pro' ); ?>">
								<span class="masterstudy-analytics-engagement-page-table__search-icon"></span>
							</div>
							<?php
							STM_LMS_Templates::show_lms_template(
								'components/analytics/settings-dropdown',
								array(
									'id'         => 'engagement',
									'menu_items' => array(
										array(
											'id'    => 'engagement-table',
											'title' => esc_html__( 'Hide report', 'masterstudy-lms-learning-management-system-pro' ),
										),
									),
								)
							);
							?>
						</div>
						<?php
						STM_LMS_Templates::show_lms_template(
							'components/analytics/datatable',
							array(
								'id'      => 'engagement',
								'columns' => $courses_columns,
							)
						);
						?>
					</div>
				</div>
			</div>
<?php
if ( $is_user_account ) {
	?>
		</div>
	</div>
	<?php
	get_footer();
}
