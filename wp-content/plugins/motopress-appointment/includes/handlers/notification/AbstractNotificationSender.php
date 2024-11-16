<?php

namespace MotoPress\Appointment\Handlers;

use MotoPress\Appointment\Entities\Notification;
use MotoPress\Appointment\Entities\Reservation;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * To register custom notification sender create it after plugins loaded
 */
abstract class AbstractNotificationSender {

	public function __construct() {

		add_filter(
			'mpa_registered_notification_senders',
			function( array $registeredNotificationSenders ) {
				$registeredNotificationSenders[ static::getSenderId() ] = $this;
				return $registeredNotificationSenders;
			}
		);

		add_filter(
			'mpa_' . static::getSenderNotificationTypeId() . '_notification_settings',
			function( $notificationSettings ) {

				$notificationSettings += $this->getSettingsFields();

				return $notificationSettings;
			}
		);
	}

	/**
	 * @return string unique notification sender id with brand prefix
	 * for example: mpa_email_notification_sender
	 */
	abstract public static function getSenderId(): string;

	/**
	 * @return string sender name like Default Email Notification Sender
	 */
	abstract public static function getSenderName(): string;

	/**
	 * @return one of notification type ids from constants in \MotoPress\Appointment\Entities\Notification
	 */
	abstract public static function getSenderNotificationTypeId(): string;


	abstract protected function getSettingsFields(): array;


	abstract public function sendNotification( Notification $notification, Reservation $reservation, bool $isTestNotification = false );
}
