<?php

if ( ! defined( 'ABSPATH' ) ){
   exit;
}

class Elementor_Ihc_Select_Level_Widget extends \Elementor\Widget_Base
{

  public function get_name()
  {
      return 'ump-select-level-shortcode';
  }

  public function get_title()
  {
      return esc_html__( 'UMP - Select Membership', 'ihc' );
  }

  public function get_icon()
  {
      return 'fa fa-code';
  }

  protected function render()
  {
      echo esc_ump_content('[ihc-select-level]');
  }

}
