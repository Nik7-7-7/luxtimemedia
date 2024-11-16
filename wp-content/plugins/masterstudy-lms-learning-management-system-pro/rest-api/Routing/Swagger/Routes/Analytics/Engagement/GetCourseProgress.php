<?php
namespace MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Engagement;

use MasterStudy\Lms\Routing\Swagger\Route;

class GetCourses extends Route {
	public function response(): array {
		return array(
			'course_name'  => array(
				'type'        => 'string',
				'description' => 'Course name',
			),
			'not_started'  => array(
				'type'        => 'number',
				'description' => 'Not started courses',
			),
			'in_progress'  => array(
				'type'        => 'number',
				'description' => 'In-progress courses',
			),
			'completed'    => array(
				'type'        => 'number',
				'description' => 'Completed courses',
			),
			'expired'      => array(
				'type'        => 'number',
				'description' => 'Expired courses',
			),
			'reviews'      => array(
				'type'        => 'number',
				'description' => 'Reviews of course',
			),
			'date_created' => array(
				'type'        => 'string',
				'description' => 'Course created Date',
			),
			'course_id'    => array(
				'type'        => 'number',
				'description' => 'Course ID',
			),
		);
	}

	/**
	 * Route Summary
	 */
	public function get_summary(): string {
		return 'Get Progress Courses';
	}
	/**
	 * Route Description
	 */
	public function get_description(): string {
		return 'Every Course with progress status';
	}
}
