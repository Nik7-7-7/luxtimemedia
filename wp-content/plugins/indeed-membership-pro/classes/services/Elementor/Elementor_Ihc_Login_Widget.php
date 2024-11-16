<?php

if ( ! defined( 'ABSPATH' ) ){
   exit;
}

class Elementor_Ihc_Login_Widget extends \Elementor\Widget_Base
{

  public function get_name()
  {
      return 'ump-login-page-shortcode';
  }

  public function get_title()
  {
      return esc_html__( 'UMP - Login Page', 'ihc' );
  }

  public function get_icon()
  {
      return 'fa fa-code';
  }

  protected function render()
  {
      echo esc_ump_content('[ihc-login-form]');
  }

}
