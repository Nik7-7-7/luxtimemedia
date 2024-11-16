<?php

namespace MotoPress\Appointment\Plugin;

use Throwable;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class PluginPatch_1_20_0 extends AbstractPluginPatch {

	const PROCESSING_PER_TASK_RESERVATIONS_COUNT = 10;

	public static function getVersion(): string {
		return '1.20.0';
	}

	public static function execute(): bool {

		$reservations = mpapp()->repositories()->reservation()->findAll(
			array(
				'posts_per_page' => self::PROCESSING_PER_TASK_RESERVATIONS_COUNT,
				// phpcs:ignore
				'meta_query'  => array(
					array(
						'key'     => '_mpa_total_price',
						'compare' => 'NOT EXISTS',
					),
				),
			)
		);

		if ( ! empty( $reservations ) ) {

			$bookings = array();

			foreach ( $reservations as $reservation ) {

				try {

					if ( empty( $reservation->getBooking() ) ) {

						// save 0 price to make sure we will not find this
						// reservation on the next patch step
						update_post_meta(
							$reservation->getId(),
							'_mpa_total_price',
							0.0
						);

					} elseif ( ! empty( $reservation->getBooking() ) &&
						empty( $bookings[ $reservation->getBookingId() ] )
					) {

						$bookings[ $reservation->getBookingId() ] = $reservation->getBooking();

					}
				} catch ( Throwable $e ) {

					// save 0 price to make sure we will not find this
					// reservation on the next patch step
					update_post_meta(
						$reservation->getId(),
						'_mpa_total_price',
						0.0
					);
				}
			}

			if ( ! empty( $bookings ) ) {

				foreach ( $bookings as $booking ) {

					try {

						\MotoPress\Appointment\Helpers\PriceCalculationHelper::updateBookingPrices( $booking );

					} catch ( Throwable $e ) {

						foreach ( $booking->getReservations() as $reservation ) {

							// save 0 price to make sure we will not find this
							// reservation on the next patch step
							update_post_meta(
								$reservation->getId(),
								'_mpa_total_price',
								0.0
							);
						}
					}
				}
			}
		}

		// return true if we found less then 10 reservation
		// because there is nothing to process anymore
		return self::PROCESSING_PER_TASK_RESERVATIONS_COUNT > count( $reservations );
	}
}
