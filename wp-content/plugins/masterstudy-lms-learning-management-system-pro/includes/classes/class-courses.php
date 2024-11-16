<?php

new STM_LMS_Courses_Pro();

class STM_LMS_Courses_Pro {

	public function __construct() {
		add_action( 'stm-lms-content-stm-courses', array( self::class, 'single_course' ), 5 );
	}

	public static function affiliate_course( $course_id ) {
		$is_affiliate   = get_post_meta( $course_id, 'affiliate_course', true );
		$affiliate_text = get_post_meta( $course_id, 'affiliate_course_text', true );
		$affiliate_link = get_post_meta( $course_id, 'affiliate_course_link', true );

		if ( ! empty( $is_affiliate ) && 'on' === $is_affiliate && ! empty( $affiliate_text ) && ! empty( $affiliate_link ) ) {
			STM_LMS_Templates::show_lms_template(
				'components/buy-button/paid-courses/affiliate',
				array(
					'text'      => $affiliate_text,
					'link'      => $affiliate_link,
					'course_id' => $course_id,
				)
			);
			return true;
		}

		return false;
	}

	public static function single_course() {
		$course_id     = get_the_ID();
		$course_option = get_post_meta( $course_id, 'page_style', true );
		$style         = ! empty( $course_option ) ? $course_option : ( function_exists( 'get_course_style_from_categories' ) ? get_course_style_from_categories( $course_id ) : '' );

		if ( isset( $_GET['course_style'] ) ) {
			$style = sanitize_text_field( wp_unslash( $_GET['course_style'] ) );
		}

		if ( empty( $style ) ) {
			$style = STM_LMS_Options::get_option( 'course_style', 'default' );
		}

		if ( 'default' !== $style ) {
			remove_all_actions( 'stm-lms-content-stm-courses' );
			STM_LMS_Templates::show_lms_template( 'course/' . $style );
		}
	}
}
