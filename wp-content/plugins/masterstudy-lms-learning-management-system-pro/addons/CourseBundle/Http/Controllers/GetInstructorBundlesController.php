<?php

namespace MasterStudy\Lms\Pro\addons\CourseBundle\Http\Controllers;

use MasterStudy\Lms\Pro\addons\CourseBundle\Repository\CourseBundleRepository;
use MasterStudy\Lms\Pro\addons\CourseBundle\Http\Serializers\BundlesDataSerializer;
use MasterStudy\Lms\Http\WpResponseFactory;
use MasterStudy\Lms\Validation\Validator;
use WP_REST_Request;
use WP_REST_Response;

final class GetInstructorBundlesController {
	public function __invoke( WP_REST_Request $request ): WP_REST_Response {
		$validator = new Validator(
			$request->get_params(),
			array(
				'page' => 'nullable|integer',
				'pp'   => 'nullable|integer',
				'user' => 'nullable|integer',
			)
		);

		if ( $validator->fails() ) {
			return WpResponseFactory::validation_failed( $validator->get_errors_array() );
		}

		$validated = $validator->get_validated();
		$args      = array(
			'posts_per_page' => $validated['pp'] ?? 6,
			'paged'          => $validated['page'] ?? 1,
		);

		if ( isset( $validated['user'] ) ) {
			$args['author__in'] = array( $validated['user'] );
		}

		$bundles = ( new CourseBundleRepository() )->get_instructor_bundles( $args );

		return new WP_REST_Response(
			( new BundlesDataSerializer() )->toArray( $bundles )
		);
	}
}
