<?php

namespace MotoPress\Appointment\Emails\Tags\Reservation;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
class ReservationEndTimeTag extends AbstractReservationEntityTag {

	public function getName(): string {
		return 'end_time';
	}

	protected function description(): string {
		return esc_html__( 'End time', 'motopress-appointment' );
	}

	public function getTagContent(): string {
		return mpa_format_time( $this->entity->getServiceTime()->getEndTime() );
	}
}
