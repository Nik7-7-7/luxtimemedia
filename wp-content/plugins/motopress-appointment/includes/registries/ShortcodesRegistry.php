<?php

namespace MotoPress\Appointment\Registries;

use MotoPress\Appointment\Shortcodes;
use MotoPress\Appointment\Shortcodes\SingleEmployee as EmployeeShortcodes;
use MotoPress\Appointment\Views\ShortcodesView;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class ShortcodesRegistry {

	/**
	 * @var Shortcodes\AbstractShortcode[]
	 *
	 * @since 1.0
	 */
	protected $shortcodes = array();

	/**
	 * @return Shortcodes\AppointmentFormShortcode
	 *
	 * @since 1.0
	 */
	public function appointmentForm() {
		if ( ! isset( $this->shortcodes['appointmentForm'] ) ) {
			$this->shortcodes['appointmentForm'] = new Shortcodes\AppointmentFormShortcode();

			ShortcodesView::getInstance()->addAppointmentFormActions();
		}

		return $this->shortcodes['appointmentForm'];
	}

	/**
	 * @return Shortcodes\CustomerAccountShortcode
	 *
	 * @since 1.18.0
	 */
	public function customerAccount() {
		if ( ! isset( $this->shortcodes['customerAccount'] ) ) {
			$this->shortcodes['customerAccount'] = new Shortcodes\CustomerAccountShortcode();
		}

		return $this->shortcodes['customerAccount'];
	}

	/**
	 * @return Shortcodes\EmployeesListShortcode
	 *
	 * @since 1.2
	 */
	public function employeesList() {
		if ( ! isset( $this->shortcodes['employeesList'] ) ) {
			$this->shortcodes['employeesList'] = new Shortcodes\EmployeesListShortcode();

			ShortcodesView::getInstance()->addEmployeesListActions();
		}

		return $this->shortcodes['employeesList'];
	}

	/**
	 * @return EmployeeShortcodes\EmployeeImageShortcode
	 *
	 * @since 1.2
	 */
	public function employeeImage() {
		if ( ! isset( $this->shortcodes['employeeImage'] ) ) {
			$this->shortcodes['employeeImage'] = new EmployeeShortcodes\EmployeeImageShortcode();
		}

		return $this->shortcodes['employeeImage'];
	}

	/**
	 * @return EmployeeShortcodes\EmployeeTitleShortcode
	 *
	 * @since 1.2
	 */
	public function employeeTitle() {
		if ( ! isset( $this->shortcodes['employeeTitle'] ) ) {
			$this->shortcodes['employeeTitle'] = new EmployeeShortcodes\EmployeeTitleShortcode();
		}

		return $this->shortcodes['employeeTitle'];
	}

	/**
	 * @return EmployeeShortcodes\EmployeeServicesListShortcode
	 *
	 * @since 1.2
	 */
	public function employeeServiceList() {
		if ( ! isset( $this->shortcodes['employeeServiceList'] ) ) {
			$this->shortcodes['employeeServiceList'] = new EmployeeShortcodes\EmployeeServicesListShortcode();
		}

		return $this->shortcodes['employeeServiceList'];
	}

	/**
	 * @return EmployeeShortcodes\EmployeeScheduleShortcode
	 *
	 * @since 1.2
	 */
	public function employeeSchedule() {
		if ( ! isset( $this->shortcodes['employeeSchedule'] ) ) {
			$this->shortcodes['employeeSchedule'] = new EmployeeShortcodes\EmployeeScheduleShortcode();
		}

		return $this->shortcodes['employeeSchedule'];
	}

	/**
	 * @return EmployeeShortcodes\EmployeeContentShortcode
	 *
	 * @since 1.2
	 */
	public function employeeContent() {
		if ( ! isset( $this->shortcodes['employeeContent'] ) ) {
			$this->shortcodes['employeeContent'] = new EmployeeShortcodes\EmployeeContentShortcode();
		}

		return $this->shortcodes['employeeContent'];
	}

	/**
	 * @return EmployeeShortcodes\EmployeeContactsShortcode
	 *
	 * @since 1.2
	 */
	public function employeeContacts() {
		if ( ! isset( $this->shortcodes['employeeContacts'] ) ) {
			$this->shortcodes['employeeContacts'] = new EmployeeShortcodes\EmployeeContactsShortcode();
		}

		return $this->shortcodes['employeeContacts'];
	}

	/**
	 * @return EmployeeShortcodes\EmployeeSocialNetworksShortcode
	 *
	 * @since 1.2
	 */
	public function employeeSocialNetworks() {
		if ( ! isset( $this->shortcodes['employeeSocialNetworks'] ) ) {
			$this->shortcodes['employeeSocialNetworks'] = new EmployeeShortcodes\EmployeeSocialNetworksShortcode();
		}

		return $this->shortcodes['employeeSocialNetworks'];
	}

	/**
	 * @return EmployeeShortcodes\EmployeeAdditionalInfoShortcode
	 *
	 * @since 1.2
	 */
	public function employeeAdditionalInfo() {
		if ( ! isset( $this->shortcodes['employeeAdditionalInfo'] ) ) {
			$this->shortcodes['employeeAdditionalInfo'] = new EmployeeShortcodes\EmployeeAdditionalInfoShortcode();
		}

		return $this->shortcodes['employeeAdditionalInfo'];
	}

	/**
	 * @return Shortcodes\LocationsListShortcode
	 *
	 * @since 1.2
	 */
	public function locationsList() {
		if ( ! isset( $this->shortcodes['locationsList'] ) ) {
			$this->shortcodes['locationsList'] = new Shortcodes\LocationsListShortcode();

			ShortcodesView::getInstance()->addLocationsListActions();
		}

		return $this->shortcodes['locationsList'];
	}

	/**
	 * @return Shortcodes\ServicesListShortcode
	 *
	 * @since 1.2
	 */
	public function servicesList() {
		if ( ! isset( $this->shortcodes['servicesList'] ) ) {
			$this->shortcodes['servicesList'] = new Shortcodes\ServicesListShortcode();

			ShortcodesView::getInstance()->addServicesListActions();
		}

		return $this->shortcodes['servicesList'];
	}

	/**
	 * @return Shortcodes\ServiceCategoriesShortcode
	 *
	 * @since 1.2
	 */
	public function serviceCategories() {
		if ( ! isset( $this->shortcodes['serviceCategories'] ) ) {
			$this->shortcodes['serviceCategories'] = new Shortcodes\ServiceCategoriesShortcode();

			ShortcodesView::getInstance()->addServiceCategoriesActions();
		}

		return $this->shortcodes['serviceCategories'];
	}

	/**
	 * @return Shortcodes\BookingDetailsShortcode
	 */
	public function bookingDetails() {
		if ( ! isset( $this->shortcodes['bookingDetails'] ) ) {
			$this->shortcodes['bookingDetails'] = new Shortcodes\BookingDetailsShortcode();
		}

		return $this->shortcodes['bookingDetails'];
	}

	/**
	 * @return Shortcodes\BookingCancellationLink
	 * @since 1.24.0
	 */
	public function bookingCancellationLink() {
		if ( ! isset( $this->shortcodes['bookingCancelationLink'] ) ) {
			$this->shortcodes['bookingCancelationLink'] = new Shortcodes\BookingCancellationLinkShortcode();
		}

		return $this->shortcodes['bookingCancelationLink'];
	}

	/**
	 * @return Shortcodes\ReservationReceivedShortcode
	 */
	public function reservationReceived() {
		if ( ! isset( $this->shortcodes['reservationReceived'] ) ) {
			$this->shortcodes['reservationReceived'] = new Shortcodes\ReservationReceivedShortcode();
		}

		return $this->shortcodes['reservationReceived'];
	}

	/**
	 * @since 1.0
	 */
	public function registerAll() {
		$this->getShortcodes();
	}

	/**
	 * @param string|string[] $shortcode One or more names.
	 * @return Shortcodes\AbstractShortcode|Shortcodes\AbstractShortcode[]|null
	 *
	 * @since 1.3
	 */
	public function getShortcodeByName( $shortcode ) {
		$shortcodes = array();

		foreach ( (array) $shortcode as $shortcodeName ) {
			$methodName = mpa_str_to_method_name( mpa_unprefix( $shortcodeName ) );

			if ( method_exists( $this, $methodName ) ) {
				$instance = $this->$methodName();

				$shortcodes[ $instance->getName() ] = $instance;
			}
		}

		if ( ! is_array( $shortcode ) ) {
			if ( ! empty( $shortcodes ) ) {
				return reset( $shortcodes );
			} else {
				return null;
			}
		} else {
			return $shortcodes;
		}
	}

	/**
	 * @return Shortcodes\AbstractShortcode[] All available shortcodes.
	 *
	 * @since 1.2
	 */
	public function getShortcodes() {
		return array(
			$this->appointmentForm(),
			$this->customerAccount(),

			// Lists
			$this->employeesList(),
			$this->locationsList(),
			$this->servicesList(),
			$this->serviceCategories(),

			// Single employee
			$this->employeeImage(),
			$this->employeeTitle(),
			$this->employeeServiceList(),
			$this->employeeSchedule(),
			$this->employeeContent(),
			$this->employeeContacts(),
			$this->employeeSocialNetworks(),
			$this->employeeAdditionalInfo(),

			// Direct link action
			$this->bookingDetails(),
			$this->bookingCancellationLink(),

			$this->reservationReceived(),
		);
	}

	/**
	 * @return Shortcodes\AbstractShortcode[] Shortcodes that have their Edit Shortcode Page.
	 *
	 * @since 1.2
	 */
	public function getPostShortcodes() {
		return array(
			$this->appointmentForm(),

			// Lists
			$this->employeesList(),
			$this->locationsList(),
			$this->servicesList(),
			$this->serviceCategories(),
		);
	}

	/**
	 * @param string|string[] Optional. One or more shortcode names. All
	 *     shortcodes by default.
	 * @return array
	 *
	 * @since 1.3
	 */
	public function getShortcodeDetails( $shortcode = '' ) {
		if ( empty( $shortcode ) ) {
			$shortcodes = $this->getShortcodes();
		} else {
			$shortcodes = $this->getShortcodeByName( (array) $shortcode );
		}

		$details = array();

		foreach ( $shortcodes as $shortcode ) {
			// Notice: return value of getShortcodes() has numeric indexes
			$details[ $shortcode->getName() ] = array(
				'label'      => $shortcode->getLabel(),
				'attributes' => $shortcode->getAttributes(),
			);
		}

		return $details;
	}
}
