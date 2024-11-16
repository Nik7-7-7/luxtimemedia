<?php

if ( ! defined( 'ABSPATH' ) ){
   exit;
}

class Elementor_Ihc_Checkout_Widget extends \Elementor\Widget_Base
{

  public function get_name()
  {
      return 'ump-checkout-page-shortcode';
  }

  public function get_title()
  {
      return esc_html__( 'UMP - Checkout Page', 'ihc' );
  }

  public function get_icon()
  {
      return 'fa fa-code';
  }

  protected function render()
  {
      echo esc_ump_content('[ihc-checkout-page]');
  }

}
