<?php

namespace MotoPress\Appointment\DirectLinkActions\Pages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ReservationReceivedPage extends AbstractRealPage {

	public function __construct() {

		parent::__construct();

		add_filter(
			\MotoPress\Appointment\Shortcodes\ReservationReceivedShortcode::FILTER_NAME_RESERVATION_RECEIVED_BOOKING,
			function ( $booking ) {
				$foundBooking = $this->getEntity();
				return null === $foundBooking ? $booking : $foundBooking;
			}
		);
	}

	/**
	 * @return string
	 */
	protected function getPageSlug() {
		return 'reservation-received';
	}

	/**
	 * @return string
	 */
	protected function optionNameWithPageId() {
		return 'mpa_reservation_received_page';
	}

	/**
	 * @return string
	 */
	protected function getTitle() {
		return esc_html__( 'Reservation Received', 'motopress-appointment' );
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
	protected function mustContent() {
		return $this->defaultContent();
	}

	/**
	 * @return string
	 */
	protected function defaultContent() {
		return mpapp()->shortcodes()->reservationReceived()->getRawShortcode();
	}

	/**
	 * @return int
	 */
	protected function getPageId() {
		return mpapp()->settings()->getReservationReceivedPageId();
	}
}
