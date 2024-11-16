<?php
namespace PixelYourSite\SuperPack;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class SpWooShopCondition extends SpCondition {

    public function register_sub_conditions()
    {
        // empty
    }

    public function get_label()
    {
        return 'Shop Page';
    }

    public function get_name()
    {
        return 'shop_page';
    }

    public function check($args)
    {
       return is_shop();
    }
}