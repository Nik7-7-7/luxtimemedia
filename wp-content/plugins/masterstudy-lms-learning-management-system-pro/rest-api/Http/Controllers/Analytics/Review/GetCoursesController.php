<?php
namespace MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Review;

use MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Controller;
use MasterStudy\Lms\Pro\RestApi\Http\Serializers\Review\CourseSerializer;
use MasterStudy\Lms\Pro\RestApi\Repositories\DataTable\ReviewRepository;
use WP_REST_Request;
use WP_REST_Response;

final class GetCoursesController extends Controller {
	public function __invoke( WP_REST_Request $request ): WP_REST_Response {
		$validation = $this->validate(
			$request,
			array(
				'start'  => 'required|integer',
				'length' => 'required|integer',
			),
		);

		if ( $validation instanceof WP_REST_Response ) {
			return $validation;
		}

		$validated_date    = $this->get_validated_data();
		$review_repository = new ReviewRepository(
			$this->get_date_from(),
			$this->get_date_to(),
			$validated_date['start'] ?? 1,
			$validated_date['length'] ?? 10,
		);

		return new WP_REST_Response(
			array(
				'recordsTotal'    => $validated_date['length'],
				'recordsFiltered' => $validated_date['length'],
				'data'            => ( new CourseSerializer() )->collectionToArray( $review_repository->get_reviewed_courses_data() ),
			)
		);
	}
}
