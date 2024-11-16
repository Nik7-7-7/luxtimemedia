<?php

declare(strict_types=1);

namespace MotoPress\Appointment\Fields\Basic;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.11.0
 */
class MultiselectField extends SelectField {

	const TYPE = 'multiselect';

	protected $default = array();

	/**
	 * @param mixed $value
	 * @return array
	 */
	protected function validateValue( $value ) {
		if ( '' === $value || ( is_array( $value ) && empty( $value ) ) ) {
			return array();
		} elseif ( ! is_array( $value ) ) {
			return $this->default;
		}

		$value = array_map( 'sanitize_text_field', $value );
		$value = array_map( 'mpa_maybe_intval', $value ); // If it's IDs, then convert them to int

		return $value;
	}

	/**
	 * @return string
	 */
	public function renderInput() {
		$output  = '<input type="hidden" name="' . esc_attr( $this->inputName ) . '" value="">';
		$output .= mpa_tmpl_select( $this->options, $this->value, $this->inputAtts() );

		return $output;
	}

	/**
	 * @return array
	 */
	protected function inputAtts() {
		$inputAtts          = parent::inputAtts();
		$inputAtts['name'] .= '[]';

		$inputAtts += array(
			'multiple' => 'multiple',
		);

		return $inputAtts;
	}
}
