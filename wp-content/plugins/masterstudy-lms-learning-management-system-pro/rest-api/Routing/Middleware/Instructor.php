<?php

namespace MasterStudy\Lms\Pro\RestApi\Routing\Middleware;

use MasterStudy\Lms\Http\WpResponseFactory;
use MasterStudy\Lms\Pro\RestApi\Context\InstructorContext;
use MasterStudy\Lms\Routing\MiddlewareInterface;

class Instructor implements MiddlewareInterface {
	public function process( $request, callable $next ) {
		$current_user = wp_get_current_user();

		// Allow administrators to access all routes
		if ( in_array( 'administrator', $current_user->roles, true ) ) {
			return $next( $request );
		}

		// Allow instructors to access only their own data
		if ( in_array( 'stm_lms_instructor', $current_user->roles, true ) ) {
			$instructor_id = $this->get_url_param( $request, 'instructor_id' );

			if ( $instructor_id === $current_user->ID || null === $instructor_id ) {
				// Set the current instructor ID in the context
				InstructorContext::get_instance()->set_instructor_id( $current_user->ID );

				// Allow instructors to access only their own courses
				$course_id = $this->get_url_param( $request, 'course_id' );
				if ( $course_id && ! masterstudy_lms_is_user_course_owner( $course_id, $current_user->ID ) ) {
					return WpResponseFactory::forbidden();
				}

				// Allow instructors to access only their own students
				$student_id = $this->get_url_param( $request, 'user_id' );
				if ( $student_id && ! masterstudy_lms_is_instructors_student( $student_id, $current_user->ID ) ) {
					return WpResponseFactory::forbidden();
				}

				return $next( $request );
			}
		}

		return WpResponseFactory::forbidden();
	}

	private function get_url_param( $request, $key ): ?int {
		$url_params = $request->get_url_params();

		if ( array_key_exists( $key, $url_params ) ) {
			return (int) $url_params[ $key ];
		}

		return null;
	}
}
