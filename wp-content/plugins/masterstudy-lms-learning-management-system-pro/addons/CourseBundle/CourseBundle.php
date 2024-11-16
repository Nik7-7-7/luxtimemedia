<?php

namespace MasterStudy\Lms\Pro\addons\CourseBundle;

use MasterStudy\Lms\Plugin;
use MasterStudy\Lms\Plugin\Addon;
use MasterStudy\Lms\Plugin\Addons;

class CourseBundle implements Addon {
	public function get_name(): string {
		return Addons::COURSE_BUNDLE;
	}

	public function register( Plugin $plugin ): void {
		$plugin->load_file( __DIR__ . '/actions.php' );
		$plugin->load_file( __DIR__ . '/ajax-actions.php' );
		$plugin->load_file( __DIR__ . '/filters.php' );
		$plugin->load_file( __DIR__ . '/vc-module.php' );
		$plugin->load_file( __DIR__ . '/woocommerce.php' );
		$plugin->get_router()->load_routes( __DIR__ . '/routes.php' );
	}
}
