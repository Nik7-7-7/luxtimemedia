<?php

namespace MotoPress\Appointment\PostTypes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class SchedulePostType extends AbstractPostType {

	/** @since 1.0 */
	const POST_TYPE = 'mpa_schedule';

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getLabel() {
		return esc_html__( 'Schedules', 'motopress-appointment' );
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getSingularLabel() {
		return esc_html__( 'Schedule', 'motopress-appointment' );
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
			'add_new'            => esc_html_x( 'Add New', 'Add new schedule', 'motopress-appointment' ),
			'add_new_item'       => esc_html__( 'Add New Schedule', 'motopress-appointment' ),
			'new_item'           => esc_html__( 'New Schedule', 'motopress-appointment' ),
			'edit_item'          => esc_html__( 'Edit Schedule', 'motopress-appointment' ),
			'view_item'          => esc_html__( 'View Schedule', 'motopress-appointment' ),
			'search_items'       => esc_html__( 'Search Schedule', 'motopress-appointment' ),
			'not_found'          => esc_html__( 'No schedule found', 'motopress-appointment' ),
			'not_found_in_trash' => esc_html__( 'No schedules found in Trash', 'motopress-appointment' ),
			'all_items'          => esc_html__( 'Schedules', 'motopress-appointment' ),
		);
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	protected function getDescription() {
		return esc_html__( 'This is where you can add new schedules.', 'motopress-appointment' );
	}

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function registerArgs() {
		return array(
			'supports'     => array( 'title' ),
			'public'       => false,
			'show_ui'      => true,
			'show_in_menu' => mpapp()->pages()->appointmentMenu()->getId(),
			'capabilities' => array(
				'create_posts' => 'create_' . static::POST_TYPE . 's',
			),
		);
	}
}
