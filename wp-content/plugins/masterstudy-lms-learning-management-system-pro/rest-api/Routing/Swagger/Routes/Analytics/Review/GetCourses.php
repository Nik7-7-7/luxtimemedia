<?php
namespace MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Review;

use MasterStudy\Lms\Routing\Swagger\ResponseInterface;
use MasterStudy\Lms\Routing\Swagger\Route;

class GetCourses extends Route implements ResponseInterface {
	public function response(): array {
		return array(
			'course_name' => array(
				'type'        => 'string',
				'description' => 'Course name',
			),
			'reviews'     => array(
				'type'        => 'number',
				'description' => 'Reviews of course',
			),
			'course_id'   => array(
				'type'        => 'number',
				'description' => 'Course ID',
			),
		);
	}

	/**
	 * Route Summary
	 */
	public function get_summary(): string {
		return 'Get Reviewed Courses';
	}
	/**
	 * Route Description
	 */
	public function get_description(): string {
		return 'Every Course with reviews';
	}
}
