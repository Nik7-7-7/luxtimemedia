<?php

namespace MotoPress\Appointment\PostTypes;

use MotoPress\Appointment\PostTypes\Taxonomy\PostTypeCategory;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class LocationPostType extends AbstractBlockEditorPostType {

	/** @since 1.0 */
	const POST_TYPE = 'mpa_location';

	/** @since 1.0 */
	const CATEGORY_NAME = 'mpa_location_category';

	use PostTypeCategory;

	/**
	 * @since 1.0
	 */
	protected function addActions() {
		parent::addActions();

		add_action( 'init', array( $this, 'registerCategory' ), 5 );
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getLabel() {
		return esc_html__( 'Locations', 'motopress-appointment' );
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getSingularLabel() {
		return esc_html__( 'Location', 'motopress-appointment' );
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	protected function getDescription() {
		return esc_html__( 'This is where you can add new locations.', 'motopress-appointment' );
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
			'add_new'            => esc_html_x( 'Add New', 'Add new location', 'motopress-appointment' ),
			'add_new_item'       => esc_html__( 'Add New Location', 'motopress-appointment' ),
			'new_item'           => esc_html__( 'New Location', 'motopress-appointment' ),
			'edit_item'          => esc_html__( 'Edit Location', 'motopress-appointment' ),
			'view_item'          => esc_html__( 'View Location', 'motopress-appointment' ),
			'search_items'       => esc_html__( 'Search Location', 'motopress-appointment' ),
			'not_found'          => esc_html__( 'No location found', 'motopress-appointment' ),
			'not_found_in_trash' => esc_html__( 'No locations found in Trash', 'motopress-appointment' ),
			'all_items'          => esc_html__( 'Locations', 'motopress-appointment' ),
		);
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
				'slug'       => 'location',
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

	/**
	 * @return array
	 *
	 * @since 1.2
	 */
	protected function getCategoryLabels() {
		return array(
			'name'                       => esc_html__( 'Location Categories', 'motopress-appointment' ),
			'singular_name'              => esc_html__( 'Location Category', 'motopress-appointment' ),
			'search_items'               => esc_html__( 'Search Location Categories', 'motopress-appointment' ),
			'popular_items'              => esc_html__( 'Popular Location Categories', 'motopress-appointment' ),
			'all_items'                  => esc_html__( 'All Location Categories', 'motopress-appointment' ),
			'parent_item'                => esc_html__( 'Parent Location Category', 'motopress-appointment' ),
			'parent_item_colon'          => esc_html__( 'Parent Location Category:', 'motopress-appointment' ),
			'edit_item'                  => esc_html__( 'Edit Location Category', 'motopress-appointment' ),
			'update_item'                => esc_html__( 'Update Location Category', 'motopress-appointment' ),
			'add_new_item'               => esc_html__( 'Add New Location Category', 'motopress-appointment' ),
			'new_item_name'              => esc_html__( 'New Location Category Name', 'motopress-appointment' ),
			'separate_items_with_commas' => esc_html__( 'Separate categories with commas', 'motopress-appointment' ),
			'add_or_remove_items'        => esc_html__( 'Add or remove categories', 'motopress-appointment' ),
			'choose_from_most_used'      => esc_html__( 'Choose from the most used categories', 'motopress-appointment' ),
			'not_found'                  => esc_html__( 'No categories found.', 'motopress-appointment' ),
			'menu_name'                  => esc_html__( 'Categories', 'motopress-appointment' ),
		);
	}

	/**
	 * @return array
	 *
	 * @since 1.2
	 */
	protected function getCategoryArgs() {
		return parent::registerArgs() + array(
			'public'       => true,
			'show_ui'      => true,
			'show_in_menu' => mpapp()->pages()->appointmentMenu()->getId(),
			'hierarchical' => false,
			'query_var'    => true,
			'rewrite'      => array(
				'slug'         => 'location-category',
				'with_front'   => false,
				'hierarchical' => false,
			),
		);
	}
}
