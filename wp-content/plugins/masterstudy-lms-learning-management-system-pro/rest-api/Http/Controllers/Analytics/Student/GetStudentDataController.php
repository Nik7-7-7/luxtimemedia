<?php

namespace MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Student;

use MasterStudy\Lms\Http\WpResponseFactory;
use MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Controller;
use MasterStudy\Lms\Pro\RestApi\Http\Serializers\Student\StudentDataSerializer;
use MasterStudy\Lms\Pro\RestApi\Repositories\Student\EnrollmentRepository;
use WP_REST_Request;
use WP_REST_Response;

final class GetStudentDataController extends Controller {
	public function __invoke( int $user_id, WP_REST_Request $request ): WP_REST_Response {
		if ( ! get_userdata( $user_id ) ) {
			return WpResponseFactory::not_found();
		}

		$validation = $this->validate( $request );

		if ( $validation instanceof WP_REST_Response ) {
			return $validation;
		}

		$enrollment_repository = new EnrollmentRepository( $user_id, $this->get_date_from(), $this->get_date_to() );
		$orders_provider       = $this->get_checkout_provider()->get_provider( 'orders' );
		$revenue_repository    = new $orders_provider( $this->get_date_from(), $this->get_date_to() );

		return new WP_REST_Response(
			( new StudentDataSerializer() )->toArray(
				array_merge(
					$revenue_repository->get_student_revenue( $user_id ),
					$enrollment_repository->get_enrollments_data()
				)
			)
		);
	}
}
