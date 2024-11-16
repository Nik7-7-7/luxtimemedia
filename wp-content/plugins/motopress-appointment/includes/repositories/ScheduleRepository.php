<?php

namespace MotoPress\Appointment\Repositories;

use MotoPress\Appointment\Entities\Schedule;
use MotoPress\Appointment\Structures\DatePeriod;
use MotoPress\Appointment\Structures\TimePeriod;
use DateTime;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 *
 * @see Schedule
 */
class ScheduleRepository extends AbstractRepository {

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function entitySchema() {
		return array(
			'post'     => array( 'ID', 'post_title' ),
			'postmeta' => array(
				'_mpa_employee'        => true,
				'_mpa_main_location'   => true,
				'_mpa_timetable'       => true,
				'_mpa_days_off'        => false,
				'_mpa_custom_workdays' => false,
			),
		);
	}

	/**
	 * @param array $postData
	 * @return Schedule
	 *
	 * @since 1.0
	 */
	protected function mapPostDataToEntity( $postData ) {
		$id = (int) $postData['ID'];

		$fields = array(
			'title'          => $postData['post_title'],
			'employeeId'     => (int) $postData['employee'],
			'locationId'     => (int) $postData['main_location'],
			'timetable'      => $this->buildTimetable( $postData['timetable'], (int) $postData['main_location'] ),
			'daysOff'        => $this->buildDaysOff( $postData['days_off'] ),
			'customWorkdays' => $this->buildCustomWorkdays( $postData['custom_workdays'] ),
		);

		return new Schedule( $id, $fields );
	}

	/**
	 * @param array|'' $timetableData Array of [string 'day', int 'start',
	 *     int 'end', string 'activity', int 'location'] (location is optional)
	 *     or empty string.
	 * @param int $mainLocation
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function buildTimetable( $timetableData, $mainLocation ) {
		// The order of days is equal to the order after array_values()
		// (0 = Sunday, 1 = Monday etc.)
		$timetable = array_fill_keys(
			array(
				'sunday',
				'monday',
				'tuesday',
				'wednesday',
				'thursday',
				'friday',
				'saturday',
			),
			array()
		);

		if ( ! is_array( $timetableData ) ) {
			return $timetable;
		}

		foreach ( $timetableData as $period ) {
			$startTime = mpa_format_minutes( $period['start'], 'internal' );
			$endTime   = mpa_format_minutes( $period['end'], 'internal' );

			$timePeriod = new TimePeriod( $startTime, $endTime );

			$location = ! empty( $period['location'] ) ? $period['location'] : $mainLocation;
			$day      = $period['day'];

			$timetable[ $day ][] = array(
				'time_period' => $timePeriod,
				'location'    => $location,
				'activity'    => $period['activity'],
			);
		}

		return $timetable;
	}

	/**
	 * @since 1.0
	 *
	 * @param string[]|'' $periods
	 * @return DateTime[] ['Y-m-d' => DateTime]
	 */
	protected function buildDaysOff( $periods ) {
		if ( ! is_array( $periods ) ) {
			return array();
		}

		$daysOff = array();

		foreach ( $periods as $period ) {
			$datePeriod = new DatePeriod( $period );
			$daysOff   += $datePeriod->splitToDates();
		}

		return $daysOff;
	}

	/**
	 * @param string[]|'' $days
	 * @return array Array of [DatePeriod 'date_period', TimePeriod 'time_period'].
	 *
	 * @since 1.0
	 */
	protected function buildCustomWorkdays( $days ) {

		if ( ! is_array( $days ) ) {
			return array();
		}

		return array_map(
			function ( $period ) {
				// Explode '2020-01-25 - 2020-02-10, 10:00 - 14:00' into
				// ['2020-01-25 - 2020-02-10', '10:00 - 14:00']
				$periods = explode( ', ', $period );

				$datePeriod = new DatePeriod( $periods[0] );
				$timePeriod = new TimePeriod( $periods[1] );

				return array(
					'date_period' => $datePeriod,
					'time_period' => $timePeriod,
				);
			},
			$days
		);
	}

	/**
	 * @param int $employeeId
	 * @return Schedule|null
	 *
	 * @since 1.0
	 */
	public function findByEmployee( $employeeId ) {
		return $this->findByMeta( '_mpa_employee', $employeeId );
	}

	/**
	 * @param int|int[] $employee
	 * @param array $args Optional.
	 * @return array
	 *
	 * @see \MotoPress\Appointment\Repositories\AbstractRepository::findAllByMeta()
	 *
	 * @since 1.0
	 */
	public function findAllByEmployee( $employee, $args = array() ) {
		return $this->findAllByMeta( '_mpa_employee', $employee, null, $args );
	}
}
