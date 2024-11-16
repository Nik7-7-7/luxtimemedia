<?php

namespace MotoPress\Appointment\Services;

use MotoPress\Appointment\Entities\{Booking, Coupon, Employee, Location,
	Reservation, Service};
use MotoPress\Appointment\Helpers\PriceCalculationHelper;
use MotoPress\Appointment\Structures\TimePeriod;
use MotoPress\Appointment\Utils\ParseUtils;
use DateTime;
use RuntimeException;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class BookingService {

	/**
	 * @param array $order
	 * @return Booking|WP_Error
	 *
	 * @since 1.0
	 */
	public function createBooking( $order ) {

		if ( ! is_array( $order ) ) {
			return new WP_Error( 'invalid_input', esc_html__( 'Unable to make a reservation. Your order seems to be empty.', 'motopress-appointment' ) );
		}

		// Create reservations
		$reservations = array();

		if ( isset( $order['items'] ) ) {
			foreach ( $order['items'] as $item ) {
				$reservation = $this->createReservation( $item );

				if ( is_wp_error( $reservation ) ) {
					// Return WP_Error
					return $reservation;
				}

				$reservations[] = $reservation;
			}
		}

		// Use preset booking ID
		$bookingId = isset( $order['payment_details']['booking_id'] )
			? ParseUtils::parseId( $order['payment_details']['booking_id'] )
			: 0;

		// If booking already had a reservations and need update reservation list, then
		// delete all previously reservations because we do not want to duplicate some of them
		if ( $bookingId && isset( $order['items'] ) ) {

			$draftBooking = mpapp()->repositories()->booking()->findById( $bookingId, true );

			if ( null !== $draftBooking && ! empty( $draftBooking->getReservations() ) ) {

				foreach ( $draftBooking->getReservations() as $storedReservation ) {
					wp_delete_post( $storedReservation->getId(), true );
				}
			}
		}

		if ( $reservations ) {
			// Create booking
			$booking = new Booking(
				$bookingId,
				array(
					'status'        => mpapp()->settings()->getDefaultBookingStatus(),
					'reservations'  => $reservations,
					'customerNotes' => isset( $order['customer']['notes'] ) ? sanitize_text_field( wp_unslash( $order['customer']['notes'] ) ) : '',
				)
			);
		} else {
			// get a ready booking
			$booking = mpapp()->repositories()->booking()->findById( $bookingId );

			if ( null === $booking ) {
				return new WP_Error( 'invalid_input', esc_html__( 'Unable to make a reservation. Your order seems to be empty.', 'motopress-appointment' ) );
			}
		}

		// Parse coupon
		$coupon = $this->parseCoupon( $order );

		if ( is_wp_error( $coupon ) ) {
			return $coupon;
		}

		// Apply coupon
		if ( ! is_null( $coupon ) && $coupon->isApplicableForBooking( $booking ) ) {
			$booking->setCouponId( $coupon->getId() );
		} else {
			$booking->setCouponId( 0 );
		}

		if ( isset( $order['customer'] ) ) {

			$customerName  = $order['customer']['name'] ? sanitize_text_field( wp_unslash( $order['customer']['name'] ) ) : '';
			$customerEmail = $order['customer']['email'] ? sanitize_text_field( wp_unslash( $order['customer']['email'] ) ) : '';
			$customerPhone = $order['customer']['phone'] ? sanitize_text_field( wp_unslash( $order['customer']['phone'] ) ) : '';

			$booking->setCustomerName( $customerName );

			if ( $customerEmail && ! is_email( $customerEmail ) ) {
				return new WP_Error( 'invalid_input', esc_html__( 'Please provide a valid email address.', 'motopress-appointment' ) );
			}

			$booking->setCustomerEmail( $customerEmail );
			$booking->setCustomerPhone( $customerPhone );

			$customer = null;

			if ( $customerEmail ) {
				$customer = mpapp()->repositories()->customer()->findByEmail( $customerEmail );
			}

			if ( ! $customer && $customerPhone ) {
				$customer = mpapp()->repositories()->customer()->findByPhone( $customerPhone );
			}

			if ( ! is_null( $customer ) ) {
				$booking->setCustomerId( $customer->getId() );
			}
		}

		$booking = PriceCalculationHelper::calculateAndSetBookingPrices( $booking );

		return $booking;
	}

	/**
	 * @param array $item Order item
	 * @return Reservation|WP_Error
	 *
	 * @since 1.0
	 */
	public function createReservation( $item ) {

		$reservation = null;

		try {
			$this->requireFields( $item );

			// Required properties
			$service = $this->parseService( $item );
			$date    = $this->parseDate( $item );
			$time    = $this->parseTime( $item );

			$time->setDate( $date );

			// Optional properties
			$employee = $this->parseEmployee( $item, $service, $date );
			$location = $this->parseLocation( $item, $employee, $date );

			// Prepare other properties
			$capacity = $this->parseCapacity( $item, $service, $employee );

			// other prices depend on booking coupon so we set them later
			$price = $service->getPrice( $employee->getId(), $capacity );

			$bufferTime = mpa_add_buffer_time( clone $time, $service );

			$reservationFields = array(
				'price'       => $price,
				'date'        => $date,
				'serviceTime' => $time,
				'bufferTime'  => $bufferTime,
				'serviceId'   => $service->getId(),
				'employeeId'  => $employee->getId(),
				'locationId'  => $location->getId(),
				'capacity'    => $capacity,
			);

			if ( ! empty( $item['uid'] ) ) {
				// But don't add "" and replace default UUID4 key
				$reservationFields['uid'] = sanitize_text_field( $item['uid'] );
			}

			$reservation = new Reservation( 0, $reservationFields );

		} catch ( RuntimeException $error ) {
			return new WP_Error( 'invalid_input', $error->getMessage() );
		}

		return $reservation;
	}

	/**
	 * @param array $item
	 *
	 * @throws RuntimeException
	 *
	 * @since 1.0
	 */
	protected function requireFields( $item ) {
		if ( ! isset( $item['service_id'], $item['date'], $item['time'] ) ) {
			throw new RuntimeException( esc_html__( 'Unable to make a reservation. Your order misses some of the required data.', 'motopress-appointment' ) );
		}
	}

	/**
	 * @param array $item
	 * @return Service
	 *
	 * @throws RuntimeException
	 *
	 * @since 1.0
	 */
	protected function parseService( $item ) {
		$serviceId = absint( $item['service_id'] );
		$service   = mpa_get_service( $serviceId );

		if ( is_null( $service ) ) {
			// Translators: %d: ID of the post.
			throw new RuntimeException( sprintf( esc_html__( 'Unable to make a reservation. There is no such service with ID #%d.', 'motopress-appointment' ), $serviceId ) );
		}

		return $service;
	}

	/**
	 * @param array $item
	 * @return DateTime
	 *
	 * @throws RuntimeException
	 *
	 * @since 1.0
	 */
	protected function parseDate( $item ) {
		$date = mpa_parse_date( $item['date'] );

		if ( ! $date ) {
			throw new RuntimeException( esc_html__( 'Unable to make a reservation. The date value is broken.', 'motopress-appointment' ) );
		}

		return $date;
	}

	/**
	 * @param array $item
	 * @return TimePeriod
	 *
	 * @throws RuntimeException
	 *
	 * @since 1.0
	 */
	protected function parseTime( $item ) {
		$time = TimePeriod::createFromPeriod( $item['time'] );

		if ( ! $time ) {
			throw new RuntimeException( esc_html__( 'Unable to make a reservation. The time value is broken.', 'motopress-appointment' ) );
		}

		return $time;
	}

	/**
	 * @param array $item
	 * @param Service $service
	 * @param DateTime $date
	 * @return Employee
	 *
	 * @throws RuntimeException
	 *
	 * @since 1.0
	 */
	protected function parseEmployee( $item, $service, $date ) {

		if ( isset( $item['employee_id'] ) ) {

			// Just find the selected employee
			$employeeId = absint( $item['employee_id'] );
			$employee   = mpa_get_employee( $employeeId );

		} else {
			// Search for employee
			$locationId = isset( $item['location_id'] ) ? absint( $item['location_id'] ) : 0;
			$employee   = null;

			foreach ( $service->getEmployeeIds() as $employeeId ) {

				// Filter all employees that work at location $locationId for current $date
				if ( 0 !== $locationId ) {

					$schedule = mpapp()->repositories()->schedule()->findByEmployee( $employeeId );

					if ( is_null( $schedule ) || ! $schedule->isWorkingAt( $locationId, $date ) ) {
						continue;
					}
				}

				$employee = mpa_get_employee( $employeeId );

				if ( ! is_null( $employee ) ) {
					break; // Found one
				}
			} // For each employee
		}

		if ( is_null( $employee ) ) {
			throw new RuntimeException( esc_html__( 'Unable to make a reservation. No employee found for your request.', 'motopress-appointment' ) );
		}

		return $employee;
	}

	/**
	 * @param array $item
	 * @param Employee $employee
	 * @param DateTime $date
	 * @return Location
	 *
	 * @throws RuntimeException
	 *
	 * @since 1.0
	 */
	protected function parseLocation( $item, $employee, $date ) {

		if ( isset( $item['location_id'] ) ) {
			// Just find the selected location
			$locationId = absint( $item['location_id'] );
			$location   = mpa_get_location( $locationId );

		} else {
			// Search for location
			$schedule  = mpapp()->repositories()->schedule()->findByEmployee( $employee->getId() );
			$locations = ! is_null( $schedule ) ? $schedule->getLocationIdsForDate( $date ) : array();
			$location  = ! empty( $locations ) ? mpa_get_location( $locations[0] ) : null;
		}

		if ( is_null( $location ) ) {
			throw new RuntimeException( esc_html__( 'Unable to make a reservation. No places left for this service.', 'motopress-appointment' ) );
		}

		return $location;
	}

	/**
	 * @since 1.3.1
	 *
	 * @param array $item
	 *     @param int $item['capacity'] Optional. Min capacity by default.
	 * @param Service $service
	 * @param Employee $employee
	 * @return int Valid capacity for the service.
	 */
	protected function parseCapacity( $item, $service, $employee ) {

		$item += array(
			'capacity' => $service->getMinCapacity( $employee->getId() ),
		);

		$minCapacity = $service->getMinCapacity( $employee->getId() );
		$maxCapacity = $service->getMaxCapacity( $employee->getId() );

		$capacity = max( $minCapacity, min( $item['capacity'], $maxCapacity ) );

		return $capacity;
	}

	/**
	 * @param array $order
	 * @return Coupon|null|WP_Error
	 *
	 * @since 1.11.0
	 */
	protected function parseCoupon( $order ) {

		$couponCode = '';

		if ( isset( $order['payment_details'] ) && ! empty( $order['payment_details']['coupon_code'] ) ) {

			$couponCode = sanitize_text_field( $order['payment_details']['coupon_code'] );

		} elseif ( ! empty( $order['coupon'] ) ) {

			$couponCode = sanitize_text_field( $order['coupon'] );
		}

		if ( empty( $couponCode ) ) {
			return null;
		}

		if ( $couponCode ) {
			$coupon = mpapp()->repositories()->coupon()->findByCode( $couponCode );
		} else {
			return new WP_Error( 'invalid_input', esc_html__( 'Invalid parameter: coupon ID or code are not set.', 'motopress-appointment' ) );
		}

		if ( is_null( $coupon ) ) {
			return new WP_Error( 'invalid_input', esc_html__( 'Coupon not found.', 'motopress-appointment' ) );
		}

		if ( $coupon->isExpired() ) {
			return new WP_Error( 'invalid_input', esc_html__( 'This coupon has expired.', 'motopress-appointment' ) );
		}

		if ( $coupon->isExceededUsageLimit() ) {
			return new WP_Error( 'invalid_input', esc_html__( 'Coupon usage limit has been reached.', 'motopress-appointment' ) );
		}

		return $coupon;
	}

	/**
	 * Create new or update booking post in database.
	 * @deprecated use mpapp()->repositories()->booking()->saveBooking( $booking )
	 * @todo remove this method when all will use mpapp()->repositories()->booking()->saveBooking()
	 */
	public function saveBooking( Booking $booking ) {

		mpapp()->repositories()->booking()->saveBooking( $booking );
	}
}
