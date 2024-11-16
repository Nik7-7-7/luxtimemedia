<?php
/**
 * Class: EmployeeScheduleWidget
 * Name: Employee Schedule
 * Slug: mpa-employee-schedule
 */

namespace MotoPress\Appointment\Elementor\Widgets;

use \Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EmployeeScheduleWidget extends AbstractAppointmentWidget {


	public function get_name() {
		return 'mpa-employee-schedule';
	}

	public function get_title() {
		return esc_html__( 'Employee Schedule', 'motopress-appointment' );
	}

	public function get_icon() {
		return 'eicon-columns';
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
				'shortcode_id',
				array(
					'label'       => esc_html__( 'ID', 'motopress-appointment' ),
					'type'        => Controls_Manager::TEXT,
					'label_block' => true,
					'description' => esc_html__( "Post ID of an employee to display content from. Note: this parameter automatically uses the current post ID when a shortcode is inside the employee's post and is required otherwise.", 'motopress-appointment' ),
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
		$attributes       = $this->get_settings();
		$attributes['id'] = $attributes['shortcode_id'];

		echo mpa_shortcodes()->employeeSchedule()->render( $attributes );
	}
}
