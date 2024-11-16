<?php

namespace MotoPress\Appointment\Emails;

use MotoPress\Appointment\Entities;
use MotoPress\Appointment\PostTypes\Statuses\BookingStatuses;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.1.0
 */
class EmailsDispatcher {

	/**
	 * By default loads on "plugins_loaded" (15).
	 *
	 * @see \MotoPress\Appointment\Plugin::load()
	 *
	 * @since 1.1.0
	 */
	public function load() {
		$this->addActions();
	}

	/**
	 * @since 1.1.0
	 */
	protected function addActions() {
		// Filter settings
		add_filter( 'mpa_email_section_settings', array( $this, 'filterSettings' ), 10, 2 );

		// Listen booking actions
		add_action( 'mpa_booking_placed_by_user', array( $this, 'triggerNewUserBooking' ), 10, 1 );
		add_action( 'mpa_booking_placed_by_admin', array( $this, 'triggerNewAdminBooking' ), 10, 1 );

		add_action( 'mpa_booking_status_changed', array( $this, 'triggerBookingUpdate' ), 10, 3 );

		add_action( 'mpa_new_customer_account', array( $this, 'triggerNewCustomerAccount' ), 10, 2 );
	}

	/**
	 * @param array $fields
	 * @param string $sectionName
	 * @return array
	 *
	 * @access protected
	 *
	 * @since 1.1.0
	 */
	public function filterSettings( $fields, $sectionName ) {
		$email        = mpapp()->emails()->getEmailByName( $sectionName );
		$templatePart = mpapp()->templates()->getEmailTemplatePartByName( $sectionName );

		if ( ! is_null( $email ) ) {
			$fields += $email->getSettingsFields();
		} elseif ( ! is_null( $templatePart ) ) {
			$fields += $templatePart->getFields();
		}

		return $fields;
	}

	/**
	 * @param Entities\Booking $booking
	 *
	 * @access protected
	 *
	 * @see BookingStatuses::notifyTransition()
	 *
	 * @since 1.1.0 (As method triggerNewBooking())
	 * @since 1.10.1 renamed to triggerNewUserBooking().
	 */
	public function triggerNewUserBooking( $booking ) {
		if ( $this->isSuspended() ) {
			return;
		}

		switch ( mpapp()->settings()->getConfirmationMode() ) {
			case 'auto':
				$this->triggerEmail( mpapp()->emails()->adminNewBooking(), $booking );
				$this->triggerEmail( mpapp()->emails()->customerNewBooking(), $booking );
				break;

			case 'manual':
				$this->triggerEmail( mpapp()->emails()->adminPendingBooking(), $booking );
				$this->triggerEmail( mpapp()->emails()->customerPendingBooking(), $booking );
				break;

			case 'payment':
				// No emails for new bookings
				break;
		}
	}

	/**
	 * @param Entities\Booking $booking
	 *
	 * @see EditBookingPage::afterSave()
	 *
	 * @since 1.10.1
	 */
	public function triggerNewAdminBooking( Entities\Booking $booking ) {
		if ( $this->isSuspended() ) {
			return;
		}

		$isConfirmed = array_key_exists( $booking->getStatus(), mpapp()->postTypes()->booking()->statuses()->getBookedStatuses() );

		if ( $isConfirmed ) {
			// Confirmed by Admin
			$this->triggerEmail( mpapp()->emails()->customerApprovedBooking(), $booking );
		}
	}

	/**
	 * @param Entities\Booking $booking
	 * @param string $newStatus
	 * @param string $oldStatus
	 *
	 * @access protected
	 *
	 * @see BookingStatuses::notifyTransition()
	 *
	 * @since 1.1.0
	 */
	public function triggerBookingUpdate( $booking, $newStatus, $oldStatus ) {
		if ( $this->isSuspended() ) {
			return;
		}

		switch ( $newStatus ) {
			case BookingStatuses::STATUS_CONFIRMED:
				if ( $booking->isAdminBooking() ) {
					// Confirmed by Admin
					$this->triggerEmail( mpapp()->emails()->customerApprovedBooking(), $booking );

				} elseif ( mpapp()->settings()->getConfirmationMode() === 'manual' ) {
					// Confirmation by Admin
					$this->triggerEmail( mpapp()->emails()->customerApprovedBooking(), $booking );

				} elseif ( mpapp()->settings()->getConfirmationMode() === 'payment' ) {
					// Confirmation via Payment
					$this->triggerEmail( mpapp()->emails()->adminApprovedBooking(), $booking );
					$this->triggerEmail( mpapp()->emails()->customerApprovedPayment(), $booking );

				}
				break;

			case BookingStatuses::STATUS_CANCELLED:
				$this->triggerEmail( mpapp()->emails()->customerCancelledBooking(), $booking );
				break;
		}
	}

	/**
	 * @param AbstractBookingEmail $email
	 * @param Entities\Booking $booking
	 * @param array $args Optional.
	 *     @param bool $args['is_test'] False by default.
	 * @return bool
	 *
	 * @since 1.1.0
	 */
	public function triggerEmail( $email, $booking, $args = array() ) {
		// Fill optional args
		$args += array(
			'is_test' => false,
		);

		$isTest = $args['is_test'];

		// Check if the email is disabled
		if ( $email->isDisabled() && ! $isTest ) {
			return false;
		}

		/**
		 * Init email with booking entity
		 *
		 * @todo: MPI-10998 - In the future, we need to move the installation of entities to the email initialization.
		 * This is necessary to reduce the responsibility of the email dispatcher.
		 */
		if ( method_exists( $email, 'setBooking' ) ) {
			$email->setBooking( $booking );
		}

		// Do we have any receiver?
		if ( ! $this->checkRecipients( $email, $isTest ) ) {
			if ( ! $isTest ) {
				$this->log( $booking, $this->recipientsError( $email ) );
			}

			return false;
		}

		// Send email
		$isSended = $this->sendEmail( $email, $booking, $args );

		if ( ! $isTest ) {
			$this->log( $booking, $this->sendedMessage( $email, $isSended ) );
		}

		return $isSended;
	}

	/***
	 * @since 1.18.0
	 *
	 * @see Using filter 'mpa_email_tag_customer_account_password'
	 * for setting value of email tag.
	 *
	 * @todo: Need add implementation of actions 'mpa_before_send_email', 'mpa_after_send_email',
	 * for identity works with triggerEmail();
	 */
	public function triggerNewCustomerAccount( $customer, $userdata ) {

		add_filter( 'mpa_email_tag_customer_account_password', function ( $defaultTagContent ) use ( $userdata ) {
			if ( ! isset( $userdata['user_pass'] ) ) {
				return $defaultTagContent;
			}

			return $userdata['user_pass'];
		}, 10, 1 );

		$email = mpapp()->emails()->customerAccountCreation();
		// Init email with customer entity
		$email->setCustomer( $customer );

		// Send email
		$mailer = mpa_mailer();
		$sendTo = $email->getRecipients();

		$mailer->send( $sendTo, $email->getSubject(), $email->render() );
	}

	/**
	 * @param AbstractEmail $email
	 * @param bool $isTest Optional. False by default.
	 * @return bool
	 *
	 * @since 1.1.0
	 */
	protected function checkRecipients( $email, $isTest = false ) {
		$recipients = $this->getRecipients( $email, $isTest );

		return ! empty( $recipients );
	}

	/**
	 * @param AbstractEmail $email
	 * @param bool $isTest Optional. False by default.
	 * @return string
	 *
	 * @since 1.1.0
	 */
	protected function getRecipients( $email, $isTest = false ) {
		if ( $isTest ) {
			return mpapp()->settings()->getAdminEmail();
		} else {
			return $email->getRecipients();
		}
	}

	/**
	 * @param AbstractEmail $email
	 * @param Entities\Booking $booking
	 * @param array $args Optional.
	 *     @param Entities\Payment $args['payment'] Null by default.
	 *     @param bool $args['is_test'] False by default.
	 * @return bool
	 *
	 * @since 1.1.0
	 */
	public function sendEmail( $email, $booking, $args = array() ) {
		// Fill optional args
		$args += array(
			'payment' => null,
			'is_test' => false,
		);

		// Change the language
		$emailType = $this->typeOfEmail( $email );

		if ( ! $args['is_test'] ) { // Use default language for tests
			/**
			 * mpa_set_language_before_send_admin_email
			 * mpa_set_language_before_send_customer_email
			 * mpa_set_language_before_send_notification_email
			 * mpa_set_language_before_send_other_email
			 *
			 * @since 1.1.0
			 */
			do_action( "mpa_set_language_before_send_{$emailType}_email", $email, $booking, $args );
		}

		// Send email
		$mailer = mpa_mailer();
		$sendTo = $this->getRecipients( $email, $args['is_test'] );

		/** @since 1.1.0 */
		do_action( 'mpa_before_send_email', $email, $booking, $args );

		$isSended = $mailer->send( $sendTo, $email->getSubject(), $email->render() );

		/** @since 1.1.0 */
		do_action( 'mpa_after_send_email', $email, $booking, $args );

		return $isSended;
	}

	/**
	 * @param AbstractEmail $email
	 * @return string admin|customer|notification|other
	 *
	 * @since 1.1.0
	 */
	protected function typeOfEmail( $email ) {
		if ( $email instanceof Admin\AbstractAdminEmail ) {
			return 'admin';
		} elseif ( $email instanceof Customer\AbstractCustomerEmail ) {
			return 'customer';
		} elseif ( $email instanceof Notification\NotificationEmail ) {
			return 'notification';
		} else {
			return 'other';
		}
	}

	/**
	 * @param Entities\Booking $booking
	 * @param string $message
	 *
	 * @since 1.1.0
	 */
	protected function log( $booking, $message ) {
		if ( empty( $message ) ) {
			return;
		}

		$booking->addLog( $message, $this->getLogAuthor() );
	}

	/**
	 * @return int|null
	 *
	 * @since 1.1.0
	 */
	protected function getLogAuthor() {
		// Null means "define automatically". Some addons may change it
		return null;
	}

	/**
	 * @param AbstractEmail $email
	 * @return string
	 *
	 * @since 1.1.0
	 */
	protected function recipientsError( $email ) {
		switch ( $this->typeOfEmail( $email ) ) {
			case 'admin':
			case 'customer':
			case 'other':
			default:
				// Translators: %s: Email name, like "New Booking Email".
				$message = esc_html__( '"%s" email will not be sent: there are no recipients.', 'motopress-appointment' );
				break;

			case 'notification':
				// Translators: %s: Notification post title.
				$message = esc_html__( 'Notification "%s" will not be sent: there is no email address to send the notification to.', 'motopress-appointment' );
				break;
		}

		return sprintf( $message, $email->getLabel() );
	}

	/**
	 * @param AbstractEmail $email
	 * @param bool $isSended
	 * @return string
	 *
	 * @since 1.1.0
	 */
	protected function sendedMessage( $email, $isSended ) {
		if ( $isSended ) {
			switch ( $this->typeOfEmail( $email ) ) {
				case 'admin':
					// Translators: %s: Email name, like "New Booking Email".
					$message = wp_kses_post( __( '"%s" email was sent to admin.', 'motopress-appointment' ) );
					break;

				case 'customer':
					// Translators: %s: Email name, like "New Booking Email".
					$message = wp_kses_post( __( '"%s" email was sent to customer.', 'motopress-appointment' ) );
					break;

				case 'notification':
					// Translators: %s: Notification post title.
					$message = wp_kses_post( __( 'Notification "%s" was sent.', 'motopress-appointment' ) );
					break;

				case 'other':
				default:
					// Translators: %s: Email name, like "New Booking Email".
					$message = wp_kses_post( __( '"%s" email was sent.', 'motopress-appointment' ) );
					break;
			}
		} else {
			switch ( $this->typeOfEmail( $email ) ) {
				case 'admin':
					// Translators: %s: Email name, like "New Booking Email".
					$message = wp_kses_post( __( '"%s" mail sending to admin is failed.', 'motopress-appointment' ) );
					break;

				case 'customer':
					// Translators: %s: Email name, like "New Booking Email".
					$message = wp_kses_post( __( '"%s" mail sending to customer is failed.', 'motopress-appointment' ) );
					break;

				case 'notification':
					// Translators: %s: Notification post title.
					$message = wp_kses_post( __( 'Notification "%s" sending failed.', 'motopress-appointment' ) );
					break;

				case 'other':
				default:
					// Translators: %s: Email name, like "New Booking Email".
					$message = wp_kses_post( __( '"%s" mail sending is failed.', 'motopress-appointment' ) );
					break;
			}
		}

		return sprintf( $message, $email->getLabel() );
	}

	public function isSuspended(): bool {
		/**
		 * @since 1.10.1
		 *
		 * @param bool $preventEmails False by default.
		 */
		return apply_filters( 'mpa_prevent_emails', false );
	}
}
