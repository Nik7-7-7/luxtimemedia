<?php

declare(strict_types=1);

namespace MotoPress\Appointment\Services;

use MotoPress\Appointment\Entities\Reservation;
use MotoPress\Appointment\Entities\Schedule;
use MotoPress\Appointment\Entities\Service;
use MotoPress\Appointment\Helpers\AvailabilityHelper;
use MotoPress\Appointment\Helpers\ScheduleHelper;
use MotoPress\Appointment\Structures\TimePeriod;
use DateTime;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds time slots for the Appointment Form.
 *
 * @since 1.2.1
 */
class TimeSlotService {
	/**
	 * @since 2.0.0
	 */
	const TIME_ALIGNMENT_NONE = 'none';

	/**
	 * @since 2.0.0
	 */
	const TIME_ALIGNMENT_HOUR = 'hour';

	/**
	 * @var Service
	 */
	protected $service = null;

	/**
	 * An empty array means that all locations are allowed.
	 *
	 * @since 2.0.0
	 *
	 * @var int[]
	 */
	protected $locationIn = [];

	/**
	 * @since 1.2.2
	 *
	 * @var int[]
	 */
	protected $employeeIds = [];

	/**
	 * @since 2.0.0
	 *
	 * @var Schedule[] [ Employee ID => Schedule ]
	 */
	protected $schedules = [];

	/**
	 * @since 2.0.0
	 *
	 * @var Reservation[]
	 */
	protected $cartReservations = [];

	/**
	 * @since 2.0.0
	 *
	 * @var 'none'|'hour'
	 */
	protected $timeAlignment = self::TIME_ALIGNMENT_NONE;

	/**
	 * @param int[] $locationIn <i>Optional.</i> An empty array means that all
	 *     locations are allowed.
	 */
	public function __construct( Service $service, array $locationIn = [] ) {
		$this->service = $service;
		$this->locationIn = $locationIn;
	}

	/**
	 * @since 2.0.0
	 *
	 * @param string $timeAlignment
	 */
	public function setTimeAlignment( string $timeAlignment ) {
		$this->timeAlignment = $timeAlignment;
	}

	/**
	 * @since 2.0.0
	 */
	public function addSchedule( Schedule $schedule ) {
		$employeeId = $schedule->getEmployeeId();

		$this->schedules[ $employeeId ] = $schedule;
		$this->employeeIds[] = $employeeId;
	}

	/**
	 * @since 1.4.0
	 *
	 * @param Reservation[] $cartItems
	 */
	public function blockCartItems( array $cartItems ) {
		foreach ( $cartItems as $cartItem ) {
			$cartReservation = clone $cartItem;

			// Set the maximum capacity to exclude time slot from further
			// selection. Two cart items cannot have the same time period —
			// this option is currently not supported.
			$maxCapacity = $this->service->getMaxCapacity(
				$cartReservation->getEmployeeId()
			);

			$cartReservation->setCapacity( $maxCapacity );

			$this->cartReservations[] = $cartReservation;
		}
	}

	/**
	 * @since 2.0.0
	 *
	 * @return array [
	 *     'Y-m-d' => [
	 *         Time period (string) => [
	 *             // Variations
	 *             [
	 *                 0 => Employee ID,
	 *                 1 => Location ID,
	 *                 2 => Min capacity,
	 *                 3 => Max/free capacity,
	 *             ],
	 *             ...
	 *         ]
	 *     ]
	 * ]
	 */
	public function buildTimeSlotsForPeriod( DateTime $dateFrom, DateTime $dateTo ): array {
		$reservations = mpapp()->repositories()->reservation()->findAllBlocking(
			[
				'employee_id' => $this->employeeIds,
				'from_date'   => $dateFrom,
				'to_date'     => $dateTo,
			]
		);

		$reservations = array_merge( $reservations, $this->cartReservations );

		$timeSlots = [];

		foreach ( $this->schedules as $employeeId => $schedule ) {
			$locationIds = $schedule->getLocationIds();

			if ( ! empty( $this->locationIn ) ) {
				$locationIds = array_intersect( $locationIds, $this->locationIn );
			}

			foreach ( $locationIds as $locationId ) {
				for ( $date = clone $dateFrom; $date < $dateTo; $date->modify( '+1 day' ) ) {
					$dateString = mpa_format_date( $date, 'internal' );

					$allWorkingHours = ScheduleHelper::getWorkingHoursForDate( $schedule, $date, $locationId );

					foreach ( $allWorkingHours as $workingHours ) {

						$reservedSlots = array_filter(
							$reservations,
							function ( Reservation $reservation ) use ( $employeeId, $workingHours ) {
								return $reservation->getEmployeeId() == $employeeId
									&& $reservation->getBufferTime()->intersectsWith( $workingHours );
							}
						);

						// [ Time period (string) => Free capacity (int) ]
						$workingSlots = $this->buildTimeSlots(
							clone $workingHours,
							[
								'employee_id'      => $employeeId,
								'location_id'      => $locationId,
								'search_date_from' => $dateFrom,
								'search_date_to'   => $dateTo,
								'date'             => $date,
							],
							$reservedSlots
						);

						// "Merge" slots of the day with other booking variants
						foreach ( $workingSlots as $timeString => $freeCapacity ) {
							$timeSlots[ $dateString ][ $timeString ][] = [
								$employeeId,
								$locationId,
								$this->service->getMinCapacity( $employeeId ),
								$freeCapacity
							];
						}

					} // For each time period
				} // For each day
			} // For each location
		} // For each employee

		// Sort days and periods in ascending order after searching time slots
		// for different employees and locations
		ksort( $timeSlots );

		foreach ( $timeSlots as &$daySlots ) {
			ksort( $daySlots );
		}

		unset( $daySlots );

		return $timeSlots;
	}

	/**
	 * @since 2.0.0
	 *
	 * @param array $args
	 *     @param int      $args['employee_id']
	 *     @param int      $args['location_id']
	 *     @param DateTime $args['search_date_from']
	 *     @param DateTime $args['search_date_to']
	 *     @param DateTIme $args['date']
	 * @param Reservation[] $reservedSlots
	 * @return array [ Time period (string) => Free capacity (int) ]
	 */
	private function buildTimeSlots( TimePeriod $workingHours, array $args, array $reservedSlots ): array {
		$startTime = clone max( $workingHours->getStartTime(), $args['search_date_from'] );
		$endTime   = clone min( $workingHours->getEndTime(), $args['search_date_to'] );

		// Apply buffer time
		$startTime->modify( "+{$this->service->getBufferTimeBefore()} minutes" );
		$endTime->modify( "-{$this->service->getBufferTimeAfter()} minutes" );

		// Convert time to minutes
		$dateTimeToMinutes = function ( DateTime $time ) {
			return (int) ( ( $time->getTimestamp() + $time->getOffset() ) / 60 );
		};

		$startMinutes = $dateTimeToMinutes( $startTime );
		$endMinutes   = $dateTimeToMinutes( $endTime );

		// Align time to the first valid slot
		$timeStep = $this->service->getCustomTimeStep();
		$minutesToNextValidSlot = 0;

		switch ( $this->timeAlignment ) {
			case self::TIME_ALIGNMENT_NONE:
				$scheduleStartMinutes    = $dateTimeToMinutes( $workingHours->getStartTime() );
				$minutesAfterWorkingTime = $startMinutes - $scheduleStartMinutes;
				$minutesToNextValidSlot  = $timeStep - ( $minutesAfterWorkingTime % $timeStep );

				break;

			case self::TIME_ALIGNMENT_HOUR:
				$minutesAfterLastHour   = (int) $startTime->format( 'i' );
				$minutesToNextValidSlot = $timeStep - ( $minutesAfterLastHour % $timeStep );

				if ( $minutesAfterLastHour + $minutesToNextValidSlot > 60 ) {
					$minutesToNextValidSlot = 60 - $minutesAfterLastHour; // Next hour
				}

				break;
		}

		// No need to shift the time when $minutesToNextValidSlot = $timeStep - 0 = $timeStep
		if ( $minutesToNextValidSlot != $timeStep ) {
			$startMinutes += $minutesToNextValidSlot;
		}

		// Prepare Reservation for the AvailabilityHelper methods
		$testReservation = new Reservation(
			0,
			[
				'serviceId'  => $this->service->getId(),
				'employeeId' => $args['employee_id'],
				'locationId' => $args['location_id'],
				'date'       => clone $args['date'],
			]
		);

		// Build slots
		$duration = $this->service->getDuration( $args['employee_id'] );
		$minutesAfterHour = $startMinutes % 60; // For 'hour' alignment

		// [ Time period (string) => [ Employee ID, Location ID, Free capacity ] ]
		$timeSlots = [];

		for ( $timestamp = $startMinutes; ; $timestamp += $timeStep, $minutesAfterHour += $timeStep ) {

			// Align timestamp
			if ( $minutesAfterHour == 60 ) {
				// Do nothing, just reset the counter
				$minutesAfterHour = 0;

			} elseif ( $minutesAfterHour > 60 ) {
				if ( $this->timeAlignment == self::TIME_ALIGNMENT_HOUR ) {
					$timestamp -= $minutesAfterHour % 60;
					$minutesAfterHour = 0;
				} else {
					$minutesAfterHour %= 60;
				}
			}

			// Check after realignment, not in loop header. Otherwise, the last
			// slot before the end of working hours will not be added. For
			// example: the slot "12:00 - 13:00" will be lost for the schedule
			// "09:00 - 13:00" and alignment by hour.
			if ( $timestamp + $duration > $endMinutes ) {
				// No enough time for one more slot
				break;
			}

			// New time slot
			$newSlot = new TimePeriod(
				mpa_format_minutes( $timestamp, 'internal' ),
				mpa_format_minutes( $timestamp + $duration, 'internal' )
			);

			$newSlot->setDate( $args['date'] );

			// Check availability
			$testReservation->setServiceTime( $newSlot );
			$testReservation->setBufferTime( mpa_add_buffer_time( clone $newSlot, $this->service ) );

			$freeCapacity = AvailabilityHelper::calcFreeCapacityForSlot( $testReservation, $reservedSlots );

			// Add available slot
			if ( $freeCapacity > 0 ) {
				$timeSlots[ $newSlot->toString( 'internal' ) ] = $freeCapacity;
			}
		}

		return $timeSlots;
	}

	/**
	 * @since 2.0.0
	 *
	 * @param array $args
	 *     @param int[] $args['employee_in']
	 *     @param int[] $args['location_in']
	 *     @param DateTime|null $args['date_from']
	 *     @param DateTime|null $args['date_to']
	 *     @param bool $args['since_today']
	 *     @param bool $args['skip_rules']
	 * @param Reservation[] $cartItems
	 * @return array
	 */
	public static function makeTimeSlots( Service $service, array $args, array $cartItems = [] ): array {
		$args += [
			'employee_in' => [],
			'location_in' => [],
			'date_from'   => null,
			'date_to'     => null,
			'since_today' => true,
			'skip_rules'  => false,
			'alignment'   => mpapp()->settings()->getTimeStepAlignment(),
		];
 
		// Check required fields
		if ( is_null( $args['date_from'] ) || is_null( $args['date_to'] ) ) {
			return [];
		}

		// Validate employees
		$employeeIds = $service->getEmployeeIds();

		if ( ! empty( $args['employee_in'] ) ) {
			$employeeIds = array_intersect( $employeeIds, $args['employee_in'] );
		}

		if ( empty( $employeeIds ) ) {
			return [];
		}

		// Validate dates
		$dateFrom = $args['date_from'];
		$dateTo   = $args['date_to'];

		if ( $args['since_today'] ) {
			$now = new DateTime( 'now', wp_timezone() );

			if ( $now > $dateFrom ) {
				$dateFrom = $now;
			}
		}

		if ( ! $args['skip_rules'] ) {
			$minDate = $service->getMinBookingDateTime();
			$maxDate = $service->getMaxAdvanceBookingDateTime();

			if ( ! is_null( $minDate ) && $minDate > $dateFrom ) {
				$dateFrom = $minDate;
			}

			if ( ! is_null( $maxDate ) && $maxDate < $dateTo ) {
				$dateTo = $maxDate;
			}
		}

		if ( $dateFrom > $dateTo ) {
			return [];
		}

		// Prepare TimeSlotService
		$timeSlotService = new static( $service, $args['location_in'] );

		foreach ( $employeeIds as $employeeId ) {
			$schedule = mpapp()->repositories()->schedule()->findByEmployee( $employeeId );

			if ( ! is_null( $schedule ) ) {
				$timeSlotService->addSchedule( $schedule );
			}
		}

		$timeSlotService->blockCartItems( $cartItems );
		$timeSlotService->setTimeAlignment( $args['alignment'] );

		// Build slots
		$timeSlots = $timeSlotService->buildTimeSlotsForPeriod( $dateFrom, $dateTo );

		return $timeSlots;
	}
}
