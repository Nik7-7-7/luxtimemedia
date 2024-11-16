<?php
namespace PixelYourSite\SuperPack;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class SpChildOfCondition extends SpCondition {

    public function register_sub_conditions()
    {
       // empty
    }

    public function get_label()
    {
        return 'Direct Child Of';
    }

    public function get_name()
    {
        return 'child_of';
    }

    public function check($args)
    {
        if($args['id'] == '') return true;

        if ( ! is_singular() ) {
            return false;
        }

        $id = (int) $args['id'];
        $parent_id = wp_get_post_parent_id( get_the_ID() );

        return ( ( 0 === $id && 0 < $parent_id ) || ( $parent_id === $id ) );
    }

    public function get_controls()
    {
        $hierarchical_post_types = get_post_types( [
            'hierarchical' => true,
            'public' => true,
        ] );

        return [
            'type' => 'search',
            'query' => [
                'object' => 'post',
                'query' => [
                    'post_type' => array_keys( $hierarchical_post_types ),
                ],
            ],
            'name' => 'sub_id',
        ];
    }
}