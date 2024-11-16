<?php

namespace MotoPress\Appointment\Metaboxes\Shortcode;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.2
 */
class LocationsListMetabox extends AbstractShortcodeMetabox {

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	protected function theName(): string {
		return 'locations_list_metabox';
	}

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	public function getLabel(): string {
		return esc_html__( 'Locations List', 'motopress-appointment' );
	}

	/**
	 * @return array
	 *
	 * @since 1.2
	 */
	protected function theFields() {
		return array(
			'name'           => array(
				'type'  => 'hidden',
				'value' => mpa_shortcodes()->locationsList()->getName(),
			),
			'show_items'     => array(
				'type'        => 'checklist',
				'label'       => esc_html__( 'Show Items', 'motopress-appointment' ),
				'description' => esc_html__( 'Show or hide extra blocks.', 'motopress-appointment' ),
				'options'     => array(
					'image'   => esc_html__( 'Featured image', 'motopress-appointment' ),
					'title'   => esc_html__( 'Post title', 'motopress-appointment' ),
					'excerpt' => esc_html__( 'Excerpt', 'motopress-appointment' ),
				),
				'value'       => array( 'image', 'title', 'excerpt' ),
			),
			'locations'      => array(
				'type'        => 'text',
				'label'       => esc_html__( 'Locations', 'motopress-appointment' ),
				'description' => esc_html__( 'Comma-separated slugs or IDs of locations that will be shown.', 'motopress-appointment' ),
				'size'        => 'large',
			),
			'categories'     => array(
				'type'        => 'text',
				'label'       => esc_html__( 'Categories', 'motopress-appointment' ),
				'description' => esc_html__( 'Comma-separated slugs or IDs of categories that will be shown.', 'motopress-appointment' ),
				'size'        => 'large',
			),
			'posts_per_page' => array(
				'type'    => 'number',
				'label'   => esc_html__( 'Posts Per Page', 'motopress-appointment' ),
				'min'     => -1,
				'default' => '',
				'size'    => 'small',
			),
			'columns_count'  => array(
				'type'        => 'number',
				'label'       => esc_html__( 'Columns Count', 'motopress-appointment' ),
				'description' => esc_html__( 'The number of columns in the grid.', 'motopress-appointment' ),
				'min'         => 1,
				'max'         => 6,
				'default'     => 3,
				'size'        => 'small',
			),
		);
	}

	/**
	 * @param string $shortcodeName
	 * @return bool
	 *
	 * @since 1.2
	 */
	protected function isForShortcode( $shortcodeName ) {
		return $shortcodeName == mpa_shortcodes()->locationsList()->getName();
	}
}
