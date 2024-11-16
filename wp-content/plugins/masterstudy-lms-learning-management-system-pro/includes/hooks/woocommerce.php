<?php
//Check if WooCommerce active
if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

// Remove `stm_lms_product` from main archive
if ( is_admin() ) {
	add_action( 'pre_get_posts', 'stm_lms_product_remove_from_archive' );

	function stm_lms_product_remove_from_archive( $query ) {
		if ( empty( $_GET['stm_lms_product'] ) && empty( $_GET['post_status'] ) && // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			( ! empty( $_GET['post_type'] ) && 'product' === $_GET['post_type'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		) {
			$tax_query = array(
				array(
					'taxonomy' => 'product_type',
					'field'    => 'slug',
					'terms'    => 'stm_lms_product',
					'operator' => 'NOT IN',
				),
			);

			$query->set( 'tax_query', $tax_query );
		}
	}
}

// Register endpoint for WooCommerce orders
add_action( 'init', 'register_woocommerce_order_details_endpoint' );

function register_woocommerce_order_details_endpoint() {
	add_rewrite_endpoint( 'woocommerce-order-details', EP_ROOT | EP_PAGES );
}

// Redirect to the order details page
add_action( 'template_redirect', 'order_details_template_redirect' );

function order_details_template_redirect() {
	global $wp_query;

	if ( isset( $wp_query->query_vars['woocommerce-order-details'] ) ) {
		$order_id = intval( $wp_query->query_vars['woocommerce-order-details'] );

		$order = wc_get_order( $order_id );

		if ( $order && STM_LMS_Cart::woocommerce_checkout_enabled() ) {
			STM_LMS_Templates::show_lms_template(
				'account/private/parts/woocommerce-orders-details',
				array(
					'order_id' => $order_id,
				)
			);
			exit;
		} else {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			get_template_part( '404' );
			exit;
		}
	}
}
