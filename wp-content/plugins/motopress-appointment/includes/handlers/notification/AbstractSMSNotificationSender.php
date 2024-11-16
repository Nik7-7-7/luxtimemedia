<?php

namespace MotoPress\Appointment\Handlers;

use MotoPress\Appointment\Entities\Notification;
use MotoPress\Appointment\Entities\Reservation;
use MotoPress\Appointment\Helpers\EmailTagsHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


abstract class AbstractSMSNotificationSender extends AbstractNotificationSender {

	private $messageTags;

	public function __construct() {

		parent::__construct();

		$this->messageTags = apply_filters( 'mpa_notification_sms_tags', EmailTagsHelper::NotificationTags() );
	}


	public static function getSenderNotificationTypeId(): string {
		return Notification::TYPE_ID_SMS;
	}

	public function sendNotification( Notification $notification, Reservation $reservation, bool $isTestNotification = false ) {

		$recipientsPhones = array();

		if ( $isTestNotification ) {

			$adminPhone = mpapp()->settings()->getAdminPhone();

			if ( empty( $adminPhone ) ) {

				throw new \Exception( __( 'Could not send test SMS notification because there is no Admin phone in the notification settings.', 'motopress-appointment' ) );
			}

			$recipientsPhones[] = $adminPhone;
		} else {
			$recipientsPhones = $notification->getRecipientsContacts( $reservation );
		}

		$booking = $reservation->getBooking();

		if ( empty( $recipientsPhones ) ) {

			$errorMessage = sprintf(
				// translators: %1$d is a notification id and %2$d is a reservation id
				__( 'Could not send an SMS notification [id = %1$d] because the recipient\'s list is empty for reservation [id = %2$d].', 'motopress-appointment' ),
				$notification->getId(),
				$reservation->getId()
			);

			$this->addBookingLog( $booking, $errorMessage );

			throw new \Exception( $errorMessage );
		}

		$smsMessage = $notification->getSMSMessage();

		// init message tags
		$service  = mpapp()->repositories()->service()->findById( $reservation->getServiceId() );
		$location = mpapp()->repositories()->location()->findById( $reservation->getLocationId() );
		$employee = mpapp()->repositories()->employee()->findById( $reservation->getEmployeeId() );

		$this->messageTags->setEntity( $reservation );
		$this->messageTags->setEntity( $booking );

		if ( $service ) {
			$this->messageTags->setEntity( $service );
		}

		if ( $location ) {
			$this->messageTags->setEntity( $location );
		}

		if ( $employee ) {
			$this->messageTags->setEntity( $employee );
		}

		/**
		 * mpa_sms_pre_replace_tag_content
		 *
		 * @since 2.1.1
		 */
		$smsMessage = apply_filters('mpa_sms_pre_replace_tag_content', $smsMessage);
		$smsMessage = $this->messageTags->replaceTags( $smsMessage );

		$collectedErrorMessages      = '';
		$isSomeSMSWasSentSuccessfuly = false;

		foreach ( $recipientsPhones as $recipientKey => $recipientsPhone ) {

			try {

				$this->sendSMS( $recipientsPhone, $smsMessage );
				$isSomeSMSWasSentSuccessfuly = true;

				if ( ! $isTestNotification ) {

					$this->addBookingLog(
						$booking,
						sprintf(
							// translators: %1$s is the title of the notification, %2$s is a receiver key such as admin, customer, etc.
							__( 'SMS notification "%1$s" to %2$s was sent successfully.', 'motopress-appointment' ),
							$notification->getTitle(),
							$recipientKey
						)
					);
				}
			} catch ( \Throwable $e ) {

				$errorMessage = sprintf(
					// translators: %1$s is the title of the notification, %2$s is a receiver key such as admin, customer, etc.
					__( 'SMS notification "%1$s" to %2$s failed.', 'motopress-appointment' ),
					$notification->getTitle(),
					$recipientKey
				) . ' ' . $e->getMessage();

				if ( ! $isTestNotification ) {

					$this->addBookingLog( $booking, $errorMessage );
				}

				$collectedErrorMessages .= $errorMessage;
			}
		}

		if ( ! $isSomeSMSWasSentSuccessfuly ) {

			throw new \Exception( $collectedErrorMessages );
		}
	}

	private function addBookingLog( $booking, $logMessage ) {

		$bookinfLogs = $booking->getLogs();

		reset( $bookinfLogs );

		$checkingLastLogsCount = 10;

		foreach ( $bookinfLogs as $bookinfLog ) {

			$checkingLastLogsCount--;

			if ( $logMessage == $bookinfLog->comment_content ) {

				return;
			}

			if ( 0 >= $checkingLastLogsCount ) {
				break;
			}
		}

		$booking->addLog( $logMessage );
	}

	/**
	 * @throws \Exception if sms was not send
	 */
	abstract protected function sendSMS( string $phoneNumber, string $message );
}
