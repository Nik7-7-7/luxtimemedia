<?php

namespace MotoPress\Appointment\Entities;

use MotoPress\Appointment\Structures\TimePeriod;
use DateTime;

use const MotoPress\Appointment\ACTIVITY_WORK;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 *
 * @see \MotoPress\Appointment\Repositories\ScheduleRepository
 */
class Schedule extends AbstractEntity {

	/**
	 * @var string
	 *
	 * @since 1.0
	 */
	protected $title = '';

	/**
	 * @var int
	 *
	 * @since 1.0
	 */
	protected $employeeId = 0;

	/**
	 * @var int
	 *
	 * @since 1.0
	 */
	protected $locationId = 0;

	/**
	 * @var array [
	 *     day (string: 'sunday', 'monday', ..., 'saturday') => [
	 *         [
	 *             'time_period' => TimePeriod,
	 *             'location'    => int,
	 *             'activity'    => 'work'|'lunch'|'break',
	 *         ],
	 *         ...
	 *     ],
	 * ]
	 *
	 * @since 1.0
	 */
	protected $timetable = array();

	/**
	 * @since 1.0 DatePeriod[]
	 * @since 1.2.2 DateTime[]
	 * @var DateTime[] ['Y-m-d' => DateTime]
	 */
	protected $daysOff = array();

	/**
	 * @var array Array of [DatePeriod 'date_period', TimePeriod 'time_period'].
	 *
	 * @since 1.0
	 */
	protected $customWorkdays = array();


	public function getTitle(): string {
		return $this->title;
	}

	public function getEmployeeId(): int {
		return $this->employeeId;
	}

	public function getLocationId(): int {
		return $this->locationId;
	}

	public function getTimetable(): array {
		return $this->timetable;
	}

	public function getDaysOff(): array {
		return $this->daysOff;
	}

	public function getCustomWorkdays(): array {
		return $this->customWorkdays;
	}

	/**
	 * @since 1.0
	 *
	 * @param string|DateTime $date
	 * @return bool
	 */
	public function isDayOff( $date ) {
		if ( ! is_string( $date ) ) {
			$date = mpa_format_date( $date, 'internal' );
		}

		return array_key_exists( $date, $this->daysOff );
	}

	/**
	 * @since 1.2.2
	 *
	 * @param DateTime|string $date
	 * @return bool
	 */
	public function hasCustomWorkingHoursForDate( $date ) {
		foreach ( $this->customWorkdays as $workday ) {
			$datePeriod = $workday['date_period'];

			if ( $datePeriod->inPeriod( $date ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @since 1.2.2
	 *
	 * @param DateTime|string $date
	 * @return TimePeriod[]
	 */
	public function getCustomWorkingHoursForDate( $date ) {
		// May be equal date periods with different time frames (time before and
		// after the lunch), so get them all, instead of returning the first one
		$timePeriods = array();

		foreach ( $this->customWorkdays as $workday ) {
			$datePeriod = $workday['date_period'];

			if ( $datePeriod->inPeriod( $date ) ) {
				$timePeriods[] = clone $workday['time_period'];
			}
		}

		return $timePeriods;
	}

	/**
	 * @param int $locationId
	 * @param DateTime|string $date
	 * @return bool
	 *
	 * @since 1.0
	 */
	public function isWorkingAt( $locationId, $date = null ) {
		if ( is_null( $date ) ) {
			$locations = $this->getLocationIds();
		} else {
			if ( $this->isDayOff( $date ) ) {
				return false;
			}

			$locations = $this->getLocationIdsForDate( $date );
		}

		return in_array( $locationId, $locations );
	}

	/**
	 * Skips days without working hours.
	 *
	 * @param int $firstDay Optional. -1 by default (use settings value).
	 * @return array [Day index => TimePeriod], where day index starts from 0
	 *     for Sunday.
	 *
	 * @since 1.2
	 */
	public function getWorkingWeek( $firstDay = -1 ) {
		// Set proper first day
		$timetable = array_values( $this->timetable );
		$timetable = mpa_shift_days_array( $timetable );

		// Build work week
		$workWeek = array();

		foreach ( array_values( $this->timetable ) as $day => $allPeriods ) {

			// Filter work hours
			$workPeriods = array_filter(
				$allPeriods,
				function ( $period ) {
					return ACTIVITY_WORK == $period['activity'];
				}
			);

			// Pull TimePeriod objects
			$workPeriods = array_map(
				function ( $period ) {
					return $period['time_period'];
				},
				$workPeriods
			);

			// Merge all periods into one and save
			if ( ! empty( $workPeriods ) ) {
				$dayPeriod = array_shift( $workPeriods );
				$dayPeriod->mergePeriods( $workPeriods );

				$workWeek[ $day ] = $dayPeriod;
			}
		} // For each day

		return $workWeek;
	}

	/**
	 * @return int[]
	 *
	 * @since 1.2.2 (Replaced the <code>Schedule::getLocations()</code>)
	 */
	public function getLocationIds() {
		$locations = array();

		// Add main location
		if ( 0 != $this->locationId ) {
			$locations[] = $this->locationId;
		}

		// Add all locations from the timetable
		foreach ( $this->timetable as $dayPeriods ) {
			foreach ( $dayPeriods as $period ) {
				$locations[] = $period['location'];
			}
		}

		return mpa_array_unique_reset( $locations );
	}

	/**
	 * @param DateTime|string $date
	 * @return int[]
	 *
	 * @since 1.2.2 (Replaced the <code>Schedule::getLocationsForDate()</code>)
	 */
	public function getLocationIdsForDate( $date ) {
		if ( is_string( $date ) ) {
			$date = mpa_parse_date( $date );
		}

		$locations = array();

		// Get locations from timetable
		$dayIndex  = (int) $date->format( 'w' ); // 0-6
		$dayOfWeek = array_keys( $this->timetable )[ $dayIndex ]; // sunday-saturday

		foreach ( $this->timetable[ $dayOfWeek ] as $period ) {
			$locations[] = $period['location'];
		}

		// Add locations from custom working days
		foreach ( $this->customWorkdays as $period ) {
			if ( $period['date_period']->inPeriod( $date ) ) {
				$locations[] = $this->locationId;

				// No need to add $this->locationId multiple times
				break;
			}
		}

		return mpa_array_unique_reset( $locations );
	}
}
