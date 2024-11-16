<?php

declare(strict_types=1);

namespace MotoPress\Appointment\Fields\Basic;

use MotoPress\Appointment\Fields\AbstractField;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.11.0
 */
class DateField extends AbstractField {

	const TYPE = 'date';

	/**
	 * @var string "Y-m-d" date
	 */
	protected $default = '';

	/**
	 * @param mixed $value
	 * @return array
	 */
	protected function validateValue( $value ) {
		$validDate = mpa_validate_date( $value );

		if ( false !== $validDate ) {
			return $validDate;
		} else {
			return $this->default;
		}
	}

	/**
	 * @return string
	 */
	public function renderInput() {
		return '<input type="hidden"' . mpa_tmpl_atts( $this->inputAtts() ) . '>'
			. ' ' . mpa_tmpl_preloader();
	}

	/**
	 * @return array
	 */
	protected function inputAtts() {
		$inputAtts = parent::inputAtts();

		$inputAtts += array(
			'value' => $this->value,
		);

		$inputAtts['class'] = ltrim( $inputAtts['class'] . ' mpa-date-input' );

		return $inputAtts;
	}

	/**
	 * @return array
	 */
	protected function controlAtts() {
		return parent::controlAtts() + array(
			'data-display-format' => mpapp()->settings()->getDateFormat(),
			'data-size'           => $this->sizeClass,
		);
	}
}
