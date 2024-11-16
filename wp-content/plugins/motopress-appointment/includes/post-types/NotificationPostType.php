<?php

declare(strict_types=1);

namespace MotoPress\Appointment\PostTypes;

use MotoPress\Appointment\Entities\Booking;
use MotoPress\Appointment\PostTypes\Statuses\NotificationStatuses;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.13.0
 */
class NotificationPostType extends AbstractPostType {

	const POST_TYPE = 'mpa_notification';

	/**
	 * @var NotificationStatuses
	 */
	protected $statuses;


	public function __construct() {
		parent::__construct();

		$this->statuses = new NotificationStatuses( self::POST_TYPE );
	}

	/**
	 * @return string
	 */
	public function getLabel() {
		return esc_html__( 'Notifications', 'motopress-appointment' );
	}

	/**
	 * @return string
	 */
	public function getSingularLabel() {
		return esc_html__( 'Notification', 'motopress-appointment' );
	}

	/**
	 * @return string
	 */
	protected function getDescription() {
		return esc_html__( 'This is where you can add new notifications.', 'motopress-appointment' );
	}

	/**
	 * @return array
	 */
	protected function getLabels() {
		return array(
			'name'               => $this->getLabel(),
			'singular_name'      => $this->getSingularLabel(),
			'add_new'            => esc_html_x( 'Add New', 'Add new notification', 'motopress-appointment' ),
			'add_new_item'       => esc_html__( 'Add New Notification', 'motopress-appointment' ),
			'new_item'           => esc_html__( 'New Notification', 'motopress-appointment' ),
			'edit_item'          => esc_html__( 'Edit Notification', 'motopress-appointment' ),
			'view_item'          => esc_html__( 'View Notification', 'motopress-appointment' ),
			'search_items'       => esc_html__( 'Search Notification', 'motopress-appointment' ),
			'not_found'          => esc_html__( 'No notification found', 'motopress-appointment' ),
			'not_found_in_trash' => esc_html__( 'No notifications found in Trash', 'motopress-appointment' ),
			'all_items'          => esc_html__( 'Notifications', 'motopress-appointment' ),
		);
	}

	/**
	 * @return array
	 */
	protected function registerArgs() {
		return array(
			'capabilities' => array(
				'create_posts' => 'create_' . static::POST_TYPE . 's',
			),
			'public'       => false,
			'show_in_menu' => mpapp()->pages()->appointmentMenu()->getId(),
			'show_ui'      => true,
			'supports'     => array( 'title' ),
		);
	}

	/**
	 * @return NotificationStatuses
	 */
	public function statuses() {
		return $this->statuses;
	}
}
