<?php

namespace MotoPress\Appointment\Shortcodes\SingleEmployee;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.2
 */
class EmployeeScheduleShortcode extends AbstractSingleEmployeeShortcode {

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	public function getName() {
		return mpa_prefix( 'employee_schedule' );
	}

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	public function getLabel() {
		return esc_html__( 'Employee Schedule', 'motopress-appointment' );
	}

	/**
	 * @return string|string[]
	 *
	 * @since 1.2
	 */
	public function getTemplate() {
		return 'employee/schedule.php';
	}
}
