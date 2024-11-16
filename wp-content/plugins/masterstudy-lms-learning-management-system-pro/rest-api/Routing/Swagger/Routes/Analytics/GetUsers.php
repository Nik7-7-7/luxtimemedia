<?php

namespace MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics;

use MasterStudy\Lms\Routing\Swagger\ResponseInterface;
use MasterStudy\Lms\Routing\Swagger\Route;

class GetUsers extends Route implements ResponseInterface {
	public function response(): array {
		return array(
			'total_users'       => array(
				'type'        => 'integer',
				'description' => 'Total Users',
			),
			'total_students'    => array(
				'type'        => 'integer',
				'description' => 'Total Students',
			),
			'total_instructors' => array(
				'type'        => 'integer',
				'description' => 'Total Instructors',
			),
			'users'             => array(
				'type'        => 'array',
				'description' => 'Users',
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'period' => array(
							'type'        => 'array',
							'description' => 'Periods array',
						),
						'values' => array(
							'type'        => 'array',
							'description' => 'Users values array',
						),
					),
				),
			),
			'instructors'       => array(
				'type'        => 'array',
				'description' => 'Instructors',
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'period' => array(
							'type'        => 'array',
							'description' => 'Periods array',
						),
						'values' => array(
							'type'        => 'array',
							'description' => 'Instructors values array',
						),
					),
				),
			),
		);
	}

	public function get_summary(): string {
		return 'Get Users Data';
	}

	public function get_description(): string {
		return 'Returns Users statistics and charts data';
	}
}
