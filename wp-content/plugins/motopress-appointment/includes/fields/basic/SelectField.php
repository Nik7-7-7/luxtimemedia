<?php

namespace MotoPress\Appointment\Fields\Basic;

use MotoPress\Appointment\Fields\AbstractField;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class SelectField extends AbstractField {

	/** @since 1.0 */
	const TYPE = 'select';

	/**
	 * @var array
	 *
	 * @since 1.0
	 */
	protected $options = array();

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function mapFields() {
		return array_merge(
			parent::mapFields(),
			array(
				'options' => 'options',
			)
		);
	}

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

		$value = sanitize_text_field( $value );
		$value = mpa_maybe_intval( $value ); // If it's ID, then convert it to int

		return $value;
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function renderInput() {
		if ( array_key_exists( $this->value, $this->options ) ) {
			$selectedItem = $this->value;
		} else {
			$selectedItem = mpa_first_key( $this->options );
		}

		return mpa_tmpl_select( $this->options, $selectedItem, $this->inputAtts() );
	}
}
