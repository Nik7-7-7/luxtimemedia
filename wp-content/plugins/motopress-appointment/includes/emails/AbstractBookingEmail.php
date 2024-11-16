<?php

namespace MotoPress\Appointment\Emails;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.18.0
 */
abstract class AbstractBookingEmail extends AbstractEmail {

	/**
	 * @var \MotoPress\Appointment\Entities\Booking|null
	 */
	protected $booking = null;

	/**
	 * @param \MotoPress\Appointment\Entities\Booking|null $booking
	 */
	public function setBooking( $booking ) {
		$this->booking = $booking;
		$this->tags->setEntity( $booking );
	}
}