<?php

namespace MotoPress\Appointment\Handlers;

use \MotoPress\Appointment\PostTypes\EmployeePostType;
use \MotoPress\Appointment\PostTypes\SchedulePostType;
use \MotoPress\Appointment\PostTypes\BookingPostType;
use \MotoPress\Appointment\PostTypes\ReservationPostType;
use \MotoPress\Appointment\PostTypes\PaymentPostType;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.7.0
 */
class SecurityHandler {

	const CURRENT_SECURITY_VERSION = '1.4.3';

	const OPTION_NAME_SECURITY_VERSION = 'mpa_security_version';

	const MPA_EMPLOYEE_META_KEY_WORDPRESS_USER = '_mpa_employee_wordpress_user';

	const ROLE_APPOINTMENT_MANAGER  = 'mpa_appointment_manager';
	const ROLE_APPOINTMENT_EMPLOYEE = 'mpa_appointment_employee';
	const ROLE_APPOINTMENT_CUSTOMER = 'mpa_appointment_customer';

	const CAPABILITY_ASSIGN_USER_TO_EMPLOYEE     = 'mpa_assign_user_to_employee';
	const CAPABILITY_ASSIGN_EMPLOYEE_TO_SCHEDULE = 'mpa_assign_employee_to_schedule';
	const CAPABILITY_VIEW_ANALYTICS              = 'mpa_view_analytics';
	const CAPABILITY_VIEW_EXTENSIONS             = 'mpa_view_extensions';

	const CAPABILITY_LIST_CUSTOMERS = 'mpa_list_customers';
	const CAPABILITY_EDIT_CUSTOMER = 'mpa_edit_customer';
	const CAPABILITY_DELETE_CUSTOMER = 'mpa_delete_customer';

	public function __construct() {

		// Updates roles and their capabilities if security version in database not equals to the current version in code.
		if ( ! $this->isSecurityVersionUpToDate() ) {
			add_action( 'plugins_loaded', array( $this, 'registeringAppointmentRolesAndCapabilities' ) );
		}

		add_action( 'admin_init', array( $this, 'applyCapabilities' ) );
		add_action( 'rest_api_init', array( $this, 'applyCapabilities' ) );
		add_action( 'show_admin_bar', array( $this, 'disableAdminBar' ), 10, 1 );
	}

	public function applyCapabilities() {
		if ( ! is_admin() && ( ! defined( 'REST_REQUEST' ) || ! REST_REQUEST ) ) {
			return;
		}

		add_filter( 'user_has_cap', array( $this, 'applyEmployeeCapabilities' ), 10, 4 );
		add_action( 'pre_get_posts', array( $this, 'excludePermissionDeniedEmployee' ), 10, 1 );
		add_action( 'pre_get_posts', array( $this, 'excludePermissionDeniedSchedule' ), 10, 1 );
		add_filter( 'posts_clauses', array( $this, 'excludePermissionDeniedBooking' ), 10, 2 );
		add_filter( 'posts_clauses', array( $this, 'excludePermissionDeniedPayment' ), 10, 2 );
		add_filter( 'posts_clauses', array( $this, 'excludePermissionDeniedReservation' ), 10, 2 );
		add_filter( 'wp_count_posts', array( $this, 'updatePostCountForExcludedByPermissionDeniedPosts' ), 10, 3 );
	}

	/**
	 * Prevent customer user role from seeing the admin bar.
	 *
	 * @param bool $showAdminBar If should display admin bar.
	 *
	 * @return bool
	 *
	 * @since 1.18.0
	 */
	public function disableAdminBar( $showAdminBar ) {
		$currentUser = wp_get_current_user();

		if ( is_null( $currentUser ) ) {
			return $showAdminBar;
		}

		if ( in_array( self::ROLE_APPOINTMENT_CUSTOMER, (array) $currentUser->roles ) ) {
			$showAdminBar = false;
		}

		return $showAdminBar;
	}

	/**
	 * Overrides user capabilities for Employee and Schedule post types if requested user is Employee.
	 */
	public function applyEmployeeCapabilities( $allUserCaps, $requiredCaps, $args, $wpUser ) {
		$wpPostId = isset( $args[2] ) ? $args[2] : 0;
		$wpPost   = ! empty( $wpPostId ) ? get_post( $wpPostId ) : null;

		// add user capabilities for edit employee if current user is a requested employee
		if ( null != $wpPost && EmployeePostType::POST_TYPE == $wpPost->post_type &&
			( in_array( 'edit_others_mpa_employees', $requiredCaps ) ||
				in_array( 'edit_published_mpa_employees', $requiredCaps ) ) &&
			( isset( $allUserCaps['edit_mpa_employee'] ) && $allUserCaps['edit_mpa_employee'] ) ) {

			$employeeWordPressUserId = get_post_meta( $wpPostId, static::MPA_EMPLOYEE_META_KEY_WORDPRESS_USER, true );

			if ( ! empty( $employeeWordPressUserId ) && $employeeWordPressUserId == $wpUser->ID ) {

				foreach ( $requiredCaps as $cap ) {

					$allUserCaps[ $cap ] = true;
				}
			}
		} elseif ( in_array( 'edit_others_mpa_employees', $requiredCaps ) &&
					( ! isset( $allUserCaps['edit_others_mpa_employees'] ) || ! $allUserCaps['edit_others_mpa_employees'] ) &&
					( isset( $allUserCaps['edit_mpa_employee'] ) && $allUserCaps['edit_mpa_employee'] ) ) {

			$currentUserEmployeeId = mpapp()->repositories()->employee()->findIdByUserId( $wpUser->ID );

			$allUserCaps['edit_others_mpa_employees'] = ! empty( $currentUserEmployeeId );
		}

		// add user capabilities for edit employee schedule if current user is a requested employee
		if ( null != $wpPost && SchedulePostType::POST_TYPE == $wpPost->post_type &&
			( in_array( 'edit_others_mpa_schedules', $requiredCaps ) ||
				in_array( 'edit_published_mpa_schedules', $requiredCaps ) ) &&
			( isset( $allUserCaps['edit_mpa_schedule'] ) && $allUserCaps['edit_mpa_schedule'] ) ) {

			$currentUserEmployee = mpapp()->repositories()->employee()->findByUserId( $wpUser->ID );

			if ( ! empty( $currentUserEmployee ) ) {

				$scheduleEmployeeId = get_post_meta( $wpPostId, '_mpa_employee', true );

				if ( ! empty( $scheduleEmployeeId ) && $scheduleEmployeeId == $currentUserEmployee->getId() ) {

					foreach ( $requiredCaps as $cap ) {

						$allUserCaps[ $cap ] = true;
					}
				}
			}
		} elseif ( in_array( 'edit_others_mpa_schedules', $requiredCaps ) &&
					( ! isset( $allUserCaps['edit_others_mpa_schedules'] ) || ! $allUserCaps['edit_others_mpa_schedules'] ) &&
					( isset( $allUserCaps['edit_mpa_schedule'] ) && $allUserCaps['edit_mpa_schedule'] ) ) {

			$currentUserEmployeeId = mpapp()->repositories()->employee()->findIdByUserId( $wpUser->ID );

			$allUserCaps['edit_others_mpa_schedules'] = ! empty( $currentUserEmployeeId );
		}

		return $allUserCaps;
	}

	protected function isSecurityVersionUpToDate() {
		$securityVersion = get_option( static::OPTION_NAME_SECURITY_VERSION );

		return version_compare( $securityVersion, static::CURRENT_SECURITY_VERSION, '==' );
	}

	protected function setSecurityVersionUpToDate() {
		$wpRoles = wp_roles();

		if ( $wpRoles->use_db ) {
			update_option( static::OPTION_NAME_SECURITY_VERSION, static::CURRENT_SECURITY_VERSION, true );
		}
	}

	protected static function resetSecurityVersion() {
		delete_option( static::OPTION_NAME_SECURITY_VERSION );
	}

	public function registeringAppointmentRolesAndCapabilities() {
		self::removeAppointmentRolesAndCapabilities();
		$this->addRolesAndCapabilities();
	}

	public static function removeAppointmentRolesAndCapabilities() {
		remove_role( static::ROLE_APPOINTMENT_MANAGER );
		remove_role( static::ROLE_APPOINTMENT_EMPLOYEE );
		remove_role( static::ROLE_APPOINTMENT_CUSTOMER );

		$wpRoles = wp_roles();
		$wpRoles->remove_cap( 'administrator', static::CAPABILITY_ASSIGN_USER_TO_EMPLOYEE );
		$wpRoles->remove_cap( 'administrator', static::CAPABILITY_ASSIGN_EMPLOYEE_TO_SCHEDULE );
		$wpRoles->remove_cap( 'administrator', static::CAPABILITY_VIEW_ANALYTICS );
		$wpRoles->remove_cap( 'administrator', static::CAPABILITY_VIEW_EXTENSIONS );
		$wpRoles->remove_cap( 'administrator', static::CAPABILITY_LIST_CUSTOMERS );
		$wpRoles->remove_cap( 'administrator', static::CAPABILITY_EDIT_CUSTOMER );
		$wpRoles->remove_cap( 'administrator', static::CAPABILITY_DELETE_CUSTOMER );

		self::resetSecurityVersion();
	}

	private function addRolesAndCapabilities() {
		$wpRoles = wp_roles();

		// Appointment Manager Role
		$editorRole                     = get_role( 'editor' );
		$appointmentManagerCapabilities = $editorRole->capabilities;

		$appointmentPostTypes = mpapp()->postTypes()->getPostTypes();

		foreach ( $appointmentPostTypes as $postType ) {

			$postTypeCapabilities = $this->getPostTypeCapabilities( $postType->getPostType() );

			foreach ( $postTypeCapabilities as $capName ) {

				$appointmentManagerCapabilities[ $capName ] = true;
				$wpRoles->add_cap( 'administrator', $capName );
			}
		}

		$appointmentManagerCapabilities[ static::CAPABILITY_ASSIGN_USER_TO_EMPLOYEE ]     = true;
		$appointmentManagerCapabilities[ static::CAPABILITY_ASSIGN_EMPLOYEE_TO_SCHEDULE ] = true;
		$appointmentManagerCapabilities[ static::CAPABILITY_VIEW_ANALYTICS ]              = true;
		$appointmentManagerCapabilities[ static::CAPABILITY_VIEW_EXTENSIONS ]             = true;
		$appointmentManagerCapabilities[ static::CAPABILITY_LIST_CUSTOMERS ]              = true;
		$appointmentManagerCapabilities[ static::CAPABILITY_EDIT_CUSTOMER ]               = true;
		$appointmentManagerCapabilities['list_users']                                     = true;

		$wpRoles->add_cap( 'administrator', static::CAPABILITY_ASSIGN_USER_TO_EMPLOYEE );
		$wpRoles->add_cap( 'administrator', static::CAPABILITY_ASSIGN_EMPLOYEE_TO_SCHEDULE );
		$wpRoles->add_cap( 'administrator', static::CAPABILITY_VIEW_ANALYTICS );
		$wpRoles->add_cap( 'administrator', static::CAPABILITY_VIEW_EXTENSIONS );
		$wpRoles->add_cap( 'administrator', static::CAPABILITY_LIST_CUSTOMERS );
		$wpRoles->add_cap( 'administrator', static::CAPABILITY_EDIT_CUSTOMER );
		$wpRoles->add_cap( 'administrator', static::CAPABILITY_DELETE_CUSTOMER );

		add_role(
			static::ROLE_APPOINTMENT_MANAGER,
			__( 'Appointment Manager', 'motopress-appointment' ),
			$appointmentManagerCapabilities
		);

		// Appointment Employee Role

		add_role(
			static::ROLE_APPOINTMENT_EMPLOYEE,
			__( 'Appointment Employee', 'motopress-appointment' ),
			array(
				'read'                             => true,

				'read_mpa_bookings'                => true,
				'edit_mpa_bookings'                => true,

				'edit_mpa_reservations'            => true,

				'read_mpa_payments'                => true,
				'edit_mpa_payments'                => true,

				'read_mpa_employee'                => true,
				'edit_mpa_employee'                => true,
				'read_mpa_employees'               => true,
				'edit_mpa_employees'               => true,

				'read_mpa_schedule'                => true,
				'edit_mpa_schedule'                => true,
				'read_mpa_schedules'               => true,
				'edit_mpa_schedules'               => true,

				'read_mpa_locations'               => true,
				'edit_mpa_locations'               => true,

				'read_mpa_services'                => true,
				'edit_mpa_services'                => true,

				'view_admin_dashboard'             => true,

				static::CAPABILITY_VIEW_ANALYTICS  => true,
				static::CAPABILITY_VIEW_EXTENSIONS => true,
			)
		);

		// Appointment Customer Role
		add_role( static::ROLE_APPOINTMENT_CUSTOMER, __( 'Appointment Customer', 'motopress-appointment' ),
			array(
				'read_mpa_bookings' => true,
			)
		);

		$this->setSecurityVersionUpToDate();
	}

	private function getPostTypeCapabilities( string $singularPostTypeName ): array {
		return array(
			'edit_post'              => "edit_{$singularPostTypeName}",
			'read_post'              => "read_{$singularPostTypeName}",
			'delete_post'            => "delete_{$singularPostTypeName}",
			'edit_posts'             => "edit_{$singularPostTypeName}s",
			'edit_others_posts'      => "edit_others_{$singularPostTypeName}s",
			'publish_posts'          => "publish_{$singularPostTypeName}s",
			'read_private_posts'     => "read_private_{$singularPostTypeName}s",
			'read'                   => "read_{$singularPostTypeName}s",
			'delete_posts'           => "delete_{$singularPostTypeName}s",
			'delete_private_posts'   => "delete_private_{$singularPostTypeName}s",
			'delete_published_posts' => "delete_published_{$singularPostTypeName}s",
			'delete_others_posts'    => "delete_others_{$singularPostTypeName}s",
			'edit_private_posts'     => "edit_private_{$singularPostTypeName}s",
			'edit_published_posts'   => "edit_published_{$singularPostTypeName}s",
			'create_posts'           => "create_{$singularPostTypeName}s",
		);
	}

	/**
	 * If current user does not have permission to read not owned employee post type, exclude them from the query.
	 * @param $query
	 *
	 * @return void
	 */
	public function excludePermissionDeniedEmployee( $query ) {

		$postType = $query->get( 'post_type' );
		if ( EmployeePostType::POST_TYPE != $postType || $this->hasEditOthersCapabilities( $postType ) ) {
			return;
		}

		remove_action( 'pre_get_posts', array( $this, 'excludePermissionDeniedEmployee' ), 10 );
		$currentUserEmployeeId = mpapp()->repositories()->employee()->findIdByUserId();
		add_action( 'pre_get_posts', array( $this, 'excludePermissionDeniedEmployee' ), 10, 1 );

		if ( ! empty( $currentUserEmployeeId ) ) {
			$query->set(
				'meta_query',
				array(
					array(
						'key'     => static::MPA_EMPLOYEE_META_KEY_WORDPRESS_USER,
						'value'   => get_current_user_id(),
						'compare' => '=',
					),
				)
			);
		}
	}

	/**
	 * If current user does not have permission to read not owned schedule post type, exclude them from the query.
	 * @param $query
	 *
	 * @return void
	 */
	public function excludePermissionDeniedSchedule( $query ) {

		$postType = $query->get( 'post_type' );
		if ( SchedulePostType::POST_TYPE != $postType || $this->hasEditOthersCapabilities( $postType ) ) {
			return;
		}

		$currentUserEmployeeId = mpapp()->repositories()->employee()->findIdByUserId();

		if ( ! empty( $currentUserEmployeeId ) ) {
			$query->set(
				'meta_query',
				array(
					array(
						'key'     => '_mpa_employee',
						'value'   => $currentUserEmployeeId,
						'compare' => '=',
					),
				)
			);
		}
	}

	/**
	 * If current user does not have permission to read not owned booking post type, exclude them from the query.
	 * @param $clauses string[]
	 * @param $query \WP_Query
	 *
	 * @return string[]
	 */
	public function excludePermissionDeniedBooking( $clauses, $query ) {
		global $wpdb;

		$postType = $query->get( 'post_type' );
		if ( BookingPostType::POST_TYPE != $postType || $this->hasEditOthersCapabilities( $postType ) ) {
			return $clauses;
		}

		$currentUserEmployeeId = mpapp()->repositories()->employee()->findIdByUserId();

		if ( ! empty( $currentUserEmployeeId ) ) {
			$clauses['where'] .= ' AND ' . $wpdb->posts . '.ID IN (SELECT DISTINCT p.post_parent FROM ' .
								$wpdb->posts . ' AS p RIGHT JOIN ' .
								$wpdb->postmeta . ' AS m ON p.ID = m.post_id
                WHERE p.post_type="' . ReservationPostType::POST_TYPE .
								'" AND m.meta_key="_mpa_employee" AND meta_value="' .
								$currentUserEmployeeId . '")';
		}

		return $clauses;
	}

	/**
	 * If current user does not have permission to read not owned payment post type, exclude them from the query.
	 * @param $clauses string[]
	 * @param $query \WP_Query
	 *
	 * @return string[]
	 */
	public function excludePermissionDeniedPayment( $clauses, $query ) {
		global $wpdb;

		$postType = $query->get( 'post_type' );
		if ( PaymentPostType::POST_TYPE != $postType || $this->hasEditOthersCapabilities( $postType ) ) {
			return $clauses;
		}

		$currentUserEmployeeId = mpapp()->repositories()->employee()->findIdByUserId();

		if ( ! empty( $currentUserEmployeeId ) ) {
			$clauses['where'] .= ' AND ' . $wpdb->posts . '.post_parent IN (SELECT DISTINCT p.post_parent FROM ' .
								$wpdb->posts . ' AS p RIGHT JOIN ' .
								$wpdb->postmeta . ' AS m ON p.ID = m.post_id
                WHERE p.post_type="' . ReservationPostType::POST_TYPE .
								'" AND m.meta_key="_mpa_employee" AND meta_value="' .
								$currentUserEmployeeId . '")';
		}

		return $clauses;
	}

	/**
	 * If current user does not have permission to read not owned reservation post type, exclude them from the query.
	 * @param $clauses string[]
	 * @param $query \WP_Query
	 *
	 * @return string[]
	 */
	public function excludePermissionDeniedReservation( $clauses, $query ) {
		global $wpdb;

		$postType = $query->get( 'post_type' );
		if ( ReservationPostType::POST_TYPE != $postType || $this->hasEditOthersCapabilities( $postType ) ) {
			return $clauses;
		}

		$currentUserEmployeeId = mpapp()->repositories()->employee()->findIdByUserId();

		if ( ! empty( $currentUserEmployeeId ) ) {
			$clauses['where'] .= ' AND ' . $wpdb->posts . '.ID IN (SELECT DISTINCT p.ID FROM ' .
								$wpdb->posts . ' AS p RIGHT JOIN ' .
								$wpdb->postmeta . ' AS m ON p.ID = m.post_id
                WHERE p.post_type="' . ReservationPostType::POST_TYPE .
								'" AND m.meta_key="_mpa_employee" AND meta_value="' .
								$currentUserEmployeeId . '")';
		}

		return $clauses;
	}

	private function hasEditOthersCapabilities( $type ) {
		switch ( $type ) {
			case BookingPostType::POST_TYPE:
				$capability = 'edit_others_mpa_bookings';
				break;
			case PaymentPostType::POST_TYPE:
				$capability = 'edit_others_mpa_payments';
				break;
			case EmployeePostType::POST_TYPE:
				$capability = 'edit_others_mpa_employees';
				break;
			case SchedulePostType::POST_TYPE:
				$capability = 'edit_others_mpa_schedules';
				break;
			default:
				//todo: is it correct?
				$capability = 'edit_others_pages';
		}

		remove_filter( 'user_has_cap', array( $this, 'applyEmployeeCapabilities' ), 10 );
		$hasEditOtherCapability = current_user_can( $capability );
		add_filter( 'user_has_cap', array( $this, 'applyEmployeeCapabilities' ), 10, 4 );

		return $hasEditOtherCapability;
	}

	/**
	 * Corrects post counts query for Booking, Payment, Employee or Schedule list
	 * if this list query was filtered because of current user is employee and
	 * has no right to see full list of these post types.
	 */
	public function updatePostCountForExcludedByPermissionDeniedPosts( $counts, $type, $perm ) {
		global $wpdb;

		if ( $this->hasEditOthersCapabilities( $type ) ) {
			return $counts;
		}

		$currentUserEmployeeId = mpapp()->repositories()->employee()->findIdByUserId();

		if ( empty( $currentUserEmployeeId ) ) {
			return $counts;
		}

		$query = "SELECT post_status, COUNT( * ) AS num_posts FROM {$wpdb->posts} WHERE post_type = %s";

		if ( 'readable' === $perm && is_user_logged_in() ) {
			$post_type_object = get_post_type_object( $type );
			if ( ! current_user_can( $post_type_object->cap->read_private_posts ) ) {
				$query .= $wpdb->prepare(
					" AND (post_status != 'private' OR ( post_author = %d AND post_status = 'private' ))",
					get_current_user_id()
				);
			}
		}

		switch ( $type ) {
			case BookingPostType::POST_TYPE:
				$query .= ' AND ' . $wpdb->posts . '.ID IN (SELECT DISTINCT p.post_parent FROM ' .
						$wpdb->posts . ' AS p RIGHT JOIN ' .
						$wpdb->postmeta . ' AS m ON p.ID = m.post_id
                        WHERE p.post_type="' . ReservationPostType::POST_TYPE .
						'" AND m.meta_key="_mpa_employee" AND meta_value="' .
						$currentUserEmployeeId . '")';
				break;
			case PaymentPostType::POST_TYPE:
				$query .= ' AND ' . $wpdb->posts . '.post_parent IN (SELECT DISTINCT p.post_parent FROM ' .
						$wpdb->posts . ' AS p RIGHT JOIN ' .
						$wpdb->postmeta . ' AS m ON p.ID = m.post_id
                    WHERE p.post_type="' . ReservationPostType::POST_TYPE .
						'" AND m.meta_key="_mpa_employee" AND meta_value="' .
						$currentUserEmployeeId . '")';
				break;
			case ReservationPostType::POST_TYPE:
				$query .= ' AND ' . $wpdb->posts . '.ID IN (SELECT DISTINCT p.ID FROM ' .
						$wpdb->posts . ' AS p RIGHT JOIN ' .
						$wpdb->postmeta . ' AS m ON p.ID = m.post_id
                    WHERE p.post_type="' . ReservationPostType::POST_TYPE .
						'" AND m.meta_key="_mpa_employee" AND meta_value="' .
						$currentUserEmployeeId . '")';
				break;
			case EmployeePostType::POST_TYPE:
				$query .= ' AND ' . $wpdb->posts . '.ID = ' . $currentUserEmployeeId;
				break;
			case SchedulePostType::POST_TYPE:
				$query .= ' AND ' . $wpdb->posts . '.ID IN (SELECT DISTINCT p.ID FROM ' .
						$wpdb->posts . ' AS p RIGHT JOIN ' .
						$wpdb->postmeta . ' AS m ON p.ID = m.post_id
                    WHERE p.post_type="' . SchedulePostType::POST_TYPE .
						'" AND m.meta_key="_mpa_employee" AND meta_value="' .
						$currentUserEmployeeId . '")';
				break;
		}

		$query .= ' GROUP BY post_status';

		$results = (array) $wpdb->get_results( $wpdb->prepare( $query, $type ), ARRAY_A );
		$counts  = array_fill_keys( get_post_stati(), 0 );

		foreach ( $results as $row ) {
			$counts[ $row['post_status'] ] = $row['num_posts'];
		}

		return (object) $counts;
	}


	public static function isUserCanAssignUserToEmployee( int $userId = 0 ): bool {
		if ( 0 >= $userId ) {
			$userId = get_current_user_id();
		}

		return user_can( $userId, static::CAPABILITY_ASSIGN_USER_TO_EMPLOYEE );
	}

	public static function isUserCanAssignEmployeeToSchedule( int $userId = 0 ): bool {
		if ( 0 >= $userId ) {
			$userId = get_current_user_id();
		}

		return user_can( $userId, static::CAPABILITY_ASSIGN_EMPLOYEE_TO_SCHEDULE );
	}

	public static function isUserCanViewOthersBookings( int $userId = 0 ): bool {

		if ( 0 >= $userId ) {
			$userId = get_current_user_id();
		}

		return user_can( $userId, 'read_mpa_bookings' );
	}


	public static function isUserCanEditOthersBookings( int $userId = 0 ): bool {
		if ( 0 >= $userId ) {
			$userId = get_current_user_id();
		}

		return user_can( $userId, 'edit_others_mpa_bookings' );
	}


	public static function isUserCanEditCustomer( int $userId = 0 ): bool {
		if ( 0 >= $userId ) {
			$userId = get_current_user_id();
		}

		return user_can( $userId, static::CAPABILITY_EDIT_CUSTOMER );
	}


	public static function isUserCanDeleteCustomer( int $userId = 0 ): bool {
		if ( 0 >= $userId ) {
			$userId = get_current_user_id();
		}

		return user_can( $userId, static::CAPABILITY_DELETE_CUSTOMER );
	}


	public static function isUserCanEditUsers( int $userId = 0 ): bool {
		if ( 0 >= $userId ) {
			$userId = get_current_user_id();
		}

		return user_can( $userId, 'edit_users' );
	}
}
