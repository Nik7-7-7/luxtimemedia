<?php

namespace MotoPress\Appointment\Divi\Modules;

use ET_Builder_Module;

class EmployeeTitleModule extends ET_Builder_Module {

	public $slug       = 'mpa_employee_title';
	public $vb_support = 'partial';

	protected $module_credits = array(
		'module_uri' => '',
		'author'     => 'MotoPress',
		'author_uri' => 'https://motopress.com/',
	);

	public function init() {

		$this->name = esc_html__( 'Employee Title', 'motopress-appointment' );
	}

	public function get_fields() {

		return array(
			'id' => array(
				'label'           => esc_html__( 'ID', 'motopress-appointment' ),
				'type'            => 'text',
				'option_category' => 'basic_option',
				'description'     => esc_html__( "Post ID of an employee to display content from. Note: this parameter automatically uses the current post ID when a shortcode is inside the employee's post and is required otherwise.", 'motopress-appointment' ),
			),
		);
	}

	public function render( $attrs, $content, $render_slug ) {

		$id = $this->props['id'];

		$args = array(
			'id' => $id,
		);

		return mpa_shortcodes()->employeeTitle()->render( $args, $content );
	}
}
