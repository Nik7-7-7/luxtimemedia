<?php

namespace MotoPress\Appointment\Fields\Basic;

use MotoPress\Appointment\Fields\AbstractField;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.1.0
 */
class TextareaField extends AbstractField {

	/** @since 1.1.0 */
	const TYPE = 'textarea';

	/**
	 * @var int
	 *
	 * @since 1.1.0
	 */
	public $rows = 0;

	/**
	 * @var string
	 *
	 * @since 1.1.0
	 */
	public $placeholder = '';

	/**
	 * @return array
	 *
	 * @since 1.1.0
	 */
	protected function mapFields() {
		return parent::mapFields() + array(
			'rows'        => 'rows',
			'placeholder' => 'placeholder',
		);
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 *
	 * @since 1.1.0
	 */
	protected function validateValue( $value ) {

		if ( '' === $value ) {
			return $this->default;
		} else {
			return $value;
		}
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function renderInput() {
		return '<textarea' . mpa_tmpl_atts( $this->inputAtts() ) . '>' . esc_textarea( $this->value ) . '</textarea>';
	}

	/**
	 * @return array
	 *
	 * @since 1.1.0
	 */
	protected function inputAtts() {
		$atts = parent::inputAtts();

		if ( $this->rows > 0 ) {
			$atts['rows'] = $this->rows;
		}

		if ( ! empty( $this->placeholder ) ) {
			$atts['placeholder'] = $this->placeholder;
		}

		return $atts;
	}
}
