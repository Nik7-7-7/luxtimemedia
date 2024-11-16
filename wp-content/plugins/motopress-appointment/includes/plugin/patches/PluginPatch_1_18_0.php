<?php

namespace MotoPress\Appointment\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PluginPatch_1_18_0 extends AbstractPluginPatch {

	public static function getVersion(): string {
		return '1.18.0';
	}

	/**
	 * Inserting data to the customers table.
	 * Converting customer postmeta for each booking into a customers table.
	 * Each booking is linked to a customer ID from the customer table.
	 */
	private static function fillCustomerTable() {

		$args = array(
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key'     => '_mpa_customer_id',
					'compare' => 'NOT EXISTS',
				),
				array(
					'relation' => 'OR',
					array(
						'key'     => '_mpa_customer_email',
						'value'   => '',
						'compare' => '!=',
					),
					array(
						'key'     => '_mpa_customer_phone',
						'value'   => '',
						'compare' => '!=',
					),
				),
			),
		);

		$bookingsWithoutCustomerId = mpapp()->repositories()->booking()->findAll( $args );

		foreach ( $bookingsWithoutCustomerId as $booking ) {

			$customer      = null;
			$customerName  = $booking->getCustomerName();
			$customerEmail = $booking->getCustomerEmail();
			$customerPhone = $booking->getCustomerPhone();

			$bookingId              = $booking->getId();
			$bookingCreatedDateTime = get_post_datetime( $bookingId );

			if ( $customerEmail ) {
				$customer = mpapp()->repositories()->customer()->findByEmail( $customerEmail );
			}

			if ( ! $customer && $customerPhone ) {
				$customer = mpapp()->repositories()->customer()->findByPhone( $customerPhone );
			}

			if ( is_null( $customer ) ) {

				$customerData = array();

				if ( $customerName ) {
					$customerData['name'] = $customerName;
				}
				if ( $customerEmail ) {
					$customerData['email'] = $customerEmail;
				}
				if ( $customerPhone ) {
					$customerData['phone'] = $customerPhone;
				}
				if ( $bookingCreatedDateTime ) {
					$customerData['date_registered'] = $bookingCreatedDateTime->format( 'Y-m-d H:i:s' );
				}

				try {

					$customer = mpapp()->repositories()->customer()->mapDataToEntity( $customerData );
					mpapp()->repositories()->customer()->save( $customer );

				} catch ( \Exception $e ) {
					continue;
				}
			}

			if ( $customer->getDateRegistered() > $bookingCreatedDateTime ) {

				// Set DateTime in correct format
				// Convert $bookingCreatedDateTime from DateTimeImmutable to DateTime
				$customer->setDateRegistered( \DateTime::createFromFormat( 'Y-m-d H:i:s', $bookingCreatedDateTime->format( 'Y-m-d H:i:s' ) ) );
				try {

					mpapp()->repositories()->customer()->save( $customer );

					// phpcs:ignore
				} catch ( \Exception $e ) {
					// do nothing
				}
			}

			$customerId = $customer->getId();

			if ( ! $customerId ) {
				continue;
			}

			update_post_meta( $bookingId, '_mpa_customer_id', $customerId );
		}
	}

	/**
	 * Creating a custom database table for Customers.
	 */
	public static function execute(): bool {

		DatabaseTables::createCustomerTable();
		self::fillCustomerTable();

		return true;
	}
}
