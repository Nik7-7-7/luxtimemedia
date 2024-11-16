<?php

namespace MotoPress\Appointment\Metaboxes\Schedule;

use MotoPress\Appointment\Metaboxes\FieldsMetabox;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class ScheduleTimetableMetabox extends FieldsMetabox {

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	protected function theName(): string {
		return 'schedule_timetable_metabox';
	}

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function theFields() {
		return array(
			'timetable' => array(
				'type' => 'timetable',
			),
		);
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getLabel(): string {
		return esc_html__( 'Timetable', 'motopress-appointment' );
	}
}
