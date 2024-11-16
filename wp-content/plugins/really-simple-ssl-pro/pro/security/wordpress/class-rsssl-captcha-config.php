<?php
/**
 * Namespace: RSSSL_PRO\Security\WordPress
 * Class: Rsssl_Captcha_Config
 *
 * This class provides configuration options for captcha operations.
 * It is loaded only on the admin side and if Captcha is enabled.
 * It fetches and returns config options from the database,
 * as well as fields that need to generate options in React frontend code.
 *
 * @package RSSSL\Pro\Security\WordPress
 * @since   7.3
 */

namespace RSSSL\Pro\Security\WordPress;

use RSSSL\Pro\Security\WordPress\Captcha\Rsssl_Captcha_Provider;
use RSSSL\Pro\Security\WordPress\Captcha\Rsssl_HCaptcha;
use RSSSL\Pro\Security\WordPress\Captcha\Rsssl_ReCaptcha;

/**
 * Class Rsssl_Captcha_Config
 *
 * This class provides configuration options for captcha operations.
 * Is loaded on admin only and if Captcha is enabled. It fetches returns config options from the database.
 * and also some fields that need to generate options in React frontend code.
 *
 * @package RSSSL\Pro\Security\WordPress
 */
class Rsssl_Captcha_Config {

	// init the class.

	/**
	 * Constructor method for the class.
	 *
	 * This method initializes the class and sets up a filter for the rsssl_do_action hook.
	 * The filter triggers the 'captcha_api' method when the hook is called.
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'rsssl_after_save_field', array( $this, 'can_i_use_captcha' ), 10, 3 );
		add_filter( 'rsssl_do_action', array( $this, 'verify_captcha' ), 10, 3 );
//		add_filter( 'rsssl_notices', array( $this, 'show_captcha_notices' ), 10, 1 );
	}

	/**
	 * Determines if the captcha can be used.
	 *
	 * This method is called after a field is saved and checks if the value of the field with the given field ID has changed.
	 * If the value has changed, it refreshes the captcha verification by setting 'captcha_verified' and 'captcha_fully_enabled' options to false.
	 *
	 * @param  string $field_id  The ID of the field.
	 * @param  mixed  $field_value  The new value of the field.
	 * @param  mixed  $prev_value  The previous value of the field.
	 *
	 * @return void
	 */
	public function can_i_use_captcha( string $field_id, $field_value, $prev_value ): void {
		// If the value of the field has changed, we need to refresh the captcha verification.
		// if the field values have changed, we need to refresh the captcha verification.
		if ( ( 'enabled_captcha_provider' === $field_id ) && $field_value !== $prev_value ) {
			rsssl_update_option( 'captcha_verified', false );
			rsssl_update_option( 'captcha_fully_enabled', false );
		}

		// if current value is false we disable the captcha verification in limit login attempts
		// TODO: Move this to Limit login Attempts class with the rework coming.
		if ( ( 'captcha_fully_enabled' === $field_id ) && $field_value !== $prev_value && ! (bool) $field_value ) {
			rsssl_update_option( 'limit_login_attempts_captcha', false );
		}

		// Here we check all keys and secrets if they were altered we need to refresh the captcha verification.
		$ids = array(
			'hcaptcha_site_key',
			'hcaptcha_secret_key',
			'recaptcha_site_key',
			'recaptcha_secret_key',
		);

		foreach ( $ids as $id ) {
			if ( $id === $field_id && $field_value !== $prev_value ) {
				rsssl_update_option( 'captcha_verified', false );
				rsssl_update_option( 'captcha_fully_enabled', false );
			}
		}
	}

	/**
	 * Verify the captcha response.
	 *
	 * This method checks if the user is logged in as an admin and if not, returns the response.
	 * The method then retrieves the captcha provider and validates the captcha token.
	 * If the token is valid, it updates the captcha verification option to true and sets the response's 'success' key to true.
	 * If the token is invalid, it sets the response's 'success' key to false and the 'error' key to an error message.
	 *
	 * @param  array  $response  The response array.
	 * @param  string $action  The action string.
	 * @param  array  $data  The data array containing the captcha token.
	 *
	 * @return array The modified response array.
	 */
	public function verify_captcha( array $response, string $action, array $data ): array {
		if ( ! rsssl_admin_logged_in() ) {
			return $response;
		}
		$provider = $this->get_set_provider();

		// if the provider is none, we don't need to verify the captcha.
		if ( 'none' === rsssl_get_option( 'enabled_captcha_provider' ) ) {
			rsssl_update_option( 'captcha_verified', false );
			rsssl_update_option( 'captcha_fully_enabled', false );
			return $response;
		}

		if ( 'verify_captcha' === $action ) {
			$captcha_token = $data['responseToken'];
			if ( $provider->validate( $captcha_token ) ) {
				rsssl_update_option( 'captcha_verified', true );
				rsssl_update_option( 'captcha_fully_enabled', true );

				$response['success']    = true;
				$response['set_values'] = array(
					'captcha_verified'      => rsssl_get_option( 'captcha_verified' ),
					'captcha_fully_enabled' => rsssl_get_option( 'captcha_fully_enabled' ),
				);
			} else {
				$response['success'] = false;
				rsssl_update_option( 'captcha_verified', false );
				rsssl_update_option( 'captcha_fully_enabled', false );
				$response['error'] = sprintf(
				/* translators: %s: The captcha provider name. */
					__( '%s validation failed', 'really-simple-ssl' ),
					rsssl_get_option( 'enabled_captcha_provider' )
				);
			}
		}
		return $response;
	}

	/**
	 * Get the active captcha provider.
	 *
	 * This method retrieves the enabled captcha provider from the database,
	 * and returns the corresponding class name for that provider.
	 *
	 * @return object The class name of the active captcha provider.
	 */
	public function get_set_provider(): object {
		$providers = array(
			'hcaptcha'  => new Rsssl_HCaptcha(),
			'recaptcha' => new Rsssl_ReCaptcha(),
			'none'      => new \stdClass(),
		);
		//get keys of $providers array in array
		$provider_options = array_keys( $providers );
		$provider = rsssl_get_option( 'enabled_captcha_provider' );
		if ( ! in_array( $provider, $provider_options, true ) ) {
			$provider = 'none';
			rsssl_update_option( 'enabled_captcha_provider', $provider );
		}

		return $providers[ $provider ];
	}

	/**
	 * Generates an array of captcha notices based on the verification status.
	 *
	 * This method creates a captcha notice with the provided message, type, and slug based on the
	 * verification status. The notice is then added to the $notices array with a unique key.
	 *
	 * @param  array $notices  An array of existing captcha notices.
	 *
	 * @return array An updated array of captcha notices.
	 */
	public function show_captcha_notices( array $notices ): array {
		// If the Captcha value is none or false, we need to show a notice to enable it.
		if ( 'none' !== rsssl_get_option( 'enabled_captcha_provider' ) && false === (bool) rsssl_get_option( 'captcha_fully_enabled' )) {
			$is_captcha_verified = rsssl_get_option( 'captcha_fully_enabled' );
			$notice              = $this->create_captcha_notice(
				$is_captcha_verified ? __( 'Captcha was verified', 'really-simple-ssl' ) : __( 'Captcha was not verified', 'really-simple-ssl' ),
				$is_captcha_verified ? __( 'Captcha was verified successfully and you can now enable it in the supported features.', 'really-simple-ssl' ) : __( 'Captcha was not verified successfully. Please try again.', 'really-simple-ssl' ),
				$is_captcha_verified ? 'success' : 'warning',
				$is_captcha_verified ? 'success' : 'warning'
			);
			$notices[ 'enabled_captcha_provider_captcha_' . ( $is_captcha_verified ? 'verified' : 'not_verified' ) ] = $notice;
		}
		return $notices;
	}

	/**
	 * Creates a captcha notice array.
	 *
	 * This method creates and returns an array representing a captcha notice.
	 *
	 * @param  string $title  The title of the notice.
	 * @param  string $msg  The message of the notice.
	 * @param  string $icon  The icon class for the notice.
	 * @param  string $type  The type of the notice.
	 *
	 * @return array The captcha notice array.
	 */
	private function create_captcha_notice( string $title, string $msg, string $icon, string $type ): array {
		return array(
			'callback'          => '_true_',
			'score'             => 1,
			'show_with_options' => array( 'enabled_captcha_provider' ),
			'output'            => array(
				'true' => array(
					'title'              => $title,
					'msg'                => $msg,
					'icon'               => $icon,
					'type'               => $type,
					'dismissible'        => false,
					'admin_notice'       => false,
					'plusone'            => true,
					'highlight_field_id' => 'enabled_captcha_provider',
				),
			),
		);
	}
}

new Rsssl_Captcha_Config();
