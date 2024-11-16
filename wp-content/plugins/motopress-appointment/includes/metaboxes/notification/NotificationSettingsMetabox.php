<?php

declare(strict_types=1);

namespace MotoPress\Appointment\Metaboxes\Notification;

use MotoPress\Appointment\Metaboxes\FieldsMetabox;
use MotoPress\Appointment\Entities\Notification;
use MotoPress\Appointment\Fields\Complex\TriggerPeriodField;
use MotoPress\Appointment\Fields\AbstractField;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.13.0
 */
class NotificationSettingsMetabox extends FieldsMetabox {

	protected function theName(): string {
		return 'notification_settings_metabox';
	}

	public function getLabel(): string {
		return esc_html__( 'Settings', 'motopress-appointment' );
	}

	/**
	 * @return array
	 */
	protected function theFields() {

		$notificationTypes = mpapp()->settings()->getActiveNotificationTypes();

		$fields['type'] = array(
			'type'    => 'select',
			'label'   => esc_html__( 'Notification Type', 'motopress-appointment' ),
			'options' => $notificationTypes,
			'default' => mpapp()->settings()->getDefaultNotificationType(),
		);

		// Other fields
		$fields += array(
			'trigger_event_id' => array(
				'type'    => 'radio',
				'label'   => __( 'Trigger Event', 'motopress-appointment' ),
				'options' => array(
					Notification::TRIGGER_EVENT_ID_BOOKING_PLACED           => __( 'Booking placed', 'motopress-appointment' ),
					Notification::TRIGGER_EVENT_ID_BOOKING_CANCELED         => __( 'Booking canceled', 'motopress-appointment' ),
					Notification::TRIGGER_EVENT_ID_PAYMENT_COMPLETED        => __( 'Payment completed', 'motopress-appointment' ),
					Notification::TRIGGER_EVENT_ID_BEFORE_AFTER_APPOINTMENT => __( 'Before/after appointment', 'motopress-appointment' ),
				),
				'default' => Notification::TRIGGER_EVENT_ID_BEFORE_AFTER_APPOINTMENT,
			),
			'trigger'          => array(
				'type'     => 'trigger-period',
				'label'    => __( 'Trigger Details', 'motopress-appointment' ),
				'activeIf' => array(
					'fieldName'  => mpa_prefix( 'trigger_event_id', 'metabox' ),
					'fieldValue' => array( Notification::TRIGGER_EVENT_ID_BEFORE_AFTER_APPOINTMENT ),
					'else'       => 'hide',
				),
			),
			'trigger_time'     => array(
				'type'        => 'time-slot',
				'label'       => esc_html__( 'Sending Time', 'motopress-appointment' ),
				'description' => esc_html__( 'Time at which the notification must be sent (only for daily ones).', 'motopress-appointment' ),
				'activeIf'    => array(
					'fieldName'  => '_mpa_trigger[unit]', // inner field of trigger-period
					'fieldValue' => array( TriggerPeriodField::UNIT_DAY ),
					'else'       => 'hide',
				),
			),
			'recipients'       => array(
				'type'    => 'checklist',
				'label'   => esc_html__( 'Recipients', 'motopress-appointment' ),
				'options' => array(
					'admin'    => esc_html__( 'Admin', 'motopress-appointment' ),
					'employee' => esc_html__( 'Employee', 'motopress-appointment' ),
					// @NOLITE-CODE-START
					'customer' => esc_html__( 'Customer', 'motopress-appointment' ),
					// @NOLITE-CODE-END
					'custom'   => esc_html__( 'Custom Contacts', 'motopress-appointment' ),
				),
				
			),
			'custom_emails'    => array(
				'type'        => 'text',
				'label'       => esc_html__( 'Custom Email Addresses', 'motopress-appointment' ),
				'description' => esc_html__( 'You can use multiple comma-separated email addresses.', 'motopress-appointment' ),
				'size'        => 'large',
				'activeIf'    => array(
					'fieldName'  => mpa_prefix( 'type', 'metabox' ),
					'fieldValue' => array( Notification::TYPE_ID_EMAIL ),
					'else'       => 'hide',
				),
			),
			'custom_phones'    => array(
				'type'                   => 'multiple-phone',
				'label'                  => esc_html__( 'Custom Phone Numbers', 'motopress-appointment' ),
				'description'            => esc_html__( 'You can use multiple comma-separated phone numbers in international format.', 'motopress-appointment' ),
				'size'                   => 'large',
				'activeIf'               => array(
					'fieldName'  => mpa_prefix( 'type', 'metabox' ),
					'fieldValue' => array( Notification::TYPE_ID_SMS ),
					'else'       => 'hide',
				),
			),
			'sms_message'      => array(
				'type'         => 'textarea',
				'label'        => esc_html__( 'Message', 'motopress-appointment' ),
				'description'  => mpapp()->emails()->notificationEmail( null, null )->getTags()->getDescription(),
				'rows'         => 5,
				'size'         => 'large',
				'default'      => __( '{service_name} on {reservation_date} at {start_time} has been booked. {employee_name} is waiting for you at {location_name}', 'motopress-appointment' ),
				'translatable' => true,
				'activeIf'     => array(
					'fieldName'  => mpa_prefix( 'type', 'metabox' ),
					'fieldValue' => array( Notification::TYPE_ID_SMS ),
					'else'       => 'hide',
				),
			),

			// email message fields
			'email_subject'    => array(
				'type'     => 'text',
				'label'    => esc_html__( 'Subject', 'motopress-appointment' ),
				'default'  => mpapp()->settings()->getEmailNotificationDefaultSubject(),
				'size'     => 'large',
				'activeIf' => array(
					'fieldName'  => mpa_prefix( 'type', 'metabox' ),
					'fieldValue' => array( Notification::TYPE_ID_EMAIL ),
					'else'       => 'hide',
				),
			),
			'email_header'     => array(
				'type'     => 'text',
				'label'    => esc_html__( 'Header', 'motopress-appointment' ),
				'default'  => mpapp()->settings()->getEmailNotificationDefaultHeader(),
				'size'     => 'large',
				'activeIf' => array(
					'fieldName'  => mpa_prefix( 'type', 'metabox' ),
					'fieldValue' => array( Notification::TYPE_ID_EMAIL ),
					'else'       => 'hide',
				),
			),
			'email_message'    => array(
				'type'        => 'rich-editor',
				'label'       => esc_html__( 'Message', 'motopress-appointment' ),
				'description' => mpapp()->emails()->notificationEmail( null, null )->getTags()->getDescription(),
				'rows'        => 20,
				'default'     => mpapp()->settings()->getEmailNotificationDefaultMessage(),
				'activeIf'    => array(
					'fieldName'  => mpa_prefix( 'type', 'metabox' ),
					'fieldValue' => array( Notification::TYPE_ID_EMAIL ),
					'else'       => 'hide',
				),
			),
		);

		return $fields;
	}

	protected function isFieldMustBeValidatedBeforeSave( AbstractField $field ): bool {

		$notificationType = ! empty( $_POST['_mpa_type'] ) ? sanitize_text_field( wp_unslash( $_POST['_mpa_type'] ) ) : '';

		$isFieldMustBeValidateBeforeSave = true;

		if ( ( Notification::TYPE_ID_EMAIL === $notificationType &&
			in_array( $field->getName(), array( 'custom_phones', 'sms_message' ), true ) ) ||

			( Notification::TYPE_ID_SMS === $notificationType &&
			in_array( $field->getName(), array( 'custom_emails', 'email_subject', 'email_header', 'email_message' ), true ) )
		) {
				$isFieldMustBeValidateBeforeSave = false;
		}

		return $isFieldMustBeValidateBeforeSave;
	}
}
