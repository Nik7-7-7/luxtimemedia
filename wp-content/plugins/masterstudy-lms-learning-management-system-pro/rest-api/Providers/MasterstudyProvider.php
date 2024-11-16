<?php

namespace MasterStudy\Lms\Pro\RestApi\Providers;

use MasterStudy\Lms\Pro\RestApi\Interfaces\ProviderInterface;

class MasterstudyProvider implements ProviderInterface {
	public function get_providers(): array {
		return array(
			'orders'   => \MasterStudy\Lms\Pro\RestApi\Repositories\Checkout\Masterstudy\OrderRepository::class,
			'groups'   => \MasterStudy\Lms\Pro\RestApi\Repositories\Checkout\Masterstudy\GroupRepository::class,
			'bundles'  => \MasterStudy\Lms\Pro\RestApi\Repositories\Checkout\Masterstudy\BundleRepository::class,
			'students' => \MasterStudy\Lms\Pro\RestApi\Repositories\Checkout\Masterstudy\StudentRepository::class,
			'courses'  => \MasterStudy\Lms\Pro\RestApi\Repositories\Checkout\Masterstudy\CourseRepository::class,
		);
	}
}
