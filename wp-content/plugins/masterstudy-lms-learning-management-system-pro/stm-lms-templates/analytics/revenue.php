<?php
$is_user_account = ! is_admin();

if ( $is_user_account ) {
	get_header();
}

use MasterStudy\Lms\Plugin\Addons;

wp_enqueue_style( 'masterstudy-analytics-revenue-page' );
wp_enqueue_style( 'masterstudy-analytics-components' );
wp_enqueue_script( 'masterstudy-analytics-revenue-page' );

$table_routes = array(
	'courses'  => 'revenue/courses',
	'students' => 'revenue/students',
);

$revenue_page_data['search_placeholders'] = array(
	$table_routes['courses']  => esc_html__( 'Search by course name', 'masterstudy-lms-learning-management-system-pro' ),
	$table_routes['students'] => esc_html__( 'Search by student name', 'masterstudy-lms-learning-management-system-pro' ),
);

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

$table_tabs = array(
	array(
		'id'    => $table_routes['courses'],
		'title' => esc_html__( 'Courses', 'masterstudy-lms-learning-management-system-pro' ),
	),
	array(
		'id'    => $table_routes['students'],
		'title' => esc_html__( 'Students', 'masterstudy-lms-learning-management-system-pro' ),
	),
);

$stats_types = array(
	'revenue',
	'courses',
	'orders',
);

if ( STM_LMS_Subscriptions::subscription_enabled() && ! $is_user_account ) {
	$stats_types[] = 'memberships';
}

$revenue_page_data = array(
	$table_routes['courses']  => $courses_columns,
	$table_routes['students'] => array(
		array(
			'title' => esc_html__( '№', 'masterstudy-lms-learning-management-system-pro' ),
			'data'  => 'number',
		),
		array(
			'title' => esc_html__( 'Name', 'masterstudy-lms-learning-management-system-pro' ),
			'data'  => 'name',
		),
		array(
			'title' => esc_html__( 'Total orders', 'masterstudy-lms-learning-management-system-pro' ),
			'data'  => 'total_orders',
		),
		array(
			'title' => esc_html__( 'Purchased courses', 'masterstudy-lms-learning-management-system-pro' ),
			'data'  => 'courses',
		),
		array(
			'title' => esc_html__( 'Revenue', 'masterstudy-lms-learning-management-system-pro' ),
			'data'  => 'revenue',
		),
		array(
			'title' => '',
			'data'  => 'student_id',
		),
	),
	'titles'                  => array(
		'revenue'     => esc_html__( 'Revenue', 'masterstudy-lms-learning-management-system-pro' ),
		'by_product'  => array(
			esc_html__( 'Courses', 'masterstudy-lms-learning-management-system-pro' ),
			esc_html__( 'Bundles', 'masterstudy-lms-learning-management-system-pro' ),
		),
		'by_students' => array(
			esc_html__( 'Existing students', 'masterstudy-lms-learning-management-system-pro' ),
			esc_html__( 'New students', 'masterstudy-lms-learning-management-system-pro' ),
		),
		'payouts'     => array(
			esc_html__( 'Instructor revenue', 'masterstudy-lms-learning-management-system-pro' ),
			esc_html__( 'Admin commission', 'masterstudy-lms-learning-management-system-pro' ),
		),
	),
	'user_account_url'        => STM_LMS_User::login_page_url() . 'analytics/',
);

$revenue_page_data['search_placeholders'] = array(
	$table_routes['courses']  => esc_html__( 'Search by course name', 'masterstudy-lms-learning-management-system-pro' ),
	$table_routes['students'] => esc_html__( 'Search by student name', 'masterstudy-lms-learning-management-system-pro' ),
);

$charts_data = array(
	array(
		'title' => esc_html__( 'By new vs existing students', 'masterstudy-lms-learning-management-system-pro' ),
		'id'    => 'by-students',
	),
);

if ( is_ms_lms_addon_enabled( 'enterprise_courses' ) ) {
	$table_routes['groups'] = 'revenue/groups';
	$revenue_page_data['search_placeholders'][ $table_routes['groups'] ] = esc_html__( 'Search by group name', 'masterstudy-lms-learning-management-system-pro' );

	$revenue_page_data[ $table_routes['groups'] ] = array(
		array(
			'title' => esc_html__( '№', 'masterstudy-lms-learning-management-system-pro' ),
			'data'  => 'number',
		),
		array(
			'title' => esc_html__( 'Group name', 'masterstudy-lms-learning-management-system-pro' ),
			'data'  => 'group_name',
		),
		array(
			'title' => esc_html__( 'Orders', 'masterstudy-lms-learning-management-system-pro' ),
			'data'  => 'orders',
		),
		array(
			'title' => esc_html__( 'Courses', 'masterstudy-lms-learning-management-system-pro' ),
			'data'  => 'courses',
		),
		array(
			'title' => esc_html__( 'Students', 'masterstudy-lms-learning-management-system-pro' ),
			'data'  => 'students',
		),
		array(
			'title' => esc_html__( 'Revenue', 'masterstudy-lms-learning-management-system-pro' ),
			'data'  => 'revenue',
		),
	);

	array_splice(
		$table_tabs,
		1,
		0,
		array(
			array(
				'id'    => $table_routes['groups'],
				'title' => esc_html__( 'Groups', 'masterstudy-lms-learning-management-system-pro' ),
			),
		),
	);

	array_splice(
		$revenue_page_data[ $table_routes['students'] ],
		4,
		0,
		array(
			array(
				'title' => esc_html__( 'Group courses purchased', 'masterstudy-lms-learning-management-system-pro' ),
				'data'  => 'purchased_groups',
			),
		)
	);
}

if ( is_ms_lms_addon_enabled( Addons::COURSE_BUNDLE ) ) {
	$table_routes['bundles'] = 'revenue/bundles';

	$revenue_page_data['search_placeholders'][ $table_routes['bundles'] ] = esc_html__( 'Search by bundle name', 'masterstudy-lms-learning-management-system-pro' );


	$revenue_page_data[ $table_routes['bundles'] ] = array(
		array(
			'title' => esc_html__( '№', 'masterstudy-lms-learning-management-system-pro' ),
			'data'  => 'number',
		),
		array(
			'title' => esc_html__( 'Bundle name', 'masterstudy-lms-learning-management-system-pro' ),
			'data'  => 'bundle_name',
		),
		array(
			'title' => esc_html__( 'Orders', 'masterstudy-lms-learning-management-system-pro' ),
			'data'  => 'orders',
		),
		array(
			'title' => esc_html__( 'Courses inside', 'masterstudy-lms-learning-management-system-pro' ),
			'data'  => 'courses_inside',
		),
		array(
			'title' => esc_html__( 'Revenue', 'masterstudy-lms-learning-management-system-pro' ),
			'data'  => 'revenue',
		),
	);

	array_splice(
		$charts_data,
		0,
		0,
		array(
			array(
				'title' => esc_html__( 'By product', 'masterstudy-lms-learning-management-system-pro' ),
				'id'    => 'by-product',
			),
		),
	);

	array_splice( $stats_types, 2, 0, 'bundles' );

	array_splice(
		$table_tabs,
		2,
		0,
		array(
			array(
				'id'    => $table_routes['bundles'],
				'title' => esc_html__( 'Bundles', 'masterstudy-lms-learning-management-system-pro' ),
			),
		),
	);

	array_splice(
		$revenue_page_data[ $table_routes['students'] ],
		4,
		0,
		array(
			array(
				'title' => esc_html__( 'Purchased bundles', 'masterstudy-lms-learning-management-system-pro' ),
				'data'  => 'bundles',
			),
		)
	);
}


wp_localize_script(
	'masterstudy-analytics-revenue-page',
	'revenue_page_data',
	$revenue_page_data,
);

if ( is_ms_lms_addon_enabled( 'statistics' ) && ( $is_user_account && STM_LMS_Options::get_option( 'instructors_payouts', true ) ) || ! $is_user_account ) {
	$charts_data [] = array(
		'title' => esc_html__( 'Processed payouts', 'masterstudy-lms-learning-management-system-pro' ),
		'id'    => 'payouts',
	);
}

$tables_data = array(
	array(
		'title' => esc_html__( 'Revenue chart', 'masterstudy-lms-learning-management-system-pro' ),
		'id'    => 'revenue-chart',
	),
	array(
		'title' => esc_html__( 'Revenue table', 'masterstudy-lms-learning-management-system-pro' ),
		'id'    => 'revenue-table',
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
			<div class="masterstudy-analytics-revenue-page">
				<?php
				STM_LMS_Templates::show_lms_template(
					'analytics/partials/header',
					array(
						'page_slug'            => 'revenue',
						'page_title'           => esc_html__( 'Revenue', 'masterstudy-lms-learning-management-system-pro' ),
						'settings_title'       => esc_html__( 'Revenue reports page', 'masterstudy-lms-learning-management-system-pro' ),
						'settings_description' => esc_html__( 'You can choose the information you want to see in your revenue reports.', 'masterstudy-lms-learning-management-system-pro' ),
						'tables_data'          => $tables_data,
						'charts_data'          => $charts_data,
						'is_user_account'      => $is_user_account,
					)
				);

				STM_LMS_Templates::show_lms_template(
					'analytics/partials/stats-section',
					array(
						'page_slug'   => 'revenue',
						'stats_types' => $stats_types,
					)
				);
				?>
				<div class="masterstudy-analytics-revenue-page-line" data-chart-id="revenue-chart">
					<div class="masterstudy-analytics-revenue-page-line__wrapper">
						<div class="masterstudy-analytics-revenue-page-line__content">
							<?php STM_LMS_Templates::show_lms_template( 'components/analytics/loader', array( 'loader_type' => 'line-chart-loader' ) ); ?>
							<div class="masterstudy-analytics-revenue-page-line__header">
								<h2 class="masterstudy-analytics-revenue-page-line__title">
									<?php echo esc_html__( 'Revenue', 'masterstudy-lms-learning-management-system-pro' ); ?>
								</h2>
								<div id="revenue-total" class="masterstudy-analytics-revenue-page-line__total"></div>
								<?php
								STM_LMS_Templates::show_lms_template(
									'components/analytics/settings-dropdown',
									array(
										'id'         => 'revenue',
										'menu_items' => array(
											array(
												'id'    => 'revenue-chart',
												'title' => esc_html__( 'Hide report', 'masterstudy-lms-learning-management-system-pro' ),
											),
										),
									)
								);
								?>
							</div>
							<div class="masterstudy-analytics-revenue-page-line__chart">
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
				<?php foreach ( $charts_data as $data ) { ?>
					<div class="masterstudy-analytics-revenue-page-doughnut" data-chart-id="<?php echo esc_attr( $data['id'] ); ?>">
						<div class="masterstudy-analytics-revenue-page-doughnut__wrapper">
							<div class="masterstudy-analytics-revenue-page-doughnut__content">
								<?php STM_LMS_Templates::show_lms_template( 'components/analytics/loader', array( 'loader_type' => 'doughnut-y-loader' ) ); ?>
								<div class="masterstudy-analytics-revenue-page-doughnut__header">
									<h2 class="masterstudy-analytics-revenue-page-doughnut__title">
										<?php echo esc_html( $data['title'] ); ?>
									</h2>
									<?php
									STM_LMS_Templates::show_lms_template(
										'components/analytics/settings-dropdown',
										array(
											'id'         => 'revenue',
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
								<div class="masterstudy-analytics-revenue-page-doughnut__chart">
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
										'quantity' => 2,
									),
								);
								?>
							</div>
						</div>
					</div>
				<?php } ?>
				<div class="masterstudy-analytics-revenue-page-table" data-chart-id="revenue-table">
					<?php STM_LMS_Templates::show_lms_template( 'components/analytics/loader', array( 'loader_type' => 'table-loader' ) ); ?>
					<div class="masterstudy-analytics-revenue-page-table__wrapper">
						<div class="masterstudy-analytics-revenue-page-table__header">
							<div class="masterstudy-analytics-revenue-page-table__title">
								<?php echo esc_html__( 'Revenue', 'masterstudy-lms-learning-management-system-pro' ); ?>
							</div>
							<div class="masterstudy-analytics-table__tabs">
								<?php
								STM_LMS_Templates::show_lms_template(
									'components/tabs',
									array(
										'items'            => $table_tabs,
										'style'            => 'default',
										'active_tab_index' => 0,
										'dark_mode'        => false,
									)
								);
								?>
							</div>
							<div class="masterstudy-analytics-revenue-page-table__search-wrapper">
								<input type="text" id="table-revenue-search" class="masterstudy-analytics-revenue-page-table__search" placeholder="<?php echo esc_html__( 'Search by course name', 'masterstudy-lms-learning-management-system-pro' ); ?>">
								<span class="masterstudy-analytics-revenue-page-table__search-icon"></span>
							</div>
							<?php
							STM_LMS_Templates::show_lms_template(
								'components/analytics/settings-dropdown',
								array(
									'id'         => 'revenue',
									'menu_items' => array(
										array(
											'id'    => 'revenue-table',
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
								'id'      => 'revenue',
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
