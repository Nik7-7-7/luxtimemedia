<?php

namespace MotoPress\Appointment\Metaboxes\Employee;

use MotoPress\Appointment\Metaboxes\FieldsMetabox;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.2
 */
class EmployeeSocialNetworksMetabox extends FieldsMetabox {

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	protected function theName(): string {
		return 'employee_social_networks_metabox';
	}

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	public function getLabel(): string {
		return esc_html__( 'Social Networks', 'motopress-appointment' );
	}

	/**
	 * @return array
	 *
	 * @since 1.2
	 */
	protected function theFields() {
		return array(
			'social_networks' => array(
				'type'         => 'attributes',
				'translatable' => true,
			),
		);
	}
}
