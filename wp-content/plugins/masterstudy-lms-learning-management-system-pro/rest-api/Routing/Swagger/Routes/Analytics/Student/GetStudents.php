<?php

namespace MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Student;

use MasterStudy\Lms\Routing\Swagger\ResponseInterface;
use MasterStudy\Lms\Routing\Swagger\Route;

class GetStudents extends Route implements ResponseInterface {
	public function response(): array {
		return array(
			'student_id'  => array(
				'type'        => 'integer',
				'description' => 'Student ID.',
			),
			'name'        => array(
				'type'        => 'string',
				'description' => 'Student name.',
			),
			'enrollments' => array(
				'type'        => 'integer',
				'description' => 'Enrollments.',
			),
			'reviews'     => array(
				'type'        => 'integer',
				'description' => 'Student reviews.',
			),
			'joined'      => array(
				'type'        => 'string',
				'description' => 'Student joined date.',
			),
		);
	}

	public function get_summary(): string {
		return 'Get Student Statistics';
	}

	public function get_description(): string {
		return 'Returns basic Student Statistics data.';
	}
}
