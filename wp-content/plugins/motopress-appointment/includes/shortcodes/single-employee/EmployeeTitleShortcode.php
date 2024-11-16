<?php

namespace MotoPress\Appointment\Shortcodes\SingleEmployee;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.2
 */
class EmployeeTitleShortcode extends AbstractSingleEmployeeShortcode {

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	public function getName() {
		return mpa_prefix( 'employee_title' );
	}

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	public function getLabel() {
		return esc_html__( 'Employee Title', 'motopress-appointment' );
	}

	/**
	 * @return string|string[]
	 *
	 * @since 1.2
	 */
	public function getTemplate() {
		return array(
			'employee/title.php',
			'post/title.php',
		);
	}
}
