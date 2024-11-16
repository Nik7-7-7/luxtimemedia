<?php

declare(strict_types=1);

namespace MotoPress\Appointment\Helpers;

use MotoPress\Appointment\Entities\Schedule;
use MotoPress\Appointment\Structures\TimePeriod;

use const MotoPress\Appointment\ACTIVITY_WORK;
use const MotoPress\Appointment\TIMETABLE_DAYS;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 2.0.0
 */
abstract class ScheduleHelper {
	/**
	 * @var array [
	 *     Employee ID => [
	 *         Location ID => [
	 *             0 (Sunday)   => TimePeriod[],
	 *             1 (Monday)   => TimePeriod[],
	 *             ...
	 *             6 (Saturday) => TimePeriod[],
	 *         ]
	 *     ]
	 * ]
	 */
	private static $cachedTimetableDays = [];

	/**
	 * @return TimePeriod[]
	 */
	public static function getWorkingHoursForDate( Schedule $schedule, \DateTime $date, int $locationId = 0 ): array {
		if ( $schedule->isDayOff( $date ) ) {
			return [];
		}

		// Use custom working hours with higher priority than the timetable hours
		if ( $schedule->hasCustomWorkingHoursForDate( $date ) ) {
			$workingHours = $schedule->getCustomWorkingHoursForDate( $date );
		} else {
			$workingHours = static::getTimetableWorkingHoursForDate( $schedule, $date, $locationId );
		}

		foreach( $workingHours as $timePeriod ) {
			$timePeriod->setDate( $date );
		}

		return $workingHours;
	}

	/**
	 * @return TimePeriod[]
	 */
	public static function getTimetableWorkingHoursForDate( Schedule $schedule, \DateTime $date, int $locationId = 0 ): array {
		$employeeId = $schedule->getEmployeeId();
		$weekDay = (int) $date->format( 'w' );

		if ( ! isset( static::$cachedTimetableDays[ $employeeId ][ $locationId ][ $weekDay ] ) ) {

			$workingPeriods = array_filter(
				static::getTimetablePeriodsForDate( $schedule, $date ),

				function ( $period ) use ( $locationId ) {
					// Filter by activity
					if ( $period['activity'] != ACTIVITY_WORK ) {
						return false;
					}

					// Filter by location
					if ( $locationId != 0 && $period['location'] != $locationId ) {
						return false;
					}

					return true;
				}
			);

			// Pull TimePeriod objects
			$workingHours = array_map(
				function ( $period ) {
					return $period['time_period'];
				},
				$workingPeriods
			);

			static::$cachedTimetableDays[ $employeeId ][ $locationId ][ $weekDay ] = $workingHours;
		}

		return mpa_array_clone(
			static::$cachedTimetableDays[ $employeeId ][ $locationId ][ $weekDay ]
		);
	}

	/**
	 * @return array [
	 *     [
	 *         'time_period' => TimePeriod,
	 *         'location'    => int,
	 *         'activity'    => 'work'|'lunch'|'break',
	 *     ],
	 *     ...
	 * ]
	 */
	public static function getTimetablePeriodsForDate( Schedule $schedule, \DateTime $date ): array {
		$weekDay = (int) $date->format( 'w' );
		$dayString = TIMETABLE_DAYS[ $weekDay ];

		return $schedule->getTimetable()[ $dayString ] ?? [];
	}
}
