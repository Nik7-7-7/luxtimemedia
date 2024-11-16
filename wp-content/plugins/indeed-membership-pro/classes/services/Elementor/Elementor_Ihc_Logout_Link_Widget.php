<?php

if ( ! defined( 'ABSPATH' ) ){
   exit;
}

class Elementor_Ihc_Logout_Link_Widget extends \Elementor\Widget_Base
{

  public function get_name()
  {
      return 'ump-logout-link-shortcode';
  }

  public function get_title()
  {
      return esc_html__( 'UMP - Logout Link', 'ihc' );
  }

  public function get_icon()
  {
      return 'fa fa-code';
  }

  protected function render()
  {
      echo esc_ump_content('[ihc-logout-link]');
  }

}
