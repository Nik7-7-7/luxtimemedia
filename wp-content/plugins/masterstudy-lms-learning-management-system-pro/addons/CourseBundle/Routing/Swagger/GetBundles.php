<?php

namespace MasterStudy\Lms\Pro\addons\CourseBundle\Routing\Swagger;

use MasterStudy\Lms\Routing\Swagger\RequestInterface;
use MasterStudy\Lms\Routing\Swagger\ResponseInterface;
use MasterStudy\Lms\Routing\Swagger\Route;

class GetBundles extends Route implements RequestInterface, ResponseInterface {
	public function request(): array {
		return array(
			'per_page' => array(
				'type'        => 'integer',
				'description' => 'Posts per page. Default is 10.',
			),
			'author'   => array(
				'type'        => 'integer',
				'description' => 'Author ID.',
			),
		);
	}

	public function response(): array {
		return array(
			'bundles' => array(
				'type'  => 'array',
				'items' => array(),
			),
		);
	}

	public function get_summary(): string {
		return 'Get Course Bundles';
	}

	public function get_description(): string {
		return 'Returns a list of course bundles based on the provided parameters.';
	}
}
