<?php

namespace MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Course;

use MasterStudy\Lms\Http\WpResponseFactory;
use MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Controller;
use MasterStudy\Lms\Pro\RestApi\Http\Serializers\Course\CourseLessonSerializer;
use MasterStudy\Lms\Pro\RestApi\Repositories\DataTable\LessonRepository;
use MasterStudy\Lms\Repositories\CourseRepository;
use WP_REST_Request;
use WP_REST_Response;

final class GetCourseLessonsController extends Controller {

	public function __invoke( int $course_id, WP_REST_Request $request ): WP_REST_Response {
		if ( ! ( new CourseRepository() )->exists( $course_id ) ) {
			return WpResponseFactory::not_found();
		}

		$validation = $this->validate_datatable(
			$request,
			array(
				'type' => 'string',
			)
		);

		if ( $validation instanceof WP_REST_Response ) {
			return $validation;
		}

		$validated_data    = $this->get_validated_data();
		$course_repository = ( new LessonRepository(
			$this->get_date_from(),
			$this->get_date_to(),
			$validated_data['start'] ?? 1,
			$validated_data['length'] ?? 10,
			$validated_data['search']['value'] ?? null
		) );
		$columns           = ( new LessonRepository(
			$this->get_date_from(),
			$this->get_date_to(),
			1,
			30,
		) )->get_lessons_data(
			$course_id,
			$validated_data['columns'] ?? array(),
			$validated_data['order'] ?? array(),
			$validated_data['type'] ?? 'all'
		);

		// Sort by: revenue, courses, orders, name. Example: sort[name] = asc
		$lessons       = $course_repository->get_lessons_data(
			$course_id,
			$validated_data['columns'] ?? array(),
			$validated_data['order'] ?? array()
		);
		$total_lessons = $course_repository->get_total_lessons( $course_id );

		return new WP_REST_Response(
			array(
				'recordsTotal'    => $total_lessons,
				'recordsFiltered' => $total_lessons,
				'columns'         => ( new CourseLessonSerializer() )->collectionToArray( $columns ),
				'data'            => ( new CourseLessonSerializer() )->collectionToArray( $lessons ),
			)
		);
	}
}
