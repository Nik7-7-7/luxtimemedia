<?php

use MasterStudy\Lms\Routing\Router;

/** @var Router $router */

// Orders routes
$router->group(
	array(
		'middleware' => array(
			\MasterStudy\Lms\Routing\Middleware\Authentication::class,
			\MasterStudy\Lms\Routing\Middleware\PostGuard::class,
		),
		'prefix'     => '/orders',
	),
	function ( Router $router ) {
		$router->get(
			'/woocommerce-orders',
			\MasterStudy\Lms\Pro\RestApi\Http\Controllers\Orders\GetOrdersController::class,
			\MasterStudy\Lms\Pro\RestApi\Routing\Swagger\Routes\Orders\GetOrders::class,
		);
	}
);
