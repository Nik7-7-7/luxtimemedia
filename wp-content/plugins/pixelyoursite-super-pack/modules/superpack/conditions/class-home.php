<?php
namespace PixelYourSite\SuperPack;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class SpHomeCondition extends SpCondition {

    public function register_sub_conditions()
    {
       return[];
    }

    public function get_name()
    {
       return 'front_page';
    }

    public function get_label()
    {
        return 'Front Page';
    }

    public function check($args)
    {
        return is_front_page();
    }

}