<?php

namespace MotoPress\Appointment\REST\Controllers\Motopress\Appointment\V1;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
abstract class AbstractRestController {

	/** @since 1.0 */
	const VENDOR = 'motopress/appointment';

	/** @since 1.0 */
	const VERSION = 1;

	/**
	 * @since 1.0
	 */
	abstract public function register_routes();

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getNamespace() {
		// 'motopress/appointment/v1'
		return static::VENDOR . '/v' . static::VERSION;
	}
}
