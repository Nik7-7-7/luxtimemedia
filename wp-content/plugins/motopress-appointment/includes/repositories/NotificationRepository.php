<?php

namespace MotoPress\Appointment\Repositories;

use MotoPress\Appointment\Entities\Notification;
use MotoPress\Appointment\PostTypes\Statuses\AbstractPostStatuses;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.13.0
 */
class NotificationRepository extends AbstractRepository {

	/**
	 * @return Notification[]
	 */
	public function findAllActive(): array {
		return $this->findAll();
	}

	/**
	 * @return int[] ids of notifications
	 */
	public function findAllActiveNotificationIds(): array {
		return $this->findAll( array( 'fields' => 'ids' ) );
	}

	/**
	 * @param int $maxCount - if 0 then find all
	 * @return Notification[]
	 */
	public function findActiveByType( string $notificationType, int $maxCount = 0 ): array {

		$args = array(
			'meta_query'     => $this->metaQueryArgs( '_mpa_type', $notificationType, '=' ),
			'posts_per_page' => $maxCount,
		);

		return $this->findAll( $args );
	}

	/**
	 * @return array
	 */
	protected function entitySchema() {
		return array(
			'post'     => array( 'ID', 'post_status', 'post_title' ),
			'postmeta' => array(
				'_mpa_type'             => true,
				'_mpa_trigger_event_id' => true,
				'_mpa_trigger'          => true,
				'_mpa_trigger_time'     => true,
				'_mpa_recipients'       => true,
				'_mpa_custom_emails'    => true,
				'_mpa_email_subject'    => true,
				'_mpa_email_header'     => true,
				'_mpa_email_message'    => true,
				'_mpa_custom_phones'    => true,
				'_mpa_sms_message'      => true,
			),
		);
	}

	/**
	 * @param array $postData
	 * @return Notification
	 */
	protected function mapPostDataToEntity( $postData ) {
		$id = (int) $postData['ID'];

		$fields = array(
			'title'    => $postData['post_title'],
			'isActive' => AbstractPostStatuses::STATUS_PUBLISH === $postData['post_status'],
			'type'     => $postData['type'],
		);

		if ( ! empty( $postData['trigger_event_id'] ) ) {
			$fields['triggerEventId'] = $postData['trigger_event_id'];
		}

		if ( ! empty( $postData['trigger'] ) ) {
			$fields['trigger'] = $postData['trigger'];
		}

		if ( ! empty( $postData['trigger_time'] ) ) {
			$fields['triggerTime'] = $postData['trigger_time'];
		}

		if ( ! empty( $postData['recipients'] ) ) {
			$fields['recipients'] = $postData['recipients'];
		}

		if ( ! empty( $postData['email_subject'] ) ) {
			$fields['emailSubject'] = $postData['email_subject'];
		}

		if ( ! empty( $postData['email_header'] ) ) {
			$fields['emailHeader'] = $postData['email_header'];
		}

		if ( ! empty( $postData['email_message'] ) ) {
			$fields['emailMessage'] = $postData['email_message'];
		}

		if ( ! empty( $postData['custom_emails'] ) ) {
			$fields['customEmails'] = mpa_explode( $postData['custom_emails'] );
		}

		if ( ! empty( $postData['custom_phones'] ) ) {
			$fields['customPhones'] = mpa_explode( $postData['custom_phones'] );
		}

		if ( ! empty( $postData['sms_message'] ) ) {
			$fields['smsMessage'] = $postData['sms_message'];
		}

		return new Notification( $id, $fields );
	}
}
