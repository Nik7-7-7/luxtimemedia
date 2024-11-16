<?php

namespace MotoPress\Appointment\Registries;

use MotoPress\Appointment\AdminPages\Custom as CustomPages;
use MotoPress\Appointment\AdminPages\Edit   as EditPages;
use MotoPress\Appointment\AdminPages\Manage as ManagePages;
use MotoPress\Appointment\Handlers\SecurityHandler;
use MotoPress\Appointment\PostTypes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class PagesRegistry {

	/**
	 * @var CustomPages\AbstractCustomPage[]
	 *
	 * @since 1.0
	 */
	protected $customPages = array();

	/**
	 * @var ManagePages\ManagePostsPage[]
	 *
	 * @since 1.0
	 */
	protected $managePages = array();

	/**
	 * @var EditPages\EditPostPage[]
	 *
	 * @since 1.0
	 */
	protected $editPages = array();

	/**
	 * @param string $postType
	 * @return EditPages\EditPostPage|null
	 *
	 * @since 1.0
	 */
	public function getEditPage( $postType ) {
		$method = 'edit' . ucfirst( mpa_unprefix( $postType ) );

		if ( method_exists( $this, $method ) ) {
			return $this->$method();
		} else {
			return null;
		}
	}

	/**
	 * @return CustomPages\AppointmentMenuPage
	 *
	 * @since 1.0
	 */
	public function appointmentMenu() {
		if ( ! isset( $this->customPages['appointmentMenu'] ) ) {
			$this->customPages['appointmentMenu'] = new CustomPages\AppointmentMenuPage(
				'appointment_menu',
				array(
					'parent_menu' => '',
					'menu_icon'   => 'dashicons-calendar',
					'capability'  => 'read_mpa_bookings',
				)
			);
		}

		return $this->customPages['appointmentMenu'];
	}

	/**
	 * @return CustomPages\SettingsPage
	 *
	 * @since 1.0
	 */
	public function settings() {
		if ( ! isset( $this->customPages['settings'] ) ) {
			$this->customPages['settings'] = new CustomPages\SettingsPage(
				'settings',
				array(
					'parent_menu' => $this->appointmentMenu()->getId(),
				)
			);
		}

		return $this->customPages['settings'];
	}

	/**
	 * @return CustomPages\HelpPage
	 *
	 * @since 1.1.0
	 */
	public function help() {
		if ( ! isset( $this->customPages['help'] ) ) {
			$this->customPages['help'] = new CustomPages\HelpPage(
				'help',
				array(
					'parent_menu' => $this->appointmentMenu()->getId(),
					'capability'  => 'edit_mpa_shortcodes',
				)
			);
		}

		return $this->customPages['help'];
	}

	/**
	 * @return CustomPages\ExtensionsPage
	 *
	 * @since 1.16.0
	 */
	public function extensions() {
		if ( ! isset( $this->customPages['extensions'] ) ) {
			$this->customPages['extensions'] = new CustomPages\ExtensionsPage(
				'extensions',
				array(
					'parent_menu' => $this->appointmentMenu()->getId(),
					'capability'  => SecurityHandler::CAPABILITY_VIEW_EXTENSIONS,
					'position'    => 200,
				)
			);
		}

		return $this->customPages['extensions'];
	}

	/**
	 * @return CustomPages\CalendarPage
	 */
	public function calendar() {
		if ( ! isset( $this->customPages['calendar'] ) ) {
			$this->customPages['calendar'] = new CustomPages\CalendarPage(
				'calendar',
				array(
					'parent_menu' => $this->appointmentMenu()->getId(),
					'capability'  => 'read_mpa_bookings',
					'position'    => 0,
				)
			);
		}

		return $this->customPages['calendar'];
	}

	/**
	 * @return CustomPages\CustomersPage
	 * @since 1.18.0
	 */
	public function customers() {
		if ( ! isset( $this->customPages['customers'] ) ) {
			$this->customPages['customers'] = new CustomPages\CustomersPage( 'customers', [
				'parent_menu' => $this->appointmentMenu()->getId(),
				'capability'  => SecurityHandler::CAPABILITY_LIST_CUSTOMERS,
				'position'    => 9,
			] );
		}

		return $this->customPages['customers'];
	}

	/**
	 * @since 1.21.0
	 *
	 * @return CustomPages\AnalyticsPage
	 */
	public function analytics() {
		if ( ! isset( $this->customPages['analytics'] ) ) {
			$this->customPages['analytics'] = new CustomPages\AnalyticsPage(
				'analytics',
				array(
					'parent_menu' => $this->appointmentMenu()->getId(),
					'capability'  => SecurityHandler::CAPABILITY_VIEW_ANALYTICS,
					'position'    => 10,
				)
			);
		}

		return $this->customPages['analytics'];
	}

	/**
	 * @return ManagePages\ManageEmployeesPage
	 *
	 * @since 1.0
	 */
	public function manageEmployees() {
		if ( ! isset( $this->managePages['manageEmployees'] ) ) {
			$this->managePages['manageEmployees'] = new ManagePages\ManageEmployeesPage( PostTypes\EmployeePostType::POST_TYPE );
		}

        return $this->managePages['manageEmployees'];
    }

	/**
	 * @return ManagePages\ManageSchedulesPage
	 *
	 * @since 1.0
	 */
	public function manageSchedules() {
		if ( ! isset( $this->managePages['manageSchedules'] ) ) {
			$this->managePages['manageSchedules'] = new ManagePages\ManageSchedulesPage( PostTypes\SchedulePostType::POST_TYPE );
		}

		return $this->managePages['manageSchedules'];
	}

	/**
	 * @return ManagePages\ManageLocationsPage
	 *
	 * @since 1.0
	 */
	public function manageLocations() {
		if ( ! isset( $this->managePages['manageLocations'] ) ) {
			$this->managePages['manageLocations'] = new ManagePages\ManageLocationsPage( PostTypes\LocationPostType::POST_TYPE );
		}

		return $this->managePages['manageLocations'];
	}

	/**
	 * @return ManagePages\ManageServicesPage
	 *
	 * @since 1.0
	 */
	public function manageServices() {
		if ( ! isset( $this->managePages['manageServices'] ) ) {
			$this->managePages['manageServices'] = new ManagePages\ManageServicesPage( PostTypes\ServicePostType::POST_TYPE );
		}

		return $this->managePages['manageServices'];
	}

	/**
	 * @return ManagePages\ManageBookingsPage
	 *
	 * @since 1.0
	 */
	public function manageBookings() {
		if ( ! isset( $this->managePages['manageBookings'] ) ) {
			$this->managePages['manageBookings'] = new ManagePages\ManageBookingsPage( PostTypes\BookingPostType::POST_TYPE );
		}

		return $this->managePages['manageBookings'];
	}

	/**
	 * @since 1.5.0
	 *
	 * @return ManagePages\ManagePaymentsPage
	 */
	public function managePayments() {
		if ( ! isset( $this->managePages[ __FUNCTION__ ] ) ) {
			$this->managePages[ __FUNCTION__ ] = new ManagePages\ManagePaymentsPage( PostTypes\PaymentPostType::POST_TYPE );
		}

		return $this->managePages[ __FUNCTION__ ];
	}

	/**
	 * @return ManagePages\ManageNotificationsPage
	 *
	 * @since 1.13.0
	 */
	public function manageNotifications() {
		if ( ! isset( $this->managePages[ __FUNCTION__ ] ) ) {
			$this->managePages[ __FUNCTION__ ] = new ManagePages\ManageNotificationsPage( PostTypes\NotificationPostType::POST_TYPE );
		}

		return $this->managePages[ __FUNCTION__ ];
	}

	/**
	 * @return ManagePages\ManagePostsPage
	 *
	 * @since 1.2
	 */
	public function manageShortcodes() {
		if ( ! isset( $this->managePages['manageShortcodes'] ) ) {
			$this->managePages['manageShortcodes'] = new ManagePages\ManageShortcodesPage( PostTypes\ShortcodePostType::POST_TYPE );
		}

		return $this->managePages['manageShortcodes'];
	}

	/**
	 * @return EditPages\EditPostPage
	 *
	 * @since 1.0
	 */
	public function editEmployee() {
		if ( ! isset( $this->editPages['editEmployee'] ) ) {
			$this->editPages['editEmployee'] = new EditPages\EditPostPage( PostTypes\EmployeePostType::POST_TYPE );
		}

		return $this->editPages['editEmployee'];
	}

	/**
	 * @return EditPages\EditPostPage
	 *
	 * @since 1.0
	 */
	public function editSchedule() {
		if ( ! isset( $this->editPages['editSchedule'] ) ) {
			$this->editPages['editSchedule'] = new EditPages\EditPostPage( PostTypes\SchedulePostType::POST_TYPE );
		}

		return $this->editPages['editSchedule'];
	}

	/**
	 * @return EditPages\EditPostPage
	 *
	 * @since 1.0
	 */
	public function editLocation() {
		if ( ! isset( $this->editPages['editLocation'] ) ) {
			$this->editPages['editLocation'] = new EditPages\EditPostPage( PostTypes\LocationPostType::POST_TYPE );
		}

		return $this->editPages['editLocation'];
	}

	/**
	 * @return EditPages\EditPostPage
	 *
	 * @since 1.0
	 */
	public function editService() {
		if ( ! isset( $this->editPages['editService'] ) ) {
			$this->editPages['editService'] = new EditPages\EditPostPage( PostTypes\ServicePostType::POST_TYPE );
		}

		return $this->editPages['editService'];
	}

	/**
	 * @return EditPages\EditNoCommentsPage
	 *
	 * @since 1.0
	 */
	public function editBooking() {
		if ( ! isset( $this->editPages['editBooking'] ) ) {
			$this->editPages['editBooking'] = new EditPages\EditBookingPage( PostTypes\BookingPostType::POST_TYPE );
		}

		return $this->editPages['editBooking'];
	}

	/**
	 * @since 1.5.0
	 *
	 * @return EditPages\EditNoCommentsPage
	 */
	public function editPayment() {
		if ( ! isset( $this->editPages[ __FUNCTION__ ] ) ) {
			$this->editPages[ __FUNCTION__ ] = new EditPages\EditNoCommentsPage( PostTypes\PaymentPostType::POST_TYPE );
		}

		return $this->editPages[ __FUNCTION__ ];
	}

	/**
	 * @since 1.13.0
	 *
	 * @return EditPages\EditPostPage
	 */
	public function editNotification() {
		if ( ! isset( $this->editPages[ __FUNCTION__ ] ) ) {
			$this->editPages[ __FUNCTION__ ] = new EditPages\EditPostPage( PostTypes\NotificationPostType::POST_TYPE );
		}

		return $this->editPages[ __FUNCTION__ ];
	}

	/**
	 * @return EditPages\EditShortcodePage
	 *
	 * @since 1.2
	 */
	public function editShortcode() {
		if ( ! isset( $this->editPages['editShortcode'] ) ) {
			$this->editPages['editShortcode'] = new EditPages\EditShortcodePage( PostTypes\ShortcodePostType::POST_TYPE );
		}

		return $this->editPages['editShortcode'];
	}

	/**
	 * @since 1.11.0
	 *
	 * @return EditPages\EditCouponPage
	 */
	public function editCoupon() {
		if ( ! isset( $this->editPages[ __FUNCTION__ ] ) ) {
			$this->editPages[ __FUNCTION__ ] = new EditPages\EditCouponPage( PostTypes\CouponPostType::POST_TYPE );
		}

		return $this->editPages[ __FUNCTION__ ];
	}

	/**
	 * @since 1.0
	 */
	public function registerCustomPages() {

		$this->appointmentMenu();
		$this->settings();
		$this->help();
		$this->extensions();
		$this->calendar();
		$this->customers();
		$this->analytics();

		
	}

	/**
	 * @since 1.0
	 */
	public function registerManagePostsPages() {
		$this->manageEmployees();
		$this->manageSchedules();
		$this->manageLocations();
		$this->manageServices();
		$this->manageBookings();
		$this->managePayments();
		$this->manageNotifications();
		$this->manageShortcodes();
	}

	/**
	 * @since 1.0
	 */
	public function registerEditPostPages() {
		$this->editEmployee();
		$this->editSchedule();
		$this->editLocation();
		$this->editService();
		$this->editBooking();
		$this->editPayment();
		$this->editNotification();
		$this->editShortcode();
		$this->editCoupon();
	}
}
