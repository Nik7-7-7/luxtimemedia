<?php

namespace MotoPress\Appointment\Metaboxes\Booking;

use MotoPress\Appointment\Metaboxes\FieldsMetabox;
use MotoPress\Appointment\Helpers\AvailabilityHelper;
use MotoPress\Appointment\Helpers\PriceCalculationHelper;

use WP_Post;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class BookingPriceMetabox extends FieldsMetabox {

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	protected function theName(): string {
		return 'booking_price_metabox';
	}

	/**
	 * @since 1.0
	 *
	 * @return array
	 */
	protected function theFields() {

		$allowedCoupons = mpapp()->repositories()->coupon()->findAll(
			array(
				'fields' => array( 'id' => 'code' ),
			)
		);

		return array(
			'total_price'       => array(
				'type'  => 'price',
				'label' => esc_html__( 'Total Price', 'motopress-appointment' ),
			),
			'coupon_id'         => array(
				'type'    => 'select',
				'label'   => esc_html__( 'Coupon', 'motopress-appointment' ),
				'options' => mpa_no_value( 0 ) + $allowedCoupons,
				'default' => 0,
			),
			'reserved_services' => array(
				'type'  => 'edit-reservations',
				'label' => esc_html__( 'Reserved Services', 'motopress-appointment' ),
			),
			'payment_details'   => array(
				'type'  => 'payment-details',
				'label' => esc_html__( 'Payment Details', 'motopress-appointment' ),
			),
		);
	}

	public function getLabel(): string {
		return esc_html__( 'Booking Price', 'motopress-appointment' );
	}

	protected function parseValues( int $postId, WP_Post $post ): array {

		$values = parent::parseValues( $postId, $post );

		// phpcs:ignore
		if ( isset( $_POST['reservations'] ) ) {

			$servicesField = $this->getField( 'reserved_services' );

			if ( null !== $servicesField ) {

				// data will be validated in the setValue()
				// phpcs:ignore
				$servicesField->setValue( $_POST['reservations'], 'validate' );
				$values['reservations'] = $servicesField->getValue( 'save' );
			}
		}

		return $values;
	}

	/**
	 * @param array $values [add, update, delete, reservations]
	 */
	protected function saveValues( array $values, int $postId, WP_Post $post ) {

		// TODO: move all code for booking saving to BookingRepository
		// to make sure we use the same code for all savings

		// Load booking before change its data to be able
		// to check do we need recalculate prices.
		$booking = mpapp()->repositories()->booking()->findById( get_the_ID() );

		// Do we need to update the price because of new coupon?
		$isPriceRecalculationNeeded = isset( $values['update']['_mpa_coupon_id'] ) &&
			null !== $booking &&
			absint( $values['update']['_mpa_coupon_id'] ) !== $booking->getCouponId();

		// update bookings data
		parent::saveValues( $values, $postId, $post );

		// update reservations
		if ( null !== $booking && isset( $values['reservations'] ) ) {

			mpapp()->repositories()->booking()->updateBookingReservations(
				$booking,
				$values['reservations']
			);

			$isPriceRecalculationNeeded = true;
		}

		if ( $isPriceRecalculationNeeded ) {

			// reload booking with updated data
			$booking = mpapp()->repositories()->booking()->findById( get_the_ID(), true );

			if ( null !== $booking ) {

				PriceCalculationHelper::updateBookingPrices( $booking );
			}
		}

		// update booking title and name when booking was created from admin area
		$wpPost = get_post( $postId );

		if ( ! empty( $wpPost ) &&
		     ( empty( $wpPost->post_title ) || empty( $wpPost->post_name ) )
		) {
			wp_update_post(
				array(
					'ID'         => $postId,
					// Translators: %d: Booking ID.
					'post_title' => sprintf( esc_html__( 'Booking #%d', 'motopress-appointment' ), $postId ),
					'post_name'  => "mpa-booking-{$postId}",
				)
			);
		}
	}

	/**
	 * @param int $postId
	 * @param WP_Post $post
	 *
	 * @return bool
	 * @throws \Exception
	 * @since 1.22.0
	 */
	protected function canSave( int $postId, \WP_Post $post ): bool {
		$canSave = parent::canSave( $postId, $post );

		$values = $this->parseValues( $postId, $post );

		if ( ! isset( $values['reservations'] ) ) {
			return true;
		}

		$booking = mpapp()->repositories()->booking()->findById( $postId );

		if ( ! $booking ) {
			return true;
		}

		$unblockedTimeSlotsStatuses = mpapp()->postTypes()->booking()->statuses()->getUnblockedTimeSlotsStatuses();

		// status was not changed
		if ( $booking->getReservations() === $values['reservations'] ) {
			return true;
		}

		$newBooking = clone $booking;
		$newBooking->setReservations( $values['reservations'] );

		// if current booking has a time-slot conflict which any bookings,
		// prevent updating reservations and show error notice
		if ( ! in_array( $post->post_status, $unblockedTimeSlotsStatuses ) &&
		     ! AvailabilityHelper::isSlotsAvailableForBooking( $newBooking->getReservations() )
		) {
			$errorMessage = esc_html__( 'Sorry, the selected time slot is already booked.', 'motopress-appointment' );

			throw new \Exception( $errorMessage );
		}

		return $canSave;
	}
}
