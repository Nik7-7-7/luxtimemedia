<?php

namespace MotoPress\Appointment\Plugin\Settings;

use MotoPress\Appointment\PostTypes\Statuses\BookingStatuses;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait GeneralSettings {

	public function getBusinessName(): string {
		return get_bloginfo( 'name' );
	}

	/**
	 * @return int Step for time slots (in minutes).
	 *
	 * @since 1.0
	 */
	public function getTimeStep() {
		return (int) get_option( 'mpa_default_time_step', 30 );
	}

	/**
	 * @return string 'hour'|'none'
	 *
	 * @since 1.2.1
	 */
	public function getTimeStepAlignment() {
		return get_option( 'mpa_time_step_alignment', 'none' );
	}

	/**
	 * @return string 'pending' or 'confirmed'.
	 *
	 * @since 1.1.0
	 */
	public function getDefaultBookingStatus() {

		if ( $this->isAutoConfirmationMode() ) {
			return BookingStatuses::STATUS_CONFIRMED;
		} else {
			return BookingStatuses::STATUS_PENDING;
		}
	}

	/**
	 * @since 1.5.0
	 *
	 * @return string
	 */
	public function getConfirmationMode() {
		return get_option( 'mpa_confirmation_mode', 'auto' );
	}

	/**
	 * @since 1.10.2
	 *
	 * @return int
	 */
	public function getTermsPageIdForAcceptance() {
		return (int) get_option( 'mpa_terms_page_id_for_acceptance', 0 );
	}

	/**
	 * @since 1.11.0
	 */
	public function isAutoConfirmationMode(): bool {
		return 'auto' == $this->getConfirmationMode();
	}

	/**
	 * @since 1.11.0
	 */
	public function isAdminConfirmationMode(): bool {
		return 'manual' == $this->getConfirmationMode();
	}

	/**
	 * @since 1.11.0
	 */
	public function isPaymentConfirmationMode(): bool {
		return 'payment' == $this->getConfirmationMode();
	}

	/**
	 * @since 1.4.0
	 *
	 * @return bool
	 */
	public function isMultibookingEnabled() {
		return (bool) get_option( 'mpa_allow_multibooking', false );
	}

	/**
	 * @since 1.11.0
	 *
	 * @return bool
	 */
	public function isCouponsEnabled() {
		return (bool) get_option( 'mpa_allow_coupons', false );
	}

	/**
	 * @since 1.15.0
	 *
	 * @return bool
	 */
	public function isUserCanBookingCancellation() {
		return (bool) get_option( 'mpa_user_can_cancel_booking', false );
	}

	/**
	 * @since 1.15.0
	 *
	 * @return int
	 */
	public function getBookingCancellationPage() {
		return (int) get_option( 'mpa_booking_cancellation_page', false );
	}

	/**
	 * @since 1.15.0
	 *
	 * @return int
	 */
	public function getBookingCancelledPage() {
		return (int) get_option( 'mpa_booking_cancelled_page', false );
	}

	/**
	 * @since 1.18.0
	 *
	 * @return string
	 */
	public function getCustomerAccountCreationMode(): string {
		return get_option( 'mpa_customer_account_creation_mode', 'account_creation_disabled' );
	}

	/**
	 * @since 1.18.0
	 *
	 * @return bool
	 */
	public function isCustomerAccountCreatingDisabled(): bool {
		return 'account_creation_disabled' == $this->getCustomerAccountCreationMode();
	}

	/**
	 * @since 1.18.0
	 *
	 * @return bool
	 */
	public function isCustomerAccountCreateByCustomerRequest(): bool {
		return 'create_by_customer_request' == $this->getCustomerAccountCreationMode();
	}

	/**
	 * @since 1.18.0
	 *
	 * @return bool
	 */
	public function isCustomerAccountCreateAutomatically(): bool {
		return 'create_automatically' == $this->getCustomerAccountCreationMode();
	}

	/**
	 * @since 1.18.0
	 *
	 * @return bool
	 */
	public function isAllowCustomerAccountCreation(): bool {
		return 'account_creation_disabled' != $this->getCustomerAccountCreationMode();
	}

	/**
	 * @since 1.18.0
	 *
	 * @return int
	 */
	public function getCustomerAccountPage(): int {
		return (int) get_option( 'mpa_customer_account_page', false );
	}

	/**
	 * @since 1.5.0
	 *
	 * @return string
	 */
	public function getCountry() {
		return get_option( 'mpa_country', '' );
	}

	/**
	 * @return string Currency code, like 'EUR'.
	 *
	 * @since 1.0
	 */
	public function getCurrency() {
		return get_option( 'mpa_currency', 'EUR' );
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getCurrencySymbol() {
		return mpapp()->bundles()->currencies()->getSymbol( $this->getCurrency() );
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getCurrencyPosition() {
		return get_option( 'mpa_currency_position', 'before' );
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getDecimalSeparator() {
		return get_option( 'mpa_decimal_separator', '.' );
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getThousandSeparator() {
		return get_option( 'mpa_thousand_separator', ',' );
	}

	/**
	 * @return int
	 *
	 * @since 1.0
	 */
	public function getDecimalsCount() {
		return (int) get_option( 'mpa_number_of_decimals', 2 );
	}

	/**
	 * @return string
	 *
	 * @since 1.22.0
	 */
	public function getTimezone() {
		return wp_timezone_string();
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getDateFormat() {
		return get_option( 'date_format', 'F j, Y' );
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getTimeFormat() {
		return get_option( 'time_format', 'H:i' );
	}

	/**
	 * @param string $glue Optional. Glue string to concatenate the date and
	 *     time parts. ' @ ' by default.
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getPostDateTimeFormat( $glue = ' @ ' ) {
		return $this->getDateFormat() . $glue . $this->getTimeFormat();
	}

	/**
	 * @return int
	 *
	 * @since 1.0
	 */
	public function getFirstDayOfWeek() {
		return (int) get_option( 'start_of_week', 0 );
	}

	/**
	 * @since 1.4.0
	 *
	 * @return array [width, height]
	 */
	public function getThumbnailSize() {
		return array(
			'width'  => (int) get_option( 'thumbnail_size_w', 150 ),
			'height' => (int) get_option( 'thumbnail_size_h', 150 ),
		);
	}

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	public function getGeneralSettings() {
		return array(
			'business_name'                   => $this->getBusinessName(),
			'default_time_step'               => $this->getTimeStep(),
			'default_booking_status'          => $this->getDefaultBookingStatus(),
			'confirmation_mode'               => $this->getConfirmationMode(),
			'terms_page_id_for_acceptance'    => $this->getTermsPageIdForAcceptance(),
			'allow_multibooking'              => $this->isMultibookingEnabled(),
			'allow_coupons'                   => $this->isCouponsEnabled(),
			'allow_customer_account_creation' => $this->isAllowCustomerAccountCreation(),
			'country'                         => $this->getCountry(),
			'currency'                        => $this->getCurrency(),
			'currency_symbol'                 => $this->getCurrencySymbol(),
			'currency_position'               => $this->getCurrencyPosition(),
			'decimal_separator'               => $this->getDecimalSeparator(),
			'thousand_separator'              => $this->getThousandSeparator(),
			'number_of_decimals'              => $this->getDecimalsCount(),
			'timezone'                        => $this->getTimezone(),
			'date_format'                     => $this->getDateFormat(),
			'time_format'                     => $this->getTimeFormat(),
			'post_date_time_format'           => $this->getPostDateTimeFormat(),
			'week_starts_on'                  => $this->getFirstDayOfWeek(),
			'thumbnail_size'                  => $this->getThumbnailSize(),
		);
	}
}
