<?php

namespace MotoPress\Appointment\Fields\Complex;

use MotoPress\Appointment\Fields\Basic\SelectField;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.7.0
 */
class EmployeeUserField extends SelectField {

	/** @since 1.7.0 */
	const TYPE = 'employee-user';

	/**
	 * @param string $inputName Prefixed name.
	 * @param array $args
	 * @param mixed $value Optional. Null by default.
	 *
	 * @since 1.7.0
	 */
	public function __construct( $inputName, $args, $value = null ) {
		parent::__construct( $inputName, $args, $value );

		add_action(
			'admin_enqueue_scripts',
			function() {

				wp_localize_script(
					'jquery',
					'mpaUserMetaboxSettings',
					array(
						'root'  => esc_url_raw( rest_url() ),
						'nonce' => wp_create_nonce( 'wp_rest' ),
					)
				);
			}
		);
	}

	/**
	 * @return array
	 *
	 * @since 1.7.0
	 */
	protected function inputAtts() {

		$atts = parent::inputAtts() + array(
			'list'  => $this->inputId . '_data',
			'value' => $this->value,
		);

		return $atts;
	}

	/**
	 * @since 1.7.0
	 */
	public function renderInput(): string {

		$output = '<input ' . mpa_tmpl_atts( $this->inputAtts() ) . '>
            <datalist id="' . esc_attr( $this->inputId ) . '_data">';

		foreach ( $this->options as $optionValue ) {

			$output .= '<option value="' . esc_attr( $optionValue ) . '"></option>';
		}

		$output .= '</datalist>';

		return $output;
	}
}
