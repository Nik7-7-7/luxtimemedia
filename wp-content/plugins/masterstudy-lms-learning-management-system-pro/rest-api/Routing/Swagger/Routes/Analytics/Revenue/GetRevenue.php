<?php

namespace MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Revenue;

use MasterStudy\Lms\Routing\Swagger\ResponseInterface;
use MasterStudy\Lms\Routing\Swagger\Route;

class GetRevenue extends Route implements ResponseInterface {
	public function response(): array {
		return array(
			'total_revenue'           => array(
				'type'        => 'number',
				'format'      => 'float',
				'description' => 'Total revenue',
			),
			'courses_total'           => array(
				'type'        => 'number',
				'format'      => 'float',
				'description' => 'Total revenue from courses',
			),
			'bundles_total'           => array(
				'type'        => 'number',
				'format'      => 'float',
				'description' => 'Total revenue from bundles',
			),
			'new_students_total'      => array(
				'type'        => 'number',
				'format'      => 'float',
				'description' => 'Total revenue from new students',
			),
			'existing_students_total' => array(
				'type'        => 'number',
				'format'      => 'float',
				'description' => 'Total revenue from existing students',
			),
			'orders_count'            => array(
				'type' => 'integer',
			),
			'memberships_count'       => array(
				'type' => 'integer',
			),
			'earnings'                => array(
				'type'        => 'array',
				'description' => 'Earnings',
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'period' => array(
							'type'        => 'array',
							'description' => 'Periods array',
						),
						'values' => array(
							'type'        => 'array',
							'description' => 'Earned values array',
						),
					),
				),
			),
		);
	}

	public function get_summary(): string {
		return 'Get Revenue';
	}

	public function get_description(): string {
		return 'Returns basic Revenue data.';
	}
}
