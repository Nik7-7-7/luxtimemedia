<?php

namespace MotoPress\Appointment\Metaboxes\Schedule;

use MotoPress\Appointment\Metaboxes\FieldsMetabox;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class ScheduleCustomWorkdaysMetabox extends FieldsMetabox {

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	protected function theName(): string {
		return 'schedule_custom_workdays_metabox';
	}

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function theFields() {
		return array(
			'custom_workdays' => array(
				'type' => 'custom-workdays',
			),
		);
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getLabel(): string {
		return esc_html__( 'Custom Working Days', 'motopress-appointment' );
	}
}
