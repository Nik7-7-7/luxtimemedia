<?php

namespace MasterStudy\Lms\Pro\RestApi\Http\Controllers\Analytics;

use MasterStudy\Lms\Pro\RestApi\Services\CheckoutService;
use MasterStudy\Lms\Pro\RestApi\Providers\MasterstudyProvider;
use MasterStudy\Lms\Pro\RestApi\Providers\WoocommerceProvider;
use MasterStudy\Lms\Pro\RestApi\Traits\AnalyticsValidator;

class Controller {
	use AnalyticsValidator;

	/**
	 * Returns active Checkout provider.
	 */
	public function get_checkout_provider(): CheckoutService {
		$checkout = \STM_LMS_Options::get_option( 'wocommerce_checkout', false ) && class_exists( 'WooCommerce' )
			? new WoocommerceProvider()
			: new MasterstudyProvider();

		return new CheckoutService( $checkout );
	}
}
