<?php

namespace MotoPress\Appointment\Emails\Tags\Reservation;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
class ReservationStartTimeTag extends AbstractReservationEntityTag {

	public function getName(): string {
		return 'start_time';
	}

	protected function description(): string {
		return esc_html__( 'Start time', 'motopress-appointment' );
	}

	public function getTagContent(): string {
		return mpa_format_time( $this->entity->getServiceTime()->getStartTime() );
	}
}
