<?php
/**
 * @package MotoPress\Appointment\Rest
 * @since 1.21.0
 */

namespace MotoPress\Appointment\Rest\Data;

use MotoPress\Appointment\Entities\Payment;
use MotoPress\Appointment\Payments\Gateways\AbstractPaymentGateway;

class PaymentData extends AbstractPostData {

	/**
	 * @var Payment
	 */
	public $entity;

	public static function getRepository() {
		return mpapp()->repositories()->payment();
	}

	public static function getProperties() {
		$statusModel = mpapp()->postTypes()->payment()->statuses();

		return array(
			'id'             => array(
				'description' => 'Unique identifier for the resource.',
				'type'        => 'integer',
				'context'     => array( 'embed', 'view', 'edit' ),
				'readonly'    => true,
			),
			'uid'            => array(
				'description' => 'Payment uid.',
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'status'         => array(
				'description' => 'Payment status.',
				'type'        => 'string',
				'context'     => array( 'embed', 'view', 'edit' ),
				'enum'        => array(
					$statusModel::STATUS_PENDING,
					$statusModel::STATUS_ON_HOLD,
					$statusModel::STATUS_COMPLETED,
					$statusModel::STATUS_CANCELLED,
					$statusModel::STATUS_ABANDONED,
					$statusModel::STATUS_FAILED,
					$statusModel::STATUS_REFUNDED,
				),
				'default'     => mpapp()->postTypes()->payment()->statuses()->getDefaultManualStatus(),
			),
			'booking_id'     => array(
				'description' => 'Associated booking ID.',
				'type'        => 'integer',
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
				'enum'        => array_keys( mpapp()->bundles()->currencies()->getCurrencies() ),
				'context'     => array( 'embed', 'view', 'edit' ),
			),
			'gateway_id'     => array(
				'description' => 'Payment gateway identifier.',
				'type'        => 'string',
				'context'     => array( 'embed', 'view', 'edit' ),
			),
			'gateway_mode'   => array(
				'description' => 'Payment gateway mode (e.g., live, sandbox).',
				'type'        => 'string',
				'context'     => array( 'embed', 'view', 'edit' ),
				'enum'        => array(
					AbstractPaymentGateway::GATEWAY_MODE_LIVE,
					AbstractPaymentGateway::GATEWAY_MODE_SANDBOX,
				),
				'readonly'    => true,
			),
			'payment_method' => array(
				'description' => 'Method of payment (e.g., card, cash).',
				'type'        => 'string',
				'enum'        => array_keys( mpapp()->payments()->getAll() ),
				'context'     => array( 'embed', 'view', 'edit' ),
			),
			'transaction_id' => array(
				'description' => 'Transaction identifier for the payment.',
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
		);
	}
}