<?php

namespace MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Student;

use MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Controller;
use MasterStudy\Lms\Pro\RestApi\Http\Serializers\Student\StudentCourseSerializer;
use MasterStudy\Lms\Pro\RestApi\Repositories\DataTable\StudentCoursesRepository;
use WP_REST_Request;
use WP_REST_Response;

final class GetStudentCoursesController extends Controller {
	public function __invoke( WP_REST_Request $request ): WP_REST_Response {
		$validation = $this->validate_datatable(
			$request,
			array(
				'user_id' => 'required|integer',
			)
		);

		if ( $validation instanceof WP_REST_Response ) {
			return $validation;
		}

		$validated_data = $this->get_validated_data();
		$student_id     = $validated_data['user_id'];

		$students_repository = new StudentCoursesRepository(
			$this->get_date_from(),
			$this->get_date_to(),
			$validated_data['start'] ?? 0,
			$validated_data['length'] ?? 10
		);

		$students = $students_repository->get_student_course_progress_data(
			$student_id,
			$validated_data['columns'],
			array_key_exists( 'order', $validated_data ) ? $validated_data['order'] : null,
		);

		$total = $students_repository->get_total_student_courses( $student_id );

		return new WP_REST_Response(
			array(
				'recordsTotal'    => intval( $total ),
				'recordsFiltered' => intval( $total ),
				'data'            => ( new StudentCourseSerializer() )->collectionToArray( $students ),
			)
		);
	}
}
