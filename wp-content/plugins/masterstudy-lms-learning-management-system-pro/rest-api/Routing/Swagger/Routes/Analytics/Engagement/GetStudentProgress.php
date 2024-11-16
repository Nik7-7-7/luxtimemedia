<?php
namespace MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Engagement;

use MasterStudy\Lms\Routing\Swagger\Route;

class GetStudents extends Route {
	public function response(): array {
		return array(
			'student_name' => array(
				'type'        => 'string',
				'description' => 'Student name',
			),
			'total'        => array(
				'type'        => 'number',
				'description' => 'Total',
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
			'certificates' => array(
				'type'        => 'number',
				'description' => 'Certificates',
			),
			'joined'       => array(
				'type'        => 'number',
				'description' => 'Joined courses',
			),
		);
	}

	/**
	 * Route Summary
	 */
	public function get_summary(): string {
		return 'Get Progress Students';
	}
	/**
	 * Route Description
	 */
	public function get_description(): string {
		return 'Students with progress by courses';
	}
}
