<?php
function masterstudy_enqueue() {
	masterstudy_global_scripts();

	/*Course player scripts registration*/
	wp_register_script( 'masterstudy-course-player-assignments', STM_LMS_PRO_URL . 'assets/js/course-player/assignments.js', array( 'jquery', 'masterstudy-audio-player' ), STM_LMS_PRO_VERSION, true );

	/*Course player styles registration*/
	wp_register_style( 'masterstudy-course-player-lesson-zoom', STM_LMS_PRO_URL . 'assets/css/course-player/zoom-conference.css', null, STM_LMS_PRO_VERSION );
	wp_register_style( 'masterstudy-course-player-lesson-stream', STM_LMS_PRO_URL . 'assets/css/course-player/stream.css', null, STM_LMS_PRO_VERSION );
	wp_register_style( 'masterstudy-course-player-lesson-google', STM_LMS_PRO_URL . 'assets/css/course-player/google-meet.css', null, STM_LMS_PRO_VERSION );
	wp_register_style( 'masterstudy-course-player-drip-content', STM_LMS_PRO_URL . '/assets/css/course-player/drip-content.css', null, STM_LMS_PRO_VERSION );
	wp_register_style( 'masterstudy-course-player-assignments', STM_LMS_PRO_URL . 'assets/css/course-player/assignments.css', null, STM_LMS_PRO_VERSION );

	/*Course player fonts styles registration*/
	wp_register_style( 'masterstudy-course-player-lesson-zoom-fonts', STM_LMS_PRO_URL . 'assets/css/course-player/fonts/zoom-conference.css', null, STM_LMS_PRO_VERSION );
	wp_register_style( 'masterstudy-course-player-lesson-stream-fonts', STM_LMS_PRO_URL . 'assets/css/course-player/fonts/stream.css', null, STM_LMS_PRO_VERSION );
	wp_register_style( 'masterstudy-course-player-lesson-google-fonts', STM_LMS_PRO_URL . 'assets/css/course-player/fonts/google-meet.css', null, STM_LMS_PRO_VERSION );
	wp_register_style( 'masterstudy-course-player-drip-content-fonts', STM_LMS_PRO_URL . '/assets/css/course-player/fonts/drip-content.css', null, STM_LMS_PRO_VERSION );
	wp_register_style( 'masterstudy-course-player-assignments-fonts', STM_LMS_PRO_URL . 'assets/css/course-player/fonts/assignments.css', null, STM_LMS_PRO_VERSION );

	/* Certificate page scripts & style registration */
	wp_register_style( 'masterstudy-instructor-certificates', STM_LMS_PRO_URL . 'assets/css/certificate-builder/instructor-certificates.css', null, STM_LMS_PRO_VERSION );
	wp_register_script( 'masterstudy-instructor-certificates', STM_LMS_PRO_URL . 'assets/js/certificate-builder/instructor-certificates.js', array( 'jquery' ), STM_LMS_PRO_VERSION, true );
	wp_register_style( 'masterstudy-certificate-page', STM_LMS_PRO_URL . 'assets/css/certificate-builder/certificate-page.css', null, STM_LMS_PRO_VERSION );

	/*Single course page styles & scripts registration*/
	wp_register_script( 'masterstudy-single-course-main', STM_LMS_PRO_URL . 'assets/js/course/main.js', array( 'jquery', 'plyr' ), STM_LMS_PRO_VERSION, true );
	wp_register_style( 'masterstudy-single-course-classic', STM_LMS_PRO_URL . 'assets/css/course/classic.css', null, STM_LMS_PRO_VERSION );
	wp_register_style( 'masterstudy-single-course-modern', STM_LMS_PRO_URL . 'assets/css/course/modern.css', null, STM_LMS_PRO_VERSION );
	wp_register_style( 'masterstudy-single-course-timeless', STM_LMS_PRO_URL . 'assets/css/course/timeless.css', null, STM_LMS_PRO_VERSION );
	wp_register_style( 'masterstudy-single-course-dynamic-sidebar', STM_LMS_PRO_URL . 'assets/css/course/dynamic-sidebar.css', null, STM_LMS_PRO_VERSION );
	wp_register_style( 'masterstudy-single-course-sleek-sidebar', STM_LMS_PRO_URL . 'assets/css/course/sleek-sidebar.css', null, STM_LMS_PRO_VERSION );
	wp_register_style( 'masterstudy-single-course-minimalistic', STM_LMS_PRO_URL . 'assets/css/course/minimalistic.css', null, STM_LMS_PRO_VERSION );
	wp_register_style( 'masterstudy-single-course-dynamic', STM_LMS_PRO_URL . 'assets/css/course/dynamic.css', null, STM_LMS_PRO_VERSION );
	wp_register_style( 'masterstudy-single-course-full-width', STM_LMS_PRO_URL . 'assets/css/course/full-width.css', null, STM_LMS_PRO_VERSION );
	wp_register_style( 'masterstudy-single-course-modern-curriculum', STM_LMS_PRO_URL . 'assets/css/course/modern-curriculum.css', null, STM_LMS_PRO_VERSION );
	wp_register_style( 'masterstudy-single-course-video-preview', STM_LMS_PRO_URL . 'assets/css/course/video-preview.css', null, STM_LMS_PRO_VERSION );

	/*Components scripts registration*/
	wp_register_script( 'masterstudy-form-builder-fields', STM_LMS_PRO_URL . 'assets/js/components/form-builder-fields.js', array( 'jquery' ), STM_LMS_PRO_VERSION, true );
	wp_localize_script(
		'masterstudy-form-builder-fields',
		'masterstudy_form_builder_data',
		array(
			'ajax_url'          => admin_url( 'admin-ajax.php' ),
			'file_upload_nonce' => wp_create_nonce( 'stm_lms_upload_form_file' ),
			'file_delete_nonce' => wp_create_nonce( 'stm_lms_delete_form_file' ),
			'icon_url'          => STM_LMS_PRO_URL . '/assets/icons/files/',
			'only_one_file'     => __( 'Only one file allowed', 'masterstudy-lms-learning-management-system' ),
		)
	);
	wp_register_script( 'masterstudy-buy-button-points', STM_LMS_PRO_URL . 'assets/js/components/buy-button/points.js', array( 'jquery' ), STM_LMS_PRO_VERSION, true );
	wp_register_script( 'masterstudy-buy-button-prerequisites', STM_LMS_PRO_URL . 'assets/js/components/buy-button/prerequisites.js', array( 'jquery' ), STM_LMS_PRO_VERSION, true );
	wp_register_script( 'masterstudy-group-course-trigger', STM_LMS_PRO_URL . 'assets/js/components/modals/group-courses-trigger.js', array( 'jquery' ), STM_LMS_PRO_VERSION, true );
	wp_register_script( 'masterstudy-group-course-add-group', STM_LMS_PRO_URL . 'assets/js/components/modals/group-courses-add-group.js', array( 'jquery' ), STM_LMS_PRO_VERSION, true );
	wp_register_script( 'masterstudy-group-course-add-to-cart', STM_LMS_PRO_URL . 'assets/js/components/modals/group-courses-add-to-cart.js', array( 'jquery' ), STM_LMS_PRO_VERSION, true );
	wp_register_script( 'masterstudy-bundle-button', STM_LMS_PRO_URL . 'assets/js/components/bundle-button.js', array( 'jquery' ), STM_LMS_PRO_VERSION, true );

	/*Components styles registration*/
	wp_register_style( 'masterstudy-buy-button-points', STM_LMS_PRO_URL . 'assets/css/components/buy-button/points.css', null, STM_LMS_PRO_VERSION );
	wp_register_style( 'masterstudy-buy-button-group-courses', STM_LMS_PRO_URL . '/assets/css/components/buy-button/group-courses.css', null, STM_LMS_PRO_VERSION );
	wp_register_style( 'masterstudy-buy-button-affiliate', STM_LMS_PRO_URL . '/assets/css/components/buy-button/affiliate.css', null, STM_LMS_PRO_VERSION );
	wp_register_style( 'masterstudy-buy-button-prerequisites', STM_LMS_PRO_URL . 'assets/css/components/buy-button/prerequisite-button.css', null, STM_LMS_PRO_VERSION );
	wp_register_style( 'masterstudy-prerequisites-info', STM_LMS_PRO_URL . 'assets/css/components/buy-button/prerequisite-info.css', null, STM_LMS_PRO_VERSION );
	wp_register_style( 'masterstudy-group-course', STM_LMS_PRO_URL . 'assets/css/components/group-courses.css', null, STM_LMS_PRO_VERSION );
	wp_register_style( 'masterstudy-form-builder-fields', STM_LMS_PRO_URL . 'assets/css/components/form-builder-fields.css', null, STM_LMS_PRO_VERSION );
	wp_register_style( 'masterstudy-form-builder-public-fields', STM_LMS_PRO_URL . 'assets/css/components/form-builder-public-fields.css', null, STM_LMS_PRO_VERSION );
	wp_register_style( 'masterstudy-bundle-button', STM_LMS_PRO_URL . 'assets/css/components/bundle-button.css', null, STM_LMS_PRO_VERSION );
	wp_register_style( 'masterstudy-bundle-card', STM_LMS_PRO_URL . 'assets/css/components/bundle/card.css', null, STM_LMS_PRO_VERSION );

	/*Components fonts styles registration*/
	wp_register_style( 'masterstudy-buy-button-points-fonts', STM_LMS_PRO_URL . 'assets/css/components/fonts/buy-button/points.css', null, STM_LMS_PRO_VERSION );
	wp_register_style( 'masterstudy-buy-button-group-courses-fonts', STM_LMS_PRO_URL . '/assets/css/components/fonts/buy-button/group-courses.css', null, STM_LMS_PRO_VERSION );
	wp_register_style( 'masterstudy-buy-button-affiliate-fonts', STM_LMS_PRO_URL . '/assets/css/components/fonts/buy-button/affiliate.css', null, STM_LMS_PRO_VERSION );
	wp_register_style( 'masterstudy-buy-button-prerequisites-fonts', STM_LMS_PRO_URL . 'assets/css/components/fonts/buy-button/prerequisite-button.css', null, STM_LMS_PRO_VERSION );
	wp_register_style( 'masterstudy-prerequisites-info-fonts', STM_LMS_PRO_URL . 'assets/css/components/fonts/buy-button/prerequisite-info.css', null, STM_LMS_PRO_VERSION );
}
add_action( 'wp_enqueue_scripts', 'masterstudy_enqueue' );

function masterstudy_admin_enqueue() {
	masterstudy_global_scripts();

	wp_enqueue_style( 'fonts', STM_LMS_PRO_URL . 'assets/css/variables/fonts.css', null, STM_LMS_PRO_VERSION );
	wp_enqueue_style( 'masterstudy-admin-certificate', STM_LMS_PRO_URL . 'assets/css/certificate-builder/admin.css', null, STM_LMS_PRO_VERSION );
}
add_action( 'admin_enqueue_scripts', 'masterstudy_admin_enqueue' );

function masterstudy_global_scripts() {
	$locale = masterstudy_get_locale();

	/*Libraries styles & scripts registration*/
	wp_register_script( 'masterstudy-chartjs-library', STM_LMS_PRO_URL . 'assets/vendors/chart.umd.js', array( 'jquery' ), STM_LMS_PRO_VERSION, true );
	wp_register_script( 'masterstudy-datatables-library', STM_LMS_PRO_URL . 'assets/vendors/datatables.min.js', array( 'jquery' ), STM_LMS_PRO_VERSION, true );
	wp_register_style( 'masterstudy-datatables-library', STM_LMS_PRO_URL . 'assets/vendors/datatables.min.css', null, STM_LMS_PRO_VERSION );
	wp_register_script( 'masterstudy-datepicker-library', STM_LMS_PRO_URL . 'assets/vendors/flatpickr.min.js', array( 'jquery' ), STM_LMS_PRO_VERSION, true );
	wp_register_script( 'masterstudy-datepicker-locale', "https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/{$locale['current_locale']}.js", array( 'masterstudy-datepicker-library' ), STM_LMS_PRO_VERSION, true );
	wp_register_style( 'masterstudy-datepicker-library', STM_LMS_PRO_URL . 'assets/vendors/flatpickr.min.css', null, STM_LMS_PRO_VERSION );
	wp_register_script( 'masterstudy-analytics-helpers', STM_LMS_PRO_URL . 'assets/js/analytics/helpers.js', array( 'jquery' ), STM_LMS_PRO_VERSION, true );
	if ( class_exists( 'STM_LMS_Options' ) ) {
		wp_localize_script(
			'masterstudy-analytics-helpers',
			'stats_data',
			array(
				'currency_symbol'      => STM_LMS_Options::get_option( 'currency_symbol', '$' ),
				'currency_position'    => STM_LMS_Options::get_option( 'currency_position', 'left' ),
				'currency_thousands'   => STM_LMS_Options::get_option( 'currency_thousands', ',' ),
				'currency_decimals'    => STM_LMS_Options::get_option( 'currency_decimals', '.' ),
				'decimals_num'         => STM_LMS_Options::get_option( 'decimals_num', 2 ),
				'report_button_title'  => __( 'Detailed report', 'masterstudy-lms-learning-management-system-pro' ),
				'details_title'        => __( 'Details', 'masterstudy-lms-learning-management-system-pro' ),
				'progress_title'       => __( 'Course progress', 'masterstudy-lms-learning-management-system-pro' ),
				'per_page_placeholder' => __( 'per page', 'masterstudy-lms-learning-management-system-pro' ),
				'not_found'            => __( 'No matching items found' ),
				'not_available'        => __( 'No data to display' ),
				'custom_period'        => __( 'Date range', 'masterstudy-lms-learning-management-system-pro' ),
				'locale'               => $locale,
				'assignments_addon'    => is_ms_lms_addon_enabled( 'assignments' ),
				'payouts_addon'        => is_ms_lms_addon_enabled( 'statistics' ),
				'upcoming_addon'       => is_ms_lms_addon_enabled( 'coming_soon' ),
				'bundle_addon'         => is_ms_lms_addon_enabled( 'course_bundle' ),
				'img_route'            => STM_LMS_PRO_URL,
				'user_account_url'     => STM_LMS_User::login_page_url(),
				'courses_page_url'     => STM_LMS_Course::courses_page_url(),
				'instructors_payouts'  => STM_LMS_Options::get_option( 'instructors_payouts', true ),
				'is_user_account'      => ! is_admin(),
				'is_admin'             => current_user_can( 'administrator' ) ?? 0,
				'not_started_lesson'   => __( 'Not Started', 'masterstudy-lms-learning-management-system-pro' ),
				'failed_lesson'        => __( 'Failed', 'masterstudy-lms-learning-management-system-pro' ),
				'completed_lesson'     => __( 'Complete', 'masterstudy-lms-learning-management-system-pro' ),
				'progress_lesson'      => __( 'In progress', 'masterstudy-lms-learning-management-system-pro' ),
			)
		);
	}

	/*API provider*/
	wp_register_script( 'masterstudy-api-provider', STM_LMS_PRO_URL . 'assets/js/analytics/api-provider.js', array(), STM_LMS_PRO_VERSION, true );
	wp_localize_script(
		'masterstudy-api-provider',
		'api_data',
		array(
			'rest_url' => esc_url_raw( rest_url( 'masterstudy-lms/v2/' ) ),
			'nonce'    => wp_create_nonce( 'wp_rest' ),
		)
	);

	/*Analytics styles & scripts registration*/
	$analytics_deps = array(
		'jquery',
		'masterstudy-chartjs-library',
		'masterstudy-datatables-library',
		'masterstudy-datepicker-library',
		'masterstudy-datepicker-locale',
		'masterstudy-api-provider',
		'masterstudy-analytics-helpers',
	);

	wp_register_style( 'masterstudy-analytics-components', STM_LMS_PRO_URL . 'assets/css/components/analytics/main.css', null, STM_LMS_PRO_VERSION );
	wp_register_script( 'masterstudy-analytics-revenue-page', STM_LMS_PRO_URL . 'assets/js/analytics/revenue.js', $analytics_deps, STM_LMS_PRO_VERSION, true );
	wp_register_style( 'masterstudy-analytics-revenue-page', STM_LMS_PRO_URL . 'assets/css/analytics/revenue.css', null, STM_LMS_PRO_VERSION );
	wp_register_script( 'masterstudy-analytics-engagement-page', STM_LMS_PRO_URL . 'assets/js/analytics/engagement.js', $analytics_deps, STM_LMS_PRO_VERSION, true );
	wp_register_style( 'masterstudy-analytics-engagement-page', STM_LMS_PRO_URL . 'assets/css/analytics/engagement.css', null, STM_LMS_PRO_VERSION );
	wp_register_script( 'masterstudy-analytics-users-page', STM_LMS_PRO_URL . 'assets/js/analytics/users.js', $analytics_deps, STM_LMS_PRO_VERSION, true );
	wp_register_style( 'masterstudy-analytics-users-page', STM_LMS_PRO_URL . 'assets/css/analytics/users.css', null, STM_LMS_PRO_VERSION );
	wp_register_script( 'masterstudy-analytics-reviews-page', STM_LMS_PRO_URL . 'assets/js/analytics/reviews.js', $analytics_deps, STM_LMS_PRO_VERSION, true );
	wp_register_style( 'masterstudy-analytics-reviews-page', STM_LMS_PRO_URL . 'assets/css/analytics/reviews.css', null, STM_LMS_PRO_VERSION );
	wp_register_script( 'masterstudy-analytics-course-page', STM_LMS_PRO_URL . 'assets/js/analytics/course.js', $analytics_deps, STM_LMS_PRO_VERSION, true );
	wp_register_style( 'masterstudy-analytics-course-page', STM_LMS_PRO_URL . 'assets/css/analytics/course.css', null, STM_LMS_PRO_VERSION );
	wp_register_script( 'masterstudy-analytics-instructor-page', STM_LMS_PRO_URL . 'assets/js/analytics/instructor.js', $analytics_deps, STM_LMS_PRO_VERSION, true );
	wp_register_style( 'masterstudy-analytics-instructor-page', STM_LMS_PRO_URL . 'assets/css/analytics/instructor.css', null, STM_LMS_PRO_VERSION );
	wp_register_script( 'masterstudy-analytics-student-page', STM_LMS_PRO_URL . 'assets/js/analytics/student.js', $analytics_deps, STM_LMS_PRO_VERSION, true );
	wp_register_style( 'masterstudy-analytics-student-page', STM_LMS_PRO_URL . 'assets/css/analytics/student.css', null, STM_LMS_PRO_VERSION );
	wp_register_script( 'masterstudy-analytics-instructor-students-page', STM_LMS_PRO_URL . 'assets/js/analytics/instructor-students.js', $analytics_deps, STM_LMS_PRO_VERSION, true );
	wp_register_style( 'masterstudy-analytics-instructor-students-page', STM_LMS_PRO_URL . 'assets/css/analytics/instructor-students.css', null, STM_LMS_PRO_VERSION );
	wp_register_script( 'masterstudy-analytics-short-report', STM_LMS_PRO_URL . 'assets/js/analytics/short-report.js', $analytics_deps, STM_LMS_PRO_VERSION, true );
	wp_register_style( 'masterstudy-analytics-short-report', STM_LMS_PRO_URL . 'assets/css/analytics/short-report.css', null, STM_LMS_PRO_VERSION );
	wp_register_style( 'masterstudy-woocommerce-orders', STM_LMS_PRO_URL . 'assets/css/woocommerce/orders.css', null, STM_LMS_PRO_VERSION );
	wp_register_script( 'masterstudy-woocommerce-orders', STM_LMS_PRO_URL . 'assets/js/woocommerce/orders.js', $analytics_deps, STM_LMS_PRO_VERSION, true );
}

function masterstudy_get_locale() {
	$locale         = get_locale();
	$current_locale = 'en';
	$locales        = array(
		'en_US' => 'en',
		'en_GB' => 'en',
		'ru_RU' => 'ru',
		'ja'    => 'ja',
		'zh_CN' => 'zh',
		'de_DE' => 'de',
		'it_IT' => 'it',
		'fr_FR' => 'fr',
		'es_ES' => 'es',
	);

	if ( array_key_exists( $locale, $locales ) ) {
		$current_locale = $locales[ $locale ];
	}

	return array(
		'current_locale' => $current_locale,
		'firstDayOfWeek' => get_option( 'start_of_week', 0 ),
	);
}
