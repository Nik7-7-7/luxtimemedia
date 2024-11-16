<?php

declare(strict_types=1);

namespace MotoPress\Appointment\Metaboxes\Service;

use MotoPress\Appointment\Metaboxes\FieldsMetabox;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.13.0
 */
class ServiceNoticesMetabox extends FieldsMetabox {

	protected function theName(): string {
		return 'service_notices_metabox';
	}

	/**
	 * @return array
	 */
	protected function theFields() {
		return array(
			'notification_notice_1' => array(
				'type'  => 'textarea',
				// Translators: %d: "Notice 1", "Notice 2" etc.
				'label' => sprintf( esc_html__( 'Notification notice %d', 'motopress-appointment' ), 1 ),
				'rows'  => 2,
				'size'  => 'large',
			),
			'notification_notice_2' => array(
				'type'  => 'textarea',
				// Translators: %d: "Notice 1", "Notice 2" etc.
				'label' => sprintf( esc_html__( 'Notification notice %d', 'motopress-appointment' ), 2 ),
				'rows'  => 2,
				'size'  => 'large',
			),
		);
	}

	public function getLabel(): string {
		return esc_html__( 'Notification Notices', 'motopress-appointment' );
	}
}
