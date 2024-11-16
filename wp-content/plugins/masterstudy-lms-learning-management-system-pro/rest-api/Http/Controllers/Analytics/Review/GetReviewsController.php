<?php
namespace MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Review;

use MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics\Controller;
use MasterStudy\Lms\Pro\RestApi\Http\Serializers\Review\ReviewsSerializer;
use MasterStudy\Lms\Pro\RestApi\Repositories\DataTable\ReviewRepository;
use WP_REST_Request;
use WP_REST_Response;

final class GetReviewsController extends Controller {
	public function __invoke( string $status, WP_REST_Request $request ): WP_REST_Response {
		$validation = $this->validate_datatable( $request );

		if ( $validation instanceof WP_REST_Response ) {
			return $validation;
		}

		$validated_data    = $this->get_validated_data();
		$review_repository = new ReviewRepository(
			$this->get_date_from(),
			$this->get_date_to(),
			$validated_data['start'] ?? 1,
			$validated_data['length'] ?? 10,
			$validated_data['search']['value'] ?? null
		);

		$reviews       = $review_repository->get_reviews_data(
			$status,
			$validated_data['columns'] ?? array(),
			$validated_data['order'] ?? array()
		);
		$reviews_total = $review_repository->get_total( array( $status ) );

		return new WP_REST_Response(
			array(
				'recordsTotal'    => $reviews_total,
				'recordsFiltered' => $reviews_total,
				'data'            => ( new ReviewsSerializer() )->collectionToArray( $reviews ),
			)
		);
	}
}
