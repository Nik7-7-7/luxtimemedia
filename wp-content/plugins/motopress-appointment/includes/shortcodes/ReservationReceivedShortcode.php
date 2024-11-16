<?php

namespace MotoPress\Appointment\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ReservationReceivedShortcode extends AbstractShortcode {

	const FILTER_NAME_RESERVATION_RECEIVED_BOOKING = 'mpa_reservation_received_booking';


	public function getName(): string {
		return mpa_prefix( 'reservation_received' );
	}

	public function getLabel(): string {
		return esc_html__( 'Reservation Received', 'motopress-appointment' );
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
		$booking = apply_filters( self::FILTER_NAME_RESERVATION_RECEIVED_BOOKING, $booking );

		if ( $booking ) {

			mpa_assets()->enqueueStyle( 'mpa-public' );

			$result = mpa_render_template(
				'shortcodes/reservation-received.php',
				array(
					'booking' => $booking,
				)
			);
		}

		return $result;
	}
}
