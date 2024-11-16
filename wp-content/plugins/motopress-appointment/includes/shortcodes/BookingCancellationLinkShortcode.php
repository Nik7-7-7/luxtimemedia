<?php

namespace MotoPress\Appointment\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BookingCancellationLinkShortcode extends AbstractShortcode {

	const FILTER_NAME_BOOKING_CANCELLATION_LINK_BOOKING = 'mpa_booking_cancellation_link_booking';


	public function __construct() {

		parent::__construct();

		add_filter(
			$this->getName() . '_shortcode_wrapper_class',
			function ( $wrapperClass ) {
				return $wrapperClass . ' mpa-direct-link-action-shortcode';
			}
		);
	}

	public function getName() {
		return 'mpa_direct_link_booking_cancellation_link';
	}

	public function getLabel() {
		return esc_html__( 'Booking cancelation link', 'motopress-appointment' );
	}

	public function getRawShortcode() {
		return '[' . $this->getName() . ']';
	}

	/**
	 * @param array $args
	 * @param string $content
	 * @param string $shortcodeTag
	 */
	protected function renderContent( $args, $content, $shortcodeTag ) {

		$result = '';

		$booking = null;
		$booking = apply_filters( self::FILTER_NAME_BOOKING_CANCELLATION_LINK_BOOKING, $booking );

		if ( $booking ) {

			$cancellationURL = mpapp()->directLinkActions()->getBookingCancellationAction()->getActionURL( $booking );

			$result = '<p><a href="' . $cancellationURL . '" class="button">' .
				esc_html__( 'Cancel Booking', 'motopress-appointment' ) . '</a></p>';
		}

		return $result;
	}
}
