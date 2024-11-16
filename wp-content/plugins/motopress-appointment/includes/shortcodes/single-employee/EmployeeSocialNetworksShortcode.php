<?php

namespace MotoPress\Appointment\Shortcodes\SingleEmployee;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.2
 */
class EmployeeSocialNetworksShortcode extends AbstractSingleEmployeeShortcode {

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	public function getName() {
		return mpa_prefix( 'employee_social_networks' );
	}

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	public function getLabel() {
		return esc_html__( 'Employee Social Networks', 'motopress-appointment' );
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
			'attributes'           => 'socialNetworks',
			'attributes_separator' => '',
			'class'                => 'mpa-employee-social-networks',
		) + $args;
	}
}
