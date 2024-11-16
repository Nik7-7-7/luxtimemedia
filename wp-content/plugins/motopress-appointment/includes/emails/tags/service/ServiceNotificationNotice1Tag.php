<?php

namespace MotoPress\Appointment\Emails\Tags\Service;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
class ServiceNotificationNotice1Tag extends AbstractServiceEntityTag {

	public function getName(): string {
		return 'notification_notice_1';
	}

	protected function description(): string {
		// Translators: %d: "Notice 1", "Notice 2" etc.
		return sprintf( esc_html__( 'Notification notice %d', 'motopress-appointment' ), 1 );
	}

	public function getTagContent(): string {
		return nl2br( $this->entity->getNotificationNotice1() );
	}
}
