<?php

namespace MotoPress\Appointment\Emails\Tags\Service;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
class ServiceIdTag extends AbstractServiceEntityTag {

	public function getName(): string {
		return 'service_id';
	}

	protected function description(): string {
		return esc_html__( 'Service ID', 'motopress-appointment' );
	}

	public function getTagContent(): string {
		$id = $this->entity->getId();

		return strval( $id );
	}
}
