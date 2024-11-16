<?php

namespace MasterStudy\Lms\Pro\addons\CourseBundle\Http\Controllers;

use MasterStudy\Lms\Pro\addons\CourseBundle\Repository\CourseBundleRepository;
use MasterStudy\Lms\Http\WpResponseFactory;
use MasterStudy\Lms\Validation\Validator;
use WP_REST_Request;


final class GetBundlesController {
	public function __invoke( WP_REST_Request $request ): \WP_REST_Response {
		$validator = new Validator(
			$request->get_params(),
			array(
				'per_page'   => 'nullable|integer|min,1|max,100',
				'sort'       => 'nullable|string',
				'bundle_ids' => 'nullable|string',
			)
		);

		if ( $validator->fails() ) {
			return WpResponseFactory::validation_failed( $validator->get_errors_array() );
		}

		return new \WP_REST_Response(
			( new CourseBundleRepository() )->get_all( $validator->get_validated() )
		);
	}
}
