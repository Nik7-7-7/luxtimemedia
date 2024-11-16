<?php

declare(strict_types=1);

namespace MotoPress\Appointment\Helpers;

use MotoPress\Appointment\Entities\Booking;
use MotoPress\Appointment\Entities\Coupon;
use MotoPress\Appointment\Entities\Service;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


final class PriceCalculationHelper {

	// this is helper with static functions only
	private function __construct() {}

	/**
	 * @param float $price
	 * @param array $args Optional.
	 * @return string
	 */
	public static function formatPrice( float $price, $priceArgs = array() ): string {

		$args = array_merge(
			array(
				'currency_symbol'    => mpapp()->settings()->getCurrencySymbol(),
				'currency_position'  => mpapp()->settings()->getCurrencyPosition(),
				'decimal_separator'  => mpapp()->settings()->getDecimalSeparator(),
				'thousand_separator' => mpapp()->settings()->getThousandSeparator(),
				'decimals'           => mpapp()->settings()->getDecimalsCount(),
				'literal_free'       => true,
				'trim_zeros'         => true,
			),
			$priceArgs
		);

		$priceString = number_format(
			abs( $price ),
			$args['decimals'],
			$args['decimal_separator'],
			$args['thousand_separator']
		);

		if ( 0 === $price && $args['literal_free'] ) {

			$priceString = apply_filters( 'mpa_free_literal', esc_html_x( 'Free', 'Zero price', 'motopress-appointment' ) );

		} else {

			if ( $args['trim_zeros'] ) {
				$priceString = static::trimPrice( $priceString );
			}

			if ( ! empty( $args['currency_symbol'] ) ) {

				switch ( $args['currency_position'] ) {
					case 'before':
						$priceString = $args['currency_symbol'] . $priceString;
						break;
					case 'after':
						$priceString = $priceString . $args['currency_symbol'];
						break;
					case 'before_with_space':
						$priceString = $args['currency_symbol'] . ' ' . $priceString;
						break;
					case 'after_with_space':
						$priceString = $priceString . ' ' . $args['currency_symbol'];
						break;
				}

				// decode curency symbol
				$priceString = html_entity_decode( $priceString );
			}

			// Add sign
			if ( $price < 0 ) {
				$priceString = '-' . $priceString;
			}
		}

		return $priceString;
	}


	public static function formatPriceAsHTML( float $price, array $args = array() ): string {

		$args = array_merge(
			array(
				'currency_symbol'    => mpapp()->settings()->getCurrencySymbol(),
				'currency_position'  => mpapp()->settings()->getCurrencyPosition(),
				'decimal_separator'  => mpapp()->settings()->getDecimalSeparator(),
				'thousand_separator' => mpapp()->settings()->getThousandSeparator(),
				'decimals'           => mpapp()->settings()->getDecimalsCount(),
				'literal_free'       => true,
				'trim_zeros'         => true,
			),
			$args
		);

		$priceString = number_format(
			abs( $price ),
			$args['decimals'],
			$args['decimal_separator'],
			$args['thousand_separator']
		);

		$class = 'mpa-price';

		if ( 0 === $price ) {
			$class .= ' mpa-zero-price';
		}

		if ( 0 === $price && $args['literal_free'] ) {

			// Use text 'Free' as a price string
			$class .= ' mpa-price-free';

			$priceString = apply_filters( 'mpa_free_literal', esc_html_x( 'Free', 'Zero price', 'motopress-appointment' ) );

		} else {

			if ( $args['trim_zeros'] ) {
				$priceString = static::trimPrice( $priceString );
			}

			if ( ! empty( $args['currency_symbol'] ) ) {

				$currencySpan = '<span class="mpa-currency">' . $args['currency_symbol'] . '</span>';

				switch ( $args['currency_position'] ) {
					case 'before':
						$priceString = $currencySpan . $priceString;
						break;
					case 'after':
						$priceString = $priceString . $currencySpan;
						break;
					case 'before_with_space':
						$priceString = $currencySpan . '&nbsp;' . $priceString;
						break;
					case 'after_with_space':
						$priceString = $priceString . '&nbsp;' . $currencySpan;
						break;
				}
			}

			// Add sign
			if ( $price < 0 ) {
				$priceString = '-' . $priceString;
			}
		}

		$priceHtml = '<span class="' . $class . '">' . $priceString . '</span>';

		return $priceHtml;
	}

	/**
	 * Will trim '5.00' to '5', but leave '5.50' as is.
	 */
	public static function trimPrice( string $price, string $decimalSeparator = null ): string {

		if ( is_null( $decimalSeparator ) ) {
			$decimalSeparator = mpapp()->settings()->getDecimalSeparator();
		}

		$regex = '/' . preg_quote( $decimalSeparator, '/' ) . '0++$/';
		$price = preg_replace( $regex, '', $price );

		return $price;
	}


	public static function calculateAndSetBookingPrices( Booking $booking ): Booking {

		$bookingTotalPrice = 0.0;
		$coupon            = $booking->getCoupon();

		foreach ( $booking->getReservations() as $reservation ) {

			$reservationService = $reservation->getService();

			$reservation->setPrice(
				$reservationService->getPrice(
					$reservation->getEmployeeId(),
					$reservation->getCapacity()
				)
			);

			$reservation->setDiscount( 0.0 );
			$reservation->setTotalPrice( $reservation->getPrice() );

			if ( null !== $coupon && $coupon->isApplicableForReservation( $reservation ) ) {

				$reservationDiscount = 0.0;

				switch ( $coupon->getType() ) {

					case Coupon::COUPON_DISCOUNT_TYPE_FIXED:
						$reservationDiscount = $coupon->getAmount();
						break;

					case Coupon::COUPON_DISCOUNT_TYPE_PERCENTAGE:
						$reservationDiscount = round(
							$reservation->getPrice() * $coupon->getAmount() / 100,
							mpapp()->settings()->getDecimalsCount()
						);
						break;
				}

				// make sure discount <= reservation price
				$reservationDiscount = min( $reservationDiscount, $reservation->getPrice() );

				$reservation->setDiscount( $reservationDiscount );

				$reservation->setTotalPrice(
					max( 0, $reservation->getPrice() - $reservationDiscount )
				);
			}

			$depositAmount = 0.0;

			if ( Service::DEPOSIT_TYPE_FIXED === $reservationService->getDepositType() ) {

				$depositAmount = min(
					$reservation->getTotalPrice(),
					$reservationService->getDepositAmount()
				);

			} elseif ( Service::DEPOSIT_TYPE_PERCENTAGE === $reservationService->getDepositType() ) {

				$depositAmount = min(
					$reservation->getTotalPrice(),
					round(
						$reservation->getTotalPrice() * $reservationService->getDepositAmount() / 100,
						mpapp()->settings()->getDecimalsCount()
					)
				);
			}

			$reservation->setDepositAmount( $depositAmount );

			$bookingTotalPrice += $reservation->getTotalPrice();
		}

		$booking->setTotalPrice( $bookingTotalPrice );

		return $booking;
	}

	public static function updateBookingPrices( Booking $booking ): Booking {

		$booking = static::calculateAndSetBookingPrices( $booking );

		$booking = mpapp()->repositories()->booking()->saveBookingPrices( $booking );

		return $booking;
	}


	public static function calculateBookingDepositPrice( Booking $booking ): float {

		$bookingDepositAmount = 0.0;

		foreach ( $booking->getReservations() as $reservation ) {

			$bookingDepositAmount += $reservation->getDepositAmount();
		}

		return $bookingDepositAmount;
	}

	/**
	 * @return array [ int reservation_id => float reservation_deposite_price ]
	 * @deprecated use Reservation->getDepositAmount()
	 */
	public static function calculateReservationsDepositPrices( Booking $booking ): array {

		$reservationsDepositPrices = array();

		foreach ( $booking->getReservations() as $reservation ) {

			$reservationsDepositPrices[ $reservation->getId() ] = $reservation->getDepositAmount();
		}

		return $reservationsDepositPrices;
	}
}
