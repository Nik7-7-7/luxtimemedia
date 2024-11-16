<?php

namespace MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Review;

use MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Controller;
use MasterStudy\Lms\Pro\RestApi\Http\Serializers\Review\ReviewChartsSerializer;
use MasterStudy\Lms\Pro\RestApi\Repositories\ReviewRepository;
use WP_REST_Request;
use WP_REST_Response;

final class GetReviewChartsController extends Controller {
	public function __invoke( WP_REST_Request $request ): WP_REST_Response {
		$validation = $this->validate( $request );

		if ( $validation instanceof WP_REST_Response ) {
			return $validation;
		}

		$reviews_repository = new ReviewRepository(
			$this->get_date_from(),
			$this->get_date_to(),
		);

		return new WP_REST_Response(
			( new ReviewChartsSerializer() )->toArray( $reviews_repository->get_reviews_data() )
		);
	}
}
