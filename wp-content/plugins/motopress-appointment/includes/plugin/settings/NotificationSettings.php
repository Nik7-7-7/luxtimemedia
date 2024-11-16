<?php

namespace MotoPress\Appointment\Plugin\Settings;

use MotoPress\Appointment\Entities\Notification;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


trait NotificationSettings {


	public static function getDefaultNotificationType(): string {
		return Notification::TYPE_ID_EMAIL;
	}

	/**
	 * @return array [ notification_type_id => notification_type_label ]
	 */
	public function getAllNotificationTypes(): array {

		return array(
			Notification::TYPE_ID_EMAIL => _x( 'Email', 'Noun', 'motopress-appointment' ),
			Notification::TYPE_ID_SMS   => _x( 'SMS', 'Noun', 'motopress-appointment' ),
		);
	}

	/**
	 * @return array [ notification_type_id => notification_type_label ]
	 */
	public function getActiveNotificationTypes(): array {

		$allNotificationTypes = $this->getAllNotificationTypes();

		$activeNotificationType = array(
			Notification::TYPE_ID_EMAIL => $allNotificationTypes[ Notification::TYPE_ID_EMAIL ],
		);

		if ( mpapp()->getNotificationHandler()->isSMSNotificationsEnabled() ) {

			$activeNotificationType[ Notification::TYPE_ID_SMS ] = $allNotificationTypes[ Notification::TYPE_ID_SMS ];
		}

		return $activeNotificationType;
	}

	public function getEmailNotificationFromEmail(): string {

		$fromEmail = get_option( 'mpa_email_notification_from_email', '' );

		return ! empty( $fromEmail ) ? $fromEmail : mpapp()->settings()->getFromEmail();
	}

	public function getEmailNotificationFromName(): string {

		$fromName = get_option( 'mpa_email_notification_from_name' );

		return ! empty( $fromName ) ? $fromName : mpapp()->settings()->getFromName();
	}

	public function getEmailNotificationDefaultSubject(): string {
		return __( 'Notification from {site_title}', 'motopress-appointment' );
	}

	public function getEmailNotificationDefaultHeader(): string {
		return __( 'Notification for your booking #{booking_id}', 'motopress-appointment' );
	}

	public function getEmailNotificationDefaultMessage(): string {
		return mpa_render_template( 'emails/notification/default-notification.php' );
	}

	public function getSMSNotificationSenderId(): string {
		return get_option( 'mpa_sms_notification_sender_id', '' );
	}

	public function getAdminPhone(): string {
		return get_option( 'mpa_admin_phone', '' );
	}
}
