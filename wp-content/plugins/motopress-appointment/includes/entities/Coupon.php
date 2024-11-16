<?php

declare(strict_types=1);

namespace MotoPress\Appointment\Entities;

use MotoPress\Appointment\PostTypes\Statuses\AbstractPostStatuses;
use DateTime;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.11.0
 */
class Coupon extends AbstractEntity {

	const COUPON_DISCOUNT_TYPE_FIXED      = 'fixed';
	const COUPON_DISCOUNT_TYPE_PERCENTAGE = 'percentage';

	/**
	 * @var string
	 */
	protected $status = 'new';

	/**
	 * @var string
	 */
	protected $code = '';

	/**
	 * @var string
	 */
	protected $description = '';

	/**
	 * @var string
	 */
	protected $type = self::COUPON_DISCOUNT_TYPE_FIXED;

	/**
	 * @var float
	 */
	protected $amount = 0.0;

	/**
	 * @var DateTime|null
	 */
	protected $expirationDate = null;

	/**
	 * @var int[]
	 */
	protected $serviceIds = array();

	/**
	 * @var DateTime|null
	 */
	protected $minDate = null;

	/**
	 * @var DateTime|null
	 */
	protected $maxDate = null;

	/**
	 * @var int
	 */
	protected $usageLimit = 0;

	/**
	 * @var int
	 */
	protected $usageCount = 0;

	public function getStatus(): string {
		return $this->status;
	}

	public function getCode(): string {
		return $this->code;
	}

	public function getDescription(): string {
		return $this->description;
	}

	public function getType(): string {
		return $this->type;
	}

	public function getAmount(): float {
		return $this->amount;
	}

	/**
	 * @return DateTime|null
	 */
	public function getExpirationDate() {
		return $this->expirationDate;
	}

	/**
	 * @return int[]
	 */
	public function getServiceIds(): array {
		return $this->serviceIds;
	}

	/**
	 * @return DateTime|null
	 */
	public function getMinDate() {
		return $this->minDate;
	}

	/**
	 * @return DateTime|null
	 */
	public function getMaxDate() {
		return $this->maxDate;
	}

	public function getUsageLimit(): int {
		return $this->usageLimit;
	}

	public function getUsageCount(): int {
		return $this->usageCount;
	}

	public function isValid(): bool {
		return $this->isPublic()
			&& ! $this->isExpired()
			&& ! $this->isExceededUsageLimit();
	}

	public function isPublic(): bool {
		return AbstractPostStatuses::STATUS_PUBLISH == $this->status;
	}

	public function isExpired(): bool {
		return ! is_null( $this->expirationDate )
			&& mpa_format_date( $this->expirationDate, 'internal' ) <= current_time( 'Y-m-d' );
	}

	public function isExceededUsageLimit(): bool {
		return $this->usageLimit > 0 && $this->usageCount >= $this->usageLimit;
	}

	public function isApplicableForBooking( Booking $booking ): bool {

		if ( ! $this->isValid() ) {
			return false;
		}

		foreach ( $booking->getReservations() as $reservation ) {
			if ( $this->isApplicableForReservation( $reservation ) ) {
				return true;
			}
		}

		return false;
	}

	public function isApplicableForReservation( Reservation $reservation ): bool {

		// Zero selected services = all services are available
		if ( ! empty( $this->serviceIds ) && ! in_array( $reservation->getServiceId(), $this->serviceIds ) ) {
			return false;
		}

		if ( ! is_null( $this->minDate ) && $reservation->getDate() < $this->minDate ) {
			return false;
		}

		if ( ! is_null( $this->maxDate ) && $reservation->getDate() > $this->maxDate ) {
			return false;
		}

		return true;
	}

	public function increaseUsageCount( $save = false ) {

		$this->usageCount++;

		if ( $save ) {
			mpapp()->repositories()->coupon()->saveUsageCount( $this, $this->usageCount );
		}
	}

	/**
	 * Required for repositories. Otherwise, all/most fields will have to be
	 * made public.
	 *
	 * @example mpapp()->repositories()->coupon()->findAll(['fields' => ['id' => 'code']])
	 *
	 * @return mixed
	 */
	public function __get( string $name ) {

		$methodName = 'get' . ucfirst( $name );

		return $this->$methodName();
	}
}
