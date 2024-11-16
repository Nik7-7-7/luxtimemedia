<?php
namespace MasterStudy\Lms\Pro\RestApi\Http\Serializers\Course;

use MasterStudy\Lms\Http\Serializers\AbstractSerializer;

final class LessonsByUsersSerializer extends AbstractSerializer {

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	public function toArray( $data ): array {
		$columns               = array_map(
			function( $column ) {
				return explode( '|', $column );
			},
			explode( ',', $data['completed'] )
		);
		$ready_columns         = array(
			'user_id'      => $data['user_id'],
			'student_name' => $data['student_name'],
		) + array_column( $columns, 1, 0 );
		$ready_columns['last'] = '';

		return $ready_columns;
	}
}
