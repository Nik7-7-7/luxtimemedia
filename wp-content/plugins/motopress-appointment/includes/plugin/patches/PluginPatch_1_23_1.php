<?php

namespace MotoPress\Appointment\Plugin;

use Throwable;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class PluginPatch_1_23_1 extends AbstractPluginPatch {


	public static function getVersion(): string {
		return '1.23.1';
	}

	public static function execute(): bool {

		$serviceIds = mpapp()->repositories()->service()->findAll(
			array(
				'fields' => 'ids',
			)
		);

		foreach ( $serviceIds as $serviceId ) {

			$timeBeforeBooking = get_post_meta( $serviceId, '_mpa_time_before_booking', true );

			$timeBeforeBooking = absint( $timeBeforeBooking );

			if ( 0 < $timeBeforeBooking ) {

				$rest    = $timeBeforeBooking;
				$years   = intdiv( $rest, 525600 /** 365 * 24 * 60 */ );
				$rest    = $rest - $years * 525600;
				$months  = intdiv( $rest, 43200 /** 30 * 24 * 60 */ );
				$rest    = $rest - $months * 43200;
				$days    = intdiv( $rest, 1440 /** 24 * 60 */ );
				$rest    = $rest - $days * 1440;
				$hours   = intdiv( $rest, 60 );
				$minutes = $rest - $hours * 60;

				$newValue = '';

				try {

					$newValue = new \DateInterval( 'P' . $years . 'Y' . $months . 'M' . $days . 'DT' . $hours . 'H' . $minutes . 'M' );
					$newValue = $newValue->format( 'P%yY%mM%dDT%hH%iM' );

					update_post_meta(
						$serviceId,
						'_mpa_time_before_booking',
						$newValue
					);

					// phpcs:ignore
				} catch ( \Throwable $e ) {
					// ignore parsing exception
				}
			}

			$maxAdvanceBeforeReservation = get_post_meta( $serviceId, '_mpa_max_advance_time_before_reservation', true );

			$maxAdvanceBeforeReservation = absint( $maxAdvanceBeforeReservation );

			if ( 0 < $maxAdvanceBeforeReservation ) {

				$rest    = $maxAdvanceBeforeReservation;
				$years   = intdiv( $rest, 525600 /** 365 * 24 * 60 */ );
				$rest    = $rest - $years * 525600;
				$months  = intdiv( $rest, 43200 /** 30 * 24 * 60 */ );
				$rest    = $rest - $months * 43200;
				$days    = intdiv( $rest, 1440 /** 24 * 60 */ );
				$rest    = $rest - $days * 1440;
				$hours   = intdiv( $rest, 60 );
				$minutes = $rest - $hours * 60;

				$newValue = '';

				try {

					$newValue = new \DateInterval( 'P' . $years . 'Y' . $months . 'M' . $days . 'DT' . $hours . 'H' . $minutes . 'M' );
					$newValue = $newValue->format( 'P%yY%mM%dDT%hH%iM' );

					update_post_meta(
						$serviceId,
						'_mpa_max_advance_time_before_reservation',
						$newValue
					);

					// phpcs:ignore
				} catch ( \Throwable $e ) {
					// ignore parsing exception
				}
			}
		}

		return true;
	}
}
