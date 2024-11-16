<?php

namespace MotoPress\Appointment\Fields\Basic;

use MotoPress\Appointment\Utils\ValidateUtils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class PriceField extends NumberField {

	/** @since 1.0 */
	const TYPE = 'price';

	/**
	 * @var float|false
	 *
	 * @since 1.0
	 */
	public $min = 0;

	/**
	 * @var float|false
	 *
	 * @since 1.0
	 */
	public $max = false;

	/**
	 * @var float|false
	 *
	 * @since 1.0
	 */
	public $step = 0.01;

	/**
	 * @var float
	 *
	 * @since 1.0
	 */
	protected $default = 0.0;

	/**
	 * @param mixed $value
	 * @return mixed
	 *
	 * @since 1.0
	 */
	protected function validateValue( $value ) {
		if ( '' === $value ) {
			return $this->default;
		}

		$value = ValidateUtils::validatePrice( $value, $this->min, $this->max );

		if ( false !== $value ) {
			return round( $value, 2 );
		} else {
			return $this->default;
		}
	}
}
