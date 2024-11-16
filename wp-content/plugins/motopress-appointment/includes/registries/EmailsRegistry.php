<?php

namespace MotoPress\Appointment\Registries;

use MotoPress\Appointment\Emails\Admin;
use MotoPress\Appointment\Emails\Customer;
use MotoPress\Appointment\Emails\Notification\NotificationEmail;
use MotoPress\Appointment\Emails\AbstractEmail;
use MotoPress\Appointment\Entities\Notification;
use MotoPress\Appointment\Entities\Reservation;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.1.0
 */
class EmailsRegistry {

	/**
	 * @var AbstractEmail[]
	 *
	 * @since 1.1.0
	 */
	protected $emails = array();

	/**
	 * @return Admin\AdminNewBookingEmail
	 *
	 * @since 1.1.0
	 */
	public function adminNewBooking() {
		if ( ! isset( $this->emails['adminNewBooking'] ) ) {
			$this->emails['adminNewBooking'] = new Admin\AdminNewBookingEmail();
		}

		return $this->emails['adminNewBooking'];
	}

	/**
	 * @return Admin\AdminPendingBookingEmail
	 *
	 * @since 1.1.0
	 */
	public function adminPendingBooking() {
		if ( ! isset( $this->emails['adminPendingBooking'] ) ) {
			$this->emails['adminPendingBooking'] = new Admin\AdminPendingBookingEmail();
		}

		return $this->emails['adminPendingBooking'];
	}

	/**
	 * @since 1.5.0
	 *
	 * @return Admin\AdminApprovedBookingEmail
	 */
	public function adminApprovedBooking() {
		if ( ! isset( $this->emails[ __FUNCTION__ ] ) ) {
			$this->emails[ __FUNCTION__ ] = new Admin\AdminApprovedBookingEmail();
		}

		return $this->emails[ __FUNCTION__ ];
	}

	/**
	 * @return Admin\AdminCancelledBookingEmail
	 *
	 * @since 1.15.0
	 */
	public function adminCancelledBooking() {
		if ( ! isset( $this->emails['adminCancelledBooking'] ) ) {
			$this->emails['adminCancelledBooking'] = new Admin\AdminCancelledBookingEmail();
		}

		return $this->emails['adminCancelledBooking'];
	}

	/**
	 * @return Customer\CustomerNewBookingEmail
	 *
	 * @since 1.1.0
	 */
	public function customerNewBooking() {
		if ( ! isset( $this->emails['customerNewBooking'] ) ) {
			$this->emails['customerNewBooking'] = new Customer\CustomerNewBookingEmail();
		}

		return $this->emails['customerNewBooking'];
	}

	/**
	 * @return Customer\CustomerPendingBookingEmail
	 *
	 * @since 1.1.0
	 */
	public function customerPendingBooking() {
		if ( ! isset( $this->emails['customerPendingBooking'] ) ) {
			$this->emails['customerPendingBooking'] = new Customer\CustomerPendingBookingEmail();
		}

		return $this->emails['customerPendingBooking'];
	}

	/**
	 * @return Customer\CustomerApprovedBookingEmail
	 *
	 * @since 1.1.0
	 */
	public function customerApprovedBooking() {
		if ( ! isset( $this->emails['customerApprovedBooking'] ) ) {
			$this->emails['customerApprovedBooking'] = new Customer\CustomerApprovedBookingEmail();
		}

		return $this->emails['customerApprovedBooking'];
	}

	/**
	 * @since 1.5.0
	 *
	 * @return Customer\CustomerApprovedPaymentEmail
	 */
	public function customerApprovedPayment() {
		if ( ! isset( $this->emails[ __FUNCTION__ ] ) ) {
			$this->emails[ __FUNCTION__ ] = new Customer\CustomerApprovedPaymentEmail();
		}

		return $this->emails[ __FUNCTION__ ];
	}

	/**
	 * @return Customer\CustomerCancelledBookingEmail
	 *
	 * @since 1.1.0
	 */
	public function customerCancelledBooking() {
		if ( ! isset( $this->emails['customerCancelledBooking'] ) ) {
			$this->emails['customerCancelledBooking'] = new Customer\CustomerCancelledBookingEmail();
		}

		return $this->emails['customerCancelledBooking'];
	}

	/**
	 * @return Customer\CustomerAccountCreationEmail
	 *
	 * @since 1.18.0
	 */
	public function customerAccountCreation() {
		if ( ! isset( $this->emails['customerAccountCreation'] ) ) {
			$this->emails['customerAccountCreation'] = new Customer\CustomerAccountCreationEmail();
		}

		return $this->emails['customerAccountCreation'];
	}

	/**
	 * @since 1.13.0
	 *
	 * @param Notification|null $notification
	 * @param Reservation|null $reservation Optional.
	 */
	public function notificationEmail( $notification, $reservation = null ): NotificationEmail {
		if ( is_null( $notification ) ) {
			$notification = new Notification( 0 );
		}

		$email = new NotificationEmail( $notification );

		if ( ! is_null( $reservation ) ) {
			$email->setReservation( $reservation );
		}

		return $email;
	}

	/**
	 * @return AbstractEmail[]
	 *
	 * @since 1.1.0
	 */
	public function getAdminEmails() {
		$adminEmails = array();

		if ( mpapp()->settings()->isAutoConfirmationMode() ) {
			$adminEmails[] = $this->adminNewBooking();
		}

		if ( mpapp()->settings()->isAdminConfirmationMode() ) {
			$adminEmails[] = $this->adminPendingBooking();
		}

		if ( mpapp()->settings()->isPaymentConfirmationMode() ) {
			$adminEmails[] = $this->adminApprovedBooking();
		}

		if ( mpapp()->settings()->isUserCanBookingCancellation() ) {
			$adminEmails[] = $this->adminCancelledBooking();
		}

		$adminEmails = $this->mapByName( $adminEmails );

		/** @since 1.1.0 */
		$adminEmails = apply_filters( 'mpa_admin_emails', $adminEmails );

		return $adminEmails;
	}

	/**
	 * @return AbstractEmail[]
	 *
	 * @since 1.1.0
	 */
	public function getCustomerEmails() {
		if ( mpapp()->settings()->isAutoConfirmationMode() ) {
			$customerEmails[] = $this->customerNewBooking();
		}

		if ( mpapp()->settings()->isAdminConfirmationMode() ) {
			$customerEmails[] = $this->customerPendingBooking();
			$customerEmails[] = $this->customerApprovedBooking();
		}

		if ( mpapp()->settings()->isPaymentConfirmationMode() ) {
			$customerEmails[] = $this->customerApprovedPayment();
		}

		if ( mpapp()->settings()->isAllowCustomerAccountCreation() ) {
			$customerEmails[] = $this->customerAccountCreation();
		}

		$customerEmails[] = $this->customerCancelledBooking();

		$customerEmails = $this->mapByName( $customerEmails );

		/** @since 1.1.0 */
		$customerEmails = apply_filters( 'mpa_customer_emails', $customerEmails );

		return $customerEmails;
	}

	/**
	 * @return AbstractEmail[]
	 *
	 * @since 1.1.0
	 */
	public function getEmails() {
		return array_merge( $this->getAdminEmails(), $this->getCustomerEmails() );
	}

	/**
	 * @param string $name
	 * @return AbstractEmail|null
	 *
	 * @since 1.1.0
	 */
	public function getEmailByName( $name ) {
		// 'admin_pending_booking_email'
		if ( 'notification_email' == $name ) {
			return null;
		}

		$entryId = mpa_str_to_method_name( $name );      // 'adminPendingBookingEmail'
		$entryId = str_replace( 'Email', '', $entryId ); // 'adminPendingBooking'

		// Get email
		if ( method_exists( $this, $entryId ) ) {
			return $this->$entryId();
		} else {
			return null;
		}
	}

	/**
	 * @param AbstractEmail[] $entries [Entry ID => AbstractEmail]
	 * @return AbstractEmail[] [Entry name => AbstractEmail]
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
