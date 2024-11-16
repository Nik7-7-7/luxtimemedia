<?php

namespace MotoPress\Appointment\Metaboxes\Schedule;

use MotoPress\Appointment\Metaboxes\FieldsMetabox;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class ScheduleDaysOffMetabox extends FieldsMetabox {

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	protected function theName(): string {
		return 'schedule_days_off_metabox';
	}

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function theFields() {
		return array(
			'days_off' => array(
				'type' => 'days-off',
			),
		);
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getLabel(): string {
		return esc_html__( 'Days Off', 'motopress-appointment' );
	}
}
