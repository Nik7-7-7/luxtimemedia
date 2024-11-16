<?php

namespace MotoPress\Appointment\PostTypes;

use MotoPress\Appointment\PostTypes\Taxonomy\PostTypeCategory;
use MotoPress\Appointment\PostTypes\Taxonomy\PostTypeTag;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class ServicePostType extends AbstractBlockEditorPostType {

	/** @since 1.0 */
	const POST_TYPE = 'mpa_service';

	/** @since 1.0 */
	const CATEGORY_NAME = 'mpa_service_category';

	/** @since 1.0 */
	const TAG_NAME = 'mpa_service_tag';

	use PostTypeCategory, PostTypeTag;

	/**
	 * @since 1.0
	 */
	protected function addActions() {
		parent::addActions();

		add_action( 'init', array( $this, 'registerCategory' ), 5 );
		add_action( 'init', array( $this, 'registerTag' ), 5 );

		add_action( 'admin_menu', array( $this, 'addTaxonomiesToMenu' ), 15 );
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getLabel() {
		return esc_html__( 'Services', 'motopress-appointment' );
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getSingularLabel() {
		return esc_html__( 'Service', 'motopress-appointment' );
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	protected function getDescription() {
		return esc_html__( 'This is where you can add new services.', 'motopress-appointment' );
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
			'add_new'            => esc_html_x( 'Add New', 'Add new service', 'motopress-appointment' ),
			'add_new_item'       => esc_html__( 'Add New Service', 'motopress-appointment' ),
			'new_item'           => esc_html__( 'New Service', 'motopress-appointment' ),
			'edit_item'          => esc_html__( 'Edit Service', 'motopress-appointment' ),
			'view_item'          => esc_html__( 'View Service', 'motopress-appointment' ),
			'search_items'       => esc_html__( 'Search Service', 'motopress-appointment' ),
			'not_found'          => esc_html__( 'No service found', 'motopress-appointment' ),
			'not_found_in_trash' => esc_html__( 'No services found in Trash', 'motopress-appointment' ),
			'all_items'          => esc_html__( 'Services', 'motopress-appointment' ),
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
				'slug'       => 'service',
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
			'name'                       => esc_html__( 'Service Categories', 'motopress-appointment' ),
			'singular_name'              => esc_html__( 'Service Category', 'motopress-appointment' ),
			'search_items'               => esc_html__( 'Search Service Categories', 'motopress-appointment' ),
			'popular_items'              => esc_html__( 'Popular Service Categories', 'motopress-appointment' ),
			'all_items'                  => esc_html__( 'All Service Categories', 'motopress-appointment' ),
			'parent_item'                => esc_html__( 'Parent Service Category', 'motopress-appointment' ),
			'parent_item_colon'          => esc_html__( 'Parent Service Category:', 'motopress-appointment' ),
			'edit_item'                  => esc_html__( 'Edit Service Category', 'motopress-appointment' ),
			'update_item'                => esc_html__( 'Update Service Category', 'motopress-appointment' ),
			'add_new_item'               => esc_html__( 'Add New Service Category', 'motopress-appointment' ),
			'new_item_name'              => esc_html__( 'New Service Category Name', 'motopress-appointment' ),
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
			'hierarchical' => true,
			'query_var'    => true,
			'rewrite'      => array(
				'slug'         => 'service-category',
				'with_front'   => false,
				'hierarchical' => false,
			),
		);
	}

	/**
	 * @return array
	 *
	 * @since 1.2
	 */
	protected function getTagLabels() {
		return array(
			'name'                       => esc_html__( 'Service Tags', 'motopress-appointment' ),
			'singular_name'              => esc_html__( 'Service Tag', 'motopress-appointment' ),
			'search_items'               => esc_html__( 'Search Service Tags', 'motopress-appointment' ),
			'popular_items'              => esc_html__( 'Popular Service Tags', 'motopress-appointment' ),
			'all_items'                  => esc_html__( 'All Service Tags', 'motopress-appointment' ),
			'parent_item'                => esc_html__( 'Parent Service Tag', 'motopress-appointment' ),
			'parent_item_colon'          => esc_html__( 'Parent Service Tag:', 'motopress-appointment' ),
			'edit_item'                  => esc_html__( 'Edit Service Tag', 'motopress-appointment' ),
			'update_item'                => esc_html__( 'Update Service Tag', 'motopress-appointment' ),
			'add_new_item'               => esc_html__( 'Add New Service Tag', 'motopress-appointment' ),
			'new_item_name'              => esc_html__( 'New Service Tag Name', 'motopress-appointment' ),
			'separate_items_with_commas' => esc_html__( 'Separate tags with commas', 'motopress-appointment' ),
			'add_or_remove_items'        => esc_html__( 'Add or remove tags', 'motopress-appointment' ),
			'choose_from_most_used'      => esc_html__( 'Choose from the most used tags', 'motopress-appointment' ),
			'not_found'                  => esc_html__( 'No tags found.', 'motopress-appointment' ),
			'menu_name'                  => esc_html__( 'Tags', 'motopress-appointment' ),
		);
	}

	/**
	 * @return array
	 *
	 * @since 1.2
	 */
	protected function getTagArgs() {
		return parent::registerArgs() + array(
			'public'       => true,
			'show_ui'      => true,
			'show_in_menu' => mpapp()->pages()->appointmentMenu()->getId(),
			'query_var'    => true,
			'rewrite'      => array(
				'slug'       => 'service-tag',
				'with_front' => false,
			),
		);
	}

	/**
	 * @access protected
	 *
	 * @global array $submenu
	 *
	 * @since 1.0
	 */
	public function addTaxonomiesToMenu() {
		global $submenu;

		$menuId = mpapp()->pages()->appointmentMenu()->getId();

		if ( ! isset( $submenu[ $menuId ] ) ) {
			return;
		}

		$categoriesSlug = 'edit-tags.php?post_type=' . self::POST_TYPE . '&amp;taxonomy=' . self::CATEGORY_NAME;
		$tagsSlug       = 'edit-tags.php?post_type=' . self::POST_TYPE . '&amp;taxonomy=' . self::TAG_NAME;

		$taxonomyMenuItems = array(
			array( esc_html__( 'Service Categories', 'motopress-appointment' ), 'manage_categories', $categoriesSlug ),
			array( esc_html__( 'Service Tags', 'motopress-appointment' ), 'manage_categories', $tagsSlug ),
		);

		array_splice( $submenu[ $menuId ], 4, 0, $taxonomyMenuItems );
	}
}
