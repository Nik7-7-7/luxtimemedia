<?php
/**
 * @package MotoPress\Appointment\Rest
 * @since 1.8.0
 */

namespace MotoPress\Appointment\Rest\Data;

use MotoPress\Appointment\Entities\Reservation;
use MotoPress\Appointment\Rest\ApiHelper;

class ReservationData extends AbstractPostData {

	/**
	 * @var Reservation
	 */
	public $entity;

	/**
	 * @var string
	 */
	protected $wpTimezoneString;

	public function __construct( $entity ) {
		parent::__construct( $entity );

		$this->wpTimezoneString = wp_timezone_string();
	}

	public static function getRepository() {
		return mpapp()->repositories()->reservation();
	}

	public static function getProperties() {
		return array(
			'id'                         => array(
				'description' => 'Unique identifier for the resource.',
				'type'        => 'integer',
				'context'     => array( 'embed', 'view', 'edit' ),
				'readonly'    => true,
			),
			'uid'                        => array(
				'description' => 'Reservation uid.',
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'reservation_start_time'     => array(
				'description' => 'Reservation start time.',
				'type'        => 'string',
				'format'      => 'date-time',
				'context'     => array( 'embed', 'view', 'edit' ),
				'required'    => true,
			),
			'reservation_start_time_gmt' => array(
				'description' => 'Reservation start time, as GMT.',
				'type'        => 'string',
				'format'      => 'date-time',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'reservation_end_time'       => array(
				'description' => 'Reservation end time.',
				'type'        => 'string',
				'format'      => 'date-time',
				'context'     => array( 'embed', 'view', 'edit' ),
				'required'    => true,
			),
			'reservation_end_time_gmt'   => array(
				'description' => 'Reservation end time, as GMT.',
				'type'        => 'string',
				'format'      => 'date-time',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'buffer_start_time'          => array(
				'description' => 'Buffer start time.',
				'type'        => 'string',
				'format'      => 'date-time',
				'context'     => array( 'embed', 'view', 'edit' ),
				'required'    => true,
			),
			'buffer_start_time_gmt'      => array(
				'description' => 'Buffer start time, as GMT.',
				'type'        => 'string',
				'format'      => 'date-time',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'buffer_end_time'            => array(
				'description' => 'Buffer end time.',
				'type'        => 'string',
				'format'      => 'date-time',
				'context'     => array( 'embed', 'view', 'edit' ),
				'required'    => true,
			),
			'buffer_end_time_gmt'        => array(
				'description' => 'Buffer end time, as GMT.',
				'type'        => 'string',
				'format'      => 'date-time',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'booking_id'                 => array(
				'description' => 'Booking id.',
				'type'        => 'integer',
				'context'     => array( 'embed', 'view', 'edit' ),
				'required'    => true,
			),
			'service_id'                 => array(
				'description' => 'Service id.',
				'type'        => 'integer',
				'context'     => array( 'embed', 'view', 'edit' ),
				'required'    => true,
			),
			'employee_id'                => array(
				'description' => 'Employee id.',
				'type'        => 'integer',
				'context'     => array( 'embed', 'view', 'edit' ),
				'required'    => true,
			),
			'location_id'                => array(
				'description' => 'Location id.',
				'type'        => 'integer',
				'context'     => array( 'embed', 'view', 'edit' ),
				'required'    => true,
			),
			'capacity'                   => array(
				'description' => 'Capacity.',
				'type'        => 'integer',
				'context'     => array( 'embed', 'view', 'edit' ),
				'required'    => true,
			),
			'price'                      => array(
				'description' => 'Price.',
				'type'        => 'number',
				'context'     => array( 'embed', 'view', 'edit' ),
				'readonly'    => true,
			),
		);
	}

	public function getReservationStartTime() {
		return ApiHelper::prepareDateTimeResponse( $this->entity->getServiceTime()->getStartTime(), $this->wpTimezoneString );
	}

	public function getReservationStartTimeGmt() {
		return ApiHelper::prepareDateTimeResponse( $this->entity->getServiceTime()->getStartTime(), 'UTC' );
	}

	public function getReservationEndTime() {
		return ApiHelper::prepareDateTimeResponse( $this->entity->getServiceTime()->getEndTime(), $this->wpTimezoneString );
	}

	public function getReservationEndTimeGmt() {
		return ApiHelper::prepareDateTimeResponse( $this->entity->getServiceTime()->getEndTime(), 'UTC' );
	}

	public function getBufferStartTime() {
		return ApiHelper::prepareDateTimeResponse( $this->entity->getBufferTime()->getStartTime(), $this->wpTimezoneString );
	}

	public function getBufferStartTimeGmt() {
		return ApiHelper::prepareDateTimeResponse( $this->entity->getBufferTime()->getStartTime(), 'UTC' );
	}

	public function getBufferEndTime() {
		return ApiHelper::prepareDateTimeResponse( $this->entity->getBufferTime()->getEndTime(), $this->wpTimezoneString );
	}

	public function getBufferEndTimeGmt() {
		return ApiHelper::prepareDateTimeResponse( $this->entity->getBufferTime()->getEndTime(), 'UTC' );
	}
}
