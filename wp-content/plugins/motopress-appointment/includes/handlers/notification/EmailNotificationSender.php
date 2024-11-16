<?php

namespace MotoPress\Appointment\Handlers;

use MotoPress\Appointment\Entities\Notification;
use MotoPress\Appointment\Entities\Reservation;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class EmailNotificationSender extends AbstractNotificationSender {


	public static function getSenderId(): string {
		return 'mpa_email_notification_sender';
	}

	public static function getSenderName(): string {
		return __( 'Default Email Notification Sender', 'motopress-appointment' );
	}

	public static function getSenderNotificationTypeId(): string {
		return Notification::TYPE_ID_EMAIL;
	}

	public function getSettingsFields(): array {

		return array(
			'email_notifications__group'     => array(
				'type'  => 'group',
				'label' => '',
			),
			'email_notification_from_email' => array(
				'type'         => 'email',
				'label'        => esc_html__( 'From Email', 'motopress-appointment' ),
				'placeholder'  => mpapp()->settings()->getFromEmail(),
				'size'         => 'regular',
				'translatable' => true,
				'description'  => __( 'It is used as the sender email for notifications.', 'motopress-appointment' ),
			),
			'email_notification_from_name'  => array(
				'type'         => 'text',
				'label'        => esc_html__( 'From Name', 'motopress-appointment' ),
				'placeholder'  => mpapp()->settings()->getFromName(),
				'size'         => 'regular',
				'translatable' => true,
				'description'  => __( 'It is used as the sender name for notifications.', 'motopress-appointment' ),
			),
		);
	}

	public function sendNotification( Notification $notification, Reservation $reservation, bool $isTestNotification = false ) {

		$email = mpapp()->emails()->notificationEmail( $notification, $reservation );

		$filterFromEmail = function( $fromEmail ) {
			return sanitize_email( mpapp()->settings()->getEmailNotificationFromEmail() );
		};

		$filterFromName = function( $fromName ) {
			return wp_specialchars_decode( esc_html( mpapp()->settings()->getEmailNotificationFromName() ), ENT_QUOTES );
		};

		// use 9999999 prority to overwrite default emails filters in \MotoPress\Appointment\Emails\Mailer
		add_filter( 'wp_mail_from', $filterFromEmail, 9999999 );
		add_filter( 'wp_mail_from_name', $filterFromName, 9999999 );

		mpapp()->emailsDispatcher()->triggerEmail(
			$email,
			$reservation->getBooking(),
			array(
				'is_test' => $isTestNotification,
			)
		);

		remove_filter( 'wp_mail_from', $filterFromEmail, 9999999 );
		remove_filter( 'wp_mail_from_name', $filterFromName, 9999999 );
	}
}
