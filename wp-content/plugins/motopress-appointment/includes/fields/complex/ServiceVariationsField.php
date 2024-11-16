<?php

namespace MotoPress\Appointment\Fields\Complex;

use MotoPress\Appointment\Fields\AbstractField;
use MotoPress\Appointment\Utils\ParseUtils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 * @since 1.3.1 added the <code>min_capacity</code> and <code>max_capacity</code> fields.
 */
class ServiceVariationsField extends AbstractField {

	/** @since 1.0 */
	const TYPE = 'service-variations';

	/**
	 * @since 1.0
	 * @var array
	 */
	protected $default = array();

	/**
	 * @since 1.0
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	protected function validateValue( $value ) {

		if ( '' === $value || ! is_array( $value ) ) {
			return $this->default;
		}

		$variations = array();

		foreach ( $value as $variation ) {
			if ( ! isset( $variation['employee'], $variation['price'], $variation['duration'] )
				|| ! isset( $variation['min_capacity'], $variation['max_capacity'] )
			) {
				continue;
			}

			// Parse "employee" and "duration"
			$newVariation = array(
				'employee' => mpa_posint( $variation['employee'] ),
				'duration' => mpa_posint( $variation['duration'] ),
			);

			// Parse "price", "min_capacity" and "max_capacity"
			foreach ( array( 'price', 'min_capacity', 'max_capacity' ) as $field ) {
				$fieldValue = $variation[ $field ];

				if ( '' === $fieldValue ) {
					$newVariation[ $field ] = ''; // Allow '' for this fields (use value from settings)
				} elseif ( 'price' === $field ) {
					$newVariation[ $field ] = ParseUtils::parsePrice( $fieldValue );
				} else {
					$newVariation[ $field ] = mpa_posint( $fieldValue );
				}
			}

			// Add valid variation
			$variations[] = $newVariation;
		}

		return $variations;
	}

	/**
	 * @return string
	 */
	public function renderLabel() {
		return $this->hasLabel() ? '<label>' . esc_html( $this->label ) . '</label>' : '';
	}

	/**
	 * @since 1.0
	 *
	 * @return string
	 */
	public function renderInput() {
		return mpa_render_template(
			'private/fields/service-variations-field.php',
			array(
				'input_name' => $this->inputName,
				'variations' => $this->value,
			)
		);
	}
}
