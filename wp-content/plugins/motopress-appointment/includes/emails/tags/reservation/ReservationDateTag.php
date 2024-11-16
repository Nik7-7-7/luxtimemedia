<?php

namespace MotoPress\Appointment\Emails\Tags\Reservation;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
class ReservationDateTag extends AbstractReservationEntityTag {

	public function getName(): string {
		return 'reservation_date';
	}

	protected function description(): string {
		return esc_html__( 'Reservation date', 'motopress-appointment' );
	}

	public function getTagContent(): string {
		return mpa_format_date( $this->entity->getDate() );
	}
}
