<?php

namespace MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Orders;

use MasterStudy\Lms\Routing\Swagger\Fields\Post;
use MasterStudy\Lms\Routing\Swagger\RequestInterface;
use MasterStudy\Lms\Routing\Swagger\ResponseInterface;
use MasterStudy\Lms\Routing\Swagger\Route;

class GetOrders extends Route implements RequestInterface, ResponseInterface {
	public function request(): array {
		return array(
			'per_page' => array(
				'type'        => 'integer',
				'description' => 'Posts per page. Default is 10.',
			),
			'page'     => array(
				'type'        => 'integer',
				'description' => 'Current page. Default is 1.',
			),
			'author'   => array(
				'type'        => 'integer',
				'description' => 'Author ID.',
			),
			's'        => array(
				'type'        => 'string',
				'description' => 'Search courses by title.',
			),
			'sort'     => array(
				'type'        => 'string',
				'description' => 'Sort courses by.',
				'enum'        => array(
					'date_low',
					'price_high',
					'price_low',
					'rating',
					'popular',
				),
			),
			'category' => array(
				'type'        => 'string',
				'description' => 'Category IDs. Comma-separated.',
			),
		);
	}

	public function response(): array {
		return array(
			'orders' => Post::as_array(),
			'total'  => array(
				'type'        => 'integer',
				'description' => 'Total number of orders.',
			),
			'pages'  => array(
				'type'        => 'integer',
				'description' => 'Total number of pages.',
			),
		);
	}

	public function get_summary(): string {
		return 'Get Orders';
	}

	public function get_description(): string {
		return 'Returns a list of orders based on the provided parameters.';
	}
}
