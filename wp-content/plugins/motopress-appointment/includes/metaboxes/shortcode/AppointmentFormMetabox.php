<?php

namespace MotoPress\Appointment\Metaboxes\Shortcode;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.2
 */
class AppointmentFormMetabox extends AbstractShortcodeMetabox {

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	protected function theName(): string {
		return 'appointment_form_metabox';
	}

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	public function getLabel(): string {
		return esc_html__( 'Appointment Form', 'motopress-appointment' );
	}

	/**
	 * @return array
	 *
	 * @since 1.2
	 */
	protected function theFields() {

		return array(
			'name'       => array(
				'type'  => 'hidden',
				'value' => mpa_shortcodes()->appointmentForm()->getName(),
			),
			'form_title' => array(
				'type'         => 'text',
				'label'        => esc_html__( 'Form Title', 'motopress-appointment' ),
				'size'         => 'regular',
				'translatable' => true,
			),
			'show_items' => array(
				'type'        => 'checklist',
				'label'       => esc_html__( 'Show Items', 'motopress-appointment' ),
				'description' => esc_html__( 'Show or hide form fields.', 'motopress-appointment' ),
				'options'     => array(
					'category' => esc_html__( 'Service Category', 'motopress-appointment' ),
					'service'  => esc_html__( 'Service', 'motopress-appointment' ),
					'location' => esc_html__( 'Location', 'motopress-appointment' ),
					'employee' => esc_html__( 'Employee', 'motopress-appointment' ),
				),
				'value'       => array( 'category', 'service', 'location', 'employee' ),
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
