<?php

declare(strict_types=1);

namespace MotoPress\Appointment\Helpers;

use MotoPress\Appointment\Entities\Reservation;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 2.0.0
 */
class AvailabilityHelper {
	/**
	 * @param Reservation[] $reservedSlots
	 */
	public static function calcFreeCapacityForSlot( Reservation $newSlot, array $reservedSlots ): int {
		$service = $newSlot->getService();

		if ( is_null( $service ) ) {
			return 0;
		}

		$reservedSlots = ReservationHelper::filterNonIntersectingPeriods(
			$reservedSlots,
			$newSlot->getBufferTime()
		);

		$employeeId = $newSlot->getEmployeeId();

		$freeCapacity = $service->getMaxCapacity( $employeeId );

		if ( ! $service->isGroupService() ) {
			if ( ! empty( $reservedSlots ) ) {
				// An employee cannot perform two [intersecting] tasks at the
				// same time
				$freeCapacity = 0;
			}

		} else {
			foreach ( $reservedSlots as $reservedSlot ) {
				if (
					// Can't perform two different services at the same time
					$reservedSlot->getServiceId() != $service->getId()
					// Can't be in two places at the same time
					|| $reservedSlot->getLocationId() != $newSlot->getLocationId()
					// Can't intersect with a busy period
					|| ! $newSlot->getServiceTime()->isEqualTo( $reservedSlot->getServiceTime() )
				) {
					// New time slot is not available
					$freeCapacity = 0;

					break;

				} else {
					// Found the same period
					$freeCapacity -= $reservedSlot->getCapacity();
				}
			}
		}

		// Don't allow less than min capacity
		if ( $freeCapacity < $service->getMinCapacity( $employeeId ) ) {
			$freeCapacity = 0;
		}

		return $freeCapacity;
	}

	public static function isSlotAvailableForBooking( Reservation $reservation ): bool {
		$reservedSlots = ReservationHelper::findReservedSlotsByReservation( $reservation );

		$freeCapacity = static::calcFreeCapacityForSlot( $reservation, $reservedSlots );

		return $freeCapacity >= $reservation->getCapacity();
	}

	/**
	 * Check are time slots available for each booking reservation if we do not
	 * take into account its own reservations (maybe some other booking was made
	 * before for the same time slots). We can check service availability for
	 * booking during its modification or before booking completion.
	 *
	 * @param Reservation[] $reservations
	 */
	public static function isSlotsAvailableForBooking( array $reservations ): bool {
		foreach ( $reservations as $reservation ) {
			if ( ! static::isSlotAvailableForBooking( $reservation ) ) {
				return false;
			}
		}

		return true;
	}
}
