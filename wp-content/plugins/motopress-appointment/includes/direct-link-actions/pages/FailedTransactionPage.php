<?php

namespace MotoPress\Appointment\DirectLinkActions\Pages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FailedTransactionPage extends AbstractRealPage {


	public function __construct() {

		parent::__construct();

		add_filter(
			\MotoPress\Appointment\Shortcodes\BookingDetailsShortcode::FILTER_NAME_BOOKING_DETAILS_BOOKING,
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
		return 'failed-transaction';
	}

	/**
	 * @return string
	 */
	protected function optionNameWithPageId() {
		return 'mpa_failed_transaction_page';
	}

	/**
	 * @return string
	 */
	protected function getTitle() {
		return esc_html__( 'Transaction Failed', 'motopress-appointment' );
	}

	/**
	 * @return string
	 */
	protected function mustContent() {
		return '';
	}

	/**
	 * @return string
	 */
	protected function defaultContent() {
		return esc_html__( 'Unfortunately, your transaction cannot be completed at this time. Please try again or contact us.', 'motopress-appointment' );
	}

	/**
	 * @return int
	 */
	protected function getPageId() {
		return mpapp()->settings()->getFailedTransactionPageId();
	}
}
