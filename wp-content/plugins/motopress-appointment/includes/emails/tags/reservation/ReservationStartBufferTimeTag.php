<?php

namespace MotoPress\Appointment\Emails\Tags\Reservation;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
class ReservationStartBufferTimeTag extends AbstractReservationEntityTag {

	public function getName(): string {
		return 'start_buffer_time';
	}

	protected function description(): string {
		return esc_html__( 'Start time (with buffer time)', 'motopress-appointment' );
	}

	public function getTagContent(): string {
		return mpa_format_time( $this->entity->getBufferTime()->getStartTime() );
	}
}
