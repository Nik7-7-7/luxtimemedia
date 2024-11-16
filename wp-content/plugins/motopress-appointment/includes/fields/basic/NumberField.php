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
class NumberField extends AbstractField {

	/** @since 1.0 */
	const TYPE = 'number';

	/**
	 * @var int|false
	 *
	 * @since 1.0
	 */
	public $min = 0;

	/**
	 * @var int|false
	 *
	 * @since 1.0
	 */
	public $max = false;

	/**
	 * @var int|false
	 *
	 * @since 1.0
	 */
	public $step = 1;

	/**
	 * @var int
	 *
	 * @since 1.0
	 */
	protected $default = 0;

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function mapFields() {
		return array_merge(
			parent::mapFields(),
			array(
				'min'  => 'min',
				'max'  => 'max',
				'step' => 'step',
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

		$value = ValidateUtils::validateFloat( $value, $this->min, $this->max );

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
		return '<input' . mpa_tmpl_atts( $this->inputAtts() ) . '>';
	}

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function inputAtts() {
		return array_merge(
			parent::inputAtts(),
			array(
				'type'  => 'number',
				'value' => $this->value,
			),
			$this->rangeAtts()
		);
	}

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function rangeAtts() {
		$atts = array();

		if ( false !== $this->min ) {
			$atts['min'] = $this->min;
		}

		if ( false !== $this->max ) {
			$atts['max'] = $this->max;
		}

		if ( false !== $this->step ) {
			$atts['step'] = $this->step;
		}

		return $atts;
	}
}
