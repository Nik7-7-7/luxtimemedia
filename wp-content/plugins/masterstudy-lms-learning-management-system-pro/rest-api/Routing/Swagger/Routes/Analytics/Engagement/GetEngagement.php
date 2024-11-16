<?php

namespace MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Engagement;

use MasterStudy\Lms\Routing\Swagger\ResponseInterface;
use MasterStudy\Lms\Routing\Swagger\Route;

class GetEngagement extends Route implements ResponseInterface {
	public function response(): array {
		return array(
			'total_enrollments'  => array(
				'type'        => 'integer',
				'description' => 'Total Enrollments',
			),
			'certificates'       => array(
				'type'        => 'integer',
				'description' => 'Given certificates count',
			),
			'total_assignments'  => array(
				'type'        => 'integer',
				'description' => 'Total User Assignments',
			),
			'new_students'       => array(
				'type'        => 'integer',
				'description' => 'New students count',
			),
			'new_courses'        => array(
				'type'        => 'integer',
				'description' => 'New courses count',
			),
			'new_lessons'        => array(
				'type'        => 'integer',
				'description' => 'New lessons count',
			),
			'new_quizzes'        => array(
				'type'        => 'integer',
				'description' => 'New quizzes count',
			),
			'new_assignments'    => array(
				'type'        => 'integer',
				'description' => 'New assignments count',
			),
			'new_groups_courses' => array(
				'type'        => 'integer',
				'description' => 'New group courses count',
			),
			'new_tiral_courses'  => array(
				'type'        => 'integer',
				'description' => 'New tiral courses count',
			),
			'enrollments'        => array(
				'type'       => 'object',
				'properties' => array(
					'periods' => array(
						'type'        => 'array',
						'description' => 'Periods',
					),
					'all'     => array(
						'type'        => 'array',
						'description' => 'All enrollments by period',
					),
					'unique'  => array(
						'type'        => 'array',
						'description' => 'Unique enrollments by period',
					),
				),
			),
			'courses_by_status'  => array(
				'type'       => 'object',
				'properties' => array(
					'not_started' => array(
						'type'        => 'integer',
						'description' => 'Not started courses count',
					),
					'in_progress' => array(
						'type'        => 'integer',
						'description' => 'In progress courses count',
					),
					'completed'   => array(
						'type'        => 'integer',
						'description' => 'Completed courses count',
					),
				),
			),
			'assignments'        => array(
				'type'       => 'object',
				'properties' => array(
					'in_progress' => array(
						'type'        => 'integer',
						'description' => 'In Progress assignments count',
					),
					'pending'     => array(
						'type'        => 'integer',
						'description' => 'Pending assignments count',
					),
					'passed'      => array(
						'type'        => 'integer',
						'description' => 'Passed assignments count',
					),
					'failed'      => array(
						'type'        => 'integer',
						'description' => 'Failed assignments count',
					),
				),
			),
		);
	}

	public function get_summary(): string {
		return 'Get Engagement';
	}

	public function get_description(): string {
		return 'Returns Engagement data.';
	}
}
