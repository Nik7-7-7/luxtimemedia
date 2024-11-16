<?php

namespace MasterStudy\Lms\Pro\RestApi\Http\Serializers\Student;

use MasterStudy\Lms\Http\Serializers\AbstractSerializer;

final class StudentDataSerializer extends AbstractSerializer {
	public function toArray( $data ): array {
		return array(
			'revenue'      => floatval( $data['revenue'] ?? 0 ),
			'orders'       => intval( $data['orders'] ?? 0 ),
			'bundles'      => intval( $data['bundles'] ?? 0 ),
			'groups'       => intval( $data['groups'] ?? 0 ),
			'reviews'      => intval( $data['reviews'] ?? 0 ),
			'certificates' => intval( $data['certificates'] ?? 0 ),
			'points'       => intval( $data['points'] ?? 0 ),
			'courses'      => array(
				'enrolled'    => intval( $data['courses']['enrolled'] ?? 0 ),
				'not_started' => intval( $data['courses']['not_started'] ?? 0 ),
				'in_progress' => intval( $data['courses']['in_progress'] ?? 0 ),
				'completed'   => intval( $data['courses']['completed'] ?? 0 ),
			),
			'quizzes'      => array(
				'passed' => $data['quizzes']['passed'] ?? array(),
				'failed' => $data['quizzes']['failed'] ?? array(),
			),
			'assignments'  => array(
				'in_progress' => $data['assignments']['in_progress'] ?? array(),
				'pending'     => $data['assignments']['pending'] ?? array(),
				'passed'      => $data['assignments']['passed'] ?? array(),
				'failed'      => $data['assignments']['failed'] ?? array(),
			),
			'enrollments'  => array(
				'period'    => $data['enrollments']['period'] ?? array(),
				'all'       => $data['enrollments']['all'] ?? array(),
				'completed' => $data['enrollments']['completed'] ?? array(),
			),
		);
	}
}
