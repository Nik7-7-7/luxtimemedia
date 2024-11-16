<?php
namespace PixelYourSite\SuperPack;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class SpSearchCondition extends SpCondition {

    public function register_sub_conditions()
    {

    }

    public function get_name() {
        return 'search';
    }

    public function get_label() {
        return 'Search Results';
    }

    public function check( $args ) {
        return is_search();
    }
}