<?php

declare(strict_types=1);

namespace MotoPress\Appointment\Utils;

use DateTime;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.13.0
 */
class DateTimeUtils {

	/**
	 * @param string $format Optional. 'public', 'internal' ('H:i') or custom
	 * time format. 'public' by default.
	 */
	public static function timeNow( string $format = 'public' ): string {
		$now = new DateTime( 'now', wp_timezone() );
		return mpa_format_time( $now, $format );
	}

	/**
	 * @param 'seconds'|'hours'|'days' $units Optional. "seconds" by default.
	 */
	public static function timeToDate( DateTime $date, string $units = 'seconds' ): float {
		$now           = new DateTime( 'now', wp_timezone() );
		$secondsToDate = $date->getTimestamp() - $now->getTimestamp();

		if ( 'days' == $units ) {
			return $secondsToDate / DAY_IN_SECONDS;
		} elseif ( 'hours' == $units ) {
			return $secondsToDate / HOUR_IN_SECONDS;
		} else {
			return $secondsToDate;
		}
	}
}
