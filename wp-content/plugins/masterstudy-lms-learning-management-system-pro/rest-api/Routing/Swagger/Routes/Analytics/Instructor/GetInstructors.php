<?php

namespace MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Instructor;

use MasterStudy\Lms\Routing\Swagger\ResponseInterface;
use MasterStudy\Lms\Routing\Swagger\Route;

class GetInstructors extends Route implements ResponseInterface {
	public function response(): array {
		return array(
			'instructor_id' => array(
				'type'        => 'integer',
				'description' => 'Instructor id.',
			),
			'name'          => array(
				'type'        => 'string',
				'description' => 'Instructor name.',
			),
			'enrollments'   => array(
				'type'        => 'integer',
				'description' => 'Enrollments',
			),
			'own_courses'   => array(
				'type'        => 'integer',
				'description' => 'Own courses',
			),
			'reviews'       => array(
				'type'        => 'integer',
				'description' => 'Instructor reviews',
			),
			'joined'        => array(
				'type'        => 'string',
				'description' => 'Instructor joined date',
			),
		);
	}

	public function get_summary(): string {
		return 'Get Instructor Statistics';
	}

	public function get_description(): string {
		return 'Returns basic Instructor Statistics data.';
	}
}
