<?php

declare(strict_types=1);

namespace MotoPress\Appointment\Helpers;

use MotoPress\Appointment\Entities\Reservation;
use MotoPress\Appointment\Entities\Service;
use MotoPress\Appointment\Structures\TimePeriod;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 2.0.0
 */
abstract class ReservationHelper {
	/**
	 * @param Reservation[] $reservations
	 * @return Reservation[]
	 */
	public static function filterNonIntersectingPeriods( array $reservations, TimePeriod $timePeriod ): array {
		return array_filter(
			$reservations,
			function ( Reservation $reservation ) use ( $timePeriod ) {
				return $reservation->getBufferTime()->intersectsWith( $timePeriod );
			}
		);
	}

	/**
	 * @return Reservation[]
	 */
	public static function findReservedSlotsByReservation( Reservation $reservation ): array {
		return mpapp()->repositories()->reservation()->findAllBlocking(
			[
				// Skip parameters 'service_id' and 'location_id'. We'll need different
				// services and locations in AvailabilityHelper::calcFreeCapacityForSlot()
				'employee_id'    => $reservation->getEmployeeId(),
				'from_date'      => $reservation->getDate(),
				'to_date'        => $reservation->getDate(),
				'booking_not_in' => $reservation->getBookingId(),
			]
		);
	}

	/**
	 * @param Reservation|null $reservation
	 * @return int[]
	 */
	public static function getCapacityOptions( Service $service, int $employeeId = 0, $reservation = null ): array {
		$minCapacity = $service->getMinCapacity( $employeeId );
		$maxCapacity = $service->getMaxCapacity( $employeeId );

		if ( ! is_null( $reservation ) ) {
			$reservedSlots = static::findReservedSlotsByReservation( $reservation );

			$maxCapacity = AvailabilityHelper::calcFreeCapacityForSlot( $reservation, $reservedSlots );
		}

		return range( $minCapacity, $maxCapacity );
	}
}
