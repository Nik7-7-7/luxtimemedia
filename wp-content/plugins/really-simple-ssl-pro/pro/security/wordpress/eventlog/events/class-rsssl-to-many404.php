<?php
/**
 * The 'Rsssl_To_Many404' class is a part of the 'Really Simple Security pro' plugin,
 * which is developed by the company 'Really Simple Plugins'.
 * This class is responsible for handling the to many 404 event.
 *
 * @package     RSSSL\Pro\Security\WordPress\Eventlog\Events  // The categorization of this class.
 */

namespace RSSSL\Pro\Security\WordPress\Eventlog\Events;

use RSSSL\Pro\Security\WordPress\EventLog\Rsssl_Event_Log_Handler;
use RSSSL\Pro\Security\WordPress\Limitlogin\Rsssl_Geo_Location;

/**
 * Class Rsssl_To_Many404
 *
 * This class extends the Rsssl_Event_Log_Handler class and provides methods to handle events,
 * sanitize data, and set translated and formatted messages.
 */
class Rsssl_To_Many404 extends Rsssl_Event_Log_Handler {
	/**
	 * Class constructor.
	 *
	 * Initializes the object with a value of 1000.
	 */
	public function __construct() {
		parent::__construct( 2020 );
	}

	/**
	 * Handles an event.
	 *
	 * @param array $data The event data.
	 *
	 * @return void
	 */
	public static function handle_event( array $data = array() ): void {
		$_self = new self();
		$event = $_self->get_event( $_self->event_code );

		// We get the ip address from the data.
		$ip_address = $data['ip_address'] ?? null;
		$event['description'] = $_self->set_message( ['ip_address' => $ip_address], $event['description'] );

		$country    = Rsssl_Geo_Location::get_county_by_ip( $ip_address );
		$event_data = array(
			'iso2_code'    => $country,
			'country_name' => Rsssl_Geo_Location::get_country_by_iso2( $country ),
		);
		// we log the event with the data.
		$_self->log_event( $event, $event_data );
	}

	/**
	 * Sanitizes an array of data.
	 *
	 * @param array $data The data to sanitize.
	 *
	 * @return array The sanitized data.
	 */
	protected function sanitize( array $data ): array {
		// based on the value if the data is a string we sanitize it.
		foreach ( $data as $key => $value ) {
			if ( is_string( $value ) ) {
				$data[ $key ] = sanitize_text_field( $value );
			}
			if ( isset( $data['ip_address'] ) ) {
				$data['ip_address'] = filter_var( $data['ip_address'], FILTER_VALIDATE_IP );
			}
		}
		// Now here you can add more sanitization for the data for custom values.

		// Return the sanitized data.
		return $data;
	}

	/**
	 * Sets a translated message using sprintf function.
	 *
	 * @param array  $args An array of arguments used in the message.
	 * @param string $message The message to be translated and formatted.
	 *
	 * @return string The formatted and translated message.
	 */
	protected function set_message( array $args, string $message ): string {
		return sprintf( __( $message, 'really-simple-ssl' ), $args['ip_address'] );
	}
}
