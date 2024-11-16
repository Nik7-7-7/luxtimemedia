<?php

namespace MotoPress\Appointment\Entities;

use MotoPress\Appointment\Structures\TimePeriod;
use DateTime;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class Reservation extends AbstractUniqueEntity {

	/**
	 * @var int
	 */
	private $bookingId = 0;

	/**
	 * @var float subtotal price - service price for employee
	 * multiplied by capacity/clients count.
	 */
	private $price = 0.0;

	/**
	 * @var float
	 */
	private $discount = 0.0;

	/**
	 * @var float
	 */
	private $totalPrice = 0.0;

	/**
	 * @var float
	 */
	private $depositAmount = 0.0;

	/**
	 * @var DateTime
	 */
	private $date = null;

	/**
	 * @var TimePeriod
	 */
	private $serviceTime = null;

	/**
	 * @var TimePeriod
	 */
	private $bufferTime = null;

	/**
	 * @var int
	 */
	private $serviceId = 0;

	/**
	 * @var int
	 */
	private $employeeId = 0;

	/**
	 * @var int
	 */
	private $locationId = 0;

	/**
	 * @var int
	 */
	private $capacity = 1;

	/**
	 * @var int[]
	 */
	private $sentNotificationIds = array();

	public function getBookingId(): int {
		return $this->bookingId;
	}

	public function setBookingId( int $bookingId ) {
		$this->bookingId = $bookingId;
	}

	/**
	 * @return Booking|null
	 */
	public function getBooking( $forceReload = false ) {

		if ( $this->bookingId > 0 ) {
			return mpa_get_booking( $this->bookingId, $forceReload );
		} else {
			return null;
		}
	}

	public function getPrice(): float {
		return $this->price;
	}

	public function setPrice( float $price ) {
		$this->price = $price;
	}

	public function getDiscount(): float {
		return $this->discount;
	}

	public function setDiscount( float $discount ) {
		$this->discount = $discount;
	}

	public function getTotalPrice(): float {
		return $this->totalPrice;
	}

	public function setTotalPrice( float $totalPrice ) {
		$this->totalPrice = $totalPrice;
	}

	public function getDepositAmount(): float {
		return $this->depositAmount;
	}

	public function setDepositAmount( float $depositAmount ) {
		$this->depositAmount = $depositAmount;
	}

	/**
	 * @return DateTime
	 *
	 * @since 1.11.0
	 */
	public function getDate(): DateTime {
		return $this->date;
	}

	public function setDate( DateTime $date ) {
		$this->date = $date;
	}

	/**
	 * @since 1.13.0
	 */
	public function getServiceTime(): TimePeriod {
		return $this->serviceTime;
	}

	public function setServiceTime( TimePeriod $serviceTime ) {
		$this->serviceTime = $serviceTime;
	}

	/**
	 * @since 1.13.0
	 */
	public function getBufferTime(): TimePeriod {
		return $this->bufferTime;
	}

	public function setBufferTime( TimePeriod $bufferTime ) {
		$this->bufferTime = $bufferTime;
	}

	/**
	 * @return int
	 *
	 * @since 1.11.0
	 */
	public function getServiceId(): int {
		return $this->serviceId;
	}

	public function setServiceId( int $serviceId ) {
		$this->serviceId = $serviceId;
	}

	/**
	 * @return Service|null
	 */
	public function getService( $forceReload = false ) {
		return mpapp()->repositories()->service()->findById( $this->getServiceId(), $forceReload );
	}

	/**
	 * @since 1.13.0
	 */
	public function getEmployeeId(): int {
		return $this->employeeId;
	}

	public function setEmployeeId( int $employeeId ) {
		$this->employeeId = $employeeId;
	}

	/**
	 * @return Employee|null
	 */
	public function getEmployee( $forceReload = false ) {
		return mpapp()->repositories()->employee()->findById( $this->getEmployeeId(), $forceReload );
	}

	/**
	 * @since 1.13.0
	 */
	public function getLocationId(): int {
		return $this->locationId;
	}

	public function setLocationId( int $locationId ) {
		$this->locationId = $locationId;
	}

	/**
	 * @return Location|null
	 */
	public function getLocation( $forceReload = false ) {
		return mpapp()->repositories()->location()->findById( $this->getLocationId(), $forceReload );
	}

	/**
	 * @since 1.13.0
	 */
	public function getCapacity(): int {
		return $this->capacity;
	}

	public function setCapacity( int $capacity ) {
		$this->capacity = $capacity;
	}

	/**
	 * @return int[]
	 *
	 * @since 1.13.0
	 */
	public function getSentNotificationIds(): array {
		return $this->sentNotificationIds;
	}

	/**
	 * @param int[] $notificationIds
	 */
	public function setSentNotificationIds( array $notificationIds ) {
		$this->sentNotificationIds = $notificationIds;
	}

	/**
	 * @since 2.0.0
	 */
	public function __clone() {
		if ( ! is_null( $this->date ) ) {
			$this->date = clone $this->date;
		}

		if ( ! is_null( $this->serviceTime ) ) {
			$this->serviceTime = clone $this->serviceTime;
		}

		if ( ! is_null( $this->bufferTime ) ) {
			$this->bufferTime = clone $this->bufferTime;
		}
	}
}
