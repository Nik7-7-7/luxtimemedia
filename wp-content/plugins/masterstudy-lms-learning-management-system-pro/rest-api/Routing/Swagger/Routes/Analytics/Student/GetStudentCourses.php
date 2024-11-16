<?php

namespace MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Student;

use MasterStudy\Lms\Routing\Swagger\ResponseInterface;
use MasterStudy\Lms\Routing\Swagger\Route;

class GetStudentCourses extends Route implements ResponseInterface {
	public function response(): array {
		return array(
			'name'     => array(
				'type'        => 'string',
				'description' => 'Student name.',
			),
			'duration' => array(
				'type'        => 'string',
				'description' => 'Enrollments.',
			),
			'started'  => array(
				'type'        => 'string',
				'description' => 'Student reviews.',
			),
			'progress' => array(
				'type'        => 'integer',
				'description' => 'Student joined date.',
			),
		);
	}

	public function get_summary(): string {
		return 'Get Student Course progress statistics';
	}

	public function get_description(): string {
		return 'Returns basic Student Course progress statistics data.';
	}
}
