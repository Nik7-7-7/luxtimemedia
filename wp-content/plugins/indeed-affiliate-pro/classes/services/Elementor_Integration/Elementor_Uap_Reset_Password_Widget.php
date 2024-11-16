<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Elementor_Uap_Reset_Password_Widget extends \Elementor\Widget_Base
{

  public function get_name()
  {
      return 'uap-reset-password';
  }

  public function get_title()
  {
      return esc_html__( 'UAP - Reset Password', 'uap' );
  }

  public function get_icon(){
      return 'fa fa-code';
  }

  protected function render(){
      echo esc_uap_content('[uap-reset-password]');
  }


}
