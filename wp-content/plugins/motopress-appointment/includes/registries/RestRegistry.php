<?php
/**
 * API endpoint handler.
 *
 * This handles API related functionality in Motopress Appointment Booking.
 * The main REST API in Motopress Appointment Booking which is built on top of the WP REST API.
 *
 * @package MotoPress\Appointment\Rest
 * @since 1.8.0
 */

namespace MotoPress\Appointment\Registries;

use MotoPress\Appointment\Rest\Server;

class RestRegistry {

	/**
	 * This is domain for the REST API and takes
	 * first-order position in endpoint URLs.
	 *
	 * @var string
	 */
	const VENDOR = 'mpa';

	/**
	 * This is the major version for the REST API and takes
	 * first-order position in endpoint URLs.
	 *
	 * @var string
	 */
	const VERSION = '1.0.0';

	protected $server;

	public function __construct() {
		$this->server = Server::instance();
		$this->server->init();
	}

	public function getServer() {
		return $this->server;
	}
}
