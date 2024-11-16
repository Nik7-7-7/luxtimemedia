<?php
namespace MasterStudy\Lms\Pro\RestApi\Http\Serializers\Engagement;

use MasterStudy\Lms\Http\Serializers\AbstractSerializer;

final class CourseSerializer extends AbstractSerializer {

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	public function toArray( $data ): array {
		return array(
			'course_name'  => $data['name'],
			'not_started'  => intval( $data['not_started'] ),
			'in_progress'  => intval( $data['in_progress'] ),
			'completed'    => intval( $data['completed'] ),
			'expired'      => intval( $data['expired'] ),
			'reviews'      => intval( $data['reviews'] ),
			'course_id'    => intval( $data['course_id'] ),
			'course_slug'  => $data['course_slug'],
			'date_created' => $data['date_created_view'],
		);
	}
}
