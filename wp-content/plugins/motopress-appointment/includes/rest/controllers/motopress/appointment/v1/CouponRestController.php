<?php

namespace MotoPress\Appointment\REST\Controllers\Motopress\Appointment\V1;

use MotoPress\Appointment\Entities\Coupon;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.11.0
 */
class CouponRestController extends AbstractRestController {

	public function register_routes() {
		// '/motopress/appointment/v1/coupons'
		register_rest_route(
			$this->getNamespace(),
			'/coupons',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'getCoupons' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'code'     => array(
						'default'           => '',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);
	}

	/**
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function getCoupons( $request ) {
		$couponCode = $request->get_param( 'code' );

		if ( ! $couponCode ) {
			return mpa_rest_request_error( esc_html__( 'Invalid parameter: coupon ID or code are not set.', 'motopress-appointment' ) );
		}

		$coupon = mpapp()->repositories()->coupon()->findByCode( $couponCode );

		if ( is_null( $coupon ) || ! $coupon->isPublic() ) {
			return mpa_rest_request_error( esc_html__( 'Coupon not found.', 'motopress-appointment' ) );
		}

		if ( $coupon->isExpired() ) {
			return mpa_rest_request_error( esc_html__( 'This coupon has expired.', 'motopress-appointment' ) );
		}

		if ( $coupon->isExceededUsageLimit() ) {
			return mpa_rest_request_error( esc_html__( 'Coupon usage limit has been reached.', 'motopress-appointment' ) );
		}

		return rest_ensure_response( $this->mapEntity( $coupon ) );
	}

	/**
	 * @param Coupon $coupon
	 * @return array
	 */
	protected function mapEntity( $coupon ) {
		$expirationDate = $coupon->getExpirationDate();
		$minDate        = $coupon->getMinDate();
		$maxDate        = $coupon->getMaxDate();

		return array(
			'id'             => $coupon->getId(),
			'code'           => $coupon->getCode(),
			'status'         => $coupon->getStatus(),
			'description'    => $coupon->getDescription(),
			'type'           => $coupon->getType(),
			'amount'         => $coupon->getAmount(),
			'expirationDate' => ! is_null( $expirationDate ) ? mpa_format_date( $expirationDate, 'internal' ) : null,
			'serviceIds'     => $coupon->getServiceIds(),
			'minDate'        => ! is_null( $minDate ) ? mpa_format_date( $minDate, 'internal' ) : null,
			'maxDate'        => ! is_null( $maxDate ) ? mpa_format_date( $maxDate, 'internal' ) : null,
			'usageLimit'     => $coupon->getUsageLimit(),
			'usageCount'     => $coupon->getUsageCount(),
		);
	}
}
