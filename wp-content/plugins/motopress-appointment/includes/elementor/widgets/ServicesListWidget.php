<?php
/**
 * Class: ServicesListWidget
 * Name: Services List
 * Slug: mpa-services-list
 */

namespace MotoPress\Appointment\Elementor\Widgets;

use \Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ServicesListWidget extends AbstractAppointmentWidget {


	public function get_name() {
		return 'mpa-services-list';
	}

	public function get_title() {
		return esc_html__( 'Services List', 'motopress-appointment' );
	}

	public function get_icon() {
		return 'eicon-post-list';
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_content',
			array(
				'label' => esc_html__( 'Settings', 'motopress-appointment' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

			$this->add_control(
				'show_image',
				array(
					'label'        => esc_html__( 'Show featured image.', 'motopress-appointment' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'true',
					'default'      => 'true',
				)
			);

			$this->add_control(
				'show_title',
				array(
					'label'        => esc_html__( 'Show post title.', 'motopress-appointment' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'true',
					'default'      => 'true',
				)
			);

			$this->add_control(
				'show_excerpt',
				array(
					'label'        => esc_html__( 'Show post excerpt.', 'motopress-appointment' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'true',
					'default'      => 'true',
				)
			);

			$this->add_control(
				'show_price',
				array(
					'label'        => esc_html__( 'Show service price.', 'motopress-appointment' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'true',
					'default'      => 'true',
				)
			);

			$this->add_control(
				'show_duration',
				array(
					'label'        => esc_html__( 'Show service duration.', 'motopress-appointment' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'true',
					'default'      => 'true',
				)
			);

			$this->add_control(
				'show_capacity',
				array(
					'label'        => esc_html__( 'Show service capacity.', 'motopress-appointment' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'true',
					'default'      => 'true',
				)
			);

			$this->add_control(
				'show_employees',
				array(
					'label'        => esc_html__( 'Show service employees.', 'motopress-appointment' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'true',
					'default'      => 'true',
				)
			);

			$this->add_control(
				'services',
				array(
					'label'       => esc_html__( 'Services', 'motopress-appointment' ),
					'type'        => Controls_Manager::TEXT,
					'label_block' => true,
					'description' => esc_html__( 'Comma-separated slugs or IDs of services that will be shown.', 'motopress-appointment' ),
				)
			);

			$this->add_control(
				'employees',
				array(
					'label'       => esc_html__( 'Employees', 'motopress-appointment' ),
					'type'        => Controls_Manager::TEXT,
					'label_block' => true,
					'description' => esc_html__( 'Comma-separated slugs or IDs of employees that perform these services.', 'motopress-appointment' ),
				)
			);

			$this->add_control(
				'categories',
				array(
					'label'       => esc_html__( 'Categories', 'motopress-appointment' ),
					'type'        => Controls_Manager::TEXT,
					'label_block' => true,
					'description' => esc_html__( 'Comma-separated slugs or IDs of categories that will be shown.', 'motopress-appointment' ),
				)
			);

			$this->add_control(
				'tags',
				array(
					'label'       => esc_html__( 'Tags', 'motopress-appointment' ),
					'type'        => Controls_Manager::TEXT,
					'label_block' => true,
					'description' => esc_html__( 'Comma-separated slugs or IDs of tags that will be shown.', 'motopress-appointment' ),
				)
			);

			$this->add_control(
				'posts_per_page',
				array(
					'label'    => esc_html__( 'Posts Per Page', 'motopress-appointment' ),
					'type'     => Controls_Manager::NUMBER,
					'required' => true,
					'step'     => 1,
					'min'      => -1,
					'default'  => 3,
				)
			);

			$this->add_control(
				'columns_count',
				array(
					'label'       => esc_html__( 'Columns Count', 'motopress-appointment' ),
					'description' => esc_html__( 'The number of columns in the grid.', 'motopress-appointment' ),
					'type'        => Controls_Manager::NUMBER,
					'required'    => true,
					'step'        => 1,
					'min'         => -1,
					'default'     => 3,
				)
			);

			$this->add_control(
				'orderby',
				array(
					'label'     => esc_html__( 'Order By', 'motopress-appointment' ),
					'type'      => Controls_Manager::SELECT,
					'default'   => 'none',
					'options'   => array(
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
						'price'            => esc_html__( 'Price', 'motopress-appointment' ),
					),
					'separator' => 'before',
				)
			);

			$this->add_control(
				'order',
				array(
					'label'     => esc_html__( 'Order', 'motopress-appointment' ),
					'type'      => Controls_Manager::SELECT,
					'default'   => 'desc',
					'options'   => array(
						'desc' => esc_html__( 'DESC', 'motopress-appointment' ),
						'asc'  => esc_html__( 'ASC', 'motopress-appointment' ),
					),
					'condition' => array(
						'orderby!' => 'none',
					),
				)
			);

			$this->add_control(
				'html_id',
				array(
					'label'       => esc_html__( 'HTML Anchor', 'motopress-appointment' ),
					'type'        => Controls_Manager::TEXT,
					'default'     => '',
					'dynamic'     => array(
						'active' => true,
					),
					'label_block' => true,
					'separator'   => 'before',
					'description' => mpa_kses_link( __( 'HTML Anchor. Anchors lets you link directly to a section on a page. <a href="https://wordpress.org/support/article/page-jumps/" target="_blank">Learn more about anchors.</a>', 'motopress-appointment' ) ),
				)
			);

			$this->add_control(
				'html_class',
				array(
					'label'       => esc_html__( 'CSS Class(es)', 'motopress-appointment' ),
					'type'        => Controls_Manager::TEXT,
					'default'     => '',
					'dynamic'     => array(
						'active' => true,
					),
					'label_block' => true,
					'description' => esc_html__( 'Additional CSS Class(es). Separate multiple classes with spaces.', 'motopress-appointment' ),
				)
			);

		$this->end_controls_section();
	}

	protected function render() {
		$attributes = $this->get_settings();
		echo mpa_shortcodes()->servicesList()->render( $attributes );
	}
}
