<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Elementor_Uap_Login_Form_Widget extends \Elementor\Widget_Base
{

  public function get_name()
  {
      return 'uap-login-form';
  }

  public function get_title()
  {
      return esc_html__( 'UAP - Login Page', 'uap' );
  }

  public function get_icon()
  {
      return 'fa fa-code';
  }

  protected function render()
  {
      echo esc_uap_content('[uap-login-form]');
  }

}
