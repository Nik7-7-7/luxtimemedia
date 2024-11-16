<?php

namespace MotoPress\Appointment\REST\Controllers\Motopress\Appointment\V1;

use MotoPress\Appointment\Entities\Service;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class ServicesRestController extends AbstractRestController {

	/**
	 * @since 1.0
	 */
	public function register_routes() {
		// '/motopress/appointment/v1/services'
		register_rest_route(
			$this->getNamespace(),
			'/services',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'getServices' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'id' => array(
						// Possible values: ID (int), IDs (comma-separated string), IDs (array)
					),
				),
			)
		);

		// '/motopress/appointment/v1/services/available'
		register_rest_route(
			$this->getNamespace(),
			'/services/available',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'getAvailableServices' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 *
	 * @since 1.0
	 * @since 1.9.0 accepts multiple IDs.
	 */
	public function getServices( $request ) {

		$params = $request->get_params();

		if ( ! isset( $params['id'] ) ) {
			// "Get all services" is not supported in the current version
			return mpa_rest_request_error( esc_html__( 'Invalid parameter: service ID is not set.', 'motopress-appointment' ) );
		}

		$serviceIds = mpa_rest_sanitize_ids( $params['id'] );

		if ( empty( $serviceIds ) ) {
			return mpa_rest_request_error( esc_html__( 'Invalid parameter: service ID is not valid.', 'motopress-appointment' ) );
		}

		if ( is_array( $params['id'] ) || count( $serviceIds ) > 1 ) { // "1,2,3" → [1, 2, 3], see mpa_rest_sanitize_ids()
			return $this->getMultipleServices( $serviceIds );
		} else {
			$serviceId = reset( $serviceIds );
			return $this->getSingleService( $serviceId );
		}
	}

	/**
	 * @param int $serviceId
	 * @return WP_REST_Response|WP_Error
	 */
	protected function getSingleService( $serviceId ) {
		$service = mpa_get_service( $serviceId );

		if ( ! is_null( $service ) ) {
			return rest_ensure_response( $this->mapEntity( $service ) );
		} else {
			return mpa_rest_request_error(
				sprintf(
					// Translators: %d: Service ID.
					esc_html__( 'Invalid request: services not found.', 'motopress-appointment' ),
					$serviceId
				)
			);
		}
	}

	/**
	 * @param int[] $serviceIds
	 * @return WP_REST_Response|WP_Error
	 */
	protected function getMultipleServices( $serviceIds ) {
		$services = mpa_get_services(
			array(
				'post__in' => $serviceIds,
				'fields'   => 'all',
			)
		);

		if ( ! empty( $services ) ) {
			$entities = array_map( array( $this, 'mapEntity' ), $services );
			return rest_ensure_response( $entities );
		} else {
			return mpa_rest_request_error( esc_html__( 'Invalid request: services not found.', 'motopress-appointment' ) );
		}
	}

	/**
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 *
	 * @since 1.0
	 */
	public function getAvailableServices( $request ) {
		return rest_ensure_response( mpa_extract_available_services() );
	}

	/**
	 * @param Service $entity
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function mapEntity( $entity ) {

		$entityData = array(
			'id'                              => $entity->getId(),
			'name'                            => $entity->getTitle(),
			'description'                     => $entity->getDescription(),
			// send price just for 1 person as it set in service
			// because frontend cart and cart item calculate all prices from the ground
			'price'                           => $entity->getPrice( 0, 1 ),
			'depositType'                     => $entity->getDepositType(),
			'depositAmount'                   => $entity->getDepositAmount(),
			'duration'                        => $entity->getDuration(),
			'bufferTimeBefore'                => $entity->getBufferTimeBefore(),
			'bufferTimeAfter'                 => $entity->getBufferTimeAfter(),
			'timeBeforeBooking'               => $entity->getTimeBeforeBooking() ? $entity->getTimeBeforeBooking()->format( 'P%yY%mM%dDT%hH%iM' ) : '',
			'maxAdvanceTimeBeforeReservation' => $entity->getMaxAdvanceTimeBeforeReservation() ? $entity->getMaxAdvanceTimeBeforeReservation()->format( 'P%yY%mM%dDT%hH%iM' ) : '',
			'isGroupServiceEnabled'           => $entity->isGroupService(),
			'minCapacity'                     => $entity->getMinCapacity(),
			'maxCapacity'                     => $entity->getMaxCapacity(),
			'multiplyPrice'                   => $entity->isMultiplyPrice(),
			'customQuantityLabel'             => $entity->getCustomQuantityLabel(),
			'employeeIds'                     => $entity->getEmployeeIds(),
			'variations'                      => $entity->getVariations()->toArray( 'deep' ),
			'image'                           => mpa_get_post_attachment_image_url( $entity->getId(), 'post-thumbnail' ),
			'thumbnail'                       => mpa_get_post_attachment_image_url( $entity->getId() ),
		);

		return $entityData;
	}
}
