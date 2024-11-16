<?php

namespace MotoPress\Appointment\Fields\Complex;

use MotoPress\Appointment\Fields\AbstractField;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.2
 */
class AttributesField extends AbstractField {

	/** @since 1.2 */
	const TYPE = 'attributes';

	/**
	 * @var array
	 *
	 * @since 1.2
	 */
	protected $default = array();

	/**
	 * @param mixed $value
	 * @return array
	 *
	 * @since 1.2
	 */
	protected function validateValue( $value ) {

		if ( '' === $value || ! is_array( $value ) ) {
			return $this->default;
		}

		$attributes = array();

		foreach ( $value as $attribute ) {
			if ( ! isset( $attribute['label'], $attribute['content'], $attribute['link'], $attribute['class'] ) ) {
				continue;
			}

			$label   = sanitize_text_field( trim( $attribute['label'] ) );
			$content = wp_filter_post_kses( trim( $attribute['content'] ) ); // Allow some HTML
			$link    = esc_url_raw( $attribute['link'] );
			$class   = mpa_sanitize_html_classes( $attribute['class'] );

			// Filter empty rows
			if ( ! empty( $label ) || '' !== $content || ! empty( $link ) || ! empty( $class ) ) {
				$attributes[] = compact( 'label', 'content', 'link', 'class' );
			}
		}

		return $attributes;
	}

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	public function renderInput() {

		$hasValues = ! empty( $this->value );

		// Add empty value in case of no rows
		$output = '<input type="hidden" name="' . esc_attr( $this->inputName ) . '" value="">';

		// Add form table
		$output .= '<table class="widefat striped mpa-table-centered' . ( $hasValues ? '' : ' mpa-hide' ) . '">';

			// Add table header
			$output         .= '<thead>';
				$output     .= '<tr>';
					$output .= '<th class="row-title column-label">' . esc_html__( 'Label', 'motopress-appointment' ) . '</th>';
					$output .= '<th class="row-title column-content">' . esc_html__( 'Content', 'motopress-appointment' ) . '</th>';
					$output .= '<th class="row-title column-link">' . esc_html__( 'Link URL', 'motopress-appointment' ) . '</th>';
					$output .= '<th class="row-title column-class">' . esc_html__( 'CSS Class(es)', 'motopress-appointment' ) . '</th>';
					$output .= '<th class="column-actions"></th>';
				$output     .= '</tr>';
			$output         .= '</thead>';

			// Add values
			$output .= '<tbody>';

		foreach ( $this->value as $attribute ) {
			$rowId      = uniqid();
			$namePrefix = "{$this->inputName}[{$rowId}]";

			$output         .= '<tr class="mpa-attribute" data-id="' . esc_attr( $rowId ) . '">';
				$output     .= '<td class="column-label">';
					$output .= '<input type="text" name="' . esc_attr( "{$namePrefix}[label]" ) . '" value="' . esc_attr( $attribute['label'] ) . '" class="large-text">';
				$output     .= '</td>';

				$output     .= '<td class="column-content">';
					$output .= '<input type="text" name="' . esc_attr( "{$namePrefix}[content]" ) . '" value="' . esc_attr( $attribute['content'] ) . '" class="large-text">';
				$output     .= '</td>';

				$output     .= '<td class="column-link">';
					$output .= '<input type="text" name="' . esc_attr( "{$namePrefix}[link]" ) . '" value="' . esc_attr( $attribute['link'] ) . '" class="large-text">';
				$output     .= '</td>';

				$output     .= '<td class="column-class">';
					$output .= '<input type="text" name="' . esc_attr( "{$namePrefix}[class]" ) . '" value="' . esc_attr( $attribute['class'] ) . '" class="large-text">';
				$output     .= '</td>';

				$output     .= '<td class="column-actions">';
					$output .= mpa_tmpl_dashicon( 'trash', 'mpa-remove-button' );
				$output     .= '</td>';
			$output         .= '</tr>';
		} // For each attribute

			$output .= '</tbody>';

		$output .= '</table>';

		// Add controls
		$output     .= '<p class="mpa-controls">';
			$output .= mpa_tmpl_button(
				esc_html__( 'Add New', 'motopress-appointment' ),
				array(
					'class' => 'button button-secondary mpa-add-button',
				)
			);
		$output     .= '</p>';

		return $output;
	}
}
