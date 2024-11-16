<?php
/**
 * Class: ServiceCategoriesWidget
 * Name: Service Categories
 * Slug: mpa-service-categories
 */

namespace MotoPress\Appointment\Elementor\Widgets;

use \Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ServiceCategoriesWidget extends AbstractAppointmentWidget {


	public function get_name() {
		return 'mpa-service-categories';
	}

	public function get_title() {
		return esc_html__( 'Service Categories', 'motopress-appointment' );
	}

	public function get_icon() {
		return 'eicon-bullet-list';
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
				'show_count',
				array(
					'label'        => esc_html__( 'Show Services Count?', 'motopress-appointment' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'true',
					'default'      => 'true',
				)
			);

			$this->add_control(
				'show_description',
				array(
					'label'        => esc_html__( 'Show Description?', 'motopress-appointment' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'true',
					'default'      => 'true',
				)
			);

			$this->add_control(
				'parent',
				array(
					'label'       => esc_html__( 'Parent', 'motopress-appointment' ),
					'type'        => Controls_Manager::TEXT,
					'label_block' => true,
					'description' => esc_html__( 'Parent term slug or ID to retrieve direct-child terms from.', 'motopress-appointment' ),
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
				'exclude_categories',
				array(
					'label'       => esc_html__( 'Exclude Categories', 'motopress-appointment' ),
					'type'        => Controls_Manager::TEXT,
					'label_block' => true,
					'description' => esc_html__( 'Comma-separated slugs or IDs of categories that will not be shown.', 'motopress-appointment' ),
				)
			);

			$this->add_control(
				'hide_empty',
				array(
					'label'        => esc_html__( 'Hide Empty', 'motopress-appointment' ),
					'type'         => Controls_Manager::SWITCHER,
					'description'  => esc_html__( 'Hide terms not assigned to any posts.', 'motopress-appointment' ),
					'return_value' => 'true',
					'default'      => 'true',
				)
			);

			$this->add_control(
				'depth',
				array(
					'label'       => esc_html__( 'Depth', 'motopress-appointment' ),
					'description' => esc_html__( 'Display depth of child categories.', 'motopress-appointment' ),
					'type'        => Controls_Manager::NUMBER,
					'required'    => true,
					'step'        => 1,
					'min'         => -1,
					'default'     => 3,
				)
			);

			$this->add_control(
				'number',
				array(
					'label'       => esc_html__( 'Number', 'motopress-appointment' ),
					'description' => esc_html__( 'Maximum number of categories to show.', 'motopress-appointment' ),
					'type'        => Controls_Manager::NUMBER,
					'required'    => true,
					'step'        => 1,
					'min'         => -1,
					'default'     => 3,
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
						'none'       => esc_html__( 'No order', 'motopress-appointment' ),
						'name'       => esc_html__( 'Term name', 'motopress-appointment' ),
						'slug'       => esc_html__( 'Term slug', 'motopress-appointment' ),
						'term_id'    => esc_html__( 'Term ID', 'motopress-appointment' ),
						'parent'     => esc_html__( 'Parent ID', 'motopress-appointment' ),
						'count'      => esc_html__( 'Number of associated objects', 'motopress-appointment' ),
						'include'    => esc_html__( 'Keep the order of "IDs" parameter', 'motopress-appointment' ),
						'term_order' => esc_html__( 'Term order', 'motopress-appointment' ),
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
		echo mpa_shortcodes()->serviceCategories()->render( $attributes );
	}
}
