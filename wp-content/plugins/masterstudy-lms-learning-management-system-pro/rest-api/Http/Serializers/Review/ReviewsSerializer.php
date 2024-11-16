<?php

namespace MasterStudy\Lms\Pro\RestApi\Http\Serializers\Review;

use MasterStudy\Lms\Http\Serializers\AbstractSerializer;

final class ReviewsSerializer extends AbstractSerializer {

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	public function toArray( $data ): array {
		return array(
			'user_name'    => $data['user_name'],
			'course_name'  => $data['course_name'],
			'review'       => $data['review'],
			'rating'       => intval( $data['rating'] ),
			'date_created' => $data['date_created_view'],
			'review_id'    => intval( $data['review_id'] ),
		);
	}
}
