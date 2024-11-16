<?php

namespace MotoPress\Appointment\Emails\Tags\Reservation;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
class ReservationPriceTag extends AbstractReservationEntityTag {

	public function getName(): string {
		return 'reservation_price';
	}

	protected function description(): string {
		return esc_html__( 'Reservation price', 'motopress-appointment' );
	}

	public function getTagContent(): string {
		return mpa_tmpl_price( $this->entity->getPrice() );
	}
}
