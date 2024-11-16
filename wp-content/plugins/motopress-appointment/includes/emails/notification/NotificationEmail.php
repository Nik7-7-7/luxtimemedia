<?php

declare(strict_types=1);

namespace MotoPress\Appointment\Emails\Notification;

use MotoPress\Appointment\Emails\AbstractEmail;
use MotoPress\Appointment\Emails\Tags\InterfaceTags;
use MotoPress\Appointment\Entities\Notification;
use MotoPress\Appointment\Entities\Booking;
use MotoPress\Appointment\Entities\Reservation;
use MotoPress\Appointment\Helpers\EmailTagsHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.13.0
 */
class NotificationEmail extends AbstractEmail {

	/**
	 * @var Notification
	 */
	protected $notification = null;

	/**
	 * @var Reservation|null
	 */
	protected $reservation = null;

	public function __construct( Notification $notification ) {
		parent::__construct();

		$this->notification = $notification;
	}

	/**
	 * @param Reservation|null $reservation
	 */
	public function setReservation( $reservation ) {
		if ( ! ( $reservation instanceof Reservation ) ) {
			return;
		}

		$booking = $reservation->getBooking();

		if ( ! ( $booking instanceof Booking ) ) {
			return;
		}

		$this->reservation = $reservation;

		$service  = mpapp()->repositories()->service()->findById( $reservation->getServiceId() );
		$location = mpapp()->repositories()->location()->findById( $reservation->getLocationId() );
		$employee = mpapp()->repositories()->employee()->findById( $reservation->getEmployeeId() );

		$this->tags->setEntity( $reservation );
		$this->tags->setEntity( $booking );
		if ( $service ) {
			$this->tags->setEntity( $service );
		}
		if ( $location ) {
			$this->tags->setEntity( $location );
		}
		if ( $employee ) {
			$this->tags->setEntity( $employee );
		}
	}

	/**
	 * @return string
	 */
	public function getName() {
		return 'notification_email';
	}

	/**
	 * @return string
	 */
	public function getLabel() {
		return $this->notification->getTitle();
	}

	/**
	 * @return bool
	 */
	public function isDisabled() {
		return ! $this->notification->isActive() || parent::isDisabled();
	}

	/**
	 * @param string $content
	 * @return string
	 */
	protected function replaceTags( $content ) {
		/**
		 * mpa_notification_pre_replace_tag_content
		 *
		 * @since 2.1.1
		 */
		$content = apply_filters('mpa_notification_pre_replace_tag_content', $content);
		return $this->tags->replaceTags( $content );
	}

	/**
	 * @return string Emails, separated by comma.
	 */
	public function getDefaultRecipients() {
		$recipients = $this->notification->getRecipientsContacts( $this->reservation );

		return implode( ', ', $recipients );
	}

	/**
	 * @return string
	 */
	protected function getDefaultSubject() {
		return $this->notification->getEmailSubject();
	}

	/**
	 * @return string
	 */
	protected function getDefaultHeader() {
		return $this->notification->getEmailHeader();
	}

	/**
	 * @return string
	 */
	protected function getDefaultMessage() {
		return $this->notification->getEmailMessage();
	}

	/**
	 * @return string
	 */
	protected function getMessageTemplate() {
		return 'emails/notification/default-notification.php';
	}

	protected function initTags(): InterfaceTags {
		return EmailTagsHelper::NotificationTags();
	}
}
