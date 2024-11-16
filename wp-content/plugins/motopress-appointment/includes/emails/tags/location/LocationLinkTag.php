<?php

namespace MotoPress\Appointment\Emails\Tags\Location;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
class LocationLinkTag extends AbstractLocationEntityTag {

	public function getName(): string {
		return 'location_link';
	}

	protected function description(): string {
		return esc_html__( 'Location link', 'motopress-appointment' );
	}

	public function getTagContent(): string {
		$id = $this->entity->getId();

		return sprintf( '<a href="%s">%s</a>', get_the_permalink( $id ), get_the_title( $id ) );
	}
}
