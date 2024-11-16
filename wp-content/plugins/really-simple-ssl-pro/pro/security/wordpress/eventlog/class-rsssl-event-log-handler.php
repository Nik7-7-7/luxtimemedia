<?php
/**
 * The 'Rsssl_Event_Log_Handler' class is a part of the 'Really Simple Security pro' plugin,
 * which is developed by the company 'Really Simple Plugins'.
 * This class is an abstract class for handling and logging events.
 *
 * @package     RSSSL\Pro\Security\WordPress\Eventlog  // The categorization of this class.
 */

namespace RSSSL\Pro\Security\WordPress\Eventlog;

use RSSSL\Pro\Security\WordPress\Eventlog\Models\Rsssl_Event;
use RSSSL\Pro\Security\WordPress\Rsssl_Event_Log;

/**
 * Class Rsssl_Event_Log_Handler
 *
 * The abstract class for handling and logging events.
 */
abstract class Rsssl_Event_Log_Handler {
	/**
	 * The model for the event.
	 *
	 * @var Model
	 */
	public $model;

	/**
	 * The event code.
	 *
	 * @var int The event code.
	 */
	protected $event_code;

	/**
	 * Constructs a new instance of the class.
	 *
	 * @param int $event_code The event code.
	 *
	 * @return void
	 */
	public function __construct( int $event_code ) {
		$this->model      = new Rsssl_Event();
		$this->event_code = $event_code;
        // Checking if event_log exists.
        if ( ! Rsssl_Event_Log::check_table_exists() ) {
            Rsssl_Event_Log::create_event_log_table();
        }
	}

	/**
	 * Handle an event with the given data.
	 *
	 * @param array $data The data for the event.
	 *
	 * @return void
	 */
	abstract public static function handle_event( array $data = array() ): void;

	/**
	 * Sanitize the given data.
	 *
	 * @param array $data The data to sanitize.
	 *
	 * @return array The sanitized data.
	 */
	abstract protected function sanitize( array $data ): array;

	/**
	 * Log an event with the given data.
	 *
	 * @param array $data The data for the event.
	 * @param array $event_data The event data.
	 *
	 * @return void
	 */
	public function log_event( array $data = array(), array $event_data = array() ): void {
		$data = $this->sanitize( $data );
		$this->model->create(
			$this->event_code,
			$data['name'],
			$data['description'],
			$data['type'],
			$data['severity'],
			$event_data,
			$data['user_login'] ?? ''
		);
	}

	/**
	 * Retrieves event data by code.
	 *
	 * @param int $code The code of the event.
	 *
	 * @return array
	 */
	public function get_event( int $code ): array {
		$event_data = $this->model->get_event_data( $code );
		foreach ( $event_data as $key => $value ) {
			$event_data[ $key ] = __( (string) $value, 'really-simple-ssl' ); // phpcs:ignore
		}
		return $event_data;
	}

	/**
	 * Set the message with the given arguments and message string.
	 *
	 * @param array  $args The arguments for the message.
	 * @param string $message The message string.
	 *
	 * @return string The formatted message.
	 */
	abstract protected function set_message( array $args, string $message ): string;
}
