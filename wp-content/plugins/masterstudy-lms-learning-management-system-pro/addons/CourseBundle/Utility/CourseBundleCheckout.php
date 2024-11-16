<?php

namespace MasterStudy\Lms\Pro\addons\CourseBundle\Utility;

use MasterStudy\Lms\Pro\addons\CourseBundle\Repository\CourseBundleRepository;

class CourseBundleCheckout {
	public static function add_to_cart( $item_id, $user_id ): array {
		// Convert item_id to an integer and validate input
		$bundle = intval( $item_id );
		if ( empty( $user_id ) || empty( $bundle ) ) {
			return array( 'error' => 'Invalid user or bundle ID' );
		}

		// Retrieve bundle price and check if WooCommerce is enabled
		$quantity = 1;
		$price    = CourseBundleRepository::get_bundle_price( $item_id );

		// Add the item to the cart if not already added
		if ( ! count( stm_lms_get_item_in_cart( $user_id, $item_id, array( 'user_cart_id' ) ) ) > 0 ) {
			stm_lms_add_user_cart( compact( 'user_id', 'item_id', 'quantity', 'price', 'bundle' ) );
		}

		// Generate and return the response
		$response = array(
			'text'     => esc_html__( 'Go to Cart', 'masterstudy-lms-learning-management-system-pro' ),
			'redirect' => \STM_LMS_Options::get_option( 'redirect_after_purchase', false ),
		);

		if ( ! \STM_LMS_Cart::woocommerce_checkout_enabled() ) {
			$response['cart_url'] = esc_url( \STM_LMS_Cart::checkout_url() );
		} else {
			include_once WC_ABSPATH . 'includes/wc-cart-functions.php';
			include_once WC_ABSPATH . 'includes/class-wc-hooks.php';

			if ( is_null( WC()->cart ) ) {
				wc_load_cart();
			}

			WC()->cart->add_to_cart(
				\STM_LMS_Woocommerce::create_product( $item_id ),
				1,
				0,
				array(),
				array(
					'bundle_id' => $item_id,
				)
			);

			$response['cart_url'] = esc_url( wc_get_cart_url() );
		}

		return apply_filters( 'masterstudy_lms_add_to_cart_response', $response, $item_id );
	}
}
