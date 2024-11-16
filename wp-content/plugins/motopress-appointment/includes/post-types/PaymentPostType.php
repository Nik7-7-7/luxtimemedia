<?php

namespace MotoPress\Appointment\PostTypes;

use MotoPress\Appointment\Entities\Payment;
use MotoPress\Appointment\PostTypes\Logs\PostTypeLogs;
use MotoPress\Appointment\PostTypes\Statuses\PaymentStatuses;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.5.0
 */
class PaymentPostType extends AbstractPostType {

	/** @since 1.5.0 */
	const POST_TYPE = 'mpa_payment';

	/**
	 * @since 1.5.0
	 *
	 * @var PaymentStatuses
	 */
	protected $statuses;

	/**
	 * @var PostTypeLogs
	 *
	 * @since 1.14.0
	 */
	protected $logs;

	/**
	 * @since 1.5.0
	 */
	public function __construct() {

		parent::__construct();

		$this->statuses = new PaymentStatuses( self::POST_TYPE );
		$this->logs     = new PostTypeLogs( self::POST_TYPE );
	}

	/**
	 * @since 1.5.0
	 */
	protected function addActions() {
		parent::addActions();

		add_action( "mpa_new_{$this->entityType}_created", array( $this, 'prepareNewPost' ) );
	}

	/**
	 * @since 1.5.0
	 * @access protected
	 *
	 * @param Payment $payment
	 */
	public function prepareNewPost( $payment ) {
		wp_update_post(
			array(
				'ID'         => $payment->getId(),
				// Translators: %d: Payment ID.
				'post_title' => sprintf( esc_html__( 'Payment #%d', 'motopress-appointment' ), $payment->getId() ),
				'post_name'  => "mpa-payment-{$payment->getId()}",
			)
		);

		mpa_add_post_uid( $payment->getId() );
	}

	/**
	 * @since 1.5.0
	 *
	 * @return string
	 */
	public function getLabel() {
		return esc_html__( 'Payments', 'motopress-appointment' );
	}

	/**
	 * @since 1.5.0
	 *
	 * @return string
	 */
	public function getSingularLabel() {
		return esc_html__( 'Payment', 'motopress-appointment' );
	}

	/**
	 * @since 1.5.0
	 *
	 * @return array
	 */
	protected function getLabels() {
		return array(
			'name'               => $this->getLabel(),
			'singular_name'      => $this->getSingularLabel(),
			'add_new'            => esc_html_x( 'Add New', 'Add new payment', 'motopress-appointment' ),
			'add_new_item'       => esc_html__( 'Add New Payment', 'motopress-appointment' ),
			'new_item'           => esc_html__( 'New Payment', 'motopress-appointment' ),
			'edit_item'          => esc_html__( 'Edit Payment', 'motopress-appointment' ),
			'view_item'          => esc_html__( 'View Payment', 'motopress-appointment' ),
			'search_items'       => esc_html__( 'Search Payment', 'motopress-appointment' ),
			'not_found'          => esc_html__( 'No payments found', 'motopress-appointment' ),
			'not_found_in_trash' => esc_html__( 'No payments found in Trash', 'motopress-appointment' ),
			'all_items'          => esc_html__( 'Payments', 'motopress-appointment' ),
		);
	}

	/**
	 * @since 1.5.0
	 *
	 * @return array
	 */
	protected function registerArgs() {
		return array(
			'public'       => false,
			'show_in_menu' => mpapp()->pages()->appointmentMenu()->getId(),
			'show_ui'      => true,
			'supports'     => false,
			'capabilities' => array(
				'create_posts' => 'create_' . static::POST_TYPE . 's',
			),
		);
	}

	/**
	 * @since 1.5.0
	 *
	 * @return PaymentStatuses
	 */
	public function statuses() {
		return $this->statuses;
	}

	/**
	 * @return PostTypeLogs
	 *
	 * @since 1.14.0
	 */
	public function logs() {
		return $this->logs;
	}
}
