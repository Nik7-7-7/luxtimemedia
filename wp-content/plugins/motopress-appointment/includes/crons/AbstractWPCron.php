<?php

namespace MotoPress\Appointment\Crons;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


abstract class AbstractWPCron {

	const WP_CRON_START_INTERVAL_ID_DAILY       = 'daily';
	const WP_CRON_START_INTERVAL_ID_TWICE_DAILY = 'twicedaily';
	const WP_CRON_START_INTERVAL_ID_HOURLY      = 'hourly';

	const CRON_START_INTERVAL_ID_EVERY_3_MIN  = 'mpa_every_3_minutes';
	const CRON_START_INTERVAL_ID_EVERY_10_MIN = 'mpa_every_10_minutes';
	const CRON_START_INTERVAL_ID_EVERY_30_MIN = 'mpa_every_30_minutes';


	public function __construct() {

		add_action(
			static::getCronActionHookName(),
			function() {

				try {

					$this->executeCron();

				} catch ( \Throwable $e ) {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
					error_log( $e->getMessage() . PHP_EOL . $e->getTraceAsString() );
				}
			}
		);
	}

	/**
	 * @return string cron action hook name bust starts with 'mpa_' and ends with '_cron'!
	 */
	abstract public static function getCronActionHookName(): string;


	abstract public static function getCronStartIntervalId(): string;


	public static function getCronStartIntervalInSeconds(): int {

		$cronStartIntervalInSeconds = static::WP_CRON_START_INTERVAL_ID_HOURLY;

		if ( static::CRON_START_INTERVAL_ID_EVERY_3_MIN === static::getCronStartIntervalId() ) {

			$cronStartIntervalInSeconds = 3 * MINUTE_IN_SECONDS;

		} elseif ( static::CRON_START_INTERVAL_ID_EVERY_10_MIN === static::getCronStartIntervalId() ) {

			$cronStartIntervalInSeconds = 10 * MINUTE_IN_SECONDS;

		} elseif ( static::CRON_START_INTERVAL_ID_EVERY_30_MIN === static::getCronStartIntervalId() ) {

			$cronStartIntervalInSeconds = 30 * MINUTE_IN_SECONDS;
		}

		return $cronStartIntervalInSeconds;
	}


	public static function getCronStartIntervalDescription(): string {

		$intervalDescription = '';

		if ( static::CRON_START_INTERVAL_ID_EVERY_3_MIN === static::getCronStartIntervalId() ) {

			$intervalDescription = 'Every 3 minutes.';

		} elseif ( static::CRON_START_INTERVAL_ID_EVERY_10_MIN === static::getCronStartIntervalId() ) {

			$intervalDescription = 'Every 10 minutes.';

		} elseif ( static::CRON_START_INTERVAL_ID_EVERY_30_MIN === static::getCronStartIntervalId() ) {

			$intervalDescription = 'Every 30 minutes.';
		}

		return $intervalDescription;
	}


	abstract protected function executeCron();


	/**
	 * @return int|false False if the cron is not scheduled.
	 */
	public static function getNextScheduledTime() {

		return wp_next_scheduled( static::getCronActionHookName() );
	}

	/**
	 * @param bool $isReschedule - if true then reschedule cron
	 * @param int $unixTimestamp - timestamp (UTC) for when to next run the cron if 0 then gets current time
	 */
	public static function schedule( bool $isReschedule = false, int $unixTimestampUTC = 0 ) {

		$cronActionHookName = static::getCronActionHookName();

		if ( $isReschedule ) {

			wp_clear_scheduled_hook( $cronActionHookName );

		} elseif ( false !== wp_next_scheduled( $cronActionHookName ) ) {

			return;
		}

		if ( 0 === $unixTimestampUTC ) {
			$unixTimestampUTC = time();
		}

		wp_schedule_event( $unixTimestampUTC, static::getCronStartIntervalId(), $cronActionHookName );
	}

	public static function unschedule() {

		$cronActionHookName    = static::getCronActionHookName();
		$cronNextScheduledTime = wp_next_scheduled( $cronActionHookName );

		if ( false !== $cronNextScheduledTime ) {

			wp_unschedule_event( $cronNextScheduledTime, $cronActionHookName );
		}
	}
}
