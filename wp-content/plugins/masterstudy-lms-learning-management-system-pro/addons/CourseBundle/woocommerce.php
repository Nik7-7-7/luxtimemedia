<?php

use MasterStudy\Lms\Pro\addons\CourseBundle\Repository\CourseBundleRepository;

if ( class_exists( 'STM_LMS_Woocommerce_Courses_Admin' ) && STM_LMS_Cart::woocommerce_checkout_enabled() ) {
	new STM_LMS_Woocommerce_Courses_Admin(
		'bundle',
		esc_html__( 'LMS Bundles', 'masterstudy-lms-learning-management-system-pro' ),
		CourseBundleRepository::PRICE_META_KEY
	);
}

function masterstudy_lms_course_bundle_order_approved( $course_data, $user_id ) {
	if ( ! empty( $course_data['bundle_id'] ) ) {
		$courses = CourseBundleRepository::get_bundle_courses( $course_data['bundle_id'] );

		if ( ! empty( $courses ) ) {
			foreach ( $courses as $course_id ) {
				if ( get_post_type( $course_id ) === 'stm-courses' ) {
					STM_LMS_Course::add_user_course(
						$course_id,
						$user_id,
						0,
						0,
						false,
						'',
						$course_data['bundle_id']
					);
					STM_LMS_Course::add_student( $course_id );
				}
			}
		}
	}
}
add_action( 'stm_lms_woocommerce_order_approved', 'masterstudy_lms_course_bundle_order_approved', 10, 2 );

function masterstudy_lms_course_bundle_order_cancelled( $course_data, $user_id ) {
	if ( ! empty( $course_data['bundle_id'] ) ) {
		$bundle_id = intval( $course_data['bundle_id'] );

		if ( ! STM_LMS_Woocommerce::has_course_been_purchased( $user_id, $bundle_id ) ) {
			$bundle_courses = CourseBundleRepository::get_bundle_courses( $bundle_id );

			if ( ! empty( $bundle_courses ) ) {
				global $wpdb;

				foreach ( $bundle_courses as $id ) {
					$wpdb->delete(
						stm_lms_user_courses_name( $wpdb ),
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
}
add_action( 'stm_lms_woocommerce_order_cancelled', 'masterstudy_lms_course_bundle_order_cancelled', 10, 2 );

function masterstudy_lms_single_bundle_start( $bundle_id ) {
	if ( class_exists( 'STM_LMS_Woocommerce' ) ) {
		STM_LMS_Woocommerce::create_product( $bundle_id );
	}
}
add_action( 'stm_lms_single_bundle_start', 'masterstudy_lms_single_bundle_start' );

function masterstudy_lms_course_bundle_before_create_order( $order_meta, $cart_item ) {
	if ( ! empty( $cart_item['bundle_id'] ) ) {
		$order_meta['bundle_id'] = $cart_item['bundle_id'];
	}

	return $order_meta;
}
add_filter( 'stm_lms_before_create_order', 'masterstudy_lms_course_bundle_before_create_order', 100, 2 );
