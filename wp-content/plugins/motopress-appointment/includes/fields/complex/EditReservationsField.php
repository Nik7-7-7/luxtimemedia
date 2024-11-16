<?php

declare(strict_types=1);

namespace MotoPress\Appointment\Fields\Complex;

use MotoPress\Appointment\Entities\Reservation;
use MotoPress\Appointment\Fields\AbstractField;
use MotoPress\Appointment\Services\BookingService;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EditReservationsField extends AbstractField {

	const TYPE = 'edit-reservations';

	/**
	 * @var Reservation[]
	 */
	protected $default = array();

	protected $bookingId = 0;

	protected function setupArgs( $args ) {
		parent::setupArgs( $args );

		if ( ! $this->bookingId ) {
			$this->bookingId = (int) get_the_ID();
		}
	}

	protected function mapFields(): array {
		return parent::mapFields() + array(
			'booking_id' => 'bookingId',
		);
	}

	/**
	 * @param mixed $value
	 * @return Reservation[]
	 */
	protected function validateValue( $value ): array {
		if ( empty( $value ) || ! is_array( $value ) ) {
			return $this->default;
		}

		$reservations   = array();
		$bookingService = new BookingService();

		foreach ( $value as $rawReservation ) {
			$reservation = $bookingService->createReservation( $rawReservation );

			if ( ! is_null( $reservation ) && ! is_wp_error( $reservation ) ) {
				$reservation->setBookingId( $this->bookingId );

				$reservations[] = $reservation;
			}
		}

		return $reservations;
	}

	public function renderInput(): string {
		$reservations = mpa_get_reservations( $this->bookingId );

		return mpa_render_template( 'private/fields/edit-reservations-field.php', array( 'reservations' => $reservations ) );
	}
}
