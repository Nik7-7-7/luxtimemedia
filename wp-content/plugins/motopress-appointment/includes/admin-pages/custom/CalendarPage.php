<?php

namespace MotoPress\Appointment\AdminPages\Custom;

use MotoPress\Appointment\Handlers\SecurityHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CalendarPage extends AbstractCustomPage {

	protected function enqueueScripts() {
		mpapp()->assets()->enqueueBundle( 'mpa-calendar-page' );
		$this->localizeScripts();
	}

	/**
	 * @access protected
	 */
	public function display() {
		echo '<div class="wrap">';
			echo '<div id="app"></div>';
		echo '</div>';
	}

	/**
	 * @return string
	 */
	protected function getPageTitle() {
		return esc_html__( 'Calendar', 'motopress-appointment' );
	}

	/**
	 * @return string
	 */
	protected function getMenuTitle() {
		return esc_html__( 'Calendar', 'motopress-appointment' );
	}

	protected function localizeScripts() {
		mpapp()->assets()->addLocalizeData(
			'mpa-calendar-page',
			'restAPI',
			[
				'root'  => esc_url_raw( rest_url() ),
				'nonce' => wp_create_nonce( 'wp_rest' ),
			]
		);

		mpapp()->assets()->addLocalizeData(
			'mpa-calendar-page',
			'urls',
			array(
				'admin' => esc_url_raw( admin_url() ),
			)
		);

		mpapp()->assets()->addLocalizeData(
			'mpa-calendar-page',
			'settings',
			array(
				'start_of_week' => mpapp()->settings()->getFirstDayOfWeek(),
				'time_format'   => mpapp()->settings()->getTimeFormat(),
				'timezone'      => mpapp()->settings()->getTimezone(),
			)
		);
		mpapp()->assets()->addLocalizeData(
			'mpa-calendar-page',
			'permissions',
			array(
				'edit_others_mpa_bookings' => SecurityHandler::isUserCanEditOthersBookings() ?? '',
			)
		);
	}
}
