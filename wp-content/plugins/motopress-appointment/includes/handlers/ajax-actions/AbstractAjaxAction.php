<?php

namespace MotoPress\Appointment\Handlers\AjaxActions;

use Throwable;
use Exception;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Any action must not contain any business logic! It just validate request data,
 * directs validated request to the Helpers, gets result and send response.
 */
abstract class AbstractAjaxAction {

	const REQUEST_DATA_WP_NONCE = 'wp_nonce';

	/**
	 * Action name must have prefix: mpa_
	 */
	abstract public static function getAjaxActionName(): string;

	public static function isActionForLoggedInUser(): bool {
		return true;
	}

	public static function isActionForGuestUser(): bool {
		return true;
	}

	/**
	 * @throws Exception when request data contains wrong data
	 */
	protected static function getIntegerFromRequest( string $requestDataName, bool $isRequired = false, int $defaultValue = 0 ) {

		$result = $defaultValue;

		// phpcs:ignore
		if ( isset( $_REQUEST[ $requestDataName ] ) && '' !== $_REQUEST[ $requestDataName ] ) {

			// phpcs:ignore
			$result = intval( wp_unslash( $_REQUEST[ $requestDataName ] ) );

		} elseif ( $isRequired ) {
			throw new Exception( 'Required integer parameter ' . $requestDataName . ' is missing in request.' );
		}

		return $result;
	}

	/**
	 * @throws Exception when request data contains wrong data
	 */
	protected static function getStringFromRequest( string $requestDataName, bool $isRequired = false, string $defaultValue = '' ) {

		$result = $defaultValue;

		// phpcs:ignore
		if ( ! empty( $_REQUEST[ $requestDataName ] ) ) {

			// phpcs:ignore
			$result = sanitize_text_field( wp_unslash( $_REQUEST[ $requestDataName ] ) );

		} elseif ( $isRequired ) {
			throw new Exception( 'Required string parameter ' . $requestDataName . ' is missing in request.' );
		}

		return $result;
	}

	/**
	 * Date must be in string Y-m-d fromat
	 *
	 * @return DateTime|null
	 * @throws Exception when request data contains wrong data
	 */
	protected static function getDateFromRequest( string $requestDataName, bool $isRequired = false, $defaultValue = null ) {

		$result = $defaultValue;

		// phpcs:ignore
		if ( ! empty( $_REQUEST[ $requestDataName ] ) ) {

			// phpcs:ignore
			$stringData = sanitize_text_field( wp_unslash( $_REQUEST[ $requestDataName ] ) );
			$result     = \DateTime::createFromFormat( 'Y-m-d', $stringData );

			if ( ! $result instanceof \DateTime ) {

				throw new Exception( 'Parameter ' . $requestDataName . ' must be a date in Y-m-d string format but (' . $stringData . ') was given.' );
			}
		} elseif ( $isRequired ) {
			throw new Exception( 'Required DateTime parameter ' . $requestDataName . ' is missing in request.' );
		}

		return $result;
	}

	/**
	 * @throws Exception when request data contains wrong data
	 */
	protected static function getBooleanFromRequest( string $requestDataName, bool $isRequired = false, bool $defaultValue = false ) {

		$result = $defaultValue;

		// phpcs:ignore
		if ( ! empty( $_REQUEST[ $requestDataName ] ) ) {

            // phpcs:ignore
			$result = rest_sanitize_boolean( wp_unslash( $_REQUEST[ $requestDataName ] ) );

		} elseif ( $isRequired ) {
			throw new Exception( 'Required boolean parameter ' . $requestDataName . ' is missing in request.' );
		}

		return $result;
	}

	/**
	 * Each ajax action must overwrite this method to get request data
	 * but first of all must call parent::getValidatedRequestData()
	 * @throws Exception when validation of request parameters failed
	 */
	protected static function getValidatedRequestData(): array {

		$requestParameters = array();

		$wpNonce = static::getStringFromRequest( static::REQUEST_DATA_WP_NONCE );

		if ( ! wp_verify_nonce( $wpNonce, static::getAjaxActionName() ) ) {

			throw new Exception(
				__( 'Request does not pass security verification. Please refresh the page and try one more time.', 'motopress-appointment' )
			);
		}

		$requestParameters[ static::REQUEST_DATA_WP_NONCE ] = $wpNonce;

		return $requestParameters;
	}

	final public static function processAjaxRequest() {

		$requestData = array();

		try {

			$requestData = static::getValidatedRequestData();

		} catch ( Throwable $e ) {

			// phpcs:ignore
			error_log( $e );
			wp_send_json_error( array( 'errorMessage' => $e->getMessage() ), 400 );
		}

		try {

			static::doAction( $requestData );

		} catch ( Throwable $e ) {

			// phpcs:ignore
			error_log( $e );
			wp_send_json_error( array( 'errorMessage' => $e->getMessage() ), 500 );
		}
	}

	/**
	 * @throws Exception when action processing failed.
	 */
	abstract protected static function doAction( array $requestData );
}
