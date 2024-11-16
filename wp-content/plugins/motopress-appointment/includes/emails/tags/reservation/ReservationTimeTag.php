<?php

namespace MotoPress\Appointment\Emails\Tags\Reservation;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
class ReservationTimeTag extends AbstractReservationEntityTag {

	public function getName(): string {
		return 'reservation_time';
	}

	protected function description(): string {
		return esc_html__( 'Reservation time (period)', 'motopress-appointment' );
	}

	public function getTagContent(): string {
		return $this->entity->getServiceTime()->toString();
	}
}
