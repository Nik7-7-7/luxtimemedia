<?php

declare(strict_types=1);

namespace MotoPress\Appointment\Entities;

use MotoPress\Appointment\PostTypes\Statuses\NotificationStatuses;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.13.0
 */
class Notification extends AbstractEntity {

	const TYPE_ID_EMAIL = 'email';
	const TYPE_ID_SMS   = 'sms';

	const TRIGGER_EVENT_ID_BOOKING_PLACED           = 'booking_placed';
	const TRIGGER_EVENT_ID_BOOKING_CANCELED         = 'booking_canceled';
	const TRIGGER_EVENT_ID_PAYMENT_COMPLETED        = 'payment_completed';
	const TRIGGER_EVENT_ID_BEFORE_AFTER_APPOINTMENT = 'before_after_appointment';

	/**
	 * @var string
	 */
	protected $title = '';

	/**
	 * @var bool
	 */
	protected $isActive = true;

	/**
	 * @var string
	 */
	protected $type = self::TYPE_ID_EMAIL;

	/**
	 * @var string - one of constants in this class
	 */
	protected $triggerEventId = self::TRIGGER_EVENT_ID_BEFORE_AFTER_APPOINTMENT;

	/**
	 * @var array
	 */
	protected $trigger = array(
		'period'   => 1,
		'unit'     => \MotoPress\Appointment\Fields\Complex\TriggerPeriodField::DEFAULT_UNIT,
		'operator' => \MotoPress\Appointment\Fields\Complex\TriggerPeriodField::DEFAULT_OPERATOR,
	);

	/**
	 * @var string Time in "H:i" format.
	 */
	protected $triggerTime = '00:00';

	/**
	 * @var string[] contains "admin", "employee", "customer", "custom"
	 */
	protected $recipients = array();

	/**
	 * @var string[] comma separated emails
	 */
	protected $customEmails = array();

	/**
	 * @var string[] comma separated phones
	 */
	protected $customPhones = array();

	/**
	 * @var string
	 */
	protected $emailSubject = '';

	/**
	 * @var string
	 */
	protected $emailHeader = '';

	/**
	 * @var string
	 */
	protected $emailMessage = '';

	/**
	 * @var string
	 */
	protected $smsMessage = '';

	public function getTitle(): string {
		return $this->title;
	}

	public function isActive(): bool {
		return $this->isActive;
	}

	public function getType(): string {
		return $this->type;
	}

	public function getStatus(): string {
		return $this->isActive() ? NotificationStatuses::STATUS_PUBLISH : NotificationStatuses::STATUS_DRAFT;
	}

	public function getStatusLabel(): string {
		return mpapp()->postTypes()->notification()->statuses()->getLabel( $this->getStatus() );
	}

	public function getManualStatusLabel(): string {
		return mpapp()->postTypes()->notification()->statuses()->getManualStatusLabel( $this->getStatus() );
	}

	public function getTriggerEventId(): string {
		return $this->triggerEventId;
	}
	/**
	 * @return array [period, unit, operator]
	 */
	public function getTrigger(): array {
		return $this->trigger;
	}

	public function getTriggerPeriod(): int {
		return $this->trigger['period'];
	}

	public function getTriggerUnit(): string {
		return $this->trigger['unit'];
	}

	public function getTriggerOperator(): string {
		return $this->trigger['operator'];
	}

	/**
	 * @return string Time in "H:i" format.
	 */
	public function getTriggerTime(): string {
		return $this->triggerTime;
	}

	public function hasRecipients(): bool {
		return ! empty( $this->recipients );
	}

	/**
	 * @return string[] "admin", "employee", "customer", "custom".
	 */
	public function getRecipients(): array {
		return $this->recipients;
	}

	/**
	 * @return string[]
	 */
	public function getCustomEmails(): array {
		return $this->customEmails;
	}

	public function getEmailSubject(): string {
		return $this->emailSubject;
	}

	public function getEmailHeader(): string {
		return $this->emailHeader;
	}

	public function getEmailMessage(): string {
		return $this->emailMessage;
	}

	/**
	 * @return string[]
	 */
	public function getCustomPhones(): array {
		return $this->customPhones;
	}

	public function getSMSMessage(): string {
		return $this->smsMessage;
	}

	/**
	 * @return array of recipients contacts: emails or phones.
	 * @param Reservation $reservation
	 */
	public function getRecipientsContacts( Reservation $reservation ): array {

		$receivers          = $this->getRecipients();
		$recipientsContacts = array();

		if ( static::TYPE_ID_EMAIL === $this->getType() ) {

			if ( in_array( 'admin', $receivers, true ) ) {
				$recipientsContacts['admin'] = mpapp()->settings()->getAdminEmail();
			}

			if ( in_array( 'employee', $receivers, true ) && null !== $reservation ) {

				$employee = mpapp()->repositories()->employee()->findById( $reservation->getEmployeeId() );

				if ( ! empty( $employee ) ) {

					$employeeUser = $employee->getWPUser();

					if ( null !== $employeeUser ) {
						$recipientsContacts['employee'] = $employeeUser->user_email;
					}
				}
			}

			if ( in_array( 'customer', $receivers, true ) ) {

				$customerEmail = $reservation->getBooking()->getCustomerEmail();

				if ( ! empty( $customerEmail ) ) {
					$recipientsContacts['customer'] = $customerEmail;
				}
			}

			if ( in_array( 'custom', $receivers, true ) ) {

				$i = 1;
				foreach ( $this->getCustomEmails() as $customEmail ) {

					$recipientsContacts[ 'custom_' . $i ] = $customEmail;
					$i++;
				}
			}
		} elseif ( static::TYPE_ID_SMS === $this->getType() ) {

			if ( in_array( 'admin', $receivers, true ) && ! empty( mpapp()->settings()->getAdminPhone() ) ) {
				$recipientsContacts['admin'] = mpapp()->settings()->getAdminPhone();
			}

			if ( in_array( 'employee', $receivers, true ) && null !== $reservation ) {

				$employee = mpapp()->repositories()->employee()->findById( $reservation->getEmployeeId() );

				if ( ! empty( $employee ) && ! empty( $employee->getPhoneNumber() ) ) {
					$recipientsContacts['employee'] = $employee->getPhoneNumber();
				}
			}

			if ( in_array( 'customer', $receivers, true ) ) {

				$customerPhone = $reservation->getBooking()->getCustomerPhone();

				if ( $customerPhone ) {
					$recipientsContacts['customer'] = $customerPhone;
				}
			}

			if ( in_array( 'custom', $receivers, true ) ) {

				$i = 1;
				foreach ( $this->getCustomPhones() as $customPhone ) {

					$recipientsContacts[ 'custom_' . $i ] = $customPhone;
					$i++;
				}
			}
		}

		return $recipientsContacts;
	}
}
