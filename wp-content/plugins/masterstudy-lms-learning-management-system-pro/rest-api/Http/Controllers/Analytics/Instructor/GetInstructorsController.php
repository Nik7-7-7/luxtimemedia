<?php

namespace MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Instructor;

use MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Controller;
use MasterStudy\Lms\Pro\RestApi\Http\Serializers\Instructor\InstructorSerializer;
use MasterStudy\Lms\Pro\RestApi\Repositories\DataTable\InstructorRepository;
use WP_REST_Request;
use WP_REST_Response;

final class GetInstructorsController extends Controller {
	public function __invoke( WP_REST_Request $request ): WP_REST_Response {
		$validation = $this->validate_datatable( $request );

		if ( $validation instanceof WP_REST_Response ) {
			return $validation;
		}

		$validated_data        = $this->get_validated_data();
		$instructor_repository = new InstructorRepository(
			$this->get_date_from(),
			$this->get_date_to(),
			$validated_data['start'] ?? 0,
			$validated_data['length'] ?? 10,
			$validated_data['search']['value'] ?? null
		);

		$instructors = $instructor_repository->get_instructors_data(
			$validated_data['columns'] ?? array(),
			$validated_data['order'] ?? array()
		);
		$total       = $instructor_repository->get_total_instructors();

		return new WP_REST_Response(
			array(
				'recordsTotal'    => intval( $total ),
				'recordsFiltered' => intval( $total ),
				'data'            => ( new InstructorSerializer() )->collectionToArray( $instructors ),
			)
		);
	}
}
