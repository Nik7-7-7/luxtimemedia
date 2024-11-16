<?php

namespace MotoPress\Appointment\REST\Controllers\Motopress\Appointment\V1;

use MotoPress\Appointment\Entities\Schedule;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class SchedulesRestController extends AbstractRestController {

	/**
	 * @since 1.0
	 */
	public function register_routes() {

		// '/motopress/appointment/v1/schedules'
		register_rest_route(
			$this->getNamespace(),
			'/schedules',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'getSchedules' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'id' => array(
						'default'           => 0,
						'sanitize_callback' => 'mpa_rest_sanitize_id',
					),
				),
			)
		);
	}

	/**
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 *
	 * @since 1.0
	 */
	public function getSchedules( $request ) {

		// Get and check the input
		$scheduleId = $request->get_param( 'id' );

		if ( ! $scheduleId ) {
			// "Get all schedules" is not supported in the current version
			return mpa_rest_request_error( esc_html__( 'Invalid parameter: schedule ID is not set.', 'motopress-appointment' ) );
		}

		// Load schedule
		$schedule = mpa_get_schedule( $scheduleId );

		// Return the response
		if ( ! is_null( $schedule ) ) {
			return rest_ensure_response( $this->mapEntity( $schedule ) );
		} else {
			return mpa_rest_request_error( sprintf( esc_html__( 'Invalid request: schedule #$d not found.', 'motopress-appointment' ), $scheduleId ) );
		}
	}

	/**
	 * @param Schedule $entity
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function mapEntity( $entity ) {

		$daysOff = array_keys( $entity->getDaysOff() );

		$customWorkdays = array_map(
			function ( $period ) {
				return array(
					'date_period' => $period['date_period']->toString( 'internal' ),
					'time_period' => $period['time_period']->toString( 'internal' ),
				);
			},
			$entity->getCustomWorkdays()
		);

		$entityFields = array(
			'id'             => $entity->getId(),
			'name'           => $entity->getTitle(),
			'employeeId'     => $entity->getEmployeeId(),
			'locationId'     => $entity->getLocationId(),
			'timetable'      => array(),
			'daysOff'        => $daysOff,
			'customWorkdays' => $customWorkdays,
		);

		// Fill the timetable
		foreach ( $entity->getTimetable() as $periods ) {

			$entityFields['timetable'][] = array_map(
				function ( $period ) {
					return array(
						'time_period' => $period['time_period']->toString( 'internal' ),
						'location'    => $period['location'],
						'activity'    => $period['activity'],
					);
				},
				$periods
			);
		}

		return $entityFields;
	}
}
