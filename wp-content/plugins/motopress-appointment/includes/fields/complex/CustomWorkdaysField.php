<?php

namespace MotoPress\Appointment\Fields\Complex;

use MotoPress\Appointment\Fields\AbstractField;
use MotoPress\Appointment\Structures\DatePeriod;
use MotoPress\Appointment\Structures\TimePeriod;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CustomWorkdaysField extends AbstractField {

	/** @since 1.0 */
	const TYPE = 'custom-workdays';

	/**
	 * @var array
	 *
	 * @since 1.0
	 */
	protected $default = array();

	/**
	 * @param mixed $value
	 * @return mixed
	 *
	 * @since 1.0
	 */
	protected function validateValue( $value ) {
		if ( empty( $value ) ) {
			return $this->default;
		}

		$periods = array();

		foreach ( (array) $value as $period ) {
			// Explode '2020-01-25 - 2020-02-10, 10:00 - 14:00' into
			// ['2020-01-25 - 2020-02-10', '10:00 - 14:00']
			$parts = explode( ', ', $period );

			if ( count( $parts ) >= 2 && DatePeriod::validate( $parts[0] ) && TimePeriod::validate( $parts[1] ) ) {
				$periods[] = $period;
			}
		}

		return $periods;
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function renderInput() {

		$hoursOptions = array();

		for ( $i = 0; $i < 24; $i++ ) {

			$hoursOptions[ $i ] = ( 10 > $i ? '0' : '' ) . $i;
		}

		$minutesOptions = array();

		for ( $i = 0; $i < 60; $i++ ) {

			$minutesOptions[ $i ] = ( 10 > $i ? '0' : '' ) . $i;
		}

		$periods = $this->value;
		rsort( $periods );

		$hasItems = ! empty( $periods );

		$output = '<input type="hidden" name="' . esc_attr( $this->inputName ) . '" value="">';

		$output                 .= '<table class="widefat striped mpa-table">';
			$output             .= '<thead>';
				$output         .= '<tr>';
					$output     .= '<th class="row-title column-dates">';
						$output .= esc_html__( 'Day / Period', 'motopress-appointment' );
					$output     .= '</th>';
					$output     .= '<th class="row-title column-time">';
						$output .= esc_html__( 'Working Hours', 'motopress-appointment' );
					$output     .= '</th>';
					$output     .= '<th class="column-actions">';
						$output .= mpa_tmpl_button( esc_html__( 'Add', 'motopress-appointment' ), array( 'class' => 'button button-primary mpa-add-button' ) );
					$output     .= '</th>';
				$output         .= '</tr>';
			$output             .= '</thead>';
			$output             .= '<tbody>';

				$output         .= '<tr class="no-items' . ( $hasItems ? ' mpa-hide' : '' ) . '">';
					$output     .= '<td colspan="3">';
						$output .= esc_html__( 'No items found', 'motopress-appointment' );
					$output     .= '</td>';
				$output         .= '</tr>';

				$output         .= '<tr class="mpa-new-period mpa-hide">';
					$output     .= '<td class="column-dates">';
						$output .= '<input class="mpa-hide" type="hidden">';
					$output     .= '</td>';
					$output     .= '<td class="column-time">';
						$output .= '<div class="mpa-period">' .
						mpa_tmpl_select(
							$hoursOptions,
							0,
							array(
								'class' => 'mpa-period__start-hours',
							)
						) .
						':' .
						mpa_tmpl_select(
							$minutesOptions,
							0,
							array(
								'class' => 'mpa-period__start-minutes',
							)
						) .
						'&nbsp;—&nbsp;' .
						mpa_tmpl_select(
							$hoursOptions,
							0,
							array(
								'class' => 'mpa-period__end-hours',
							)
						) .
						':' .
						mpa_tmpl_select(
							$minutesOptions,
							0,
							array(
								'class' => 'mpa-period__end-minutes',
							)
						) .
						'</div><p><label><input class="mpa-period__all-day" type="checkbox" name="" value="1">&nbsp;' . __( 'All day', 'motopress-appointment' ) . '</label></p>';
					$output     .= '</td>';
					$output     .= '<td class="column-actions">';
						$output .= mpa_tmpl_button( esc_html__( 'Add', 'motopress-appointment' ), array( 'class' => 'button button-primary mpa-add-button' ) );
					$output     .= '</td>';
				$output         .= '</tr>';

		foreach ( $periods as $period ) {
			$rowId = uniqid();

			$periodParts = explode( ', ', $period );
			$datePeriod  = new DatePeriod( $periodParts[0] );
			$timePeriod  = new TimePeriod( $periodParts[1] );

			$output         .= '<tr class="mpa-period" data-id="' . esc_attr( $rowId ) . '">';
				$output     .= '<td class="column-dates">';
					$output .= $datePeriod->toString( 'short' );
				$output     .= '</td>';
				$output     .= '<td class="column-time">';
					$output .= $timePeriod;
				$output     .= '</td>';
				$output     .= '<td class="column-actions">';
					$output .= '<input type="hidden" name="' . esc_attr( $this->inputName ) . '[]" value="' . esc_attr( $period ) . '">';
					$output .= mpa_tmpl_button( esc_html__( 'Remove', 'motopress-appointment' ), array( 'class' => 'button button-secondary mpa-remove-button' ) );
				$output     .= '</td>';
			$output         .= '</tr>';
		}

			$output .= '</tbody>';
		$output     .= '</table>';

		return $output;
	}

	/**
	 * @return bool
	 *
	 * @since 1.0
	 */
	public function isSingle() {
		return false;
	}
}
