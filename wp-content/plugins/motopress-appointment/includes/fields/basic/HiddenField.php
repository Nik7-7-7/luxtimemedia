<?php

namespace MotoPress\Appointment\Fields\Basic;

use MotoPress\Appointment\Fields\AbstractField;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.2
 */
class HiddenField extends AbstractField {

	/** @since 1.2 */
	const TYPE = 'hidden';

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	public function renderInput() {
		return '<input' . mpa_tmpl_atts( $this->inputAtts() ) . '>';
	}

	/**
	 * @return array
	 *
	 * @since 1.2
	 */
	protected function inputAtts() {
		return parent::inputAtts() + array(
			'type'  => 'hidden',
			'value' => $this->value,
		);
	}
}
