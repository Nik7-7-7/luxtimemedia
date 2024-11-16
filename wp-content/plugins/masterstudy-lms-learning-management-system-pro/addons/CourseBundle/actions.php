<?php

use MasterStudy\Lms\Pro\addons\CourseBundle\Repository\CourseBundleRepository;

function masterstudy_lms_course_bundle_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'title'          => '',
			'columns'        => '',
			'posts_per_page' => '',
			'select_bundles' => '',
		),
		$atts
	);

	return STM_LMS_Templates::load_lms_template( 'shortcodes/stm_lms_course_bundles', $atts );
}
add_shortcode( 'stm_lms_course_bundles', 'masterstudy_lms_course_bundle_shortcode' );

function masterstudy_lms_course_budle_order_accepted( $user_id, $cart_items ): void {
	// Process each cart item
	if ( ! empty( $cart_items ) ) {
		foreach ( $cart_items as $cart_item ) {
			// Check if the item is a bundle and process courses within it
			if ( ! empty( $cart_item['bundle'] ) ) {
				$courses = CourseBundleRepository::get_bundle_courses( $cart_item['bundle'] );

				// Add each course in the bundle to the user's courses
				if ( ! empty( $courses ) ) {
					foreach ( $courses as $course_id ) {
						STM_LMS_Course::add_user_course(
							$course_id,
							$user_id,
							0,
							0,
							false,
							'',
							$cart_item['bundle']
						);
						STM_LMS_Course::add_student( $course_id );
					}
				}
			} else {
				// Add the single course to the user's courses if not a bundle
				STM_LMS_Course::add_user_course( $cart_item['item_id'], $user_id, 0, 0 );
			}
		}
	}

	// Clear the cart after processing
	stm_lms_get_delete_cart_items( $user_id );
}
add_action( 'stm_lms_order_accepted', 'masterstudy_lms_course_budle_order_accepted', 10, 2 );

function masterstudy_lms_course_budle_order_removed( $course_id, $cart_item, $user_id ): void {
	// Check if the removed item is a bundle
	if ( ! empty( $cart_item['bundle'] ) ) {
		$bundle_id      = intval( $cart_item['bundle'] );
		$bundle_courses = CourseBundleRepository::get_bundle_courses( $bundle_id );

		// Remove each course in the bundle from the user's courses
		if ( ! empty( $bundle_courses ) ) {
			foreach ( $bundle_courses as $id ) {
				global $wpdb;
				$table = stm_lms_user_courses_name( $wpdb );

				// Delete the course from the user_courses table
				$wpdb->delete(
					$table,
					array(
						'user_id'   => $user_id,
						'course_id' => $id,
						'bundle_id' => $bundle_id,
					)
				);
			}
		}
	}
}
add_action( 'stm_lms_order_remove', 'masterstudy_lms_course_budle_order_removed', 10, 3 );


function masterstudy_lms_course_wishlist_list( $wishlist ) {
	$columns = 3;
	$title   = esc_html__( 'Bundles', 'masterstudy-lms-learning-management-system-pro' );
	$args    = "author=''";

	if ( ! empty( $wishlist ) ) {
		STM_LMS_Templates::show_lms_template(
			'bundles/card/php/list',
			compact( 'wishlist', 'columns', 'title', 'args' )
		);
	}
}
add_action( 'stm_lms_after_wishlist_list', 'masterstudy_lms_course_wishlist_list', 10, 1 );
