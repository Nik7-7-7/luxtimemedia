<?php

if ( ! defined( 'ABSPATH' ) ){
   exit;
}

class Elementor_Ihc_Visitor_Inside_User_Page_Widget extends \Elementor\Widget_Base
{

  public function get_name()
  {
      return 'ump-visitor-inside-user-page-shortcode';
  }

  public function get_title()
  {
      return esc_html__( 'UMP - Visitor Inside User Page', 'ihc' );
  }

  public function get_icon()
  {
      return 'fa fa-code';
  }

  protected function render()
  {
      echo esc_ump_content('[ihc-visitor-inside-user-page]');
  }

}
