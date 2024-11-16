<?php

namespace MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Instructor;

use MasterStudy\Lms\Routing\Swagger\ResponseInterface;
use MasterStudy\Lms\Routing\Swagger\Route;

class GetInstructorData extends Route implements ResponseInterface {
	public function response(): array {
		return array(
			'total_revenue'      => array(
				'type'        => 'number',
				'format'      => 'float',
				'description' => 'Total revenue',
			),
			'total_enrollments'  => array(
				'type'        => 'integer',
				'description' => 'Total Enrollments',
			),
			'unique_enrollments' => array(
				'type'        => 'integer',
				'description' => 'Unique Enrollments',
			),
			'revenue'            => array(
				'type'        => 'object',
				'description' => 'Revenue',
				'properties'  => array(
					'period' => array(
						'type'        => 'array',
						'description' => 'Period',
					),
					'values' => array(
						'type'        => 'array',
						'description' => 'Values',
					),
				),
			),
			'enrollments'        => array(
				'type'        => 'object',
				'description' => 'Enrollments',
				'properties'  => array(
					'periods' => array(
						'type'        => 'array',
						'description' => 'Periods',
					),
					'all'     => array(
						'type'        => 'array',
						'description' => 'All',
					),
					'unique'  => array(
						'type'        => 'array',
						'description' => 'Unique',
					),
				),
			),
		);
	}

	/**
	 * Route Summary
	 */
	public function get_summary(): string {
		return 'Get Instructor Data';
	}
	/**
	 * Route Description
	 */
	public function get_description(): string {
		return 'Get Instructor Revenue and Enrollments Data';
	}
}
