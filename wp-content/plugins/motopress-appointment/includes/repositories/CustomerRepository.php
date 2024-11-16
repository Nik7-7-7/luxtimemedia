<?php

namespace MotoPress\Appointment\Repositories;

use MotoPress\Appointment\Entities\Customer;
use MotoPress\Appointment\Entities\Booking;
use MotoPress\Appointment\Handlers\SecurityHandler;

/**
 * @since 1.18.0
 */
class CustomerRepository {

	/**
	 * @param array $customerData
	 * $customerData['id']
	 * $customerData['user_id']
	 * $customerData['name']
	 * $customerData['phone']
	 * $customerData['date_registered']
	 * $customerData['last_active']
	 *
	 * @return Customer
	 * @throws \Exception
	 */
	public function mapDataToEntity( $customerData ): Customer {

		$customer = new Customer();

		foreach ( $customerData as $key => $value ) {
			switch ( $key ) {
				case 'id':
					$customer->setId( (int) $value );
					break;
				case 'user_id':
					$customer->setUserId( (int) $value );
					break;
				case 'email':
					$customer->setEmail( (string) $value );
					break;
				case 'name':
					$customer->setName( (string) $value );
					break;
				case 'phone':
					$customer->setPhone( (string) $value );
					break;
				case 'date_registered':
					$dateRegistered = new \DateTime( $value );
					$customer->setDateRegistered( $dateRegistered );
					break;
				case 'last_active':
					if ( $value ) {
						$lastActive = new \DateTime( $value );
						$customer->setLastActive( $lastActive );
					}
					break;
			}
		}

		return $customer;
	}

	/**
	 * @throws \Exception
	 */
	public function saveBookingCustomer( Booking $booking ) {

		$bookingId = $booking->getId();

		if ( ! $bookingId ) {
			return;
		}

		$customerId    = $booking->getCustomerId();
		$customerName  = $booking->getCustomerName();
		$customerPhone = $booking->getCustomerPhone();
		$customerEmail = $booking->getCustomerEmail();

		$customer = null;

		if ( $customerId ) {
			$customer = $this->findById( $customerId );
		}

		if ( is_null( $customer ) ) {
			$customer = $this->findByEmail( $customerEmail );
		}

		if ( is_null( $customer ) ) {
			$customer = $this->findByPhone( $customerPhone );
		}

		if ( is_null( $customer ) ) {

			$customerData = array(
				'name'  => $customerName,
				'email' => $customerEmail,
				'phone' => $customerPhone,
			);

			$customer = $this->mapDataToEntity( $customerData );

			if ( empty($customer->getEmail()) && empty($customer->getPhone()) ) {

				if ( !empty( $customer->getName() ) ) {
					update_post_meta( $bookingId, '_mpa_customer_name', $customerName );
				}

				return;
			}

			$this->save( $customer );
		}

		update_post_meta( $bookingId, '_mpa_customer_id', $customer->getId() );

		if ( $customer->getName() != $customerName ) {
			update_post_meta( $bookingId, '_mpa_customer_name', $customerName );
		} else {
			delete_post_meta( $bookingId, '_mpa_customer_name' );
		}

		if ( $customer->getEmail() != $customerEmail ) {
			update_post_meta( $bookingId, '_mpa_customer_email', $customerEmail );
		} else {
			delete_post_meta( $bookingId, '_mpa_customer_email' );
		}

		if ( $customer->getPhone() != $customerPhone ) {
			update_post_meta( $bookingId, '_mpa_customer_phone', $customerPhone );
		} else {
			delete_post_meta( $bookingId, '_mpa_customer_phone' );
		}
	}

	/**
	 * @param string $columnName
	 * @param string $value
	 *
	 * @return Customer|null
	 */
	protected function findByColumnValue( string $columnName, string $value ) {

		global $wpdb;

		$table      = $wpdb->prefix . 'mpa_customers';
		$columnName = esc_sql( $columnName );

		$preparedQuery = $wpdb->prepare( "SELECT * FROM $table WHERE $columnName = %s", $value );
		$customerData  = $wpdb->get_row( $preparedQuery, ARRAY_A );

		if ( ! $customerData ) {
			return null;
		}

		return $this->mapDataToEntity( $customerData );
	}

	/**
	 *
	 * @param int $id
	 *
	 * @return Customer|null
	 */
	public function findById( int $id ) {
		if ( 0 === $id ) {
			return null;
		}

		return $this->findByColumnValue( 'id', (string) $id );
	}

	/**
	 *
	 * @param int $id
	 *
	 * @return Customer|null
	 */
	public function findByUserId( int $id ) {
		if ( 0 === $id ) {
			return null;
		}

		return $this->findByColumnValue( 'user_id', (string) $id );
	}

	/**
	 *
	 * @param string $email
	 *
	 * @return Customer|null
	 */
	public function findByEmail( string $email ) {
		if ( ! $email ) {
			return null;
		}

		return $this->findByColumnValue( 'email', $email );
	}

	/**
	 *
	 * @param string $phone
	 *
	 * @return Customer|null
	 */
	public function findByPhone( string $phone ) {
		if ( ! $phone ) {
			return null;
		}

		return $this->findByColumnValue( 'phone', $phone );
	}

	/**
	 * @param $atts
	 * $atts['order'] = ASC|DESC
	 * $atts['orderby'] = id|user_id|email|name|phone|date_registered|last_active|bookings
	 *
	 * @return Customer[]
	 */
	public function findAll( $atts = array() ) {
		global $wpdb;

		$table = $wpdb->prefix . 'mpa_customers';

		$order = isset( $atts['order'] ) ? $atts['order'] : 'DESC';

		$orderby = '';
		$where   = '';

		if ( isset( $atts['s'] ) && ( $atts['s'] !== '' ) ) {
			$searchAtts = '%' . $wpdb->esc_like( $atts['s'] ) . '%';
			$where      = $wpdb->prepare(
				"WHERE $table.name LIKE %s OR
				$table.email LIKE %s OR
				$table.phone LIKE %s",
				$searchAtts,
				$searchAtts,
				$searchAtts
			);
		}

		$select = "SELECT * FROM $table $where";

		if ( isset( $atts['orderby'] ) ) {
			if ( $atts['orderby'] == 'bookings' ) {
				$select = $wpdb->prepare(
					"SELECT $table.*, COUNT({$wpdb->postmeta}.meta_value ) as `bookings`
					FROM $table 
					    LEFT JOIN {$wpdb->postmeta} 
					        ON $table.id = {$wpdb->postmeta}.meta_value AND
					           {$wpdb->postmeta}.meta_key = %s",
					'_mpa_customer_id'
				);

				$groupBy = $wpdb->prepare( 'GROUP BY %1s', 'id' );
				$select .= " $where $groupBy ";
			}
			$orderby = $wpdb->prepare( 'ORDER BY %1s %1s', $atts['orderby'], $order );
		}

		$paged    = isset( $atts['paged'] ) ? (int) $atts['paged'] : 1;
		$per_page = isset( $atts['per_page'] ) ? (int) $atts['per_page'] : 99999;
		$offset   = ( $paged - 1 ) * $per_page;
		$limit    = $wpdb->prepare( 'LIMIT %d,%d', $offset, $per_page );

		$sql = "$select $orderby $limit";

		$result = $wpdb->get_results( $sql, ARRAY_A );

		if ( null == $result ) {
			return array();
		}

		foreach ( $result as $customer ) {
			$customers[] = $this->mapDataToEntity( $customer );
		}

		return $customers;
	}

	/**
	 * An array of all user_id's associated with the customers.
	 * @return int[]|array
	 */
	public function findAllAssociatedUserIds(): array {
		global $wpdb;

		$table = $wpdb->prefix . 'mpa_customers';

		$sql = "SELECT $table.user_id FROM $table WHERE $table.user_id IS NOT NULL";

		return $wpdb->get_col( $sql );
	}

	/**
	 * @return int
	 */
	public function getTotalCustomers(): int {
		global $wpdb;

		$table = $wpdb->prefix . 'mpa_customers';

		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table" );
	}

	/**
	 * @param Customer $customer
	 *
	 * @return bool
	 * @throws \Exception Non unique email or phone of customer.
	 */
	public function save( Customer &$customer ): bool {

		global $wpdb;

		$wpdb->show_errors = false;

		$table = $wpdb->prefix . 'mpa_customers';

		$formats = array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' );

		if ( $customer->getDateRegistered() ) {
			$dateRegistered = $customer->getDateRegistered()->format( 'Y-m-d H:i:s' );
		} else {
			$currentDateTime = new \DateTime();
			$dateRegistered  = $currentDateTime->format( 'Y-m-d H:i:s' );
		}

		if ( $customer->getLastActive() ) {
			$lastActive = $customer->getLastActive()->format( 'Y-m-d H:i:s' );
		} else {
			$lastActive = null;
		}

		$data = array(
			'user_id'         => empty( $customer->getUserId() ) ? null : $customer->getUserId(),
			'name'            => $customer->getName(),
			'email'           => empty( $customer->getEmail() ) ? null : $customer->getEmail(),
			'phone'           => empty( $customer->getPhone() ) ? null : $customer->getPhone(),
			'date_registered' => $dateRegistered,
			'last_active'     => $lastActive,
		);

		if ( $customer->getId() ) {
			$result = $wpdb->update(
				$table,
				$data,
				array(
					'id' => $customer->getId(),
				),
				$formats,
				array( '%d' )
			);
		} else {
			$result = $wpdb->insert(
				$table,
				$data,
				$formats
			);

			$customer->setId( $wpdb->insert_id );

		}

		if ( ! $result ) {
			if ( $wpdb->last_error ) {
				throw new \Exception( $wpdb->last_error );
			}

			return false;
		}

		return true;
	}

	/**
	 * @param int $customerId
	 *
	 * @return int
	 */
	public function getTotalBookingsOfCustomer( int $customerId ): int {
		$args = array(
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		);

		$bookingIds = mpapp()->repositories()->booking()->findAllByMeta( '_mpa_customer_id', $customerId, '=', $args );

		if ( ! is_array( $bookingIds ) ) {
			return 0;
		}

		return count( $bookingIds );
	}

	/**
	 * @param int $customerId
	 *
	 * @return bool
	 */
	public function updateLastActive( int $customerId ): bool {
		if ( ! $customerId ) {
			return false;
		}

		$customer = mpapp()->repositories()->customer()->findById( $customerId );

		if ( ! $customer ) {
			return false;
		}

		$currentDateTime = new \DateTime();
		$customer->setLastActive( $currentDateTime );

		$updated = false;
		try {
			$updated = mpapp()->repositories()->customer()->save( $customer );
		} catch ( \Exception $e ) {
		}

		return $updated;
	}

	/**
	 * @param int $customerId
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function delete( int $customerId ): bool {
		global $wpdb;

		$table = $wpdb->prefix . 'mpa_customers';

		if ( ! SecurityHandler::isUserCanDeleteCustomer() ) {
			wp_die( esc_html__( 'You do not have permission to do this action.', 'motopress-appointment' ) );
		}

		if ( 0 !== $this->getTotalBookingsOfCustomer( $customerId ) ) {
			wp_die( esc_html__( 'You cannot delete a customer if they have bookings.', 'motopress-appointment' ) );
		}

		$deleted = $wpdb->delete(
			$table,
			array(
				'ID' => $customerId,
			),
			array( '%d' )
		);

		if ( ! $deleted ) {
			if ( $wpdb->last_error ) {
				throw new \Exception( $wpdb->last_error );
			}

			return false;
		}

		do_action( 'mpa_deleted_customer', $customerId );

		return true;
	}
}
