<?php
/**
 *
 * @package MotoPress\Appointment\Rest
 * @since 1.8.0
 */

namespace MotoPress\Appointment\Rest;

use MotoPress\Appointment\Rest\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit;

/**
 * Class responsible for loading the REST API and all REST API namespaces.
 */
class Server {
	use SingletonTrait;

	/**
	 * todo: delete when all features are migrated to mpa/v1
	 */
	const NAMESPACE_V_MOTOPRESS_APPOINTMENT_V1 = __NAMESPACE__ . '\Controllers\Motopress\Appointment\V1\\';

	const NAMESPACE_V1 = __NAMESPACE__ . '\Controllers\V1\\';

	/**
	 * @var array List of all included API controllers for version /motopress/appointment/v1
	 * todo: delete when all features are migrated to mpa/v1
	 */
	const CONTROLLERS_V_MOTOPRESS_APPOINTMENT_V1 = array(
		'bookings'          => 'BookingsRestController',
		'bookings_calendar' => 'CalendarRestController',
		'payments'          => 'PaymentsRestController',
		'schedules'         => 'SchedulesRestController',
		'services'          => 'ServicesRestController',
		'settings'          => 'SettingsRestController',
		'coupons'           => 'CouponRestController',
		'customers'         => 'CustomersRestController',
	);

	/**
	 * @var array List of all included API controllers for version 1
	 */
	const CONTROLLERS_V1 = array(
		'bookings'     => 'BookingsController',
		'payments'     => 'PaymentsController',
		'reservations' => 'ReservationsController',
		'services'     => 'ServicesController',
		'locations'    => 'LocationsController',
		'employees'    => 'EmployeesController',
		'coupons'      => 'CouponsController',
	);

	/**
	 * REST API namespaces and endpoints.
	 *
	 * @var array
	 */
	protected $controllers = array();

	/**
	 * Hook into WordPress ready to init the REST API as needed.
	 */
	public function init() {
		add_action( 'rest_api_init', array( $this, 'registerRestRoutes' ), 10 );
	}

	/**
	 * Register REST API routes.
	 */
	public function registerRestRoutes() {
		foreach ( $this->getRestNamespaces() as $namespace => $controllers ) {
			foreach ( $controllers as $controller_name => $controller_class ) {
				$this->controllers[ $namespace ][ $controller_name ] = new $controller_class();
				$this->controllers[ $namespace ][ $controller_name ]->register_routes();
			}
		}
	}

	/**
	 * Get API namespaces - new namespaces should be registered here.
	 *
	 * @return array List of Namespaces and Main controller classes.
	 */
	private function getRestNamespaces() {
		return apply_filters(
			'mpa_rest_api_get_rest_namespaces',
			array(
				'motopress/appointment/v1' => $this->getControllers( '_MOTOPRESS_APPOINTMENT_V1' ),
				'mpa/v1'                   => $this->getControllers( 1 ),
			)
		);
	}

	/**
	 * List of controllers with their namespace for mpa/$version
	 *
	 * @param  int  $version  Version of Api
	 *
	 * @return array
	 */
	private function getControllers( $version ) {
		global $ver;
		$ver = $version;
		if ( ! defined( 'self::NAMESPACE_V' . $version ) ||
			! defined( 'self::CONTROLLERS_V' . $version )
		) {
			wp_die( 'Version API of ' . esc_html( $version ) . ' not found.' );
		}

		return array_map(
			function ( $controller ) {
				global $ver;

				return constant( 'self::NAMESPACE_V' . $ver ) . $controller;
			},
			constant( 'self::CONTROLLERS_V' . $ver )
		);
	}
}
