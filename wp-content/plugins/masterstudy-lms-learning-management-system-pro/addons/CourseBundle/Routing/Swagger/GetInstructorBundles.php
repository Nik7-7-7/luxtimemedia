<?php

namespace MasterStudy\Lms\Pro\addons\CourseBundle\Routing\Swagger;

use MasterStudy\Lms\Routing\Swagger\RequestInterface;
use MasterStudy\Lms\Routing\Swagger\ResponseInterface;
use MasterStudy\Lms\Routing\Swagger\Route;

class GetInstructorBundles extends Route implements RequestInterface, ResponseInterface {
	public function request(): array {
		return array(
			'page' => array(
				'type'        => 'integer',
				'description' => 'Posts per page.',
			),
			'user' => array(
				'type'        => 'integer',
				'description' => 'Author ID.',
			),
			'pp'   => array(
				'type'        => 'integer',
				'description' => 'Posts per page.',
			),
		);
	}

	public function response(): array {
		return array(
			'courses'     => array(
				'type'        => 'array',
				'description' => 'List of bundle templates.',
			),
			'pagination'  => array(
				'type'        => 'string',
				'description' => 'HTML representation of the pagination.',
			),
			'total_pages' => array(
				'type'        => 'integer',
				'description' => 'Total number of pages.',
			),
		);
	}

	public function get_summary(): string {
		return "Get Instructor's Bundles";
	}

	public function get_description(): string {
		return 'Returns a list of course bundles based on the provided parameters.';
	}
}
