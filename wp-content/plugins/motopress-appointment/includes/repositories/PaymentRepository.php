<?php

namespace MotoPress\Appointment\Repositories;

use MotoPress\Appointment\Entities\Payment;
use MotoPress\Appointment\PostTypes\Statuses\PaymentStatuses;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.5.0
 * @see Payment
 */
class PaymentRepository extends AbstractRepository {

	/**
	 * @since 1.5.0
	 */
	protected function addActions() {

		parent::addActions();

		add_filter( "{$this->postType}_repository_get_posts_query_args", array( $this, 'filterArgs' ), 5 );
	}

	/**
	 * @since 1.5.0
	 *
	 * @return array
	 */
	protected function entitySchema() {
		return array(
			'post'     => array( 'ID', 'post_parent', 'post_status' ),
			'postmeta' => array(
				'_mpa_uid'            => true,
				'_mpa_amount'         => true,
				'_mpa_currency'       => true,
				'_mpa_gateway_id'     => true,
				'_mpa_gateway_mode'   => true,
				'_mpa_payment_method' => true,
				'_mpa_transaction_id' => true,
			),
		);
	}

	/**
	 * @since 1.5.0
	 *
	 * @param array $postData
	 * @return Payment
	 */
	protected function mapPostDataToEntity( $postData ) {

		$id = (int) $postData['ID'];

		$fields = array(
			'uid'           => $postData['uid'],
			'status'        => $postData['post_status'],
			'bookingId'     => (int) $postData['post_parent'],
			'amount'        => (float) $postData['amount'],
			'currency'      => $postData['currency'],
			'gatewayId'     => $postData['gateway_id'],
			'gatewayMode'   => $postData['gateway_mode'],
			'paymentMethod' => $postData['payment_method'],
			'transactionId' => $postData['transaction_id'],
		);

		return new Payment( $id, $fields );
	}

	/**
	 * @since 1.5.0
	 *
	 * @param int $bookingId
	 * @param array $args Optional.
	 * @return Payment[]
	 */
	public function findAllByBooking( $bookingId, $args = array() ) {
		return $this->findAll( array( 'post_parent' => $bookingId ) + $args );
	}

	/**
	 * @since 1.5.0
	 *
	 * @param array $args Optional.
	 * @return Payment[]
	 */
	public function findAllExpired( $args = array() ) {
		$args['pending_expired'] = true;

		return $this->findAll( $args );
	}

	/**
	 * @since 1.5.0
	 *
	 * @param string $translationId
	 * @return Payment|null
	 */
	public function findByTransactionId( $translationId ) {
		return $this->findByMeta( '_mpa_transaction_id', $translationId );
	}

	/**
	 * @since 1.5.0
	 *
	 * @return bool
	 */
	public function havePendingPayments() {
		$pendingPayments = $this->findAll(
			array(
				'fields'         => 'ids',
				'post_status'    => PaymentStatuses::STATUS_PENDING,
				'posts_per_page' => 1,
			)
		);

		return count( $pendingPayments ) > 0;
	}

	/**
	 * @since 1.5.0
	 *
	 * @return array
	 */
	protected function defaultQueryArgs() {
		$defaultArgs                = parent::defaultQueryArgs();
		$defaultArgs['post_status'] = 'any';

		return $defaultArgs;
	}

	/**
	 * @since 1.5.0
	 *
	 * @access protected
	 *
	 * @param array $args
	 * @return array
	 */
	public function filterArgs( $args ) {

		if ( ! isset( $args['meta_query'] ) ) {
			$args['meta_query'] = array();
		}

		// Expired payments
		if ( isset( $args['pending_expired'] ) ) {
			$args['post_status'] = PaymentStatuses::STATUS_PENDING;

			if ( $args['pending_expired'] ) {
				$args['meta_query'][] = array(
					'key'     => '_mpa_pending_time',
					'value'   => time(),
					'type'    => 'NUMERIC',
					'compare' => '<=',
				);
			}

			unset( $args['pending_expired'] );
		}

		return $args;
	}
}
