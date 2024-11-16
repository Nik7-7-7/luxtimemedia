<?php

namespace MotoPress\Appointment\Repositories;

use MotoPress\Appointment\Entities\Reservation;
use MotoPress\Appointment\PostTypes\Statuses\BookingStatuses;
use MotoPress\Appointment\PostTypes\ReservationPostType;
use MotoPress\Appointment\Structures\TimePeriod;
use MotoPress\Appointment\Utils\ParseUtils;
use MotoPress\Appointment\Handlers\NotificationHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 *
 * @see Reservation
 */
class ReservationRepository extends AbstractRepository {

	/**
	 * @since 1.0
	 */
	protected function addActions() {
		parent::addActions();

		add_filter( "{$this->postType}_repository_get_posts_query_args", array( $this, 'filterArgs' ), 5 );
	}

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function entitySchema() {

		return array(
			'post'     => array( 'ID', 'post_parent' ),
			'postmeta' => array(
				'_mpa_uid'            => true,
				'_mpa_price'          => true,
				'_mpa_discount'       => true,
				'_mpa_total_price'    => true,
				'_mpa_deposit_amount' => true,
				'_mpa_date'           => true,
				'_mpa_service_time'   => true,
				'_mpa_buffer_time'    => true,
				'_mpa_service'        => true,
				'_mpa_employee'       => true,
				'_mpa_location'       => true,
				'_mpa_capacity'       => true,
				NotificationHandler::META_KEY_NOTIFICATION_SENT => false,
			),
		);
	}

	/**
	 * @param array $postData
	 * @return Reservation
	 *
	 * @since 1.0
	 */
	protected function mapPostDataToEntity( $postData ) {

		$id = (int) $postData['ID'];

		$date        = mpa_parse_date( $postData['date'] );
		$serviceTime = new TimePeriod( $postData['service_time'] );
		$bufferTime  = new TimePeriod( $postData['buffer_time'] );

		$serviceTime->setDate( $date );
		$bufferTime->setDate( $date );

		$sentNotificationIds = $postData[ mpa_unprefix( NotificationHandler::META_KEY_NOTIFICATION_SENT ) ];

		$fields = array(
			'uid'                 => $postData['uid'],
			'bookingId'           => (int) $postData['post_parent'],
			'price'               => (float) $postData['price'],
			'discount'            => (float) $postData['discount'],
			'totalPrice'          => (float) $postData['total_price'],
			'depositAmount'       => (float) $postData['deposit_amount'],
			'date'                => $date,
			'serviceTime'         => $serviceTime,
			'bufferTime'          => $bufferTime,
			'serviceId'           => (int) $postData['service'],
			'employeeId'          => (int) $postData['employee'],
			'locationId'          => (int) $postData['location'],
			'capacity'            => (int) $postData['capacity'],
			'sentNotificationIds' => ParseUtils::parseIds( $sentNotificationIds ),
		);

		return new Reservation( $id, $fields );
	}

	/**
	 * Create new or update reservation post in database.
	 */
	public function saveReservation( Reservation $reservation ) {

		$postId = $reservation->getId();

		if ( ! $reservation->getId() ) {

			$postId = wp_insert_post(
				array(
					'post_type'   => ReservationPostType::POST_TYPE,
					'post_status' => 'publish',
					'post_parent' => $reservation->getBookingId(),
				)
			);

			if ( is_wp_error( $postId ) || 0 === $postId ) {
				return;
			} else {
				$reservation->setId( $postId );
			}
		}

		// update reservation title and name
		$wpPost = get_post( $postId );

		if ( ! empty( $wpPost ) &&
		     ( empty( $wpPost->post_title ) || empty( $wpPost->post_name ) )
		) {
			wp_update_post(
				array(
					'ID'         => $postId,
					// Translators: %d: Reservation ID.
					'post_title' => sprintf( esc_html__( 'Reservation #%d', 'motopress-appointment' ), $postId ),
					'post_name'  => "mpa-booking-{$postId}",
				)
			);
		}

		update_post_meta( $postId, '_mpa_uid', $reservation->getUid() );
		update_post_meta( $postId, '_mpa_price', $reservation->getPrice() );
		update_post_meta( $postId, '_mpa_discount', $reservation->getDiscount() );
		update_post_meta( $postId, '_mpa_total_price', $reservation->getTotalPrice() );
		update_post_meta( $postId, '_mpa_deposit_amount', $reservation->getDepositAmount() );
		update_post_meta( $postId, '_mpa_date', mpa_format_date( $reservation->getDate(), 'internal' ) );
		update_post_meta( $postId, '_mpa_service_time', $reservation->getServiceTime()->toString( 'internal' ) );
		update_post_meta( $postId, '_mpa_buffer_time', $reservation->getBufferTime()->toString( 'internal' ) );
		update_post_meta( $postId, '_mpa_service', $reservation->getServiceId() );
		update_post_meta( $postId, '_mpa_employee', $reservation->getEmployeeId() );
		update_post_meta( $postId, '_mpa_location', $reservation->getLocationId() );
		update_post_meta( $postId, '_mpa_capacity', $reservation->getCapacity() );
	}

	/**
	 * @param int $bookingId
	 * @return Reservation[]
	 *
	 * @since 1.0
	 */
	public function findAllByBooking( $bookingId ) {
		return $this->findAll( array( 'post_parent' => $bookingId ) );
	}

	/**
	 * @param int $serviceId
	 * @param array $args Optional.
	 *     @param \DateTime|string $args['from_date']
	 *     @param \DateTime|string $args['to_date']
	 *     @param string|array     $args['fields']
	 * @return array
	 *
	 * @since 1.0
	 */
	public function findAllByService( $serviceId, $args = array() ) {
		$args['service_id'] = $serviceId;

		return $this->findAll( $args );
	}

	/**
	 * @since 2.0.0
	 *
	 * @return Reservation[]|int[]
	 */
	public function findAllBlocking( $args = [] ): array {
		$joinFilter = function ( string $join ): string {
			global $wpdb;

			$join .= " INNER JOIN `{$wpdb->posts}` AS `mpa_bookings` ON `mpa_bookings`.`ID` = `{$wpdb->posts}`.`post_parent`";

			return $join;
		};

		$whereFilter = function ( string $where ): string {
			$blockingStatuses = array_keys(
				mpapp()->postTypes()->booking()->statuses()->getBlockedTimeSlotsStatuses()
			);

			$blockingStatusesString = '"' . implode( '", "', $blockingStatuses ) . '"';

			$where .= ' AND `mpa_bookings`.`post_status` IN (' . $blockingStatusesString . ')';

			return $where;
		};

		add_filter( 'posts_join', $joinFilter );
		add_filter( 'posts_where', $whereFilter );

		$reservations = $this->findAll( $args );

		remove_filter( 'posts_join', $joinFilter );
		remove_filter( 'posts_where', $whereFilter );

		return $reservations;
	}

	/**
	 * @since 1.13.0
	 *
	 * @return Reservation|null
	 */
	public function findRandomConfirmed() {

		global $wpdb;

		$query = $wpdb->prepare(
			"SELECT reservations.ID FROM {$wpdb->posts} AS reservations"
				. " INNER JOIN {$wpdb->posts} AS bookings ON bookings.ID = reservations.post_parent"
				. ' WHERE reservations.post_type = %s AND bookings.post_status = %s'
				. ' ORDER BY RAND() LIMIT 1',
			ReservationPostType::POST_TYPE,
			BookingStatuses::STATUS_CONFIRMED
		);

		// phpcs:ignore
		$reservationId = $wpdb->get_var( $query );
		$reservation   = ! is_null( $reservationId ) ? $this->findById( (int) $reservationId ) : null;

		return $reservation;
	}

	/**
	 * @param array $args
	 * @return array
	 *
	 * @access protected
	 *
	 * @since 1.0
	 */
	public function filterArgs( $args ) {
		// Exclude booking ID(s)
		if ( isset( $args['booking_not_in' ] ) ) {
			if ( ( is_array( $args['booking_not_in' ] ) && ! empty( $args['booking_not_in' ] ) )
				|| ( ! is_array( $args['booking_not_in' ] ) && $args['booking_not_in' ] > 0 )
			) {
				$args['post_parent__not_in'] = (array) $args['booking_not_in'];
			}

			unset( $args['booking_not_in'] );
		}

		// Add meta query
		$metaQuery = array();

		// Service ID, employee ID, location ID
		$idFields = array(
			'_mpa_service'  => 'service_id',
			'_mpa_employee' => 'employee_id',
			'_mpa_location' => 'location_id',
		);

		foreach ( $idFields as $postmeta => $name ) {
			if ( isset( $args[ $name ] ) ) {
				$value = $args[ $name ];

				$metaQuery[] = array(
					'key'     => $postmeta,
					'value'   => $value,
					'compare' => is_array( $value ) ? 'IN' : '=',
				);

				unset( $args[ $name ] );
			}
		}

		// Start date
		if ( isset( $args['from_date'] ) ) {
			$fromDate = is_string( $args['from_date'] ) ? $args['from_date'] : mpa_format_date( $args['from_date'], 'internal' );

			$metaQuery[] = array(
				'key'     => '_mpa_date',
				'value'   => $fromDate,
				'compare' => '>=',
			);

			unset( $args['from_date'] );
		}

		// End date
		if ( isset( $args['to_date'] ) ) {
			$toDate = is_string( $args['to_date'] ) ? $args['to_date'] : mpa_format_date( $args['to_date'], 'internal' );

			$metaQuery[] = array(
				'key'     => '_mpa_date',
				'value'   => $toDate,
				'compare' => '<=',
			);

			unset( $args['to_date'] );
		}

		// Merge meta query
		if ( ! empty( $metaQuery ) ) {

			if ( ! isset( $args['meta_query'] ) ) {

				// phpcs:ignore
				$args['meta_query'] = $metaQuery;

			} else {

				$args['meta_query'][] = $metaQuery;
			}
		}

		return $args;
	}
}
