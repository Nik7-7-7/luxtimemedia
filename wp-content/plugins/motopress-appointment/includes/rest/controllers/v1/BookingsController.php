<?php
/**
 * @package MotoPress\Appointment\Rest
 * @since 1.8.0
 */

namespace MotoPress\Appointment\Rest\Controllers\V1;

use MotoPress\Appointment\Rest\Controllers\AbstractRestObjectController;
use MotoPress\Appointment\Rest\Data\BookingData;
use WP_REST_Request;

class BookingsController extends AbstractRestObjectController {


	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'mpa/v1';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'bookings';

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = 'mpa_booking';


	/**
	 * Prepare links for the request.
	 *
	 * @param  BookingData  $bookingData  Booking data object.
	 * @param  WP_REST_Request  $request  Request object.
	 *
	 * @return array Links for the given post.
	 */
	protected function prepare_links( $bookingData, $request ) {
		$links = parent::prepare_links( $bookingData, $request );

		$reservations = $bookingData->getReservations();
		if ( count( $reservations ) ) {
			foreach ( $reservations as $reservation ) {
				$links['reservations'][] = array(
					'href'       => rest_url( sprintf( '/%s/%s/%d', $this->namespace, 'reservations', $reservation ) ),
					'embeddable' => true,
				);
			}
		}

		$payments = $bookingData->getPayments();
		if ( count( $payments ) ) {
			foreach ( $payments as $payment ) {
				$links['payments'][] = array(
					'href'       => rest_url( sprintf( '/%s/%s/%d', $this->namespace, 'payments', $payment ) ),
					'embeddable' => true,
				);
			}
		}

		$coupon = $bookingData->getCoupon();
		if ( $coupon ) {
			$links['coupon'] = array(
				'href'       => rest_url( sprintf( '/%s/%s/%d', $this->namespace, 'coupons', $coupon ) ),
				'embeddable' => true,
			);
		}

		return $links;
	}
}
