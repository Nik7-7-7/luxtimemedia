<?php

namespace MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Instructor;

use MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Controller;
use MasterStudy\Lms\Pro\RestApi\Http\Serializers\Instructor\InstructorReportSerializer;
use MasterStudy\Lms\Pro\RestApi\Repositories\Instructor\EnrollmentRepository;
use WP_REST_Request;
use WP_REST_Response;

final class GetInstructorReportController extends Controller {
	public function __invoke( WP_REST_Request $request ): WP_REST_Response {
		$validation = $this->validate( $request );

		if ( $validation instanceof WP_REST_Response ) {
			return $validation;
		}

		$enrollment_repository = new EnrollmentRepository( get_current_user_id(), $this->get_date_from(), $this->get_date_to() );
		$orders_provider       = $this->get_checkout_provider()->get_provider( 'orders' );
		$revenue_repository    = new $orders_provider( $this->get_date_from(), $this->get_date_to() );

		return new WP_REST_Response(
			( new InstructorReportSerializer() )->toArray(
				array_merge(
					$revenue_repository->get_instructor_revenue(
						$enrollment_repository->get_instructor_course_ids_for_orders()
					),
					$enrollment_repository->get_instructor_data()
				)
			)
		);
	}
}
