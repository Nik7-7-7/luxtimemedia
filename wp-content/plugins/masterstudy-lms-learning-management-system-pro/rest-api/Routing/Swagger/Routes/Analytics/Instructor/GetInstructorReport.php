<?php

namespace MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Instructor;

use MasterStudy\Lms\Routing\Swagger\ResponseInterface;
use MasterStudy\Lms\Routing\Swagger\Route;

class GetInstructorReport extends Route implements ResponseInterface {
	public function response(): array {
		return array(
			'revenue'      => array(
				'type'        => 'number',
				'format'      => 'float',
				'description' => 'Revenue',
			),
			'orders'       => array(
				'type'        => 'integer',
				'description' => 'Number of orders',
			),
			'courses'      => array(
				'type'        => 'integer',
				'description' => 'Number of courses',
			),
			'enrollments'  => array(
				'type'        => 'integer',
				'description' => 'Number of enrollments',
			),
			'students'     => array(
				'type'        => 'integer',
				'description' => 'Number of students',
			),
			'reviews'      => array(
				'type'        => 'integer',
				'description' => 'Number of reviews',
			),
			'certificates' => array(
				'type'        => 'integer',
				'description' => 'Number of certificates',
			),
			'bundles'      => array(
				'type'        => 'integer',
				'description' => 'Number of bundles',
			),
		);
	}

	/**
	 * Route Summary
	 */
	public function get_summary(): string {
		return 'Get Instructor Short Report';
	}
	/**
	 * Route Description
	 */
	public function get_description(): string {
		return 'Get Instructor Short Report Data for the given period';
	}
}
