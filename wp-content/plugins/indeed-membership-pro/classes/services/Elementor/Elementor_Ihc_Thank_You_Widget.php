<?php

if ( ! defined( 'ABSPATH' ) ){
   exit;
}

class Elementor_Ihc_Thank_You_Widget extends \Elementor\Widget_Base
{

  public function get_name()
  {
      return 'ump-thank-you-page-shortcode';
  }

  public function get_title()
  {
      return esc_html__( 'UMP - Thank You Page', 'ihc' );
  }

  public function get_icon()
  {
      return 'fa fa-code';
  }

  protected function render()
  {
      echo esc_ump_content('[ihc-thank-you-page]');
  }

}
