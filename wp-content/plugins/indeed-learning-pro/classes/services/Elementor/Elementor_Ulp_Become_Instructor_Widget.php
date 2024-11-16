<?php

if ( ! defined( 'ABSPATH' ) ){
   exit;
}

class Elementor_Ulp_Become_Instructor_Widget extends \Elementor\Widget_Base
{

  public function get_name()
  {
      return 'ulp-become-instructor-shortcode';
  }

  public function get_title()
  {
      return esc_html__( 'ULP - Become Instructor', 'ulp' );
  }

  public function get_icon()
  {
      return 'fa fa-code';
  }

  protected function render()
  {
      echo esc_ulp_content('[ulp-become-instructor]');
  }

}
