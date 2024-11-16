<?php

namespace MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Course;

use MasterStudy\Lms\Http\WpResponseFactory;
use MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Controller;
use MasterStudy\Lms\Pro\RestApi\Http\Serializers\Course\CourseDataSerializer;
use MasterStudy\Lms\Pro\RestApi\Repositories\EngagementRepository;
use MasterStudy\Lms\Repositories\CourseRepository;
use WP_REST_Request;
use WP_REST_Response;

final class GetCourseDataController extends Controller {
	public function __invoke( int $course_id, WP_REST_Request $request ): WP_REST_Response {
		if ( ! ( new CourseRepository() )->exists( $course_id ) ) {
			return WpResponseFactory::not_found();
		}

		$validation = $this->validate( $request );

		if ( $validation instanceof WP_REST_Response ) {
			return $validation;
		}

		$orders_provider       = $this->get_checkout_provider()->get_provider( 'orders' );
		$revenue_repository    = new $orders_provider( $this->get_date_from(), $this->get_date_to(), $course_id );
		$engagement_repository = new EngagementRepository( $this->get_date_from(), $this->get_date_to(), $course_id );

		$course_data = array_merge(
			$revenue_repository->get_revenue(),
			$engagement_repository->get_charts_data(),
			$engagement_repository->get_subscribers(),
			array(
				'reviews' => $engagement_repository->get_reviews_count(),
			)
		);

		return new WP_REST_Response(
			( new CourseDataSerializer() )->toArray( $course_data )
		);
	}
}
