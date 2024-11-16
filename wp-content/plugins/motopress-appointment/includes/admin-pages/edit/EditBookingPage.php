<?php

declare(strict_types=1);

namespace MotoPress\Appointment\AdminPages\Edit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EditBookingPage extends EditNoCommentsPage {

	protected function addActions() {

		parent::addActions();

		// Disalbe all emails and wait until WordPress saves customer information
		// (otherwise customer emails won't work)
		if ( $this->isCurrentSaveNewPage() ) {

			// See EmailsDispatcher::isSuspended()
			add_filter( 'mpa_prevent_emails', '__return_true' );

			// When all post metas are saved (FieldsMetabox::save(), priority 15)
			add_action( "save_post_{$this->postType}", array( $this, 'afterSave' ), 20 );
		}
	}

	public function afterSave() {
		// Handle the action once (in case someone uses wp_update_post() again)
		remove_action( "save_post_{$this->postType}", array( $this, 'afterSave' ), 20 );

		// Allow emails again
		remove_filter( 'mpa_prevent_emails', '__return_true' );

		$booking = mpa_get_booking( 0, true ); // Force reload

		if ( ! is_null( $booking ) ) {
			// Mark booking as placed by admin
			add_post_meta( $booking->getId(), '_mpa_is_admin_booking', true, true );

			/**
			 * Fires after actions "mpa_new_booking_created" and "mpa_booking_{$newStatus}".
			 *
			 * @see BookingStatuses::notifyTransition()
			 *
			 * @since 1.10.1
			 */
			do_action( 'mpa_booking_placed_by_admin', $booking );
		}
	}
}
