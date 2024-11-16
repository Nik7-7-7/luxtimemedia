<?php

namespace MotoPress\Appointment\AdminPages\Custom;

use MotoPress\Appointment\Handlers\SecurityHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.21.0
 */
class AnalyticsPage extends AbstractCustomPage {

	protected function enqueueScripts() {
		mpa_assets()->enqueueBundle( 'mpa-analytics-page' );
		$this->localizeScripts();
	}

	public function display() {
		echo '<div class="wrap">';
			echo '<div id="app"></div>';
		echo '</div>';
	}

	protected function getPageTitle() {
		return esc_html__( 'Analytics', 'motopress-appointment' );
	}

	protected function getMenuTitle() {
		return esc_html__( 'Analytics', 'motopress-appointment' );
	}

	protected function localizeScripts() {
		mpapp()->assets()->addLocalizeData(
			'mpa-analytics-page',
			'restAPI',
			[
				'root'  => esc_url_raw( rest_url() ),
				'nonce' => wp_create_nonce( 'wp_rest' ),
			]
		);

		mpapp()->assets()->addLocalizeData(
			'mpa-analytics-page',
			'urls',
			array(
				'admin' => esc_url_raw( admin_url() ),
			)
		);

		// [Gateway id => gateway public name, ...]
		mpapp()->assets()->addLocalizeData(
			'mpa-analytics-page',
			'gateways',
			array_map( function ( $gateway ) {
				return $gateway->getPublicName();
			}, mpapp()->payments()->getAll() )
		);

		// todo: better pass only needed data
		mpapp()->assets()->addLocalizeData(
			'mpa-analytics-page',
			'settings',
			mpapp()->settings()->getGeneralSettings()
		);
		mpapp()->assets()->addLocalizeData(
			'mpa-analytics-page',
			'permissions',
			array(
				'edit_others_mpa_bookings' => SecurityHandler::isUserCanEditOthersBookings() ?? '',
			)
		);
	}
}
