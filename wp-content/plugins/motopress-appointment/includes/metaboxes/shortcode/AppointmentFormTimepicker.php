<?php

namespace MotoPress\Appointment\Metaboxes\Shortcode;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.5.0
 */
class AppointmentFormTimepicker extends AbstractShortcodeMetabox {

	/**
	 * @since 1.5.0
	 *
	 * @return string
	 */
	protected function theName(): string {
		return 'appointment_form_timepicker_metabox';
	}

	/**
	 * @since 1.5.0
	 *
	 * @return string
	 */
	public function getLabel(): string {
		return esc_html__( 'Timepicker', 'motopress-appointment' );
	}

	/**
	 * @since 1.5.0
	 *
	 * @return array
	 */
	protected function theFields() {
		return array(
			'timepicker_columns'       => array(
				'type'    => 'number',
				'label'   => esc_html__( 'Columns Count', 'motopress-appointment' ),
				'min'     => 1,
				'max'     => 5,
				'default' => 3,
				'size'    => 'small',
			),
			'show_timepicker_end_time' => array(
				'type'    => 'checkbox',
				'label'   => esc_html__( 'Show End Time', 'motopress-appointment' ),
				'label2'  => esc_html__( 'Show the time when the appointment ends.', 'motopress-appointment' ),
				'default' => false,
			),
		);
	}

	/**
	 * @since 1.5.0
	 *
	 * @param string $shortcodeName
	 * @return bool
	 */
	protected function isForShortcode( $shortcodeName ) {
		return $shortcodeName == mpa_shortcodes()->appointmentForm()->getName();
	}
}
