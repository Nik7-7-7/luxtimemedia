<?php

namespace MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Revenue;

use MasterStudy\Lms\Routing\Swagger\ResponseInterface;
use MasterStudy\Lms\Routing\Swagger\Route;

class GetStudents extends Route implements ResponseInterface {
	public function response(): array {
		return array(
			'course_id'    => array(
				'type'        => 'integer',
				'description' => 'Course id.',
			),
			'course_name'  => array(
				'type'        => 'string',
				'description' => 'Course name.',
			),
			'enrollments'  => array(
				'type'        => 'integer',
				'description' => 'Enrollments',
			),
			'price'        => array(
				'type'        => 'float',
				'description' => 'Course Price',
			),
			'revenue'      => array(
				'type'        => 'float',
				'description' => 'Course Revenue',
			),
			'views'        => array(
				'type'        => 'integer',
				'description' => 'Course views',
			),
			'date_created' => array(
				'type'        => 'string',
				'description' => 'Course created Date',
			),
		);
	}

	public function get_summary(): string {
		return 'Get Course Revenue';
	}

	public function get_description(): string {
		return 'Returns basic Course Revenue data.';
	}
}
