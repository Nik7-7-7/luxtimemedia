<?php

namespace MotoPress\Appointment\Emails\Tags\Location;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
class LocationDescriptionTag extends AbstractLocationEntityTag {

	public function getName(): string {
		return 'location_description';
	}

	protected function description(): string {
		return esc_html__( 'Location description', 'motopress-appointment' );
	}

	public function getTagContent(): string {
		return $this->entity->getInfo();
	}
}
