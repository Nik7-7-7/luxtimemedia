<?php

namespace MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Analytics\Review;

use MasterStudy\Lms\Routing\Swagger\ResponseInterface;
use MasterStudy\Lms\Routing\Swagger\Route;

class GetReviewCharts extends Route implements ResponseInterface {
	public function response(): array {
		return array(
			'total'              => array(
				'type'        => 'integer',
				'description' => 'Total number of reviews',
			),
			'courses_chart'      => array(
				'type'        => 'object',
				'description' => 'Reviews count by period',
				'properties'  => array(
					'period' => array(
						'type'        => 'array',
						'items'       => array( 'type' => 'string' ),
						'description' => 'Array of periods (dates) for the reviews',
					),
					'items'  => array(
						'type'        => 'array',
						'description' => 'Array of review data for each period',
						'items'       => array(
							'type'       => 'object',
							'properties' => array(
								'label'  => array(
									'type'        => 'string',
									'description' => 'Label for the review data (e.g., "Reviews")',
								),
								'values' => array(
									'type'        => 'array',
									'items'       => array( 'type' => 'integer' ),
									'description' => 'Array of review counts for each period',
								),
							),
						),
					),
				),
			),
			'total_by_type'      => array(
				'type'        => 'array',
				'description' => 'Total reviews count by rating type (1 to 5 stars)',
				'items'       => array( 'type' => 'integer' ),
			),
			'reviews_type_chart' => array(
				'type'        => 'object',
				'description' => 'Reviews count by rating type for each period',
				'properties'  => array(
					'period' => array(
						'type'        => 'array',
						'items'       => array( 'type' => 'string' ),
						'description' => 'Array of periods (dates) for the reviews',
					),
					'items'  => array(
						'type'        => 'array',
						'description' => 'Array of review data for each rating type across periods',
						'items'       => array(
							'type'       => 'object',
							'properties' => array(
								'label'  => array(
									'type'        => 'string',
									'description' => 'Label for the rating type (e.g., "5 stars")',
								),
								'values' => array(
									'type'        => 'array',
									'items'       => array( 'type' => 'integer' ),
									'description' => 'Array of review counts for each period by rating type',
								),
							),
						),
					),
				),
			),
		);
	}

	/**
	 * Route Summary
	 */
	public function get_summary(): string {
		return 'Get Review Statistics';
	}

	/**
	 * Route Description
	 */
	public function get_description(): string {
		return 'Retrieve review statistics, including total reviews, review counts by period, and review counts by rating type for each period.';
	}
}
