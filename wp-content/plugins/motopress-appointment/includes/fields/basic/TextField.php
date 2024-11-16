<?php

namespace MotoPress\Appointment\Fields\Basic;

use MotoPress\Appointment\Fields\AbstractField;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class TextField extends AbstractField {

	/** @since 1.0 */
	const TYPE = 'text';

	/**
	 * @var string
	 *
	 * @since 1.1.0
	 */
	public $placeholder = '';

	/**
	 * @var boolean
	 *
	 * @since 1.23.0
	 */
	public $required = false;

	/**
	 * @var string
	 *
	 * @since 1.23.0
	 */
	public $pattern = '';

	/**
	 * @return array
	 *
	 * @since 1.1.0
	 */
	protected function mapFields() {
		return parent::mapFields() + array(
			'placeholder' => 'placeholder',
			'required'    => 'required',
			'pattern'     => 'pattern',
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
		} else {
			return sanitize_text_field( $value );
		}
	}

	/**
	 * @return string
	 *
	 * @since 1.23.0
	 */
	public function renderLabel() {

		// Tip: use '&nbsp;' to output an empty label
		if ( $this->hasLabel() ) {

			$labelTag = '<label for="' . esc_attr( $this->inputId ) . '">' . esc_html( $this->label ) . '</label>';

			// add required tip
			if ( $this->required ) {
				$labelTag .= ' <strong><abbr title="required">*</abbr></strong>';
			}

			return $labelTag;
		} else {
			return '';
		}
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function renderInput() {
		return '<input' . mpa_tmpl_atts( $this->inputAtts() ) . '>';
	}

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function inputAtts() {
		$atts = parent::inputAtts() + array(
			'type'  => 'text',
			'value' => $this->value,
		);

		if ( ! empty( $this->placeholder ) ) {
			$atts['placeholder'] = $this->placeholder;
		}

		if ( ! empty( $this->required ) ) {
			$atts['required'] = $this->required;
		}

		if ( ! empty( $this->pattern ) ) {
			$atts['pattern'] = $this->pattern;
		}

		return $atts;
	}
}
