<?php

namespace MotoPress\Appointment\Registries;

use MotoPress\Appointment\Emails\TemplateParts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.1.0
 */
class TemplatesRegistry {

	/**
	 * @var array
	 *
	 * @since 1.1.0
	 */
	protected $templates = array();

	/**
	 * @return TemplateParts\AdminReservationDetailsTemplatePart
	 *
	 * @since 1.1.0
	 */
	public function adminReservationDetails() {
		if ( ! isset( $this->templates['adminReservationDetails'] ) ) {
			$this->templates['adminReservationDetails'] = new TemplateParts\AdminReservationDetailsTemplatePart();
		}

		return $this->templates['adminReservationDetails'];
	}

	/**
	 * @return TemplateParts\AdminPaymentDetailsTemplatePart
	 *
	 * @since 1.15.0
	 */
	public function adminPaymentDetails() {
		if ( ! isset( $this->templates['adminPaymentDetails'] ) ) {
			$this->templates['adminPaymentDetails'] = new TemplateParts\AdminPaymentDetailsTemplatePart();
		}

		return $this->templates['adminPaymentDetails'];
	}

	/**
	 * @return TemplateParts\CustomerReservationDetailsTemplatePart
	 *
	 * @since 1.1.0
	 */
	public function customerReservationDetails() {
		if ( ! isset( $this->templates['customerReservationDetails'] ) ) {
			$this->templates['customerReservationDetails'] = new TemplateParts\CustomerReservationDetailsTemplatePart();
		}

		return $this->templates['customerReservationDetails'];
	}

	/**
	 * @return TemplateParts\CustomerBookingCancellationTemplatePart
	 *
	 * @since 1.15.0
	 */
	public function customerBookingCancellation() {
		if ( ! isset( $this->templates['customerBookingCancellation'] ) ) {
			$this->templates['customerBookingCancellation'] = new TemplateParts\CustomerBookingCancellationTemplatePart();
		}

		return $this->templates['customerBookingCancellation'];
	}

	/**
	 * @return TemplateParts\CustomerPaymentDetailsTemplatePart
	 *
	 * @since 1.15.0
	 */
	public function customerPaymentDetails() {
		if ( ! isset( $this->templates['customerPaymentDetails'] ) ) {
			$this->templates['customerPaymentDetails'] = new TemplateParts\CustomerPaymentDetailsTemplatePart();
		}

		return $this->templates['customerPaymentDetails'];
	}

	/**
	 * @return TemplateParts\AbstractTemplatePart[]
	 *
	 * @since 1.1.0
	 */
	public function getEmailTemplateParts() {
		$isPaymentEnable = mpapp()->settings()->isPaymentsEnabled();

		$templateParts[] = $this->adminReservationDetails();

		if ( $isPaymentEnable ) {
			$templateParts[] = $this->adminPaymentDetails();
		}

		$templateParts[] = $this->customerReservationDetails();

		if ( $isPaymentEnable ) {
			$templateParts[] = $this->customerPaymentDetails();
		}

		if ( mpapp()->settings()->isUserCanBookingCancellation() ) {
			$templateParts[] = $this->customerBookingCancellation();
		}

		$templateParts = $this->mapByName( $templateParts );

		/** @since 1.1.0 */
		$templateParts = apply_filters( 'mpa_email_template_parts', $templateParts );

		return $templateParts;
	}

	/**
	 * @param string $name
	 * @return TemplateParts\AbstractTemplatePart|null
	 *
	 * @since 1.1.0
	 */
	public function getEmailTemplatePartByName( $name ) {
		// 'admin_reservation_details'
		$entryId = mpa_str_to_method_name( $name );     // 'adminReservationDetails'

		// Get template part
		if ( method_exists( $this, $entryId ) ) {
			$entry = $this->$entryId();

			if ( $entry instanceof TemplateParts\AbstractTemplatePart ) {
				return $entry;
			} else {
				return null;
			}
		} else {
			return null;
		}
	}

	/**
	 * @param TemplateParts\AbstractTemplatePart[] $entries [Entry ID => AbstractTemplatePart]
	 * @return TemplateParts\AbstractTemplatePart[] [Entry name => AbstractTemplatePart]
	 *
	 * @since 1.1.0
	 */
	protected function mapByName( $entries ) {
		$mapped = array();

		foreach ( $entries as $entry ) {
			$mapped[ $entry->getName() ] = $entry;
		}

		return $mapped;
	}
}
