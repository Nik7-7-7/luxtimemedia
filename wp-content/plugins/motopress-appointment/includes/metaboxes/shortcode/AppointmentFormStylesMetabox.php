<?php

namespace MotoPress\Appointment\Metaboxes\Shortcode;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AppointmentFormStylesMetabox extends AbstractShortcodeMetabox {

	protected function theName(): string {
		return 'appointment_form_styles_metabox';
	}

	public function getLabel(): string {
		return esc_html__( 'Styles', 'motopress-appointment' );
	}

	protected function theFields() {
		return array(
			'form_width'            => array(
				'type'              => 'text',
				'label'             => esc_html__( 'Form Width', 'motopress-appointment' ),
				'description'       => __( 'Example: 100%', 'motopress-appointment' )
			),
			'primary_color'         => array(
				'type'              => 'color-picker',
				'label'             => esc_html__( 'Primary Text Color', 'motopress-appointment' ),
				'colorpicker_type'  => 'component'
			),
			'primary_bg_color'      => array(
				'type'              => 'color-picker',
				'label'             => esc_html__( 'Primary Background Color', 'motopress-appointment' ),
				'colorpicker_type'  => 'component'
			),
			'secondary_color'       => array(
				'type'              => 'color-picker',
				'label'             => esc_html__( 'Secondary Text Color', 'motopress-appointment' ),
				'colorpicker_type'  => 'component'
			),
			'secondary_bg_color'    => array(
				'type'              => 'color-picker',
				'label'             => esc_html__( 'Secondary Background Color', 'motopress-appointment' ),
				'colorpicker_type'  => 'component'
			),
			'buttons_padding'       => array(
				'type'              => 'text',
				'label'             => esc_html__( 'Buttons Padding', 'motopress-appointment' ),
				'description'       => __( 'Example: 5px 10px', 'motopress-appointment' )
			),
		);
	}

	protected function isForShortcode( $shortcodeName ) {
		return $shortcodeName == mpa_shortcodes()->appointmentForm()->getName();
	}
}
