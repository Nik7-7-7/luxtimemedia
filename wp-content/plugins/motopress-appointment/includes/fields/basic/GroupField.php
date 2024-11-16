<?php

namespace MotoPress\Appointment\Fields\Basic;

use MotoPress\Appointment\Fields\AbstractField;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.1.0
 */
class GroupField extends AbstractField {

	/** @since 1.1.0 */
	const TYPE = 'group';

	/**
	 * @var array [Link URL => Label]
	 *
	 * @since 1.1.0
	 */
	public $titleActions = array();

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function mapFields() {
		return parent::mapFields() + array(
			'title_actions' => 'titleActions',
		);
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function renderLabel() {
		if ( $this->hasLabel() ) {
			// Add title
			$atts = array(
				'id'    => $this->inputId . '-title',
				'class' => 'mpa-fields-group-title',
			);

			if ( ! empty( $this->titleActions ) ) {
				$atts['class'] .= ' wp-heading-inline';
			}

			$groupLabelWithAnchorLink = sprintf(
				'%1$s <a class="mpa-anchor-link" id="%2$s" href="%3$s">#</a>',
				esc_html( $this->label ),
				esc_attr( $this->inputId . '_link' ),
				esc_url( add_query_arg( array() ) . '#' . $this->inputId . '_link' )
			);

			$output = '<h2' . mpa_tmpl_atts( $atts ) . '>' . $groupLabelWithAnchorLink . '</h2>';

			// Add title actions
			if ( ! empty( $this->titleActions ) ) {
				foreach ( $this->titleActions as $href => $actionLabel ) {
					$output .= mpa_tmpl_link( $href, $actionLabel, array( 'class' => 'page-title-action' ) );
				}
			}

			return $output;

		} else {
			return '';
		}
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function renderInput() {
		return '';
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function renderDescription() {
		if ( $this->hasDescription() ) {
			$atts = array(
				'id'    => $this->inputId . '-description',
				'class' => 'mpa-fields-group-description',
			);

			return '<div' . mpa_tmpl_atts( $atts ) . '>'
					. '<p>' . $this->description . '</p>'
				. '</div>';

		} else {
			return '';
		}
	}
}
