<?php

namespace MasterStudy\Lms\Pro\RestApi\Providers;

use MasterStudy\Lms\Pro\RestApi\Interfaces\ProviderInterface;

class WoocommerceProvider implements ProviderInterface {
	public function get_providers(): array {
		return array(
			'orders'   => \MasterStudy\Lms\Pro\RestApi\Repositories\Checkout\Woocommerce\OrderRepository::class,
			'groups'   => \MasterStudy\Lms\Pro\RestApi\Repositories\Checkout\Woocommerce\GroupRepository::class,
			'bundles'  => \MasterStudy\Lms\Pro\RestApi\Repositories\Checkout\Woocommerce\BundleRepository::class,
			'students' => \MasterStudy\Lms\Pro\RestApi\Repositories\Checkout\Woocommerce\StudentRepository::class,
			'courses'  => \MasterStudy\Lms\Pro\RestApi\Repositories\Checkout\Woocommerce\CourseRepository::class,
		);
	}
}
