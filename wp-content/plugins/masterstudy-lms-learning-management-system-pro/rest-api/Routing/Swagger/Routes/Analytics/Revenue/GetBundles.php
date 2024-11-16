<?php

namespace MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Revenue;

use MasterStudy\Lms\Routing\Swagger\ResponseInterface;
use MasterStudy\Lms\Routing\Swagger\Route;

class GetBundles extends Route implements ResponseInterface {
	public function response(): array {
		return array(
			'bundle_id'      => array(
				'type'        => 'integer',
				'description' => 'Bundle ID',
			),
			'bundle_name'    => array(
				'type'        => 'string',
				'description' => 'Bundle name',
			),
			'courses_inside' => array(
				'type'        => 'integer',
				'description' => 'Courses in Bundle',
			),
			'orders'         => array(
				'type'        => 'integer',
				'description' => 'Orders in Bundle',
			),
			'revenue'        => array(
				'type'        => 'float',
				'description' => 'Bundle Revenue',
			),
		);
	}

	/**
	 * Route Summary
	 */
	public function get_summary(): string {
		return 'Get Bundles';
	}
	/**
	 * Route Description
	 */
	public function get_description(): string {
		return 'Get Revenue Bundles list';
	}
}
