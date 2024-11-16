<?php
namespace MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Review;

use MasterStudy\Lms\Routing\Swagger\ResponseInterface;
use MasterStudy\Lms\Routing\Swagger\Route;

class GetUsers extends Route implements ResponseInterface {
	public function response(): array {
		return array(
			'student_name' => array(
				'type'        => 'string',
				'description' => 'Student name',
			),
			'reviews'      => array(
				'type'        => 'number',
				'description' => 'Reviews of student',
			),
			'student_id'   => array(
				'type'        => 'number',
				'description' => 'Student ID',
			),
		);
	}

	/**
	 * Route Summary
	 */
	public function get_summary(): string {
		return 'Get Student reviews';
	}
	/**
	 * Route Description
	 */
	public function get_description(): string {
		return 'Every Student with reviews';
	}
}
