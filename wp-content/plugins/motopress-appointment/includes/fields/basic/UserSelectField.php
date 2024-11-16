<?php

namespace MotoPress\Appointment\Fields\Basic;

use MotoPress\Appointment\Fields\AbstractField;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.18.0
 */
class UserSelectField extends AbstractField {
	const TYPE = 'user-select';

	/**
	 * @var int[]|array An array of user_id's which need exclude from the field.
	 */
	protected $exclude = array();

	protected function mapFields() {
		return parent::mapFields() + array( 'exclude' => 'exclude' );
	}

	protected function getExclude() {
		if ( ! is_array( $this->exclude ) ) {
			return array();
		}

		return $this->exclude;
	}

	/**
	 * @return string
	 */
	public function renderInput(): string {
		$atts = array(
			'selected' => $this->getValue(),
			'exclude'  => $this->getExclude(),
		);
		$args = $this->inputAtts() + $atts;

		return mpa_tmpl_user_select( $args );
	}
}