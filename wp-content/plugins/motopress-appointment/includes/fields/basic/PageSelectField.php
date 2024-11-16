<?php

namespace MotoPress\Appointment\Fields\Basic;

use MotoPress\Appointment\Fields\AbstractField;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.5.0
 */
class PageSelectField extends AbstractField {

	/** @since 1.5.0 */
	const TYPE = 'page-select';

	/**
	 * @since 1.5.0
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	protected function validateValue( $value ) {
		if ( '' === $value ) {
			return $this->default;
		}

		$value = sanitize_text_field( $value );

		// If it's ID, then convert it to int
		$value = mpa_maybe_intval( $value );

		// Check if the post exists
		$value = get_post( $value ) ? $value : $this->default;

		return $value;
	}

	/**
	 * @since 1.5.0
	 *
	 * @return string
	 */
	public function renderInput() {
		$args = $this->inputAtts() + array( 'selected' => $this->getValue() );

		return mpa_tmpl_page_select( $args );
	}
}
