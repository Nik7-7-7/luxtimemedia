<?php

namespace MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Course;

use MasterStudy\Lms\Routing\Swagger\ResponseInterface;
use MasterStudy\Lms\Routing\Swagger\Route;

class GetCourseLessons extends Route implements ResponseInterface {
	public function response(): array {
		return array(
			'lesson_id'     => array(
				'type'        => 'integer',
				'description' => 'Lesson ID',
			),
			'lesson_name'   => array(
				'type'        => 'string',
				'description' => 'Lesson Name',
			),
			'completed'     => array(
				'type'        => 'integer',
				'description' => 'Lesson completed',
			),
			'dropped'       => array(
				'type'        => 'integer',
				'description' => 'Lesson dropped',
			),
			'not_completed' => array(
				'type'        => 'integer',
				'description' => 'Lesson not completed',
			),
			'total'         => array(
				'type'        => 'integer',
				'description' => 'Total lesson in users',
			),
			'lesson_type'   => array(
				'type'        => 'string',
				'description' => 'Lesson type',
			),
			'date_created'  => array(
				'type'        => 'string',
				'description' => 'Date of create lesson',
			),
		);
	}

	/**
	 * Route Summary
	 */
	public function get_summary(): string {
		return 'Get Lessons';
	}
	/**
	 * Route Description
	 */
	public function get_description(): string {
		return 'Get Lessons Engagement';
	}
}
