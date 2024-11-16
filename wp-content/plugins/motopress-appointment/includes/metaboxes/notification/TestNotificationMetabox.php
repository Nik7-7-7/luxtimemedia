<?php

declare(strict_types=1);

namespace MotoPress\Appointment\Metaboxes\Notification;

use MotoPress\Appointment\Metaboxes\CustomMetabox;
use WP_Post;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.13.0
 */
class TestNotificationMetabox extends CustomMetabox {

	protected function theName(): string {
		return 'test_notification_metabox';
	}

	public function getLabel(): string {
		return esc_html__( 'Test Notification Now', 'motopress-appointment' );
	}

	protected function renderMetabox(): string {

		$output  = '<p class="mpa-test-notification-description">';
		$output .= esc_html( __( 'Save this notification and send it to the administrator.', 'motopress-appointment' ) );
		$output .= '</p>';

		$output .= '<p class="mpa-test-notification-description">';
		$output .= wp_kses_post(
			sprintf(
				// translators: %1$s, %2$s, %2$s - urls for admin pages
				__( 'To be able to send a test notification, make sure you have at least one <a href="%1$s">confirmed booking</a> and an administrator contact set in the <a href="%2$s">Notification Settings</a>! If you want to test sending service notices in the Notification Message, make sure you have added Service Notice 1/Notice 2 to your <a href="%3$s">Services</a>.', 'motopress-appointment' ),
				esc_url( get_admin_url( null, 'edit.php?post_type=' . mpapp()->postTypes()->booking()->getPostType() ) ),
				esc_url( get_admin_url( null, 'admin.php?page=mpa_settings&tab=notification' ) ),
				esc_url( get_admin_url( null, 'edit.php?post_type=' . mpapp()->postTypes()->service()->getPostType() ) )
			)
		);
		$output .= '</p>';

		$output .= '<p class="mpa-test-notification-submit">';
		$output .= '<input type="submit" name="send_notification" class="button button-secondary button-large" value="' . esc_attr__( 'Send Test Notification', 'motopress-appointment' ) . '">';
		$output .= '</p>';

		return $output;
	}

	protected function saveValues( array $values, int $postId, WP_Post $post ) {

		if ( isset( $_POST['send_notification'] ) ) {

			$notification = mpapp()->repositories()->notification()->findById( $postId );
			$reservation  = mpapp()->repositories()->reservation()->findRandomConfirmed();

			if ( empty( $notification ) ) {

				throw new \Exception(
					sprintf(
						// translators: %s is integer id of the notification
						__( 'Could not send a notification [id = %s] because it was not found.', 'motopress-appointment' ),
						$postId
					)
				);
			}

			if ( empty( $reservation ) ) {
				throw new \Exception( __( 'Could not send a test notification because there are no confirmed bookings.', 'motopress-appointment' ) );
			}

			mpapp()->getNotificationHandler()->sendNotification( $notification, $reservation, true );
		}
	}
}
