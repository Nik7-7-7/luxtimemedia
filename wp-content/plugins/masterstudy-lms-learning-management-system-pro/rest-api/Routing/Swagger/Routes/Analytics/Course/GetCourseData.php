<?php

namespace MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Course;

use MasterStudy\Lms\Routing\Swagger\ResponseInterface;
use MasterStudy\Lms\Routing\Swagger\Route;

class GetCourseData extends Route implements ResponseInterface {
	public function response(): array {
		return array(
			'total_revenue'     => array(
				'type'        => 'number',
				'format'      => 'float',
				'description' => 'Total revenue',
			),
			'total_enrollments' => array(
				'type'        => 'integer',
				'description' => 'Total Enrollments',
			),
			'total_assignments' => array(
				'type'        => 'integer',
				'description' => 'Total User Assignments',
			),
			'certificates'      => array(
				'type'        => 'integer',
				'description' => 'Given certificates count',
			),
			'orders_count'      => array(
				'type'        => 'integer',
				'description' => 'Orders count',
			),
			'preorders_count'   => array(
				'type'        => 'integer',
				'description' => 'Preorders count',
			),
			'subscribers_count' => array(
				'type'        => 'integer',
				'description' => 'Subscribers count',
			),
			'reviews'           => array(
				'type'        => 'integer',
				'description' => 'Reviews count',
			),
			'earnings'          => array(
				'type'        => 'array',
				'description' => 'Earnings',
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'period' => array(
							'type'        => 'array',
							'description' => 'Periods array',
						),
						'values' => array(
							'type'        => 'array',
							'description' => 'Earned values array',
						),
					),
				),
			),
			'preorders'         => array(
				'type'        => 'array',
				'description' => 'Preorders',
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'period' => array(
							'type'        => 'array',
							'description' => 'Periods array',
						),
						'values' => array(
							'type'        => 'array',
							'description' => 'Preorders values array',
						),
					),
				),
			),
			'subscribers'       => array(
				'type'        => 'array',
				'description' => 'Subscribers',
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'period' => array(
							'type'        => 'array',
							'description' => 'Periods array',
						),
						'values' => array(
							'type'        => 'array',
							'description' => 'Subscribers values array',
						),
					),
				),
			),
			'enrollments'       => array(
				'type'       => 'object',
				'properties' => array(
					'periods' => array(
						'type'        => 'array',
						'description' => 'Periods',
					),
					'values'  => array(
						'type'        => 'array',
						'description' => 'All enrollments by period',
					),
				),
			),
			'courses_by_status' => array(
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
			'assignments'       => array(
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
		return 'Get Single Course Data';
	}

	public function get_description(): string {
		return 'Returns Single Course statistics and charts data';
	}
}
