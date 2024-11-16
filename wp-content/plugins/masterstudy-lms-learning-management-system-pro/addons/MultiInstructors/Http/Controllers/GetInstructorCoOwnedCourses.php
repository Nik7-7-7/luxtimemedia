<?php

namespace MasterStudy\Lms\Pro\addons\MultiInstructors\Http\Controllers;

use MasterStudy\Lms\Pro\addons\MultiInstructors\Repository\MultiInstructorsRepository;
use MasterStudy\Lms\Pro\addons\MultiInstructors\Http\Serializers\MultiInstructorsSerializer;
use MasterStudy\Lms\Http\WpResponseFactory;
use MasterStudy\Lms\Validation\Validator;
use WP_REST_Request;
use WP_REST_Response;

final class GetInstructorCoOwnedCourses {
	public function __invoke( WP_REST_Request $request ): WP_REST_Response {
		$validator = new Validator(
			$request->get_params(),
			array(
				'page' => 'required|integer',
				'pp'   => 'required|integer',
				'user' => 'required|integer',
			)
		);

		if ( $validator->fails() ) {
			return WpResponseFactory::validation_failed( $validator->get_errors_array() );
		}

		$courses = ( new MultiInstructorsRepository() )->instructor_co_owned_courses( $validator->get_validated() );

		return new WP_REST_Response(
			( new MultiInstructorsSerializer() )->toArray( $courses )
		);
	}
}
