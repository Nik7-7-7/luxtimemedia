<?php

namespace MotoPress\Appointment\Repositories;

use MotoPress\Appointment\Entities\Booking;
use MotoPress\Appointment\PostTypes\BookingPostType;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 *
 * @see Booking
 */
class BookingRepository extends AbstractRepository {

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function entitySchema() {

		return array(
			'post'     => array( 'ID', 'post_status' ),
			'postmeta' => array(
				'_mpa_uid'            => true,
				'_mpa_total_price'    => true,
				'_mpa_customer_id'    => true,
				'_mpa_customer_name'  => true,
				'_mpa_customer_email' => true,
				'_mpa_customer_phone' => true,
				'_mpa_customer_notes' => true,
				'_mpa_coupon_id'      => true,
			),
		);
	}

	/**
	 * @param array $postData
	 * @return Booking
	 *
	 * @since 1.0
	 */
	protected function mapPostDataToEntity( $postData ) {

		$id = (int) $postData['ID'];

		$fields = array(
			'uid'           => $postData['uid'],
			'status'        => $postData['post_status'],
			'totalPrice'    => (float) $postData['total_price'],
			'reservations'  => mpapp()->repositories()->reservation()->findAllByBooking( $id ),
			'customerId'    => (int) $postData['customer_id'],
			'customerNotes' => $postData['customer_notes'],
			'couponId'      => (int) $postData['coupon_id'],
		);

		// Additional customer data stored in postmeta
		if ( isset( $postData['customer_name'] ) && $postData['customer_name'] ) {
			$fields['customerName'] = $postData['customer_name'];
		}
		if ( isset( $postData['customer_email'] ) && $postData['customer_email'] ) {
			$fields['customerEmail'] = $postData['customer_email'];
		}
		if ( isset( $postData['customer_phone'] ) && $postData['customer_phone'] ) {
			$fields['customerPhone'] = $postData['customer_phone'];
		}

		return new Booking( $id, $fields );
	}


	/**
	 * Create new or update booking post in database.
	 */
	public function saveBooking( Booking $booking ) {

		$postId = $booking->getId();

		if ( ! $booking->getId() ) {

			$postId = wp_insert_post(
				array(
					'post_type'   => BookingPostType::POST_TYPE,
					'post_status' => $booking->getStatus(),
				),
				true
			);

			if ( is_wp_error( $postId ) ) {
				return;
			} else {
				$booking->setId( $postId );
			}
		} else {

			mpa_update_post_status( $postId, $booking->getStatus() );
		}

		// update booking title and name
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

		// Update booking meta fields
		mpa_add_post_uid( $postId, $booking->getUid() );

		update_post_meta( $postId, '_mpa_total_price', $booking->getTotalPrice() );
		update_post_meta( $postId, '_mpa_coupon_id', $booking->getCouponId() );
		update_post_meta( $postId, '_mpa_customer_notes', $booking->getCustomerNotes() );

		// Update booking meta
		do_action( 'mpa_update_booking_post_meta', $booking );

		try {
			mpapp()->repositories()->customer()->saveBookingCustomer( $booking );
			// phpcs:ignore
		} catch ( \Exception $e ) {
			// todo: We are currently suppressing the exception,
			// but it may be necessary to handle the exception in the future.
		}

		// Save reservations
		foreach ( $booking->getReservations() as $reservation ) {

			$reservation->setBookingId( $postId );

			mpapp()->repositories()->reservation()->saveReservation( $reservation );
		}

		// Notify others about new booking
		if ( false !== $postId && 'auto-draft' !== $booking->getStatus() ) {

			mpapp()->repositories()->customer()->updateLastActive( $booking->getCustomerId() );

			/**
			 * Fires after actions "mpa_new_booking_created" and "mpa_booking_{$newStatus}".
			 * @see \MotoPress\Appointment\PostTypes\Statuses\BookingStatuses::notifyTransition()
			 */
			do_action( 'mpa_booking_placed_by_user', $booking );
		}
	}

	/**
	 * Remove old reservations and store a new ones.
	 */
	public function updateBookingReservations( Booking $booking, array $newReservations ) {

		$newUids = array();

		foreach ( $newReservations as $newReservation ) {

			$newUids[] = $newReservation->getUid();
		}

		// Remove absent reservations from booking
		foreach ( $booking->getReservations() as $reservation ) {

			if ( ! in_array( $reservation->getUid(), $newUids, true ) ) {

				wp_delete_post( $reservation->getId(), true );
			}
		}

		$reservationRepository = mpapp()->repositories()->reservation();

		// Update / add new reservations
		foreach ( $newReservations as $reservation ) {

			// Try to find ID by UID and match new reservation to the existing one
			if ( ! $reservation->getId() ) {

				$reservation->setId(
					$reservationRepository->findIdByMeta(
						'uid',
						$reservation->getUid()
					)
				);
			}

			$reservationRepository->saveReservation( $reservation );
		}

		$booking->setReservations( $newReservations );

		do_action( 'mpa_reservations_updated', $booking );
	}


	public function saveBookingPrices( Booking $booking ): Booking {

		update_post_meta( $booking->getId(), '_mpa_total_price', $booking->getTotalPrice() );

		$reservationRepository = mpapp()->repositories()->reservation();

		foreach ( $booking->getReservations() as $reservation ) {

			$reservationRepository->saveReservation( $reservation );
		}

		return $booking;
	}
}
