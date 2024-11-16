<?php

namespace MotoPress\Appointment\DirectLinkActions\Pages;

use MotoPress\Appointment\DirectLinkActions\Helpers\BookingHelper;
use MotoPress\Appointment\PostTypes\Statuses\BookingStatuses;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.0
 */
class BookingCancellationPage extends AbstractRealPage {


	public function __construct() {

		parent::__construct();

		add_filter(
			"mpa_direct_link_action_page_pre_render_{$this->getPageSlug()}",
			function ( $wpPost, $booking ) {

				if ( BookingStatuses::STATUS_CANCELLED === $booking->getStatus() ) {

					$wpPost->post_content = esc_html__( 'Booking is already canceled.', 'motopress-appointment' );
					return $wpPost;
				}

				if ( ! BookingHelper::isCanBeCancelled( $booking ) ) {

					$wpPost->post_content = esc_html__( 'Cancelation of your booking is not possible for some reason. Please contact the website administrator.', 'motopress-appointment' );
					return $wpPost;
				}

				return $wpPost;
			},
			10,
			2
		);

		add_filter(
			\MotoPress\Appointment\Shortcodes\BookingDetailsShortcode::FILTER_NAME_BOOKING_DETAILS_BOOKING,
			function ( $booking ) {
				$foundBooking = $this->getEntity();
				return null === $foundBooking ? $booking : $foundBooking;
			}
		);

		add_filter(
			\MotoPress\Appointment\Shortcodes\BookingCancellationLinkShortcode::FILTER_NAME_BOOKING_CANCELLATION_LINK_BOOKING,
			function ( $booking ) {
				$foundBooking = $this->getEntity();
				return null === $foundBooking ? $booking : $foundBooking;
			}
		);
	}

	/**
	 * @return \MotoPress\Appointment\Repositories\BookingRepository
	 */
	protected function getEntityRepository() {
		return mpapp()->repositories()->booking();
	}

	/**
	 * @return string
	 */
	protected function getPageSlug() {
		return 'booking-cancellation';
	}

	/**
	 * @return string
	 */
	protected function optionNameWithPageId() {
		return 'mpa_booking_cancellation_page';
	}

	/**
	 * @return string
	 */
	protected function getTitle() {
		return esc_html__( 'Booking Cancelation Page', 'motopress-appointment' );
	}

	/**
	 * @return string
	 */
	protected function mustContent() {
		return mpapp()->shortcodes()->bookingCancellationLink()->getRawShortcode();
	}

	/**
	 * @return string
	 */
	protected function defaultContent() {
		return mpapp()->shortcodes()->bookingDetails()->getRawShortcode() .
			mpapp()->shortcodes()->bookingCancellationLink()->getRawShortcode();
	}

	/**
	 * @return int
	 */
	protected function getPageId() {
		return mpapp()->settings()->getBookingCancellationPage();
	}
}
