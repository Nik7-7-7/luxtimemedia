<?php

namespace MotoPress\Appointment\Fields\Basic;

use MotoPress\Appointment\Fields\AbstractField;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.2
 */
class RadioField extends AbstractField {

	/** @since 1.2 */
	const TYPE = 'radio';

	/**
	 * @var array
	 *
	 * @since 1.2
	 */
	protected $options = array();

	/**
	 * @return array
	 *
	 * @since 1.2
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
	 * @since 1.2
	 */
	protected function validateValue( $value ) {
		if ( '' === $value ) {
			return $this->default;
		}

		$value = sanitize_text_field( $value );
		$value = mpa_maybe_intval( $value ); // If it's ID, then convert it to int

		if ( array_key_exists( $value, $this->options ) ) {
			return $value;
		} else {
			return $this->default;
		}
	}

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	public function renderInput() {
		if ( array_key_exists( $this->value, $this->options ) ) {
			$selectedItem = $this->value;
		} else {
			$selectedItem = mpa_first_key( $this->options );
		}

		$output = '<fieldset>';

		foreach ( $this->options as $value => $label ) {
			// Prepare atts
			$atts = array(
				'type'  => 'radio',
				'id'    => mpa_tmpl_id( "{$this->inputId}-{$value}" ),
				'name'  => $this->inputName,
				'value' => $value,
			);

			if ( $value == $selectedItem ) {
				$atts['checked'] = 'checked';
			}

			// Render button
			$output     .= '<label>';
				$output .= '<input' . mpa_tmpl_atts( $atts ) . '>';
				$output .= '<span>' . esc_html( $label ) . '</span>';
			$output     .= '</label>';

			$output .= '<br>';
		}

			// Remove last <br />
			$output = substr( $output, 0, -4 );

		$output .= '</fieldset>';

		return $output;
	}
}
