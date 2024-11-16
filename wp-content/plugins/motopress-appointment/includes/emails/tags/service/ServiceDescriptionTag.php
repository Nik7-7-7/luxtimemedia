<?php

namespace MotoPress\Appointment\Emails\Tags\Service;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
class ServiceDescriptionTag extends AbstractServiceEntityTag {

	public function getName(): string {
		return 'service_description';
	}

	protected function description(): string {
		return esc_html__( 'Service description', 'motopress-appointment' );
	}

	public function getTagContent(): string {
		return $this->entity->getDescription();
	}
}
