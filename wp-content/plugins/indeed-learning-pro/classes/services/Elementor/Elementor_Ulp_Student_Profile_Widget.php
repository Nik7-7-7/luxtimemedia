<?php

if ( ! defined( 'ABSPATH' ) ){
   exit;
}

class Elementor_Ulp_Student_Profile_Widget extends \Elementor\Widget_Base
{

  public function get_name()
  {
      return 'ulp-student-profile-shortcode';
  }

  public function get_title()
  {
      return esc_html__( 'ULP - Student Profile', 'ulp' );
  }

  public function get_icon()
  {
      return 'fa fa-code';
  }

  protected function render()
  {
      echo esc_ulp_content('[ulp-student-profile]');
  }

}
