<?php
namespace MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Engagement;

use MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Controller;
use MasterStudy\Lms\Pro\RestApi\Http\Serializers\Engagement\CourseSerializer;
use WP_REST_Request;
use WP_REST_Response;

final class GetCoursesController extends Controller {

	public function __invoke( WP_REST_Request $request ): WP_REST_Response {
		$validation = $this->validate_datatable( $request );

		if ( $validation instanceof WP_REST_Response ) {
			return $validation;
		}

		$validated_data     = $this->get_validated_data();
		$courses_provider   = $this->get_checkout_provider()->get_provider( 'courses' );
		$courses_repository = ( new $courses_provider(
			$this->get_date_from(),
			$this->get_date_to(),
			$validated_data['start'] ?? 1,
			$validated_data['length'] ?? 10,
			$validated_data['search']['value'] ?? null
		) );

		// Sort by: revenue, courses, orders, name. Example: sort[name] = asc
		$courses       = $courses_repository->get_progress_courses_data(
			$validated_data['columns'] ?? array(),
			$validated_data['order'] ?? array()
		);
		$total_courses = $courses_repository->get_total();

		return new WP_REST_Response(
			array(
				'recordsTotal'    => $total_courses,
				'recordsFiltered' => $total_courses,
				'data'            => ( new CourseSerializer() )->collectionToArray( $courses ),
			)
		);
	}
}
