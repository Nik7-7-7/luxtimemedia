<?php

namespace MotoPress\Appointment\Emails\Tags\Service;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
class ServiceNameTag extends AbstractServiceEntityTag {

	public function getName(): string {
		return 'service_name';
	}

	protected function description(): string {
		return esc_html__( 'Service name', 'motopress-appointment' );
	}

	public function getTagContent(): string {
		return $this->entity->getTitle();
	}
}
