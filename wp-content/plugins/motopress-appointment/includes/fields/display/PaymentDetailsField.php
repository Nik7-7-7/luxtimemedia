<?php

namespace MotoPress\Appointment\Fields\Display;

use MotoPress\Appointment\Entities\Booking;
use MotoPress\Appointment\Fields\AbstractField;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.5.0
 */
class PaymentDetailsField extends AbstractField {

	/** @since 1.5.0 */
	const TYPE = 'payment-details';

	/**
	 * @since 1.5.0
	 * @var int|Booking
	 */
	protected $booking = 0;

	/**
	 * @since 1.5.0
	 *
	 * @param array $args
	 */
	protected function setupArgs( $args ) {

		parent::setupArgs( $args );

		if ( ! $this->booking ) {
			$this->booking = (int) get_the_ID();
		}
	}

	/**
	 * @since 1.5.0
	 *
	 * @return array
	 */
	protected function mapFields() {
		return parent::mapFields() + array(
			'booking' => 'booking',
		);
	}

	/**
	 * @since 1.5.0
	 *
	 * @return string
	 */
	public function renderInput() {

		$booking = is_object( $this->booking ) ? $this->booking : mpa_get_booking( $this->booking );

		if ( ! is_null( $booking ) ) {
			return mpa_render_template(
				'private/fields/payment-details.php',
				array(
					'booking'  => $booking,
					'payments' => $booking->getPayments(),
				)
			);
		} else {
			return mpa_tmpl_placeholder();
		}
	}
}
