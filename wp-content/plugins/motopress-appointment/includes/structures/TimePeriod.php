<?php

namespace MotoPress\Appointment\Structures;

use DateTime;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Usage:
 *     <pre>new TimePeriod(string $period);</pre>
 *     <pre>new TimePeriod(DateTime|string $startTime, DateTime|string $endTime);</pre>
 * where $period is a string like '08:00 - 14:00'.
 *
 * @since 1.0
 */
class TimePeriod {

	/** @since 1.0 */
	const PERIOD_PATTERN = '/^%start_time% - %end_time%$/';

	/**
	 * @var DateTime
	 * @todo make this field private after woo plugin will be changed
	 * @deprecated Do not use this field directly use getStartTime() instead.
	 */
	public $startTime = null;

	/**
	 * @var DateTime
	 * @todo make this field private after woo plugin will be changed
	 * @deprecated Do not use this field directly use getEndTime() instead.
	 */
	public $endTime = null;

	/**
	 * See variants in the class description.
	 *
	 * @param DateTime|string $timeOrPeriod
	 * @param DateTime|string $endTime Optional. Null by default.
	 *
	 * @since 1.0
	 */
	public function __construct( $timeOrPeriod, $endTime = null ) {
		if ( is_null( $endTime ) ) {
			$this->parsePeriod( $timeOrPeriod );
		} else {
			$this->setStartTime( $timeOrPeriod );
			$this->setEndTime( $endTime );
		}
	}

	/**
	 * @param string $period
	 *
	 * @since 1.0
	 */
	protected function parsePeriod( $period ) {
		// Explode '08:00 - 14:00' into ['08:00', '14:00']
		$time = explode( ' - ', $period );

		$this->setStartTime( $time[0] );
		$this->setEndTime( $time[1] );
	}

	/**
	 * @param DateTime|string $startTime
	 */
	public function setStartTime( $startTime ) {

		if ( is_string( $startTime ) ) {
			$this->startTime = mpa_parse_time( $startTime );
		} else {
			$this->startTime = clone $startTime;
		}
	}

	/**
	 * @param DateTime|string $endTime
	 */
	public function setEndTime( $endTime ) {

		if ( is_string( $endTime ) ) {
			$this->endTime = mpa_parse_time( $endTime );
		} else {
			$this->endTime = clone $endTime;
		}

		// if endTime = 00:00 hour than it could be a full day rental
		if ( '00:00' === $this->endTime->format( 'H:i' ) &&
			// IMPORTANT: we assume startTime is set before endTime!
			$this->getStartTime()->format( 'd' ) === $this->endTime->format( 'd' )
		) {
			$this->endTime->modify( '+1 day' );
		}
	}

	/**
	 * @param DateTime $date
	 *
	 * @since 1.0
	 */
	public function setDate( $date ) {

		$this->startTime->setDate( (int) $date->format( 'Y' ), (int) $date->format( 'm' ), (int) $date->format( 'd' ) );
		$this->endTime->setDate( (int) $date->format( 'Y' ), (int) $date->format( 'm' ), (int) $date->format( 'd' ) );

		// if endTime = 00:00 hour than it could be a full day rental
		if ( '00:00' === $this->endTime->format( 'H:i' ) &&
			// IMPORTANT: we assume startTime is set before endTime!
			$this->getStartTime()->format( 'd' ) === $this->endTime->format( 'd' )
		) {
			$this->endTime->modify( '+1 day' );
		}
	}

	/**
	 * @since 1.13.0
	 */
	public function getStartTime(): DateTime {
		return clone $this->startTime;
	}

	/**
	 * @since 1.13.0
	 */
	public function getEndTime(): DateTime {
		return clone $this->endTime;
	}

	/**
	 * @since 1.13.0
	 */
	public function isInPeriod( DateTime $date ): bool {
		return $date >= $this->getStartTime() && $date <= $this->getEndTime();
	}

	/**
	 * @param self $period
	 * @param string 'datetime'|'time' $period Optional. 'datetime' by default.
	 * @return bool
	 *
	 * @since 1.2.1
	 * @since 1.13.0 added the <code>$compare</code> argument.
	 */
	public function intersectsWith( $period, $compare = 'datetime' ) {

		if ( 'time' === $compare ) {
			return mpa_format_time( $this->getStartTime(), 'internal' ) < mpa_format_time( $period->getEndTime(), 'internal' )
				&& mpa_format_time( $this->getEndTime(), 'internal' ) > mpa_format_time( $period->getStartTime(), 'internal' );
		} else {
			return $this->getStartTime() < $period->getEndTime()
				&& $this->getEndTime() > $period->getStartTime();
		}
	}

	/**
	 * @param self $period
	 * @return bool
	 *
	 * @since 1.2.1
	 */
	public function isSubperiodOf( $period ) {
		return $this->getStartTime() >= $period->getStartTime()
			&& $this->getEndTime() <= $period->getEndTime();
	}

	/**
	 * @since 2.0.0
	 */
	public function isEqualTo( self $period ): bool {
		return $this->getStartTime() == $period->getStartTime()
			&& $this->getEndTime() == $period->getEndTime();
	}

	/**
	 * @return bool
	 *
	 * @since 1.2.1
	 */
	public function isEmpty() {
		return $this->getDuration() <= 0;
	}

	/**
	 * @return int Duration time in minutes.
	 *
	 * @since 1.0
	 */
	public function getDuration() {
		return (int) ( abs( $this->getEndTime()->getTimestamp() - $this->getStartTime()->getTimestamp() ) / 60 );
	}

	/**
	 * @param int $startOffset Minutes offset for start time.
	 * @param int $endOffset Minutes offset for end time.
	 *
	 * @since 1.0
	 */
	public function expand( $startOffset, $endOffset ) {
		$this->startTime->setTimestamp( $this->getStartTime()->getTimestamp() - $startOffset * 60 );
		$this->endTime->setTimestamp( $this->getEndTime()->getTimestamp() + $endOffset * 60 );
	}

	/**
	 * @param self $period
	 *
	 * @since 1.2
	 */
	public function mergePeriod( $period ) {
		if ( mpa_date_diff( $this->getStartTime(), $period->getStartTime() ) < 0 ) {
			$this->setStartTime( clone $period->getStartTime() );
		}

		if ( mpa_date_diff( $this->getEndTime(), $period->getEndTime() ) > 0 ) {
			$this->setEndTime( clone $period->getEndTime() );
		}
	}

	/**
	 * @param self[] $periods
	 *
	 * @since 1.2
	 */
	public function mergePeriods( $periods ) {
		foreach ( $periods as $period ) {
			$this->mergePeriod( $period );
		}
	}

	/**
	 * @param self $period
	 *
	 * @since 1.2.1
	 */
	public function diffPeriod( $period ) {
		if ( $this->getStartTime() < $period->getStartTime() ) {
			if ( $period->getStartTime() < $this->getEndTime() ) {
				$this->setEndTime( $period->getStartTime() );
			}
		} else {
			if ( $period->getEndTime() > $this->getStartTime() ) {
				$this->setStartTime( $period->getEndTime() );
			}
		}
	}

	/**
	 * @param self $period
	 * @return self[]
	 *
	 * @since 1.2.1
	 */
	public function splitByPeriod( $period ) {
		$split = array();

		if ( $period->getStartTime()->getTimestamp() - $this->getStartTime()->getTimestamp() > 0 ) {
			$split[] = new self( $this->getStartTime(), $period->getStartTime() );
		}

		if ( $this->getEndTime()->getTimestamp() - $period->getEndTime()->getTimestamp() > 0 ) {
			$split[] = new self( $period->getEndTime(), $this->getEndTime() );
		}

		return $split;
	}

	/**
	 * @param string $format Optional. 'public', 'short', 'internal' or custom
	 *     time format. 'public' by default.
	 * @param string $glue Optional. ' - ' by default.
	 * @return string
	 *
	 * @since 1.0
	 */
	public function toString( $format = 'public', $glue = ' - ' ) {
		// Force glue ' - ' for internal values
		if ( 'internal' === $format ) {
			$glue = ' - ';
		}

		// mpa_format_time() does not support format 'short'
		$timeFormat = 'short' === $format ? 'public' : $format;
		$startTime  = mpa_format_time( $this->getStartTime(), $timeFormat );
		$endTime    = mpa_format_time( $this->getEndTime(), $timeFormat );

		if ( 'internal' !== $format &&
			// format date time in case if we have AM/PM format settings in WordPress
			'00:00' === $this->getStartTime()->format( 'H:i' ) &&
			'00:00' === $this->getEndTime()->format( 'H:i' ) ) {

			return __( 'All day', 'motopress-appointment' );

		} elseif ( 'short' === $format && $startTime === $endTime ) {

			return $startTime;

		} else {

			return $startTime . $glue . $endTime;
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
		$this->setStartTime( clone $this->getStartTime() );
		$this->setEndTime( clone $this->getEndTime() );
	}

	/**
	 * @param string $period
	 * @return string|false Valid string or false.
	 *
	 * @since 1.0
	 */
	public static function validate( $period ) {
		$periodPattern = str_replace(
			array( '%start_time%', '%end_time%' ),
			'\\d{2}:\\d{2}',
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
