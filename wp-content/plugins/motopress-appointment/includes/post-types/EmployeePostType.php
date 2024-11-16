<?php

namespace MotoPress\Appointment\PostTypes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class EmployeePostType extends AbstractBlockEditorPostType {

	/** @since 1.0 */
	const POST_TYPE = 'mpa_employee';

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getLabel() {
		return esc_html__( 'Employees', 'motopress-appointment' );
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getSingularLabel() {
		return esc_html__( 'Employee', 'motopress-appointment' );
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
			'add_new'            => esc_html_x( 'Add New', 'Add new employee', 'motopress-appointment' ),
			'add_new_item'       => esc_html__( 'Add New Employee', 'motopress-appointment' ),
			'new_item'           => esc_html__( 'New Employee', 'motopress-appointment' ),
			'edit_item'          => esc_html__( 'Edit Employee', 'motopress-appointment' ),
			'view_item'          => esc_html__( 'View Employee', 'motopress-appointment' ),
			'search_items'       => esc_html__( 'Search Employee', 'motopress-appointment' ),
			'not_found'          => esc_html__( 'No employees found', 'motopress-appointment' ),
			'not_found_in_trash' => esc_html__( 'No employees found in Trash', 'motopress-appointment' ),
			'all_items'          => esc_html__( 'Employees', 'motopress-appointment' ),
		);
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	protected function getDescription() {
		return esc_html__( 'This is where you can add new employees.', 'motopress-appointment' );
	}

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function registerArgs() {
		return parent::registerArgs() + array(
			'public'       => true,
			'rewrite'      => array(
				'slug'       => 'employee',
				'with_front' => false,
				'feeds'      => true,
			),
			'show_in_menu' => mpapp()->pages()->appointmentMenu()->getId(),
			'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
			'capabilities' => array(
				'create_posts' => 'create_' . static::POST_TYPE . 's',
			),
		);
	}
}
