<?php

class ET_Builder_Module_Mailster_Block_Form extends ET_Builder_Module {

	protected $whitelisted_fields;
	protected $icon;

	public function init() {
		$this->name       = esc_html__( 'Mailster Form', 'mailster' );
		$this->slug       = 'divi-mailster-block-form';
		$this->vb_support = 'partial';

		$this->whitelisted_fields = array(
			'form',
		);

		$this->main_css_element = '%%order_class%%';
		$this->icon             = '1'; // email icon
		$this->advanced_fields  = false;
	}

	public function get_fields() {

		$forms = mailster( 'block-forms' )->get_all();

		$options = array( '0' => __( 'Select form', 'mailster' ) );
		foreach ( $forms as $form ) {
			$options[ $form->ID ] = $form->post_title;
		}

		return array(
			'form' => array(
				'default'          => '0',
				'default_on_front' => false,
				'label'            => esc_html__( 'Select form', 'mailster' ),
				'type'             => 'select',
				'description'      => esc_html__( 'Choose the form you like to display here.', 'mailster' ),
				'options'          => $options,
			),
		);
	}

	public function render( $attrs, $content = null, $render_slug = null ) {

		$form = isset( $attrs['form'] ) ? $attrs['form'] : null;
		if ( ! $form ) {
			return;
		}

		// Render module content
		$output = mailster( 'block-forms' )->render_form( $form, array(), false );

		return $this->_render_module_wrapper( $output, $render_slug );
	}
}
