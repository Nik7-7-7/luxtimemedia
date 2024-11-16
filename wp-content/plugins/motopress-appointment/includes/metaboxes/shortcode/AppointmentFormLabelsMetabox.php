<?php

namespace MotoPress\Appointment\Metaboxes\Shortcode;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AppointmentFormLabelsMetabox extends AbstractShortcodeMetabox {

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	protected function theName(): string {
		return 'appointment_form_labels_metabox';
	}

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	public function getLabel(): string {
		return esc_html__( 'Labels', 'motopress-appointment' );
	}

	/**
	 * @return array
	 *
	 * @since 1.2
	 */
	protected function theFields() {
		return array(
			'label_category'   => array(
				'type'         => 'text',
				'label'        => esc_html__( 'Service Category', 'motopress-appointment' ),
				'placeholder'  => esc_html__( 'Service Category', 'motopress-appointment' ),
				'size'         => 'regular',
				'translatable' => true,
			),
			'label_service'    => array(
				'type'         => 'text',
				'label'        => esc_html__( 'Service', 'motopress-appointment' ),
				'placeholder'  => esc_html__( 'Service', 'motopress-appointment' ),
				'size'         => 'regular',
				'translatable' => true,
			),
			'label_location'   => array(
				'type'         => 'text',
				'label'        => esc_html__( 'Location', 'motopress-appointment' ),
				'placeholder'  => esc_html__( 'Location', 'motopress-appointment' ),
				'size'         => 'regular',
				'translatable' => true,
			),
			'label_employee'   => array(
				'type'         => 'text',
				'label'        => esc_html__( 'Employee', 'motopress-appointment' ),
				'placeholder'  => esc_html__( 'Employee', 'motopress-appointment' ),
				'size'         => 'regular',
				'translatable' => true,
			),
			'label_unselected' => array(
				'type'         => 'text',
				'label'        => esc_html__( 'Unselected Service', 'motopress-appointment' ),
				'description'  => esc_html__( 'Custom label for the unselected service field.', 'motopress-appointment' ),
				'placeholder'  => esc_html__( '— Select —', 'motopress-appointment' ),
				'size'         => 'regular',
				'translatable' => true,
			),
			'label_option'     => array(
				'type'         => 'text',
				'label'        => esc_html__( 'Unselected Option', 'motopress-appointment' ),
				'description'  => esc_html__( 'Custom label for the unselected service category, location and employee fields.', 'motopress-appointment' ),
				'placeholder'  => esc_html__( '— Any —', 'motopress-appointment' ),
				'size'         => 'regular',
				'translatable' => true,
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
