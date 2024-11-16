<?php

declare(strict_types=1);

namespace MotoPress\Appointment\Fields\Complex;

use MotoPress\Appointment\Fields\AbstractField;
use MotoPress\Appointment\Utils\ParseUtils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.13.0
 */
class TriggerPeriodField extends AbstractField {

	const TYPE = 'trigger-period';

	const UNIT_HOUR = 'hour';
	const UNIT_DAY  = 'day';

	const DEFAULT_UNIT     = 'day';
	const DEFAULT_OPERATOR = 'before';

	/**
	 * @var int
	 */
	protected $min = 1;

	/**
	 * @var int
	 */
	protected $max = 365;

	/**
	 * @var array
	 */
	protected $default = array(
		'period'   => 1,
		'unit'     => self::DEFAULT_UNIT,
		'operator' => self::DEFAULT_OPERATOR,
	);


	public static function getTriggerOperators(): array {
		return array(
			'before' => esc_html_x( 'before', 'Before some day', 'motopress-appointment' ),
			'after'  => esc_html_x( 'after', 'After some day', 'motopress-appointment' ),
		);
	}

	private function getTriggerUnits(): array {
		return array(
			self::UNIT_DAY  => esc_html_x( 'days', 'Units to trigger the cron', 'motopress-appointment' ),
			self::UNIT_HOUR => esc_html_x( 'hours', 'Units to trigger the cron', 'motopress-appointment' ),
		);
	}

	/**
	 * @param array $trigger - [ period, unit, operator ]
	 * @param string $triggerTime - time in "H:i" format
	 */
	public static function convertTriggerToString( array $trigger, string $triggerTime ): string {

		$operator = static::getTriggerOperators()[ $trigger['operator'] ];

		if ( static::UNIT_DAY === $trigger['unit'] ) {

			$units = esc_html( _n( 'day', 'days', $trigger['period'], 'motopress-appointment' ) );
			$time  = mpa_format_time( mpa_parse_time( $triggerTime ) );

			return sprintf(
				// Translators: 1: Number of days or hours, 2: Units ("days" or "hours"), 3: Trigger operator: "before" or "after", 4: Trigger time: "11:30".
				'%1$d %2$s %3$s the appointment at %4$s',
				$trigger['period'],
				$units,
				$operator,
				$time
			);

		} else {
			$units = esc_html( _n( 'hour', 'hours', $trigger['period'], 'motopress-appointment' ) );

			return sprintf(
				// Translators: 1: Number of days or hours, 2: Units ("days" or "hours"), 3: Trigger operator: "before" or "after".
				'%1$d %2$s %3$s the appointment',
				$trigger['period'],
				$units,
				$operator
			);
		}
	}

	/**
	 * @return array
	 */
	protected function mapFields() {
		return array_merge(
			parent::mapFields(),
			array(
				'min' => 'min',
				'max' => 'max',
			)
		);
	}

	/**
	 * @param mixed $value
	 * @return array
	 */
	protected function validateValue( $value ) {

		if ( ! is_array( $value )
			|| ! isset( $value['period'] )
			|| ! isset( $value['unit'] )
			|| ! isset( $value['operator'] )
		) {
			return $this->default;
		}

		$period   = ParseUtils::parseInt( $value['period'], $this->min, $this->max );
		$unit     = sanitize_text_field( $value['unit'] );
		$operator = sanitize_text_field( $value['operator'] );

		if ( ! array_key_exists( $unit, $this->getTriggerUnits() ) ) {
			$unit = static::DEFAULT_UNIT;
		}

		if ( ! array_key_exists( $operator, static::getTriggerOperators() ) ) {
			$operator = static::DEFAULT_OPERATOR;
		}

		return array(
			'period'   => $period,
			'unit'     => $unit,
			'operator' => $operator,
		);
	}

	/**
	 * @return string
	 */
	public function renderInput() {

		$output  = '<fieldset>';
		$output .= '<input type="number" name="' . esc_attr( $this->inputName . '[period]' ) . '" id="' . esc_attr( $this->inputId . '-period' ) . '" value="' . esc_attr( $this->value['period'] ) . '" min="' . esc_attr( $this->min ) . '" max="' . esc_attr( $this->max ) . '" step="1" />';

		$output .= mpa_tmpl_select(
			$this->getTriggerUnits(),
			$this->value['unit'],
			array(
				'name' => $this->inputName . '[unit]',
				'id'   => $this->inputId . '-unit',
			)
		);

		$output .= mpa_tmpl_select(
			static::getTriggerOperators(),
			$this->value['operator'],
			array(
				'name' => $this->inputName . '[operator]',
				'id'   => $this->inputId . '-operator',
			)
		);

		$output .= ' ' . esc_html_x( 'the appointment', 'X days before the appointment', 'motopress-appointment' );
		$output .= '</fieldset>';

		return $output;
	}
}
