<?php
namespace MasterStudy\Lms\Pro\RestApi\Http\Serializers\Course;

use MasterStudy\Lms\Http\Serializers\AbstractSerializer;

final class CourseLessonSerializer extends AbstractSerializer {

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	public function toArray( $data ): array {
		return array(
			'lesson_name'   => $data['lesson_name'],
			'completed'     => sprintf( '%d%%', intval( $data['completed'] ) ),
			'dropped'       => sprintf( '%d%%', intval( $data['dropped'] ) ),
			'not_completed' => intval( $data['not_completed'] ),
			'total'         => intval( $data['total'] ),
			'lesson_type'   => str_replace( '_', ' ', $data['lesson_type'] ),
			'lesson_id'     => intval( $data['lesson_id'] ),
			'date_created'  => $data['lesson_date'],
		);
	}
}
