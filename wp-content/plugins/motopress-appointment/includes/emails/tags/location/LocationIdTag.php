<?php

namespace MotoPress\Appointment\Emails\Tags\Location;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
class LocationIdTag extends AbstractLocationEntityTag {

	public function getName(): string {
		return 'location_id';
	}

	protected function description(): string {
		return esc_html__( 'Location ID', 'motopress-appointment' );
	}

	public function getTagContent(): string {
		$id = $this->entity->getId();

		return strval( $id );
	}
}
