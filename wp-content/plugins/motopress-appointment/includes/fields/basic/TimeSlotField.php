<?php

declare(strict_types=1);

namespace MotoPress\Appointment\Fields\Basic;

use MotoPress\Appointment\Fields\AbstractField;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.13.0
 */
class TimeSlotField extends AbstractField {

	const TYPE = 'time-slot';

	/**
	 * @var int
	 */
	protected $timeStep = 30;

	/**
	 * @var string
	 */
	protected $default = '00:00';

	/**
	 * @var array [Internal time format => Public time format]
	 */
	protected $options = array();

	/**
	 * @param array $args
	 */
	protected function setupArgs( $args ) {
		$this->timeStep = mpapp()->settings()->getTimeStep();

		parent::setupArgs( $args );

		// Setup time slots
		$lastMinute = 24 * 60 - 1; // Stay in range 00:00 - 23:59
		$timeSlots  = range( 0, $lastMinute, $this->timeStep );

		foreach ( $timeSlots as $timeSlot ) {
			$this->options[ mpa_format_minutes( $timeSlot, 'internal' ) ] = mpa_format_minutes( $timeSlot );
		}
	}

	/**
	 * @return array
	 */
	protected function mapFields() {
		return parent::mapFields() + array(
			'time_step' => 'timeStep',
		);
	}

	/**
	 * @param mixed $value
	 * @return array
	 */
	protected function validateValue( $value ) {
		if ( array_key_exists( $value, $this->options ) ) {
			return $value;
		} else {
			return $this->default;
		}
	}

	/**
	 * @return string
	 */
	public function renderInput() {
		if ( array_key_exists( $this->value, $this->options ) ) {
			$selectedItem = $this->value;
		} else {
			// Pick a close value and don't reset it to '00:00'
			$selectedItem = $this->pickCloseOption( $this->value );
		}

		return mpa_tmpl_select( $this->options, $selectedItem, $this->inputAtts() );
	}

	protected function pickCloseOption( string $value ): string {
		$previousValue = '00:00';

		foreach ( array_keys( $this->options ) as $time ) {
			if ( $time > $value ) {
				return $previousValue;
			} else {
				$previousValue = $time;
			}
		}

		return $previousValue;
	}
}
