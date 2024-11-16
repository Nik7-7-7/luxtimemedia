<?php

namespace MotoPress\Appointment\Registries;

use MotoPress\Appointment\PostTypes;
use MotoPress\Appointment\Repositories;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class RepositoriesRegistry {

	/**
	 * @var Repositories\AbstractRepository[]
	 *
	 * @since 1.0
	 */
	protected $repositories = array();

	/**
	 * @return Repositories\EmployeeRepository
	 *
	 * @since 1.0
	 */
	public function employee() {
		if ( ! isset( $this->repositories['employee'] ) ) {
			$this->repositories['employee'] = new Repositories\EmployeeRepository( PostTypes\EmployeePostType::POST_TYPE );
		}

		return $this->repositories['employee'];
	}

	/**
	 * @return Repositories\ScheduleRepository
	 *
	 * @since 1.0
	 */
	public function schedule() {
		if ( ! isset( $this->repositories['schedule'] ) ) {
			$this->repositories['schedule'] = new Repositories\ScheduleRepository( PostTypes\SchedulePostType::POST_TYPE );
		}

		return $this->repositories['schedule'];
	}

	/**
	 * @return Repositories\LocationRepository
	 *
	 * @since 1.0
	 */
	public function location() {
		if ( ! isset( $this->repositories['location'] ) ) {
			$this->repositories['location'] = new Repositories\LocationRepository( PostTypes\LocationPostType::POST_TYPE );
		}

		return $this->repositories['location'];
	}

	/**
	 * @return Repositories\ServiceRepository
	 *
	 * @since 1.0
	 */
	public function service() {
		if ( ! isset( $this->repositories['service'] ) ) {
			$this->repositories['service'] = new Repositories\ServiceRepository( PostTypes\ServicePostType::POST_TYPE );
		}

		return $this->repositories['service'];
	}

	/**
	 * @return Repositories\BookingRepository
	 *
	 * @since 1.0
	 */
	public function booking() {
		if ( ! isset( $this->repositories['booking'] ) ) {
			$this->repositories['booking'] = new Repositories\BookingRepository( PostTypes\BookingPostType::POST_TYPE );
		}

		return $this->repositories['booking'];
	}

	/**
	 * @return Repositories\ReservationRepository
	 *
	 * @since 1.0
	 */
	public function reservation() {
		if ( ! isset( $this->repositories['reservation'] ) ) {
			$this->repositories['reservation'] = new Repositories\ReservationRepository( PostTypes\ReservationPostType::POST_TYPE );
		}

		return $this->repositories['reservation'];
	}

	/**
	 * @since 1.5.0
	 *
	 * @return Repositories\PaymentRepository
	 */
	public function payment() {
		if ( ! isset( $this->repositories[ __FUNCTION__ ] ) ) {
			$this->repositories[ __FUNCTION__ ] = new Repositories\PaymentRepository( PostTypes\PaymentPostType::POST_TYPE );
		}

		return $this->repositories[ __FUNCTION__ ];
	}

	/**
	 * @since 1.11.0
	 *
	 * @return Repositories\CouponRepository
	 */
	public function coupon() {
		if ( ! isset( $this->repositories[ __FUNCTION__ ] ) ) {
			$this->repositories[ __FUNCTION__ ] = new Repositories\CouponRepository( PostTypes\CouponPostType::POST_TYPE );
		}

		return $this->repositories[ __FUNCTION__ ];
	}

	/**
	 * @since 1.13.0
	 *
	 * @return Repositories\NotificationRepository
	 */
	public function notification() {
		if ( ! isset( $this->repositories[ __FUNCTION__ ] ) ) {
			$this->repositories[ __FUNCTION__ ] = new Repositories\NotificationRepository( PostTypes\NotificationPostType::POST_TYPE );
		}

		return $this->repositories[ __FUNCTION__ ];
	}

	/**
	 * @return Repositories\CustomerRepository
	 * @since 1.18.0
	 *
	 */
	public function customer() {
		if ( ! isset( $this->repositories[ __FUNCTION__ ] ) ) {
			$this->repositories[ __FUNCTION__ ] = new Repositories\CustomerRepository();
		}

		return $this->repositories[ __FUNCTION__ ];
	}

	/**
	 * @param string $postType
	 * @return Repositories\AbstractRepository|null
	 *
	 * @since 1.0
	 */
	public function getByPostType( $postType ) {
		switch ( $postType ) {
			case PostTypes\EmployeePostType::POST_TYPE:
				return $this->employee();
			case PostTypes\SchedulePostType::POST_TYPE:
				return $this->schedule();
			case PostTypes\LocationPostType::POST_TYPE:
				return $this->location();
			case PostTypes\ServicePostType::POST_TYPE:
				return $this->service();
			case PostTypes\BookingPostType::POST_TYPE:
				return $this->booking();
			case PostTypes\ReservationPostType::POST_TYPE:
				return $this->reservation();
			case PostTypes\PaymentPostType::POST_TYPE:
				return $this->payment();
			case PostTypes\CouponPostType::POST_TYPE:
				return $this->coupon();
			case PostTypes\NotificationPostType::POST_TYPE:
				return $this->notification();
			default:
				return null;
		}
	}
}
