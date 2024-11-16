<?php

namespace MasterStudy\Lms\Pro\addons\MultiInstructors;

use MasterStudy\Lms\Plugin\Addon;
use MasterStudy\Lms\Plugin\Addons;

final class MultiInstructors implements Addon {

	/**
	 * @return string
	 */
	public function get_name(): string {
		return 'multi_instructors';
	}

	/**
	 *
	 * @param \MasterStudy\Lms\Plugin $plugin
	 */
	public function register( \MasterStudy\Lms\Plugin $plugin ): void {
		$plugin->load_file( __DIR__ . '/hooks.php' );
		$plugin->get_router()->load_routes( __DIR__ . '/routes.php' );
	}
}
