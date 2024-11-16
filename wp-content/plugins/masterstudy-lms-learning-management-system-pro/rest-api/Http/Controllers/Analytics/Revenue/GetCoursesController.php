<?php
namespace MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Revenue;

use MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Controller;
use MasterStudy\Lms\Pro\RestApi\Http\Serializers\Revenue\CourseSerializer;
use WP_REST_Request;
use WP_REST_Response;

final class GetCoursesController extends Controller {
	public function __invoke( WP_REST_Request $request ): WP_REST_Response {
		$validation = $this->validate_datatable(
			$request,
			array(
				'instructor_id' => 'integer',
			)
		);

		if ( $validation instanceof WP_REST_Response ) {
			return $validation;
		}
		$validated_data     = $this->get_validated_data();
		$courses_provider   = $this->get_checkout_provider()->get_provider( 'courses' );
		$courses_repository = new $courses_provider(
			$this->get_date_from(),
			$this->get_date_to(),
			$validated_data['start'] ?? 0,
			$validated_data['length'] ?? 10,
			$validated_data['search']['value'] ?? null
		);

		$courses = $courses_repository->get_all_course_data(
			$validated_data['columns'],
			array_key_exists( 'order', $validated_data ) ? $validated_data['order'] : null,
			$validated_data['instructor_id'] ?? null
		);
		$total   = $courses_repository->get_total();

		if ( array_key_exists( 'instructor_id', $validated_data ) && $validated_data['instructor_id'] ) {
			$total = $courses_repository->get_total_instructor_courses( $validated_data['instructor_id'] );
		}

		return new WP_REST_Response(
			array(
				'recordsTotal'    => intval( $total ),
				'recordsFiltered' => intval( $total ),
				'data'            => ( new CourseSerializer() )->collectionToArray( $courses ),
			)
		);
	}
}
