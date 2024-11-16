<?php

namespace MotoPress\Appointment\Handlers;

use MotoPress\Appointment\Metaboxes\Booking;
use MotoPress\Appointment\Metaboxes\Coupon;
use MotoPress\Appointment\Metaboxes\Employee;
use MotoPress\Appointment\Metaboxes\Payment;
use MotoPress\Appointment\Metaboxes\Schedule;
use MotoPress\Appointment\Metaboxes\Service;
use MotoPress\Appointment\Metaboxes\Shortcode;
use MotoPress\Appointment\Metaboxes\AbstractMetabox;
use MotoPress\Appointment\Metaboxes\SubmitMetabox;
use MotoPress\Appointment\Metaboxes\Notification;
use MotoPress\Appointment\PostTypes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class AdminMetaboxHandler {

	/**
	 * @var AbstractMetabox[]
	 */
	protected $metaboxes = array();


	public function __construct() {

		$employeeCpt = mpa_unprefix( PostTypes\EmployeePostType::POST_TYPE );
		add_action(
			"mpa_edit_{$employeeCpt}_page_loaded",
			function() {
				$this->registerEmployeeMetaboxes();
			}
		);

		$scheduleCpt = mpa_unprefix( PostTypes\SchedulePostType::POST_TYPE );
		add_action(
			"mpa_edit_{$scheduleCpt}_page_loaded",
			function() {
				$this->registerScheduleMetaboxes();
			}
		);

		$serviceCpt = mpa_unprefix( PostTypes\ServicePostType::POST_TYPE );
		add_action(
			"mpa_edit_{$serviceCpt}_page_loaded",
			function() {
				$this->registerServiceMetaboxes();
			}
		);

		$couponCpt = mpa_unprefix( PostTypes\CouponPostType::POST_TYPE );
		add_action(
			"mpa_edit_{$couponCpt}_page_loaded",
			function() {
				$this->registerCouponMetaboxes();
			}
		);

		$bookingCpt = mpa_unprefix( PostTypes\BookingPostType::POST_TYPE );
		add_action(
			"mpa_edit_{$bookingCpt}_page_loaded",
			function() {
				$this->registerBookingMetaboxes();
			}
		);

		$paymentCpt = mpa_unprefix( PostTypes\PaymentPostType::POST_TYPE );
		add_action(
			"mpa_edit_{$paymentCpt}_page_loaded",
			function() {
				$this->registerPaymentMetaboxes();
			}
		);

		$notificationCpt = mpa_unprefix( PostTypes\NotificationPostType::POST_TYPE );
		add_action(
			"mpa_edit_{$notificationCpt}_page_loaded",
			function() {
				$this->registerNotificationMetaboxes();
			}
		);

		$shortcodeCpt = mpa_unprefix( PostTypes\ShortcodePostType::POST_TYPE );
		add_action(
			"mpa_edit_{$shortcodeCpt}_page_loaded",
			function() {
				$this->registerShortcodeMetaboxes();
			}
		);
	}


	private function registerEmployeeMetaboxes() {

		if ( isset( $this->metaboxes['employeeGoogleCalendar'] ) ) {
			return;
		}

		$this->metaboxes['employeeGoogleCalendar'] = new Employee\EmployeeGoogleCalendarMetabox(
			PostTypes\EmployeePostType::POST_TYPE,
			'side'
		);

		$this->metaboxes['employeeContacts'] = new Employee\EmployeeContactsMetabox(
			PostTypes\EmployeePostType::POST_TYPE
		);

		$this->metaboxes['employeeSocialNetworks'] = new Employee\EmployeeSocialNetworksMetabox(
			PostTypes\EmployeePostType::POST_TYPE
		);

		$this->metaboxes['employeeAdditionalInfo'] = new Employee\EmployeeAdditionalInfoMetabox(
			PostTypes\EmployeePostType::POST_TYPE
		);
	}


	private function registerScheduleMetaboxes() {

		if ( isset( $this->metaboxes['scheduleSettings'] ) ) {
			return;
		}

		$this->metaboxes['scheduleSettings']       = new Schedule\ScheduleSettingsMetabox(
			PostTypes\SchedulePostType::POST_TYPE
		);
		$this->metaboxes['scheduleTimetable']      = new Schedule\ScheduleTimetableMetabox(
			PostTypes\SchedulePostType::POST_TYPE
		);
		$this->metaboxes['scheduleDaysOff']        = new Schedule\ScheduleDaysOffMetabox(
			PostTypes\SchedulePostType::POST_TYPE
		);
		$this->metaboxes['scheduleCustomWorkdays'] = new Schedule\ScheduleCustomWorkdaysMetabox(
			PostTypes\SchedulePostType::POST_TYPE
		);
	}


	private function registerServiceMetaboxes() {

		if ( isset( $this->metaboxes['serviceSettings'] ) ) {
			return;
		}

		$this->metaboxes['serviceSettings']   = new Service\ServiceSettingsMetabox(
			PostTypes\ServicePostType::POST_TYPE
		);
		$this->metaboxes['servicePerformers'] = new Service\ServicePerformersMetabox(
			PostTypes\ServicePostType::POST_TYPE
		);
		$this->metaboxes['serviceNotices']    = new Service\ServiceNoticesMetabox(
			PostTypes\ServicePostType::POST_TYPE,
			'normal',
			'low'
		);
		$this->metaboxes['serviceDeposit']    = new Service\ServiceDepositMetabox(
			PostTypes\ServicePostType::POST_TYPE
		);
	}


	private function registerCouponMetaboxes() {

		if ( isset( $this->metaboxes['couponSettings'] ) ) {
			return;
		}

		$this->metaboxes['couponSettings'] = new Coupon\CouponSettingsMetabox(
			PostTypes\CouponPostType::POST_TYPE
		);
	}


	private function registerBookingMetaboxes() {

		if ( isset( $this->metaboxes['bookingSubmit'] ) ) {
			return;
		}

		$this->metaboxes['bookingSubmit'] = new Booking\BookingStatusMetabox(
			PostTypes\BookingPostType::POST_TYPE
		);

		$this->metaboxes['bookingPrice'] = new Booking\BookingPriceMetabox(
			PostTypes\BookingPostType::POST_TYPE
		);

		$this->metaboxes['bookingCustomer'] = new Booking\BookingCustomerMetabox(
			PostTypes\BookingPostType::POST_TYPE
		);

		$this->metaboxes['resendBookingConfirmationEmail'] = new Booking\ResendBookingConfirmationEmailMetabox(
			PostTypes\BookingPostType::POST_TYPE,
			'side'
		);

		$this->metaboxes['bookingLog'] = new Booking\BookingLogMetabox(
			PostTypes\BookingPostType::POST_TYPE,
			'side'
		);
	}


	private function registerPaymentMetaboxes() {

		if ( isset( $this->metaboxes['paymentSubmit'] ) ) {
			return;
		}

		$this->metaboxes['paymentSubmit']  = new SubmitMetabox(
			PostTypes\PaymentPostType::POST_TYPE
		);
		$this->metaboxes['paymentDetails'] = new Payment\PaymentDetailsMetabox(
			PostTypes\PaymentPostType::POST_TYPE
		);
		$this->metaboxes['paymentLog']     = new Payment\PaymentLogMetabox(
			PostTypes\PaymentPostType::POST_TYPE,
			'side'
		);
	}


	private function registerNotificationMetaboxes() {

		if ( isset( $this->metaboxes['notificationSubmit'] ) ) {
			return;
		}

		$this->metaboxes['notificationSubmit']   = new SubmitMetabox(
			PostTypes\NotificationPostType::POST_TYPE
		);
		$this->metaboxes['notificationSettings'] = new Notification\NotificationSettingsMetabox(
			PostTypes\NotificationPostType::POST_TYPE,
			'normal',
			'high'
		);
		$this->metaboxes['testNotification']     = new Notification\TestNotificationMetabox(
			PostTypes\NotificationPostType::POST_TYPE,
			'side'
		);
	}


	private function registerShortcodeMetaboxes() {

		if ( isset( $this->metaboxes['appointmentForm'] ) ) {
			return;
		}

		$this->metaboxes['appointmentForm']           = new Shortcode\AppointmentFormMetabox(
			PostTypes\ShortcodePostType::POST_TYPE,
			'normal',
			'high'
		);
		$this->metaboxes['appointmentFormDefaults']   = new Shortcode\AppointmentFormDefaultsMetabox(
			PostTypes\ShortcodePostType::POST_TYPE
		);
		$this->metaboxes['appointmentFormLabels']     = new Shortcode\AppointmentFormLabelsMetabox(
			PostTypes\ShortcodePostType::POST_TYPE
		);
		$this->metaboxes['appointmentFormStyles']     = new Shortcode\AppointmentFormStylesMetabox(
			PostTypes\ShortcodePostType::POST_TYPE
		);
		$this->metaboxes['appointmentFormTimepicker'] = new Shortcode\AppointmentFormTimepicker(
			PostTypes\ShortcodePostType::POST_TYPE
		);

		$this->metaboxes['employeesList']     = new Shortcode\EmployeesListMetabox(
			PostTypes\ShortcodePostType::POST_TYPE,
			'normal',
			'high'
		);
		$this->metaboxes['locationsList']     = new Shortcode\LocationsListMetabox(
			PostTypes\ShortcodePostType::POST_TYPE,
			'normal',
			'high'
		);
		$this->metaboxes['servicesList']      = new Shortcode\ServicesListMetabox(
			PostTypes\ShortcodePostType::POST_TYPE,
			'normal',
			'high'
		);
		$this->metaboxes['serviceCategories'] = new Shortcode\ServiceCategoriesMetabox(
			PostTypes\ShortcodePostType::POST_TYPE,
			'normal',
			'high'
		);

		$this->metaboxes['shortcodeOrder']      = new Shortcode\ShortcodeOrderMetabox(
			PostTypes\ShortcodePostType::POST_TYPE,
			'advanced'
		);
		$this->metaboxes['shortcodeTermsOrder'] = new Shortcode\ShortcodeTermsOrderMetabox(
			PostTypes\ShortcodePostType::POST_TYPE,
			'advanced'
		);
		$this->metaboxes['shortcodeAdvanced']   = new Shortcode\ShortcodeAdvancedMetabox(
			PostTypes\ShortcodePostType::POST_TYPE,
			'advanced',
			'low'
		);

		$this->metaboxes['shortcodeExample'] = new Shortcode\ShortcodeExampleMetabox(
			PostTypes\ShortcodePostType::POST_TYPE,
			'side',
			'high'
		);
	}
}
