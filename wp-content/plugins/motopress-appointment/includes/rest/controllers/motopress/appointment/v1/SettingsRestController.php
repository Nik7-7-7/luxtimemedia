<?php

namespace MotoPress\Appointment\REST\Controllers\Motopress\Appointment\V1;

use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class SettingsRestController extends AbstractRestController {

	/**
	 * @since 1.0
	 */
	public function register_routes() {
		// '/motopress/appointment/v1/settings'
		register_rest_route(
			$this->getNamespace(),
			'/settings',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'getSettings' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 *
	 * @since 1.0
	 * @since 1.2.1 added the <code>flatpickr_locale</code> parameter.
	 */
	public function getSettings( $request ) {
		$settings = mpapp()->settings()->getPublicSettings();

		/**
		 * @param string $language
		 *
		 * @since 1.2.1
		 */
		$flatpickrLocale = apply_filters( 'mpa_flatpickr_l10n', mpapp()->i18n()->getCurrentLanguage() );

		// Add language data
		$settings['flatpickr_locale'] = mpa_is_flatpickr_l10n( $flatpickrLocale ) ? $flatpickrLocale : 'en';

		return rest_ensure_response( $settings );
	}
}
