<?php

namespace MotoPress\Appointment\Entities;

use WP_Comment;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 *
 * @see \MotoPress\Appointment\Repositories\BookingRepository
 */
class Booking extends AbstractUniqueEntity {

	/**
	 * @var string
	 *
	 * @since 1.0
	 * @since 1.19.0 protected
	 */
	protected $status = 'new';

	/**
	 * @var float
	 *
	 * @since 1.0
	 */
	protected $totalPrice = 0.0;

	/**
	 * @var Reservation[]
	 *
	 * @since 1.0
	 */
	protected $reservations = array();

	/**
	 * @var string
	 *
	 * @since 1.10.2
	 * @since 1.18.0 protected
	 */
	protected $customerNotes = '';

	/**
	 * @var int
	 *
	 * @since 1.11.0
	 */
	protected $couponId = 0;

	/**
	 * @var int
	 *
	 * @since 1.18.0
	 */
	protected $customerId = 0;

	/**
	 * @since 1.18.0
	 *
	 * @var string|null
	 */
	protected $customerName = null;

	/**
	 * @since 1.18.0
	 *
	 * @var string|null
	 */
	protected $customerEmail = null;

	/**
	 * @since 1.18.0
	 *
	 * @var string|null
	 */
	protected $customerPhone = null;

	/**
	 * @since 1.18.0
	 *
	 * @todo Backward compatible for mpa-woocommerce version 1.1.1 and below. Will be remove in future.
	 *
	 * @throws \Exception
	 *
	 * @param $property string
	 *
	 * @return mixed
	 */
	public function __get( $property ) {
		switch ( $property ) {
			case 'customer':
				$customer = $this->getCustomer();
				$customer->setName( $this->getCustomerName() );
				$customer->setEmail( $this->getCustomerEmail() );
				$customer->setPhone( $this->getCustomerPhone() );

				return $customer;
			default:
				throw new \Exception( 'Property ' . $property . ' does not exist' );
		}
	}

	/**
	 * @since 1.17.0
	 *
	 * @return string
	 */
	public function getStatus(): string {
		return $this->status;
	}

	/**
	 * @since 1.17.0
	 *
	 * @param string $status
	 */
	public function setStatus( string $status ) {
		$this->status = $status;
	}

	public function getCouponDiscount(): float {

		$discount = 0.0;

		foreach ( $this->getReservations() as $reservation ) {

			$discount += $reservation->getDiscount();
		}

		return $discount;
	}

	/**
	 * @since 1.5.0
	 *
	 * @return float
	 */
	public function getTotalPrice() {
		return $this->totalPrice;
	}

	/**
	 * @since 1.17.0
	 *
	 * @param float $totalPrice
	 */
	public function setTotalPrice( float $totalPrice ) {
		$this->totalPrice = $totalPrice;
	}

	/**
	 * @since 1.5.0
	 *
	 * @return float
	 */
	public function getPaidPrice() {
		$paidPrice = array_reduce(
			$this->getPayments(),
			function ( $carry, $payment ) {
				if ( $payment->isCompleted() ) {
					return $carry + $payment->getAmount();
				} else {
					return $carry;
				}
			},
			0.0
		);

		return $paidPrice;
	}

	/**
	 * @since 1.5.0
	 *
	 * @return float
	 */
	public function getToPayPrice() {
		return max( 0, $this->getTotalPrice() - $this->getPaidPrice() );
	}

	/**
	 * @since 1.11.0
	 *
	 * @return Reservation[]
	 */
	public function getReservations(): array {
		return $this->reservations;
	}

	/**
	 * @since 1.17.0
	 *
	 * @param Reservation[] $reservations
	 */
	public function setReservations( array $reservations ) {
		$this->reservations = $reservations;
	}

	/**
	 * @since 1.18.0
	 *
	 * @retun int
	 */
	public function getCustomerId(): int {
		return $this->customerId;
	}

	/**
	 * @since 1.18.0
	 *
	 * @param int $customerId
	 */
	public function setCustomerId( int $customerId ) {
		$this->customerId = $customerId;
	}

	/**
	 * @since 1.13.0
	 * @since 1.18.0 protected
	 *
	 * @return Customer
	 */
	protected function getCustomer(): Customer {
		$customer   = null;
		$customerId = $this->getCustomerId();

		if ( $customerId ) {
			$customer = mpapp()->repositories()->customer()->findById( $customerId );
		}

		if ( $customer ) {
			return $customer;
		}

		return new Customer();
	}

	/**
	 * @since 1.18.0
	 * @return string
	 */
	public function getCustomerName(): string {
		if ( $this->customerName ) {
			return $this->customerName;
		}

		return $this->getCustomer()->getName();
	}

	public function setCustomerName( string $customerName ) {
		$this->customerName = $customerName;
	}

	public function getCustomerEmail(): string {

		if ( $this->customerEmail ) {
			return $this->customerEmail;
		}

		return $this->getCustomer()->getEmail();
	}

	public function setCustomerEmail( string $customerEmail ) {
		$this->customerEmail = $customerEmail;
	}

	public function getCustomerPhone(): string {

		if ( $this->customerPhone ) {
			return $this->customerPhone;
		}

		return $this->getCustomer()->getPhone();
	}

	public function setCustomerPhone( string $customerPhone ) {
		$this->customerPhone = $customerPhone;
	}

	/**
	 * @since 1.17.0
	 *
	 * @return string
	 */
	public function getCustomerNotes(): string {
		return $this->customerNotes;
	}

	/**
	 * @since 1.11.0
	 *
	 * @return Coupon|null
	 */
	public function getCoupon() {
		return mpapp()->repositories()->coupon()->findById( $this->couponId );
	}

	/**
	 * @since 1.11.0
	 *
	 * @return int
	 */
	public function getCouponId(): int {
		return $this->couponId;
	}

	/**
	 * @since 1.11.0
	 *
	 * @param int $couponId
	 */
	public function setCouponId( int $couponId ) {
		$this->couponId = $couponId;
	}

	/**
	 * @since 1.11.0
	 *
	 * @return bool
	 */
	public function hasCoupon(): bool {
		return 0 < $this->couponId;
	}

	/**
	 * @since 1.5.0
	 *
	 * @param Payment|int $payment
	 */
	public function expectPayment( $payment ) {
		$paymentId = is_object( $payment ) ? $payment->getId() : $payment;

		update_post_meta( $this->id, '_mpa_expect_payment', $paymentId );
	}

	/**
	 * @since 1.5.0
	 *
	 * @param Payment|int $payment
	 * @return bool
	 */
	public function isExpectsPayment( $payment ) {
		$paymentId = is_object( $payment ) ? $payment->getId() : $payment;

		return $paymentId === $this->getExpectingPaymentId();
	}

	/**
	 * @since 1.5.0
	 *
	 * @return int
	 */
	public function getExpectingPaymentId() {
		$expectPayment = get_post_meta( $this->id, '_mpa_expect_payment', true );

		if ( '' !== $expectPayment ) {
			return (int) $expectPayment;
		} else {
			return 0;
		}
	}

	/**
	 * @since 1.5.0
	 *
	 * @return Payment|null
	 */
	public function getExpectingPayment() {
		return mpapp()->repositories()->payment()->findById( $this->getExpectingPaymentId() );
	}

	/**
	 * @since 1.5.0
	 *
	 * @return Payment[]
	 */
	public function getPayments() {
		return mpapp()->repositories()->payment()->findAllByBooking( $this->id );
	}

	/**
	 * @param array $args Optional.
	 * @return array
	 *
	 * @see \MotoPress\Appointment\Repositories\AbstractRepository::findAll()
	 *
	 * @since 1.0
	 */
	public function getServices( $args = array() ) {
		return $this->getLinkedEntities( $this->getServiceIds(), $args, 'mpa_get_services' );
	}

	/**
	 * @param array $args Optional.
	 * @return array
	 *
	 * @see \MotoPress\Appointment\Repositories\AbstractRepository::findAll()
	 *
	 * @since 1.0
	 */
	public function getEmployees( $args = array() ) {
		return $this->getLinkedEntities( $this->getEmployeeIds(), $args, 'mpa_get_employees' );
	}

	/**
	 * @param array $args Optional.
	 * @return array
	 *
	 * @see \MotoPress\Appointment\Repositories\AbstractRepository::findAll()
	 *
	 * @since 1.0
	 */
	public function getLocations( $args = array() ) {
		return $this->getLinkedEntities( $this->getLocationIds(), $args, 'mpa_get_locations' );
	}

	/**
	 * @param int[] $ids
	 * @param array $args
	 * @param string $getCallback
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function getLinkedEntities( $ids, $args, $getCallback ) {

		if ( empty( $ids ) ) {
			return array();
		}

		$searchArgs = array_merge( $args, array( 'include' => $ids ) );

		// for example: mpa_get_services($searchArgs) etc.
		return $getCallback( $searchArgs );
	}

	/**
	 * @return int[]
	 *
	 * @since 1.0
	 */
	public function getServiceIds() {

		return array_map(
			function ( $reservation ) {
				return $reservation->getServiceId();
			},
			$this->reservations
		);
	}

	/**
	 * @return int[]
	 *
	 * @since 1.0
	 */
	public function getEmployeeIds() {

		return array_map(
			function ( $reservation ) {
				return $reservation->getEmployeeId();
			},
			$this->reservations
		);
	}

	/**
	 * @return int[]
	 *
	 * @since 1.0
	 */
	public function getLocationIds() {

		return array_map(
			function ( $reservation ) {
				return $reservation->getLocationId();
			},
			$this->reservations
		);
	}

	/**
	 * @return bool
	 *
	 * @since 1.10.1
	 */
	public function isAdminBooking(): bool {
		$isAdminBooking = get_post_meta( $this->id, '_mpa_is_admin_booking', true );

		return (bool) $isAdminBooking;
	}

	/**
	 * @return bool
	 *
	 * @since 1.11.0
	 */
	public function isConfirmed(): bool {
		return array_key_exists( $this->status, mpapp()->postTypes()->booking()->statuses()->getBookedStatuses() );
	}

	/**
	 * @param int|null $authorId Optional. Current logged in user ID by default.
	 * @return int|false The new comment's ID on success, false on failure.
	 *
	 * @since 1.14.0
	 */
	public function addLog( string $message, $authorId = null ) {
		return mpapp()->postTypes()->booking()->logs()->addLog( $this->id, $message, $authorId );
	}

	/**
	 * @return WP_Comment[] Logs in descending order.
	 *
	 * @since 1.14.0
	 */
	public function getLogs(): array {
		return mpapp()->postTypes()->booking()->logs()->getLogs( $this->id );
	}

	/**
	 * @return array
	 *
	 * @since 1.x.x
	 */
	// TODO: maybe move to AbstractClass
	public function toArray() : array {
		return array( 
			'uid'           => $this->getUid(),
			'status'        => $this->getStatus(),
			'totalPrice'    => $this->getTotalPrice(),
			'reservations'  => $this->getReservations(),
			'customerNotes' => $this->getCustomerNotes(),
			'couponId'      => $this->getCouponId(),
			'customerId'    => $this->getCustomerId(),
			'customerName'  => $this->getCustomerName(),
			'customerEmail' => $this->getCustomerEmail(),
			'customerPhone' => $this->getCustomerPhone(),
		);
	}
}
