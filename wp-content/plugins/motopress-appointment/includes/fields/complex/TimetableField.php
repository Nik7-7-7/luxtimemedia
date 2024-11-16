<?php

namespace MotoPress\Appointment\Fields\Complex;

use MotoPress\Appointment\Fields\AbstractField;

use const MotoPress\Appointment\ACTIVITY_WORK;
use const MotoPress\Appointment\ACTIVITY_LUNCH;
use const MotoPress\Appointment\ACTIVITY_BREAK;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class TimetableField extends AbstractField {

	/** @since 1.0 */
	const TYPE = 'timetable';

	/**
	 * @var array
	 *
	 * @since 1.0
	 */
	protected $default = array();

	/**
	 * @var array
	 *
	 * @since 1.0
	 */
	protected $activities = array();

	/**
	 * @var array
	 *
	 * @since 1.0
	 */
	protected $daysOfWeek = array();

	/**
	 * @param string $name
	 * @param array $args
	 * @param mixed $value Optional. Null by default.
	 *
	 * @since 1.0
	 */
	public function __construct( $name, $args, $value = null ) {
		/** @since 1.0 */
		$this->activities = apply_filters(
			'mpa_employee_activities',
			array(
				ACTIVITY_WORK  => esc_html__( 'Working hours', 'motopress-appointment' ),
				ACTIVITY_LUNCH => esc_html__( 'Lunchtime', 'motopress-appointment' ),
				ACTIVITY_BREAK => esc_html_x( 'Break', 'A pause in work', 'motopress-appointment' ),
			)
		);

		$this->daysOfWeek = array(
			'monday'    => esc_html__( 'Monday' ), // Here and below are core texts
			'tuesday'   => esc_html__( 'Tuesday' ),
			'wednesday' => esc_html__( 'Wednesday' ),
			'thursday'  => esc_html__( 'Thursday' ),
			'friday'    => esc_html__( 'Friday' ),
			'saturday'  => esc_html__( 'Saturday' ),
			'sunday'    => esc_html__( 'Sunday' ),
		);

		parent::__construct( $name, $args, $value );
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 *
	 * @since 1.0
	 */
	protected function validateValue( $value ) {

		if ( '' === $value || ! is_array( $value ) ) {
			return $this->default;
		}

		$daysOfWeek = array_keys( $this->daysOfWeek );
		$activities = array_keys( $this->activities );

		$periods = array();

		foreach ( $value as $period ) {
			if ( ! isset( $period['day'], $period['start'], $period['end'] ) ) {
				continue;
			}

			$day = $period['day'];

			if ( ! in_array( $day, $daysOfWeek ) ) {
				continue;
			}

			$start = mpa_posint( $period['start'] );
			$end   = mpa_posint( $period['end'] );

			if ( isset( $period['activity'] ) && in_array( $period['activity'], $activities ) ) {
				$activity = $period['activity'];
			} else {
				$activity = ACTIVITY_WORK;
			}

			$dayActivity = array(
				'day'      => $day,
				'start'    => $start,
				'end'      => $end,
				'activity' => $activity,
			);

			if ( ! empty( $period['location'] ) ) {
				$dayActivity['location'] = mpa_posint( $period['location'] ); // It's ID
			}

			$periods[] = $dayActivity;
		}

		return $periods;
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function renderInput() {

		$periods = mpa_array_group_by( $this->value, 'day' );
		$periods = array_merge( array_fill_keys( array_keys( $this->daysOfWeek ), array() ), $periods );

		$output = '<input type="hidden" name="' . esc_attr( $this->inputName ) . '" value="">';

		$output .= '<div class="mpa-days-container">';
		foreach ( $this->daysOfWeek as $day => $label ) {
			$output .= $this->renderColumn( $day, $label, $periods[ $day ] );
		}
		$output .= '</div>';

		$output .= '<hr>';

		$output .= $this->renderEditForm();

		$output     .= '<div class="mpa-controls">';
			$output .= mpa_tmpl_button( _x( 'Add', 'Add item', 'motopress-appointment' ), array( 'class' => 'button button-primary mpa-add-button' ) );
			$output .= mpa_tmpl_button( __( 'Cancel', 'motopress-appointment' ), array( 'class' => 'button mpa-cancel-button' ) );
		$output     .= '</div>';

		return $output;
	}

	/**
	 * @param string $day
	 * @param string $label
	 * @param array $periods
	 * @return string
	 *
	 * @since 1.0
	 */
	protected function renderColumn( $day, $label, $periods ) {

		$output = '<div class="mpa-day-container" data-for="' . esc_attr( $day ) . '">';

			$output     .= '<div class="mpa-day-header">';
				$output .= esc_html( $label );
			$output     .= '</div>';

			$output .= '<div class="mpa-day-periods">';
		foreach ( $periods as $period ) {
			$output .= $this->renderPeriod( $period );
		}
			$output .= '</div>'; // Day periods

		$output .= '</div>'; // Day container

		return $output;
	}

	/**
	 * @param array $period
	 *     @param string $period['day']
	 *     @param string $period['start']
	 *     @param string $period['end']
	 *     @param string $period['activity']
	 *     @param int|'' $period['location'] Optional.
	 * @return string
	 *
	 * @since 1.0
	 */
	protected function renderPeriod( $period ) {

		if ( ! isset( $period['location'] ) ) {
			$period['location'] = '';
		}

		$periodId    = uniqid();
		$inputPrefix = "{$this->inputName}[{$periodId}]";

		$output      = '<div class="mpa-day-period" data-id="' . esc_attr( $periodId ) . '">';
			$output .= mpa_tmpl_hidden( "{$inputPrefix}[day]", $period['day'], array( 'class' => 'mpa-period-day' ) );
			$output .= mpa_tmpl_hidden( "{$inputPrefix}[start]", $period['start'], array( 'class' => 'mpa-period-start' ) );
			$output .= mpa_tmpl_hidden( "{$inputPrefix}[end]", $period['end'], array( 'class' => 'mpa-period-end' ) );
			$output .= mpa_tmpl_hidden( "{$inputPrefix}[activity]", $period['activity'], array( 'class' => 'mpa-period-activity' ) );
			$output .= mpa_tmpl_hidden( "{$inputPrefix}[location]", $period['location'], array( 'class' => 'mpa-period-location' ) );

			$output .= '<span class="mpa-period-time">';

		if ( 0 === $period['start'] && 0 === $period['end'] ) {

			$output .= __( 'All day', 'motopress-appointment' );

		} else {

			$output .= mpa_format_minutes( $period['start'] );
			$output .= '&nbsp;—&nbsp;';
			$output .= mpa_format_minutes( $period['end'] );
		}

			$output .= '</span>';

			$output .= mpa_tmpl_dashicon( 'trash', 'mpa-remove-button' );

			$output .= '<br>';

			$output     .= '<span class="mpa-period-activity">';
				$output .= $this->activities[ $period['activity'] ];
			$output     .= '</span>';

		if ( ! empty( $period['location'] ) && ACTIVITY_WORK === $period['activity'] ) {
			$output .= '<br>';

			$output .= '<span class="mpa-period-location">';
				// Translators: %s: Location name, like "Barbershop".
				$output .= sprintf( esc_html_x( 'at %s', 'Working at %s', 'motopress-appointment' ), mpa_tmpl_edit_post_link( $period['location'] ) );
			$output     .= '</span>';
		}
		$output .= '</div>';

		return $output;
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	protected function renderEditForm() {

		$locations = mpa_no_value() + mpa_get_locations();

		$fields = array();

		$fields['day'] = array(
			'label' => esc_html__( 'Day Of Week', 'motopress-appointment' ) . '&nbsp;' . mpa_tmpl_required(),
			'field' => mpa_tmpl_select( $this->daysOfWeek, 'monday', array( 'class' => 'mpa-day-of-week' ) ),
		);

		$hoursOptions = array();

		for ( $i = 0; $i < 24; $i++ ) {

			$hoursOptions[ $i ] = ( 10 > $i ? '0' : '' ) . $i;
		}

		$minutesOptions = array();

		for ( $i = 0; $i < 60; $i++ ) {

			$minutesOptions[ $i ] = ( 10 > $i ? '0' : '' ) . $i;
		}

		$fields['period'] = array(
			'label' => esc_html__( 'Period Of Time', 'motopress-appointment' ) . '&nbsp;' . mpa_tmpl_required(),
			'field' => '<div class="mpa-period">' .
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
			'</div><p><label><input class="mpa-period__all-day" type="checkbox" name="" value="1">&nbsp;' . __( 'All day', 'motopress-appointment' ) . '</label></p>' .
			'<p class="mpa-error mpa-hide">' . esc_html__( 'End time must be bigger than the start time.' ) . '</p>',
		);

		$fields['activity'] = array(
			'label' => esc_html__( 'Activity Type', 'motopress-appointment' ) . '&nbsp;' . mpa_tmpl_required(),
			'field' => mpa_tmpl_select( $this->activities, ACTIVITY_WORK, array( 'class' => 'mpa-activity' ) ),
		);

		$fields['location'] = array(
			'label' => esc_html__( 'Location', 'motopress-appointment' ),
			'field' => mpa_tmpl_select( $locations, '', array( 'class' => 'mpa-location' ) ),
		);

		return mpa_tmpl_form_table( $fields, array( 'class' => 'mpa-edit-table mpa-hide' ) );
	}
}
