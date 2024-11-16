<?php
$is_user_account = ! is_admin();

if ( $is_user_account ) {
	get_header();
}

wp_enqueue_style( 'masterstudy-analytics-instructor-students-page' );
wp_enqueue_style( 'masterstudy-analytics-components' );
wp_enqueue_script( 'masterstudy-analytics-instructor-students-page' );

$students_columns = array(
	array(
		'title' => esc_html__( '№', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'number',
	),
	array(
		'title' => esc_html__( 'Name', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'name',
	),
	array(
		'title' => esc_html__( 'Enrollments', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'enrollments',
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

wp_localize_script(
	'masterstudy-analytics-instructor-students-page',
	'instructor_students_page_data',
	array(
		'instructor-students' => $students_columns,
		'user_account_url'    => STM_LMS_User::login_page_url() . 'analytics/',
	)
);

$tables_data = array(
	array(
		'title' => esc_html__( 'My students', 'masterstudy-lms-learning-management-system-pro' ),
		'id'    => 'instructor-students-table',
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
			<div class="masterstudy-analytics-instructor-students-page">
				<?php
				STM_LMS_Templates::show_lms_template(
					'analytics/partials/header',
					array(
						'page_slug'            => 'instructor-students',
						'page_title'           => '',
						'settings_title'       => '',
						'settings_description' => '',
						'tables_data'          => $tables_data,
						'charts_data'          => array(),
						'is_user_account'      => $is_user_account,
					)
				);
				?>
				<div class="masterstudy-analytics-instructor-students-page-table" data-chart-id="instructor-students-table">
					<?php STM_LMS_Templates::show_lms_template( 'components/analytics/loader', array( 'loader_type' => 'table-loader' ) ); ?>
					<div class="masterstudy-analytics-instructor-students-page-table__wrapper">
						<div class="masterstudy-analytics-instructor-students-page-table__header">
							<div class="masterstudy-analytics-instructor-students-page-table__title">
								<?php echo esc_html__( 'My students', 'masterstudy-lms-learning-management-system-pro' ); ?>
								<span id="instructor-students-table-total" class="masterstudy-analytics-instructor-students-page-table__title-value"></span>
							</div>
							<div class="masterstudy-analytics-instructor-students-page-table__search-wrapper">
								<input type="text" id="table-instructor-students-search" class="masterstudy-analytics-instructor-students-page-table__search" placeholder="<?php echo esc_html__( 'Search by name', 'masterstudy-lms-learning-management-system-pro' ); ?>">
								<span class="masterstudy-analytics-instructor-students-page-table__search-icon"></span>
							</div>
						</div>
						<?php
						STM_LMS_Templates::show_lms_template(
							'components/analytics/datatable',
							array(
								'id'      => 'instructor-students',
								'columns' => $students_columns,
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
