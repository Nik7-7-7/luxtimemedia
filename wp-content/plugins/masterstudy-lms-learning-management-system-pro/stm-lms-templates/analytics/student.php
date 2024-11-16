<?php
$is_user_account = ! is_admin();

if ( $is_user_account ) {
	get_header();
}

use MasterStudy\Lms\Plugin\Addons;

wp_enqueue_style( 'masterstudy-analytics-student-page' );
wp_enqueue_style( 'masterstudy-analytics-components' );
wp_enqueue_script( 'masterstudy-analytics-student-page' );

$courses_columns = array(
	array(
		'title' => esc_html__( '№', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'number',
	),
	array(
		'title' => esc_html__( 'Course name', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'name',
	),
	array(
		'title' => esc_html__( 'Duration', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'duration',
	),
	array(
		'title' => esc_html__( 'Started', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'started',
	),
	array(
		'title' => esc_html__( 'Progress', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'progress',
	),
);

$is_membership_active = false;
$membership_columns   = array(
	array(
		'title' => esc_html__( '№', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'number',
	),
	array(
		'title' => esc_html__( 'Plan name', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'name',
	),
	array(
		'title' => esc_html__( 'Plan price', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'price',
	),
	array(
		'title' => esc_html__( 'Date subscribed', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'date_subscribed',
	),
	array(
		'title' => esc_html__( 'Date canceled', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'date_canceled',
	),
);

$stats_types = array(
	'passed',
	'failed',
);

$revenue_stats_types = array(
	'revenue',
	'orders',
);

if ( $is_membership_active ) {
	array_splice( $revenue_stats_types, 1, 0, 'membership_plan' );
}

$courses_stats_types = array(
	'enrolled',
	'completed',
	'in_progress',
	'not_started',
);

$main_stats_types = $is_user_account ? array() : array( 'reviews' );

if ( is_ms_lms_addon_enabled( Addons::COURSE_BUNDLE ) ) {
	array_splice( $main_stats_types, 0, 0, 'bundles' );
}

if ( ! $is_user_account && is_ms_lms_addon_enabled( 'enterprise_courses' ) ) {
	array_splice( $main_stats_types, 1, 0, 'groups' );
}

if ( is_ms_lms_addon_enabled( 'certificate_builder' ) ) {
	array_splice( $main_stats_types, 3, 0, 'certificates' );
}

if ( is_ms_lms_addon_enabled( 'point_system' ) ) {
	array_splice( $main_stats_types, 4, 0, 'points' );
}

wp_localize_script(
	'masterstudy-analytics-student-page',
	'student_page_data',
	array(
		'courses'              => $courses_columns,
		'membership'           => $membership_columns,
		'is_membership_active' => $is_membership_active,
		'titles'               => array(
			'courses_chart' => array(
				'enrolled'  => esc_html__( 'Enrolled', 'masterstudy-lms-learning-management-system-pro' ),
				'completed' => esc_html__( 'Completed', 'masterstudy-lms-learning-management-system-pro' ),
			),
		),
	)
);

$charts_data = array(
	array(
		'title' => esc_html__( 'Courses', 'masterstudy-lms-learning-management-system-pro' ),
		'id'    => 'courses-chart',
	),
);

$tables_data = array(
	array(
		'title' => esc_html__( 'Courses', 'masterstudy-lms-learning-management-system-pro' ),
		'id'    => 'courses-table',
	),
);

$student_id = isset( $_GET['user_id'] ) ? intval( wp_unslash( $_GET['user_id'] ) ) : '';

if ( $is_user_account ) {
	do_action( 'stm_lms_template_main' );

	$previous_page = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : STM_LMS_User::login_page_url() . 'analytics/';
	$current_url   = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : STM_LMS_User::login_page_url() . 'analytics/';
	$url_parts     = explode( '/', trim( $current_url, '/' ) );
	$student_key   = array_search( 'student', $url_parts, true );
	if ( false !== $student_key && isset( $url_parts[ $student_key + 1 ] ) ) {
		$student_id = intval( $url_parts[ $student_key + 1 ] );
	}
	?>
	<div class="stm-lms-wrapper user-account-page">
		<div class="container">
			<?php
			do_action( 'stm_lms_admin_after_wrapper_start', STM_LMS_User::get_current_user() );
}
?>
			<div class="masterstudy-analytics-student-page">
				<?php
				STM_LMS_Templates::show_lms_template(
					'analytics/partials/user-header',
					array(
						'page_slug'       => 'student',
						'default_title'   => esc_html__( 'Student', 'masterstudy-lms-learning-management-system-pro' ),
						'previous_page'   => $is_user_account ? $previous_page : '',
						'is_user_account' => $is_user_account,
						'user_id'         => $student_id,
					)
				);
				STM_LMS_Templates::show_lms_template(
					'analytics/partials/stats-section',
					array(
						'page_slug'   => 'student',
						'stats_types' => $revenue_stats_types,
						'extra_class' => 'masterstudy-analytics-student-page-stats_main',
					)
				);
				?>
				<div class="masterstudy-analytics-student-page-line" data-chart-id="courses-chart">
					<div class="masterstudy-analytics-student-page-line__wrapper">
						<div class="masterstudy-analytics-student-page-line__content">
							<?php STM_LMS_Templates::show_lms_template( 'components/analytics/loader', array( 'loader_type' => 'line-chart-loader' ) ); ?>
							<div class="masterstudy-analytics-student-page-line__header">
								<?php echo esc_html__( 'Courses', 'masterstudy-lms-learning-management-system-pro' ); ?>
							</div>
							<div class="masterstudy-analytics-student-page-line__stats">
								<?php foreach ( $courses_stats_types as $item ) { ?>
									<div class="masterstudy-analytics-student-page-stats__block masterstudy-analytics-student-page-stats__block_courses">
										<?php
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
							<div class="masterstudy-analytics-student-page-line__chart">
								<?php
								STM_LMS_Templates::show_lms_template(
									'components/analytics/line-chart',
									array(
										'id' => 'courses',
									)
								);
								?>
							</div>
						</div>
					</div>
				</div>
				<div class="masterstudy-analytics-student-page-table" data-chart-id="courses-table">
					<?php STM_LMS_Templates::show_lms_template( 'components/analytics/loader', array( 'loader_type' => 'table-loader' ) ); ?>
					<div class="masterstudy-analytics-student-page-table__wrapper">
						<?php
						STM_LMS_Templates::show_lms_template(
							'components/analytics/datatable',
							array(
								'id'      => 'courses',
								'columns' => $courses_columns,
							)
						);
						?>
					</div>
				</div>
				<div class="masterstudy-analytics-student-page-types">
					<div class="masterstudy-analytics-student-page-types__wrapper">
						<div class="masterstudy-analytics-student-page-types__content">
							<div class="masterstudy-analytics-student-page-types__header">
								<?php echo esc_html__( 'Quizzes', 'masterstudy-lms-learning-management-system-pro' ); ?>
							</div>
							<div class="masterstudy-analytics-student-page-types__data">
								<?php foreach ( $stats_types as $item ) { ?>
									<div class="masterstudy-analytics-student-page-stats__block masterstudy-analytics-student-page-stats__block_quizzes">
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
					</div>
					<?php if ( is_ms_lms_addon_enabled( 'assignments' ) ) { ?>
						<div class="masterstudy-analytics-student-page-types__wrapper">
							<div class="masterstudy-analytics-student-page-types__content">
								<div class="masterstudy-analytics-student-page-types__header">
									<?php
									echo esc_html__( 'Assignments', 'masterstudy-lms-learning-management-system-pro' );
									STM_LMS_Templates::show_lms_template(
										'components/button',
										array(
											'id'        => 'masterstudy-analytics-assignments',
											'title'     => esc_html__( 'Assignments', 'masterstudy-lms-learning-management-system-pro' ),
											'link'      => $is_user_account ? STM_LMS_User::login_page_url() . 'assignments' : admin_url( 'edit.php?post_type=stm-user-assignment' ),
											'style'     => 'primary',
											'size'      => 'sm',
											'icon_name' => 'plus',
											'icon_position' => 'next',
											'target'    => '_blank',
										)
									);
									?>
								</div>
								<div class="masterstudy-analytics-student-page-types__data">
									<?php foreach ( $stats_types as $item ) { ?>
										<div class="masterstudy-analytics-student-page-stats__block masterstudy-analytics-student-page-stats__block_assignments">
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
						</div>
					<?php } ?>
				</div>
				<div class="masterstudy-analytics-student-page-stats masterstudy-analytics-student-page-stats_main">
					<div class="masterstudy-analytics-student-page-stats__wrapper">
						<?php foreach ( $main_stats_types as $item ) { ?>
							<div class="masterstudy-analytics-student-page-stats__block">
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
				<?php if ( $is_membership_active ) { ?>
					<div class="masterstudy-analytics-student-page-table" data-chart-id="membership-table">
						<?php STM_LMS_Templates::show_lms_template( 'components/analytics/loader', array( 'loader_type' => 'table-loader' ) ); ?>
						<div class="masterstudy-analytics-student-page-table__wrapper">
							<div class="masterstudy-analytics-student-page-table__header">
								<div class="masterstudy-analytics-student-page-table__title">
									<?php echo esc_html__( 'Memberships history', 'masterstudy-lms-learning-management-system-pro' ); ?>
								</div>
								<input type="text" id="table-courses-search" class="masterstudy-analytics-student-page-table__search" placeholder="<?php echo esc_html__( 'Search', 'masterstudy-lms-learning-management-system-pro' ); ?>">
							</div>
							<?php
							STM_LMS_Templates::show_lms_template(
								'components/analytics/datatable',
								array(
									'id'      => 'membership',
									'columns' => $membership_columns,
								)
							);
							?>
						</div>
					</div>
				<?php } ?>
			</div>
<?php
if ( $is_user_account ) {
	?>
		</div>
	</div>
	<?php
	get_footer();
}
