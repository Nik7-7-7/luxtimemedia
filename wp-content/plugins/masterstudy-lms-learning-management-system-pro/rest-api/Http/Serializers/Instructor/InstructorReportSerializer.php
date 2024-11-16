<?php

namespace MasterStudy\Lms\Pro\RestApi\Http\Serializers\Instructor;

use MasterStudy\Lms\Http\Serializers\AbstractSerializer;

final class InstructorReportSerializer extends AbstractSerializer {
	public function toArray( $data ): array {
		return array(
			'revenue'      => floatval( $data['total_revenue'] ?? 0 ),
			'orders'       => intval( $data['orders'] ?? 0 ),
			'courses'      => intval( $data['courses'] ?? 0 ),
			'enrollments'  => intval( $data['enrollments'] ?? 0 ),
			'students'     => intval( $data['students'] ?? 0 ),
			'reviews'      => intval( $data['reviews'] ?? 0 ),
			'certificates' => intval( $data['certificates'] ?? 0 ),
			'bundles'      => intval( $data['bundles'] ?? 0 ),
		);
	}
}
