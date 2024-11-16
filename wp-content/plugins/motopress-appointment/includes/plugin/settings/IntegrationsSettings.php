<?php

namespace MotoPress\Appointment\Plugin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * @since 1.10.0
 */
trait IntegrationsSettings {

	/**
	 * @since 1.10.0
	 */
	public function getGoogleCalendarClientId(): string {
		return get_option( 'mpa_google_calendar_client_id', '' );
	}

	/**
	 * @since 1.10.0
	 */
	public function getGoogleCalendarClientSecret(): string {
		return get_option( 'mpa_google_calendar_client_secret', '' );
	}
}
