<?php

namespace MotoPress\Appointment\Metaboxes\Booking;

use MotoPress\Appointment\Metaboxes\CustomMetabox;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ResendBookingConfirmationEmailMetabox extends CustomMetabox {


	protected function theName(): string {
		return 'resend_booking_confirmation_email_metabox';
	}

	public function getLabel(): string {
		return esc_html__( 'Resend Confirmation Email', 'motopress-appointment' );
	}

	protected function addActions() {

		parent::addActions();

		add_filter(
			'post_updated_messages',
			function( array $messages ) {

				// phpcs:ignore
				if ( ! empty( $_GET['mpa_resend_confirmation'] ) ) {

					$messages['post'][4] = esc_html__( 'Confirmation email has been sent to the customer.', 'motopress-appointment' );
				}

				return $messages;
			}
		);
	}


	protected function renderMetabox(): string {

		global $post;
		$currentBookingId = $post->ID;

		$booking          = mpapp()->repositories()->booking()->findById( $currentBookingId );
		$isButtonDisabled = null === $booking || empty( $booking->getCustomerEmail() ) ||
			! $booking->isConfirmed();

		ob_start();
		?>

		<p><?php esc_html_e( "Send a copy of the booking confirmation email to the customer's email address.", 'motopress-appointment' ); ?></p>

		<?php if ( $isButtonDisabled ) : ?>

			<p><?php esc_html_e( "It is not possible to send a booking confirmation email because the booking is not confirmed or the customer's email address is unknown.", 'motopress-appointment' ); ?></p>
		<?php endif; ?>

		<p>
			<input type="hidden" name="mpa_booking_id" value="<?php echo esc_attr( $currentBookingId ); ?>" />
			<input type="submit" name="mpa_resend_confirmation" class="button button-primary button-large" value="<?php esc_attr_e( 'Resend Confirmation Email', 'motopress-appointment' ); ?>" <?php echo ( $isButtonDisabled ? 'disabled="disabled"' : '' ); ?>/>
		</p>

		<?php
		return ob_get_clean();
	}


	protected function saveValues( array $values, int $postId, \WP_Post $post ) {

		// phpcs:ignore
		if ( isset( $_REQUEST['mpa_resend_confirmation'] ) && isset( $_REQUEST['mpa_booking_id'] )) {

			// phpcs:ignore
			$bookingId = absint( $_REQUEST['mpa_booking_id'] );
			$booking   = mpapp()->repositories()->booking()->findById( $bookingId );

			if ( ! empty( $booking->getCustomerEmail() ) && $booking->isConfirmed() ) {

				if ( 'auto' === mpapp()->settings()->getConfirmationMode() ) {

					mpapp()->emailsDispatcher()->triggerEmail(
						mpapp()->emails()->customerNewBooking(),
						$booking
					);

				} elseif ( 'manual' === mpapp()->settings()->getConfirmationMode() ) {

					mpapp()->emailsDispatcher()->triggerEmail(
						mpapp()->emails()->customerApprovedBooking(),
						$booking
					);

				} elseif ( 'payment' === mpapp()->settings()->getConfirmationMode() ) {

					mpapp()->emailsDispatcher()->triggerEmail(
						mpapp()->emails()->customerApprovedPayment(),
						$booking
					);
				}

				// after post save user redirected to another url
				// we need to add get parameter to this url
				// to be able to show corresponding after resend message to the user
				add_filter(
					'redirect_post_location',
					function ( string $location, int $post_id ) {
						return add_query_arg( 'mpa_resend_confirmation', 'true', $location );
					},
					10,
					2
				);
			}
		}
	}
}
