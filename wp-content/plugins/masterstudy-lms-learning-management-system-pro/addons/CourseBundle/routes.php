<?php

use MasterStudy\Lms\Routing\Router;

/**
 * Public routes for Pro version
 */
$router->group(
	array(
		'middleware' => array(
			\MasterStudy\Lms\Routing\Middleware\Guest::class,
		),
	),
	function ( Router $router ) {
		$router->get(
			'/course-bundles',
			\MasterStudy\Lms\Pro\addons\CourseBundle\Http\Controllers\GetBundlesController::class,
			\MasterStudy\Lms\Pro\addons\CourseBundle\Routing\Swagger\GetBundles::class
		);
		$router->get(
			'/instructor-bundles',
			\MasterStudy\Lms\Pro\addons\CourseBundle\Http\Controllers\GetInstructorBundlesController::class,
			\MasterStudy\Lms\Pro\addons\CourseBundle\Routing\Swagger\GetInstructorBundles::class
		);
	}
);
