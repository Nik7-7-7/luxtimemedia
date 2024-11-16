<?php

namespace MotoPress\Appointment\Crons;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.0
 */
class DeleteDraftBookingsCron extends AbstractWPCron {


	public static function getCronActionHookName(): string {
		return 'mpa_delete_draft_bookings_cron';
	}


	public static function getCronStartIntervalId(): string {
		return static::CRON_START_INTERVAL_ID_EVERY_30_MIN;
	}


	/**
	 * @param int $expiredMinutes
	 *
	 * @return int[]
	 */
	protected function getAutoDraftBookings( int $expiredMinutes = 0 ) {

		global $wpdb;

		$query = "SELECT ID FROM $wpdb->posts WHERE post_type = 'mpa_booking' AND post_status = 'auto-draft'";

		if ( $expiredMinutes > 0 ) {
			$nowTime = current_time( 'mysql' );
			$query  .= " AND post_date < DATE_SUB( '$nowTime', INTERVAL $expiredMinutes MINUTE )";
		}

		return $wpdb->get_col( $query );
	}


	protected function executeCron() {

		$expiredMinutes       = round( self::getCronStartIntervalInSeconds() / MINUTE_IN_SECONDS );
		$expiredDraftBookings = $this->getAutoDraftBookings( $expiredMinutes );

		foreach ( (array) $expiredDraftBookings as $expiredDraftBooking ) {
			// Force delete.
			wp_delete_post( $expiredDraftBooking, true );
		}

		// stop this cron if there are no more auto-draft bookings
		// we schedule it again after any booking will be created
		if ( ! $this->getAutoDraftBookings() ) {

			$this->unschedule();
		}
	}
}
