<?php

namespace MasterStudy\Lms\Pro\RestApi\Http\Serializers\Engagement;

use MasterStudy\Lms\Http\Serializers\AbstractSerializer;

final class EngagementSerializer extends AbstractSerializer {
	public function toArray( $data ): array {
		return array(
			'total_enrollments'  => intval( $data['total_enrollments'] ?? 0 ),
			'unique_enrollments' => intval( $data['unique_enrollments'] ?? 0 ),
			'total_assignments'  => intval( $data['total_assignments'] ?? 0 ),
			'certificates'       => intval( $data['certificates'] ?? 0 ),
			'new_students'       => intval( $data['new_students'] ?? 0 ),
			'new_courses'        => intval( $data['new_courses'] ?? 0 ),
			'new_lessons'        => intval( $data['new_lessons'] ?? 0 ),
			'new_quizzes'        => intval( $data['new_quizzes'] ?? 0 ),
			'new_assignments'    => intval( $data['new_assignments'] ?? 0 ),
			'new_groups_courses' => intval( $data['new_groups_courses'] ?? 0 ),
			'new_trial_courses'  => intval( $data['new_trial_courses'] ?? 0 ),
			'enrollments'        => array(
				'period' => $data['enrollments']['period'] ?? array(),
				'all'    => $data['enrollments']['all'] ?? array(),
				'unique' => $data['enrollments']['unique'] ?? array(),
			),
			'courses_by_status'  => array(
				'not_started' => intval( $data['courses_by_status']['not_started'] ?? 0 ),
				'in_progress' => intval( $data['courses_by_status']['in_progress'] ?? 0 ),
				'completed'   => intval( $data['courses_by_status']['completed'] ?? 0 ),
			),
			'assignments'        => array(
				'in_progress' => intval( $data['assignments']['in_progress'] ?? 0 ),
				'pending'     => intval( $data['assignments']['pending'] ?? 0 ),
				'passed'      => intval( $data['assignments']['passed'] ?? 0 ),
				'failed'      => intval( $data['assignments']['failed'] ?? 0 ),
			),
		);
	}
}
