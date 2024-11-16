<?php
namespace MasterStudy\Lms\Pro\RestApi\Http\Serializers\Engagement;

use MasterStudy\Lms\Http\Serializers\AbstractSerializer;

final class StudentSerializer extends AbstractSerializer {

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	public function toArray( $data ): array {
		return array(
			'student_name' => $data['student_name'],
			'total'        => intval( $data['total'] ),
			'not_started'  => intval( $data['not_started'] ),
			'in_progress'  => intval( $data['in_progress'] ),
			'completed'    => intval( $data['completed'] ),
			'expired'      => intval( $data['expired'] ),
			'reviews'      => intval( $data['reviews'] ),
			'certificates' => intval( $data['certificates'] ),
			'joined'       => $data['joined_view'],
			'student_id'   => intval( $data['user_id'] ),
		);
	}
}
