<?php

use MotoPress\Appointment\Entities;
use MotoPress\Appointment\PostTypes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.5.0
 *
 * @param array $args Optional.
 *      @param bool $args['payment']    Whether to create a payment post also.
 *                                      By default: only if payments are enabled
 *                                      in the plugin settings.
 *      @param bool $args['wp_error']   Whether to return a WP_Error on failure.
 *                                      True by default.
 *      @param bool $args['clean']      Whether to delete a booking post if failed
 *                                      to create a payment post. True by default.
 * @return array|WP_Error
 */
function mpa_draft_booking( $args = array() ) {
	$args += array(
		'payment'  => mpapp()->settings()->isPaymentsEnabled(),
		'wp_error' => true,
		'clean'    => true,
	);

	$result = array(
		'booking_id'  => 0,
		'booking_uid' => '',
	);

	if ( $args['payment'] ) {
		$result += array(
			'payment_id'  => 0,
			'payment_uid' => '',
		);
	}

	// Create booking
	$bookingId = wp_insert_post(
		array(
			'post_type'   => PostTypes\BookingPostType::POST_TYPE,
			'post_status' => 'auto-draft',
		)
	);

	if ( is_wp_error( $bookingId ) ) {
		return $args['wp_error'] ? $bookingId : $result;
	}

	$result['booking_id']  = $bookingId;
	$result['booking_uid'] = mpa_generate_uuid4();

	mpa_add_post_uid( $bookingId, $result['booking_uid'] );

	// Create payment
	if ( $args['payment'] ) {
		$paymentId = wp_insert_post(
			array(
				'post_type'   => PostTypes\PaymentPostType::POST_TYPE,
				'post_status' => 'auto-draft',
				'post_parent' => $bookingId,
			)
		);

		if ( is_wp_error( $paymentId ) ) {
			if ( $args['clean'] ) {
				wp_delete_post( $bookingId, true );

				$result['booking_id']  = 0;
				$result['booking_uid'] = '';
			}

			return $args['wp_error'] ? $paymentId : $result;
		}

		$result['payment_id']  = $paymentId;
		$result['payment_uid'] = mpa_generate_uuid4();

		mpa_add_post_uid( $paymentId, $result['payment_uid'] );
	}

	return $result;
}

/**
 * @since 1.5.0
 *
 * @param Entities\Booking|Entities\Payment $entity
 * @param string $status
 * @param bool $wpError Optional. Whether to return a WP_Error on failure. False
 *      by default.
 * @return bool|WP_Error
 */
function mpa_update_status( $entity, $status, $wpError = false ) {
	$response = mpa_update_post_status( $entity->getId(), $status, $wpError );

	if ( is_int( $response ) && $response > 0 ) {
		$entity->setStatus( $status );
	}

	return $response;
}

/**
 * @param int $id Optional. Current post by default.
 * @param bool $forceReload Optional. False by default.
 * @return Entities\Booking|null
 *
 * @since 1.0
 * @since 1.2 $id is optional.
 */
function mpa_get_booking( $id = 0, $forceReload = false ) {
	return mpapp()->repositories()->booking()->findById( $id, $forceReload );
}

/**
 * @param array $args Optional.
 * @return array
 *
 * @see Repositories\AbstractRepository::findAll()
 *
 * @since 1.0
 */
function mpa_get_bookings( $args = array() ) {
	return mpapp()->repositories()->booking()->findAll( $args );
}

/**
 * @param int $bookingId
 * @param bool $forceReload Optional. False by default.
 * @return Entities\Reservation[]
 *
 * @since 1.0
 */
function mpa_get_reservations( $bookingId, $forceReload = false ) {
	$booking = mpa_get_booking( $bookingId, $forceReload );
	return ! is_null( $booking ) ? $booking->getReservations() : array();
}

/**
 * @since 1.5.0
 *
 * @param int $id Optional. Current post by default.
 * @param bool $forceReload Optional. False by default.
 * @return Entities\Payment|null
 */
function mpa_get_payment( $id = 0, $forceReload = false ) {
	return mpapp()->repositories()->payment()->findById( $id, $forceReload );
}

/**
 * @since 1.5.0
 * @see Repositories\AbstractRepository::findAll()
 *
 * @param array $args Optional.
 * @return array
 */
function mpa_get_payments( $args = array( 'fields' => array( 'id' => 'name' ) ) ) {
	return mpapp()->repositories()->payment()->findAll( $args );
}

/**
 * @param int $id Optional. Current post by default.
 * @param bool $forceReload Optional. False by default.
 * @return Entities\Employee|null
 *
 * @since 1.0
 * @since 1.2 $id is optional.
 */
function mpa_get_employee( $id = 0, $forceReload = false ) {
	return mpapp()->repositories()->employee()->findById( $id, $forceReload );
}

/**
 * @param array $args Optional.
 * @return array
 *
 * @see Repositories\AbstractRepository::findAll()
 *
 * @since 1.0
 */
function mpa_get_employees( $args = array( 'fields' => array( 'id' => 'name' ) ) ) {
	return mpapp()->repositories()->employee()->findAll( $args );
}

/**
 * @param int|int[] $location
 * @param array $args Optional.
 * @return array
 *
 * @since 1.2
 */
function mpa_get_employees_by_location( $location, $args = array( 'fields' => array( 'id' => 'name' ) ) ) {

	$keyLocation = mpa_prefix( 'main_location', 'private' );
	$keyEmployee = mpa_prefix( 'employee', 'private' );

	$schedules = mpapp()->repositories()->schedule()->findAllByMeta( $keyLocation, (array) $location, 'IN', array( 'fields' => 'ids' ) );
	$employees = array();

	// Get employees
	foreach ( $schedules as $scheduleId ) {
		$employeeId = get_post_meta( $scheduleId, $keyEmployee, true );

		if ( ! empty( $employeeId ) ) {
			$employees[] = intval( $employeeId );
		}
	}

	// Get all required fields
	if ( ! ( 1 === count( $args ) && isset( $args['fields'] ) && 'ids' === $args['fields'] ) ) {
		$args['post__in'] = $employees;

		$employees = mpapp()->repositories()->employee()->findAll( $args );
	}

	return $employees;
}

/**
 * @param int|Entities\Employee $employee
 * @param array $args Optional.
 *     @param bool $args['show_contacts'] True by default.
 *     @param bool $args['show_social_networks'] True by default.
 *     @param bool $args['show_additional_info'] True by default.
 * @return array
 *
 * @since 1.2
 */
function mpa_get_employee_attributes( $employee, $args = array() ) {
	if ( ! is_object( $employee ) && ! is_null( $employee ) ) {
		$employee = mpa_get_employee( $employee );
	}

	if ( is_null( $employee ) ) {
		return array();
	}

	$args += array(
		'show_contacts'        => true,
		'show_social_networks' => true,
		'show_additional_info' => true,
	);

	$attributes = array();

	if ( $args['show_contacts'] ) {
		$attributes = array_merge( $attributes, $employee->getContacts() );
	}

	if ( $args['show_social_networks'] ) {
		$attributes = array_merge( $attributes, $employee->getSocialNetworks() );
	}

	if ( $args['show_additional_info'] ) {
		$attributes = array_merge( $attributes, $employee->getAdditionalInfo() );
	}

	/**
	 * @param array
	 * @param Entities\Employee
	 * @param array
	 *
	 * @since 1.2
	 */
	$attributes = apply_filters( 'mpa_get_employee_attributes', $attributes, $employee, $args );

	return $attributes;
}

/**
 * @param int $id Optional. Current post by default.
 * @param bool $forceReload Optional. False by default.
 * @return Entities\Location|null
 *
 * @since 1.0
 * @since 1.2 $id is optional.
 */
function mpa_get_location( $id = 0, $forceReload = false ) {
	return mpapp()->repositories()->location()->findById( $id, $forceReload );
}

/**
 * @param array $args Optional.
 * @return array
 *
 * @see Repositories\AbstractRepository::findAll()
 *
 * @since 1.0
 */
function mpa_get_locations( $args = array( 'fields' => array( 'id' => 'name' ) ) ) {
	return mpapp()->repositories()->location()->findAll( $args );
}

/**
 * @param int $locationId Optional. 0 by default.
 * @param string|array $fields Optional. 'all', field name or key-value pair.
 *     ['slug' => 'name'] by default.
 * @param array $args Optional.
 * @return WP_Term[]|array
 *
 * @since 1.1.0
 */
function mpa_get_location_categories( $locationId = 0, $fields = array( 'slug' => 'name' ), $args = array() ) {
	$taxonomy = MotoPress\Appointment\PostTypes\LocationPostType::CATEGORY_NAME;

	return mpa_get_terms( $locationId, $taxonomy, $fields, $args );
}

/**
 * @param int $id Optional. Current post by default.
 * @param bool $forceReload Optional. False by default.
 * @return Entities\Schedule|null
 *
 * @since 1.0
 * @since 1.2 $id is optional.
 */
function mpa_get_schedule( $id = 0, $forceReload = false ) {
	return mpapp()->repositories()->schedule()->findById( $id, $forceReload );
}

/**
 * @param array $args Optional.
 * @return array
 *
 * @see Repositories\AbstractRepository::findAll()
 *
 * @since 1.0
 */
function mpa_get_schedules( $args = array( 'fields' => array( 'id' => 'title' ) ) ) {
	return mpapp()->repositories()->schedule()->findAll( $args );
}

/**
 * @param int $id Optional. Current post by default.
 * @param bool $forceReload Optional. False by default.
 * @return Entities\Service|null
 *
 * @since 1.0
 * @since 1.2 $id is optional.
 */
function mpa_get_service( $id = 0, $forceReload = false ) {
	return mpapp()->repositories()->service()->findById( $id, $forceReload );
}

/**
 * @param array $args Optional.
 * @return array
 *
 * @see Repositories\AbstractRepository::findAll()
 *
 * @since 1.0
 */
function mpa_get_services( $args = array( 'fields' => array( 'id' => 'title' ) ) ) {
	return mpapp()->repositories()->service()->findAll( $args );
}

/**
 * @param int|Entities\Service $service
 * @param array $args Optional.
 *     @param bool $args['show_price'] True by default.
 *     @param bool $args['show_duration'] True by default.
 * @return array Array for template post/attributes.php.
 *
 * @since 1.2
 */
function mpa_get_service_attributes( $service, $args = array() ) {
	if ( ! is_object( $service ) && ! is_null( $service ) ) {
		$service = mpa_get_service( $service );
	}

	if ( is_null( $service ) ) {
		return array();
	}

	$args += array(
		'show_price'    => true,
		'show_duration' => true,
		'show_capacity' => true,
	);

	$attributes = array();

	if ( $args['show_price'] ) {
		$attributes[] = array(
			'label'   => esc_html__( 'Price', 'motopress-appointment' ),
			'content' => mpa_tmpl_price( $service->getPrice() ),
		);
	}

	if ( $args['show_duration'] ) {
		$attributes[] = array(
			'label'   => esc_html__( 'Duration', 'motopress-appointment' ),
			'content' => mpa_minutes_to_duration( $service->getDuration() ),
		);
	}

	if ( $args['show_capacity'] ) {
		$attributes[] = array(
			'label'   => esc_html__( 'Capacity', 'motopress-appointment' ),
			'content' => mpa_tmpl_service_capacity( $service ),
		);
	}

	/**
	 * @param array
	 * @param Entities\Service
	 *
	 * @since 1.2
	 */
	$attributes = apply_filters( 'mpa_get_service_attributes', $attributes, $service );

	return $attributes;
}

/**
 * @param int $serviceId Optional. 0 by default.
 * @param string|array $fields Optional. 'all', field name or key-value pair.
 *     ['slug' => 'name'] by default.
 * @param array $args Optional.
 * @return WP_Term[]|array
 *
 * @since 1.0
 */
function mpa_get_service_categories( $serviceId = 0, $fields = array( 'slug' => 'name' ), $args = array() ) {
	$taxonomy = MotoPress\Appointment\PostTypes\ServicePostType::CATEGORY_NAME;

	return mpa_get_terms( $serviceId, $taxonomy, $fields, $args );
}

/**
 * @param WP_Term|int $term
 * @return int The total number of [unique] posts, including the posts of the
 *     child categories.
 *
 * @since 1.2
 */
function mpa_get_service_category_total_count( $term ) {
	if ( is_object( $term ) ) {
		$termId = $term->term_id;
	} else {
		$termId = (int) $term;
	}

	// Get child IDs
	$categories = mpa_get_service_categories(
		0,
		'term_id',
		array(
			'hide_empty' => true,
			'child_of'   => $termId,
		)
	);

	// Add parent ID
	$categories[] = $termId;

	// Get posts
	$posts = mpa_get_services(
		array(
			'fields'    => 'ids',
			'tax_query' => array(
				array(
					'taxonomy' => mpa_service()->getCategory(),
					'terms'    => $categories,
					'operator' => 'IN',
				),
			),
		)
	);

	return count( $posts );
}

/**
 * Extracts the minimum information needed for booking/appointment shortcodes.
 * For example:
 * <pre>
 *     $bookableServices = [
 *         134 => [
 *             'name'       => "Man's Haircut",
 *             'categories' => [
 *                 'barbershop' => 'Barbershop',
 *                 'haircuts'   => 'Haircuts'
 *             ],
 *             'employees'  => [
 *                 125 => [
 *                     'name'      => 'John Doe',
 *                     'schedule'  => 131,
 *                     'locations' => [
 *                         129 => 'Barbershop'
 *                     ]
 *                 ]
 *             ]
 *         ],
 *         135 => [
 *             'name'       => "Woman's Haircut",
 *             'categories' => [
 *                 'uncategorized' => 'Uncategorized'
 *             ],
 *             'employees'  => [
 *                 127 => [
 *                     'name'      => 'Mary Doe',
 *                     'schedule'  => 133,
 *                     'locations' => [
 *                         130 => 'Hairdressing Salon'
 *                     ]
 *                 ]
 *             ]
 *         ]
 *     ];
 * </pre>
 *
 * @return array
 *
 * @since 1.0
 */
function mpa_extract_available_services() {
	// Filter services without employees; or services, which employees doesn't
	// have a schedule or working locations
	$bookableServices = array();

	$servicesArgs = array(
		'post_status' => array(
			'publish',
		),
		'fields'      => 'all',
	);
	$services     = mpa_get_services( $servicesArgs ); // returns Service[]

	if ( ! count( $services ) ) {
		return $bookableServices;
	}

	$schedulesArgs = array(
		'post_status' => array(
			'publish',
		),
		'fields'      => 'all',
	);
	$schedules     = mpa_get_schedules( $schedulesArgs );

	if ( ! count( $schedules ) ) {
		return $bookableServices;
	}

	$schedulesByEmployeeId = array_reduce( $schedules, function ( $carry, $schedule ) {
		$employeeId = $schedule->getEmployeeId();

		if ( ! $employeeId ) {
			return $carry;
		}

		$carry[ $employeeId ] = $schedule;

		return $carry;
	}, [] );

	$employeesArgs = array(
		'post_status' => array(
			'publish',
		),
		'fields'      => 'all',
	);
	$employees     = mpa_get_employees( $employeesArgs );

	if ( ! count( $employees ) ) {
		return $bookableServices;
	}

	$employeesById = array_reduce( $employees, function ( $carry, $employee ) {
		$employeeId = $employee->getId();

		$carry[ $employeeId ] = $employee;

		return $carry;
	}, [] );

	$locationsArgs = array(
		'post_status' => array(
			'publish',
		),
		'fields'      => 'all',
	);
	$locations     = mpa_get_locations( $locationsArgs );

	if ( ! count( $locations ) ) {
		return $bookableServices;
	}

	$locationsById = array_reduce( $locations, function ( $carry, $location ) {
		$locationId = $location->getId();

		$carry[ $locationId ] = $location;

		return $carry;
	}, [] );

	foreach ( $services as $service ) {
		$serviceEmployees = array();

		$serviceId = $service->getId();

		$serviceEmployeeIds = $service->getEmployeeIds();

		if ( ! count( $serviceEmployeeIds ) ) {
			continue;
		}

		foreach ( $serviceEmployeeIds as $employeeId ) {
			if ( ! array_key_exists( $employeeId, $employeesById ) ) {
				continue;
			}

			$employee = $employeesById[ $employeeId ];

			if ( ! array_key_exists( $employeeId, $schedulesByEmployeeId ) ) {
				continue;
			}

			$schedule          = $schedulesByEmployeeId[ $employeeId ];
			$scheduleTimetable = $schedule->getTimetable();

			$employeeLocations = array_reduce( $scheduleTimetable, function ( $carry, $day ) use ( $locationsById ) {
				$dayLocations = wp_list_pluck( $day, 'location' );
				foreach ( $dayLocations as $locationId ) {
					if ( array_key_exists( $locationId, $carry ) ) {
						continue;
					}

					if ( ! array_key_exists( $locationId, $locationsById ) ) {
						continue;
					}

					$location             = $locationsById[ $locationId ];
					$carry[ $locationId ] = $location->getName();
				}

				return $carry;
			}, [] );

			// add main Schedule location to the list if Schedule has custom working days
			if ( ! empty( $schedule->getCustomWorkdays() ) &&
				array_key_exists( $schedule->getLocationId(), $locationsById ) &&
				! isset( $employeeLocations[ $schedule->getLocationId() ] )
			) {
				$location                                        = $locationsById[ $schedule->getLocationId() ];
				$employeeLocations[ $schedule->getLocationId() ] = $location->getName();
			}

			if ( ! count( $employeeLocations ) ) {
				continue;
			}

			$serviceEmployees[ $employeeId ] = array(
				'name'      => $employee->getName(),
				'schedule'  => $schedule->getId(),
				'locations' => $employeeLocations,
			);
		}

		if ( ! count( $serviceEmployees ) ) {
			continue;
		}

		$categories = mpa_get_service_categories( $service->getId() );

		if ( ! empty( $categories ) ) {
			ksort( $categories );
		} else {
			$categories['uncategorized'] = esc_html__( 'Uncategorized' ); // WordPress core text
		}

		$bookableServices[ $serviceId ] = array(
			'name'       => $service->getTitle(),
			'categories' => $categories,
			'employees'  => $serviceEmployees,
		);
	}

	return $bookableServices;
}

/**
 * @return Entities\AbstractEntity|null
 *
 * @since 1.2
 */
function mpa_get_current_entity() {
	return mpa_get_entity();
}

/**
 * @param int $postId Optional. Current post by default.
 * @return Entities\AbstractEntity|null
 *
 * @since 1.2
 */
function mpa_get_entity( $postId = 0 ) {

	if ( 0 == $postId ) {
		$postId = get_the_ID();
	}

	$postType = get_post_type( $postId );

	$repository = mpapp()->repositories()->getByPostType( $postType );

	if ( ! is_null( $repository ) ) {
		return $repository->findById( $postId );
	} else {
		return null;
	}
}

/**
 * @since 2.0.0
 *
 * @param Entities\AbstractEntity[] $entities
 * @return int[]
 */
function mpa_entities_to_ids( array $entities ): array {
	return array_map(
		function ( Entities\AbstractEntity $entity ) {
			return $entity->getId();
		},
		$entities
	);
}
