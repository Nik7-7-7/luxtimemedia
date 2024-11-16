<?php

namespace MasterStudy\Lms\Pro\RestApi\Http\Serializers\Instructor;

use MasterStudy\Lms\Http\Serializers\AbstractSerializer;

final class InstructorDataSerializer extends AbstractSerializer {
	public function toArray( $data ): array {
		return array(
			'total_revenue'      => floatval( $data['total_revenue'] ?? 0 ),
			'total_enrollments'  => intval( $data['total_enrollments'] ?? 0 ),
			'unique_enrollments' => intval( $data['unique_enrollments'] ?? 0 ),
			'earnings'           => array(
				'period' => $data['earnings']['period'] ?? array(),
				'values' => $data['earnings']['values'] ?? array(),
			),
			'enrollments'        => array(
				'period' => $data['enrollments']['period'] ?? array(),
				'all'    => $data['enrollments']['all'] ?? array(),
				'unique' => $data['enrollments']['unique'] ?? array(),
			),
		);
	}
}
