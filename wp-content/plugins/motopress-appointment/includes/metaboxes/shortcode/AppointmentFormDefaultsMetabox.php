<?php

namespace MotoPress\Appointment\Metaboxes\Shortcode;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AppointmentFormDefaultsMetabox extends AbstractShortcodeMetabox {

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	protected function theName(): string {
		return 'appointment_form_defaults_metabox';
	}

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	public function getLabel(): string {
		return esc_html__( 'Default Values', 'motopress-appointment' );
	}

	/**
	 * @return array
	 *
	 * @since 1.2
	 */
	protected function theFields() {

		$unselectedSlug = array( '' => esc_html__( '— Unselected —', 'motopress-appointment' ) );
		$unselectedId   = array( 0 => esc_html__( '— Unselected —', 'motopress-appointment' ) );

		return array(
			'default_category' => array(
				'type'    => 'select',
				'label'   => esc_html__( 'Service Category', 'motopress-appointment' ),
				'options' => $unselectedSlug + mpa_get_service_categories(),
				'size'    => 'regular',
			),
			'default_service'  => array(
				'type'    => 'select',
				'label'   => esc_html__( 'Service', 'motopress-appointment' ),
				'options' => $unselectedId + mpa_get_services(),
				'size'    => 'regular',
			),
			'default_location' => array(
				'type'    => 'select',
				'label'   => esc_html__( 'Location', 'motopress-appointment' ),
				'options' => $unselectedId + mpa_get_locations(),
				'size'    => 'regular',
			),
			'default_employee' => array(
				'type'    => 'select',
				'label'   => esc_html__( 'Employee', 'motopress-appointment' ),
				'options' => $unselectedId + mpa_get_employees(),
				'size'    => 'regular',
			),
		);
	}

	/**
	 * @param string $shortcodeName
	 * @return bool
	 *
	 * @since 1.2
	 */
	protected function isForShortcode( $shortcodeName ) {
		return $shortcodeName == mpa_shortcodes()->appointmentForm()->getName();
	}
}
