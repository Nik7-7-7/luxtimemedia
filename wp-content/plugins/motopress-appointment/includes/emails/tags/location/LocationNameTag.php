<?php

namespace MotoPress\Appointment\Emails\Tags\Location;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
class LocationNameTag extends AbstractLocationEntityTag {

	public function getName(): string {
		return 'location_name';
	}

	protected function description(): string {
		return esc_html__( 'Location name', 'motopress-appointment' );
	}

	public function getTagContent(): string {
		return $this->entity->getName();
	}
}
