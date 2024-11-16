<?php

if ( ! defined( 'ABSPATH' ) ){
   exit;
}

class Elementor_Ihc_Register_Popup_Widget extends \Elementor\Widget_Base
{

  public function get_name()
  {
      return 'ump-register-popup-shortcode';
  }

  public function get_title()
  {
      return esc_html__( 'UMP - Register Popup', 'ihc' );
  }

  public function get_icon()
  {
      return 'fa fa-code';
  }

  protected function render()
  {
      $settings = $this->get_settings_for_display();
      echo esc_ump_content($settings['the_shortcode']);

  }

  protected function _register_controls()
  {

      $this->start_controls_section(
        'section_editor',
        [
          'label' => esc_html__( 'UMP - Register Popup', 'ihc' ),
        ]
      );

      $this->add_control(
        'the_shortcode',
        [
          'label' => '',
          'type' => \Elementor\Controls_Manager::WYSIWYG,
          'dynamic' => [
            'active' => true,
          ],
          'default' => '[ihc-register-popup]Register[/ihc-register-popup]',
        ]
      );
      $this->end_controls_section();

  }

}
