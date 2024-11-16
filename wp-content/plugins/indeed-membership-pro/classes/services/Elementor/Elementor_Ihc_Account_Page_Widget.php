<?php

if ( ! defined( 'ABSPATH' ) ){
   exit;
}

class Elementor_Ihc_Account_Page_Widget extends \Elementor\Widget_Base
{

  public function get_name()
  {
      return 'ump-account-page-shortcode';
  }

  public function get_title()
  {
      return esc_html__( 'UMP - Account Page', 'ihc' );
  }

  public function get_icon()
  {
      return 'fa fa-code';
  }

  protected function render()
  {
      echo esc_ump_content('[ihc-user-page]');
  }

}
