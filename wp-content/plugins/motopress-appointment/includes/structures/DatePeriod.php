<?php

namespace MotoPress\Appointment\Structures;

use DateTime;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Usage:
 *     <pre>new DatePeriod(string $period);</pre>
 *     <pre>new DatePeriod(DateTime|string $startDate, DateTime|string $endDate);</pre>
 * where $period is a string like '2020-01-25 - 2020-02-10' (or '2020-01-25 -
 * 2020-01-25' - even if the dates are the same).
 *
 * @since 1.0
 */
class DatePeriod {

	/** @since 1.0 */
	const PERIOD_PATTERN = '/^%start_date% - %end_date%$/';

	/**
	 * @var DateTime
	 *
	 * @since 1.0
	 */
	public $startDate = null;

	/**
	 * @var DateTime
	 *
	 * @since 1.0
	 */
	public $endDate = null;

	/**
	 * See variants in the class description.
	 *
	 * @param DateTime|string $dateOrPeriod
	 * @param DateTime|string $endDate Optional. Null by default.
	 *
	 * @since 1.0
	 */
	public function __construct( $dateOrPeriod, $endDate = null ) {
		if ( is_null( $endDate ) ) {
			$this->parsePeriod( $dateOrPeriod );
		} else {
			$this->setStartDate( $dateOrPeriod );
			$this->setEndDate( $endDate );
		}
	}

	/**
	 * @param string $period
	 *
	 * @since 1.0
	 */
	protected function parsePeriod( $period ) {
		// Explode '2020-01-25 - 2020-02-10' into ['2020-01-25', '2020-02-10']
		$dates = explode( ' - ', $period );

		$this->setStartDate( $dates[0] );
		$this->setEndDate( $dates[1] );
	}

	/**
	 * @param DateTime|string $startDate
	 *
	 * @since 1.0
	 */
	public function setStartDate( $startDate ) {
		$this->startDate = $this->convertToDate( $startDate );
	}

	/**
	 * @param DateTime|string $endDate
	 *
	 * @since 1.0
	 */
	public function setEndDate( $endDate ) {
		$this->endDate = $this->convertToDate( $endDate );
	}

	/**
	 * @param DateTime|string $input
	 * @return DateTime
	 *
	 * @since 1.0
	 */
	protected function convertToDate( $input ) {
		if ( is_string( $input ) ) {
			return mpa_parse_date( $input );
		} else {
			return $input;
		}
	}

	/**
	 * @return int
	 *
	 * @since 1.0
	 */
	public function calcDays() {
		$diff = $this->startDate->diff( $this->endDate );

		// See https://www.php.net/manual/en/dateinterval.format.php
		//     %r - negative sign (only when negative)
		//     %a - total number of days
		return (int) $diff->format( '%r%a' );
	}

	/**
	 * @param DateTime|string $date
	 * @return bool
	 *
	 * @since 1.0
	 */
	public function inPeriod( $date ) {
		$date = mpa_parse_date( $date );

		return $date >= $this->startDate && $date <= $this->endDate;
	}

	/**
	 * @since 1.2.2
	 *
	 * @return DateTime[] ['Y-m-d' => DateTime]
	 */
	public function splitToDates() {
		$startDate = clone $this->startDate;
		$startDate->setTime( 0, 0 );

		$endDate = clone $this->endDate;
		$endDate->setTime( 0, 0 );

		$dates = array();

		for ( $date = clone $startDate; $date <= $endDate; $date->modify( '+1 day' ) ) {
			$dateStr           = mpa_format_date( $date, 'internal' );
			$dates[ $dateStr ] = clone $date;
		}

		return $dates;
	}

	/**
	 * @param string Optional. 'public', 'short', 'internal' or custom date
	 *     format. 'public' by default.
	 * @param string Optional. ' - ' by default.
	 * @return string
	 *
	 * @since 1.0
	 */
	public function toString( $format = 'public', $glue = ' - ' ) {
		// Force glue ' - ' for internal values
		if ( 'internal' == $format ) {
			$glue = ' - ';
		}

		// mpa_format_date() does not support format 'short'
		$dateFormat = 'short' == $format ? 'public' : $format;
		$startDate  = mpa_format_date( $this->startDate, $dateFormat );
		$endDate    = mpa_format_date( $this->endDate, $dateFormat );

		if ( 'short' == $format && $startDate == $endDate ) {
			return $startDate;
		} else {
			return $startDate . $glue . $endDate;
		}
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function __toString() {
		return $this->toString();
	}

	/**
	 * @since 1.0
	 */
	public function __clone() {
		$this->startDate = clone $this->startDate;
		$this->endDate   = clone $this->endDate;
	}

	/**
	 * @param string $period
	 * @return string|false Valid string or false.
	 *
	 * @since 1.0
	 */
	public static function validate( $period ) {
		$periodPattern = str_replace(
			array( '%start_date%', '%end_date%' ),
			mpa_validate_date_pattern(),
			static::PERIOD_PATTERN
		);

		if ( (bool) preg_match( $periodPattern, $period ) ) {
			return $period;
		} else {
			return false;
		}
	}

	/**
	 * @param string $period
	 * @return static|null
	 *
	 * @since 1.0
	 */
	public static function createFromPeriod( $period ) {
		$validPeriod = static::validate( $period );

		if ( false !== $validPeriod ) {
			return new static( $validPeriod );
		} else {
			return null;
		}
	}
}
