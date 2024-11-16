<?php

namespace MotoPress\Appointment\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BookingDetailsShortcode extends AbstractShortcode {

	const FILTER_NAME_BOOKING_DETAILS_BOOKING = 'mpa_booking_details_booking';


	public function getName(): string {
		return mpa_prefix( 'booking_details' );
	}

	public function getLabel(): string {
		return esc_html__( 'Booking Details', 'motopress-appointment' );
	}

	public function getRawShortcode() {
		return '[' . $this->getName() . ']';
	}

	/**
	 * @param array $args
	 * @param string $content
	 * @param string $shortcodeTag
	 */
	protected function renderContent( $args, $content, $shortcodeTag ): string {

		mpa_assets()->enqueueBundle( 'mpa-public' );

		$result = '';

		$booking = null;
		$booking = apply_filters( self::FILTER_NAME_BOOKING_DETAILS_BOOKING, $booking );

		if ( $booking ) {

			$result = mpa_render_template(
				'shortcodes/booking-details.php',
				array(
					'booking' => $booking,
				)
			);
		}

		return $result;
	}
}
