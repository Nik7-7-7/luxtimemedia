<?php

namespace MotoPress\Appointment\Fields\Basic;

use MotoPress\Appointment\Fields\AbstractField;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.1.0
 */
class CheckboxField extends AbstractField {

	/** @since 1.1.0 */
	const TYPE = 'checkbox';

	/**
	 * @var string
	 *
	 * @since 1.1.0
	 */
	public $label2 = '';

	/**
	 * @var bool
	 *
	 * @since 1.1.0
	 */
	protected $default = false;

	/**
	 * @return array
	 *
	 * @since 1.1.0
	 */
	protected function mapFields() {
		return parent::mapFields() + array(
			'label2' => 'label2',
		);
	}

	/**
	 * @param mixed $value
	 * @return bool
	 *
	 * @since 1.1.0
	 */
	protected function validateValue( $value ) {
		if ( '' === $value ) {
			return $this->default;
		}

		return boolval( $value );
	}

	/**
	 * @param string $context Optional. 'internal' by default. See more variants
	 *     in parent class.
	 * @return bool|int
	 *
	 * @since 1.1.0
	 */
	public function getValue( $context = 'internal' ) {
		if ( 'save' == $context ) {
			return (int) $this->value;
		} else {
			return parent::getValue( $context );
		}
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function hasInnerLabel() {
		return '' !== $this->label2;
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function renderInput() {
		$output  = '<input type="hidden" name="' . esc_attr( $this->inputName ) . '" value="0">';
		$output .= '<input' . mpa_tmpl_atts( $this->inputAtts() ) . '>';

		$output .= $this->renderInnerLabel();

		return $output;
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function renderInnerLabel() {
		// Tip: use '&nbsp;' to output an empty label
		if ( $this->hasInnerLabel() ) {
			return '&nbsp;<label for="' . esc_attr( $this->inputId ) . '">' . esc_html( $this->label2 ) . '</label>';
		} else {
			return '';
		}
	}

	/**
	 * @return array
	 *
	 * @since 1.1.0
	 */
	protected function inputAtts() {
		$atts = parent::inputAtts() + array(
			'type'  => 'checkbox',
			'value' => 1,
		);

		if ( true == $this->value ) {
			$atts['checked'] = 'checked';
		}

		return $atts;
	}
}
