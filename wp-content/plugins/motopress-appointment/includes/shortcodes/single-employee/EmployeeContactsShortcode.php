<?php

namespace MotoPress\Appointment\Shortcodes\SingleEmployee;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.2
 */
class EmployeeContactsShortcode extends AbstractSingleEmployeeShortcode {

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	public function getName() {
		return mpa_prefix( 'employee_contacts' );
	}

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	public function getLabel() {
		return esc_html__( 'Employee Contact Information', 'motopress-appointment' );
	}

	/**
	 * @return string|string[]
	 *
	 * @since 1.2
	 */
	public function getTemplate() {
		return array(
			'employee/attributes.php',
			'post/attributes.php',
		);
	}

	/**
	 * @param array $args
	 * @return array
	 *
	 * @since 1.2
	 */
	protected function filterTemplateArgs( $args ) {
		return array(
			'attributes'           => 'contacts',
			'attributes_separator' => '',
			'class'                => 'mpa-employee-contacts',
		) + $args;
	}
}
