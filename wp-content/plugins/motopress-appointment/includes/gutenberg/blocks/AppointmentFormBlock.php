<?php

namespace MotoPress\Appointment\Gutenberg\Blocks;

class AppointmentFormBlock extends AbstractBlock {

	protected function getBlockName(): string {
		return 'motopress-appointment/appointment-form';
	}

	protected function getAttributes(): array {
		return array(
			'form_title'               => array(
				'type' => 'string',
			),
			'show_category'            => array(
				'type'    => 'boolean',
				'default' => 'true',
			),
			'show_service'             => array(
				'type'    => 'boolean',
				'default' => 'true',
			),
			'show_location'            => array(
				'type'    => 'boolean',
				'default' => 'true',
			),
			'show_employee'            => array(
				'type'    => 'boolean',
				'default' => 'true',
			),
			'label_category'           => array(
				'type' => 'string',
			),
			'label_service'            => array(
				'type' => 'string',
			),
			'label_location'           => array(
				'type' => 'string',
			),
			'label_employee'           => array(
				'type' => 'string',
			),
			'label_unselected'         => array(
				'type' => 'string',
			),
			'label_option'             => array(
				'type' => 'string',
			),
			'default_category'         => array(
				'type' => 'string',
			),
			'default_service'          => array(
				'type' => 'string',
			),
			'default_location'         => array(
				'type' => 'string',
			),
			'default_employee'         => array(
				'type' => 'string',
			),
			'timepicker_columns'       => array(
				'type'    => 'number',
				'default' => '3',
			),
			'show_timepicker_end_time' => array(
				'type'    => 'boolean',
				'default' => 'false',
			),
			'show_add_to_calendar'     => array(
				'type'    => 'boolean',
				'default' => 'true',
			),
			'form_width'               => array(
				'type' => 'string',
			),
			'primary_color'            => array(
				'type' => 'string',
			),
			'primary_bg_color'         => array(
				'type' => 'string',
			),
			'secondary_color'          => array(
				'type' => 'string',
			),
			'secondary_bg_color'       => array(
				'type' => 'string',
			),
			'buttons_padding'          => array(
				'type' => 'string',
			),
		);
	}

	protected function renderCallback( $attributes, $content ): string {
		return mpa_shortcodes()->appointmentForm()->render( $attributes, $content );
	}
}
