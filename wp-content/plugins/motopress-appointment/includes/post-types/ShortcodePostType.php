<?php

namespace MotoPress\Appointment\PostTypes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.2
 */
class ShortcodePostType extends AbstractPostType {

	/** @since 1.2 */
	const POST_TYPE = 'mpa_shortcode';

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	public function getLabel() {
		return esc_html__( 'Shortcodes', 'motopress-appointment' );
	}

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	public function getSingularLabel() {
		return esc_html__( 'Shortcode', 'motopress-appointment' );
	}

	/**
	 * @return array
	 *
	 * @since 1.2
	 */
	protected function getLabels() {
		return array(
			'name'               => $this->getLabel(),
			'singular_name'      => $this->getSingularLabel(),
			'add_new'            => esc_html_x( 'Add New', 'Add new shortcode', 'motopress-appointment' ),
			'add_new_item'       => esc_html__( 'Add New Shortcode', 'motopress-appointment' ),
			'new_item'           => esc_html__( 'New Shortcode', 'motopress-appointment' ),
			'edit_item'          => esc_html__( 'Edit Shortcode', 'motopress-appointment' ),
			'view_item'          => esc_html__( 'View Shortcode', 'motopress-appointment' ),
			'search_items'       => esc_html__( 'Search Shortcode', 'motopress-appointment' ),
			'not_found'          => esc_html__( 'No shortcodes found', 'motopress-appointment' ),
			'not_found_in_trash' => esc_html__( 'No shortcodes found in Trash', 'motopress-appointment' ),
			'all_items'          => esc_html__( 'Shortcodes', 'motopress-appointment' ),
		);
	}

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	protected function getDescription() {
		return esc_html__( 'This is where you can set custom settings for your shortcodes.', 'motopress-appointment' );
	}

	/**
	 * @return array
	 *
	 * @since 1.2
	 */
	protected function registerArgs() {
		return array(
			'public'       => false,
			'show_ui'      => true,
			'supports'     => array( 'title' ),
			'show_in_menu' => mpapp()->pages()->appointmentMenu()->getId(),
			'capabilities' => array(
				'create_posts' => 'create_' . static::POST_TYPE . 's',
			),
		);
	}
}
