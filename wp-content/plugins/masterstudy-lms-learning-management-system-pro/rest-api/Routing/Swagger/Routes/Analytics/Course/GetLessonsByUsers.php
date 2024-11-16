<?php

namespace MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Course;

use MasterStudy\Lms\Routing\Swagger\ResponseInterface;
use MasterStudy\Lms\Routing\Swagger\Route;

class GetLessonsByUsers extends Route implements ResponseInterface {
	public function response(): array {
		return array(
			'user_id'     => array(
				'type'        => 'integer',
				'description' => 'User ID',
			),
			'lesson_name' => array(
				'type'        => 'string',
				'description' => 'Lesson Name',
			),
		);
	}

	/**
	 * Route Summary
	 */
	public function get_summary(): string {
		return 'Get Lessons By Users';
	}
	/**
	 * Route Description
	 */
	public function get_description(): string {
		return 'Get Lessons Engagement by Users progress';
	}
}
