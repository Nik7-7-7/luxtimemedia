<?php

namespace MotoPress\Appointment\Plugin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait MiscSettings {

	public function getFormCalendarTheme(): string {
		return get_option( 'mpa_form_calendar_theme', '' );
	}

	public function getFormStylesSettings(): array {
		return get_option( 'mpa_form_styles', array() );
	}
}
