<?php
namespace MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Revenue;

use MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Controller;
use MasterStudy\Lms\Pro\RestApi\Http\Serializers\Revenue\StudentSerializer;
use WP_REST_Request;
use WP_REST_Response;

final class GetStudentsController extends Controller {
	public function __invoke( WP_REST_Request $request ): WP_REST_Response {
		$validation = $this->validate_datatable( $request );

		if ( $validation instanceof WP_REST_Response ) {
			return $validation;
		}

		$validated_data      = $this->get_validated_data();
		$students_provider   = $this->get_checkout_provider()->get_provider( 'students' );
		$students_repository = new $students_provider(
			$this->get_date_from(),
			$this->get_date_to(),
			$validated_data['start'] ?? 0,
			$validated_data['length'] ?? 10,
			$validated_data['search']['value'] ?? null
		);

		$students = $students_repository->get_students_data(
			$validated_data['columns'],
			array_key_exists( 'order', $validated_data ) ? $validated_data['order'] : null,
		);
		$total    = $students_repository->get_total();

		return new WP_REST_Response(
			array(
				'recordsTotal'    => intval( $total ),
				'recordsFiltered' => intval( $total ),
				'data'            => ( new StudentSerializer() )->collectionToArray( $students ),
			)
		);
	}
}
