<?php
$is_user_account = ! is_admin();

if ( $is_user_account ) {
	get_header();
}

wp_enqueue_style( 'masterstudy-analytics-course-page' );
wp_enqueue_style( 'masterstudy-analytics-components' );
wp_enqueue_script( 'masterstudy-analytics-course-page' );

$lessons_columns = array(
	array(
		'title' => esc_html__( '№', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'number',
	),
	array(
		'title' => esc_html__( 'Lesson name', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'lesson_name',
	),
	array(
		'title' => esc_html__( 'Completed', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'completed',
	),
	array(
		'title' => esc_html__( 'Dropped', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'dropped',
	),
	array(
		'title' => esc_html__( 'Not completed', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'not_completed',
	),
	array(
		'title' => esc_html__( 'Total', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'total',
	),
	array(
		'title' => esc_html__( 'Lesson type', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'lesson_type',
	),
	array(
		'title' => esc_html__( 'Course creation date', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'date_created',
	),
);

$user_lessons_columns = array(
	array(
		'title' => esc_html__( '№', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'number',
	),
	array(
		'title' => esc_html__( 'Name', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'student_name',
	),
);

wp_localize_script(
	'masterstudy-analytics-course-page',
	'course_page_data',
	array(
		'lessons'      => $lessons_columns,
		'user_lessons' => $user_lessons_columns,
		'titles'       => array(
			'revenue_chart'      => esc_html__( 'Revenue', 'masterstudy-lms-learning-management-system-pro' ),
			'enrollments_chart'  => esc_html__( 'Total', 'masterstudy-lms-learning-management-system-pro' ),
			'preorders_chart'    => esc_html__( 'Preorders', 'masterstudy-lms-learning-management-system-pro' ),
			'email_subscribers'  => esc_html__( 'Email Subscribers', 'masterstudy-lms-learning-management-system-pro' ),
			'enrollments_status' => array(
				esc_html__( 'Not started', 'masterstudy-lms-learning-management-system-pro' ),
				esc_html__( 'In progress', 'masterstudy-lms-learning-management-system-pro' ),
				esc_html__( 'Completed', 'masterstudy-lms-learning-management-system-pro' ),
			),
			'assignments_chart'  => array(
				esc_html__( 'In progress', 'masterstudy-lms-learning-management-system-pro' ),
				esc_html__( 'Pending review', 'masterstudy-lms-learning-management-system-pro' ),
				esc_html__( 'Passed', 'masterstudy-lms-learning-management-system-pro' ),
				esc_html__( 'Failed', 'masterstudy-lms-learning-management-system-pro' ),
			),
		),
	)
);

$stats_types = array(
	'revenue',
	'enrollments',
	'orders',
	'reviews',
	'all_lessons',
);

if ( is_ms_lms_addon_enabled( 'certificate_builder' ) ) {
	array_splice( $stats_types, 5, 0, 'certificates' );
}

$charts_data = array(
	array(
		'title' => esc_html__( 'Enrollments by status', 'masterstudy-lms-learning-management-system-pro' ),
		'id'    => 'enrollments-status',
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
		'title' => esc_html__( 'Lessons engagement', 'masterstudy-lms-learning-management-system-pro' ),
		'id'    => 'lessons-table',
	),
	array(
		'title' => esc_html__( 'Revenue', 'masterstudy-lms-learning-management-system-pro' ),
		'id'    => 'revenue-chart',
	),
	array(
		'title' => esc_html__( 'Enrollments', 'masterstudy-lms-learning-management-system-pro' ),
		'id'    => 'enrollments-chart',
	),
);

$upcoming_addon = is_ms_lms_addon_enabled( 'coming_soon' );

if ( $upcoming_addon ) {
	$tables_data[] = array(
		'title' => esc_html__( 'Preorders', 'masterstudy-lms-learning-management-system-pro' ),
		'id'    => 'preorders-chart',
	);
}

$lesson_progress = array(
	'student_name'  => esc_html__( 'Order by student name', 'masterstudy-lms-learning-management-system-pro' ),
	'progress'      => esc_html__( 'Order by progress', 'masterstudy-lms-learning-management-system-pro' ),
	'progress_down' => esc_html__( 'Order by progress down', 'masterstudy-lms-learning-management-system-pro' ),
);

$lesson_types = array(
	'all'         => esc_html__( 'All lesson types', 'masterstudy-lms-learning-management-system-pro' ),
	'stm-quizzes' => esc_html__( 'Quiz\'s', 'masterstudy-lms-learning-management-system-pro' ),
	'stm-lessons' => esc_html__( 'Text lessons', 'masterstudy-lms-learning-management-system-pro' ),
	'stream'      => esc_html__( 'Stream lessons', 'masterstudy-lms-learning-management-system-pro' ),
	'video'       => esc_html__( 'Video lessons', 'masterstudy-lms-learning-management-system-pro' ),
);
if ( is_ms_lms_addon_enabled( 'assignments' ) ) {
	$lesson_types['stm-assignments'] = esc_html__( 'Assignments', 'masterstudy-lms-learning-management-system-pro' );
}
if ( is_ms_lms_addon_enabled( 'zoom_conference' ) ) {
	$lesson_types['zoom_conference'] = esc_html__( 'Zoom conferences', 'masterstudy-lms-learning-management-system-pro' );
}
if ( is_ms_lms_addon_enabled( 'google_meet' ) ) {
	$lesson_types['stm-google-meets'] = esc_html__( 'Google meets', 'masterstudy-lms-learning-management-system-pro' );
}
if ( is_ms_lms_addon_enabled( 'audio_lesson' ) ) {
	$lesson_types['audio'] = esc_html__( 'Audio lessons', 'masterstudy-lms-learning-management-system-pro' );
}


$course_title = isset( $_GET['course_id'] ) ? esc_html( get_the_title( intval( $_GET['course_id'] ) ) ) : esc_html__( 'Course', 'masterstudy-lms-learning-management-system-pro' );

if ( $is_user_account ) {
	do_action( 'stm_lms_template_main' );

	$previous_page = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : STM_LMS_User::login_page_url() . 'analytics/';
	$current_url   = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : STM_LMS_User::login_page_url() . 'analytics/';
	$url_parts     = explode( '/', trim( $current_url, '/' ) );
	$course_key    = array_search( 'course', $url_parts, true );
	if ( false !== $course_key && isset( $url_parts[ $course_key + 1 ] ) ) {
		$course_id    = intval( $url_parts[ $course_key + 1 ] );
		$course_title = get_the_title( intval( $course_id ) );
	}
	?>
	<div class="stm-lms-wrapper user-account-page">
		<div class="container">
			<?php
			do_action( 'stm_lms_admin_after_wrapper_start', STM_LMS_User::get_current_user() );
}
?>
			<div class="masterstudy-analytics-course-page">
				<div class="masterstudy-analytics-course-page__header">
					<?php
					STM_LMS_Templates::show_lms_template(
						'components/back-link',
						array(
							'id'  => 'course',
							'url' => $is_user_account ? $previous_page : masterstudy_get_current_url( array( 'course_id' ) ),
						)
					);
					?>
					<h1 class="masterstudy-analytics-course-page__title">
						<?php echo esc_html( $course_title ); ?>
					</h1>
					<div class="masterstudy-analytics__date">
						<div class="masterstudy-analytics__date-label"></div>
						<div class="masterstudy-analytics__date-value"></div>
					</div>
				</div>
				<?php if ( $is_user_account ) { ?>
					<div class="masterstudy-analytics-course-page__separator">
						<span class="masterstudy-analytics-course-page__separator-short"></span>
						<span class="masterstudy-analytics-course-page__separator-long"></span>
					</div>
					<?php
				}
				STM_LMS_Templates::show_lms_template(
					'components/analytics/datepicker-modal',
					array(
						'id' => 'course',
					)
				);
				?>
				<div class="masterstudy-analytics-course-page-stats">
					<div class="masterstudy-analytics-course-page-stats__wrapper">
						<?php foreach ( $stats_types as $item ) { ?>
							<div class="masterstudy-analytics-course-page-stats__block" data-id="<?php echo esc_attr( $item ); ?>">
								<?php
								STM_LMS_Templates::show_lms_template( 'components/analytics/loader', array( 'loader_type' => 'data-loader' ) );
								STM_LMS_Templates::show_lms_template(
									'components/analytics/stats-block',
									array(
										'type' => $item,
									)
								);
								?>
							</div>
						<?php } ?>
					</div>
				</div>
				<div class="masterstudy-analytics-course-page__row">
					<div class="masterstudy-analytics-course-page-line" data-chart-id="revenue-chart">
						<div class="masterstudy-analytics-course-page-line__wrapper">
							<div class="masterstudy-analytics-course-page-line__content">
								<?php STM_LMS_Templates::show_lms_template( 'components/analytics/loader', array( 'loader_type' => 'line-chart-loader' ) ); ?>
								<div class="masterstudy-analytics-course-page-line__header">
									<h2 class="masterstudy-analytics-course-page-line__title">
										<?php echo esc_html__( 'Revenue', 'masterstudy-lms-learning-management-system-pro' ); ?>
									</h2>
									<div id="revenue-total" class="masterstudy-analytics-course-page-line__single-total"></div>
								</div>
								<div class="masterstudy-analytics-course-page-line__chart">
									<?php
									STM_LMS_Templates::show_lms_template(
										'components/analytics/line-chart',
										array(
											'id' => 'revenue',
										)
									);
									?>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="masterstudy-analytics-course-page__row">
					<div class="masterstudy-analytics-course-page-line" data-chart-id="enrollments-chart">
						<div class="masterstudy-analytics-course-page-line__wrapper">
							<div class="masterstudy-analytics-course-page-line__content">
								<?php STM_LMS_Templates::show_lms_template( 'components/analytics/loader', array( 'loader_type' => 'line-chart-loader' ) ); ?>
								<div class="masterstudy-analytics-course-page-line__header">
									<h2 class="masterstudy-analytics-course-page-line__title">
										<?php echo esc_html__( 'Enrollments', 'masterstudy-lms-learning-management-system-pro' ); ?>
									</h2>
									<div id="enrollments-total" class="masterstudy-analytics-course-page-line__single-total"></div>
								</div>
								<div class="masterstudy-analytics-course-page-line__chart">
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
					<?php if ( $upcoming_addon ) { ?>
						<div class="masterstudy-analytics-course-page-line" data-chart-id="preorders-chart">
							<div class="masterstudy-analytics-course-page-line__wrapper">
								<div class="masterstudy-analytics-course-page-line__content">
									<?php STM_LMS_Templates::show_lms_template( 'components/analytics/loader', array( 'loader_type' => 'line-chart-loader' ) ); ?>
									<div class="masterstudy-analytics-course-page-line__header">
										<h2 class="masterstudy-analytics-course-page-line__title">
											<?php echo esc_html__( 'Preorders', 'masterstudy-lms-learning-management-system-pro' ); ?>
										</h2>
										<div id="preorders-total" class="masterstudy-analytics-course-page-line__single-total"></div>
									</div>
									<div class="masterstudy-analytics-course-page-line__chart">
										<?php
										STM_LMS_Templates::show_lms_template(
											'components/analytics/line-chart',
											array(
												'id' => 'preorders',
											)
										);
										?>
									</div>
								</div>
							</div>
						</div>
					<?php } ?>
				</div>
				<div class="masterstudy-analytics-course-page-table" data-chart-id="lessons-users">
					<?php STM_LMS_Templates::show_lms_template( 'components/analytics/loader', array( 'loader_type' => 'table-loader' ) ); ?>
					<div class="masterstudy-analytics-course-page-table__wrapper">
						<div class="masterstudy-analytics-course-page-table__header">
							<div class="masterstudy-analytics-course-page-table__title">
								<?php echo esc_html__( 'Students engagement by lessons', 'masterstudy-lms-learning-management-system-pro' ); ?>
							</div>
							<div class="masterstudy-analytics-course-page-table-select-filters">
								<select name="sort"
										class="masterstudy-analytics-course-page-table__filter"
										id="masterstudy-analytics-course-page-orders">
									<?php foreach ( $lesson_progress as $name => $item ) : ?>
										<option value="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $item ); ?></option>
									<?php endforeach; ?>
								</select>
								<select name="type"
										class="masterstudy-analytics-course-page-table__filter"
										id="masterstudy-analytics-course-page-types">
									<?php foreach ( $lesson_types as $name => $item ) : ?>
										<option value="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $item ); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="masterstudy-analytics-course-page-table__search-wrapper">
								<input type="text" id="table-lessons-search" class="masterstudy-analytics-course-page-table__search" placeholder="<?php echo esc_html__( 'Search by student name', 'masterstudy-lms-learning-management-system-pro' ); ?>">
								<span class="masterstudy-analytics-course-page-table__search-icon"></span>
							</div>
						</div>
						<?php
						STM_LMS_Templates::show_lms_template(
							'components/analytics/datatable',
							array(
								'id'      => 'lessons-by-users',
								'columns' => array(),
							)
						);
						?>
					</div>
				</div>
				<?php foreach ( $charts_data as $data ) { ?>
					<div class="masterstudy-analytics-course-page-doughnut" data-chart-id="<?php echo esc_attr( $data['id'] ); ?>">
						<div class="masterstudy-analytics-course-page-doughnut__wrapper">
							<div class="masterstudy-analytics-course-page-doughnut__content">
								<?php STM_LMS_Templates::show_lms_template( 'components/analytics/loader', array( 'loader_type' => 'doughnut-x-loader' ) ); ?>
								<div class="masterstudy-analytics-course-page-doughnut__header">
									<h2 class="masterstudy-analytics-course-page-doughnut__title">
										<?php echo esc_html( $data['title'] ); ?>
									</h2>
								</div>
								<div class="masterstudy-analytics-course-page-doughnut__chart">
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
										'quantity' => 'enrollments-status' === $data['id'] ? 3 : 4,
									),
								);
								?>
							</div>
						</div>
					</div>
				<?php } ?>
				<div class="masterstudy-analytics-course-page-table" data-chart-id="lessons-table">
					<?php STM_LMS_Templates::show_lms_template( 'components/analytics/loader', array( 'loader_type' => 'table-loader' ) ); ?>
					<div class="masterstudy-analytics-course-page-table__wrapper">
						<div class="masterstudy-analytics-course-page-table__header">
							<div class="masterstudy-analytics-course-page-table__title">
								<?php echo esc_html__( 'Lessons engagement', 'masterstudy-lms-learning-management-system-pro' ); ?>
							</div>
							<div class="masterstudy-analytics-course-page-table__search-wrapper">
								<input type="text" id="table-lessons-search" class="masterstudy-analytics-course-page-table__search" placeholder="<?php echo esc_html__( 'Search', 'masterstudy-lms-learning-management-system-pro' ); ?>">
								<span class="masterstudy-analytics-course-page-table__search-icon"></span>
							</div>
						</div>
						<?php
						STM_LMS_Templates::show_lms_template(
							'components/analytics/datatable',
							array(
								'id'      => 'lessons',
								'columns' => $lessons_columns,
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
