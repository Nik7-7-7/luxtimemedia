<?php

namespace MotoPress\Appointment\Emails\Tags\Service;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
class ServiceCategoriesTag extends AbstractServiceEntityTag {

	public function getName(): string {
		return 'service_categories';
	}

	protected function description(): string {
		return esc_html__( 'Service categories', 'motopress-appointment' );
	}

	public function getTagContent(): string {
		return implode( ', ', mpa_get_service_categories( $this->entity->getId(), 'name' ) );
	}
}
