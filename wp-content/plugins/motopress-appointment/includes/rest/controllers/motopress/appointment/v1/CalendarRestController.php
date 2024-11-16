<?php

namespace MotoPress\Appointment\REST\Controllers\Motopress\Appointment\V1;

use MotoPress\Appointment\Services\BookingService;
use MotoPress\Appointment\Services\TimeSlotService;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.2.1
 */
class CalendarRestController extends AbstractRestController {

	/**
	 * @since 1.2.1
	 */
	public function register_routes() {
		// '/motopress/appointment/v1/calendar/time'
		register_rest_route(
			$this->getNamespace(),
			'/calendar/time',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'getTimeSlots' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					// service_id, from_date and to_date are required
					'service_id'   => array(
						// Required
						'default'           => 0,
						'sanitize_callback' => 'mpa_rest_sanitize_id',
					),
					'employee_in'  => array(
						'default'           => array(),
						'sanitize_callback' => 'mpa_rest_sanitize_ids',
					),
					'location_in'  => array(
						'default'           => array(),
						'sanitize_callback' => 'mpa_rest_sanitize_ids',
					),
					'date_from'    => array(
						// Required
						'default'           => '',
						'sanitize_callback' => 'mpa_rest_sanitize_date_string',
					),
					'date_to'      => array(
						// Required
						'default'           => '',
						'sanitize_callback' => 'mpa_rest_sanitize_date_string',
					),
					'exclude_cart' => array(
						// Optional. Items to exclude in the format of the cart items.
						'default'           => array(),
					),
					'since_today'  => array(
						'default'           => true,
						'sanitize_callback' => 'mpa_rest_sanitize_bool',
					),
				),
			)
		);
	}

	/**
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 *
	 * @since 1.2.1
	 */
	public function getTimeSlots( $request ) {
		$serviceId   = $request->get_param( 'service_id' );
		$service     = $serviceId ? mpa_get_service( $serviceId ) : null;
		$employeeIn  = $request->get_param( 'employee_in' );
		$locationIn  = $request->get_param( 'location_in' );
		$dateFrom    = mpa_parse_date( $request->get_param( 'date_from' ), null );
		$dateTo      = mpa_parse_date( $request->get_param( 'date_to' ), null );
		$excludeCart = $request->get_param( 'exclude_cart' );
		$sinceToday  = $request->get_param( 'since_today' );

		// Check required fields
		if ( ! $serviceId ) {
			return mpa_rest_request_error( esc_html__( 'Invalid parameter: service ID is not set.', 'motopress-appointment' ) );
		} elseif ( is_null( $service ) ) {
			// Translators: %d: Service ID.
			return mpa_rest_request_error( sprintf( esc_html__( 'Invalid request: services not found.', 'motopress-appointment' ), $serviceId ) );
		}

		if ( is_null( $dateFrom ) || is_null( $dateTo ) ) {
			return mpa_rest_request_error( esc_html__( 'Invalid parameter: date range is not set.', 'motopress-appointment' ) );
		}

		// Parse cart items
		$cartItems = [];

		if ( ! empty( $excludeCart ) ) {
			$bookingService = new BookingService();

			foreach ( $excludeCart as $cartItemArray ) {
				$cartItem = $bookingService->createReservation( $cartItemArray );

				if ( ! is_wp_error( $cartItem ) ) {
					$cartItems[] = $cartItem;
				}
			}
		}

		// Build time slots
		$timeSlots = TimeSlotService::makeTimeSlots(
			$service,
			[
				'employee_in' => $employeeIn,
				'location_in' => $locationIn,
				'date_from'   => $dateFrom,
				'date_to'     => $dateTo,
				'since_today' => $sinceToday,
			],
			$cartItems
		);

		return rest_ensure_response( $timeSlots );
	}
}
