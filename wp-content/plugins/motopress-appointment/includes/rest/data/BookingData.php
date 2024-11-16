<?php
/**
 * @package MotoPress\Appointment\Rest
 * @since 1.8.0
 */

namespace MotoPress\Appointment\Rest\Data;

use MotoPress\Appointment\Entities\Booking;

class BookingData extends AbstractPostData {

	/**
	 * @var Booking
	 */
	public $entity;

	public static function getRepository() {
		return mpapp()->repositories()->booking();
	}

	public static function getProperties() {
		$statusModel = mpapp()->postTypes()->booking()->statuses();

		return array(
			'id'           => array(
				'description' => 'Unique identifier for the resource.',
				'type'        => 'integer',
				'context'     => array( 'embed', 'view', 'edit' ),
				'readonly'    => true,
			),
			'uid'          => array(
				'description' => 'Booking uid.',
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'status'       => array(
				'description' => 'Booking status.',
				'type'        => 'string',
				'context'     => array( 'embed', 'view', 'edit' ),
				'enum'        => array(
					$statusModel::STATUS_PENDING,
					$statusModel::STATUS_CANCELLED,
					$statusModel::STATUS_ABANDONED,
					$statusModel::STATUS_CONFIRMED,
				),
				'default'     => mpapp()->postTypes()->booking()->statuses()->getDefaultManualStatus(),
			),
			'reservations' => array(
				'description' => 'Reservation IDs.',
				'type'        => 'array',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
				'items'       => array(
					'type'     => 'integer',
					'readonly' => true,
				),
			),
			'payments'     => array(
				'description' => 'Payment IDs.',
				'type'        => 'object',
				'context'     => array( 'embed', 'view', 'edit' ),
				'readonly'    => true,
				'properties'  => array(
					'id'             => array(
						'description' => 'ID.',
						'type'        => 'integer',
						'context'     => array( 'embed', 'view', 'edit' ),
					),
					'status'         => array(
						'description' => 'Status.',
						'type'        => 'string',
						'context'     => array( 'embed', 'view', 'edit' ),
					),
					'amount'         => array(
						'description' => 'Total payment amount.',
						'type'        => 'float',
						'context'     => array( 'embed', 'view', 'edit' ),
					),
					'currency'       => array(
						'description' => 'Payment currency code.',
						'type'        => 'string',
						'context'     => array( 'embed', 'view', 'edit' ),
					),
					'gateway_id'     => array(
						'description' => 'Payment gateway identifier.',
						'type'        => 'string',
						'context'     => array( 'embed', 'view', 'edit' ),
					),
					'payment_method' => array(
						'description' => 'Method of payment (e.g., card, cash).',
						'type'        => 'string',
						'context'     => array( 'embed', 'view', 'edit' ),
					),
				),
			),
			'coupon'       => array(
				'description' => 'Coupon code id.',
				'type'        => 'integer',
				'context'     => array( 'embed', 'view', 'edit' ),
			),
			'customer'     => array(
				'description' => 'Customer Information.',
				'type'        => 'object',
				'context'     => array( 'embed', 'view', 'edit' ),
				'properties'  => array(
					'name'  => array(
						'description' => 'Name.',
						'type'        => 'string',
						'context'     => array( 'embed', 'view', 'edit' ),
					),
					'email' => array(
						'description' => 'Email.',
						'oneOf'       => array(
							array(
								'type'   => 'string',
								'format' => 'email',
							),
							array(
								'type'      => 'string',
								'maxLength' => 0,
							),
						),
						'context'     => array( 'embed', 'view', 'edit' ),
					),
					'phone' => array(
						'description' => 'Phone.',
						'type'        => 'string',
						'context'     => array( 'embed', 'view', 'edit' ),
					),
				),
			),
			'total_price'  => array(
				'description' => 'Total price.',
				'type'        => 'number',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
		);
	}

	public function getReservations() {
		return array_map(
			function ( $reservation ) {
				return $reservation->getId();
			},
			$this->entity->getReservations()
		);
	}

	/**
	 * @return array
	 *
	 * @since 1.21.0
	 */
	public function getPayments() {
		$payments = $this->entity->getPayments();

		if ( ! count( $payments ) ) {
			return array();
		}

		return array_map( function ( $payment ) {
			return array(
				'id'             => $payment->getId(),
				'status'         => $payment->getStatus(),
				'amount'         => $payment->getAmount(),
				'currency'       => $payment->getCurrency(),
				'gateway_id'     => $payment->getGatewayId(),
				'payment_method' => $payment->getPaymentMethod(),
			);
		}, $payments );
	}

	/**
	 * @return int
	 *
	 * @since 1.21.0
	 */
	public function getCoupon() {
		return $this->entity->getCouponId();
	}

	public function getCustomer() {
		return array(
			'id'    => $this->entity->getCustomerId(),
			'name'  => $this->entity->getCustomerName(),
			'email' => $this->entity->getCustomerEmail(),
			'phone' => $this->entity->getCustomerPhone(),
		);
	}
}