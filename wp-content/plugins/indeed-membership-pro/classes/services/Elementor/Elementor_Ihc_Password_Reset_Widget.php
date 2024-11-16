<?php

if ( ! defined( 'ABSPATH' ) ){
   exit;
}

class Elementor_Ihc_Password_Reset_Widget extends \Elementor\Widget_Base
{

  public function get_name()
  {
      return 'ump-password-reset-shortcode';
  }

  public function get_title()
  {
      return esc_html__( 'UMP - Password Reset', 'ihc' );
  }

  public function get_icon()
  {
      return 'fa fa-code';
  }

  protected function render()
  {
      echo esc_ump_content('[ihc-pass-reset]');
  }

}
