<?php

if ( ! defined( 'ABSPATH' ) ){
   exit;
}

class Elementor_Ihc_Login_Popup_Widget extends \Elementor\Widget_Base
{

  public function get_name()
  {
      return 'ump-login-popup-shortcode';
  }

  public function get_title()
  {
      return esc_html__( 'UMP - Login Popup', 'ihc' );
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
          'label' => esc_html__( 'UMP - Login Popup', 'ihc' ),
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
          'default' => '[ihc-login-popup]Login[/ihc-login-popup]',
        ]
      );
      $this->end_controls_section();

  }

}
