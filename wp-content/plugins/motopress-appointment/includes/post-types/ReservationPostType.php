<?php

namespace MotoPress\Appointment\PostTypes;

use MotoPress\Appointment\Entities\Reservation;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class ReservationPostType extends AbstractPostType {

	/** @since 1.0 */
	const POST_TYPE = 'mpa_reservation';


	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getLabel() {
		return esc_html__( 'Reservations', 'motopress-appointment' );
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getSingularLabel() {
		return esc_html__( 'Reservation', 'motopress-appointment' );
	}

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function getLabels() {
		return array(
			'name'          => $this->getLabel(),
			'singular_name' => $this->getSingularLabel(),
			'all_items'     => esc_html__( 'Reservations', 'motopress-appointment' ),
		);
	}

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function registerArgs() {
		return array(
			'public'       => false,
			'show_ui'      => false,
			'supports'     => array(),
			'capabilities' => array(
				'create_posts' => 'create_' . static::POST_TYPE . 's',
			),
		);
	}
}
