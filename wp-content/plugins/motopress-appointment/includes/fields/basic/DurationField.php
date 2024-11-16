<?php

namespace MotoPress\Appointment\Fields\Basic;

use MotoPress\Appointment\Fields\AbstractField;
use MotoPress\Appointment\Utils\ValidateUtils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class DurationField extends AbstractField {

	/** @since 1.0 */
	const TYPE = 'duration';

	/** @since 1.0 */
	const MIN = 0;

	/** @since 1.0 */
	const MAX = 1440; // Up to 24 hours

	/**
	 * @var int
	 *
	 * @since 1.0
	 */
	protected $default = 0;

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

		$value = ValidateUtils::validateInt( $value, static::MIN, static::MAX );

		if ( false !== $value ) {
			return $value;
		} else {
			return $this->default;
		}
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function renderInput() {
		$durations = mpa_time_durations( static::MIN, static::MAX );

		return mpa_tmpl_select( $durations, $this->value, $this->inputAtts() );
	}
}
