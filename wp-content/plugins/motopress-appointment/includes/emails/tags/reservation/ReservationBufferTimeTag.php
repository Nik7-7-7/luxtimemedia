<?php

namespace MotoPress\Appointment\Emails\Tags\Reservation;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
class ReservationBufferTimeTag extends AbstractReservationEntityTag {

	public function getName(): string {
		return 'reservation_buffer_time';
	}

	protected function description(): string {
		return esc_html__( 'Reservation time (with buffer time)', 'motopress-appointment' );
	}

	public function getTagContent(): string {
		return $this->entity->getBufferTime()->toString();
	}
}
