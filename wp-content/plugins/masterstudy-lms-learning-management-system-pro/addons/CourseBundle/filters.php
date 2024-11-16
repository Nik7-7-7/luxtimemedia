<?php

use MasterStudy\Lms\Pro\addons\CourseBundle\Repository\CourseBundleRepository;
use MasterStudy\Lms\Pro\addons\CourseBundle\Repository\CourseBundleSettings;

function masterstudy_lms_course_bundle_register_post_type( $post_types ) {
	$post_types[] = CourseBundleRepository::POST_TYPE;

	return $post_types;
}
add_filter( 'stm_lms_post_types', 'masterstudy_lms_course_bundle_register_post_type', 5, 1 );

function masterstudy_lms_course_bundle_post_type( $posts ) {
	$posts[ CourseBundleRepository::POST_TYPE ] = array(
		'single' => esc_html__( 'Course Bundles', 'masterstudy-lms-learning-management-system-pro' ),
		'plural' => esc_html__( 'Course Bundles', 'masterstudy-lms-learning-management-system-pro' ),
		'args'   => array(
			'public'              => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'show_in_menu'        => false,
			'supports'            => array( 'title', 'editor', 'thumbnail', 'revisions', 'author' ),
		),
	);

	return $posts;
}
add_filter( 'stm_lms_post_types_array', 'masterstudy_lms_course_bundle_post_type', 10, 1 );

function masterstudy_lms_course_bundle_menu_item( $menus ) {
	if ( STM_LMS_Instructor::is_instructor() ) {
		$menus[] = array(
			'order'        => 50,
			'id'           => 'bundles',
			'slug'         => 'bundles',
			'lms_template' => 'stm-lms-user-bundles',
			'menu_title'   => esc_html__( 'Bundles', 'masterstudy-lms-learning-management-system-pro' ),
			'menu_icon'    => 'fa-layer-group',
			'menu_url'     => ms_plugin_user_account_url( 'bundles' ),
			'menu_place'   => 'main',
		);
	}

	return $menus;
}
add_filter( 'stm_lms_menu_items', 'masterstudy_lms_course_bundle_menu_item' );

function masterstudy_lms_course_bundle_settings_page( $setups ) {
	$setups[] = array(
		'page'        => array(
			'parent_slug' => 'stm-lms-settings',
			'page_title'  => 'Course Bundle Settings',
			'menu_title'  => 'Course Bundle Settings',
			'menu_slug'   => 'course_bundle_settings',
		),
		'fields'      => apply_filters(
			'stm_lms_course_bundle_settings',
			array(
				'credentials' => array(
					'name'   => esc_html__( 'Credentials', 'masterstudy-lms-learning-management-system-pro' ),
					'fields' => array(
						'bundle_limit'         => array(
							'type'        => 'text',
							'label'       => esc_html__( 'Bundles quantity limit', 'masterstudy-lms-learning-management-system-pro' ),
							'description' => esc_html__( 'Set the maximum number of bundles that can be created', 'masterstudy-lms-learning-management-system-pro' ),
						),
						'bundle_courses_limit' => array(
							'type'  => 'text',
							'label' => esc_html__(
								'Courses in bundle quantity limit',
								'masterstudy-lms-learning-management-system-pro'
							),
							'hint'  => esc_html__(
								'By default, the limit is 5 courses per bundle',
								'masterstudy-lms-learning-management-system-pro'
							),
						),
					),
				),
			)
		),
		'option_name' => CourseBundleSettings::OPTION_NAME,
	);

	return $setups;
}
add_filter( 'wpcfto_options_page_setup', 'masterstudy_lms_course_bundle_settings_page', 100 );

add_filter( 'stm_lms_accept_order', '__return_false' );

function masterstudy_lms_course_budle_after_single_item_cart_title( $item ) {
	if ( ! empty( $item['bundle'] ) ) {
		echo '<span class="enterprise-course-added"><label>' . esc_html__( 'Bundle', 'masterstudy-lms-learning-management-system-pro' ) . '</label></span>';
	}
}
add_filter( 'stm_lms_after_single_item_cart_title', 'masterstudy_lms_course_budle_after_single_item_cart_title' );

function masterstudy_lms_course_budle_cart_items_fields( $fields ) {
	$fields[] = 'bundle';

	return $fields;
}
add_filter( 'stm_lms_cart_items_fields', 'masterstudy_lms_course_budle_cart_items_fields' );
