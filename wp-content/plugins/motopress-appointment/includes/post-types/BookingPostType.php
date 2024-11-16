<?php

namespace MotoPress\Appointment\PostTypes;

use MotoPress\Appointment\Entities\Booking;
use MotoPress\Appointment\PostTypes\Logs\PostTypeLogs;
use MotoPress\Appointment\PostTypes\Statuses\BookingStatuses;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class BookingPostType extends AbstractPostType {

	/** @since 1.0 */
	const POST_TYPE = 'mpa_booking';

	/**
	 * @var BookingStatuses
	 *
	 * @since 1.0
	 */
	protected $statuses;

	/**
	 * @var PostTypeLogs
	 *
	 * @since 1.14.0
	 */
	protected $logs;

	/**
	 * @since 1.0
	 */
	public function __construct() {
		parent::__construct();

		$this->statuses = new BookingStatuses( self::POST_TYPE );
		$this->logs     = new PostTypeLogs( self::POST_TYPE );
	}

	/**
	 * @since 1.0
	 */
	protected function addActions() {

		parent::addActions();

		add_action( 'before_delete_post', array( $this, 'onDelete' ) );
	}

	/**
	 * @param int $postId
	 *
	 * @since 1.0
	 */
	public function onDelete( $postId ) {

		if ( get_post_type( $postId ) == self::POST_TYPE ) {
			// Delete linked reservations
			$reservations = mpapp()->repositories()->reservation()->findAllByBooking( $postId );

			foreach ( $reservations as $reservation ) {
				wp_delete_post( $reservation->getId(), true );
			}
		}
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getLabel() {
		return esc_html__( 'Bookings', 'motopress-appointment' );
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getSingularLabel() {
		return esc_html__( 'Booking', 'motopress-appointment' );
	}

	/**
	 * @return string
	 *
	 * @since 1.18.0
	 */
	public function getAddNewLabel(): string {
		return esc_html_x( 'Add New', 'Add new booking', 'motopress-appointment' );
	}

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function getLabels() {
		return array(
			'name'               => $this->getLabel(),
			'singular_name'      => $this->getSingularLabel(),
			'add_new'            => $this->getAddNewLabel(),
			'add_new_item'       => esc_html__( 'Add New Booking', 'motopress-appointment' ),
			'new_item'           => esc_html__( 'New Booking', 'motopress-appointment' ),
			'edit_item'          => esc_html__( 'Edit Booking', 'motopress-appointment' ),
			'view_item'          => esc_html__( 'View Booking', 'motopress-appointment' ),
			'search_items'       => esc_html__( 'Search Booking', 'motopress-appointment' ),
			'not_found'          => esc_html__( 'No bookings found', 'motopress-appointment' ),
			'not_found_in_trash' => esc_html__( 'No bookings found in Trash', 'motopress-appointment' ),
			'all_items'          => esc_html__( 'Bookings', 'motopress-appointment' ),
		);
	}

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function registerArgs() {
		return array(
			'supports'     => false,
			'public'       => false,
			'show_ui'      => true,
			'show_in_menu' => mpapp()->pages()->appointmentMenu()->getId(),
			'capabilities' => array(
				// @NOLITE-CODE-START
				'create_posts' => 'create_' . static::POST_TYPE . 's',
				// @NOLITE-CODE-END
				
			),
		);
	}

	/**
	 * @return BookingStatuses
	 *
	 * @since 1.0
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
