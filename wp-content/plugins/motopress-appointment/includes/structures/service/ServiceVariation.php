<?php

namespace MotoPress\Appointment\Structures\Service;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.3.1
 */
class ServiceVariation {

	/**
	 * @since 1.3.1
	 * @var int
	 */
	public $employeeId;

	/**
	 * @since 1.3.1
	 * @var float
	 */
	public $price;

	/**
	 * @since 1.3.1
	 * @var int
	 */
	public $duration;

	/**
	 * @since 1.3.1
	 * @var int
	 */
	public $minCapacity;

	/**
	 * @since 1.3.1
	 * @var int
	 */
	public $maxCapacity;

	/**
	 * @since 1.3.1
	 *
	 * @param array $args Optional.
	 *     @param int   $args['employee']
	 *     @param float $args['price']
	 *     @param int   $args['duration']
	 *     @param int   $args['min_capacity']
	 *     @param int   $args['max_capacity']
	 */
	public function __construct( $args = array() ) {
		$args += array(
			'employee'     => 0,
			'price'        => 0,
			'duration'     => 0,
			'min_capacity' => 1,
			'max_capacity' => 1,
		);

		$this->employeeId  = (int) $args['employee'];
		$this->price       = (float) $args['price'];
		$this->duration    = (int) $args['duration'];
		$this->minCapacity = (int) $args['min_capacity'];
		$this->maxCapacity = (int) $args['max_capacity'];
	}

	/**
	 * @since 1.3.1
	 *
	 * @return array
	 */
	public function toArray() {
		return array(
			'employee'     => $this->employeeId,
			'price'        => $this->price,
			'duration'     => $this->duration,
			'min_capacity' => $this->minCapacity,
			'max_capacity' => $this->maxCapacity,
		);
	}
}
