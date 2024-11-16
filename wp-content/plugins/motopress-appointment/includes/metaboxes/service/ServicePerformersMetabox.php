<?php

namespace MotoPress\Appointment\Metaboxes\Service;

use MotoPress\Appointment\Metaboxes\FieldsMetabox;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class ServicePerformersMetabox extends FieldsMetabox {

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	protected function theName(): string {
		return 'service_performers_metabox';
	}

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function theFields() {
		return array(
			'employees'  => array(
				'type'                 => 'checklist',
				'label'                => esc_html__( 'Employees', 'motopress-appointment' ),
				'options'              => mpa_get_employees(),
				'isAddSelectAllOption' => true,
			),
			'variations' => array(
				'type'  => 'service-variations',
				'label' => esc_html__( 'Customize Service For Employee', 'motopress-appointment' ),
			),
		);
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getLabel(): string {
		return esc_html__( 'Eligible Employees', 'motopress-appointment' );
	}
}
