<?php
wp_enqueue_style( 'masterstudy-analytics-instructor-page' );
wp_enqueue_style( 'masterstudy-analytics-components' );
wp_enqueue_script( 'masterstudy-analytics-instructor-page' );

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
		'title' => esc_html__( 'Enrollments', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'enrollments',
	),
	array(
		'title' => esc_html__( 'Revenue', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'revenue',
	),
	array(
		'title' => esc_html__( 'Views', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'views',
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

wp_localize_script(
	'masterstudy-analytics-instructor-page',
	'instructor_page_data',
	array(
		'courses'              => $courses_columns,
		'membership'           => $membership_columns,
		'is_membership_active' => $is_membership_active,
		'titles'               => array(
			'revenue_chart'     => esc_html__( 'Revenue', 'masterstudy-lms-learning-management-system-pro' ),
			'enrollments_chart' => array(
				'total'  => esc_html__( 'Total', 'masterstudy-lms-learning-management-system-pro' ),
				'unique' => esc_html__( 'Unique', 'masterstudy-lms-learning-management-system-pro' ),
			),
		),
	)
);

$charts_data = array(
	array(
		'title' => esc_html__( 'Total Revenue', 'masterstudy-lms-learning-management-system-pro' ),
		'id'    => 'revenue-chart',
	),
	array(
		'title' => esc_html__( 'Total Enrollments', 'masterstudy-lms-learning-management-system-pro' ),
		'id'    => 'enrollments-chart',
	),
);

$tables_data = array(
	array(
		'title' => esc_html__( 'Courses', 'masterstudy-lms-learning-management-system-pro' ),
		'id'    => 'courses-table',
	),
);

$instructor_id = isset( $_GET['user_id'] ) ? intval( wp_unslash( $_GET['user_id'] ) ) : '';
?>

<div class="masterstudy-analytics-instructor-page">
	<?php
	STM_LMS_Templates::show_lms_template(
		'analytics/partials/user-header',
		array(
			'page_slug'       => 'instructor',
			'default_title'   => esc_html__( 'Instructor', 'masterstudy-lms-learning-management-system-pro' ),
			'previous_page'   => '',
			'is_user_account' => false,
			'user_id'         => $instructor_id,
		)
	);
	?>
	<div class="masterstudy-analytics-instructor-page-line" data-chart-id="revenue-chart">
		<div class="masterstudy-analytics-instructor-page-line__wrapper">
			<div class="masterstudy-analytics-instructor-page-line__content">
				<?php STM_LMS_Templates::show_lms_template( 'components/analytics/loader', array( 'loader_type' => 'line-chart-loader' ) ); ?>
				<div class="masterstudy-analytics-instructor-page-line__header">
					<h2 class="masterstudy-analytics-instructor-page-line__title">
						<?php echo esc_html__( 'Total Revenue', 'masterstudy-lms-learning-management-system-pro' ); ?>
					</h2>
					<div id="revenue-total" class="masterstudy-analytics-instructor-page-line__single-total"></div>
				</div>
				<div class="masterstudy-analytics-instructor-page-line__chart">
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
	<div class="masterstudy-analytics-instructor-page-line" data-chart-id="enrollments-chart">
		<div class="masterstudy-analytics-instructor-page-line__wrapper">
			<div class="masterstudy-analytics-instructor-page-line__content">
				<?php STM_LMS_Templates::show_lms_template( 'components/analytics/loader', array( 'loader_type' => 'line-chart-loader' ) ); ?>
				<div class="masterstudy-analytics-instructor-page-line__header">
					<h2 class="masterstudy-analytics-instructor-page-line__title">
						<?php echo esc_html__( 'Total Enrollments', 'masterstudy-lms-learning-management-system-pro' ); ?>
					</h2>
					<div class="masterstudy-analytics-instructor-page-line__total-wrapper">
						<div class="masterstudy-analytics-instructor-page-line__total">
							<div class="masterstudy-analytics-instructor-page-line__total-title">
								<?php echo esc_html__( 'Total', 'masterstudy-lms-learning-management-system-pro' ); ?>:
							</div>
							<div id="enrollments-total" class="masterstudy-analytics-instructor-page-line__total-value"></div>
						</div>
						<div class="masterstudy-analytics-instructor-page-line__total">
							<div class="masterstudy-analytics-instructor-page-line__total-title">
								<?php echo esc_html__( 'Unique', 'masterstudy-lms-learning-management-system-pro' ); ?>:
							</div>
							<div id="unique-total" class="masterstudy-analytics-instructor-page-line__total-value"></div>
						</div>
					</div>
				</div>
				<div class="masterstudy-analytics-instructor-page-line__chart">
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
	<div class="masterstudy-analytics-instructor-page-table" data-chart-id="courses-table">
		<?php STM_LMS_Templates::show_lms_template( 'components/analytics/loader', array( 'loader_type' => 'table-loader' ) ); ?>
		<div class="masterstudy-analytics-instructor-page-table__wrapper">
			<div class="masterstudy-analytics-instructor-page-table__header">
				<div class="masterstudy-analytics-instructor-page-table__title">
					<?php echo esc_html__( 'Courses', 'masterstudy-lms-learning-management-system-pro' ); ?>
				</div>
				<div class="masterstudy-analytics-instructor-page-table__search-wrapper">
					<input type="text" id="table-courses-search" class="masterstudy-analytics-instructor-page-table__search" placeholder="<?php echo esc_html__( 'Search by course name', 'masterstudy-lms-learning-management-system-pro' ); ?>">
					<span class="masterstudy-analytics-instructor-page-table__search-icon"></span>
				</div>
			</div>
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
	<?php if ( $is_membership_active ) { ?>
		<div class="masterstudy-analytics-instructor-page-table" data-chart-id="membership-table">
			<?php STM_LMS_Templates::show_lms_template( 'components/analytics/loader', array( 'loader_type' => 'table-loader' ) ); ?>
			<div class="masterstudy-analytics-instructor-page-table__wrapper">
				<div class="masterstudy-analytics-instructor-page-table__header">
					<div class="masterstudy-analytics-instructor-page-table__title">
						<?php echo esc_html__( 'Memberships history', 'masterstudy-lms-learning-management-system-pro' ); ?>
					</div>
					<input type="text" id="table-membership-search" class="masterstudy-analytics-instructor-page-table__search" placeholder="<?php echo esc_html__( 'Search', 'masterstudy-lms-learning-management-system-pro' ); ?>">
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
