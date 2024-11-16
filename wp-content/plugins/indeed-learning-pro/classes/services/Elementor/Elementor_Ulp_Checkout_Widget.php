<?php

if ( ! defined( 'ABSPATH' ) ){
   exit;
}

class Elementor_Ulp_Checkout_Widget extends \Elementor\Widget_Base
{

  public function get_name()
  {
      return 'ulp-checkout-shortcode';
  }

  public function get_title()
  {
      return esc_html__( 'ULP - Checkout', 'ulp' );
  }

  public function get_icon()
  {
      return 'fa fa-code';
  }

  protected function render()
  {
      echo esc_ulp_content('[ulp_checkout]');
  }

}
