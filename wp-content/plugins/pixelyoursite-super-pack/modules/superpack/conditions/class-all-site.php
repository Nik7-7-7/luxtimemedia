<?php
namespace PixelYourSite\SuperPack;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class SpAllSiteCondition extends SpCondition {

    public function register_sub_conditions()
    {
        return[];
    }

    public function get_label()
    {
        return 'Entire Site';
    }

    public function get_name()
    {
        return 'all_site';
    }

    public function check($args)
    {
        return true;
    }

}