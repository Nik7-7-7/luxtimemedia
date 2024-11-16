<?php

namespace MotoPress\Appointment\REST\Controllers\Motopress\Appointment\V1;

use MotoPress\Appointment\PostTypes\Statuses\BookingStatuses;
use MotoPress\Appointment\Services\BookingService;
use MotoPress\Appointment\Helpers\PriceCalculationHelper;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.5.0
 */
class PaymentsRestController extends AbstractRestController {

	/**
	 * @since 1.5.0
	 */
	public function register_routes() {

		// '/motopress/appointment/v1/payments/settings'
		register_rest_route(
			$this->getNamespace(),
			'/payments/settings',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'getPaymentSettings' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'gateway_id' => array(
						'default'           => '',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		// '/motopress/appointment/v1/payments/prepare'
		register_rest_route(
			$this->getNamespace(),
			'/payments/prepare',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'preparePayment' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'action'          => array(
						'default'           => '',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'payment_details' => array(
						'type'       => 'object',
						'properties' => array(
							'booking_id'  => array(
								'type'     => 'integer',
								'required' => true,
								'context'  => array( 'edit' ),
							),
							'gateway_id'  => array(
								'type'    => 'string',
								'enum'    => array_keys( mpapp()->payments()->getActive( false ) ),
								'context' => array( 'edit' ),
							),
							'coupon_code' => array(
								'type'    => 'string',
								'default' => '',
								'context' => array( 'edit' ),
							),
							'deposit'     => array(
								'type'    => 'boolean',
								'default' => true,
								'context' => array( 'edit' ),
							),
						),
					),
				),
			)
		);
	}

	/**
	 * @since 1.5.0
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function getPaymentSettings( $request ) {

		$gatewayId = $request->get_param( 'gateway_id' );
		$gateway   = mpapp()->payments()->getGateway( $gatewayId );

		return rest_ensure_response( $gateway->getFrontendData() );
	}

	/**
	 * @since 1.5.0
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function preparePayment( $request ) {

		$order          = $request->get_params(); // todo: better parse each property
		$paymentDetails = $request->get_param( 'payment_details' );

		if ( ! is_array( $order ) || ! isset( $order['payment_details'] ) ) {
			return new WP_Error( 'invalid_input', esc_html__( 'Payment details have not been passed.', 'motopress-appointment' ) );
		}

		$bookingId = absint( $paymentDetails['booking_id'] );
		$booking   = mpapp()->repositories()->booking()->findById( $bookingId );

		if ( ! $booking ) {
			return mpa_rest_failure_error( esc_html__( 'Sorry! Failed to make a reservation at the moment.', 'motopress-appointment' ) );
		}

		if ( BookingStatuses::STATUS_CONFIRMED === $booking->getStatus() ) {
			return mpa_rest_failure_error( esc_html__( 'Your appointment has been booked.', 'motopress-appointment' ) );
		}

		$bookingService = new BookingService();
		$booking        = $bookingService->createBooking( $order );

		if ( is_wp_error( $booking ) ) {
			return $booking;
		}

		$gatewayId = sanitize_text_field( $paymentDetails['gateway_id'] );

		$activeGateways  = mpapp()->payments()->getActive();
		$selectedGateway = mpapp()->payments()->getGateway( $gatewayId );

		$payingAmount = $booking->getTotalPrice();

		if ( isset( $order['payment_details']['deposit'] ) && $order['payment_details']['deposit'] ) {

			$payingAmount = PriceCalculationHelper::calculateBookingDepositPrice( $booking );
		}

		if ( is_null( $selectedGateway ) ||
			( ! array_key_exists( $gatewayId, $activeGateways ) && 'free' !== $gatewayId )
		) {
			return new WP_Error( 'invalid_input', esc_html__( 'Payment method is not valid.', 'motopress-appointment' ) );

		}

		$currencyCode = mpapp()->settings()->getCurrency();

		if ( ! empty( $paymentDetails['currency'] ) ) {

			$currencyCode = sanitize_text_field( $paymentDetails['currency'] );
		}

		$response = '';

		try {

			$response = $selectedGateway->startPayment( $booking, $currencyCode, $payingAmount, $paymentDetails );

		} catch ( \Throwable $e ) {
			// phpcs:ignore
			error_log( $e );
			return mpa_rest_failure_error( esc_html__( 'Sorry! Failed to make a reservation at the moment.', 'motopress-appointment' ) );
		}

		return rest_ensure_response( $response );
	}
}
