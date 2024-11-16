<?php

namespace MotoPress\Appointment\Metaboxes\Shortcode;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.2
 */
class ServiceCategoriesMetabox extends AbstractShortcodeMetabox {

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	protected function theName(): string {
		return 'service_categories_metabox';
	}

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	public function getLabel(): string {
		return esc_html__( 'Service Categories', 'motopress-appointment' );
	}

	/**
	 * @return array
	 *
	 * @since 1.2
	 */
	protected function theFields() {
		return array(
			'name'               => array(
				'type'  => 'hidden',
				'value' => mpa_shortcodes()->serviceCategories()->getName(),
			),
			'show_items'         => array(
				'type'        => 'checklist',
				'label'       => esc_html__( 'Show Items', 'motopress-appointment' ),
				'description' => esc_html__( 'Show or hide extra blocks.', 'motopress-appointment' ),
				'options'     => array(
					'image'       => esc_html__( 'Featured image', 'motopress-appointment' ),
					'count'       => esc_html__( 'Services count', 'motopress-appointment' ),
					'description' => esc_html__( 'Description', 'motopress-appointment' ),
				),
				'value'       => array( 'image', 'count', 'description' ),
			),
			'parent'             => array(
				'type'        => 'text',
				'label'       => esc_html__( 'Parent', 'motopress-appointment' ),
				'description' => esc_html__( 'Parent term slug or ID to retrieve direct-child terms from.', 'motopress-appointment' ),
				'size'        => 'large',
			),
			'categories'         => array(
				'type'        => 'text',
				'label'       => esc_html__( 'Categories', 'motopress-appointment' ),
				'description' => esc_html__( 'Comma-separated slugs or IDs of categories that will be shown.', 'motopress-appointment' ),
				'size'        => 'large',
			),
			'exclude_categories' => array(
				'type'        => 'text',
				'label'       => esc_html__( 'Exclude Categories', 'motopress-appointment' ),
				'description' => esc_html__( 'Comma-separated slugs or IDs of categories that will not be shown.', 'motopress-appointment' ),
				'size'        => 'large',
			),
			'hide_empty'         => array(
				'type'    => 'checkbox',
				'label'   => esc_html__( 'Hide Empty', 'motopress-appointment' ),
				'label2'  => esc_html__( 'Hide terms not assigned to any posts.', 'motopress-appointment' ),
				'default' => true,
			),
			'depth'              => array(
				'type'        => 'number',
				'label'       => esc_html__( 'Depth', 'motopress-appointment' ),
				'description' => esc_html__( 'Display depth of child categories.', 'motopress-appointment' ),
				'min'         => -1,
				'default'     => '',
				'size'        => 'small',
			),
			'number'             => array(
				'type'        => 'number',
				'label'       => esc_html__( 'Number', 'motopress-appointment' ),
				'description' => esc_html__( 'Maximum number of categories to show.', 'motopress-appointment' ),
				'min'         => 0,
				'default'     => '',
				'size'        => 'small',
			),
			'columns_count'      => array(
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
		return $shortcodeName == mpa_shortcodes()->serviceCategories()->getName();
	}
}
