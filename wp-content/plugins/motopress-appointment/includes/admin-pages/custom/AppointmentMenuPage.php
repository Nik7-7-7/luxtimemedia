<?php

namespace MotoPress\Appointment\AdminPages\Custom;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class AppointmentMenuPage extends AbstractCustomPage {

	/**
	 * @access protected
	 *
	 * @since 1.0
	 */
	public function display() {
		// It's just a parent menu. No HTML required here
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	protected function getPageTitle() {
		return esc_html__( 'Appointments', 'motopress-appointment' );
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	protected function getMenuTitle() {
		return esc_html__( 'Appointments', 'motopress-appointment' );
	}
}
