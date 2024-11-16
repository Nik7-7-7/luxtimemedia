<?php
/**
 * @package MotoPress\Appointment\Rest
 * @since 1.8.0
 */

namespace MotoPress\Appointment\Rest\Data;

use MotoPress\Appointment\Entities\Booking;
use MotoPress\Appointment\Entities\Employee;
use MotoPress\Appointment\Entities\Location;
use MotoPress\Appointment\Entities\Payment;
use MotoPress\Appointment\Entities\Reservation;
use MotoPress\Appointment\Entities\Service;
use MotoPress\Appointment\Entities\Coupon;

class DataFactory {

	/**
	 * @param string $rest_base
	 *
	 * @return AbstractData
	 * @throws \Exception
	 */
	public static function create( $rest_base ) {
		switch ( $rest_base ) {
			case 'bookings':
				$booking = new Booking( 0, array() );
				return new BookingData( $booking );
			case 'payments':
				$payment = new Payment( 0, array() );
				return new PaymentData( $payment );
			case 'reservations':
				$reservation = new Reservation( 0, array() );
				return new ReservationData( $reservation );
			case 'services':
				$service = new Service( 0, array() );
				return new ServiceData( $service );
			case 'locations':
				$location = new Location( 0, array() );
				return new LocationData( $location );
			case 'employees':
				$employee = new Employee( 0, array() );
				return new EmployeeData( $employee );
			case 'coupons':
				$coupon = new Coupon( 0, array() );
				return new CouponData( $coupon );
			default:
				throw new \Exception( 'Not found relevant class for data of endpoint: ' . $rest_base );
		}
	}
}
