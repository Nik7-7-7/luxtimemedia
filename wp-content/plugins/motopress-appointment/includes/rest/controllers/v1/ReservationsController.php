<?php
/**
 * @package MotoPress\Appointment\Rest
 * @since 1.8.0
 */

namespace MotoPress\Appointment\Rest\Controllers\V1;

use MotoPress\Appointment\Rest\Controllers\AbstractRestObjectController;
use MotoPress\Appointment\Rest\Data\ReservationData;
use WP_REST_Request;

class ReservationsController extends AbstractRestObjectController {


	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'mpa/v1';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'reservations';

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = 'mpa_reservation';

	/**
	 * Prepare links for the request.
	 *
	 * @param  ReservationData  $reservationData  Reservation data object.
	 * @param  WP_REST_Request  $request  Request object.
	 *
	 * @return array Links for the given post.
	 */
	protected function prepare_links( $reservationData, $request ) {
		$links = parent::prepare_links( $reservationData, $request );

		$links['booking_id']  = array(
			'href'       => rest_url(
				sprintf(
					'/%s/%s/%d',
					$this->namespace,
					'bookings',
					$reservationData->entity->getBookingId()
				)
			),
			'embeddable' => true,
		);
		$links['service_id']  = array(
			'href'       => rest_url(
				sprintf(
					'/%s/%s/%d',
					$this->namespace,
					'services',
					$reservationData->entity->getServiceId()
				)
			),
			'embeddable' => true,
		);
		$links['employee_id'] = array(
			'href'       => rest_url(
				sprintf(
					'/%s/%s/%d',
					$this->namespace,
					'employees',
					$reservationData->entity->getEmployeeId()
				)
			),
			'embeddable' => true,
		);
		$links['location_id'] = array(
			'href'       => rest_url(
				sprintf(
					'/%s/%s/%d',
					$this->namespace,
					'locations',
					$reservationData->entity->getLocationId()
				)
			),
			'embeddable' => true,
		);

		return $links;
	}

	/**
	 * Get the query params for collections of attachments.
	 *
	 * @return array
	 */
	public function get_collection_params() {
		$params = parent::get_collection_params();

		$params['orderby']              = array(
			'description' => 'Order sort attribute ascending or descending.',
			'type'        => 'string',
			'enum'        => array( 'id', 'reservation_start_time', 'price' ),
			'default'     => 'reservation_start_time',
		);
		$params['uid']                  = array(
			'description'       => 'Limit result set to reservations assigned a specific UID.',
			'type'              => 'string',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['min_reservation_date'] = array(
			'description'       => 'Limit result set to reservations assigned a specific minimum reservation date as Y-m-d.',
			'type'              => 'string',
			'format'            => 'date',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['max_reservation_date'] = array(
			'description'       => 'Limit result set to reservations assigned a specific maximum reservation date as Y-m-d',
			'type'              => 'string',
			'format'            => 'date',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['booking_id']           = array(
			'description'       => 'Limit result set to reservations assigned a specific booking ID.',
			'type'              => 'array',
			'items'             => array(
				'type' => 'integer',
			),
			'sanitize_callback' => 'wp_parse_id_list',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['service_id']           = array(
			'description'       => 'Limit result set to reservations assigned a specific service ID.',
			'type'              => 'array',
			'items'             => array(
				'type' => 'integer',
			),
			'sanitize_callback' => 'wp_parse_id_list',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['employee_id']          = array(
			'description'       => 'Limit result set to reservations assigned a specific employee ID.',
			'type'              => 'array',
			'items'             => array(
				'type' => 'integer',
			),
			'sanitize_callback' => 'wp_parse_id_list',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['location_id']          = array(
			'description'       => 'Limit result set to reservations assigned a specific location ID.',
			'type'              => 'array',
			'items'             => array(
				'type' => 'integer',
			),
			'sanitize_callback' => 'wp_parse_id_list',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['min_price']            = array(
			'description'       => 'Limit result set to reservations based on a minimum price.',
			'type'              => 'number',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['max_price']            = array(
			'description'       => 'Limit result set to reservations based on a maximum price.',
			'type'              => 'number',
			'validate_callback' => 'rest_validate_request_arg',
		);

		return $params;
	}

	/**
	 * Prepare objects query.
	 *
	 * @param  WP_REST_Request  $request  Full details about the request.
	 *
	 * @return array
	 */
	public function prepareQuery( $request ) {
		$args = parent::prepareQuery( $request );

		if ( ! empty( $request['uid'] ) ) {
			$args['meta_query'] = $this->add_meta_query(
				$args,
				array(
					'key'   => '_mpa_uid',
					'value' => $request['uid'],
				)
			);
		}

		if ( ! empty( $request['min_reservation_date'] ) ) {
			$args['meta_query'] = $this->add_meta_query(
				$args,
				array(
					'key'     => '_mpa_date',
					'value'   => $request['min_reservation_date'],
					'compare' => '>=',
					'type'    => 'DATE',
				)
			);
		}
		if ( ! empty( $request['max_reservation_date'] ) ) {
			$args['meta_query'] = $this->add_meta_query(
				$args,
				array(
					'key'     => '_mpa_date',
					'value'   => $request['max_reservation_date'],
					'compare' => '<=',
					'type'    => 'DATE',
				)
			);
		}

		if ( ! empty( $request['booking_id'] ) ) {
			$args['post_parent__in'] = $request['booking_id'];
		}

		if ( ! empty( $request['service_id'] ) ) {
			$args['meta_query'] = $this->add_meta_query(
				$args,
				array(
					'key'   => '_mpa_service',
					'value' => $request['service_id'],
				)
			);
		}
		if ( ! empty( $request['employee_id'] ) ) {
			$args['meta_query'] = $this->add_meta_query(
				$args,
				array(
					'key'   => '_mpa_employee',
					'value' => $request['employee_id'],
				)
			);
		}
		if ( ! empty( $request['location_id'] ) ) {
			$args['meta_query'] = $this->add_meta_query(
				$args,
				array(
					'key'   => '_mpa_location',
					'value' => $request['location_id'],
				)
			);
		}
		if ( ! empty( $request['orderby'] ) ) {
			switch ( $request['orderby'] ) {
				case 'id':
					$args['orderby'] = $request['orderby'];
					break;
				case 'reservation_start_time':
					add_filter( 'posts_clauses', function ( $clauses, $wp_query ) {
						global $wpdb;
						if ( isset( $wp_query->query_vars['orderby'] ) && $wp_query->query['orderby'] == 'reservation_start_time' ) {
							$clauses['join']    .= " LEFT JOIN {$wpdb->postmeta} AS mt_date ON ({$wpdb->posts}.ID = mt_date.post_id AND mt_date.meta_key = '_mpa_date')";
							$clauses['join']    .= " LEFT JOIN {$wpdb->postmeta} AS mt_time ON ({$wpdb->posts}.ID = mt_time.post_id AND mt_time.meta_key = '_mpa_service_time')";
							$clauses['orderby'] = "CAST(mt_date.meta_value AS DATE) ASC, SUBSTRING_INDEX(mt_time.meta_value, ' - ', 1) ASC";
						}

						return $clauses;
					}, 10, 2 );

					break;
				case 'price':
					$args['meta_key'] = '_mpa_price';
					$args['orderby']  = 'meta_value_num';
					break;
			}
		}

		return $args;
	}
}
