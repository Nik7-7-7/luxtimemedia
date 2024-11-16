<?php
/**
 * Trait Rsssl_Api_Toolbox
 *
 * This is a collection of tools for using the API and datatables it generates filters, validations and output
 * this saves time and makes the code more readable. Also, it helps with the consistency of the code.
 *
 * @package Really-Simple-Ssl
 */

namespace RSSSL\Pro\Security\WordPress\Traits;

use DateTime;
use Exception;

/**
 * Trait Rsssl_Api_Toolbox
 *
 * This is a collection of tools for using the API and datatables it generates filters, validations and output
 * this saves time and makes the code more readable. Also, it helps with the consistency of the code.
 *
 * @package RSSSL\Pro\Security\WordPress\Traits
 * @since 7.2
 *
 * @author Really Simple Security
 * @see https://really-simple-ssl.com
 */
trait Rsssl_Api_Toolbox {

	/**
	 * This function is used to generate a JSON response.
	 *
	 * @param  bool   $success  The success status of the response.
	 * @param  string $message  The message to be included in the response.
	 * @param  mixed  $last_query  (optional) The last query to be included in the response. Defaults to null.
	 * @param  mixed  $errors  (optional) The errors to be included in the response. Defaults to an empty array.
	 *
	 * @return array              The generated JSON response as an array.
	 */
	public function json_response( bool $success, string $message, $last_query = null, $errors = null ): array {
		// Base response.
		$response = array(
			'success' => $success,
			'message' => $message,
		);

		// Add the last query to the response if it is set and WP_DEBUG is true.
		if ( ! is_null( $last_query ) && true === WP_DEBUG ) {
			$response['query'] = $last_query;
		}

		if ( ! empty( $errors ) ) {
			$response['errors'] = $errors;
		}

		return $response;
	}

	/**
	 * This function is used to validate if all the keys exist in the data array.
	 *
	 * @param  array  $data  The data array to validate.
	 * @param  string ...$keys  The keys to validate.
	 *
	 * @return bool Returns true if all the keys exist in the data array, otherwise false.
	 */
	private function validate_keys( array $data, ...$keys ): bool {
		foreach ( $keys as $key ) {
			if ( ! array_key_exists( $key, $data ) && ! empty( $data[ $key ] ) ) {
				return false;
			}
		}
		return true;
	}

	/** Validates the list type.
	 *
	 * @param string $list_type the list Type that needs to be checked.
	 *
	 * @return bool
	 */
	private function is_valid_list_type( string $list_type ): bool {
		return in_array( $list_type, self::LIST_TYPES, true );
	}

	/**
	 * This function is used to handle the exception.
	 *
	 * @param Exception $e The exception to use.
	 *
	 * @return array
	 */
	private function handle_exception( Exception $e ): array {
		// We return an error with the message.
		return $this->json_response( false, __( $e->getMessage(), 'really-simple-ssl' ) );
	}

	/**
	 * Returns an invalid list type response.
	 *
	 * @return array
	 */
	public function invalid_list_type_response(): array {
		return $this->json_response( false, __( 'Invalid list type.', 'really-simple-ssl' ) );
	}

	/**
	 * Get the time left until the max duration is reached.
	 *
	 * @param string $created The creation time.
	 * @param string $attempt The attempt time.
	 * @param int $max_duration The maximum duration in minutes.
	 *
	 * @return int The time left in seconds.
	 * @throws Exception When the time cannot be calculated.
	 */
	public function get_time_left($last_attempt ) {
		$max_duration = (int)rsssl_get_option('404_blocking_lockout_duration', 30);
        $current_time = time();
        $duration_seconds = $max_duration * 60;
        $end_time = $last_attempt + $duration_seconds;
        if ($end_time < $current_time) {
            return 0;
        }
        $remaining_seconds = $end_time - $current_time;
        return gmdate("H:i:s", $remaining_seconds);
	}
}
