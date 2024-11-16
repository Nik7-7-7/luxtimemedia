<?php
namespace PixelYourSite\SuperPack;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class SpWooSearchCondition extends SpCondition {

    public function register_sub_conditions()
    {
        //  empty
    }

    public function get_label()
    {
        return 'Search Results';
    }

    public function get_name()
    {
        return 'product_search';
    }

    public function check($args)
    {
        return is_search() && 'product' === get_query_var( 'post_type' );
    }
}