<?php

namespace MasterStudy\Lms\Pro\RestApi\Http\Serializers\Revenue;

use MasterStudy\Lms\Http\Serializers\AbstractSerializer;

final class CourseSerializer extends AbstractSerializer {
	/**
	 * @param array $data
	 *
	 * @return array
	 */
	public function toArray( $data ): array {
		return array(
			'number'       => $data['number'] ?? 0,
			'course_id'    => intval( $data['course_id'] ),
			'course_name'  => $data['course_name'],
			'course_slug'  => $data['course_slug'],
			'enrollments'  => (int) $data['enrollments'],
			'price'        => (float) $data['price'],
			'revenue'      => $data['revenue'],
			'views'        => (int) $data['views'],
			'date_created' => $data['date_created'],
		);
	}
}
