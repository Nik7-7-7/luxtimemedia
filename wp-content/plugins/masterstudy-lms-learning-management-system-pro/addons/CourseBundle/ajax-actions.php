<?php

use MasterStudy\Lms\Pro\addons\CourseBundle\Repository\CourseBundleRepository;
use MasterStudy\Lms\Pro\addons\CourseBundle\Repository\CourseBundleSettings;
use MasterStudy\Lms\Pro\addons\CourseBundle\Utility\CourseBundleCheckout;

add_action( 'wp_ajax_stm_lms_save_bundle', array( CourseBundleRepository::class, 'save_bundle' ) );

function masterstudy_lms_ajax_delete_bundle() {
	do_action( 'stm_lms_delete_bundle' );

	check_ajax_referer( 'stm_lms_delete_bundle', 'nonce' );

	$bundle_id = intval( $_GET['bundle_id'] );

	if ( ! CourseBundleRepository::check_bundle_author( $bundle_id, get_current_user_id() ) ) {
		die;
	}

	wp_delete_post( $bundle_id, true );

	wp_send_json( 'OK' );
}
add_action( 'wp_ajax_stm_lms_delete_bundle', 'masterstudy_lms_ajax_delete_bundle' );

function masterstudy_lms_ajax_change_bundle_status() {
	do_action( 'stm_lms_change_bundle_status' );

	check_ajax_referer( 'stm_lms_change_bundle_status', 'nonce' );

	$bundle_id = intval( $_GET['bundle_id'] );

	if ( ! CourseBundleRepository::check_bundle_author( $bundle_id, get_current_user_id() ) ) {
		die;
	}

	$bundle_status = get_post_status( $bundle_id );
	$post_status   = 'draft';
	$quota         = floatval( ( new CourseBundleSettings() )->get_bundles_limit() ) - floatval( CourseBundleRepository::count() );

	if ( 'draft' === $bundle_status && $quota ) {
		$post_status = 'publish';
	}

	if ( 'draft' === $bundle_status && ! $quota ) {
		wp_send_json( esc_html__( 'Quota exceeded', 'masterstudy-lms-learning-management-system-pro' ) );
	}

	wp_update_post(
		array(
			'ID'          => $bundle_id,
			'post_status' => $post_status,
		)
	);

	wp_send_json( 'OK' );
}
add_action( 'wp_ajax_stm_lms_change_bundle_status', 'masterstudy_lms_ajax_change_bundle_status' );

function masterstudy_lms_ajax_get_user_bundles() {
	wp_send_json( ( new CourseBundleRepository() )->get_bundles() );
}
add_action( 'wp_ajax_stm_lms_get_user_bundles', 'masterstudy_lms_ajax_get_user_bundles' );

function ajax_add_to_cart_bundle() {
	check_ajax_referer( 'stm_lms_add_bundle_to_cart', 'nonce' );
	wp_send_json(
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		CourseBundleCheckout::add_to_cart( intval( $_GET['item_id'] ), get_current_user_id() )
	);
}
add_action( 'wp_ajax_stm_lms_add_bundle_to_cart', 'ajax_add_to_cart_bundle' );
