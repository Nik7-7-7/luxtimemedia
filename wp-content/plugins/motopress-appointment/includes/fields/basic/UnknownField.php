<?php

namespace MotoPress\Appointment\Fields\Basic;

use MotoPress\Appointment\Fields\AbstractField;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * No proper class was found for current field/field type.
 *
 * @since 1.0
 */
class UnknownField extends AbstractField {

	/** @since 1.0 */
	const TYPE = 'unknown';

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function renderLabel() {
		if ( $this->hasLabel() ) {
			return '<label>' . $this->label . '</label>';
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
		return '<p>' . mpa_tmpl_placeholder() . '</p>';
	}
}
