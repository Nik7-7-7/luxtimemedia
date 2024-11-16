<?php

namespace MotoPress\Appointment\Emails\Tags\Reservation;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.19.0
 */
class ReservationClientsNumberTag extends AbstractReservationEntityTag {

	public function getName(): string {
		return 'reservation_clients_number';
	}

	protected function description(): string {
		return esc_html__( 'Number of clients', 'motopress-appointment' );
	}

	public function getTagContent(): string {
		return $this->entity->getCapacity();
	}
}