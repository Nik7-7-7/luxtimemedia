<?php

namespace MotoPress\Appointment\Structures\Service;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.3.1
 */
class ServiceVariations {

	/**
	 * @since 1.3.1
	 * @var ServiceVariation[] [Employee ID => ServiceVariation]
	 */
	private $variations;

	/**
	 * @since 1.3.1
	 *
	 * @param ServiceVariation[] $variations [Employee ID => ServiceVariation]
	 */
	public function __construct( $variations ) {
		$this->variations = $variations;
	}

	/**
	 * @since 1.3.1
	 *
	 * @param int $employeeId
	 * @return bool
	 */
	public function hasVariationForEmployee( $employeeId ) {
		return array_key_exists( $employeeId, $this->variations );
	}

	/**
	 * @since 1.3.1
	 *
	 * @param int $employeeId
	 * @param float $defaultPrice Optional. 0 by default.
	 * @return float
	 */
	public function getPrice( $employeeId, $defaultPrice = 0.0 ) {
		return $this->getValue( 'price', $employeeId, $defaultPrice );
	}

	/**
	 * @since 1.3.1
	 *
	 * @param int $employeeId
	 * @param int $defaultDuration Optional. 0 by default.
	 * @return int
	 */
	public function getDuration( $employeeId, $defaultDuration = 0 ) {
		return $this->getValue( 'duration', $employeeId, $defaultDuration );
	}

	/**
	 * @since 1.3.1
	 *
	 * @param int $employeeId
	 * @param int $defaultCapacity Optional. 1 by default.
	 * @return int
	 */
	public function getMinCapacity( $employeeId, $defaultCapacity = 1 ) {
		return $this->getValue( 'minCapacity', $employeeId, $defaultCapacity );
	}

	/**
	 * @since 1.3.1
	 *
	 * @param int $employeeId
	 * @param int $defaultCapacity Optional. 1 by default.
	 * @return int
	 */
	public function getMaxCapacity( $employeeId, $defaultCapacity = 1 ) {
		return $this->getValue( 'maxCapacity', $employeeId, $defaultCapacity );
	}

	/**
	 * @since 1.3.1
	 *
	 * @param string $field
	 * @param int $employeeId
	 * @param mixed $defaultValue
	 * @return mixed
	 */
	protected function getValue( $field, $employeeId, $defaultValue ) {
		if ( $this->hasVariationForEmployee( $employeeId ) ) {
			return $this->variations[ $employeeId ]->$field;
		} else {
			return $defaultValue;
		}
	}

	/**
	 * @since 1.3.1
	 *
	 * @param bool $deep Optional. False by default.
	 * @return array [Employee ID => ServiceVariations[] or array]
	 */
	public function toArray( $deep = false ) {
		if ( ! $deep ) {
			return $this->variations;
		} else {
			return array_map(
				function ( $variation ) {
					return $variation->toArray(); },
				$this->variations
			);
		}
	}
}
