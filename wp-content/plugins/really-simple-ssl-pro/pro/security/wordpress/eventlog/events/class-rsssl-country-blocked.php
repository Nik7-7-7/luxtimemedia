<?php
/**
 * The 'Rsssl_Country_Blocked' class is a part of the 'Really Simple Security pro' plugin,
 * which is developed by the company 'Really Simple Plugins'.
 * This class is responsible for handling the country blocked event.
 *
 * @package     RSSSL\Pro\Security\Wordpress\Eventlog\Events  // The categorization of this class.
 */

namespace RSSSL\Pro\Security\WordPress\EventLog\Events;

use RSSSL\Pro\Security\WordPress\EventLog\Rsssl_Event_Log_Handler;

/**
 * The 'Rsssl_Country_Blocked' class is a part of the 'Really Simple Security pro' plugin,
 * which is developed by the company 'Really Simple Plugins'.
 * This class is responsible for handling the country blocked event.
 *
 * @package     RSSSL\Pro\Security\Wordpress\Eventlog\Events
 */
class Rsssl_Country_Blocked extends Rsssl_Event_Log_Handler {
	/**
	 * Class constructor.
	 *
	 * Initializes the object with a value of 1000.
	 */
	public function __construct() {
		parent::__construct( 2010 );
	}

	/**
	 * Handle the event with the given data.
	 *
	 * @param array $data The data for the event.
	 * @param array $event_data Te optional data for the event.
	 *
	 * @return void
	 */
	public static function handle_event( array $data = array(), $event_data = array() ): void {
		$_self = new self();
		$event = $_self->get_event( $_self->event_code );

		$event['description'] = $_self->set_message( array( 'country_name' => $data['country_name'] ), $event['description'] );

		$event_data['iso2_code']        = $data['country_code'];
		$event_data['country_name'] = $data['country_name'];
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
		return sprintf( __( $message, 'really-simple-ssl' ), $args['country_name'] );
	}
}
