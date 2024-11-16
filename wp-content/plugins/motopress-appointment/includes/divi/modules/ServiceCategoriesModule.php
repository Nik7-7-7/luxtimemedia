<?php

namespace MotoPress\Appointment\Divi\Modules;

use MotoPress\Appointment\Shortcodes;

class ServiceCategoriesModule extends AbstractShortcodeModule {

	public $slug       = 'mpa_service_categories';
	public $vb_support = 'partial';

	protected $module_credits = array(
		'module_uri' => '',
		'author'     => 'MotoPress',
		'author_uri' => 'https://motopress.com/',
	);

	public function init() {

		$this->name = esc_html__( 'Service Categories', 'motopress-appointment' );
	}

	public function get_fields() {

		return array(
			'show_image'         => array(
				'label'           => esc_html__( 'Show featured image.', 'motopress-appointment' ),
				'type'            => 'yes_no_button',
				'option_category' => 'basic_option',
				'options'         => array(
					'on'  => esc_html__( 'Yes', 'motopress-appointment' ),
					'off' => esc_html__( 'No', 'motopress-appointment' ),
				),
				'default'         => 'on',
			),
			'show_count'         => array(
				'label'           => esc_html__( 'Show Services Count?', 'motopress-appointment' ),
				'type'            => 'yes_no_button',
				'option_category' => 'basic_option',
				'options'         => array(
					'on'  => esc_html__( 'Yes', 'motopress-appointment' ),
					'off' => esc_html__( 'No', 'motopress-appointment' ),
				),
				'default'         => 'on',
			),
			'show_description'   => array(
				'label'           => esc_html__( 'Show Description?', 'motopress-appointment' ),
				'type'            => 'yes_no_button',
				'option_category' => 'basic_option',
				'options'         => array(
					'on'  => esc_html__( 'Yes', 'motopress-appointment' ),
					'off' => esc_html__( 'No', 'motopress-appointment' ),
				),
				'default'         => 'on',
			),
			'parent'             => array(
				'label'           => esc_html__( 'Parent', 'motopress-appointment' ),
				'type'            => 'text',
				'option_category' => 'basic_option',
				'description'     => esc_html__( 'Parent term slug or ID to retrieve direct-child terms from.', 'motopress-appointment' ),
			),
			'categories'         => array(
				'label'           => esc_html__( 'Categories', 'motopress-appointment' ),
				'type'            => 'text',
				'option_category' => 'basic_option',
				'description'     => esc_html__( 'Comma-separated slugs or IDs of categories that will be shown.', 'motopress-appointment' ),
			),
			'exclude_categories' => array(
				'label'           => esc_html__( 'Exclude Categories', 'motopress-appointment' ),
				'type'            => 'text',
				'option_category' => 'basic_option',
				'description'     => esc_html__( 'Comma-separated slugs or IDs of categories that will not be shown.', 'motopress-appointment' ),
			),
			'hide_empty'         => array(
				'label'           => esc_html__( 'Hide Empty', 'motopress-appointment' ),
				'type'            => 'yes_no_button',
				'option_category' => 'basic_option',
				'options'         => array(
					'on'  => esc_html__( 'Yes', 'motopress-appointment' ),
					'off' => esc_html__( 'No', 'motopress-appointment' ),
				),
				'default'         => 'on',
			),
			'depth'              => array(
				'label'          => esc_html__( 'Depth', 'motopress-appointment' ),
				'description'    => esc_html__( 'Display depth of child categories.', 'motopress-appointment' ),
				'type'           => 'range',
				'default'        => '3',
				'unitless'       => true,
				'range_settings' => array(
					'min'  => -1,
					'max'  => 100,
					'step' => 1,
				),
			),
			'number'             => array(
				'label'          => esc_html__( 'Number', 'motopress-appointment' ),
				'description'    => esc_html__( 'Maximum number of categories to show.', 'motopress-appointment' ),
				'type'           => 'range',
				'default'        => '3',
				'unitless'       => true,
				'range_settings' => array(
					'min'  => -1,
					'max'  => 100,
					'step' => 1,
				),
			),
			'columns_count'      => array(
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
			'orderby'            => array(
				'label'            => esc_html__( 'Order By', 'motopress-appointment' ),
				'type'             => 'select',
				'default'          => 'none',
				'options'          => array(
					'none'       => esc_html__( 'No order', 'motopress-appointment' ),
					'name'       => esc_html__( 'Term name', 'motopress-appointment' ),
					'slug'       => esc_html__( 'Term slug', 'motopress-appointment' ),
					'term_id'    => esc_html__( 'Term ID', 'motopress-appointment' ),
					'parent'     => esc_html__( 'Parent ID', 'motopress-appointment' ),
					'count'      => esc_html__( 'Number of associated objects', 'motopress-appointment' ),
					'include'    => esc_html__( 'Keep the order of "IDs" parameter', 'motopress-appointment' ),
					'term_order' => esc_html__( 'Term order', 'motopress-appointment' ),
				),
				'default_on_front' => 'none',
			),
			'order'              => array(
				'label'            => esc_html__( 'Order', 'motopress-appointment' ),
				'type'             => 'select',
				'default'          => 'desc',
				'options'          => array(
					'desc' => esc_html__( 'DESC', 'motopress-appointment' ),
					'asc'  => esc_html__( 'ASC', 'motopress-appointment' ),
				),
				'default_on_front' => 'desc',
				'show_if'          => array(
					'orderby' => array( 'name', 'slug', 'term_id', 'parent', 'count', 'include', 'term_order' ),
				),
			),

		);
	}

	/**
	 * @since 1.19.1
	 *
	 * @return Shortcodes\ServiceCategoriesShortcode
	 */
	protected function getMPAShortcode(): Shortcodes\AbstractShortcode {
		return mpa_shortcodes()->serviceCategories();
	}
}
