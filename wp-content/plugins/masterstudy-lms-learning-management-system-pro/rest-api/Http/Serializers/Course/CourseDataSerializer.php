<?php

namespace MasterStudy\Lms\Pro\RestApi\Http\Serializers\Course;

use MasterStudy\Lms\Http\Serializers\AbstractSerializer;

final class CourseDataSerializer extends AbstractSerializer {
	public function toArray( $data ): array {
		return array(
			'total_revenue'     => floatval( $data['courses_total'] ?? 0 ),
			'total_enrollments' => intval( $data['total_enrollments'] ?? 0 ),
			'total_assignments' => intval( $data['total_assignments'] ?? 0 ),
			'certificates'      => intval( $data['certificates'] ?? 0 ),
			'orders_count'      => max( intval( $data['orders_count'] - $data['bundles_count'] ), 0 ),
			'preorders_count'   => intval( $data['preorders_count'] ?? 0 ),
			'subscribers_count' => intval( $data['subscribers_count'] ?? 0 ),
			'reviews'           => intval( $data['reviews'] ?? 0 ),
			'earnings'          => array(
				'period' => $data['earnings']['period'] ?? array(),
				'values' => $data['earnings']['values'] ?? array(),
			),
			'preorders'         => array(
				'period' => $data['preorders']['period'] ?? array(),
				'values' => $data['preorders']['values'] ?? array(),
			),
			'subscribers'       => array(
				'period' => $data['subscribers']['period'] ?? array(),
				'values' => $data['subscribers']['values'] ?? array(),
			),
			'enrollments'       => array(
				'period' => $data['enrollments']['period'] ?? array(),
				'values' => $data['enrollments']['all'] ?? array(),
			),
			'courses_by_status' => array(
				'not_started' => intval( $data['courses_by_status']['not_started'] ?? 0 ),
				'in_progress' => intval( $data['courses_by_status']['in_progress'] ?? 0 ),
				'completed'   => intval( $data['courses_by_status']['completed'] ?? 0 ),
			),
			'assignments'       => array(
				'in_progress' => intval( $data['assignments']['in_progress'] ?? 0 ),
				'pending'     => intval( $data['assignments']['pending'] ?? 0 ),
				'passed'      => intval( $data['assignments']['passed'] ?? 0 ),
				'failed'      => intval( $data['assignments']['failed'] ?? 0 ),
			),
		);
	}
}
