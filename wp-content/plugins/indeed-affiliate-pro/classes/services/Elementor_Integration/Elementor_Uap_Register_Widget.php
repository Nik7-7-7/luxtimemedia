<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Elementor_Uap_Register_Widget extends \Elementor\Widget_Base
{

  public function get_name()
  {
      return 'uap-register-shortcode';
  }

  public function get_title()
  {
      return esc_html__( 'UAP - Register Page', 'uap' );
  }

  public function get_icon()
  {
      return 'fa fa-code';
  }

  protected function render()
  {
      echo esc_uap_content('[uap-register]');
  }

}
