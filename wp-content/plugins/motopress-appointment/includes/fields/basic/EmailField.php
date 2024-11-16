<?php

namespace MotoPress\Appointment\Fields\Basic;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.1.0
 */
class EmailField extends TextField {

	/** @since 1.1.0 */
	const TYPE = 'email';

	/**
	 * @param mixed $value
	 * @return mixed
	 *
	 * @since 1.1.0
	 */
	protected function validateValue( $value ) {
		if ( '' === $value || ! is_email( $value ) ) {
			return $this->default;
		} else {
			return sanitize_email( $value );
		}
	}

	/**
	 * @return array
	 *
	 * @since 1.1.0
	 */
	protected function inputAtts() {
		return array_merge(
			parent::inputAtts(),
			array(
				'type' => 'email',
			)
		);
	}
}
