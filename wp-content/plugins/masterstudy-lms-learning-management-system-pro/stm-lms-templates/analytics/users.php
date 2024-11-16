<?php

wp_enqueue_style( 'masterstudy-analytics-users-page' );
wp_enqueue_style( 'masterstudy-analytics-components' );
wp_enqueue_script( 'masterstudy-analytics-users-page' );

$instructors_columns = array(
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
		'title' => esc_html__( 'Own courses', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'own_courses',
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
		'data'  => 'instructor_id',
	),
);

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

$table_routes = array(
	'instructors' => 'instructors',
	'students'    => 'students',
);

wp_localize_script(
	'masterstudy-analytics-users-page',
	'users_page_data',
	array(
		$table_routes['instructors'] => $instructors_columns,
		$table_routes['students']    => $students_columns,
		'titles'                     => array(
			'instructors_chart' => esc_html__( 'Total', 'masterstudy-lms-learning-management-system-pro' ),
			'users_chart'       => esc_html__( 'Total', 'masterstudy-lms-learning-management-system-pro' ),
		),
	)
);

$stats_types = array(
	'total',
	'registered_students',
	'instructors',
);

$charts_data = array(
	array(
		'title' => esc_html__( 'All users', 'masterstudy-lms-learning-management-system-pro' ),
		'id'    => 'users-chart',
	),
	array(
		'title' => esc_html__( 'Instructors', 'masterstudy-lms-learning-management-system-pro' ),
		'id'    => 'instructors-chart',
	),
);

$tables_data = array(
	array(
		'title' => esc_html__( 'All instructors', 'masterstudy-lms-learning-management-system-pro' ),
		'id'    => $table_routes['instructors'] . '-table',
	),
	array(
		'title' => esc_html__( 'All students', 'masterstudy-lms-learning-management-system-pro' ),
		'id'    => $table_routes['students'] . '-table',
	),
);
?>

<div class="masterstudy-analytics-users-page">
	<?php
	STM_LMS_Templates::show_lms_template(
		'analytics/partials/header',
		array(
			'page_slug'            => 'users',
			'page_title'           => esc_html__( 'Users statistics', 'masterstudy-lms-learning-management-system-pro' ),
			'settings_title'       => esc_html__( 'User reports page', 'masterstudy-lms-learning-management-system-pro' ),
			'settings_description' => esc_html__( "Choose the specific info you want to see in users' stats.", 'masterstudy-lms-learning-management-system-pro' ),
			'tables_data'          => $tables_data,
			'charts_data'          => $charts_data,
			'is_user_account'      => false,
		)
	);

	STM_LMS_Templates::show_lms_template(
		'analytics/partials/stats-section',
		array(
			'page_slug'   => 'users',
			'stats_types' => $stats_types,
		)
	);
	?>
	<div class="masterstudy-analytics-users-page-line" data-chart-id="users-chart">
		<div class="masterstudy-analytics-users-page-line__wrapper">
			<div class="masterstudy-analytics-users-page-line__content">
				<?php STM_LMS_Templates::show_lms_template( 'components/analytics/loader', array( 'loader_type' => 'line-chart-loader' ) ); ?>
				<div class="masterstudy-analytics-users-page-line__header">
					<h2 class="masterstudy-analytics-users-page-line__title">
						<?php echo esc_html__( 'All users', 'masterstudy-lms-learning-management-system-pro' ); ?>
					</h2>
					<div id="users-total" class="masterstudy-analytics-users-page-line__single-total"></div>
					<?php
					STM_LMS_Templates::show_lms_template(
						'components/analytics/settings-dropdown',
						array(
							'id'         => 'users',
							'menu_items' => array(
								array(
									'id'    => 'users-chart',
									'title' => esc_html__( 'Hide report', 'masterstudy-lms-learning-management-system-pro' ),
								),
							),
						)
					);
					?>
				</div>
				<div class="masterstudy-analytics-users-page-line__chart">
					<?php
					STM_LMS_Templates::show_lms_template(
						'components/analytics/line-chart',
						array(
							'id' => 'users',
						)
					);
					?>
				</div>
			</div>
		</div>
	</div>
	<div class="masterstudy-analytics-users-page-line" data-chart-id="instructors-chart">
		<div class="masterstudy-analytics-users-page-line__wrapper">
			<div class="masterstudy-analytics-users-page-line__content">
				<?php STM_LMS_Templates::show_lms_template( 'components/analytics/loader', array( 'loader_type' => 'line-chart-loader' ) ); ?>
				<div class="masterstudy-analytics-users-page-line__header">
					<h2 class="masterstudy-analytics-users-page-line__title">
						<?php echo esc_html__( 'Instructors', 'masterstudy-lms-learning-management-system-pro' ); ?>
					</h2>
					<div id="instructors-total" class="masterstudy-analytics-users-page-line__single-total"></div>
					<?php
					STM_LMS_Templates::show_lms_template(
						'components/analytics/settings-dropdown',
						array(
							'id'         => 'instructors',
							'menu_items' => array(
								array(
									'id'    => 'instructors-chart',
									'title' => esc_html__( 'Hide report', 'masterstudy-lms-learning-management-system-pro' ),
								),
							),
						)
					);
					?>
				</div>
				<div class="masterstudy-analytics-users-page-line__chart">
					<?php
					STM_LMS_Templates::show_lms_template(
						'components/analytics/line-chart',
						array(
							'id' => 'instructors',
						)
					);
					?>
				</div>
			</div>
		</div>
	</div>
	<div class="masterstudy-analytics-users-page-table" data-chart-id="<?php echo esc_attr( $table_routes['instructors'] . '-table' ); ?>">
		<?php STM_LMS_Templates::show_lms_template( 'components/analytics/loader', array( 'loader_type' => 'table-loader' ) ); ?>
		<div class="masterstudy-analytics-users-page-table__wrapper">
			<div class="masterstudy-analytics-users-page-table__header">
				<div class="masterstudy-analytics-users-page-table__title">
					<?php echo esc_html__( 'All instructors', 'masterstudy-lms-learning-management-system-pro' ); ?>
					<span id="<?php echo esc_attr( $table_routes['instructors'] ); ?>-table-total" class="masterstudy-analytics-users-page-table__title-value"></span>
				</div>
				<div class="masterstudy-analytics-users-page-table__search-wrapper">
					<input type="text" id="table-instructors-search" class="masterstudy-analytics-users-page-table__search" placeholder="<?php echo esc_html__( 'Search by instructor name', 'masterstudy-lms-learning-management-system-pro' ); ?>">
					<span class="masterstudy-analytics-users-page-table__search-icon"></span>
				</div>
				<?php
				STM_LMS_Templates::show_lms_template(
					'components/analytics/settings-dropdown',
					array(
						'id'         => $table_routes['instructors'],
						'menu_items' => array(
							array(
								'id'    => $table_routes['instructors'] . '-table',
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
					'id'      => $table_routes['instructors'],
					'columns' => $instructors_columns,
				)
			);
			?>
		</div>
	</div>
	<div class="masterstudy-analytics-users-page-table" data-chart-id="<?php echo esc_attr( $table_routes['students'] . '-table' ); ?>">
		<?php STM_LMS_Templates::show_lms_template( 'components/analytics/loader', array( 'loader_type' => 'table-loader' ) ); ?>
		<div class="masterstudy-analytics-users-page-table__wrapper">
			<div class="masterstudy-analytics-users-page-table__header">
				<div class="masterstudy-analytics-users-page-table__title">
					<?php echo esc_html__( 'All students', 'masterstudy-lms-learning-management-system-pro' ); ?>
					<span id="<?php echo esc_attr( $table_routes['students'] ); ?>-table-total" class="masterstudy-analytics-users-page-table__title-value"></span>
				</div>
				<div class="masterstudy-analytics-users-page-table__search-wrapper">
					<input type="text" id="table-students-search" class="masterstudy-analytics-users-page-table__search" placeholder="<?php echo esc_html__( 'Search by student name', 'masterstudy-lms-learning-management-system-pro' ); ?>">
					<span class="masterstudy-analytics-users-page-table__search-icon"></span>
				</div>
				<?php
				STM_LMS_Templates::show_lms_template(
					'components/analytics/settings-dropdown',
					array(
						'id'         => $table_routes['students'],
						'menu_items' => array(
							array(
								'id'    => $table_routes['students'] . '-table',
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
					'id'      => $table_routes['students'],
					'columns' => $students_columns,
				)
			);
			?>
		</div>
	</div>
</div>
