<?php

namespace MotoPress\Appointment\Metaboxes\Employee;

use MotoPress\Appointment\Metaboxes\FieldsMetabox;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.2
 */
class EmployeeAdditionalInfoMetabox extends FieldsMetabox {

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	protected function theName(): string {
		return 'employee_additional_info_metabox';
	}

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	public function getLabel(): string {
		return esc_html__( 'Additional Information', 'motopress-appointment' );
	}

	/**
	 * @return array
	 *
	 * @since 1.2
	 */
	protected function theFields() {
		return array(
			'additional_info' => array(
				'type'         => 'attributes',
				'translatable' => true,
			),
		);
	}
}
