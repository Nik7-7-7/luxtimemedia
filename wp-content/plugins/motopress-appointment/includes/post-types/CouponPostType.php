<?php

declare(strict_types=1);

namespace MotoPress\Appointment\PostTypes;

use MotoPress\Appointment\Entities\Booking;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.11.0
 */
class CouponPostType extends AbstractPostType {

	const POST_TYPE = 'mpa_coupon';

	protected function addActions() {
		parent::addActions();

		// "mpa_booking_confirmed" will not work for new bookings in "Confirmation
		// by admin manually" mode. That is why we need to use "mpa_booking_placed_by_user".
		add_action( 'mpa_booking_confirmed', array( $this, 'increaseCouponUsageCount' ) ); // Always listen status updates

		if ( mpapp()->settings()->isAutoConfirmationMode() ) {
			add_action( 'mpa_booking_placed_by_user', array( $this, 'increaseCouponUsageCount' ) );
		}
	}

	/**
	 * @access protected
	 */
	public function increaseCouponUsageCount( Booking $booking ) {
		if ( ! $booking->isConfirmed() ) {
			return; // Placed by user does not mean it's confirmed
		}

		if ( $booking->hasCoupon() ) {
			$coupon = $booking->getCoupon();

			if ( ! is_null( $coupon ) ) {
				$coupon->increaseUsageCount( 'save' );
			}
		}
	}

	/**
	 * @return string
	 */
	public function getLabel() {
		return esc_html__( 'Coupons', 'motopress-appointment' );
	}

	/**
	 * @return string
	 */
	public function getSingularLabel() {
		return esc_html__( 'Coupon', 'motopress-appointment' );
	}

	/**
	 * @return string
	 */
	protected function getDescription() {
		return esc_html__( 'This is where you can add new coupons.', 'motopress-appointment' );
	}

	/**
	 * @return array
	 */
	protected function getLabels() {
		return array(
			'name'               => $this->getLabel(),
			'singular_name'      => $this->getSingularLabel(),
			'add_new'            => esc_html_x( 'Add New', 'Add new coupon', 'motopress-appointment' ),
			'add_new_item'       => esc_html__( 'Add New Coupon', 'motopress-appointment' ),
			'new_item'           => esc_html__( 'New Coupon', 'motopress-appointment' ),
			'edit_item'          => esc_html__( 'Edit Coupon', 'motopress-appointment' ),
			'view_item'          => esc_html__( 'View Coupon', 'motopress-appointment' ),
			'search_items'       => esc_html__( 'Search Coupon', 'motopress-appointment' ),
			'not_found'          => esc_html__( 'No coupon found', 'motopress-appointment' ),
			'not_found_in_trash' => esc_html__( 'No coupons found in Trash', 'motopress-appointment' ),
			'all_items'          => esc_html__( 'Coupons', 'motopress-appointment' ),
		);
	}

	/**
	 * @return array
	 */
	protected function registerArgs() {
		return array(
			'capabilities' => array(
				'create_posts' => 'create_' . static::POST_TYPE . 's',
			),
			'public'       => false,
			'show_in_menu' => mpapp()->pages()->appointmentMenu()->getId(),
			'show_ui'      => true,
			'supports'     => array( 'title' ),
		);
	}
}
