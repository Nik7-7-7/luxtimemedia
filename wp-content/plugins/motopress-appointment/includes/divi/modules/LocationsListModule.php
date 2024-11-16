<?php

namespace MotoPress\Appointment\Divi\Modules;

use MotoPress\Appointment\Shortcodes;

class LocationsListModule extends AbstractShortcodeModule {

	public $slug       = 'mpa_location_list';
	public $vb_support = 'partial';

	protected $module_credits = array(
		'module_uri' => '',
		'author'     => 'MotoPress',
		'author_uri' => 'https://motopress.com/',
	);

	public function init() {

		$this->name = esc_html__( 'Locations List', 'motopress-appointment' );
	}

	public function get_fields() {

		return array(
			'show_image'     => array(
				'label'           => esc_html__( 'Show featured image.', 'motopress-appointment' ),
				'type'            => 'yes_no_button',
				'option_category' => 'basic_option',
				'options'         => array(
					'on'  => esc_html__( 'Yes', 'motopress-appointment' ),
					'off' => esc_html__( 'No', 'motopress-appointment' ),
				),
				'default'         => 'on',
			),
			'show_title'     => array(
				'label'           => esc_html__( 'Show post title.', 'motopress-appointment' ),
				'type'            => 'yes_no_button',
				'option_category' => 'basic_option',
				'options'         => array(
					'on'  => esc_html__( 'Yes', 'motopress-appointment' ),
					'off' => esc_html__( 'No', 'motopress-appointment' ),
				),
				'default'         => 'on',
			),
			'show_excerpt'   => array(
				'label'           => esc_html__( 'Show post excerpt.', 'motopress-appointment' ),
				'type'            => 'yes_no_button',
				'option_category' => 'basic_option',
				'options'         => array(
					'on'  => esc_html__( 'Yes', 'motopress-appointment' ),
					'off' => esc_html__( 'No', 'motopress-appointment' ),
				),
				'default'         => 'on',
			),
			'locations'      => array(
				'label'           => esc_html__( 'Locations', 'motopress-appointment' ),
				'type'            => 'text',
				'option_category' => 'basic_option',
				'description'     => esc_html__( 'Comma-separated slugs or IDs of locations that will be shown.', 'motopress-appointment' ),
			),
			'categories'     => array(
				'label'           => esc_html__( 'Categories', 'motopress-appointment' ),
				'type'            => 'text',
				'option_category' => 'basic_option',
				'description'     => esc_html__( 'Comma-separated slugs or IDs of categories that will be shown.', 'motopress-appointment' ),
			),
			'posts_per_page' => array(
				'label'          => esc_html__( 'Posts Per Page', 'motopress-appointment' ),
				'type'           => 'range',
				'default'        => '3',
				'unitless'       => true,
				'range_settings' => array(
					'min'  => -1,
					'max'  => 100,
					'step' => 1,
				),
			),
			'columns_count'  => array(
				'label'          => esc_html__( 'Columns Count', 'motopress-appointment' ),
				'description'    => esc_html__( 'The number of columns in the grid.', 'motopress-appointment' ),
				'type'           => 'range',
				'default'        => '3',
				'unitless'       => true,
				'range_settings' => array(
					'min'  => -1,
					'max'  => 100,
					'step' => 1,
				),
			),
			'orderby'        => array(
				'label'            => esc_html__( 'Order By', 'motopress-appointment' ),
				'type'             => 'select',
				'default'          => 'none',
				'options'          => array(
					'none'             => esc_html__( 'No order', 'motopress-appointment' ),
					'ID'               => esc_html__( 'Post ID', 'motopress-appointment' ),
					'author'           => esc_html__( 'Post author', 'motopress-appointment' ),
					'title'            => esc_html__( 'Post title', 'motopress-appointment' ),
					'name'             => esc_html__( 'Post name (post slug)', 'motopress-appointment' ),
					'date'             => esc_html__( 'Post date', 'motopress-appointment' ),
					'modified'         => esc_html__( 'Last modified date', 'motopress-appointment' ),
					'rand'             => esc_html__( 'Random order', 'motopress-appointment' ),
					'relevance'        => esc_html__( 'Relevance', 'motopress-appointment' ),
					'menu_order'       => esc_html__( 'Page order', 'motopress-appointment' ),
					'menu_order title' => esc_html__( 'Page order and post title', 'motopress-appointment' ),
				),
				'default_on_front' => 'none',
			),
			'order'          => array(
				'label'            => esc_html__( 'Order', 'motopress-appointment' ),
				'type'             => 'select',
				'default'          => 'desc',
				'options'          => array(
					'desc' => esc_html__( 'DESC', 'motopress-appointment' ),
					'asc'  => esc_html__( 'ASC', 'motopress-appointment' ),
				),
				'default_on_front' => 'desc',
				'show_if'          => array(
					'orderby' => array( 'ID', 'author', 'title', 'name', 'date', 'modified', 'rand', 'relevance', 'menu_order', 'menu_order title' ),
				),
			),

		);
	}

	/**
	 * @since 1.19.1
	 *
	 * @return Shortcodes\LocationsListShortcode
	 */
	protected function getMPAShortcode(): Shortcodes\AbstractShortcode {
		return mpa_shortcodes()->locationsList();
	}
}
