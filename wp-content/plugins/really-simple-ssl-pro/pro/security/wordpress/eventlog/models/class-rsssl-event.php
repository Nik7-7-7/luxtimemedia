<?php
/**
 * The 'Rsssl_Event' class is a part of the 'Really Simple Security pro' plugin,
 * which is developed by the company 'Really Simple Plugins'.
 * This class is responsible for handling the event data.
 *
 * @package     RSSSL\Pro\Security\WordPress\Eventlog\Models  // The categorization of this class.
 */

namespace RSSSL\Pro\Security\WordPress\Eventlog\Models;

use DateTime;
use DateTimeZone;
use Exception;
use RSSSL\Pro\Security\WordPress\Limitlogin\Rsssl_IP_Fetcher;

/**
 * The 'Rsssl_Event' class is responsible for handling the event data.
 *
 * @package     RSSSL\Pro\Security\WordPress\Eventlog\Models
 */
class Rsssl_Event {

	/**
	 * The table for the event.
	 *
	 * @var string TABLE_NAME The constant for the table name 'rsssl_event_logs'.
	 */
	public const TABLE_NAME = 'rsssl_event_logs';

	/**
	 * The available events.
	 *
	 * @var array $available_events The array containing available events.
	 * Format:
	 * $available_events = [
	 *     int event_code => [
	 *         'type'        => 'string',
	 *         'name'        => 'string',
	 *         'description' => 'string',
	 *         'severity'    => 'string',
	 *     ],
	 * ];
	 */
	private $available_events = array(
		1000 => array(
			'type'        => 'Authentication',
			'name'        => 'Login successful',
			'description' => 'Login by %s was successfully.',
			'severity'    => 'informational',
		),
		1001 => array(
			'type'        => 'Authentication',
			'name'        => 'Login failed',
			'description' => 'Login by %s failed (incorrect credentials)',
			'severity'    => 'warning',
		),
		2010 => array(
			'type'        => 'Firewall',
			'name'        => 'Country added to block list',
			'description' => '%s was added to the block list',
			'severity'    => 'informational',
		),
		2011 => array(
			'type'        => 'Firewall',
			'name'        => 'Country removed from block list',
			'description' => '%s was removed from the block list',
			'severity'    => 'informational',
		),
		2015 => array(
			'type'        => 'Firewall',
			'name'        => 'Region added to block list',
			'description' => 'All countries in the region %s were added to the block list',
			'severity'    => 'informational',
		),
		2016 => array(
			'type'        => 'Firewall',
			'name'        => 'Region removed from block list',
			'description' => 'all countries from %s were removed from the block list',
			'severity'    => 'informational',
		),
		2020 => array(
			'type'        => 'Firewall',
			'name'        => '404 threshold exceeded',
			'description' => 'IP address: %s exceeded the 404 threshold',
			'severity'    => 'warning',
		),
		2030 => array(
			'type'        => 'Firewall',
			'name'        => 'IP added to block list',
			'description' => 'IP address: %s was blocked by the administrator',
			'severity'    => 'warning',
		),
		2031 => array(
			'type'        => 'Firewall',
			'name'        => 'IP removed from block list',
			'description' => 'iP address: %s was removed from the block list',
			'severity'    => 'informational',
		),
		2040 => array(
			'type'        => 'Firewall',
			'name'        => 'IP added to allowlist',
			'description' => 'IP address: %s was added to the allowlist',
			'severity'    => 'informational',
		),
		2041 => array(
			'type'        => 'Firewall',
			'name'        => 'IP removed from allowlist',
			'description' => 'IP address: %s was removed from the allowlist',
			'severity'    => 'informational',
		),
	);

	/**
	 * Retrieve event data based on the given code.
	 *
	 * @param string $code The code to look up the event.
	 *
	 * @return array The event data associated with the code, or an empty array if not found.
	 */
	public function get_event_data( string $code ): array {
		return $this->available_events[ $code ] ?? array();
	}

	/**
	 * Creates a new event with the given event, message, and data.
	 *
	 * @param int $event The event code.
	 * @param string $name The event short message.
	 * @param string $message The event message.
	 * @param string $type The event type.
	 * @param string $severity The event severity.
	 * @param array $event_data The event data.
	 * @param string $username The username associated with the event.
	 *
	 * @throws Exception
	 */
	public function create( int $event, string $name, string $message, string $type, string $severity, array $event_data = array(), string $username = '' ): void {
		global $wpdb;

		// Always use UTC time.
		$date = new DateTime('now', new DateTimeZone('UTC'));
		$timestamp = $date->getTimestamp();

		$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->base_prefix . self::TABLE_NAME,
			array(
				'event_id'    => $event,
				'event_name'  => $name,
				'description' => $message,
				'event_type'  => $type,
				'severity'    => $severity,
				'timestamp'   => $timestamp,
				'source_ip'   => ( new Rsssl_IP_Fetcher() )->get_ip_address()[0],
				'event_data'  => wp_json_encode( $event_data ),
				'username'    => $username,
			)
		);
	}
}
