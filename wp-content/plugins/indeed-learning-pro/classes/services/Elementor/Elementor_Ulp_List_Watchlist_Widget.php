<?php

if ( ! defined( 'ABSPATH' ) ){
   exit;
}

class Elementor_Ulp_List_Watchlist_Widget extends \Elementor\Widget_Base
{

  public function get_name()
  {
      return 'ulp-list-watchlist-shortcode';
  }

  public function get_title()
  {
      return esc_html__( 'ULP - List Watch List', 'ulp' );
  }

  public function get_icon()
  {
      return 'fa fa-code';
  }

  protected function render()
  {
      echo esc_ulp_content('[ulp_list_watch_list]');
  }

}
