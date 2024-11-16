<?php
$is_user_account = ! is_admin();

if ( $is_user_account ) {
	get_header();
}

wp_enqueue_style( 'masterstudy-analytics-reviews-page' );
wp_enqueue_style( 'masterstudy-analytics-components' );
wp_enqueue_script( 'masterstudy-analytics-reviews-page' );

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
		'title' => esc_html__( 'Reviews', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'reviews',
	),
);

$reviewers_columns = array(
	array(
		'title' => esc_html__( '№', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'number',
	),
	array(
		'title' => esc_html__( 'Student name', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'name',
	),
	array(
		'title' => esc_html__( 'Reviews', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'reviews',
	),
);

$reviews_columns = array(
	array(
		'title' => esc_html__( '№', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'number',
	),
	array(
		'title' => esc_html__( 'User', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'user_name',
	),
	array(
		'title' => esc_html__( 'Course', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'course_name',
	),
	array(
		'title' => esc_html__( 'Review', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'review',
	),
	array(
		'title' => esc_html__( 'Rating', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'rating',
	),
	array(
		'title' => esc_html__( 'Review creation date', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'date_created',
	),
	array(
		'title' => '',
		'data'  => 'review_id',
	),
);

$table_routes = array(
	'courses'         => 'reviews-courses',
	'reviewers'       => 'reviews-users',
	'reviews-publish' => 'reviews-publish',
	'reviews-pending' => 'reviews-pending',
);

wp_localize_script(
	'masterstudy-analytics-reviews-page',
	'reviews_page_data',
	array(
		$table_routes['courses']   => $courses_columns,
		$table_routes['reviewers'] => $reviewers_columns,
		'reviews'                  => $reviews_columns,
		'user_account_url'         => STM_LMS_User::login_page_url() . 'analytics/',
	)
);

$charts_data = array(
	array(
		'title' => esc_html__( 'Review chart', 'masterstudy-lms-learning-management-system-pro' ),
		'id'    => 'reviews-chart',
	),
	array(
		'title' => esc_html__( 'Review types', 'masterstudy-lms-learning-management-system-pro' ),
		'id'    => 'reviews-type-chart',
	),
);

$tables_data = array(
	array(
		'title' => esc_html__( 'Top reviewed courses', 'masterstudy-lms-learning-management-system-pro' ),
		'id'    => $table_routes['courses'] . '-table',
	),
	array(
		'title' => esc_html__( 'Top reviewers', 'masterstudy-lms-learning-management-system-pro' ),
		'id'    => $table_routes['reviewers'] . '-table',
	),
	array(
		'title' => esc_html__( 'Review table', 'masterstudy-lms-learning-management-system-pro' ),
		'id'    => 'reviews-table',
	),
);

$reviews_totals = array(
	'one'   => 1,
	'two'   => 2,
	'three' => 3,
	'four'  => 4,
	'five'  => 5,
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
			<div class="masterstudy-analytics-reviews-page">
				<?php
				STM_LMS_Templates::show_lms_template(
					'analytics/partials/header',
					array(
						'page_slug'            => 'reviews',
						'page_title'           => esc_html__( 'Reviews Report', 'masterstudy-lms-learning-management-system-pro' ),
						'settings_title'       => esc_html__( 'Review reports page', 'masterstudy-lms-learning-management-system-pro' ),
						'settings_description' => esc_html__( 'Select the sections you want to see in your review reports.', 'masterstudy-lms-learning-management-system-pro' ),
						'tables_data'          => $tables_data,
						'charts_data'          => $charts_data,
						'is_user_account'      => $is_user_account,
					)
				);
				?>
				<div class="masterstudy-analytics-reviews-page__row">
					<div class="masterstudy-analytics-reviews-page-line" data-chart-id="reviews-chart">
						<div class="masterstudy-analytics-reviews-page-line__wrapper">
							<div class="masterstudy-analytics-reviews-page-line__content">
								<?php STM_LMS_Templates::show_lms_template( 'components/analytics/loader', array( 'loader_type' => 'line-chart-loader' ) ); ?>
								<div class="masterstudy-analytics-reviews-page-line__header">
									<h2 class="masterstudy-analytics-reviews-page-line__title">
										<?php echo esc_html__( 'Reviews', 'masterstudy-lms-learning-management-system-pro' ); ?>
									</h2>
									<div id="reviews-total" class="masterstudy-analytics-reviews-page-line__single-total"></div>
									<?php
									STM_LMS_Templates::show_lms_template(
										'components/analytics/settings-dropdown',
										array(
											'id'         => 'reviews',
											'menu_items' => array(
												array(
													'id' => 'reviews-chart',
													'title' => esc_html__( 'Hide report', 'masterstudy-lms-learning-management-system-pro' ),
												),
											),
										)
									);
									?>
								</div>
								<div class="masterstudy-analytics-reviews-page-line__chart">
									<?php
									STM_LMS_Templates::show_lms_template(
										'components/analytics/line-chart',
										array(
											'id' => 'reviews',
										)
									);
									?>
								</div>
							</div>
						</div>
					</div>
					<div class="masterstudy-analytics-reviews-page-line" data-chart-id="reviews-type-chart">
						<div class="masterstudy-analytics-reviews-page-line__wrapper">
							<div class="masterstudy-analytics-reviews-page-line__content">
								<?php STM_LMS_Templates::show_lms_template( 'components/analytics/loader', array( 'loader_type' => 'line-chart-loader' ) ); ?>
								<div class="masterstudy-analytics-reviews-page-line__header">
									<h2 class="masterstudy-analytics-reviews-page-line__title">
										<?php echo esc_html__( 'Review types', 'masterstudy-lms-learning-management-system-pro' ); ?>
									</h2>
									<?php
									STM_LMS_Templates::show_lms_template(
										'components/analytics/settings-dropdown',
										array(
											'id'         => 'reviews-type',
											'menu_items' => array(
												array(
													'id' => 'reviews-type-chart',
													'title' => esc_html__( 'Hide report', 'masterstudy-lms-learning-management-system-pro' ),
												),
											),
										)
									);
									?>
								</div>
								<div class="masterstudy-analytics-reviews-page-line__chart">
									<?php
									STM_LMS_Templates::show_lms_template(
										'components/analytics/line-chart',
										array(
											'id' => 'reviews-type',
										)
									);
									?>
								</div>
								<div class="masterstudy-analytics-reviews-page-line__totals">
									<?php foreach ( $reviews_totals as $item_id => $item_title ) { ?>
										<div id="reviews-totals-<?php echo esc_attr( $item_id ); ?>" class="masterstudy-analytics-reviews-page-line__totals-block">
											<span class="masterstudy-analytics-reviews-page-line__totals-icon"></span>
											<span class="masterstudy-analytics-reviews-page-line__totals-title">
												<?php echo esc_html( $item_title ); ?>
												<?php echo 1 === $item_title ? esc_html__( 'star', 'masterstudy-lms-learning-management-system-pro' ) : esc_html__( 'stars', 'masterstudy-lms-learning-management-system-pro' ); ?>
											</span>
											<span class="masterstudy-analytics-reviews-page-line__totals-value"></span>
										</div>
									<?php } ?>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="masterstudy-analytics-reviews-page__row">
					<div class="masterstudy-analytics-reviews-page-table" data-chart-id="<?php echo esc_attr( $table_routes['courses'] . '-table' ); ?>">
						<?php STM_LMS_Templates::show_lms_template( 'components/analytics/loader', array( 'loader_type' => 'table-loader' ) ); ?>
						<div class="masterstudy-analytics-reviews-page-table__wrapper">
							<div class="masterstudy-analytics-reviews-page-table__header">
								<div class="masterstudy-analytics-reviews-page-table__title">
									<?php echo esc_html__( 'Top reviewed courses', 'masterstudy-lms-learning-management-system-pro' ); ?>
								</div>
								<?php
								STM_LMS_Templates::show_lms_template(
									'components/analytics/settings-dropdown',
									array(
										'id'         => $table_routes['courses'],
										'menu_items' => array(
											array(
												'id'    => $table_routes['courses'] . '-table',
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
									'id'      => $table_routes['courses'],
									'columns' => $courses_columns,
								)
							);
							?>
						</div>
					</div>
					<div class="masterstudy-analytics-reviews-page-table" data-chart-id="<?php echo esc_attr( $table_routes['reviewers'] . '-table' ); ?>">
						<?php STM_LMS_Templates::show_lms_template( 'components/analytics/loader', array( 'loader_type' => 'table-loader' ) ); ?>
						<div class="masterstudy-analytics-reviews-page-table__wrapper">
							<div class="masterstudy-analytics-reviews-page-table__header">
								<div class="masterstudy-analytics-reviews-page-table__title">
									<?php echo esc_html__( 'Top reviewers', 'masterstudy-lms-learning-management-system-pro' ); ?>
								</div>
								<?php
								STM_LMS_Templates::show_lms_template(
									'components/analytics/settings-dropdown',
									array(
										'id'         => $table_routes['reviewers'],
										'menu_items' => array(
											array(
												'id'    => $table_routes['reviewers'] . '-table',
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
									'id'      => $table_routes['reviewers'],
									'columns' => $reviewers_columns,
								)
							);
							?>
						</div>
					</div>
				</div>
				<div class="masterstudy-analytics-reviews-page-table masterstudy-analytics-reviews-page-table_full-width" data-chart-id="reviews-table">
					<?php STM_LMS_Templates::show_lms_template( 'components/analytics/loader', array( 'loader_type' => 'table-loader' ) ); ?>
					<div class="masterstudy-analytics-reviews-page-table__wrapper">
						<div class="masterstudy-analytics-reviews-page-table__header">
							<div class="masterstudy-analytics-reviews-page-table__title">
								<?php echo esc_html__( 'Reviews', 'masterstudy-lms-learning-management-system-pro' ); ?>
								<span id="reviews-table-total" class="masterstudy-analytics-reviews-page-table__title-value"></span>
							</div>
							<div class="masterstudy-analytics-table__tabs">
								<?php
								STM_LMS_Templates::show_lms_template(
									'components/tabs',
									array(
										'items'            => array(
											array(
												'id'    => $table_routes['reviews-publish'],
												'title' => esc_html__( 'Posted', 'masterstudy-lms-learning-management-system-pro' ),
											),
											array(
												'id'    => $table_routes['reviews-pending'],
												'title' => esc_html__( 'Pending review', 'masterstudy-lms-learning-management-system-pro' ),
											),
										),
										'style'            => 'default',
										'active_tab_index' => 0,
										'dark_mode'        => false,
									)
								);
								?>
							</div>
							<div class="masterstudy-analytics-reviews-page-table__search-wrapper">
								<input type="text" id="table-reviews-search" class="masterstudy-analytics-reviews-page-table__search" placeholder="<?php echo esc_html__( 'Search by user, course or review', 'masterstudy-lms-learning-management-system-pro' ); ?>">
								<span class="masterstudy-analytics-reviews-page-table__search-icon"></span>
							</div>
							<?php
							STM_LMS_Templates::show_lms_template(
								'components/analytics/settings-dropdown',
								array(
									'id'         => 'reviews-table',
									'menu_items' => array(
										array(
											'id'    => 'reviews-table',
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
								'id'      => 'reviews-table',
								'columns' => $reviews_columns,
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
