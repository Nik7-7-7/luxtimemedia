<?php

namespace MotoPress\Appointment\Emails\Tags\Location;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
class LocationCategoriesTag extends AbstractLocationEntityTag {

	public function getName(): string {
		return 'location_categories';
	}

	protected function description(): string {
		return esc_html__( 'Location categories', 'motopress-appointment' );
	}

	public function getTagContent(): string {
		$id = $this->entity->getId();

		return implode( ', ', mpa_get_location_categories( $id, 'name' ) );
	}
}
